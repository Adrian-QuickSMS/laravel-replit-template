<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Models\AccountCredit;
use App\Models\EmailVerificationToken;
use App\Models\AuthAuditLog;
use App\Services\SmsVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * Authentication Controller
 *
 * Handles signup, login, logout, email verification, password reset
 *
 * SECURITY:
 * - Uses stored procedures for account creation
 * - Enforces password policy (12+ chars)
 * - Logs all auth events to audit log
 * - Rate limiting applied via middleware
 * - Email verification required
 */
class AuthController extends Controller
{
    /**
     * Sign up new account
     *
     * POST /api/auth/signup
     */
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Company & Personal Details
            'company_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'country' => 'required|string|size:2',

            // Password (optional at Step 1, set in Step 3: Security Setup)
            'password' => [
                'nullable',
                'confirmed',
                Password::min(12)
                    ->max(128)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
            ],

            // Mobile Number (optional at Step 1, set in Step 3: Security Setup)
            'mobile_number' => 'nullable|string|max:20',

            // Consent (required)
            'accept_terms' => 'required|accepted',
            'accept_privacy' => 'nullable|accepted',
            'accept_fraud_prevention' => 'nullable|accepted',

            // Marketing Consent (optional)
            'accept_marketing' => 'nullable|boolean',

            // UTM Parameters (optional, captured from URL)
            'utm_source' => 'nullable|string|max:255',
            'utm_medium' => 'nullable|string|max:255',
            'utm_campaign' => 'nullable|string|max:255',
            'utm_content' => 'nullable|string|max:255',
            'utm_term' => 'nullable|string|max:255',

            // Referrer (optional, captured from HTTP headers)
            'referrer' => 'nullable|string|max:512',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $hashedPassword = $request->password
                ? Hash::make($request->password)
                : Hash::make(Str::random(64));
            $ipAddress = $request->ip();

            // Call stored procedure — handles account, user, settings, flags, and audit
            // SECURITY DEFINER function bypasses RLS for cross-table creation
            $result = DB::select(
                "SELECT * FROM sp_create_account(?, ?, ?, ?, ?, ?, ?, ?::inet)",
                [
                    $request->company_name,
                    $request->email,
                    $hashedPassword,
                    $request->first_name,
                    $request->last_name,
                    $request->phone,
                    $request->country,
                    $ipAddress,
                ]
            );

