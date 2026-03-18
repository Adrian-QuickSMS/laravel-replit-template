<?php

namespace App\Providers;

use App\Services\AntiFloodService;
use App\Services\CountryPermissionCacheService;
use App\Services\CountryPermissionCheckService;
use App\Services\DataMaskingService;
use App\Services\IpAllowlistService;
use App\Services\OutOfHoursService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Country permission cache must be a singleton so L1 (in-process array)
        // persists for the lifetime of the request.
        $this->app->singleton(CountryPermissionCacheService::class);
        $this->app->singleton(CountryPermissionCheckService::class);

        // Security settings services — scoped (auto-cleared between requests in
        // Octane/queue workers; acts like singleton in standard FPM requests).
        // Uses instance-level caching instead of static for clean request isolation.
        $this->app->scoped(AntiFloodService::class);
        $this->app->scoped(OutOfHoursService::class);
        $this->app->scoped(DataMaskingService::class);
        $this->app->singleton(IpAllowlistService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::share('inbox_unread_count', 12);

        View::composer(['layouts.default', 'layouts.quicksms'], function ($view) {
            $tenantId = session('customer_tenant_id');
            $testCreditsRemaining = null;
            $accountStatus = null;
            if ($tenantId) {
                $account = \App\Models\Account::withoutGlobalScope('tenant')->find($tenantId);
                if ($account) {
                    $accountStatus = $account->status;
                    if (in_array($account->status, ['test_standard', 'test_dynamic'])) {
                        $testCreditsRemaining = (int) \App\Models\Billing\TestCreditWallet::where('account_id', $tenantId)
                            ->where('expired', false)
                            ->sum('credits_remaining');
                    }
                }
            }
            $view->with('test_credits_remaining_global', $testCreditsRemaining);
            $view->with('account_status_global', $accountStatus);
        });
    }
}
