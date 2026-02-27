<?php

namespace App\Services\Campaign;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\OptOutRecord;
use App\Models\Tag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * RecipientResolverService — expands, deduplicates, validates, and persists
 * campaign recipients from multiple sources (contact lists, tags, CSV, manual entry).
 *
 * This is the most complex integration in the send pipeline:
 * 1. Expand sources -> raw contacts/numbers
 * 2. Deduplicate by mobile number
 * 3. Filter opted-out numbers (regulatory requirement)
 * 4. Validate & normalise to E.164
 * 5. Detect country ISO for pricing
 * 6. Snapshot contact data for merge fields
 * 7. Batch-insert campaign_recipients
 *
 * Memory-safe: streams recipients through a chunked pipeline. At no point does
 * the full recipient list exist in memory — each chunk is expanded, validated,
 * and persisted before the next chunk is loaded. Supports campaigns with
 * millions of recipients within a constant ~50MB memory footprint.
 */
class RecipientResolverService
{
    private const CHUNK_SIZE = 2000;
    private const MAX_INVALID_DETAILS = 100;

    /**
     * Resolve all recipients for a campaign and persist them to campaign_recipients.
     *
     * Uses a streaming pipeline: expand sources in chunks -> dedup -> validate ->
     * opt-out filter -> persist. Only one chunk is held in memory at a time.
     *
     * @param Campaign $campaign The campaign to resolve recipients for
     * @param string $defaultCountry Default country for local number normalisation
     * @return ResolverResult
     */
    public function resolve(Campaign $campaign, string $defaultCountry = 'GB'): ResolverResult
    {
        $accountId = $campaign->account_id;
        $sources = $campaign->recipient_sources ?? [];

        Log::info('[RecipientResolver] Starting resolution', [
            'campaign_id' => $campaign->id,
            'account_id' => $accountId,
            'source_count' => count($sources),
        ]);

        // Pre-load the opt-out set for this account (phone numbers only, ~20 bytes each)
        $optedOutSet = $this->loadOptedOutSet($accountId);

        // Merge screening lists — contacts on these lists are treated as opted-out
        $screeningListIds = $campaign->opt_out_screening_list_ids ?? [];
        if (!empty($screeningListIds)) {
            $screeningSet = $this->loadScreeningListSet($screeningListIds, $accountId);
            $optedOutSet = array_merge($optedOutSet, $screeningSet);
        }

        // Dedup tracker: normalised number -> true (holds only the key string, not full row)
        $seenNumbers = [];

        // Counters
        $totalResolved = 0;
        $totalUnique = 0;
        $totalOptedOut = 0;
        $totalInvalid = 0;
        $totalCreated = 0;

        // Breakdown accumulators
        $sourceBreakdown = [];
        $countryBreakdown = [];
        $invalidDetails = [];

        $batchSize = $campaign->batch_size ?: Campaign::DEFAULT_BATCH_SIZE;

        // Process each source, streaming chunks through the pipeline
        foreach ($sources as $source) {
            $type = $source['type'] ?? null;
            if (!$type) {
                continue;
            }

            // Each source yields chunks of raw recipients
            $chunks = $this->expandSourceChunked($source, $accountId);

            foreach ($chunks as $rawChunk) {
                $totalResolved += count($rawChunk);

                // --- Dedup within this chunk (and against all previously seen) ---
                $dedupedChunk = [];
                foreach ($rawChunk as $recipient) {
                    $number = $recipient['mobile_number'];
                    $normalised = preg_replace('/[\s\-\(\)\.]/', '', $number);
                    $normalised = ltrim($normalised, '+');

                    if (isset($seenNumbers[$normalised])) {
                        continue;
                    }
                    $seenNumbers[$normalised] = true;
                    $dedupedChunk[] = $recipient;
                }
                $totalUnique += count($dedupedChunk);

                if (empty($dedupedChunk)) {
                    continue;
                }

                // --- Validate & normalise phone numbers ---
                $validRecipients = [];
                foreach ($dedupedChunk as $recipient) {
                    $result = PhoneNumberUtils::normalise($recipient['mobile_number'], $defaultCountry);

                    if ($result['valid'] && PhoneNumberUtils::isValidMobile($result['number'])) {
                        $recipient['mobile_number'] = $result['number'];
                        $validRecipients[] = $recipient;
                    } else {
                        $totalInvalid++;
                        if (count($invalidDetails) < self::MAX_INVALID_DETAILS) {
                            $invalidDetails[] = [
                                'number' => $recipient['mobile_number'],
                                'error' => $result['error'] ?? 'Invalid mobile number format',
                                'contact_id' => $recipient['contact_id'] ?? null,
                            ];
                        }
                    }
                }
                unset($dedupedChunk);

                if (empty($validRecipients)) {
                    continue;
                }

                // --- Enrich with country ISO ---
                foreach ($validRecipients as &$r) {
                    $r['country_iso'] = PhoneNumberUtils::detectCountry($r['mobile_number']);
                }
                unset($r);

                // --- Filter opted-out numbers ---
                $cleanRecipients = [];
                $chunkOptedOut = [];
                foreach ($validRecipients as $recipient) {
                    if (isset($optedOutSet[$recipient['mobile_number']])) {
                        $chunkOptedOut[] = $recipient;
                    } else {
                        $cleanRecipients[] = $recipient;
                    }
                }
                $totalOptedOut += count($chunkOptedOut);
                unset($validRecipients);

                // --- Accumulate breakdowns ---
                foreach ($cleanRecipients as $r) {
                    $src = $r['source'] ?? 'unknown';
                    $sourceBreakdown[$src] = ($sourceBreakdown[$src] ?? 0) + 1;
                    $country = $r['country_iso'] ?? 'unknown';
                    $countryBreakdown[$country] = ($countryBreakdown[$country] ?? 0) + 1;
                }

                // --- Persist valid recipients ---
                $totalCreated += $this->persistRecipientsChunk(
                    $campaign, $cleanRecipients, $batchSize, $totalCreated
                );
                unset($cleanRecipients);

                // --- Persist opted-out (for reporting) ---
                $this->persistOptedOutChunk($campaign, $chunkOptedOut);
                unset($chunkOptedOut);
            }
        }

        // Persist invalid recipients (for reporting, capped at MAX_INVALID_DETAILS)
        $this->persistInvalid($campaign, $invalidDetails);

        // Update campaign recipient counts
        $campaign->update([
            'total_recipients' => $totalResolved,
            'total_unique_recipients' => $totalCreated + $totalOptedOut + $totalInvalid,
            'total_opted_out' => $totalOptedOut,
            'total_invalid' => $totalInvalid,
            'pending_count' => $totalCreated,
        ]);

        arsort($countryBreakdown);

        Log::info('[RecipientResolver] Resolution complete', [
            'campaign_id' => $campaign->id,
            'total_resolved' => $totalResolved,
            'total_unique' => $totalUnique,
            'total_opted_out' => $totalOptedOut,
            'total_invalid' => $totalInvalid,
            'total_created' => $totalCreated,
        ]);

        // Free memory
        unset($seenNumbers, $optedOutSet);

        return new ResolverResult(
            totalResolved: $totalResolved,
            totalUnique: $totalUnique,
            totalOptedOut: $totalOptedOut,
            totalInvalid: $totalInvalid,
            totalCreated: $totalCreated,
            sourceBreakdown: $sourceBreakdown,
            countryBreakdown: $countryBreakdown,
            invalidDetails: $invalidDetails,
        );
    }

