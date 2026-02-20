<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Billing\LedgerService;
use App\Services\Billing\PricingEngine;
use App\Services\Billing\BalanceService;
use App\Services\Billing\BalanceAlertService;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\XeroService;
use App\Services\Billing\StripeCheckoutService;
use App\Services\Billing\HubSpotPricingSyncService;
use App\Services\Billing\ReconciliationService;

class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Singletons — one instance per request
        $this->app->singleton(LedgerService::class);
        $this->app->singleton(PricingEngine::class);
        $this->app->singleton(BalanceAlertService::class);
        $this->app->singleton(XeroService::class);
        $this->app->singleton(HubSpotPricingSyncService::class);

        // Standard bindings
        $this->app->bind(BalanceService::class, function ($app) {
            return new BalanceService(
                $app->make(LedgerService::class),
                $app->make(BalanceAlertService::class),
            );
        });

        $this->app->bind(InvoiceService::class, function ($app) {
            return new InvoiceService(
                $app->make(LedgerService::class),
            );
        });

        $this->app->bind(StripeCheckoutService::class, function ($app) {
            return new StripeCheckoutService(
                $app->make(BalanceService::class),
                $app->make(InvoiceService::class),
            );
        });

        $this->app->bind(ReconciliationService::class, function ($app) {
            return new ReconciliationService(
                $app->make(LedgerService::class),
                $app->make(BalanceService::class),
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
