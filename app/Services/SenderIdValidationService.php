<?php

namespace App\Services;

use App\Models\SenderId;
use App\Services\Admin\MessageEnforcementService;
use Illuminate\Support\Facades\Log;

/**
 * SenderID Validation Service
 *
 * Handles type-specific validation (Alpha, Numeric, Shortcode) and
 * anti-spoofing checks via the MessageEnforcementService normalisation engine.
 *
 * SECURITY: This is the gatekeeper that prevents fraudsters from registering
 * spoofed SenderIDs. All validation runs BEFORE the request enters the
 * approval workflow, providing a first line of defence.
 */
class SenderIdValidationService
{
    protected MessageEnforcementService $enforcementService;

    public function __construct(MessageEnforcementService $enforcementService)
    {
        $this->enforcementService = $enforcementService;
    }

    // =====================================================
    // TYPE-SPECIFIC VALIDATION
    // =====================================================

    /**
     * Validate a SenderID value based on its type
     *
     * @return array{valid: bool, errors: string[]}
     */
    public function validate(string $value, string $type): array
    {
        $errors = [];

        if (empty(trim($value))) {
            return ['valid' => false, 'errors' => ['SenderID value is required.']];
        }

        switch (strtoupper($type)) {
            case SenderId::TYPE_ALPHA:
                $errors = $this->validateAlpha($value);
                break;
            case SenderId::TYPE_NUMERIC:
                $errors = $this->validateNumeric($value);
                break;
            case SenderId::TYPE_SHORTCODE:
                $errors = $this->validateShortcode($value);
                break;
            default:
                $errors[] = "Invalid sender type: {$type}. Must be ALPHA, NUMERIC, or SHORTCODE.";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Alphanumeric SenderID validation
     * Max 11 characters, A-Z a-z 0-9 - _ & space
     */
    protected function validateAlpha(string $value): array
    {
        $errors = [];

        if (strlen($value) > 11) {
            $errors[] = 'Alphanumeric SenderID must be 11 characters or fewer.';
        }

        if (strlen($value) < 1) {
            $errors[] = 'Alphanumeric SenderID must be at least 1 character.';
        }

        if (!preg_match('/^[A-Za-z0-9\-_& ]+$/', $value)) {
            $errors[] = 'Alphanumeric SenderID may only contain letters, numbers, hyphens, underscores, ampersands, and spaces.';
        }

        // Must contain at least one letter (pure numeric would be a NUMERIC type)
        if (preg_match('/^[0-9]+$/', $value)) {
            $errors[] = 'Alphanumeric SenderID must contain at least one letter. Use Numeric type for number-only senders.';
        }

        return $errors;
    }

    /**
     * Numeric (UK VMN) SenderID validation
     * Exactly 12 digits, must start with 447
     */
    protected function validateNumeric(string $value): array
    {
        $errors = [];

        if (!preg_match('/^447\d{9}$/', $value)) {
            $errors[] = 'Numeric SenderID must be exactly 12 digits starting with 447 (UK international mobile format).';
        }

        return $errors;
    }

    /**
     * Shortcode SenderID validation
     * Exactly 5 digits, must start with 6, 7, or 8
     */
    protected function validateShortcode(string $value): array
    {
        $errors = [];

        if (!preg_match('/^[678]\d{4}$/', $value)) {
            $errors[] = 'Shortcode must be exactly 5 digits starting with 6, 7, or 8.';
        }

        return $errors;
    }

    // =====================================================
    // ANTI-SPOOFING CHECKS
    // =====================================================

    /**
     * Check a SenderID against anti-spoofing rules
     * Uses the MessageEnforcementService normalisation engine to detect:
     * - Exact matches against blocked patterns (HMRC, HSBC, NHS, etc.)
     * - Homoglyph attacks (Cyrillic/Greek character substitution)
     * - Leet-speak variations (5 for S, 0 for O, etc.)
     *
     * @return array{passed: bool, action: string|null, matched_rule: array|null, normalised: string, mapping_hits: array}
     */
    public function checkAntiSpoofing(string $value): array
    {
        $result = $this->enforcementService->testEnforcement('senderid', $value);

        $passed = $result['result'] === 'allow';

        if (!$passed) {
            Log::warning('[SenderIdValidation] Anti-spoofing check failed', [
                'input' => $value,
                'normalised' => $result['normalised'],
                'matched_rule' => $result['matchedRule'],
                'mapping_hits' => $result['mappingHits'],
            ]);
        }

        return [
            'passed' => $passed,
            'action' => $result['result'], // 'allow', 'block', 'quarantine'
            'matched_rule' => $result['matchedRule'],
            'normalised' => $result['normalised'],
            'mapping_hits' => $result['mappingHits'],
        ];
    }

    /**
     * Run full validation: type validation + anti-spoofing
     *
     * @return array{valid: bool, errors: string[], spoofing: array|null}
     */
    public function fullValidation(string $value, string $type): array
    {
        // Step 1: Type-specific format validation
        $typeValidation = $this->validate($value, $type);
        if (!$typeValidation['valid']) {
            return [
                'valid' => false,
                'errors' => $typeValidation['errors'],
                'spoofing' => null,
            ];
        }

        // Step 2: Anti-spoofing check (only for ALPHA - numeric/shortcode are carrier-assigned)
        $spoofing = null;
        if (strtoupper($type) === SenderId::TYPE_ALPHA) {
            $spoofing = $this->checkAntiSpoofing($value);
            if (!$spoofing['passed']) {
                $rule = $spoofing['matched_rule'];
                $action = $spoofing['action'];

                if ($action === 'block') {
                    return [
                        'valid' => false,
                        'errors' => [
                            "This SenderID is not permitted. It matches a protected pattern: " .
                            ($rule['name'] ?? 'blocked pattern') . "."
                        ],
                        'spoofing' => $spoofing,
                    ];
                }

                // 'quarantine' - allow submission but flag for enhanced review
                // The admin will see the spoofing data in the review
            }
        }

        return [
            'valid' => true,
            'errors' => [],
            'spoofing' => $spoofing,
        ];
    }
}
