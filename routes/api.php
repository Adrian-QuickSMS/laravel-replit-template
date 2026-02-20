<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReportingDashboardApiController;
use App\Http\Controllers\Api\BillingApiController;
use App\Http\Controllers\Api\PurchaseApiController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\InvoiceApiController;
use App\Http\Controllers\Api\TopUpApiController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Reporting Dashboard API (mock data)
Route::prefix('reporting/dashboard')->group(function () {
    Route::get('/', [ReportingDashboardApiController::class, 'index']);
    Route::get('/kpis', [ReportingDashboardApiController::class, 'kpis']);
    Route::get('/volume', [ReportingDashboardApiController::class, 'volumeOverTime']);
    Route::get('/inbound-volume', [ReportingDashboardApiController::class, 'inboundVolumeOverTime']);
    Route::get('/channel-split', [ReportingDashboardApiController::class, 'channelSplit']);
    Route::get('/delivery-status', [ReportingDashboardApiController::class, 'deliveryStatus']);
    Route::get('/top-countries', [ReportingDashboardApiController::class, 'topCountries']);
    Route::get('/top-sender-ids', [ReportingDashboardApiController::class, 'topSenderIds']);
    Route::get('/peak-time', [ReportingDashboardApiController::class, 'peakSendingTime']);
    Route::get('/failure-reasons', [ReportingDashboardApiController::class, 'failureReasons']);
    Route::get('/available-filters', [ReportingDashboardApiController::class, 'availableFilters']);
});

// Billing API (mock data for Finance Data page)
Route::prefix('billing')->group(function () {
    Route::get('/data', [BillingApiController::class, 'getData']);
    Route::get('/export', [BillingApiController::class, 'export']);
    Route::get('/saved-reports', [BillingApiController::class, 'getSavedReports']);
    Route::post('/saved-reports', [BillingApiController::class, 'saveReport']);
    Route::post('/schedule', [BillingApiController::class, 'schedule']);
});

// Purchase API (HubSpot Products integration)
Route::prefix('purchase')->group(function () {
    Route::get('/products', [PurchaseApiController::class, 'getProducts']);
    Route::post('/calculate-order', [PurchaseApiController::class, 'calculateOrder']);
    Route::post('/create-invoice', [PurchaseApiController::class, 'createInvoice']);
});

// Webhooks
Route::prefix('webhooks')->group(function () {
    Route::post('/hubspot/payment', [WebhookController::class, 'hubspotPayment']);
    Route::post('/stripe', [WebhookController::class, 'stripeWebhook']);
});

// Top-Up API
Route::prefix('topup')->group(function () {
    Route::post('/create-checkout-session', [TopUpApiController::class, 'createCheckoutSession']);
});

// Account API
Route::prefix('account')->group(function () {
    Route::get('/balance', [WebhookController::class, 'getAccountBalance']);
    Route::get('/payment-status', [WebhookController::class, 'checkPaymentStatus']);
});

// Invoice API (Billing database)
Route::prefix('invoices')->group(function () {
    Route::get('/', [InvoiceApiController::class, 'index']);
    Route::get('/account-summary', [InvoiceApiController::class, 'accountSummary']);
    Route::get('/{invoiceId}', [InvoiceApiController::class, 'show']);
    Route::get('/{invoiceId}/pdf', [InvoiceApiController::class, 'downloadPdf']);
    Route::post('/{invoiceId}/create-checkout-session', [InvoiceApiController::class, 'createCheckoutSession']);
});

// Contact Book API routes moved to routes/web.php under customer.auth middleware
// (api middleware group lacks session cookies, breaking tenant-scoped queries)
