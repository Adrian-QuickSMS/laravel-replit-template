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
 * Memory-safe: processes in chunks for campaigns with millions of recipients.
 */
class RecipientResolverService
{
    private const CHUNK_SIZE = 2000;
    private const MAX_INVALID_DETAILS = 100;

    /**
     * Resolve all recipients for a campaign and persist them to campaign_recipients.
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

        // Step 1: Expand all sources into raw recipient entries
        // Each entry: ['mobile_number' => ..., 'contact_id' => ..., 'first_name' => ..., etc., 'source' => ..., 'source_id' => ...]
        $rawRecipients = $this->expandSources($sources, $accountId);
        $totalResolved = count($rawRecipients);

        // Step 2: Deduplicate by mobile number (keep first occurrence)
        $uniqueRecipients = $this->deduplicate($rawRecipients);
        $totalUnique = count($uniqueRecipients);

        // Step 3: Validate and normalise phone numbers
        [$validRecipients, $invalidRecipients] = $this->validateNumbers($uniqueRecipients, $defaultCountry);
        $totalInvalid = count($invalidRecipients);
        $invalidDetails = array_slice($invalidRecipients, 0, self::MAX_INVALID_DETAILS);

        // Step 4: Detect country ISO for pricing
        $validRecipients = $this->enrichWithCountry($validRecipients);

        // Step 5: Filter opted-out numbers
        [$cleanRecipients, $optedOutRecipients] = $this->filterOptedOut($validRecipients, $accountId);
        $totalOptedOut = count($optedOutRecipients);

        // Step 6: Build breakdown statistics
        $sourceBreakdown = $this->buildSourceBreakdown($cleanRecipients);
        $countryBreakdown = $this->buildCountryBreakdown($cleanRecipients);

        // Step 7: Batch-insert campaign_recipients
        $totalCreated = $this->persistRecipients($campaign, $cleanRecipients);

        // Step 8: Persist opted-out recipients (marked as opted_out status)
        $this->persistOptedOut($campaign, $optedOutRecipients);

        // Step 9: Persist invalid recipients (marked as skipped status)
        $this->persistInvalid($campaign, $invalidRecipients);

        // Step 10: Update campaign recipient counts
        $campaign->update([
            'total_recipients' => $totalResolved,
            'total_unique_recipients' => $totalCreated + $totalOptedOut + $totalInvalid,
            'total_opted_out' => $totalOptedOut,
            'total_invalid' => $totalInvalid,
            'pending_count' => $totalCreated,
        ]);

        Log::info('[RecipientResolver] Resolution complete', [
            'campaign_id' => $campaign->id,
            'total_resolved' => $totalResolved,
            'total_unique' => $totalUnique,
            'total_opted_out' => $totalOptedOut,
            'total_invalid' => $totalInvalid,
            'total_created' => $totalCreated,
        ]);

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

    /**
     * Expand all recipient sources into a flat array of raw recipient entries.
     *
     * Supported source types:
     * - list: { type: "list", id: "uuid" }
     * - tag: { type: "tag", id: "uuid" }
     * - individual: { type: "individual", contact_ids: ["uuid", ...] }
     * - manual: { type: "manual", numbers: ["+447700900000", ...] }
     * - csv: { type: "csv", data: [{ mobile_number: ..., first_name: ..., ... }, ...] }
     */
    private function expandSources(array $sources, string $accountId): array
    {
        $recipients = [];

        foreach ($sources as $source) {
            $type = $source['type'] ?? null;
            if (!$type) {
                continue;
            }

            switch ($type) {
                case 'list':
                    $recipients = array_merge($recipients, $this->expandList($source['id'], $accountId));
                    break;

                case 'tag':
                    $recipients = array_merge($recipients, $this->expandTag($source['id'], $accountId));
                    break;

                case 'individual':
                    $recipients = array_merge($recipients, $this->expandIndividuals($source['contact_ids'] ?? [], $accountId));
                    break;

                case 'manual':
                    $recipients = array_merge($recipients, $this->expandManualNumbers($source['numbers'] ?? []));
                    break;

                case 'csv':
                    $recipients = array_merge($recipients, $this->expandCsvData($source['data'] ?? []));
                    break;

                default:
                    Log::warning('[RecipientResolver] Unknown source type', ['type' => $type]);
            }
        }

        return $recipients;
    }

