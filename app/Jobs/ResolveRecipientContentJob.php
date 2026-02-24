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

        // Slow path: placeholders present — resolve per-recipient
        $processed = 0;

        DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->where('status', CampaignRecipient::STATUS_PENDING)
            ->whereNull('resolved_content')
            ->orderBy('id')
            ->chunk(2000, function ($recipients) use ($campaign, $messageContent, &$processed, $totalPending) {
                foreach ($recipients as $row) {
                    $recipient = new CampaignRecipient((array) $row);
                    $recipient->exists = true;
                    $recipient->id = $row->id;

                    $resolvedContent = $recipient->resolveContent($messageContent);
                    $encoding = MessageTemplate::detectEncoding($resolvedContent);
                    $segments = MessageTemplate::calculateSegments($resolvedContent, $encoding);

                    DB::table('campaign_recipients')
                        ->where('id', $row->id)
                        ->update([
                            'resolved_content' => $resolvedContent,
                            'segments' => $segments,
                            'encoding' => $encoding,
                        ]);

                    $processed++;
                }

                // Update progress after each chunk
                $progress = min(99, (int) round(($processed / $totalPending) * 100));
                $campaign->withoutGlobalScope('tenant')->where('id', $campaign->id)->update([
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
