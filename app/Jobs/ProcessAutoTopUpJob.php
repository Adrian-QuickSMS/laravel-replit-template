<?php

namespace App\Jobs;

use App\Models\Billing\AutoTopUpEvent;
use App\Services\Billing\AutoTopUpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAutoTopUpJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $maxExceptions = 1;
    public int $timeout = 30;

    public function __construct(
        private string $eventId,
    ) {}

    public function uniqueId(): string
    {
        return 'auto-topup-' . $this->eventId;
    }

    public function handle(AutoTopUpService $service): void
    {
        $service->processAutoTopUp($this->eventId);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('ProcessAutoTopUpJob failed permanently', [
            'event_id' => $this->eventId,
            'error' => $e->getMessage(),
        ]);

        // Mark the event as failed so it doesn't stay orphaned in pending status
        try {
            $event = AutoTopUpEvent::find($this->eventId);
            if ($event && in_array($event->status, [AutoTopUpEvent::STATUS_PENDING, AutoTopUpEvent::STATUS_PROCESSING])) {
                $event->update([
                    'event_type' => AutoTopUpEvent::TYPE_PAYMENT_FAILED,
                    'status' => AutoTopUpEvent::STATUS_FAILED,
                    'failure_code' => 'job_failed',
                    'failure_message' => 'Processing job failed: ' . $e->getMessage(),
                    'completed_at' => now(),
                ]);
            }
        } catch (\Throwable $inner) {
            Log::error('Failed to mark auto top-up event as failed', [
                'event_id' => $this->eventId,
                'error' => $inner->getMessage(),
            ]);
        }
    }
}
