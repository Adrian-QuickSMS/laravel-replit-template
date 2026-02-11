<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AccountController;

/*
|--------------------------------------------------------------------------
| Authentication & Account Management Routes
|--------------------------------------------------------------------------
|
| These routes handle authentication (signup, login, logout) and account
| management (account details, settings, team members).
|
| SECURITY:
| - Public routes: signup, login, verify-email, forgot-password, reset-password
| - Protected routes: logout, me, account management (require auth:sanctum)
| - Tenant scoping enforced automatically via global scope
|
*/

// =====================================================
// PUBLIC AUTHENTICATION ROUTES (No Auth Required)
// =====================================================

Route::prefix('api/auth')->group(function () {

    // Signup
    Route::post('/signup', [AuthController::class, 'signup']);

    // Login
    Route::post('/login', [AuthController::class, 'login']);

    // Email Verification
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-verification', [AuthController::class, 'resendVerification']);

    // Password Reset
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

});

// =====================================================
// PROTECTED AUTHENTICATION ROUTES (Require Auth)
// =====================================================

Route::prefix('api/auth')->middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Get current user
    Route::get('/me', [AuthController::class, 'me']);

});

// =====================================================
// ACCOUNT MANAGEMENT ROUTES (Require Auth)
// =====================================================

Route::prefix('api/account')->middleware('auth:sanctum')->group(function () {

    // Account Details
    Route::get('/', [AccountController::class, 'show']);
    Route::put('/', [AccountController::class, 'update']);

    // Account Settings
    Route::put('/settings', [AccountController::class, 'updateSettings']);

    // Team Management
    Route::get('/team', [AccountController::class, 'team']);
    Route::post('/team/invite', [AccountController::class, 'inviteTeamMember']);
    Route::delete('/team/{userId}', [AccountController::class, 'removeTeamMember']);

});
