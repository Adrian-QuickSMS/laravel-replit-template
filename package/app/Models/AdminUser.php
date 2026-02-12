<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * RED SIDE: Admin Users (Platform Administrators)
 *
 * DATA CLASSIFICATION: Restricted - Internal Administration
 * SIDE: RED (never accessible to customer portal)
 * TENANT ISOLATION: None - these are platform administrators
 *
 * SECURITY NOTES:
 * - NO tenant_id (platform-level administrators)
 * - MFA is MANDATORY (enforced by database trigger)
 * - Portal roles: NO ACCESS
 * - Separate authentication guard ('admin')
 * - IP whitelist for super_admin and admin roles
 * - All actions logged to auth_audit_log
 * - Password policy: 12 characters minimum
 */
class AdminUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'admin_users';

    // UUID primary key
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'role',
        'status',
        'mfa_secret',
        'ip_whitelist',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'account_locked_until',
        'password_changed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    protected $casts = [
        'id' => 'string',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'account_locked_until' => 'datetime',
        'password_changed_at' => 'datetime',
        'mfa_enabled' => 'boolean',
        'ip_whitelist' => 'array',
        'failed_login_attempts' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Hash password on create/update
        static::saving(function ($admin) {
            if ($admin->isDirty('password') && $admin->password) {
                $admin->password = Hash::make($admin->password);
            }
        });
    }

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

        if (is_string($value) && strlen($value) === 36) {
            return $value;
        }

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

        if (is_string($value) && strlen($value) === 16) {
            $this->attributes['id'] = $value;
            return;
        }

        $hex = str_replace('-', '', $value);
        $this->attributes['id'] = hex2bin($hex);
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Audit log entries created by this admin
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuthAuditLog::class, 'actor_id')
            ->where('actor_type', 'admin_user');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope: Only active admins
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Super admins only
     */
    public function scopeSuperAdmins($query)
    {
        return $query->where('role', 'super_admin');
    }

    /**
     * Scope: Locked accounts
     */
    public function scopeLocked($query)
    {
        return $query->whereNotNull('account_locked_until')
            ->where('account_locked_until', '>', now());
    }

    // =====================================================
    // AUTHENTICATION METHODS
    // =====================================================

    /**
     * Check if email is verified
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
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
    public function lockAccount(int $minutes = 60): void
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

        // Lock after 3 failed attempts (stricter for admins)
        if ($this->failed_login_attempts >= 3) {
            $this->lockAccount(60);
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
    // MFA METHODS (MANDATORY FOR ADMINS)
    // =====================================================

    /**
     * Check if MFA is enabled (should always be true for admins)
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
    // IP WHITELIST METHODS
    // =====================================================

    /**
     * Check if IP is whitelisted
     */
    public function isIpWhitelisted(string $ip): bool
    {
        // If no whitelist, allow all (for support/readonly roles)
        if (empty($this->ip_whitelist)) {
            return true;
        }

        return in_array($ip, $this->ip_whitelist);
    }

    /**
     * Add IP to whitelist
     */
    public function addIpToWhitelist(string $ip): bool
    {
        $whitelist = $this->ip_whitelist ?? [];

        if (!in_array($ip, $whitelist)) {
            $whitelist[] = $ip;
            return $this->update(['ip_whitelist' => $whitelist]);
        }

        return true;
    }

    /**
     * Remove IP from whitelist
     */
    public function removeIpFromWhitelist(string $ip): bool
    {
        $whitelist = $this->ip_whitelist ?? [];
        $whitelist = array_values(array_diff($whitelist, [$ip]));

        return $this->update(['ip_whitelist' => $whitelist]);
    }

    // =====================================================
    // ROLE CHECKS
    // =====================================================

    /**
     * Check if admin is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if admin is admin or super admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Check if admin has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if admin has any of the specified roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if admin can access RED side data
     */
    public function canAccessRedSide(): bool
    {
        return in_array($this->role, ['super_admin', 'admin', 'finance']);
    }

    /**
     * Check if admin can modify system configuration
     */
    public function canModifySystem(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Check if admin is read-only
     */
    public function isReadOnly(): bool
    {
        return $this->role === 'readonly';
    }

    // =====================================================
    // PASSWORD METHODS
    // =====================================================

    /**
     * Change password
     */
    public function changePassword(string $newPassword): bool
    {
        return $this->update([
            'password' => $newPassword, // Will be hashed by the saving event
            'password_changed_at' => now(),
        ]);
    }

    /**
     * Check if password needs to be changed (older than 90 days)
     */
    public function needsPasswordChange(): bool
    {
        if (!$this->password_changed_at) {
            return true;
        }

        return $this->password_changed_at->diffInDays(now()) > 90;
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'support' => 'Support',
            'finance' => 'Finance',
            'readonly' => 'Read Only',
            default => 'Unknown',
        };
    }

    /**
     * Format for safe display (internal only - never exposed to portal)
     */
    public function toAdminArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'role' => $this->role,
            'role_display' => $this->getRoleDisplayName(),
            'status' => $this->status,
            'mfa_enabled' => $this->mfa_enabled,
            'has_ip_whitelist' => !empty($this->ip_whitelist),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'password_needs_change' => $this->needsPasswordChange(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }

    /**
     * Override toArray to ensure password is never exposed
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        unset($array['password']);
        unset($array['mfa_secret']);
        unset($array['remember_token']);

        return $array;
    }
}
