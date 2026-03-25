<?php

use Illuminate\Support\Facades\Route;

// Customer Portal — Billing APIs
// Middleware: auth + tenant scope + rate limiting
Route::prefix('api/v1')->middleware(['auth:customer', 'throttle:120,1'])->group(function () {

    // Balance
    Route::get('/balance', [\App\Http\Controllers\Api\V1\BalanceController::class, 'show']);
    Route::get('/balance/transactions', [\App\Http\Controllers\Api\V1\BalanceController::class, 'transactions']);
    Route::get('/balance/transactions/{id}', [\App\Http\Controllers\Api\V1\BalanceController::class, 'transactionDetail']);

    // Top-Up
    Route::post('/topup/checkout-session', [\App\Http\Controllers\Api\V1\TopUpController::class, 'createCheckoutSession']);
    // Auto Top-Up (requires view_billing permission)
    Route::middleware(['permission:view_billing'])->group(function () {
        Route::get('/topup/auto-topup', [\App\Http\Controllers\Api\V1\TopUpController::class, 'getAutoTopUp']);
        Route::put('/topup/auto-topup', [\App\Http\Controllers\Api\V1\TopUpController::class, 'updateAutoTopUp']);
        Route::post('/topup/auto-topup/disable', [\App\Http\Controllers\Api\V1\TopUpController::class, 'disableAutoTopUp']);
        Route::post('/topup/auto-topup/setup-payment-method', [\App\Http\Controllers\Api\V1\TopUpController::class, 'setupPaymentMethod'])
            ->middleware('throttle:10,1'); // Tighter limit: 10 Stripe sessions per minute
        Route::post('/topup/auto-topup/payment-method/remove', [\App\Http\Controllers\Api\V1\TopUpController::class, 'removePaymentMethod']);
        Route::get('/topup/auto-topup/events', [\App\Http\Controllers\Api\V1\TopUpController::class, 'listEvents']);
    });

    // Pricing
    Route::get('/pricing', [\App\Http\Controllers\Api\V1\PricingController::class, 'index']);
    Route::get('/pricing/{country_iso}', [\App\Http\Controllers\Api\V1\PricingController::class, 'forCountry']);
    Route::post('/pricing/estimate', [\App\Http\Controllers\Api\V1\PricingController::class, 'estimate']);

    // Invoices
    Route::get('/invoices', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'index']);
    Route::get('/invoices/{id}', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'show']);
    Route::get('/invoices/{id}/pdf', [\App\Http\Controllers\Api\V1\InvoiceController::class, 'pdf']);

    // Balance Alerts
    Route::get('/alerts/balance', [\App\Http\Controllers\Api\V1\BalanceAlertController::class, 'index']);
    Route::post('/alerts/balance', [\App\Http\Controllers\Api\V1\BalanceAlertController::class, 'store']);
    Route::put('/alerts/balance/{id}', [\App\Http\Controllers\Api\V1\BalanceAlertController::class, 'update']);
    Route::delete('/alerts/balance/{id}', [\App\Http\Controllers\Api\V1\BalanceAlertController::class, 'destroy']);
});


// Webhooks — No auth middleware (signature verification in controllers)
Route::prefix('api/webhooks')->middleware(['throttle:300,1'])->group(function () {
    Route::post('/stripe', [\App\Http\Controllers\Api\Webhooks\StripeWebhookController::class, 'handle']);
    Route::post('/xero', [\App\Http\Controllers\Api\Webhooks\XeroWebhookController::class, 'handle']);
    Route::post('/hubspot/deal', [\App\Http\Controllers\Api\Webhooks\HubSpotWebhookController::class, 'handleDeal']);
});
