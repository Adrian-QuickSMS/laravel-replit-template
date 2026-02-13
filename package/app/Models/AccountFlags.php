<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RED SIDE: Account Flags (Internal Risk/Status)
 *
 * DATA CLASSIFICATION: Restricted - Internal Operations
 * SIDE: RED (never accessible to customer portal)
 * TENANT ISOLATION: References tenant via account_id
 *
 * SECURITY NOTES:
 * - Contains fraud risk scores, payment status, compliance flags
 * - Portal roles: NO ACCESS
 * - Accessed by internal services only (svc_red role)
 * - Customers NEVER see these flags
 * - Affects platform behavior (rate limiting, message routing, etc)
 */
class AccountFlags extends Model
{
    protected $table = 'account_flags';

    // account_id is primary key (one-to-one with accounts)
    protected $primaryKey = 'account_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'fraud_risk_level',
        'fraud_score',
        'under_investigation',
        'investigation_notes',
        'payment_status',
        'outstanding_balance',
        'last_payment_date',
        'daily_message_limit',
        'messages_sent_today',
        'limit_reset_date',
        'api_rate_limit_per_minute',
        'rate_limit_exceeded',
        'rate_limit_reset_at',
        'kyc_completed',
        'aml_check_passed',
        'last_compliance_review',
        'deliverability_issues',
        'spam_complaint_rate',
        'consecutive_failed_sends',
        'updated_by',
    ];

    protected $casts = [
        'account_id' => 'string',
        'fraud_score' => 'integer',
        'under_investigation' => 'boolean',
        'outstanding_balance' => 'decimal:2',
        'last_payment_date' => 'date',
        'daily_message_limit' => 'integer',
        'messages_sent_today' => 'integer',
        'limit_reset_date' => 'date',
        'api_rate_limit_per_minute' => 'integer',
        'rate_limit_exceeded' => 'boolean',
        'rate_limit_reset_at' => 'datetime',
        'kyc_completed' => 'boolean',
        'aml_check_passed' => 'boolean',
        'last_compliance_review' => 'datetime',
        'deliverability_issues' => 'boolean',
        'spam_complaint_rate' => 'decimal:2',
        'consecutive_failed_sends' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * The account these flags belong to
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope: High or critical fraud risk
     */
    public function scopeHighRisk($query)
    {
        return $query->whereIn('fraud_risk_level', ['high', 'critical']);
    }

    /**
     * Scope: Under investigation
     */
    public function scopeUnderInvestigation($query)
    {
        return $query->where('under_investigation', true);
    }

    /**
     * Scope: Payment issues
     */
    public function scopePaymentIssues($query)
    {
        return $query->whereIn('payment_status', ['overdue', 'suspended', 'collections']);
    }

    /**
     * Scope: Rate limited accounts
     */
    public function scopeRateLimited($query)
    {
        return $query->where('rate_limit_exceeded', true);
    }

    /**
     * Scope: Compliance issues
     */
    public function scopeComplianceIssues($query)
    {
        return $query->where(function($q) {
            $q->where('kyc_completed', false)
              ->orWhere('aml_check_passed', false);
        });
    }

    /**
     * Scope: Deliverability problems
     */
    public function scopeDeliverabilityIssues($query)
    {
        return $query->where('deliverability_issues', true);
    }

    // =====================================================
    // FRAUD & RISK METHODS
    // =====================================================

    /**
     * Check if account is high risk
     */
    public function isHighRisk(): bool
    {
        return in_array($this->fraud_risk_level, ['high', 'critical']);
    }

    /**
     * Check if account is under investigation
     */
    public function isUnderInvestigation(): bool
    {
        return $this->under_investigation === true;
    }

    /**
     * Update fraud score
     */
    public function updateFraudScore(int $score, string $updatedBy): bool
    {
        $level = match(true) {
            $score >= 80 => 'critical',
            $score >= 60 => 'high',
            $score >= 30 => 'medium',
            default => 'low',
        };

        return $this->update([
            'fraud_score' => $score,
            'fraud_risk_level' => $level,
            'updated_by' => $updatedBy,
        ]);
    }

    /**
     * Flag for investigation
     */
    public function flagForInvestigation(string $reason, string $updatedBy): bool
    {
        return $this->update([
            'under_investigation' => true,
            'investigation_notes' => $reason,
            'updated_by' => $updatedBy,
        ]);
    }

    /**
     * Clear investigation flag
     */
    public function clearInvestigation(string $updatedBy): bool
    {
        return $this->update([
            'under_investigation' => false,
            'investigation_notes' => null,
            'updated_by' => $updatedBy,
        ]);
    }

    // =====================================================
    // PAYMENT METHODS
    // =====================================================

    /**
     * Check if account has payment issues
     */
    public function hasPaymentIssues(): bool
    {
        return in_array($this->payment_status, ['overdue', 'suspended', 'collections']);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(string $status, float $balance, string $updatedBy): bool
    {
        return $this->update([
            'payment_status' => $status,
            'outstanding_balance' => $balance,
            'last_payment_date' => $status === 'current' ? now() : $this->last_payment_date,
            'updated_by' => $updatedBy,
        ]);
    }

    // =====================================================
    // RATE LIMITING METHODS
    // =====================================================

    /**
     * Check if daily message limit reached
     */
    public function isDailyLimitReached(): bool
    {
        // Reset counter if date changed
        if ($this->limit_reset_date && $this->limit_reset_date->isToday() === false) {
            $this->resetDailyMessageCount();
        }

        return $this->messages_sent_today >= $this->daily_message_limit;
    }

    /**
     * Increment daily message count
     */
    public function incrementMessageCount(): bool
    {
        // Reset counter if date changed
        if ($this->limit_reset_date && $this->limit_reset_date->isToday() === false) {
            $this->resetDailyMessageCount();
        }

        return $this->increment('messages_sent_today');
    }

    /**
     * Reset daily message count
     */
    public function resetDailyMessageCount(): bool
    {
        return $this->update([
            'messages_sent_today' => 0,
            'limit_reset_date' => now()->toDateString(),
        ]);
    }

    /**
     * Check if API rate limit exceeded
     */
    public function isRateLimitExceeded(): bool
    {
        if (!$this->rate_limit_exceeded) {
            return false;
        }

        // Reset if reset time passed
        if ($this->rate_limit_reset_at && $this->rate_limit_reset_at->isPast()) {
            $this->update([
                'rate_limit_exceeded' => false,
                'rate_limit_reset_at' => null,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Set rate limit exceeded
     */
    public function setRateLimitExceeded(int $resetMinutes = 15): bool
    {
        return $this->update([
            'rate_limit_exceeded' => true,
            'rate_limit_reset_at' => now()->addMinutes($resetMinutes),
        ]);
    }

    /**
     * Update daily message limit
     */
    public function updateDailyLimit(int $limit, string $updatedBy): bool
    {
        return $this->update([
            'daily_message_limit' => $limit,
            'updated_by' => $updatedBy,
        ]);
    }

    // =====================================================
    // COMPLIANCE METHODS
    // =====================================================

    /**
     * Check if KYC completed
     */
    public function isKycCompleted(): bool
    {
        return $this->kyc_completed === true;
    }

    /**
     * Check if AML check passed
     */
    public function isAmlCheckPassed(): bool
    {
        return $this->aml_check_passed === true;
    }

    /**
     * Update KYC status
     */
    public function updateKycStatus(bool $completed, string $updatedBy): bool
    {
        return $this->update([
            'kyc_completed' => $completed,
            'last_compliance_review' => now(),
            'updated_by' => $updatedBy,
        ]);
    }

    /**
     * Update AML status
     */
    public function updateAmlStatus(bool $passed, string $updatedBy): bool
    {
        return $this->update([
            'aml_check_passed' => $passed,
            'last_compliance_review' => now(),
            'updated_by' => $updatedBy,
        ]);
    }

    // =====================================================
    // DELIVERABILITY METHODS
    // =====================================================

    /**
     * Check if deliverability issues flagged
     */
    public function hasDeliverabilityIssues(): bool
    {
        return $this->deliverability_issues === true;
    }

    /**
     * Increment failed send count
     */
    public function incrementFailedSends(): bool
    {
        $this->increment('consecutive_failed_sends');

        // Flag if more than 10 consecutive failures
        if ($this->consecutive_failed_sends >= 10) {
            $this->update(['deliverability_issues' => true]);
        }

        return true;
    }

    /**
     * Reset failed send count
     */
    public function resetFailedSends(): bool
    {
        return $this->update([
            'consecutive_failed_sends' => 0,
            'deliverability_issues' => false,
        ]);
    }

    /**
     * Update spam complaint rate
     */
    public function updateSpamRate(float $rate, string $updatedBy): bool
    {
        return $this->update([
            'spam_complaint_rate' => $rate,
            'deliverability_issues' => $rate > 0.1, // Flag if > 0.1%
            'updated_by' => $updatedBy,
        ]);
    }

    // =====================================================
    // INTERNAL USE ONLY - NEVER EXPOSE TO PORTAL
    // =====================================================

    /**
     * Format for internal admin display only
     * NEVER expose to customer portal API
     */
    public function toInternalArray(): array
    {
        return [
            'account_id' => $this->account_id,
            'fraud' => [
                'risk_level' => $this->fraud_risk_level,
                'score' => $this->fraud_score,
                'under_investigation' => $this->under_investigation,
                'investigation_notes' => $this->investigation_notes,
            ],
            'payment' => [
                'status' => $this->payment_status,
                'outstanding_balance' => $this->outstanding_balance,
                'last_payment_date' => $this->last_payment_date?->toDateString(),
            ],
            'limits' => [
                'daily_message_limit' => $this->daily_message_limit,
                'messages_sent_today' => $this->messages_sent_today,
                'api_rate_limit_per_minute' => $this->api_rate_limit_per_minute,
                'rate_limit_exceeded' => $this->rate_limit_exceeded,
            ],
            'compliance' => [
                'kyc_completed' => $this->kyc_completed,
                'aml_check_passed' => $this->aml_check_passed,
                'last_review' => $this->last_compliance_review?->toIso8601String(),
            ],
            'deliverability' => [
                'has_issues' => $this->deliverability_issues,
                'spam_complaint_rate' => $this->spam_complaint_rate,
                'consecutive_failed_sends' => $this->consecutive_failed_sends,
            ],
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
