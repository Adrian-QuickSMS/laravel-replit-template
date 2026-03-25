<?php

namespace App\Jobs;

use App\Services\Billing\AutoTopUpService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryAutoTopUpJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 30;

    public function __construct(
        private string $eventId,
    ) {}

    public function uniqueId(): string
    {
        return 'auto-topup-retry-' . $this->eventId;
    }

    public function handle(AutoTopUpService $service): void
    {
        $service->processAutoTopUp($this->eventId);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('RetryAutoTopUpJob failed', [
            'event_id' => $this->eventId,
            'error' => $e->getMessage(),
        ]);
    }
}
