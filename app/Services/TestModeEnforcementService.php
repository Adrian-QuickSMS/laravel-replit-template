<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountCredit;
use App\Models\SenderId;
use Illuminate\Support\Facades\Log;

/**
 * Test Mode Enforcement Service
 *
 * Enforces the rules matrix for Test Standard and Test Dynamic accounts.
 * This service is the SINGLE SOURCE OF TRUTH for all test mode restrictions.
 *
 * ┌─────────────────┬──────────────────────┬──────────────────────┐
 * │ Feature         │ Test Standard        │ Test Dynamic         │
 * ├─────────────────┼──────────────────────┼──────────────────────┤
 * │ Message Limit   │ 100 fragments        │ 100 fragments        │
 * │                 │ (test credits)       │ (admin can add more) │
 * ├─────────────────┼──────────────────────┼──────────────────────┤
 * │ Recipients      │ Approved test        │ Any valid mobile     │
 * │                 │ numbers only         │ number               │
 * ├─────────────────┼──────────────────────┼──────────────────────┤
 * │ SenderID        │ "QuickSMS Test       │ Any senderID or an   │
 * │                 │ Sender" or approved  │ API/Portal/email-to- │
 * │                 │ via SenderID Tool    │ sms passing validation│
 * ├─────────────────┼──────────────────────┼──────────────────────┤
 * │ Message Content │ Disclaimer prepended │ Customer content     │
 * │                 │                      │ only (no disclaimer) │
 * └─────────────────┴──────────────────────┴──────────────────────┘
 *
 * SECURITY: Called at the message-sending layer (API, Portal, Email-to-SMS)
 * to enforce test mode constraints BEFORE messages reach the gateway.
 */
class TestModeEnforcementService
{
    public function __construct(
        private SenderIdValidationService $senderIdValidator,
    ) {}

    /**
     * Run all test mode pre-flight checks for a message.
     *
     * @param Account $account    The sending account
     * @param string  $recipient  The recipient number (E.164)
     * @param string  $senderId   The sender ID string
     * @param string  $content    The raw message content
     * @return TestModeResult
     */
    public function enforce(Account $account, string $recipient, string $senderId, string $content): TestModeResult
    {
        // Live accounts skip all test enforcement
        if ($account->isLiveMode()) {
            return TestModeResult::pass($content);
        }

        // Non-test, non-live accounts cannot send
        if (!$account->isTestMode()) {
            return TestModeResult::fail(
                'Account is not in a sendable state. Current status: ' . $account->status
            );
        }

        // Check 1: Test credits available (read-only check for fast fail)
        $creditCheck = $this->checkTestCredits($account, $content);
        if (!$creditCheck['allowed']) {
            return TestModeResult::fail($creditCheck['reason']);
        }

        // Check 2: Recipient validation
        $recipientCheck = $this->checkRecipient($account, $recipient);
        if (!$recipientCheck['allowed']) {
            return TestModeResult::fail($recipientCheck['reason']);
        }

        // Check 3: SenderID validation
        $senderIdCheck = $this->checkSenderId($account, $senderId);
        if (!$senderIdCheck['allowed']) {
            return TestModeResult::fail($senderIdCheck['reason']);
        }

        // Check 4: Apply content rules (disclaimer for Test Standard)
        $finalContent = $this->applyContentRules($account, $content);

        // Check 5: Atomically deduct test credits (prevents race condition
        // where concurrent requests both pass the read-only check above)
        if (!$account->deductTestCredits($creditCheck['fragments_required'])) {
            return TestModeResult::fail(
                'Insufficient test credits (concurrent usage detected). Please retry.'
            );
        }

        return TestModeResult::pass(
            $finalContent,
            $creditCheck['fragments_required'],
            $account->isTestStandard() // disclaimer_applied
        );
    }

