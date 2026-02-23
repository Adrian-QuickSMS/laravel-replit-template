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
        'trading_name',
        'status',
        'account_type',
        'email',
        'phone',
        'address_line1',
        'address_line2',
        'city',
        'county',
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
        // Section 2: Company Information
        'company_type',
        'business_sector',
        'website',
        'company_number',
        // Operating Address
        'operating_address_same_as_registered',
        'operating_address_line1',
        'operating_address_line2',
        'operating_city',
        'operating_county',
        'operating_postcode',
        'operating_country',
        // Section 3: Support & Operations
        'accounts_billing_email',
        'incident_email',
        'support_contact_name',
        'support_contact_email',
        'support_contact_phone',
        'operations_contact_name',
        'operations_contact_email',
        'operations_contact_phone',
        // Section 4: Contract Signatory
        'signatory_name',
        'signatory_title',
        'signatory_email',
        'contract_agreed',
        'contract_signed_at',
        'contract_signed_ip',
        'contract_version',
        // Section 5: Billing, VAT & Tax
        'billing_contact_name',
        'billing_contact_phone',
        'billing_address_same_as_registered',
        'billing_address_line1',
        'billing_address_line2',
        'billing_city',
        'billing_county',
        'billing_postcode',
        'billing_country',
        'vat_registered',
        'vat_reverse_charges',
        'tax_id',
        'tax_country',
        'purchase_order_required',
        'purchase_order_number',
        'payment_terms',
        // Billing backend fields
        'billing_type',
        'billing_method',
        'product_tier',
        'credit_limit',
        'payment_terms_days',
        'currency',
        'platform_fee_monthly',
        'stripe_customer_id',
        'xero_contact_id',
        // Activation tracking
        'signup_details_complete',
        'company_info_complete',
        'support_operations_complete',
        'contract_signatory_complete',
        'billing_vat_complete',
        'activation_complete',
        'activated_at',
        'activated_by',
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
        'contract_agreed' => 'boolean',
        'contract_signed_at' => 'datetime',
        'operating_address_same_as_registered' => 'boolean',
        'vat_registered' => 'boolean',
        'vat_reverse_charges' => 'boolean',
        'billing_address_same_as_registered' => 'boolean',
        'purchase_order_required' => 'boolean',
        'signup_details_complete' => 'boolean',
        'company_info_complete' => 'boolean',
        'support_operations_complete' => 'boolean',
        'contract_signatory_complete' => 'boolean',
        'billing_vat_complete' => 'boolean',
        'activation_complete' => 'boolean',
        'activated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'credit_limit' => 'decimal:4',
        'platform_fee_monthly' => 'decimal:4',
        'payment_terms_days' => 'integer',
    ];

    protected $hidden = [];

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

    public function subAccounts(): HasMany
    {
        return $this->hasMany(SubAccount::class, 'account_id');
    }

    public function senderIds(): HasMany
    {
        return $this->hasMany(SenderId::class, 'account_id');
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

    // =====================================================
    // ACCOUNT ACTIVATION METHODS
    // =====================================================

    /**
     * Check if signup details section is complete
     */
    public function isSignupDetailsComplete(): bool
    {
        return !is_null($this->company_name)
            && !is_null($this->country)
            && $this->hasAcceptedTerms()
            && $this->hasAcceptedPrivacy()
            && $this->hasAcceptedFraudConsent();
    }

    /**
     * Check if company information section is complete
     */
    public function isCompanyInfoComplete(): bool
    {
        $required = !is_null($this->company_type)
            && !is_null($this->business_sector)
            && !is_null($this->website)
            && !is_null($this->address_line1)
            && !is_null($this->city)
            && !is_null($this->postcode)
            && !is_null($this->country);

        // Company number is mandatory for UK Limited companies only
        if ($this->company_type === 'uk_limited') {
            $required = $required && !is_null($this->company_number);
        }

        return $required;
    }

    /**
     * Check if support & operations section is complete
     */
    public function isSupportOperationsComplete(): bool
    {
        return !is_null($this->accounts_billing_email)
            && !is_null($this->support_contact_email)
            && !is_null($this->incident_email)
            && !is_null($this->support_contact_name)
            && !is_null($this->support_contact_phone)
            && !is_null($this->operations_contact_name)
            && !is_null($this->operations_contact_email)
            && !is_null($this->operations_contact_phone);
    }

    /**
     * Check if contract signatory section is complete
     */
    public function isContractSignatoryComplete(): bool
    {
        return !is_null($this->signatory_name)
            && !is_null($this->signatory_title)
            && !is_null($this->signatory_email)
            && $this->contract_agreed === true
            && !is_null($this->contract_signed_at);
    }

    /**
     * Check if billing, VAT and tax section is complete
     */
    public function isBillingVatComplete(): bool
    {
        $required = !is_null($this->billing_email)
            && !is_null($this->payment_terms);

        // VAT number is required if VAT registered
        if ($this->vat_registered) {
            $required = $required && !is_null($this->vat_number);
        }

        return $required;
    }

    /**
     * Update section completion flags
     * Call this after updating any section to refresh completion status
     */
    public function updateActivationStatus(): void
    {
        $this->update([
            'signup_details_complete' => $this->isSignupDetailsComplete(),
            'company_info_complete' => $this->isCompanyInfoComplete(),
            'support_operations_complete' => $this->isSupportOperationsComplete(),
            'contract_signatory_complete' => $this->isContractSignatoryComplete(),
            'billing_vat_complete' => $this->isBillingVatComplete(),
        ]);

        // Check if ALL sections are complete
        if ($this->signup_details_complete
            && $this->company_info_complete
            && $this->support_operations_complete
            && $this->contract_signatory_complete
            && $this->billing_vat_complete
        ) {
            // Mark account as fully activated
            if (!$this->activation_complete) {
                $this->update([
                    'activation_complete' => true,
                    'activated_at' => now(),
                ]);

                // TODO: Trigger activation events
                // - Expire trial credits (handled by AccountObserver if account_type changes)
                // - Send welcome email
                // - Enable live sending
                // - Notify sales team
            }
        }
    }

    /**
     * Get activation progress summary
     */
    public function getActivationProgress(): array
    {
        return [
            'sections' => [
                [
                    'id' => 'signup_details',
                    'name' => 'Sign Up Details',
                    'complete' => $this->signup_details_complete,
                    'required' => true,
                ],
                [
                    'id' => 'company_info',
                    'name' => 'Company Information',
                    'complete' => $this->company_info_complete,
                    'required' => true,
                ],
                [
                    'id' => 'support_operations',
                    'name' => 'Support & Operations',
                    'complete' => $this->support_operations_complete,
                    'required' => true,
                ],
                [
                    'id' => 'contract_signatory',
                    'name' => 'Contract Signatory',
                    'complete' => $this->contract_signatory_complete,
                    'required' => true,
                ],
                [
                    'id' => 'billing_vat',
                    'name' => 'Billing, VAT and Tax Information',
                    'complete' => $this->billing_vat_complete,
                    'required' => true,
                ],
            ],
            'overall_complete' => $this->activation_complete,
            'activated_at' => $this->activated_at?->toIso8601String(),
            'can_go_live' => $this->activation_complete,
        ];
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
            'trading_name' => $this->trading_name,
            'company_type' => $this->company_type,
            'business_sector' => $this->business_sector,
            'website' => $this->website,
            'company_number' => $this->company_number,
            'status' => $this->status,
            'account_type' => $this->account_type,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => [
                'line1' => $this->address_line1,
                'line2' => $this->address_line2,
                'city' => $this->city,
                'county' => $this->county,
                'postcode' => $this->postcode,
                'country' => $this->country,
            ],
            'operating_address' => [
                'same_as_registered' => $this->operating_address_same_as_registered,
                'line1' => $this->operating_address_line1,
                'line2' => $this->operating_address_line2,
                'city' => $this->operating_city,
                'county' => $this->operating_county,
                'postcode' => $this->operating_postcode,
                'country' => $this->operating_country,
            ],
            // Support & Operations
            'accounts_billing_email' => $this->accounts_billing_email,
            'incident_email' => $this->incident_email,
            'support_contact' => [
                'name' => $this->support_contact_name,
                'email' => $this->support_contact_email,
                'phone' => $this->support_contact_phone,
            ],
            'operations_contact' => [
                'name' => $this->operations_contact_name,
                'email' => $this->operations_contact_email,
                'phone' => $this->operations_contact_phone,
            ],
            // Contract Signatory
            'signatory' => [
                'name' => $this->signatory_name,
                'title' => $this->signatory_title,
                'email' => $this->signatory_email,
                'agreed' => $this->contract_agreed,
                'signed_at' => $this->contract_signed_at?->toIso8601String(),
            ],
            // Billing & Tax
            'billing_email' => $this->billing_email,
            'billing_contact' => [
                'name' => $this->billing_contact_name,
                'phone' => $this->billing_contact_phone,
            ],
            'vat_registered' => $this->vat_registered,
            'vat_number' => $this->vat_number,
            'vat_reverse_charges' => $this->vat_reverse_charges,
            'tax_id' => $this->tax_id,
            'payment_terms' => $this->payment_terms,
            'purchase_order_required' => $this->purchase_order_required,
            'purchase_order_number' => $this->purchase_order_number,
            // Activation status
            'activation' => $this->getActivationProgress(),
            'onboarded_at' => $this->onboarded_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
