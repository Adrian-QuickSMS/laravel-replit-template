<?php

namespace App\Jobs;

use App\Services\Numbers\NumberBillingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * ProcessNumberRecurringCharges — daily job to process recurring charges for numbers/keywords.
 *
 * Schedule: daily at 02:00 UTC (via app/Console/Kernel.php)
 *
 * For each due recurring charge:
 * - Sufficient balance → debit + advance next_charge_date
 * - Insufficient balance → suspend the number immediately
 */
class ProcessNumberRecurringCharges implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 600; // 10 minutes max

    public function __construct()
    {
        $this->onQueue('billing');
    }

    public function handle(NumberBillingService $billingService): void
    {
        Log::info('[ProcessNumberRecurringCharges] Starting daily recurring charge processing');

        $result = $billingService->processRecurringCharges();

        Log::info('[ProcessNumberRecurringCharges] Completed', $result);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[ProcessNumberRecurringCharges] Job failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
