<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RED SIDE: Authentication Audit Log
 *
 * DATA CLASSIFICATION: Restricted - Security Audit Trail
 * SIDE: RED (never accessible to customer portal)
 * TENANT ISOLATION: Records events for both tenants and admins
 *
 * SECURITY NOTES:
 * - Immutable log (no UPDATE or DELETE allowed)
 * - Records ALL authentication attempts (success and failure)
 * - Could reveal enumeration attacks - must be RED
 * - Retention: 2 years for compliance
 * - Portal roles: NO ACCESS
 * - ops_admin: SELECT only (read-only audit review)
 *
 * EVENTS LOGGED:
 * - login_success, login_failed, logout
 * - password_changed, password_reset_requested, password_reset_completed
 * - mfa_enabled, mfa_disabled, mfa_challenge_failed
 * - api_token_created, api_token_revoked
 * - account_locked, account_unlocked
 * - session_expired, session_terminated
 */
class AuthAuditLog extends Model
{
    protected $table = 'auth_audit_log';

    public $timestamps = false;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'actor_email',
        'tenant_id',
        'event_type',
        'ip_address',
        'user_agent',
        'metadata',
        'result',
        'failure_reason',
        'created_at',
    ];

    protected $casts = [
        'actor_id' => 'string',
        'tenant_id' => 'string',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * The user this log entry relates to (polymorphic)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->where('actor_type', 'customer_user');
    }

    /**
     * The admin user this log entry relates to
     */
    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'actor_id')->where('actor_type', 'admin_user');
    }

    /**
     * The tenant/account this event belongs to
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'tenant_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope: Only customer user events
     */
    public function scopeCustomerUsers($query)
    {
        return $query->where('actor_type', 'customer_user');
    }

    /**
     * Scope: Only admin user events
     */
    public function scopeAdminUsers($query)
    {
        return $query->where('actor_type', 'admin_user');
    }

    /**
     * Scope: Only failures
     */
    public function scopeFailures($query)
    {
        return $query->where('result', 'failure');
    }

    /**
     * Scope: Only suspicious events
     */
    public function scopeSuspicious($query)
    {
        return $query->where('result', 'suspicious');
    }

    /**
     * Scope: Login attempts
     */
    public function scopeLoginAttempts($query)
    {
        return $query->whereIn('event_type', ['login_success', 'login_failed']);
    }

    /**
     * Scope: Failed logins
     */
    public function scopeFailedLogins($query)
    {
        return $query->where('event_type', 'login_failed');
    }

    /**
     * Scope: By IP address
     */
    public function scopeFromIp($query, string $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope: By tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope: Recent events (last N days)
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // =====================================================
    // STATIC LOGGING METHODS
    // =====================================================

    /**
     * Log authentication event
     */
    public static function logEvent(array $data): self
    {
        return static::create([
            'actor_type' => $data['actor_type'] ?? 'customer_user',
            'actor_id' => $data['actor_id'] ?? null,
            'actor_email' => $data['actor_email'] ?? null,
            'tenant_id' => $data['tenant_id'] ?? null,
            'event_type' => $data['event_type'],
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'metadata' => $data['metadata'] ?? null,
            'result' => $data['result'] ?? 'success',
            'failure_reason' => $data['failure_reason'] ?? null,
            'created_at' => now(),
        ]);
    }

    /**
     * Log successful login
     */
    public static function logLoginSuccess($user, string $actorType = 'customer_user'): self
    {
        return static::logEvent([
            'actor_type' => $actorType,
            'actor_id' => $user->id,
            'actor_email' => $user->email,
            'tenant_id' => $user->tenant_id ?? null,
            'event_type' => 'login_success',
            'result' => 'success',
        ]);
    }

    /**
     * Log failed login
     */
    public static function logLoginFailure(string $email, string $reason, string $actorType = 'customer_user', $tenantId = null): self
    {
        return static::logEvent([
            'actor_type' => $actorType,
            'actor_email' => $email,
            'tenant_id' => $tenantId,
            'event_type' => 'login_failed',
            'result' => 'failure',
            'failure_reason' => $reason,
        ]);
    }

    /**
     * Log logout
     */
    public static function logLogout($user, string $actorType = 'customer_user'): self
    {
        return static::logEvent([
            'actor_type' => $actorType,
            'actor_id' => $user->id,
            'actor_email' => $user->email,
            'tenant_id' => $user->tenant_id ?? null,
            'event_type' => 'logout',
            'result' => 'success',
        ]);
    }

    /**
     * Log password change
     */
    public static function logPasswordChange($user, string $actorType = 'customer_user'): self
    {
        return static::logEvent([
            'actor_type' => $actorType,
            'actor_id' => $user->id,
            'actor_email' => $user->email,
            'tenant_id' => $user->tenant_id ?? null,
            'event_type' => 'password_changed',
            'result' => 'success',
        ]);
    }

    /**
     * Log password reset request
     */
    public static function logPasswordResetRequest(string $email, $tenantId = null): self
    {
        return static::logEvent([
            'actor_type' => 'customer_user',
            'actor_email' => $email,
            'tenant_id' => $tenantId,
            'event_type' => 'password_reset_requested',
            'result' => 'success',
        ]);
    }

    /**
     * Log MFA enabled
     */
    public static function logMfaEnabled($user, string $actorType = 'customer_user'): self
    {
        return static::logEvent([
            'actor_type' => $actorType,
            'actor_id' => $user->id,
            'actor_email' => $user->email,
            'tenant_id' => $user->tenant_id ?? null,
            'event_type' => 'mfa_enabled',
            'result' => 'success',
        ]);
    }

    /**
     * Log API token created
     */
    public static function logApiTokenCreated($user, string $tokenName): self
    {
        return static::logEvent([
            'actor_type' => 'customer_user',
            'actor_id' => $user->id,
            'actor_email' => $user->email,
            'tenant_id' => $user->tenant_id ?? null,
            'event_type' => 'api_token_created',
            'result' => 'success',
            'metadata' => ['token_name' => $tokenName],
        ]);
    }

    /**
     * Log account locked
     */
    public static function logAccountLocked($user, string $reason): self
    {
        return static::logEvent([
            'actor_type' => 'system',
            'actor_id' => $user->id,
            'actor_email' => $user->email,
            'tenant_id' => $user->tenant_id ?? null,
            'event_type' => 'account_locked',
            'result' => 'success',
            'metadata' => ['reason' => $reason],
        ]);
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Check if event was successful
     */
    public function wasSuccessful(): bool
    {
        return $this->result === 'success';
    }

    /**
     * Check if event was a failure
     */
    public function wasFailure(): bool
    {
        return $this->result === 'failure';
    }

    /**
     * Check if event was suspicious
     */
    public function wasSuspicious(): bool
    {
        return $this->result === 'suspicious';
    }

    /**
     * Get event display name
     */
    public function getEventDisplayName(): string
    {
        return str_replace('_', ' ', ucfirst($this->event_type));
    }
}
