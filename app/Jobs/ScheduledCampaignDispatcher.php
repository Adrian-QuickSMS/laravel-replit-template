<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Services\Campaign\CampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ScheduledCampaignDispatcher — checks for scheduled campaigns that are due
 * and dispatches them for sending.
 *
 * Runs every minute via Laravel scheduler (Kernel.php):
 *   $schedule->job(new ScheduledCampaignDispatcher)->everyMinute();
 *
 * For each due campaign:
 * 1. Run billing preflight (estimate, balance check, reserve)
 * 2. Resolve per-recipient content (merge fields)
 * 3. Transition to queued
 * 4. Dispatch ProcessCampaignBatch jobs for each batch
 *
 * Uses ShouldBeUnique to prevent overlapping runs.
 *
 * Queue: 'scheduler' (lightweight, fast processing)
 */
class ScheduledCampaignDispatcher implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 120;
    public int $uniqueFor = 60; // Lock for 60 seconds

    public function __construct()
    {
        $this->onQueue('scheduler');
    }

    public function handle(CampaignService $campaignService): void
    {
        // Find all campaigns due for send
        $dueCampaigns = Campaign::withoutGlobalScope('tenant')
            ->where('status', Campaign::STATUS_SCHEDULED)
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit(10) // Process max 10 per run to avoid long lock times
            ->get();

        if ($dueCampaigns->isEmpty()) {
            return;
        }

        Log::info('[ScheduledCampaignDispatcher] Found due campaigns', [
            'count' => $dueCampaigns->count(),
        ]);

        foreach ($dueCampaigns as $campaign) {
            try {
                $this->processCampaign($campaign, $campaignService);
            } catch (\Exception $e) {
                Log::error('[ScheduledCampaignDispatcher] Failed to process campaign', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);

                // Mark as failed so it doesn't retry on next scheduler run
                try {
                    $campaignService->fail($campaign, 'Scheduled dispatch failed: ' . $e->getMessage());
                } catch (\Exception $inner) {
                    Log::error('[ScheduledCampaignDispatcher] Failed to mark campaign as failed', [
                        'campaign_id' => $campaign->id,
                        'error' => $inner->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Process a single scheduled campaign.
     */
    private function processCampaign(Campaign $campaign, CampaignService $campaignService): void
    {
        Log::info('[ScheduledCampaignDispatcher] Processing scheduled campaign', [
            'campaign_id' => $campaign->id,
            'account_id' => $campaign->account_id,
            'scheduled_at' => $campaign->scheduled_at->toIso8601String(),
        ]);

        // Run billing preflight + transition to queued
        $campaignService->processScheduled($campaign);

        // Dispatch batch jobs
        $this->dispatchBatchJobs($campaign);

        Log::info('[ScheduledCampaignDispatcher] Campaign dispatched', [
            'campaign_id' => $campaign->id,
        ]);
    }

    /**
     * Dispatch ProcessCampaignBatch jobs for all batches in a campaign.
     */
    private function dispatchBatchJobs(Campaign $campaign): void
    {
        // Find the max batch number
        $maxBatch = DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->where('status', CampaignRecipient::STATUS_PENDING)
            ->max('batch_number') ?? 0;

        $totalBatches = $maxBatch + 1;

        Log::info('[ScheduledCampaignDispatcher] Dispatching batch jobs', [
            'campaign_id' => $campaign->id,
            'total_batches' => $totalBatches,
        ]);

        for ($batch = 0; $batch < $totalBatches; $batch++) {
            ProcessCampaignBatch::dispatch($campaign->id, $batch);
        }
    }
}
