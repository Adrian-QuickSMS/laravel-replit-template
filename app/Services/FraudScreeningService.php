<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountFlags;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Fraud Screening Service
 *
 * Orchestrates fraud/identity checks during signup and payment.
 * Uses a two-layer approach:
 *
 * 1. Stripe Radar - Payment fraud detection (on payment events)
 * 2. Third-party scoring API - Identity/signup fraud scoring
 *
 * Flow:
 * - Customer signs up → account enters 'pending_verification'
 * - Customer sees "processing" state (fraud check is invisible)
 * - FraudScreeningService scores the signup
 * - Score PASS → account transitions to test_standard or test_dynamic
 * - Score REVIEW → admin notified, account stays pending_verification
 * - Score FAIL → account stays pending_verification, admin notified
 *
 * SECURITY: This service is RED SIDE only. Scores, reasons, and
 * internal decisions are NEVER exposed to the customer portal.
 */
class FraudScreeningService
{
    /**
     * Score thresholds (0-100 scale, higher = more suspicious)
     */
    private const SCORE_AUTO_APPROVE = 30;  // ≤30 = auto-approve
    private const SCORE_MANUAL_REVIEW = 70; // 31-70 = manual review
    // >70 = auto-reject (stays pending, admin notified)

    /**
     * Screen a new account signup for fraud.
     *
     * Called after signup form submission. The customer sees a "processing"
     * state while this runs asynchronously.
     *
     * @param Account $account       The newly created account
     * @param array   $signupData    Signup context (IP, email, phone, etc.)
     * @param string  $targetStatus  Which test mode to enter (test_standard or test_dynamic)
     * @return FraudScreeningResult
     */
    public function screenSignup(Account $account, array $signupData, string $targetStatus = Account::STATUS_TEST_STANDARD): FraudScreeningResult
    {
        Log::info('[FraudScreening] Screening signup', [
            'account_id' => $account->id,
            'email' => $signupData['email'] ?? null,
            'ip' => $signupData['ip_address'] ?? null,
        ]);

        try {
            $score = $this->getThirdPartyScore($signupData);
        } catch (\Exception $e) {
            Log::error('[FraudScreening] Third-party scoring failed, defaulting to manual review', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            // Fail safe: if scoring service is down, require manual review
            $this->flagForReview($account, 'scoring_service_unavailable', 50);
            return FraudScreeningResult::review($account->id, 50, 'Scoring service unavailable - manual review required');
        }

        // Record the score on account flags (RED SIDE only)
        $this->recordScore($account, $score);

        // Auto-approve: low risk
        if ($score['risk_score'] <= self::SCORE_AUTO_APPROVE) {
            $this->approveAccount($account, $targetStatus, $score);
            return FraudScreeningResult::approved($account->id, $score['risk_score']);
        }

        // Auto-reject: very high risk
        if ($score['risk_score'] > self::SCORE_MANUAL_REVIEW) {
            $this->rejectAccount($account, $score);
            return FraudScreeningResult::rejected($account->id, $score['risk_score'], $score['reason'] ?? 'High fraud risk');
        }

        // Manual review: moderate risk
        $this->flagForReview($account, $score['reason'] ?? 'Moderate risk score', $score['risk_score']);
        return FraudScreeningResult::review($account->id, $score['risk_score'], $score['reason'] ?? 'Manual review required');
    }

    /**
     * Admin approves a flagged account after manual review.
     */
    public function adminApprove(Account $account, string $adminId, string $targetStatus = Account::STATUS_TEST_STANDARD): void
    {
        if (!in_array($targetStatus, Account::TEST_STATUSES)) {
            throw new \InvalidArgumentException("Target status must be a test status, got: {$targetStatus}");
        }

        $account->transitionTo($targetStatus);

        // Update flags
        $flags = $account->flags;
        if ($flags) {
            $flags->update([
                'fraud_review_status' => 'approved',
                'fraud_reviewed_by' => $adminId,
                'fraud_reviewed_at' => now(),
            ]);
        }

        Log::info('[FraudScreening] Admin approved account', [
            'account_id' => $account->id,
            'admin_id' => $adminId,
            'target_status' => $targetStatus,
        ]);
    }

    /**
     * Admin rejects a flagged account.
     */
    public function adminReject(Account $account, string $adminId, string $reason): void
    {
        $account->transitionTo(Account::STATUS_CLOSED);

        $flags = $account->flags;
        if ($flags) {
            $flags->update([
                'fraud_review_status' => 'rejected',
                'fraud_reviewed_by' => $adminId,
                'fraud_reviewed_at' => now(),
                'fraud_rejection_reason' => $reason,
            ]);
        }

        Log::info('[FraudScreening] Admin rejected account', [
            'account_id' => $account->id,
            'admin_id' => $adminId,
            'reason' => $reason,
        ]);
    }

    /**
     * Screen a payment event via Stripe Radar.
     * Called by Stripe webhook handler.
     *
     * @param Account $account
     * @param array   $stripeEvent  The Stripe event payload
     * @return FraudScreeningResult
     */
    public function screenPayment(Account $account, array $stripeEvent): FraudScreeningResult
    {
        $riskLevel = $stripeEvent['data']['object']['outcome']['risk_level'] ?? 'unknown';
        $riskScore = $stripeEvent['data']['object']['outcome']['risk_score'] ?? null;

        Log::info('[FraudScreening] Stripe Radar payment screening', [
            'account_id' => $account->id,
            'risk_level' => $riskLevel,
            'risk_score' => $riskScore,
        ]);

        if ($riskLevel === 'elevated' || $riskLevel === 'highest') {
            $this->flagForReview($account, "Stripe Radar: {$riskLevel}", $riskScore ?? 75);
            return FraudScreeningResult::review($account->id, $riskScore ?? 75, "Stripe Radar flagged: {$riskLevel}");
        }

        return FraudScreeningResult::approved($account->id, $riskScore ?? 0);
    }

    /**
     * Call the third-party fraud scoring API.
     *
     * @param array $signupData
     * @return array{risk_score: int, reason: string|null, signals: array}
     */
    protected function getThirdPartyScore(array $signupData): array
    {
        $apiKey = config('services.fraud_scoring.api_key');
        $apiUrl = config('services.fraud_scoring.url');

        if (!$apiKey || !$apiUrl) {
            Log::warning('[FraudScreening] Third-party scoring not configured, returning neutral score');
            return [
                'risk_score' => 50, // Neutral = manual review
                'reason' => 'Fraud scoring not configured',
                'signals' => [],
            ];
        }

        $response = Http::timeout(10)
            ->withHeaders(['Authorization' => "Bearer {$apiKey}"])
            ->post($apiUrl, [
                'email' => $signupData['email'] ?? null,
                'phone' => $signupData['phone'] ?? null,
                'ip_address' => $signupData['ip_address'] ?? null,
                'company_name' => $signupData['company_name'] ?? null,
                'country' => $signupData['country'] ?? null,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException("Fraud scoring API returned {$response->status()}");
        }

        $data = $response->json();

        return [
            'risk_score' => (int) ($data['score'] ?? $data['risk_score'] ?? 50),
            'reason' => $data['reason'] ?? $data['summary'] ?? null,
            'signals' => $data['signals'] ?? $data['factors'] ?? [],
        ];
    }

    /**
     * Record fraud score on account flags (RED SIDE).
     */
    protected function recordScore(Account $account, array $score): void
    {
        $flags = $account->flags ?? AccountFlags::create(['account_id' => $account->id]);

        $flags->update([
            'fraud_risk_score' => $score['risk_score'],
            'fraud_risk_signals' => json_encode($score['signals'] ?? []),
            'fraud_screened_at' => now(),
        ]);
    }

    /**
     * Auto-approve: transition account to target test status.
     */
    protected function approveAccount(Account $account, string $targetStatus, array $score): void
    {
        $account->transitionTo($targetStatus);

        $flags = $account->flags;
        if ($flags) {
            $flags->update([
                'fraud_review_status' => 'auto_approved',
                'fraud_reviewed_at' => now(),
            ]);
        }

        Log::info('[FraudScreening] Account auto-approved', [
            'account_id' => $account->id,
            'score' => $score['risk_score'],
            'target_status' => $targetStatus,
        ]);
    }

    /**
     * Auto-reject: keep account in pending_verification, notify admin.
     */
    protected function rejectAccount(Account $account, array $score): void
    {
        // Account stays in pending_verification
        $flags = $account->flags ?? AccountFlags::create(['account_id' => $account->id]);

        $flags->update([
            'fraud_risk_level' => 'high',
            'fraud_review_status' => 'auto_rejected',
            'fraud_reviewed_at' => now(),
            'under_investigation' => true,
        ]);

        $this->notifyAdmin($account, 'auto_rejected', $score);

        Log::warning('[FraudScreening] Account auto-rejected', [
            'account_id' => $account->id,
            'score' => $score['risk_score'],
        ]);
    }

    /**
     * Flag account for manual admin review.
     */
    protected function flagForReview(Account $account, string $reason, int $score): void
    {
        $flags = $account->flags ?? AccountFlags::create(['account_id' => $account->id]);

        $flags->update([
            'fraud_risk_level' => $score > self::SCORE_MANUAL_REVIEW ? 'high' : 'medium',
            'fraud_review_status' => 'pending_review',
            'fraud_risk_score' => $score,
        ]);

        $this->notifyAdmin($account, 'pending_review', [
            'risk_score' => $score,
            'reason' => $reason,
        ]);

        Log::info('[FraudScreening] Account flagged for manual review', [
            'account_id' => $account->id,
            'score' => $score,
            'reason' => $reason,
        ]);
    }

    /**
     * Notify admin(s) about a fraud screening result.
     *
     * TODO: Implement via AdminNotification model / email / Slack webhook
     */
    protected function notifyAdmin(Account $account, string $type, array $context): void
    {
        // Placeholder - will be implemented with notification system
        Log::channel('admin')->info('[FraudScreening] Admin notification', [
            'type' => $type,
            'account_id' => $account->id,
            'account_number' => $account->account_number,
            'company_name' => $account->company_name,
            'context' => $context,
        ]);
    }
}

/**
 * Result of a fraud screening check.
 */
class FraudScreeningResult
{
    public function __construct(
        public readonly string $decision,   // 'approved', 'review', 'rejected'
        public readonly string $accountId,
        public readonly int $riskScore,
        public readonly ?string $reason,
    ) {}

    public static function approved(string $accountId, int $score): self
    {
        return new self('approved', $accountId, $score, null);
    }

    public static function review(string $accountId, int $score, string $reason): self
    {
        return new self('review', $accountId, $score, $reason);
    }

    public static function rejected(string $accountId, int $score, string $reason): self
    {
        return new self('rejected', $accountId, $score, $reason);
    }

    public function isApproved(): bool
    {
        return $this->decision === 'approved';
    }

    public function requiresReview(): bool
    {
        return $this->decision === 'review';
    }

    public function isRejected(): bool
    {
        return $this->decision === 'rejected';
    }
}