    /**
     * Expand a contact list into recipient entries.
     * Uses chunked queries to handle large lists.
     */
    private function expandList(string $listId, string $accountId): array
    {
        $recipients = [];

        // Verify list belongs to account (via global scope)
        $list = ContactList::find($listId);
        if (!$list) {
            Log::warning('[RecipientResolver] List not found or not accessible', ['list_id' => $listId]);
            return [];
        }

        // Query contacts through the pivot table, chunked
        DB::table('contact_list_member')
            ->where('list_id', $listId)
            ->join('contacts', 'contacts.id', '=', 'contact_list_member.contact_id')
            ->where('contacts.account_id', $accountId)
            ->whereNull('contacts.deleted_at')
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
            ->chunk(self::CHUNK_SIZE, function ($rows) use (&$recipients, $listId) {
                foreach ($rows as $row) {
                    $recipients[] = [
                        'contact_id' => $row->contact_id,
                        'mobile_number' => $row->mobile_number,
                        'first_name' => $row->first_name,
                        'last_name' => $row->last_name,
                        'email' => $row->email,
                        'custom_data' => json_decode($row->custom_data ?? '{}', true),
                        'source' => CampaignRecipient::SOURCE_LIST,
                        'source_id' => $listId,
                    ];
                }
            });

        return $recipients;
    }

    /**
     * Expand a tag into recipient entries.
     * Finds all contacts with this tag.
     */
    private function expandTag(string $tagId, string $accountId): array
    {
        $recipients = [];

        $tag = Tag::find($tagId);
        if (!$tag) {
            Log::warning('[RecipientResolver] Tag not found or not accessible', ['tag_id' => $tagId]);
            return [];
        }

        DB::table('contact_tag')
            ->where('tag_id', $tagId)
            ->join('contacts', 'contacts.id', '=', 'contact_tag.contact_id')
            ->where('contacts.account_id', $accountId)
            ->whereNull('contacts.deleted_at')
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
            ->chunk(self::CHUNK_SIZE, function ($rows) use (&$recipients, $tagId) {
                foreach ($rows as $row) {
                    $recipients[] = [
                        'contact_id' => $row->contact_id,
                        'mobile_number' => $row->mobile_number,
                        'first_name' => $row->first_name,
                        'last_name' => $row->last_name,
                        'email' => $row->email,
                        'custom_data' => json_decode($row->custom_data ?? '{}', true),
                        'source' => CampaignRecipient::SOURCE_TAG,
                        'source_id' => $tagId,
                    ];
                }
            });

        return $recipients;
    }