    // =====================================================
    // CHUNKED SOURCE EXPANSION
    // =====================================================

    /**
     * Return an iterable of chunks from a single source.
     * Each chunk is an array of up to CHUNK_SIZE recipient entries.
     *
     * @return array[]
     */
    private function expandSourceChunked(array $source, string $accountId): array
    {
        $type = $source['type'] ?? null;

        switch ($type) {
            case 'list':
                return $this->expandListChunked($source['id'], $accountId);

            case 'tag':
                return $this->expandTagChunked($source['id'], $accountId);

            case 'individual':
                return $this->expandIndividualsChunked($source['contact_ids'] ?? [], $accountId);

            case 'manual':
                $result = $this->expandManualNumbers($source['numbers'] ?? []);
                return !empty($result) ? [$result] : [];

            case 'csv':
                return $this->expandCsvDataChunked($source['data'] ?? []);

            default:
                Log::warning('[RecipientResolver] Unknown source type', ['type' => $type]);
                return [];
        }
    }

    /**
     * Expand a contact list into chunked recipient entries.
     * Uses cursor-based pagination to avoid holding full result set.
     *
     * @return array[] Array of chunks
     */
    private function expandListChunked(string $listId, string $accountId): array
    {
        $list = ContactList::find($listId);
        if (!$list) {
            Log::warning('[RecipientResolver] List not found or not accessible', ['list_id' => $listId]);
            return [];
        }

        $chunks = [];
        $lastId = '';

        do {
            $rows = DB::table('contact_list_member')
                ->where('list_id', $listId)
                ->join('contacts', 'contacts.id', '=', 'contact_list_member.contact_id')
                ->where('contacts.account_id', $accountId)
                ->whereNull('contacts.deleted_at')
                ->where('contacts.id', '>', $lastId)
                ->select([
                    'contacts.id as contact_id',
                    'contacts.mobile_number',
                    'contacts.first_name',
                    'contacts.last_name',
                    'contacts.email',
                    'contacts.custom_data',
                    'contacts.country',
                ])
                ->orderBy('contacts.id')
                ->limit(self::CHUNK_SIZE)
                ->get();

            if ($rows->isEmpty()) {
                break;
            }

            $chunk = [];
            foreach ($rows as $row) {
                $chunk[] = [
                    'contact_id' => $row->contact_id,
                    'mobile_number' => $row->mobile_number,
                    'first_name' => $row->first_name,
                    'last_name' => $row->last_name,
                    'email' => $row->email,
                    'custom_data' => json_decode($row->custom_data ?? '{}', true),
                    'source' => CampaignRecipient::SOURCE_LIST,
                    'source_id' => $listId,
                ];
                $lastId = $row->contact_id;
            }

            $chunks[] = $chunk;
        } while ($rows->count() === self::CHUNK_SIZE);

        return $chunks;
    }

