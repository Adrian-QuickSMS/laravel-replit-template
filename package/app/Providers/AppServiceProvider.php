<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Account;
use App\Observers\AccountObserver;

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
        // Register model observers
        Account::observe(AccountObserver::class);
    }
}
