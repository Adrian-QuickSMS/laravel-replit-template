<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Services\Campaign\CampaignService;
use App\Services\Campaign\DeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * ProcessCampaignBatch — processes a batch of campaign recipients for delivery.
 *
 * One job per batch. Each batch processes `batch_size` recipients (default 1000).
 * Respects send_rate throttling, campaign pause/cancel state, and retry logic.
 *
 * Dispatched by:
 * - CampaignService::sendNow() (all batches queued at once)
 * - ScheduledCampaignDispatcher (for scheduled campaigns)
 *
 * Queue: 'campaigns' (dedicated queue for message sends)
 * Timeout: 10 minutes per batch (300 msg/sec * 1000 = ~3s, plus headroom)
 */
class ProcessCampaignBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600; // 10 minutes
    public int $maxExceptions = 2;

    public function __construct(
        public readonly string $campaignId,
        public readonly int $batchNumber,
    ) {
        $this->onQueue('campaigns');
    }

    public function handle(DeliveryService $deliveryService, CampaignService $campaignService): void
    {
        $campaign = Campaign::withoutGlobalScope('tenant')->find($this->campaignId);

        if (!$campaign) {
            Log::error('[ProcessCampaignBatch] Campaign not found', [
                'campaign_id' => $this->campaignId,
            ]);
            return;
        }

        // Check campaign state — only process if queued or sending
        if (!in_array($campaign->status, [Campaign::STATUS_QUEUED, Campaign::STATUS_SENDING])) {
            Log::info('[ProcessCampaignBatch] Campaign not in sendable state, skipping batch', [
                'campaign_id' => $campaign->id,
                'status' => $campaign->status,
                'batch' => $this->batchNumber,
            ]);
            return;
        }

        // Transition to sending on first batch
        if ($campaign->status === Campaign::STATUS_QUEUED) {
            $campaign->transitionTo(Campaign::STATUS_SENDING);
        }

        Log::info('[ProcessCampaignBatch] Processing batch', [
            'campaign_id' => $campaign->id,
            'batch' => $this->batchNumber,
        ]);

        // Get pending recipients for this batch
        $recipients = CampaignRecipient::where('campaign_id', $campaign->id)
            ->where('batch_number', $this->batchNumber)
            ->where('status', CampaignRecipient::STATUS_PENDING)
            ->orderBy('id')
            ->get();

        if ($recipients->isEmpty()) {
            Log::info('[ProcessCampaignBatch] No pending recipients in batch', [
                'campaign_id' => $campaign->id,
                'batch' => $this->batchNumber,
            ]);
            $this->checkCampaignCompletion($campaign, $campaignService);
            return;
        }

        $sendRate = $campaign->send_rate ?: Campaign::DEFAULT_SEND_RATE;
        $rateLimitKey = "campaign-send-rate:{$campaign->id}";
        $processedCount = 0;
        $failedCount = 0;

        foreach ($recipients as $recipient) {
            // Re-check campaign state periodically (every 100 messages)
            if ($processedCount > 0 && $processedCount % 100 === 0) {
                $freshCampaign = Campaign::withoutGlobalScope('tenant')->find($campaign->id);
                if ($freshCampaign && in_array($freshCampaign->status, [
                    Campaign::STATUS_PAUSED,
                    Campaign::STATUS_CANCELLED,
                ])) {
                    Log::info('[ProcessCampaignBatch] Campaign state changed mid-batch, stopping', [
                        'campaign_id' => $campaign->id,
                        'status' => $freshCampaign->status,
                        'processed_in_batch' => $processedCount,
                    ]);
                    return;
                }
            }

            // Rate limiting: respect send_rate (messages per second)
            if ($sendRate > 0) {
                $this->throttle($rateLimitKey, $sendRate);
            }

            // Mark as queued, then send
            $recipient->markQueued();

            $success = $deliveryService->sendRecipient($campaign, $recipient);

            $processedCount++;
            if (!$success) {
                $failedCount++;
            }
        }

        Log::info('[ProcessCampaignBatch] Batch complete', [
            'campaign_id' => $campaign->id,
            'batch' => $this->batchNumber,
            'processed' => $processedCount,
            'failed' => $failedCount,
        ]);

        // Check if this was the last batch
        $this->checkCampaignCompletion($campaign, $campaignService);
    }

    /**
     * Check if all batches are done and complete the campaign.
     */
    private function checkCampaignCompletion(Campaign $campaign, CampaignService $campaignService): void
    {
        $remainingPending = DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->whereIn('status', [
                CampaignRecipient::STATUS_PENDING,
                CampaignRecipient::STATUS_QUEUED,
            ])
            ->count();

        // Also check for messages still awaiting DLR (sent but not yet delivered/failed)
        $awaitingDlr = DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->where('status', CampaignRecipient::STATUS_SENT)
            ->count();

        if ($remainingPending === 0 && $awaitingDlr === 0) {
            $campaignService->complete($campaign);
        } elseif ($remainingPending === 0 && $awaitingDlr > 0) {
            Log::info('[ProcessCampaignBatch] All sent, awaiting DLRs', [
                'campaign_id' => $campaign->id,
                'awaiting_dlr' => $awaitingDlr,
            ]);
        }
    }

    /**
     * Simple rate throttle using microsleep.
     * Ensures we don't exceed $maxPerSecond messages per second.
     */
    private function throttle(string $key, int $maxPerSecond): void
    {
        $intervalMicroseconds = (int) (1_000_000 / $maxPerSecond);
        usleep($intervalMicroseconds);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[ProcessCampaignBatch] Job failed', [
            'campaign_id' => $this->campaignId,
            'batch' => $this->batchNumber,
            'error' => $exception->getMessage(),
        ]);
    }
}
