<?php

use Illuminate\Support\Facades\Route;

// Admin Console — Billing APIs
// Session-based (web middleware group for cookies/CSRF), admin auth via AdminAuthenticate
Route::prefix('api/admin/v1')->middleware([\App\Http\Middleware\AdminAuthenticate::class, 'throttle:60,1'])->group(function () {

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
    Route::put('/accounts/{id}/overdue-enforcement', [$ctrl, 'updateOverdueEnforcement']);

    // Pricing management
    Route::get('/accounts/{id}/pricing', [$ctrl, 'accountPricing']);
    Route::put('/accounts/{id}/pricing', [$ctrl, 'updateAccountPricing']);
    Route::get('/pricing/conflicts', [$ctrl, 'pricingConflicts']);
    Route::post('/pricing/conflicts/{id}/resolve', [$ctrl, 'resolveConflict']);

    // Invoice management
    Route::get('/invoices', [$ctrl, 'invoices']);
    Route::post('/accounts/{id}/invoices/generate', [$ctrl, 'generateInvoice']);
    Route::post('/invoices/{id}/void', [$ctrl, 'voidInvoice']);
    Route::post('/invoices/{id}/record-payment', [$ctrl, 'recordPayment']);
    Route::get('/invoices/{id}/payments', [$ctrl, 'invoicePayments']);

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
