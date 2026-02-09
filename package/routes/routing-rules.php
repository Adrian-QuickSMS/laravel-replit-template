<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RoutingRuleController;
use App\Http\Controllers\Admin\RoutingOverrideController;

/*
|--------------------------------------------------------------------------
| Routing Rules Routes
|--------------------------------------------------------------------------
|
| Manual routing control for QuickSMS platform
| Admin-only module for managing outbound routing rules
|
*/

Route::prefix('admin')->middleware([
    \App\Http\Middleware\AdminIpAllowlist::class,
    \App\Http\Middleware\AdminAuthenticate::class
])->group(function () {

    Route::prefix('routing-rules')->name('admin.routing.')->group(function () {

        // UK Routes
        Route::get('/uk-routes', [RoutingRuleController::class, 'ukRoutes'])
            ->name('uk-routes');

        // International Routes
        Route::get('/international-routes', [RoutingRuleController::class, 'internationalRoutes'])
            ->name('international-routes');

        // Routing Rule Management
        Route::get('/{id}', [RoutingRuleController::class, 'show'])
            ->name('show');

        Route::post('/{id}/add-gateway', [RoutingRuleController::class, 'addGateway'])
            ->name('add-gateway');

        Route::post('/{id}/set-primary', [RoutingRuleController::class, 'setPrimaryGateway'])
            ->name('set-primary');

        Route::post('/{id}/toggle-destination', [RoutingRuleController::class, 'toggleDestination'])
            ->name('toggle-destination');

        Route::get('/{id}/available-gateways', [RoutingRuleController::class, 'getAvailableGateways'])
            ->name('available-gateways');

        // Gateway Weight Management
        Route::post('/weight/{id}/change', [RoutingRuleController::class, 'changeWeight'])
            ->name('change-weight');

        Route::post('/weight/{id}/toggle', [RoutingRuleController::class, 'toggleGatewayStatus'])
            ->name('toggle-gateway-status');

        Route::delete('/weight/{id}/remove', [RoutingRuleController::class, 'removeGateway'])
            ->name('remove-gateway');

        // Customer Overrides
        Route::get('/overrides', [RoutingOverrideController::class, 'index'])
            ->name('customer-overrides');

        Route::post('/overrides', [RoutingOverrideController::class, 'store'])
            ->name('create-override');

        Route::get('/overrides/{id}', [RoutingOverrideController::class, 'show'])
            ->name('show-override');

        Route::put('/overrides/{id}', [RoutingOverrideController::class, 'update'])
            ->name('update-override');

        Route::post('/overrides/{id}/cancel', [RoutingOverrideController::class, 'cancel'])
            ->name('cancel-override');

        Route::get('/overrides/search/customers', [RoutingOverrideController::class, 'searchCustomers'])
            ->name('search-customers');
    });
});
