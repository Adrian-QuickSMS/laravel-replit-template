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
        'department',
        'mfa_secret',
        'mfa_method',
        'mfa_enabled',
        'mfa_enabled_at',
        'sms_mfa_code',
        'sms_mfa_expires_at',
        'sms_mfa_attempts',
        'invite_token',
        'invite_sent_at',
        'invite_expires_at',
        'ip_whitelist',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'locked_until',
        'password_changed_at',
        'force_password_change',
        'phone',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
        'sms_mfa_code',
    ];

    protected $casts = [
        'id' => 'string',
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
        'password_changed_at' => 'datetime',
        'mfa_enabled' => 'boolean',
        'mfa_enabled_at' => 'datetime',
        'force_password_change' => 'boolean',
        'ip_whitelist' => 'array',
        'failed_login_attempts' => 'integer',
        'sms_mfa_expires_at' => 'datetime',
        'sms_mfa_attempts' => 'integer',
        'invite_sent_at' => 'datetime',
        'invite_expires_at' => 'datetime',
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
        return $query->whereNotNull('locked_until')
            ->where('locked_until', '>', now());
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
        return $this->locked_until && $this->locked_until->isFuture();
    }

    /**
     * Lock account for specified minutes
     */
    public function lockAccount(int $minutes = 60): void
    {
        $this->update([
            'locked_until' => now()->addMinutes($minutes),
        ]);
    }

    /**
     * Unlock account
     */
    public function unlockAccount(): void
    {
        $this->update([
            'locked_until' => null,
            'failed_login_attempts' => 0,
        ]);
    }

    public function incrementFailedLogins(): void
    {
        $this->increment('failed_login_attempts');

        if ($this->failed_login_attempts >= 3) {
            $this->lockAccount(60);
        }
    }

    public function resetFailedLogins(): void
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    public function recordLogin(string $ipAddress): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
            'failed_login_attempts' => 0,
            'locked_until' => null,
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
            'department' => $this->department,
            'status' => $this->status,
            'mfa_enabled' => $this->mfa_enabled,
            'mfa_method' => $this->mfa_method,
            'has_ip_whitelist' => !empty($this->ip_whitelist),
            'phone' => $this->masked_phone,
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'failed_login_attempts' => $this->failed_login_attempts,
            'password_needs_change' => $this->needsPasswordChange(),
            'is_locked' => $this->isLocked(),
            'created_at' => $this->created_at?->toIso8601String(),
            'created_by' => $this->created_by,
            'invite_sent_at' => $this->invite_sent_at?->toIso8601String(),
        ];
    }

    // =====================================================
    // SMS MFA METHODS
    // =====================================================

    public function generateSmsMfaCode(): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'sms_mfa_code' => hash('sha256', $code),
            'sms_mfa_expires_at' => now()->addMinutes(5),
            'sms_mfa_attempts' => 0,
        ]);

        return $code;
    }

    public function verifySmsMfaCode(string $code): bool
    {
        if (!$this->sms_mfa_code || !$this->sms_mfa_expires_at) {
            return false;
        }

        if ($this->sms_mfa_expires_at->isPast()) {
            return false;
        }

        if ($this->sms_mfa_attempts >= 3) {
            return false;
        }

        $this->increment('sms_mfa_attempts');

        return hash_equals($this->sms_mfa_code, hash('sha256', $code));
    }

    public function clearSmsMfaCode(): void
    {
        $this->update([
            'sms_mfa_code' => null,
            'sms_mfa_expires_at' => null,
            'sms_mfa_attempts' => 0,
        ]);
    }

    public function getMaskedPhoneAttribute(): ?string
    {
        if (!$this->phone) return null;
        return '****' . substr($this->phone, -2);
    }

    // =====================================================
    // INVITE METHODS
    // =====================================================

    public function generateInviteToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update([
            'invite_token' => hash('sha256', $token),
            'invite_sent_at' => now(),
            'invite_expires_at' => now()->addHours(72),
        ]);
        return $token;
    }

    public function hasValidInvite(): bool
    {
        return $this->invite_token && $this->invite_expires_at && $this->invite_expires_at->isFuture();
    }

    public function scopeByDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    public function scopeInvited($query)
    {
        return $query->whereNotNull('invite_token')->whereNotNull('invite_expires_at');
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