            if (empty($result)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account creation failed. Please try again.'
                ], 500);
            }

            $accountData = $result[0];

            $normalizedMobile = $request->mobile_number
                ? User::normalizeMobileNumber($request->mobile_number)
                : null;
            $consentVersions = config('consent.versions', [
                'terms' => '1.0',
                'privacy' => '1.0',
                'fraud_prevention' => '1.0',
            ]);

            DB::statement("SET app.current_tenant_id = ?", [$accountData->account_id]);

            // Update account with consent and UTM data
            DB::table('accounts')->where('id', $accountData->account_id)->update([
                'terms_accepted_at' => now(),
                'terms_accepted_ip' => $ipAddress,
                'terms_version' => $consentVersions['terms'] ?? '1.0',
                'privacy_accepted_at' => now(),
                'privacy_accepted_ip' => $ipAddress,
                'privacy_version' => $consentVersions['privacy'] ?? '1.0',
                'fraud_consent_at' => now(),
                'fraud_consent_ip' => $ipAddress,
                'fraud_consent_version' => $consentVersions['fraud_prevention'] ?? '1.0',
                'marketing_consent_at' => $request->accept_marketing ? now() : null,
                'marketing_consent_ip' => $request->accept_marketing ? $ipAddress : null,
                'signup_ip_address' => $ipAddress,
                'signup_referrer' => $request->referrer ?? $request->header('Referer'),
                'signup_utm_source' => $request->utm_source,
                'signup_utm_medium' => $request->utm_medium,
                'signup_utm_campaign' => $request->utm_campaign,
                'signup_utm_content' => $request->utm_content,
                'signup_utm_term' => $request->utm_term,
            ]);

            if ($request->mobile_number) {
                DB::table('users')->where('id', $accountData->user_id)->update([
                    'mobile_number' => $normalizedMobile,
                ]);
            }

            // Generate email verification token
            $user = User::withoutGlobalScope('tenant')->find($accountData->user_id);
            if ($user) {
                $tokenData = EmailVerificationToken::createForUser($user);
                // TODO: Send verification email via queue
                // Mail::to($user->email)->queue(new VerifyEmailMail($tokenData['plain_token']));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully. Please verify your email and mobile number.',
                'data' => [
                    'account_id' => $accountData->account_id,
                    'user_id' => $accountData->user_id,
                    'account_number' => $accountData->account_number,
                    'email' => $request->email,
                    'email_verification_required' => true,
                    'mobile_verification_required' => true,
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Signup error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during signup. Please try again.'
            ], 500);
        }
    }

    /**
     * Login
     *
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find user for password verification (must bypass tenant scope for login)
            $user = User::withoutGlobalScope('tenant')
                ->where('email', $request->email)
                ->first();

            // Verify password at app layer (hash is never sent over the wire)
            $passwordVerified = $user && Hash::check($request->password, $user->password);

            // Call stored function for authentication (handles logging, lockout, etc.)
            // PostgreSQL functions use SELECT * FROM, not CALL
            $result = DB::select(
                "SELECT * FROM sp_authenticate_user(?, ?::inet, ?::boolean)",
                [
                    $request->email,
                    $request->ip(),
                    $passwordVerified ? 'true' : 'false',
                ]
            );

            if (empty($result)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $userData = $result[0];

            // The SP returns a status field - check for non-success
            if (isset($userData->failed_attempts) && $userData->failed_attempts >= 5) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Account is locked due to too many failed attempts. Try again later.'
                ], 401);
            }

            // If no user_id returned, credentials were invalid
            if (empty($userData->user_id)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401);
            }

            // Refresh user model with the authenticated user_id
            $user = User::withoutGlobalScope('tenant')->find($userData->user_id);

            // Check if email is verified
            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please verify your email address before logging in.',
                    'email_verification_required' => true
                ], 403);
            }

            // Check if mobile verification is required (mandatory for all users)
            if (!$user->hasMobileVerified()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please verify your mobile number to enable MFA.',
                    'mobile_verification_required' => true,
                    'user_id' => $user->id
                ], 403);
            }

            // Check if MFA is enabled (auto-enabled after mobile verification)
            if ($user->hasMfaEnabled()) {
                // TODO: Implement MFA challenge (send SMS code)
                return response()->json([
                    'status' => 'mfa_required',
                    'message' => 'MFA verification required',
                    'mfa_challenge_token' => 'TODO_IMPLEMENT_MFA',
                    'mobile_number_hint' => substr($user->mobile_number, -4)
                ], 200);
            }

            // Create session token
            $token = $user->createToken('web-session', ['*'])->plainTextToken;

            // Get account details
            $account = $user->account;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => $user->toPortalArray(),
                    'account' => $account->toPortalArray(),
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());

            // Log failed login
            AuthAuditLog::logLoginFailure(
                $request->email,
                'System error: ' . $e->getMessage(),
                'customer_user'
            );

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during login. Please try again.'
            ], 500);
        }
    }

    /**
     * Logout
     *
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            // Revoke current token
            $request->user()->currentAccessToken()->delete();

            // Log logout
            AuthAuditLog::logLogout($user, 'customer_user');

            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Logout error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during logout'
            ], 500);
        }
    }

    /**
     * Verify email address
     *
     * POST /api/auth/verify-email
     */
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|size:64',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = EmailVerificationToken::verifyAndConsume($request->token);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired verification token'
                ], 400);
            }

            // Log email verified event
            AuthAuditLog::logEvent([
                'actor_type' => 'customer_user',
                'actor_id' => $user->id,
                'actor_email' => $user->email,
                'tenant_id' => $user->tenant_id,
                'event_type' => 'email_verified',
                'result' => 'success',
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Email verified successfully. You can now log in.',
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Email verification error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during email verification'
            ], 500);
        }
    }

    /**
     * Resend verification email
     *
     * POST /api/auth/resend-verification
     */
    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::withoutGlobalScope('tenant')
                ->where('email', $request->email)
                ->first();

            if (!$user) {
                // Don't reveal whether email exists
                return response()->json([
                    'status' => 'success',
                    'message' => 'If the email exists, a verification link has been sent.'
                ], 200);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email already verified'
                ], 400);
            }

            // Generate new verification token
            $tokenData = EmailVerificationToken::createForUser($user);

            // TODO: Send verification email via queue
            // Mail::to($user->email)->queue(new VerifyEmailMail($tokenData['plain_token']));

            return response()->json([
                'status' => 'success',
                'message' => 'Verification email sent successfully'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Resend verification error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Request password reset
     *
     * POST /api/auth/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::withoutGlobalScope('tenant')
                ->where('email', $request->email)
                ->first();

            if ($user) {
                // Generate password reset token
                $token = bin2hex(random_bytes(32));
                $hashedToken = hash('sha256', $token);

                // Store in password_reset_tokens table
                DB::table('password_reset_tokens')->updateOrInsert(
                    ['email' => $request->email],
                    [
                        'token' => $hashedToken,
                        'created_at' => now()
                    ]
                );

                // Log password reset request
                AuthAuditLog::logPasswordResetRequest($request->email, $user->tenant_id);

                // TODO: Send password reset email via queue
                // Mail::to($user->email)->queue(new PasswordResetMail($token));
            }

            // Always return success (don't reveal if email exists)
            return response()->json([
                'status' => 'success',
                'message' => 'If the email exists, a password reset link has been sent.'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Forgot password error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Reset password
     *
     * POST /api/auth/reset-password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find token
            $hashedToken = hash('sha256', $request->token);

            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->where('token', $hashedToken)
                ->first();

            if (!$resetRecord) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired reset token'
                ], 400);
            }

            // Check if token expired (60 minutes)
            if (now()->diffInMinutes($resetRecord->created_at) > 60) {
                DB::table('password_reset_tokens')
                    ->where('email', $request->email)
                    ->delete();

                return response()->json([
                    'status' => 'error',
                    'message' => 'Reset token has expired'
                ], 400);
            }

            // Find user
            $user = User::withoutGlobalScope('tenant')
                ->where('email', $request->email)
                ->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            // Change password
            $user->changePassword($request->password);

            // Delete reset token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // Log password change
            AuthAuditLog::logPasswordChange($user, 'customer_user');

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successfully. You can now log in with your new password.'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Reset password error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while resetting your password. Please try again.'
            ], 500);
        }
    }

    /**
     * Get current user
     *
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user();
            $account = $user->account;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => $user->toPortalArray(),
                    'account' => $account->toPortalArray(),
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get current user error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred'
            ], 500);
        }
    }
}