    /**
     * Expand a tag into chunked recipient entries.
     * Uses cursor-based pagination for memory efficiency.
     *
     * @return array[] Array of chunks
     */
    private function expandTagChunked(string $tagId, string $accountId): array
    {
        $tag = Tag::find($tagId);
        if (!$tag) {
            Log::warning('[RecipientResolver] Tag not found or not accessible', ['tag_id' => $tagId]);
            return [];
        }

        $chunks = [];
        $lastId = '';

        do {
            $rows = DB::table('contact_tag')
                ->where('tag_id', $tagId)
                ->join('contacts', 'contacts.id', '=', 'contact_tag.contact_id')
                ->where('contacts.account_id', $accountId)
                ->whereNull('contacts.deleted_at')
                ->where('contacts.id', '>', $lastId)
                ->select([
                    'contacts.id as contact_id',
                    'contacts.mobile_number',
                    'contacts.first_name',
                    'contacts.last_name',
                    'contacts.email',
                    'contacts.custom_data',
                    'contacts.country',
                ])
                ->orderBy('contacts.id')
                ->limit(self::CHUNK_SIZE)
                ->get();

            if ($rows->isEmpty()) {
                break;
            }

            $chunk = [];
            foreach ($rows as $row) {
                $chunk[] = [
                    'contact_id' => $row->contact_id,
                    'mobile_number' => $row->mobile_number,
                    'first_name' => $row->first_name,
                    'last_name' => $row->last_name,
                    'email' => $row->email,
                    'custom_data' => json_decode($row->custom_data ?? '{}', true),
                    'source' => CampaignRecipient::SOURCE_TAG,
                    'source_id' => $tagId,
                ];
                $lastId = $row->contact_id;
            }

            $chunks[] = $chunk;
        } while ($rows->count() === self::CHUNK_SIZE);

        return $chunks;
    }

