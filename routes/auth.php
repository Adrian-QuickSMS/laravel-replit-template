<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AccountController;

Route::prefix('api/auth')->group(function () {
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/complete-security', [AuthController::class, 'completeSecuritySetup']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    });
});

Route::prefix('api')->middleware('auth:sanctum')->group(function () {
    Route::get('/account', [AccountController::class, 'show']);
    Route::put('/account', [AccountController::class, 'update']);
    Route::put('/account/settings', [AccountController::class, 'updateSettings']);
    Route::get('/account/team', [AccountController::class, 'team']);
});
