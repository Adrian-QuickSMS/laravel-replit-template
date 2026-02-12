<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GREEN SIDE: Account Credits (Promotional & Purchased)
 *
 * DATA CLASSIFICATION: Internal - Credit Tracking
 * SIDE: GREEN (customer can view own credits)
 * TENANT ISOLATION: References tenant via account_id
 *
 * SECURITY NOTES:
 * - Tracks all credit awards (signup, mobile verification, referrals, purchases)
 * - Credits expire when account transitions from trial to live
 * - Portal users can view via safe view
 * - Usage tracked for billing
 *
 * CREDIT TYPES:
 * - signup_promo: Free credits for signing up
 * - mobile_verification: 100 credits for mobile verify + marketing opt-in
 * - referral: Referral bonuses
 * - purchased: Paid credits
 * - bonus: Admin-awarded bonus
 * - compensation: Service credits for issues
 */
class AccountCredit extends Model
{
    protected $table = 'account_credits';

    protected $fillable = [
        'account_id',
        'type',
        'credits_awarded',
        'credits_used',
        'credits_remaining',
        'reason',
        'reference_id',
        'expires_at',
        'expired_at',
        'awarded_by',
    ];

    protected $casts = [
        'account_id' => 'string',
        'credits_awarded' => 'integer',
        'credits_used' => 'integer',
        'credits_remaining' => 'integer',
        'expires_at' => 'datetime',
        'expired_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * The account these credits belong to
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope: Only valid (non-expired) credits
     */
    public function scopeValid($query)
    {
        return $query->where('credits_remaining', '>', 0)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->whereNull('expired_at');
    }

    /**
     * Scope: Expired credits
     */
    public function scopeExpired($query)
    {
        return $query->where(function($q) {
            $q->whereNotNull('expired_at')
              ->orWhere(function($subQ) {
                  $subQ->whereNotNull('expires_at')
                       ->where('expires_at', '<=', now());
              });
        });
    }

    /**
     * Scope: Promotional credits (trial credits that expire)
     */
    public function scopePromotional($query)
    {
        return $query->whereIn('type', ['signup_promo', 'mobile_verification', 'referral']);
    }

    /**
     * Scope: Purchased credits (paid, never expire)
     */
    public function scopePurchased($query)
    {
        return $query->where('type', 'purchased');
    }

    /**
     * Scope: By credit type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Check if credits are currently valid
     */
    public function isValid(): bool
    {
        if ($this->credits_remaining <= 0) {
            return false;
        }

        if ($this->expired_at !== null) {
            return false;
        }

        if ($this->expires_at === null) {
            return true; // NULL = valid during trial
        }

        return $this->expires_at->isFuture();
    }

    /**
     * Check if credits have expired
     */
    public function isExpired(): bool
    {
        return !$this->isValid();
    }

    /**
     * Use credits (decrement remaining balance)
     */
    public function useCredits(int $amount): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($amount > $this->credits_remaining) {
            return false;
        }

        $this->increment('credits_used', $amount);
        $this->decrement('credits_remaining', $amount);

        return true;
    }

    /**
     * Mark credits as expired
     */
    public function markAsExpired(): void
    {
        $this->update([
            'expired_at' => now(),
            'credits_remaining' => 0,
        ]);
    }

    /**
     * Get credits available (alias for credits_remaining)
     */
    public function getAvailableCreditsAttribute(): int
    {
        return $this->isValid() ? $this->credits_remaining : 0;
    }

    /**
     * Check if this is a promotional credit
     */
    public function isPromotional(): bool
    {
        return in_array($this->type, ['signup_promo', 'mobile_verification', 'referral']);
    }

    /**
     * Check if this is a purchased credit
     */
    public function isPurchased(): bool
    {
        return $this->type === 'purchased';
    }

    /**
     * Format credit for safe portal display
     */
    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'credits_awarded' => $this->credits_awarded,
            'credits_used' => $this->credits_used,
            'credits_remaining' => $this->credits_remaining,
            'credits_available' => $this->available_credits,
            'reason' => $this->reason,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'is_valid' => $this->isValid(),
            'is_expired' => $this->isExpired(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }

    // =====================================================
    // STATIC HELPER METHODS
    // =====================================================

    /**
     * Award mobile verification credits (100 SMS)
     */
    public static function awardMobileVerificationCredits(string $accountId): self
    {
        return self::create([
            'account_id' => $accountId,
            'type' => 'mobile_verification',
            'credits_awarded' => 100,
            'credits_used' => 0,
            'credits_remaining' => 100,
            'reason' => 'Mobile verification + marketing opt-in',
            'expires_at' => null, // Valid during trial, will be expired when account goes live
        ]);
    }

    /**
     * Award signup promotional credits
     */
    public static function awardSignupCredits(string $accountId, int $amount, string $promoCode = null): self
    {
        return self::create([
            'account_id' => $accountId,
            'type' => 'signup_promo',
            'credits_awarded' => $amount,
            'credits_used' => 0,
            'credits_remaining' => $amount,
            'reason' => 'Signup promotion',
            'reference_id' => $promoCode,
            'expires_at' => null, // Valid during trial
        ]);
    }

    /**
     * Award referral credits
     */
    public static function awardReferralCredits(string $accountId, int $amount, string $referralCode): self
    {
        return self::create([
            'account_id' => $accountId,
            'type' => 'referral',
            'credits_awarded' => $amount,
            'credits_used' => 0,
            'credits_remaining' => $amount,
            'reason' => 'Referral bonus',
            'reference_id' => $referralCode,
            'expires_at' => null, // Valid during trial
        ]);
    }

    /**
     * Award purchased credits (never expire)
     */
    public static function awardPurchasedCredits(string $accountId, int $amount, string $orderId): self
    {
        return self::create([
            'account_id' => $accountId,
            'type' => 'purchased',
            'credits_awarded' => $amount,
            'credits_used' => 0,
            'credits_remaining' => $amount,
            'reason' => 'Credit purchase',
            'reference_id' => $orderId,
            'expires_at' => null, // Purchased credits never expire
        ]);
    }
}