    /**
     * Expand individual contact selections.
     */
    private function expandIndividuals(array $contactIds, string $accountId): array
    {
        if (empty($contactIds)) {
            return [];
        }

        $recipients = [];

        // Process in chunks to avoid large IN clauses
        foreach (array_chunk($contactIds, self::CHUNK_SIZE) as $chunk) {
            $contacts = DB::table('contacts')
                ->whereIn('id', $chunk)
                ->where('account_id', $accountId)
                ->whereNull('deleted_at')
                ->select(['id as contact_id', 'mobile_number', 'first_name', 'last_name', 'email', 'custom_data', 'country'])
                ->get();

            foreach ($contacts as $row) {
                $recipients[] = [
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
        }

        return $recipients;
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
     * Expand CSV-uploaded data into recipient entries.
     *
     * Each CSV row should have at minimum: mobile_number
     * Optional: first_name, last_name, email, and any custom_data fields.
     */
    private function expandCsvData(array $data): array
    {
        $recipients = [];

        foreach ($data as $row) {
            $mobileNumber = $row['mobile_number'] ?? $row['phone'] ?? $row['mobile'] ?? null;
            if (!$mobileNumber || trim($mobileNumber) === '') {
                continue;
            }

            // Extract known fields; everything else goes into custom_data
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

        return $recipients;
    }

    /**
     * Deduplicate recipients by mobile number.
     * Keeps the first occurrence (preserving contact_id linkage when available).
     */
    private function deduplicate(array $recipients): array
    {
        $seen = [];
        $unique = [];

        foreach ($recipients as $recipient) {
            $number = $recipient['mobile_number'];

            // Normalise for dedup comparison (strip formatting)
            $normalised = preg_replace('/[\s\-\(\)\.]/', '', $number);
            $normalised = ltrim($normalised, '+');

            if (isset($seen[$normalised])) {
                continue;
            }

            $seen[$normalised] = true;
            $unique[] = $recipient;
        }

        return $unique;
    }

    /**
     * Validate and normalise phone numbers to E.164 format.
     *
     * @return array{0: array, 1: array} [valid, invalid]
     */
    private function validateNumbers(array $recipients, string $defaultCountry): array
    {
        $valid = [];
        $invalid = [];

        foreach ($recipients as $recipient) {
            $result = PhoneNumberUtils::normalise($recipient['mobile_number'], $defaultCountry);

            if ($result['valid'] && PhoneNumberUtils::isValidMobile($result['number'])) {
                $recipient['mobile_number'] = $result['number'];
                $valid[] = $recipient;
            } else {
                $invalid[] = [
                    'number' => $recipient['mobile_number'],
                    'error' => $result['error'] ?? 'Invalid mobile number format',
                    'contact_id' => $recipient['contact_id'] ?? null,
                ];
            }
        }

        return [$valid, $invalid];
    }

    /**
     * Enrich recipients with country ISO detected from their phone number.
     */
    private function enrichWithCountry(array $recipients): array
    {
        foreach ($recipients as &$recipient) {
            $recipient['country_iso'] = PhoneNumberUtils::detectCountry($recipient['mobile_number']);
        }

        return $recipients;
    }

    /**
     * Filter out opted-out numbers.
     *
     * Checks the opt_out_records table for the account. A number is suppressed
     * if it appears in ANY opt-out list for this account (including the master list).
     *
     * @return array{0: array, 1: array} [clean, optedOut]
     */
    private function filterOptedOut(array $recipients, string $accountId): array
    {
        if (empty($recipients)) {
            return [[], []];
        }

        $allOptedOutNormalised = [];
        $optOutRecords = DB::table('opt_out_records')
            ->where('account_id', $accountId)
            ->pluck('mobile_number')
            ->toArray();

        foreach ($optOutRecords as $num) {
            $allOptedOutNormalised[$this->normaliseForComparison($num)] = true;
        }

        $clean = [];
        $optedOut = [];

        foreach ($recipients as $recipient) {
            $normalised = $this->normaliseForComparison($recipient['mobile_number']);
            if (isset($allOptedOutNormalised[$normalised])) {
                $optedOut[] = $recipient;
            } else {
                $clean[] = $recipient;
            }
        }

        return [$clean, $optedOut];
    }

    private function normaliseForComparison(string $number): string
    {
        $number = preg_replace('/[\s\-\(\)]/', '', $number);
        $number = ltrim($number, '+');

        if (preg_match('/^0[1-9]/', $number)) {
            $number = '44' . substr($number, 1);
        }

        return $number;
    }

    /**
     * Batch-insert valid recipients into campaign_recipients table.
     * Assigns batch numbers for chunked processing during send.
     */
    private function persistRecipients(Campaign $campaign, array $recipients): int
    {
        if (empty($recipients)) {
            return 0;
        }

        $batchSize = $campaign->batch_size ?: Campaign::DEFAULT_BATCH_SIZE;
        $totalInserted = 0;

        foreach (array_chunk($recipients, self::CHUNK_SIZE) as $chunk) {
            $rows = [];
            foreach ($chunk as $recipient) {
                $batchNumber = (int) floor($totalInserted / $batchSize);

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

                $totalInserted++;
            }

            // Use insertOrIgnore to handle the unique constraint (campaign_id, mobile_number)
            // This catches any edge-case duplicates that survive dedup (e.g., +447700900000 vs 447700900000)
            DB::table('campaign_recipients')->insertOrIgnore($rows);
        }

        return $totalInserted;
    }

    /**
     * Persist opted-out recipients with opted_out status (for reporting).
     */
    private function persistOptedOut(Campaign $campaign, array $optedOutRecipients): void
    {
        if (empty($optedOutRecipients)) {
            return;
        }

        foreach (array_chunk($optedOutRecipients, self::CHUNK_SIZE) as $chunk) {
            $rows = [];
            foreach ($chunk as $recipient) {
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
    // STATISTICS HELPERS
    // =====================================================

    /**
     * Build a source -> count breakdown.
     */
    private function buildSourceBreakdown(array $recipients): array
    {
        $breakdown = [];
        foreach ($recipients as $r) {
            $source = $r['source'] ?? 'unknown';
            $breakdown[$source] = ($breakdown[$source] ?? 0) + 1;
        }
        return $breakdown;
    }

    /**
     * Build a country_iso -> count breakdown.
     */
    private function buildCountryBreakdown(array $recipients): array
    {
        $breakdown = [];
        foreach ($recipients as $r) {
            $country = $r['country_iso'] ?? 'unknown';
            $breakdown[$country] = ($breakdown[$country] ?? 0) + 1;
        }
        arsort($breakdown);
        return $breakdown;
    }

    // =====================================================
    // PREVIEW / DRY-RUN
    // =====================================================

    /**
     * Preview recipient resolution without persisting.
     * Useful for the UI to show estimated counts before sending.
     */
    public function preview(array $sources, string $accountId, string $defaultCountry = 'GB'): array
    {
        $rawRecipients = $this->expandSources($sources, $accountId);
        $uniqueRecipients = $this->deduplicate($rawRecipients);
        [$validRecipients, $invalidRecipients] = $this->validateNumbers($uniqueRecipients, $defaultCountry);
        $validRecipients = $this->enrichWithCountry($validRecipients);
        [$cleanRecipients, $optedOutRecipients] = $this->filterOptedOut($validRecipients, $accountId);

        return [
            'total_resolved' => count($rawRecipients),
            'total_unique' => count($uniqueRecipients),
            'total_valid' => count($validRecipients),
            'total_opted_out' => count($optedOutRecipients),
            'total_invalid' => count($invalidRecipients),
            'total_sendable' => count($cleanRecipients),
            'source_breakdown' => $this->buildSourceBreakdown($cleanRecipients),
            'country_breakdown' => $this->buildCountryBreakdown($cleanRecipients),
            'invalid_details' => array_slice($invalidRecipients, 0, self::MAX_INVALID_DETAILS),
        ];
    }
}
