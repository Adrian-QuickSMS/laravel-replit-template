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

        // Warm all active accounts
        $accountIds = DB::table('accounts')
            ->whereIn('status', ['test_standard', 'test_dynamic', 'active_standard', 'active_dynamic'])
            ->pluck('id');

        $count = 0;
        foreach ($accountIds as $accountId) {
            try {
                $cacheService->warmAccount($accountId);
                $count++;
            } catch (\Exception $e) {
                Log::warning('[WarmCountryPermissionCache] Failed to warm account', [
                    'account_id' => $accountId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('[WarmCountryPermissionCache] Cache warming complete', [
            'accounts_warmed' => $count,
            'total_accounts' => $accountIds->count(),
        ]);
    }
}
