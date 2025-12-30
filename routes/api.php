<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReportingDashboardApiController;

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
    Route::get('/channel-split', [ReportingDashboardApiController::class, 'channelSplit']);
    Route::get('/delivery-status', [ReportingDashboardApiController::class, 'deliveryStatus']);
    Route::get('/top-countries', [ReportingDashboardApiController::class, 'topCountries']);
    Route::get('/top-sender-ids', [ReportingDashboardApiController::class, 'topSenderIds']);
    Route::get('/peak-time', [ReportingDashboardApiController::class, 'peakSendingTime']);
    Route::get('/failure-reasons', [ReportingDashboardApiController::class, 'failureReasons']);
});
