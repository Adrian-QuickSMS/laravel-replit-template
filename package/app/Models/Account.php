<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * GREEN SIDE: Account (Tenant Root)
 *
 * DATA CLASSIFICATION: Internal - Customer Company Data
 * SIDE: GREEN (customer portal accessible via account_safe_view)
 * TENANT ISOLATION: This IS the tenant root table
 *
 * SECURITY NOTES:
 * - UUID BINARY(16) primary key prevents enumeration
 * - Portal users access via account_safe_view only
 * - Direct SELECT requires svc_green or ops_admin role
 * - HubSpot bidirectional sync via hubspot_company_id
 * - Status changes audit logged
 */
class Account extends Model
{
    protected $table = 'accounts';

    // UUID primary key
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'account_number',
        'company_name',
        'status',
        'account_type',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'postcode',
        'country',
        'vat_number',
        'hubspot_company_id',
        'billing_email',
        'onboarded_at',
        'suspended_at',
        'closed_at',
        // Consent tracking
        'terms_accepted_at',
        'terms_accepted_ip',
        'terms_version',
        'privacy_accepted_at',
        'privacy_accepted_ip',
        'privacy_version',
        'fraud_consent_at',
        'fraud_consent_ip',
        'fraud_consent_version',
        'marketing_consent_at',
        'marketing_consent_ip',
        // Signup tracking
        'signup_ip_address',
        'signup_referrer',
        // UTM parameters
        'signup_utm_source',
        'signup_utm_medium',
        'signup_utm_campaign',
        'signup_utm_content',
        'signup_utm_term',
        // Promotional credits
        'signup_credits_awarded',
        'signup_promotion_code',
    ];

    protected $casts = [
        'id' => 'string',
        'onboarded_at' => 'datetime',
        'suspended_at' => 'datetime',
        'closed_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'privacy_accepted_at' => 'datetime',
        'fraud_consent_at' => 'datetime',
        'marketing_consent_at' => 'datetime',
        'signup_credits_awarded' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [];

    /**
     * Get the route key name for Laravel routing
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Convert UUID binary to string when retrieved
     */
    public function getIdAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        // If already a string UUID, return as-is
        if (is_string($value) && strlen($value) === 36) {
            return $value;
        }

        // Convert binary to UUID string
        $hex = bin2hex($value);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20)
        );
    }

    /**
     * Convert UUID string to binary when setting
     */
    public function setIdAttribute($value)
    {
        if ($value === null) {
            return;
        }

        // If already binary, store as-is
        if (is_string($value) && strlen($value) === 16) {
            $this->attributes['id'] = $value;
            return;
        }

        // Convert UUID string to binary
        $hex = str_replace('-', '', $value);
        $this->attributes['id'] = hex2bin($hex);
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * All users belonging to this account/tenant
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    /**
     * Account settings (one-to-one)
     */
    public function settings(): HasOne
    {
        return $this->hasOne(AccountSettings::class, 'account_id');
    }

    /**
     * Account flags (RED SIDE - internal only)
     * This relationship exists for internal services only
     * NEVER expose via portal API
     */
    public function flags(): HasOne
    {
        return $this->hasOne(AccountFlags::class, 'account_id');
    }

    /**
     * API tokens belonging to this account
     */
    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class, 'tenant_id');
    }

    /**
     * Account credits (promotional and purchased)
     */
    public function credits(): HasMany
    {
        return $this->hasMany(AccountCredit::class, 'account_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope: Only active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Trial accounts only
     */
    public function scopeTrial($query)
    {
        return $query->where('account_type', 'trial');
    }

    /**
     * Scope: Postpay accounts only
     */
    public function scopePostpay($query)
    {
        return $query->where('account_type', 'postpay');
    }

    /**
     * Scope: Prepay accounts only
     */
    public function scopePrepay($query)
    {
        return $query->where('account_type', 'prepay');
    }

    /**
     * Scope: Suspended accounts
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Scope: Synced with HubSpot
     */
    public function scopeSyncedWithHubspot($query)
    {
        return $query->whereNotNull('hubspot_company_id');
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Check if account is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if account is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if account is closed
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Check if account is in trial
     */
    public function isTrial(): bool
    {
        return $this->account_type === 'trial';
    }

    /**
     * Check if account is synced with HubSpot
     */
    public function isSyncedWithHubspot(): bool
    {
        return !empty($this->hubspot_company_id);
    }

    /**
     * Get primary account owner (first user with owner role)
     */
    public function getOwner()
    {
        return $this->users()->where('role', 'owner')->first();
    }

    /**
     * Get all admin users for this account
     */
    public function getAdmins()
    {
        return $this->users()->whereIn('role', ['owner', 'admin'])->get();
    }

    // =====================================================
    // CONSENT METHODS
    // =====================================================

    /**
     * Check if terms have been accepted
     */
    public function hasAcceptedTerms(): bool
    {
        return !is_null($this->terms_accepted_at);
    }

    /**
     * Check if privacy policy has been accepted
     */
    public function hasAcceptedPrivacy(): bool
    {
        return !is_null($this->privacy_accepted_at);
    }

    /**
     * Check if fraud prevention consent has been given
     */
    public function hasAcceptedFraudConsent(): bool
    {
        return !is_null($this->fraud_consent_at);
    }

    /**
     * Check if marketing consent has been given
     */
    public function hasMarketingConsent(): bool
    {
        return !is_null($this->marketing_consent_at);
    }

    /**
     * Get all UTM parameters as array
     */
    public function getUtmParameters(): array
    {
        return [
            'source' => $this->signup_utm_source,
            'medium' => $this->signup_utm_medium,
            'campaign' => $this->signup_utm_campaign,
            'content' => $this->signup_utm_content,
            'term' => $this->signup_utm_term,
        ];
    }

    /**
     * Award signup credits
     */
    public function awardSignupCredits(int $amount, string $type, string $reason): void
    {
        AccountCredit::create([
            'account_id' => $this->id,
            'type' => $type,
            'credits_awarded' => $amount,
            'credits_used' => 0,
            'credits_remaining' => $amount,
            'reason' => $reason,
            'expires_at' => null, // NULL = valid during trial
        ]);

        $this->increment('signup_credits_awarded', $amount);
    }

    /**
     * Get total available credits
     */
    public function getAvailableCredits(): int
    {
        return $this->credits()
            ->where('credits_remaining', '>', 0)
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->sum('credits_remaining');
    }

    /**
     * Format account for safe portal display
     * Excludes sensitive fields
     */
    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'account_number' => $this->account_number,
            'company_name' => $this->company_name,
            'status' => $this->status,
            'account_type' => $this->account_type,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => [
                'line1' => $this->address_line1,
                'line2' => $this->address_line2,
                'city' => $this->city,
                'postcode' => $this->postcode,
                'country' => $this->country,
            ],
            'vat_number' => $this->vat_number,
            'billing_email' => $this->billing_email,
            'onboarded_at' => $this->onboarded_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
