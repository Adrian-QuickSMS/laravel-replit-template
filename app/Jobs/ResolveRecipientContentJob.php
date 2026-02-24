<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\MessageTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Resolves merge fields for all pending recipients in a campaign.
 *
 * For each recipient, this job:
 * 1. Substitutes {{placeholder}} tokens with snapshotted contact data
 * 2. Detects the resulting encoding (GSM-7 or Unicode)
 * 3. Calculates the actual segment count for the resolved content
 * 4. Stores resolved_content, segments, and encoding per-recipient
 *
 * This enables accurate per-recipient cost estimation before the user
 * confirms and sends the campaign.
 *
 * Queue: campaigns | Timeout: 30 min | Tries: 1
 */
class ResolveRecipientContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 1800; // 30 minutes for large campaigns

    public function __construct(
        public readonly string $campaignId,
    ) {
        $this->onQueue('campaigns');
    }

    public function handle(): void
    {
        // Use withoutGlobalScope since this runs in a queue worker (no session context)
        $campaign = Campaign::withoutGlobalScope('tenant')->findOrFail($this->campaignId);

        Log::info('[ResolveRecipientContent] Starting content resolution', [
            'campaign_id' => $campaign->id,
            'account_id' => $campaign->account_id,
        ]);

        $campaign->update([
            'preparation_status' => 'preparing',
            'preparation_progress' => 0,
            'preparation_error' => null,
        ]);

        $messageContent = $campaign->message_content;

        // No content to resolve (e.g. RCS-only campaign)
        if (!$messageContent) {
            $campaign->update([
                'preparation_status' => 'ready',
                'preparation_progress' => 100,
                'content_resolved_at' => now(),
            ]);
            return;
        }

        $placeholders = MessageTemplate::extractPlaceholders($messageContent);

        $totalPending = DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->where('status', CampaignRecipient::STATUS_PENDING)
            ->count();

        if ($totalPending === 0) {
            $campaign->update([
                'preparation_status' => 'ready',
                'preparation_progress' => 100,
                'content_resolved_at' => now(),
            ]);
            return;
        }

        // Fast path: no placeholders — every recipient gets identical content
        if (empty($placeholders)) {
            $encoding = MessageTemplate::detectEncoding($messageContent);
            $segments = MessageTemplate::calculateSegments($messageContent, $encoding);

            DB::table('campaign_recipients')
                ->where('campaign_id', $campaign->id)
                ->where('status', CampaignRecipient::STATUS_PENDING)
                ->update([
                    'resolved_content' => $messageContent,
                    'segments' => $segments,
                    'encoding' => $encoding,
                ]);

            Log::info('[ResolveRecipientContent] No placeholders — bulk updated', [
                'campaign_id' => $campaign->id,
                'encoding' => $encoding,
                'segments' => $segments,
                'recipients' => $totalPending,
            ]);

            $campaign->update([
                'preparation_status' => 'ready',
                'preparation_progress' => 100,
                'content_resolved_at' => now(),
            ]);
            return;
        }

        // Slow path: placeholders present — resolve per-recipient using batch UPDATEs.
        // Instead of 500K individual UPDATE queries (~17 min), we collect resolved
        // content in batches of 500 and flush with a single multi-row UPDATE using
        // PostgreSQL's UPDATE FROM VALUES pattern (~60x faster).
        $processed = 0;
        $batchUpdateSize = 500;

        DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->where('status', CampaignRecipient::STATUS_PENDING)
            ->whereNull('resolved_content')
            ->orderBy('id')
            ->chunk(2000, function ($recipients) use ($campaign, $messageContent, &$processed, $totalPending, $batchUpdateSize) {
                $pendingUpdates = [];

                foreach ($recipients as $row) {
                    $recipient = new CampaignRecipient((array) $row);
                    $recipient->exists = true;
                    $recipient->id = $row->id;

                    $resolvedContent = $recipient->resolveContent($messageContent);
                    $encoding = MessageTemplate::detectEncoding($resolvedContent);
                    $segments = MessageTemplate::calculateSegments($resolvedContent, $encoding);

                    $pendingUpdates[] = [
                        'id' => $row->id,
                        'resolved_content' => $resolvedContent,
                        'segments' => $segments,
                        'encoding' => $encoding,
                    ];

                    $processed++;

                    // Flush batch when buffer is full
                    if (count($pendingUpdates) >= $batchUpdateSize) {
                        $this->flushBatchUpdate($pendingUpdates);
                        $pendingUpdates = [];
                    }
                }

                // Flush remaining rows in this chunk
                if (!empty($pendingUpdates)) {
                    $this->flushBatchUpdate($pendingUpdates);
                }

                // Update progress after each chunk
                $progress = min(99, (int) round(($processed / $totalPending) * 100));
                Campaign::withoutGlobalScope('tenant')->where('id', $campaign->id)->update([
                    'preparation_progress' => $progress,
                ]);
            });

        Log::info('[ResolveRecipientContent] Content resolution complete', [
            'campaign_id' => $campaign->id,
            'processed' => $processed,
            'total' => $totalPending,
        ]);

        Campaign::withoutGlobalScope('tenant')->where('id', $campaign->id)->update([
            'preparation_status' => 'ready',
            'preparation_progress' => 100,
            'content_resolved_at' => now(),
        ]);
    }

    /**
     * Flush a batch of resolved content updates in a single SQL statement.
     *
     * Uses PostgreSQL's UPDATE FROM VALUES pattern:
     *   UPDATE campaign_recipients AS cr SET ...
     *   FROM (VALUES (...), (...), ...) AS v(id, resolved_content, segments, encoding)
     *   WHERE cr.id = v.id::uuid
     *
     * This reduces 500 individual UPDATE round-trips to 1, cutting the total
     * resolution time for 500K recipients from ~17 minutes to ~15-30 seconds.
     */
    private function flushBatchUpdate(array $updates): void
    {
        if (empty($updates)) {
            return;
        }

        $values = [];
        $bindings = [];

        foreach ($updates as $update) {
            $values[] = '(?, ?, ?, ?)';
            $bindings[] = $update['id'];
            $bindings[] = $update['resolved_content'];
            $bindings[] = $update['segments'];
            $bindings[] = $update['encoding'];
        }

        $valuesSql = implode(', ', $values);

        DB::statement("
            UPDATE campaign_recipients AS cr
            SET resolved_content = v.resolved_content,
                segments = v.segments::integer,
                encoding = v.encoding
            FROM (VALUES {$valuesSql}) AS v(id, resolved_content, segments, encoding)
            WHERE cr.id = v.id::uuid
        ", $bindings);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[ResolveRecipientContent] Job failed', [
            'campaign_id' => $this->campaignId,
            'error' => $exception->getMessage(),
        ]);

        Campaign::withoutGlobalScope('tenant')
            ->where('id', $this->campaignId)
            ->update([
                'preparation_status' => 'failed',
                'preparation_progress' => 0,
                'preparation_error' => substr($exception->getMessage(), 0, 1000),
            ]);
    }
}
