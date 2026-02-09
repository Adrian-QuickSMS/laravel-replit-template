<?php

/**
 * Supplier Management Routes
 * Admin Console → Supplier Management
 *
 * These routes handle the Supplier Rate Card Management system
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\GatewayController;
use App\Http\Controllers\Admin\RateCardController;
use App\Http\Controllers\Admin\MccMncController;
use App\Http\Controllers\Admin\UkPrefixController;

Route::prefix('admin')->middleware([
    \App\Http\Middleware\AdminIpAllowlist::class,
    \App\Http\Middleware\AdminAuthenticate::class
])->group(function () {

    // Supplier Management Routes
    Route::prefix('supplier-management')->group(function () {

        // Suppliers
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('admin.suppliers.index');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('admin.suppliers.store');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('admin.suppliers.update');
        Route::post('/suppliers/{supplier}/suspend', [SupplierController::class, 'suspend'])->name('admin.suppliers.suspend');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('admin.suppliers.destroy');
        Route::get('/suppliers/{supplier}/gateways', [SupplierController::class, 'gateways'])->name('admin.suppliers.gateways');

        // Gateways
        Route::get('/gateways', [GatewayController::class, 'index'])->name('admin.gateways.index');
        Route::post('/gateways', [GatewayController::class, 'store'])->name('admin.gateways.store');
        Route::put('/gateways/{gateway}', [GatewayController::class, 'update'])->name('admin.gateways.update');
        Route::post('/gateways/{gateway}/toggle-status', [GatewayController::class, 'toggleStatus'])->name('admin.gateways.toggle-status');
        Route::delete('/gateways/{gateway}', [GatewayController::class, 'destroy'])->name('admin.gateways.destroy');

        // Rate Cards
        Route::get('/rate-cards', [RateCardController::class, 'index'])->name('admin.rate-cards.index');
        Route::get('/rate-cards/upload', [RateCardController::class, 'uploadForm'])->name('admin.rate-cards.upload');
        Route::post('/rate-cards/parse-file', [RateCardController::class, 'parseFile'])->name('admin.rate-cards.parse-file');
        Route::post('/rate-cards/validate-mapping', [RateCardController::class, 'validateMapping'])->name('admin.rate-cards.validate-mapping');
        Route::post('/rate-cards/process-upload', [RateCardController::class, 'processUpload'])->name('admin.rate-cards.process-upload');
        Route::get('/rate-cards/{rateCard}', [RateCardController::class, 'show'])->name('admin.rate-cards.show');
        Route::put('/rate-cards/{rateCard}', [RateCardController::class, 'update'])->name('admin.rate-cards.update');
        Route::get('/rate-cards/{rateCard}/history', [RateCardController::class, 'history'])->name('admin.rate-cards.history');
        Route::post('/rate-cards/update-billing-methods', [RateCardController::class, 'updateBillingMethods'])->name('admin.rate-cards.update-billing-methods');

        // MCC/MNC Master Reference
        Route::get('/mcc-mnc', [MccMncController::class, 'index'])->name('admin.mcc-mnc.index');
        Route::get('/mcc-mnc/{mccMnc}', [MccMncController::class, 'show'])->name('admin.mcc-mnc.show');
        Route::post('/mcc-mnc', [MccMncController::class, 'store'])->name('admin.mcc-mnc.store');
        Route::put('/mcc-mnc/{mccMnc}', [MccMncController::class, 'update'])->name('admin.mcc-mnc.update');
        Route::post('/mcc-mnc/{mccMnc}/toggle-status', [MccMncController::class, 'toggleStatus'])->name('admin.mcc-mnc.toggle-status');
        Route::delete('/mcc-mnc/{mccMnc}', [MccMncController::class, 'destroy'])->name('admin.mcc-mnc.destroy');
        Route::post('/mcc-mnc/parse-file', [MccMncController::class, 'parseFile'])->name('admin.mcc-mnc.parse-file');
        Route::post('/mcc-mnc/import', [MccMncController::class, 'import'])->name('admin.mcc-mnc.import');

        // UK Prefixes
        Route::get('/uk-prefixes', [UkPrefixController::class, 'index'])->name('admin.uk-prefixes.index');
        Route::post('/uk-prefixes/parse-file', [UkPrefixController::class, 'parseFile'])->name('admin.uk-prefixes.parse-file');
        Route::post('/uk-prefixes/import', [UkPrefixController::class, 'import'])->name('admin.uk-prefixes.import');
        Route::put('/uk-prefixes/{ukPrefix}/map', [UkPrefixController::class, 'mapNetwork'])->name('admin.uk-prefixes.map');
        Route::post('/uk-prefixes/{ukPrefix}/confirm', [UkPrefixController::class, 'confirmPrediction'])->name('admin.uk-prefixes.confirm');
        Route::post('/uk-prefixes/{ukPrefix}/reject', [UkPrefixController::class, 'rejectPrediction'])->name('admin.uk-prefixes.reject');
        Route::post('/uk-prefixes/bulk-confirm', [UkPrefixController::class, 'bulkConfirm'])->name('admin.uk-prefixes.bulk-confirm');
        Route::post('/uk-prefixes/create-and-map', [UkPrefixController::class, 'createAndMap'])->name('admin.uk-prefixes.create-and-map');

    });

});
