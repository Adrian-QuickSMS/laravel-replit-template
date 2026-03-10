<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
            if ($tenantId) {
                $account = \App\Models\Account::withoutGlobalScope('tenant')->find($tenantId);
                if ($account && in_array($account->status, ['test_standard', 'test_dynamic'])) {
                    $wallet = \App\Models\Billing\TestCreditWallet::where('account_id', $tenantId)
                        ->where('expired', false)
                        ->orderByDesc('created_at')
                        ->first();
                    $testCreditsRemaining = $wallet ? $wallet->credits_remaining : 0;
                }
            }
            $view->with('test_credits_remaining_global', $testCreditsRemaining);
        });
    }
}