    /**
     * Check if account has sufficient test credits.
     *
     * @return array{allowed: bool, reason: string|null, fragments_required: int}
     */
    public function checkTestCredits(Account $account, string $content): array
    {
        $availableCredits = $account->getAvailableCredits();

        // Calculate fragments needed
        $finalContent = $this->applyContentRules($account, $content);
        $fragmentsRequired = $this->calculateFragments($finalContent);

        if ($availableCredits < $fragmentsRequired) {
            $message = $account->isTestStandard()
                ? "Insufficient test credits. You have {$availableCredits} fragments remaining, but this message requires {$fragmentsRequired}. Contact support or activate your account."
                : "Insufficient test credits. You have {$availableCredits} fragments remaining, but this message requires {$fragmentsRequired}. Contact support for additional credits or activate your account.";

            return [
                'allowed' => false,
                'reason' => $message,
                'fragments_required' => $fragmentsRequired,
            ];
        }

        return [
            'allowed' => true,
            'reason' => null,
            'fragments_required' => $fragmentsRequired,
        ];
    }

    /**
     * Validate recipient number based on account status.
     *
     * Test Standard: Only approved test numbers
     * Test Dynamic: Any valid mobile number
     *
     * @return array{allowed: bool, reason: string|null}
     */
    public function checkRecipient(Account $account, string $recipient): array
    {
        if ($account->isTestDynamic()) {
            // Test Dynamic: any valid mobile number
            if (!$this->isValidMobileNumber($recipient)) {
                return [
                    'allowed' => false,
                    'reason' => "Invalid mobile number format: {$recipient}",
                ];
            }
            return ['allowed' => true, 'reason' => null];
        }

        // Test Standard: approved test numbers only
        if ($account->isTestStandard()) {
            if (!$this->isApprovedTestNumber($account, $recipient)) {
                return [
                    'allowed' => false,
                    'reason' => "Test Standard accounts can only send to approved test numbers. "
                        . "Add '{$recipient}' to your approved test numbers via the portal, "
                        . "or upgrade to Test Dynamic for unrestricted recipients.",
                ];
            }
            return ['allowed' => true, 'reason' => null];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Validate sender ID based on account status.
     *
     * Test Standard: "QuickSMS Test Sender" or approved SenderIDs via SenderID Tool
     * Test Dynamic: Any senderID passing validation (API, Portal, email-to-sms)
     *
     * @return array{allowed: bool, reason: string|null}
     */
    public function checkSenderId(Account $account, string $senderId): array
    {
        if ($account->isTestDynamic()) {
            // Test Dynamic: any sender ID that passes basic format validation
            // Detect type: numeric if all digits, otherwise alpha
            $type = ctype_digit($senderId) ? SenderId::TYPE_NUMERIC : SenderId::TYPE_ALPHA;
            $validation = $this->senderIdValidator->validate($senderId, $type);
            if (!$validation['valid']) {
                return [
                    'allowed' => false,
                    'reason' => "SenderID '{$senderId}' failed validation: " . implode(', ', $validation['errors']),
                ];
            }
            return ['allowed' => true, 'reason' => null];
        }

        // Test Standard: must be "QuickSMS Test Sender" or an approved/registered SenderID
        if ($account->isTestStandard()) {
            // Always allow the default test sender
            if ($senderId === 'QuickSMS' || $senderId === 'QuickSMS Test Sender') {
                return ['allowed' => true, 'reason' => null];
            }

            // Check if this is an approved SenderID for the account
            $approved = SenderId::withoutGlobalScope('tenant')
                ->where('account_id', $account->id)
                ->where('sender_id_value', $senderId)
                ->where('workflow_status', SenderId::STATUS_APPROVED)
                ->exists();

            if (!$approved) {
                return [
                    'allowed' => false,
                    'reason' => "Test Standard accounts can only use 'QuickSMS Test Sender' or "
                        . "SenderIDs approved via the SenderID registration tool. "
                        . "'{$senderId}' is not approved for this account.",
                ];
            }

            return ['allowed' => true, 'reason' => null];
        }

        return ['allowed' => true, 'reason' => null];
    }

    /**
     * Apply content rules based on account status.
     *
     * Test Standard: Prepend disclaimer
     * Test Dynamic: No modification (customer content only)
     */
    public function applyContentRules(Account $account, string $content): string
    {
        if ($account->requiresTestDisclaimer()) {
            return Account::TEST_DISCLAIMER . ' ' . $content;
        }

        return $content;
    }

    /**
     * Get the effective message content with any modifications applied.
     * Used by UI to show accurate fragment count.
     */
    public function getEffectiveContent(Account $account, string $content): array
    {
        $finalContent = $this->applyContentRules($account, $content);
        $fragments = $this->calculateFragments($finalContent);

        return [
            'original_content' => $content,
            'final_content' => $finalContent,
            'disclaimer_applied' => $account->requiresTestDisclaimer(),
            'disclaimer_text' => $account->requiresTestDisclaimer() ? Account::TEST_DISCLAIMER : null,
            'disclaimer_length' => $account->requiresTestDisclaimer() ? Account::TEST_DISCLAIMER_LENGTH + 1 : 0, // +1 for space
            'total_length' => mb_strlen($finalContent),
            'fragments' => $fragments,
            'customer_chars_remaining_single_fragment' => $account->requiresTestDisclaimer()
                ? max(0, 160 - Account::TEST_DISCLAIMER_LENGTH - 1) // -1 for space separator
                : 160,
        ];
    }

    /**
     * Check if a number is an approved test number for the account.
     */
    protected function isApprovedTestNumber(Account $account, string $number): bool
    {
        // Check account_settings or a dedicated test_numbers table
        $settings = $account->settings;

        if (!$settings) {
            return false;
        }

        $testNumbers = $settings->approved_test_numbers ?? [];

        if (empty($testNumbers)) {
            return false;
        }

        // Normalize and check
        $normalized = $this->normalizeNumber($number);
        foreach ($testNumbers as $approved) {
            if ($this->normalizeNumber($approved) === $normalized) {
                return true;
            }
        }

        return false;
    }

    /**
     * Basic mobile number validation (E.164 format).
     */
    protected function isValidMobileNumber(string $number): bool
    {
        // E.164: + followed by 1-15 digits
        return (bool) preg_match('/^\+?[1-9]\d{6,14}$/', $number);
    }

    /**
     * Normalize a phone number to consistent format.
     *
     * Handles: +447xxx, 07xxx, 00447xxx, 447xxx, and numbers with whitespace/punctuation.
     */
    protected function normalizeNumber(string $number): string
    {
        // Strip spaces, dashes, parentheses
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $number);

        // Strip leading +
        $cleaned = ltrim($cleaned, '+');

        // Convert international dialling prefix (00) to bare country code
        // e.g. 00447700900123 → 447700900123
        if (str_starts_with($cleaned, '00')) {
            $cleaned = substr($cleaned, 2);
        }
        // Convert UK local format (07xxx) to international (447xxx)
        elseif (str_starts_with($cleaned, '0')) {
            $cleaned = '44' . substr($cleaned, 1);
        }

        return $cleaned;
    }

    /**
     * Calculate number of SMS fragments for a message.
     *
     * GSM-7: 160 chars for single, 153 per fragment for multipart
     * UCS-2: 70 chars for single, 67 per fragment for multipart
     */
    protected function calculateFragments(string $content): int
    {
        $length = mb_strlen($content);

        if ($length === 0) {
            return 0;
        }

        $isGsm7 = $this->isGsm7($content);

        if ($isGsm7) {
            return $length <= 160 ? 1 : (int) ceil($length / 153);
        }

        // UCS-2
        return $length <= 70 ? 1 : (int) ceil($length / 67);
    }

    /**
     * Check if message content is GSM-7 compatible.
     */
    protected function isGsm7(string $content): bool
    {
        // GSM-7 basic character set + extension
        $gsm7Pattern = '/^[@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ\x1BÆæßÉ !"#¤%&\'()*+,\-.\/:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà\d\^{}\[\]~|€]*$/u';
        return (bool) preg_match($gsm7Pattern, $content);
    }
}

/**
 * Result of test mode enforcement check.
 */
class TestModeResult
{
    public function __construct(
        public readonly bool $allowed,
        public readonly string $finalContent,
        public readonly ?string $reason,
        public readonly int $fragmentsRequired,
        public readonly bool $disclaimerApplied,
    ) {}

    public static function pass(string $finalContent, int $fragments = 0, bool $disclaimerApplied = false): self
    {
        return new self(
            allowed: true,
            finalContent: $finalContent,
            reason: null,
            fragmentsRequired: $fragments,
            disclaimerApplied: $disclaimerApplied,
        );
    }

    public static function fail(string $reason): self
    {
        return new self(
            allowed: false,
            finalContent: '',
            reason: $reason,
            fragmentsRequired: 0,
            disclaimerApplied: false,
        );
    }
}
