<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SMS Verification Service
 *
 * Handles mobile number verification via SMS codes for:
 * - Signup mobile verification
 * - Login MFA codes
 * - Password reset notifications
 *
 * Uses system account (00000000-0000-0000-0000-000000000001) for sending
 * authentication SMS through QuickSMS platform's own infrastructure
 */
class SmsVerificationService
{
    /**
     * System account UUID for authentication SMS
     */
    const SYSTEM_ACCOUNT_ID = '00000000-0000-0000-0000-000000000001';

    /**
     * Rate limit: Max 3 codes per phone per hour
     */
    const RATE_LIMIT_MAX_ATTEMPTS = 3;
    const RATE_LIMIT_WINDOW_MINUTES = 60;

    /**
     * Code expiry: 10 minutes
     */
    const CODE_EXPIRY_MINUTES = 10;

    /**
     * Generate a random 6-digit verification code
     */
    public function generateCode(): string
    {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send mobile verification code via SMS
     *
     * @param User $user The user to send the code to
     * @param string $ipAddress IP address of the request (for rate limiting)
     * @return array ['success' => bool, 'message' => string, 'code' => string|null]
     */
    public function sendVerificationCode(User $user, string $ipAddress): array
    {
        // Check if user has a mobile number
        if (empty($user->mobile_number)) {
            return [
                'success' => false,
                'message' => 'Mobile number is required',
                'code' => null,
            ];
        }

        // Check rate limiting
        if (!$this->canSendCode($user->mobile_number, $ipAddress)) {
            $this->logAttempt($user->mobile_number, $ipAddress, $user->id, $user->tenant_id, 'rate_limited', 'Too many attempts');

            return [
                'success' => false,
                'message' => 'Too many verification attempts. Please try again in an hour.',
                'code' => null,
            ];
        }

        // Generate code
        $code = $user->generateMobileVerificationCode();

        // Send SMS via system account
        $smsSent = $this->sendSms($user->mobile_number, $code);

        if ($smsSent) {
            // Log successful attempt
            $this->logAttempt($user->mobile_number, $ipAddress, $user->id, $user->tenant_id, 'sent');

            Log::info('Mobile verification code sent', [
                'user_id' => $user->id,
                'mobile_number' => $user->mobile_number,
                'ip_address' => $ipAddress,
            ]);

            return [
                'success' => true,
                'message' => 'Verification code sent successfully',
                'code' => config('app.debug') ? $code : null, // Only return code in debug mode
            ];
        }

        // Log failed attempt
        $this->logAttempt($user->mobile_number, $ipAddress, $user->id, $user->tenant_id, 'failed', 'SMS sending failed');

        Log::error('Failed to send mobile verification code', [
            'user_id' => $user->id,
            'mobile_number' => $user->mobile_number,
            'ip_address' => $ipAddress,
        ]);

        return [
            'success' => false,
            'message' => 'Failed to send verification code. Please try again.',
            'code' => null,
        ];
    }

    /**
     * Verify mobile verification code
     *
     * @param User $user The user verifying the code
     * @param string $code The code to verify
     * @return array ['success' => bool, 'message' => string]
     */
    public function verifyCode(User $user, string $code): array
    {
        $verified = $user->verifyMobileCode($code);

        if ($verified) {
            Log::info('Mobile number verified successfully', [
                'user_id' => $user->id,
                'mobile_number' => $user->mobile_number,
            ]);

            return [
                'success' => true,
                'message' => 'Mobile number verified successfully',
            ];
        }

        Log::warning('Mobile verification code verification failed', [
            'user_id' => $user->id,
            'mobile_number' => $user->mobile_number,
        ]);

        return [
            'success' => false,
            'message' => 'Invalid or expired verification code',
        ];
    }

    /**
     * Check if a verification code can be sent (rate limiting)
     *
     * @param string $mobileNumber Normalized mobile number
     * @param string $ipAddress IP address of the request
     * @return bool True if code can be sent, false if rate limited
     */
    public function canSendCode(string $mobileNumber, string $ipAddress): bool
    {
        // Check attempts by mobile number
        $mobileAttempts = DB::table('mobile_verification_attempts')
            ->where('mobile_number', $mobileNumber)
            ->where('attempted_at', '>', now()->subMinutes(self::RATE_LIMIT_WINDOW_MINUTES))
            ->count();

        if ($mobileAttempts >= self::RATE_LIMIT_MAX_ATTEMPTS) {
            return false;
        }

        // Check attempts by IP address (prevent abuse)
        $ipAttempts = DB::table('mobile_verification_attempts')
            ->where('ip_address', $ipAddress)
            ->where('attempted_at', '>', now()->subMinutes(self::RATE_LIMIT_WINDOW_MINUTES))
            ->count();

        if ($ipAttempts >= self::RATE_LIMIT_MAX_ATTEMPTS * 3) { // Allow 3x more per IP (for shared IPs)
            return false;
        }

        return true;
    }

    /**
     * Log verification attempt
     *
     * @param string $mobileNumber Normalized mobile number
     * @param string $ipAddress IP address
     * @param string|null $userId User ID (if authenticated)
     * @param string|null $accountId Account ID (if authenticated)
     * @param string $result Result: 'sent', 'rate_limited', or 'failed'
     * @param string|null $failureReason Reason if failed
     */
    protected function logAttempt(
        string $mobileNumber,
        string $ipAddress,
        ?string $userId = null,
        ?string $accountId = null,
        string $result = 'sent',
        ?string $failureReason = null
    ): void {
        DB::table('mobile_verification_attempts')->insert([
            'mobile_number' => $mobileNumber,
            'ip_address' => $ipAddress,
            'user_id' => $userId ? hex2bin(str_replace('-', '', $userId)) : null,
            'account_id' => $accountId ? hex2bin(str_replace('-', '', $accountId)) : null,
            'attempted_at' => now(),
            'result' => $result,
            'failure_reason' => $failureReason,
        ]);
    }

    /**
     * Send SMS via system account routing
     *
     * TODO: Integrate with QuickSMS platform's SMS sending infrastructure
     * This is a placeholder that needs to be connected to the actual SMS gateway
     *
     * @param string $mobileNumber Normalized mobile number (447XXXXXXXXX)
     * @param string $code 6-digit verification code
     * @return bool True if sent successfully
     */
    protected function sendSms(string $mobileNumber, string $code): bool
    {
        $message = "Your QuickSMS verification code is: {$code}. This code will expire in " . self::CODE_EXPIRY_MINUTES . " minutes.";

        // TODO: Replace this with actual SMS sending logic
        // This should use the system account (SYSTEM_ACCOUNT_ID) to send via platform's infrastructure
        //
        // Example integration:
        // return SmsGateway::sendViaPlatform([
        //     'from_account_id' => self::SYSTEM_ACCOUNT_ID,
        //     'to' => $mobileNumber,
        //     'message' => $message,
        //     'type' => 'verification',
        // ]);

        // For now, log the code (in production, this would actually send SMS)
        Log::info('SMS Verification Code (DEMO MODE)', [
            'mobile_number' => $mobileNumber,
            'code' => $code,
            'message' => $message,
        ]);

        // In demo mode, always return true
        // In production, return actual SMS gateway result
        return true;
    }

    /**
     * Clean up old verification attempts (for scheduled cleanup)
     *
     * Removes attempts older than 24 hours
     */
    public function cleanupOldAttempts(): int
    {
        $deleted = DB::table('mobile_verification_attempts')
            ->where('attempted_at', '<', now()->subHours(24))
            ->delete();

        Log::info('Cleaned up old mobile verification attempts', [
            'records_deleted' => $deleted,
        ]);

        return $deleted;
    }

    /**
     * Get remaining attempts for a mobile number
     *
     * @param string $mobileNumber Normalized mobile number
     * @return int Number of attempts remaining in current window
     */
    public function getRemainingAttempts(string $mobileNumber): int
    {
        $attempts = DB::table('mobile_verification_attempts')
            ->where('mobile_number', $mobileNumber)
            ->where('attempted_at', '>', now()->subMinutes(self::RATE_LIMIT_WINDOW_MINUTES))
            ->count();

        return max(0, self::RATE_LIMIT_MAX_ATTEMPTS - $attempts);
    }

    /**
     * Get time until rate limit resets
     *
     * @param string $mobileNumber Normalized mobile number
     * @return int|null Minutes until reset, or null if not rate limited
     */
    public function getTimeUntilReset(string $mobileNumber): ?int
    {
        $oldestAttempt = DB::table('mobile_verification_attempts')
            ->where('mobile_number', $mobileNumber)
            ->where('attempted_at', '>', now()->subMinutes(self::RATE_LIMIT_WINDOW_MINUTES))
            ->orderBy('attempted_at', 'asc')
            ->first();

        if (!$oldestAttempt) {
            return null;
        }

        $resetTime = now()->parse($oldestAttempt->attempted_at)->addMinutes(self::RATE_LIMIT_WINDOW_MINUTES);
        return max(0, now()->diffInMinutes($resetTime, false));
    }
}