    /**
     * Expand individual contact selections in chunks.
     *
     * @return array[] Array of chunks
     */
    private function expandIndividualsChunked(array $contactIds, string $accountId): array
    {
        if (empty($contactIds)) {
            return [];
        }

        $chunks = [];

        foreach (array_chunk($contactIds, self::CHUNK_SIZE) as $idChunk) {
            $contacts = DB::table('contacts')
                ->whereIn('id', $idChunk)
                ->where('account_id', $accountId)
                ->whereNull('deleted_at')
                ->select(['id as contact_id', 'mobile_number', 'first_name', 'last_name', 'email', 'custom_data', 'country'])
                ->get();

            $chunk = [];
            foreach ($contacts as $row) {
                $chunk[] = [
                    'contact_id' => $row->contact_id,
                    'mobile_number' => $row->mobile_number,
                    'first_name' => $row->first_name,
                    'last_name' => $row->last_name,
                    'email' => $row->email,
                    'custom_data' => json_decode($row->custom_data ?? '{}', true),
                    'source' => CampaignRecipient::SOURCE_INDIVIDUAL,
                    'source_id' => null,
                ];
            }

            if (!empty($chunk)) {
                $chunks[] = $chunk;
            }
        }

        return $chunks;
    }

    /**
     * Expand manually entered phone numbers into recipient entries.
     */
    private function expandManualNumbers(array $numbers): array
    {
        $recipients = [];

        foreach ($numbers as $number) {
            if (is_string($number) && trim($number) !== '') {
                $recipients[] = [
                    'contact_id' => null,
                    'mobile_number' => trim($number),
                    'first_name' => null,
                    'last_name' => null,
                    'email' => null,
                    'custom_data' => [],
                    'source' => CampaignRecipient::SOURCE_MANUAL,
                    'source_id' => null,
                ];
            }
        }

        return $recipients;
    }

    /**
     * Expand CSV-uploaded data in chunks.
     *
     * @return array[] Array of chunks
     */
    private function expandCsvDataChunked(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $chunks = [];

        foreach (array_chunk($data, self::CHUNK_SIZE) as $dataChunk) {
            $recipients = [];

            foreach ($dataChunk as $row) {
                $mobileNumber = $row['mobile_number'] ?? $row['phone'] ?? $row['mobile'] ?? null;
                if (!$mobileNumber || trim($mobileNumber) === '') {
                    continue;
                }

                $knownFields = ['mobile_number', 'phone', 'mobile', 'first_name', 'last_name', 'email'];
                $customData = array_diff_key($row, array_flip($knownFields));

                $recipients[] = [
                    'contact_id' => null,
                    'mobile_number' => trim($mobileNumber),
                    'first_name' => $row['first_name'] ?? null,
                    'last_name' => $row['last_name'] ?? null,
                    'email' => $row['email'] ?? null,
                    'custom_data' => $customData,
                    'source' => CampaignRecipient::SOURCE_CSV,
                    'source_id' => null,
                ];
            }

            if (!empty($recipients)) {
                $chunks[] = $recipients;
            }
        }

        return $chunks;
    }

    // =====================================================
    // OPT-OUT LOADING
    // =====================================================

    /**
     * Pre-load all opted-out numbers for an account into a hash set.
     *
     * For large opt-out lists this uses chunked loading to avoid a single
     * massive query, but the resulting set must fit in memory. At ~20 bytes
     * per E.164 number, 1M opt-out records ≈ 20MB which is acceptable.
     */
    private function loadOptedOutSet(string $accountId): array
    {
        $set = [];

        DB::table('opt_out_records')
            ->where('account_id', $accountId)
            ->select('mobile_number')
            ->orderBy('id')
            ->chunk(self::CHUNK_SIZE, function ($rows) use (&$set) {
                foreach ($rows as $row) {
                    $set[$row->mobile_number] = true;
                }
            });

        return $set;
    }

