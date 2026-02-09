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

        // Gateways
        Route::get('/gateways', [GatewayController::class, 'index'])->name('admin.gateways.index');
        Route::post('/gateways', [GatewayController::class, 'store'])->name('admin.gateways.store');
        Route::put('/gateways/{gateway}', [GatewayController::class, 'update'])->name('admin.gateways.update');
        Route::post('/gateways/{gateway}/toggle-status', [GatewayController::class, 'toggleStatus'])->name('admin.gateways.toggle-status');
        Route::delete('/gateways/{gateway}', [GatewayController::class, 'destroy'])->name('admin.gateways.destroy');

        // Rate Cards
        Route::get('/rate-cards', [RateCardController::class, 'index'])->name('admin.rate-cards.index');
        Route::get('/rate-cards/upload', [RateCardController::class, 'uploadForm'])->name('admin.rate-cards.upload');
        Route::post('/rate-cards/validate-upload', [RateCardController::class, 'validateUpload'])->name('admin.rate-cards.validate-upload');
        Route::post('/rate-cards/process-upload', [RateCardController::class, 'processUpload'])->name('admin.rate-cards.process-upload');
        Route::put('/rate-cards/{rateCard}', [RateCardController::class, 'update'])->name('admin.rate-cards.update');
        Route::get('/rate-cards/{rateCard}/history', [RateCardController::class, 'history'])->name('admin.rate-cards.history');

        // MCC/MNC Master Reference
        Route::get('/mcc-mnc', [MccMncController::class, 'index'])->name('admin.mcc-mnc.index');
        Route::post('/mcc-mnc', [MccMncController::class, 'store'])->name('admin.mcc-mnc.store');
        Route::put('/mcc-mnc/{mccMnc}', [MccMncController::class, 'update'])->name('admin.mcc-mnc.update');
        Route::post('/mcc-mnc/{mccMnc}/toggle-status', [MccMncController::class, 'toggleStatus'])->name('admin.mcc-mnc.toggle-status');
        Route::delete('/mcc-mnc/{mccMnc}', [MccMncController::class, 'destroy'])->name('admin.mcc-mnc.destroy');
        Route::post('/mcc-mnc/parse-file', [MccMncController::class, 'parseFile'])->name('admin.mcc-mnc.parse-file');
        Route::post('/mcc-mnc/import', [MccMncController::class, 'import'])->name('admin.mcc-mnc.import');

    });

});
