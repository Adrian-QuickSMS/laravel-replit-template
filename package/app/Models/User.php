<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

/**
 * GREEN SIDE: Users (Customer Portal Users)
 *
 * DATA CLASSIFICATION: Restricted - User Authentication
 * SIDE: GREEN (customer portal accessible via user_profile_view)
 * TENANT ISOLATION: MANDATORY tenant_id on every row
 *
 * SECURITY NOTES:
 * - Global scope auto-filters by authenticated user's tenant_id
 * - Password hash NEVER exposed via toArray() or API responses
 * - Portal users access via user_profile_view only
 * - Failed login attempts tracked for account lockout
 * - MFA optional for customers (mandatory for admins)
 * - HubSpot bidirectional sync via hubspot_contact_id
 */
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    // UUID primary key
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'user_type',
        'email',
        'password',
        'first_name',
        'last_name',
        'job_title',
        'role',
        'status',
        'mfa_enabled',
        'mfa_secret',
        'mobile_number',
        'mobile_verified_at',
        'mobile_verification_code',
        'mobile_verification_expires_at',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'account_locked_until',
        'hubspot_contact_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
        'mobile_verification_code',
    ];

    protected $casts = [
        'id' => 'string',
        'tenant_id' => 'string',
        'email_verified_at' => 'datetime',
        'mobile_verified_at' => 'datetime',
        'mobile_verification_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
        'account_locked_until' => 'datetime',
        'password_changed_at' => 'datetime',
        'mfa_enabled' => 'boolean',
        'failed_login_attempts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method - apply global tenant scope
     *
     * NOTE: Password hashing is NOT done here to avoid double-hashing.
     * The stored procedures (sp_create_account, sp_authenticate_user) and
     * the changePassword() method handle hashing at the appropriate layer.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-scope all queries by tenant_id if authenticated
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('users.tenant_id', auth()->user()->tenant_id);
            }
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * The account/tenant this user belongs to
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'tenant_id');
    }

    /**
     * User sessions (Sanctum tokens)
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class, 'user_id');
    }

    /**
     * API tokens created by this user
     */
    public function apiTokens(): HasMany
    {
        return $this->hasMany(ApiToken::class, 'user_id');
    }

    /**
     * Email verification token
     */
    public function emailVerificationToken(): HasMany
    {
        return $this->hasMany(EmailVerificationToken::class, 'user_id');
    }

    /**
     * Password history (RED SIDE - internal only)
     */
    public function passwordHistory(): HasMany
    {
        return $this->hasMany(PasswordHistory::class, 'user_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope: Only active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Users with verified email
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope: Users with MFA enabled
     */
    public function scopeMfaEnabled($query)
    {
        return $query->where('mfa_enabled', true);
    }

    /**
     * Scope: Locked accounts
     */
    public function scopeLocked($query)
    {
        return $query->whereNotNull('account_locked_until')
            ->where('account_locked_until', '>', now());
    }

    /**
     * Scope: Owners only
     */
    public function scopeOwners($query)
    {
        return $query->where('role', 'owner');
    }

    /**
     * Scope: Admins and owners
     */
    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['owner', 'admin']);
    }

    /**
     * Scope: By tenant (removes global scope)
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->withoutGlobalScope('tenant')->where('tenant_id', $tenantId);
    }

    // =====================================================
    // AUTHENTICATION METHODS
    // =====================================================

    /**
     * Check if user's email is verified
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => now(),
        ])->save();
    }

    /**
     * Check if account is locked
     */
    public function isLocked(): bool
    {
        return $this->account_locked_until && $this->account_locked_until->isFuture();
    }

    /**
     * Lock account for specified minutes
     */
    public function lockAccount(int $minutes = 30): void
    {
        $this->update([
            'account_locked_until' => now()->addMinutes($minutes),
        ]);
    }

    /**
     * Unlock account
     */
    public function unlockAccount(): void
    {
        $this->update([
            'account_locked_until' => null,
            'failed_login_attempts' => 0,
        ]);
    }

    /**
     * Increment failed login attempts
     */
    public function incrementFailedLogins(): void
    {
        $this->increment('failed_login_attempts');

        // Lock after 5 failed attempts
        if ($this->failed_login_attempts >= 5) {
            $this->lockAccount(30);
        }
    }

    /**
     * Reset failed login attempts
     */
    public function resetFailedLogins(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'account_locked_until' => null,
        ]);
    }

    /**
     * Record successful login
     */
    public function recordLogin(string $ipAddress): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
            'failed_login_attempts' => 0,
            'account_locked_until' => null,
        ]);
    }

    // =====================================================
    // MFA METHODS
    // =====================================================

    /**
     * Check if MFA is enabled
     */
    public function hasMfaEnabled(): bool
    {
        return $this->mfa_enabled === true && !empty($this->mfa_secret);
    }

    /**
     * Enable MFA with secret
     */
    public function enableMfa(string $secret): bool
    {
        return $this->update([
            'mfa_enabled' => true,
            'mfa_secret' => encrypt($secret),
        ]);
    }

    /**
     * Disable MFA
     */
    public function disableMfa(): bool
    {
        return $this->update([
            'mfa_enabled' => false,
            'mfa_secret' => null,
        ]);
    }

    /**
     * Get decrypted MFA secret
     */
    public function getMfaSecret(): ?string
    {
        if (!$this->mfa_secret) {
            return null;
        }

        return decrypt($this->mfa_secret);
    }

    // =====================================================
    // MOBILE VERIFICATION METHODS
    // =====================================================

    /**
     * Normalize mobile number to storage format (447XXXXXXXXX)
     */
    public static function normalizeMobileNumber(string $input): string
    {
        // Remove all non-digits
        $digits = preg_replace('/[^0-9]/', '', $input);

        // Convert 07XX to 447XX (UK local to international)
        if (str_starts_with($digits, '07') && strlen($digits) == 11) {
            $digits = '44' . substr($digits, 1);
        }

        // Remove leading 44 if present, then re-add for consistency
        if (str_starts_with($digits, '44')) {
            $digits = substr($digits, 2);
        }

        // Final format: 447XXXXXXXXX (always 12 digits)
        return '44' . $digits;
    }

    /**
     * Check if user has verified their mobile number
     */
    public function hasMobileVerified(): bool
    {
        return !is_null($this->mobile_verified_at);
    }

    /**
     * Generate and store mobile verification code (6 digits)
     * Returns the plain code to be sent via SMS
     */
    public function generateMobileVerificationCode(): string
    {
        // Generate random 6-digit code
        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store hashed version with SHA-256
        $this->update([
            'mobile_verification_code' => hash('sha256', $code),
            'mobile_verification_expires_at' => now()->addMinutes(10),
        ]);

        return $code;
    }

    /**
     * Verify mobile verification code
     */
    public function verifyMobileCode(string $code): bool
    {
        // Check if code exists and hasn't expired
        if (!$this->mobile_verification_code || !$this->mobile_verification_expires_at) {
            return false;
        }

        if ($this->mobile_verification_expires_at->isPast()) {
            return false;
        }

        // Verify code matches stored hash
        $hashedCode = hash('sha256', $code);
        if ($hashedCode !== $this->mobile_verification_code) {
            return false;
        }

        // Mark mobile as verified and auto-enable MFA
        $this->update([
            'mobile_verified_at' => now(),
            'mobile_verification_code' => null,
            'mobile_verification_expires_at' => null,
            'mfa_enabled' => true, // Auto-enable MFA after mobile verification
        ]);

        return true;
    }

    /**
     * Check if user can resend verification code (rate limiting check)
     */
    public function canResendMobileCode(): bool
    {
        if (!$this->mobile_verification_expires_at) {
            return true;
        }

        // Allow resend only after 1 minute
        return $this->mobile_verification_expires_at->diffInMinutes(now()) >= 9;
    }

    /**
     * Check if user is ready for MFA (mobile verified + MFA enabled)
     */
    public function isMfaReady(): bool
    {
        return $this->hasMobileVerified() && $this->hasMfaEnabled();
    }

    /**
     * Format mobile number for display (+44 7XXX XXX XXX)
     */
    public function getFormattedMobileNumber(): ?string
    {
        if (!$this->mobile_number) {
            return null;
        }

        // Assume stored as 447XXXXXXXXX (12 digits)
        if (strlen($this->mobile_number) === 12 && str_starts_with($this->mobile_number, '44')) {
            $localNumber = substr($this->mobile_number, 2); // Remove '44'
            return sprintf(
                '+44 %s %s %s',
                substr($localNumber, 0, 4),
                substr($localNumber, 4, 3),
                substr($localNumber, 7, 3)
            );
        }

        return '+' . $this->mobile_number;
    }

    // =====================================================
    // PASSWORD METHODS
    // =====================================================

    /**
     * Check if password has been used before (last 12 passwords)
     */
    public function hasUsedPassword(string $password): bool
    {
        $history = $this->passwordHistory()
            ->orderBy('set_at', 'desc')
            ->limit(12)
            ->get();

        foreach ($history as $record) {
            if (Hash::check($password, $record->password_hash)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Change password and save to history
     */
    public function changePassword(string $newPassword): bool
    {
        // Check if password was used before
        if ($this->hasUsedPassword($newPassword)) {
            throw new \Exception('Password has been used in the last 12 passwords. Please choose a different one.');
        }

        // Save current password to history
        PasswordHistory::create([
            'user_id' => $this->id,
            'password_hash' => $this->password,
            'set_at' => $this->password_changed_at ?? $this->created_at,
        ]);

        // Update password
        return $this->update([
            'password' => $newPassword, // Will be hashed by the saving event
            'password_changed_at' => now(),
        ]);
    }

    // =====================================================
    // ROLE CHECKS
    // =====================================================

    /**
     * Check if user is account owner
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Check if user is admin or owner
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    // =====================================================
    // HUBSPOT SYNC
    // =====================================================

    /**
     * Check if user is synced with HubSpot
     */
    public function isSyncedWithHubspot(): bool
    {
        return !empty($this->hubspot_contact_id);
    }

    /**
     * Get HubSpot contact URL
     */
    public function getHubspotUrl(): ?string
    {
        if (!$this->hubspot_contact_id) {
            return null;
        }

        return "https://app.hubspot.com/contacts/" . config('services.hubspot.portal_id') . "/contact/{$this->hubspot_contact_id}";
    }

    // =====================================================
    // PORTAL API METHODS
    // =====================================================

    /**
     * Format user for safe portal display
     * NEVER expose password hash or MFA secret
     */
    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'user_type' => $this->user_type,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'job_title' => $this->job_title,
            'role' => $this->role,
            'status' => $this->status,
            'mfa_enabled' => $this->mfa_enabled,
            'email_verified' => $this->hasVerifiedEmail(),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'mobile_number' => $this->getFormattedMobileNumber(),
            'mobile_verified' => $this->hasMobileVerified(),
            'mobile_verified_at' => $this->mobile_verified_at?->toIso8601String(),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }

    /**
     * Override toArray to ensure password is never exposed
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        // Double-check password is never exposed
        unset($array['password']);
        unset($array['mfa_secret']);
        unset($array['remember_token']);

        return $array;
    }
}