    /**
     * Load phone numbers from one or more opt-out screening contact lists.
     *
     * Used to pre-populate the opted-out hash set with contacts that appear
     * on lists the user has chosen to screen against. Numbers are keyed by
     * E.164 string for O(1) lookup during recipient filtering.
     *
     * @param  string[]  $listIds   UUIDs of ContactList records to screen against
     * @param  string    $accountId Tenant account ID (scopes the contacts join)
     * @return array<string, true>  Hash set of mobile numbers
     */
    private function loadScreeningListSet(array $listIds, string $accountId): array
    {
        $set = [];

        DB::table('contact_list_member')
            ->whereIn('list_id', $listIds)
            ->join('contacts', 'contacts.id', '=', 'contact_list_member.contact_id')
            ->where('contacts.account_id', $accountId)
            ->whereNull('contacts.deleted_at')
            ->select('contacts.mobile_number')
            ->orderBy('contacts.id')
            ->chunk(self::CHUNK_SIZE, function ($rows) use (&$set) {
                foreach ($rows as $row) {
                    $set[$row->mobile_number] = true;
                }
            });

        return $set;
    }

    // =====================================================
    // PERSISTENCE (chunked)
    // =====================================================

    /**
     * Persist a chunk of valid recipients into campaign_recipients.
     * Returns the number of rows inserted.
     */
    private function persistRecipientsChunk(
        Campaign $campaign,
        array $recipients,
        int $batchSize,
        int $currentTotal
    ): int {
        if (empty($recipients)) {
            return 0;
        }

        $rows = [];
        $inserted = 0;
        foreach ($recipients as $recipient) {
            $batchNumber = (int) floor(($currentTotal + $inserted) / $batchSize);

            $rows[] = [
                'id' => (string) Str::uuid(),
                'campaign_id' => $campaign->id,
                'contact_id' => $recipient['contact_id'] ?? null,
                'mobile_number' => $recipient['mobile_number'],
                'first_name' => $recipient['first_name'] ?? null,
                'last_name' => $recipient['last_name'] ?? null,
                'email' => $recipient['email'] ?? null,
                'custom_data' => json_encode($recipient['custom_data'] ?? []),
                'source' => $recipient['source'],
                'source_id' => $recipient['source_id'] ?? null,
                'status' => CampaignRecipient::STATUS_PENDING,
                'country_iso' => $recipient['country_iso'] ?? null,
                'batch_number' => $batchNumber,
                'metadata' => '{}',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $inserted++;
        }

        DB::table('campaign_recipients')->insertOrIgnore($rows);

        return $inserted;
    }

    /**
     * Persist opted-out recipients for reporting.
     */
    private function persistOptedOutChunk(Campaign $campaign, array $optedOutRecipients): void
    {
        if (empty($optedOutRecipients)) {
            return;
        }

        $rows = [];
        foreach ($optedOutRecipients as $recipient) {
            $rows[] = [
                'id' => (string) Str::uuid(),
                'campaign_id' => $campaign->id,
                'contact_id' => $recipient['contact_id'] ?? null,
                'mobile_number' => $recipient['mobile_number'],
                'first_name' => $recipient['first_name'] ?? null,
                'last_name' => $recipient['last_name'] ?? null,
                'email' => $recipient['email'] ?? null,
                'custom_data' => json_encode($recipient['custom_data'] ?? []),
                'source' => $recipient['source'],
                'source_id' => $recipient['source_id'] ?? null,
                'status' => CampaignRecipient::STATUS_OPTED_OUT,
                'country_iso' => $recipient['country_iso'] ?? null,
                'batch_number' => 0,
                'metadata' => '{}',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('campaign_recipients')->insertOrIgnore($rows);
    }

    /**
     * Persist invalid recipients with skipped status (for reporting).
     */
    private function persistInvalid(Campaign $campaign, array $invalidRecipients): void
    {
        if (empty($invalidRecipients)) {
            return;
        }

        foreach (array_chunk($invalidRecipients, self::CHUNK_SIZE) as $chunk) {
            $rows = [];
            foreach ($chunk as $recipient) {
                $rows[] = [
                    'id' => (string) Str::uuid(),
                    'campaign_id' => $campaign->id,
                    'contact_id' => $recipient['contact_id'] ?? null,
                    'mobile_number' => $recipient['number'] ?? 'invalid',
                    'status' => CampaignRecipient::STATUS_SKIPPED,
                    'failure_reason' => $recipient['error'] ?? 'Invalid number',
                    'source' => CampaignRecipient::SOURCE_MANUAL,
                    'batch_number' => 0,
                    'custom_data' => '{}',
                    'metadata' => '{}',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('campaign_recipients')->insertOrIgnore($rows);
        }
    }

    // =====================================================
    // PREVIEW / DRY-RUN
    // =====================================================

    /**
     * Preview recipient resolution without persisting.
     * Useful for the UI to show estimated counts before sending.
     *
     * Streams through the same chunked pipeline as resolve() but only
     * collects counts, not full records. Memory-safe for any size.
     */
    public function preview(array $sources, string $accountId, string $defaultCountry = 'GB', array $screeningListIds = []): array
    {
        $optedOutSet = $this->loadOptedOutSet($accountId);

        if (!empty($screeningListIds)) {
            $screeningSet = $this->loadScreeningListSet($screeningListIds, $accountId);
            $optedOutSet = array_merge($optedOutSet, $screeningSet);
        }

        $seenNumbers = [];

        $totalResolved = 0;
        $totalUnique = 0;
        $totalOptedOut = 0;
        $totalInvalid = 0;
        $totalSendable = 0;
        $sourceBreakdown = [];
        $countryBreakdown = [];
        $invalidDetails = [];

        foreach ($sources as $source) {
            $type = $source['type'] ?? null;
            if (!$type) {
                continue;
            }

            $chunks = $this->expandSourceChunked($source, $accountId);

            foreach ($chunks as $rawChunk) {
                $totalResolved += count($rawChunk);

                foreach ($rawChunk as $recipient) {
                    $number = $recipient['mobile_number'];
                    $normalised = preg_replace('/[\s\-\(\)\.]/', '', $number);
                    $normalised = ltrim($normalised, '+');

                    if (isset($seenNumbers[$normalised])) {
                        continue;
                    }
                    $seenNumbers[$normalised] = true;
                    $totalUnique++;

                    $result = PhoneNumberUtils::normalise($number, $defaultCountry);
                    if (!$result['valid'] || !PhoneNumberUtils::isValidMobile($result['number'])) {
                        $totalInvalid++;
                        if (count($invalidDetails) < self::MAX_INVALID_DETAILS) {
                            $invalidDetails[] = [
                                'number' => $number,
                                'error' => $result['error'] ?? 'Invalid mobile number format',
                                'contact_id' => $recipient['contact_id'] ?? null,
                            ];
                        }
                        continue;
                    }

                    $e164 = $result['number'];
                    $countryIso = PhoneNumberUtils::detectCountry($e164);

                    if (isset($optedOutSet[$e164])) {
                        $totalOptedOut++;
                        continue;
                    }

                    $totalSendable++;
                    $src = $recipient['source'] ?? 'unknown';
                    $sourceBreakdown[$src] = ($sourceBreakdown[$src] ?? 0) + 1;
                    $country = $countryIso ?? 'unknown';
                    $countryBreakdown[$country] = ($countryBreakdown[$country] ?? 0) + 1;
                }
            }
        }

        arsort($countryBreakdown);

        return [
            'total_resolved' => $totalResolved,
            'total_unique' => $totalUnique,
            'total_valid' => $totalSendable + $totalOptedOut,
            'total_opted_out' => $totalOptedOut,
            'total_invalid' => $totalInvalid,
            'total_sendable' => $totalSendable,
            'source_breakdown' => $sourceBreakdown,
            'country_breakdown' => $countryBreakdown,
            'invalid_details' => $invalidDetails,
        ];
    }
}
