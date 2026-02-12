<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Account;
use App\Models\AccountCredit;
use App\Models\AuthAuditLog;
use App\Services\SmsVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * Mobile Verification Controller
 *
 * Handles mobile number verification for MFA setup
 *
 * FLOW:
 * 1. User signs up with mobile number
 * 2. POST /verify-mobile/send - Sends 6-digit code via SMS
 * 3. POST /verify-mobile/verify - User submits code
 * 4. If marketing consent given → Award 100 free credits
 * 5. MFA auto-enabled after successful verification
 */
class MobileVerificationController extends Controller
{
    protected $smsService;

    public function __construct(SmsVerificationService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Send mobile verification code
     *
     * POST /api/auth/verify-mobile/send
     *
     * Can be called by authenticated users or with user_id (for signup flow)
     */
    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required_without:auth|string|size:36',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get user (either from auth or user_id param)
            if ($request->user()) {
                $user = $request->user();
            } else {
                $user = User::withoutGlobalScope('tenant')->find($request->user_id);
            }

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            // Check if already verified
            if ($user->hasMobileVerified()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mobile number already verified'
                ], 400);
            }

            // Check if can resend (1 minute cooldown)
            if (!$user->canResendMobileCode()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Please wait before requesting another code'
                ], 429);
            }

            // Send verification code
            $result = $this->smsService->sendVerificationCode($user, $request->ip());

            if (!$result['success']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $result['message']
                ], 429);
            }

            // Get remaining attempts
            $remainingAttempts = $this->smsService->getRemainingAttempts($user->mobile_number);

            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'data' => [
                    'mobile_number_hint' => $user->getFormattedMobileNumber(),
                    'remaining_attempts' => $remainingAttempts,
                    'expires_in_minutes' => SmsVerificationService::CODE_EXPIRY_MINUTES,
                    'code' => $result['code'], // Only present in debug mode
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Send mobile verification code error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Verify mobile verification code
     *
     * POST /api/auth/verify-mobile/verify
     *
     * Awards 100 free credits if marketing consent was given
     */
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required_without:auth|string|size:36',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get user (either from auth or user_id param)
            if ($request->user()) {
                $user = $request->user();
            } else {
                $user = User::withoutGlobalScope('tenant')->find($request->user_id);
            }

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            // Check if already verified
            if ($user->hasMobileVerified()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mobile number already verified'
                ], 400);
            }

            // Verify code
            $result = $this->smsService->verifyCode($user, $request->code);

            if (!$result['success']) {
                // Log failed verification attempt
                AuthAuditLog::logEvent([
                    'actor_type' => 'customer_user',
                    'actor_id' => $user->id,
                    'actor_email' => $user->email,
                    'tenant_id' => $user->tenant_id,
                    'event_type' => 'mobile_verification_failed',
                    'result' => 'failure',
                    'ip_address' => $request->ip(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => $result['message']
                ], 400);
            }

            // Refresh user model (verifyCode updates mobile_verified_at and mfa_enabled)
            $user->refresh();

            // Check if account has marketing consent and award credits
            $account = $user->account;
            $creditsAwarded = 0;

            if ($account->hasMarketingConsent()) {
                // Award 100 free SMS credits for mobile verification + marketing opt-in
                $credit = AccountCredit::awardMobileVerificationCredits($account->id);
                $creditsAwarded = $credit->credits_awarded;

                // Update account's total signup credits
                $account->update([
                    'signup_credits_awarded' => $creditsAwarded,
                    'signup_promotion_code' => 'MOBILE_VERIFY_100'
                ]);

                \Log::info('Mobile verification credits awarded', [
                    'account_id' => $account->id,
                    'user_id' => $user->id,
                    'credits_awarded' => $creditsAwarded,
                ]);
            }

            // Log successful verification
            AuthAuditLog::logEvent([
                'actor_type' => 'customer_user',
                'actor_id' => $user->id,
                'actor_email' => $user->email,
                'tenant_id' => $user->tenant_id,
                'event_type' => 'mobile_verified',
                'result' => 'success',
                'ip_address' => $request->ip(),
                'metadata' => json_encode([
                    'credits_awarded' => $creditsAwarded,
                    'mfa_enabled' => true,
                ]),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Mobile number verified successfully. MFA has been enabled.',
                'data' => [
                    'mobile_verified' => true,
                    'mfa_enabled' => $user->mfa_enabled,
                    'credits_awarded' => $creditsAwarded,
                    'user' => $user->toPortalArray(),
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Verify mobile code error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }

    /**
     * Resend mobile verification code
     *
     * POST /api/auth/verify-mobile/resend
     */
    public function resendCode(Request $request)
    {
        // Reuse sendCode logic
        return $this->sendCode($request);
    }

    /**
     * Get mobile verification status
     *
     * GET /api/auth/verify-mobile/status
     */
    public function getStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required_without:auth|string|size:36',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get user (either from auth or user_id param)
            if ($request->user()) {
                $user = $request->user();
            } else {
                $user = User::withoutGlobalScope('tenant')->find($request->user_id);
            }

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            $remainingAttempts = $this->smsService->getRemainingAttempts($user->mobile_number);
            $timeUntilReset = $this->smsService->getTimeUntilReset($user->mobile_number);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'mobile_verified' => $user->hasMobileVerified(),
                    'mobile_number_hint' => $user->getFormattedMobileNumber(),
                    'code_expires_in_minutes' => SmsVerificationService::CODE_EXPIRY_MINUTES,
                    'remaining_attempts' => $remainingAttempts,
                    'time_until_reset_minutes' => $timeUntilReset,
                    'can_resend' => $user->canResendMobileCode(),
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get mobile verification status error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}
