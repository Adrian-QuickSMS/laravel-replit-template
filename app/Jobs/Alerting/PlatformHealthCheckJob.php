<?php

namespace App\Jobs\Alerting;

use App\Events\Alerting\QueueBacklogBuilding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Periodic platform health check that fires admin alerts
 * when metrics exceed thresholds.
 *
 * Schedule: Every 5 minutes.
 */
class PlatformHealthCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 60;

    public function __construct()
    {
        $this->onQueue(config('alerting.queue.evaluation', 'alerts'));
    }

    public function handle(): void
    {
        $this->checkQueueBacklog();
    }

    /**
     * Check if any queue has a dangerous backlog.
     */
    private function checkQueueBacklog(): void
    {
        try {
            // Check jobs table for pending jobs per queue
            $queues = DB::table('jobs')
                ->selectRaw('queue, count(*) as pending_count')
                ->groupBy('queue')
                ->get();

            foreach ($queues as $queue) {
                if ($queue->pending_count > 1000) {
                    Log::warning('[PlatformHealthCheck] Queue backlog detected', [
                        'queue' => $queue->queue,
                        'pending' => $queue->pending_count,
                    ]);

                    QueueBacklogBuilding::dispatch(
                        $queue->queue,
                        $queue->pending_count,
                    );
                }
            }
        } catch (\Throwable $e) {
            // Jobs table might not exist if using Redis queues
            Log::debug('[PlatformHealthCheck] Could not check queue backlog', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
