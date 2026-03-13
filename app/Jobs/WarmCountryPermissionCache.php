<?php

namespace App\Jobs;

use App\Services\CountryPermissionCacheService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Warm country permission cache for active accounts.
 *
 * Dispatch on a schedule (e.g. every 5 minutes) or after bulk country control changes.
 */
class WarmCountryPermissionCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 120;

    private ?string $accountId;

    /**
     * @param string|null $accountId Warm a specific account, or null for all active accounts
     */
    public function __construct(?string $accountId = null)
    {
        $this->accountId = $accountId;
        $this->onQueue('default');
    }

    public function handle(CountryPermissionCacheService $cacheService): void
    {
        if ($this->accountId) {
            $cacheService->warmAccount($this->accountId);
            Log::info('[WarmCountryPermissionCache] Warmed cache for account', [
                'account_id' => $this->accountId,
            ]);
            return;
        }

        // Warm all active accounts in chunks to avoid memory spikes and Redis write storms
        $count = 0;
        $total = 0;

        DB::table('accounts')
            ->whereIn('status', ['test_standard', 'test_dynamic', 'active_standard', 'active_dynamic'])
            ->select('id')
            ->orderBy('id')
            ->chunk(100, function ($accounts) use ($cacheService, &$count, &$total) {
                $total += $accounts->count();
                foreach ($accounts as $account) {
                    try {
                        $cacheService->warmAccount($account->id);
                        $count++;
                    } catch (\Exception $e) {
                        Log::warning('[WarmCountryPermissionCache] Failed to warm account', [
                            'account_id' => $account->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                // Brief pause between chunks to avoid Redis write storms
                usleep(50000); // 50ms
            });

        Log::info('[WarmCountryPermissionCache] Cache warming complete', [
            'accounts_warmed' => $count,
            'total_accounts' => $total,
        ]);
    }
}
