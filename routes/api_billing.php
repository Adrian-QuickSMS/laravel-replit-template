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
    Route::get('/topup/auto-topup', [\App\Http\Controllers\Api\V1\TopUpController::class, 'getAutoTopUp']);
    Route::put('/topup/auto-topup', [\App\Http\Controllers\Api\V1\TopUpController::class, 'updateAutoTopUp']);

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


// Admin Console — Billing APIs
// Middleware: admin auth + RBAC + audit logging + rate limiting
Route::prefix('api/admin/v1')->middleware(['auth:admin', 'throttle:60,1'])->group(function () {

    $ctrl = \App\Http\Controllers\Api\Admin\BillingAdminController::class;

    // Account balance & transactions
    Route::get('/accounts/{id}/balance', [$ctrl, 'accountBalance']);
    Route::get('/accounts/{id}/transactions', [$ctrl, 'accountTransactions']);
    Route::post('/accounts/{id}/adjustment', [$ctrl, 'manualAdjustment']);

    // Account billing settings
    Route::put('/accounts/{id}/billing-type', [$ctrl, 'updateBillingType']);
    Route::put('/accounts/{id}/billing-method', [$ctrl, 'updateBillingMethod']);
    Route::put('/accounts/{id}/credit-limit', [$ctrl, 'updateCreditLimit']);
    Route::put('/accounts/{id}/payment-terms', [$ctrl, 'updatePaymentTerms']);

    // Pricing management
    Route::get('/accounts/{id}/pricing', [$ctrl, 'accountPricing']);
    Route::put('/accounts/{id}/pricing', [$ctrl, 'updateAccountPricing']);
    Route::get('/pricing/conflicts', [$ctrl, 'pricingConflicts']);
    Route::post('/pricing/conflicts/{id}/resolve', [$ctrl, 'resolveConflict']);

    // Invoice management
    Route::get('/invoices', [$ctrl, 'invoices']);
    Route::post('/accounts/{id}/invoices/generate', [$ctrl, 'generateInvoice']);
    Route::post('/invoices/{id}/void', [$ctrl, 'voidInvoice']);

    // Credit notes
    Route::post('/accounts/{id}/credit-notes', [$ctrl, 'issueCreditNote']);

    // Recurring charges
    Route::get('/accounts/{id}/recurring-charges', [$ctrl, 'recurringCharges']);
    Route::post('/accounts/{id}/recurring-charges', [$ctrl, 'createRecurringCharge']);

    // Test credits
    Route::get('/accounts/{id}/test-credits', [$ctrl, 'testCredits']);
    Route::post('/accounts/{id}/test-credits', [$ctrl, 'awardTestCredits']);

    // Reconciliation
    Route::post('/reconciliation/balance/run', [$ctrl, 'runReconciliation']);

    // Margin & reporting
    Route::get('/reporting/margin', [$ctrl, 'marginReport']);
    Route::get('/reporting/margin/by-account', [$ctrl, 'marginByAccount']);
    Route::get('/reporting/margin/by-country', [$ctrl, 'marginByCountry']);

    // Audit log
    Route::get('/audit/financial', [$ctrl, 'auditLog']);
});


// Webhooks — No auth middleware (signature verification in controllers)
Route::prefix('api/webhooks')->middleware(['throttle:300,1'])->group(function () {
    Route::post('/stripe', [\App\Http\Controllers\Api\Webhooks\StripeWebhookController::class, 'handle']);
    Route::post('/xero', [\App\Http\Controllers\Api\Webhooks\XeroWebhookController::class, 'handle']);
    Route::post('/hubspot/deal', [\App\Http\Controllers\Api\Webhooks\HubSpotWebhookController::class, 'handleDeal']);
});
