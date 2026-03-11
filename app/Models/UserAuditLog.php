<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\ImmutableAuditLog;

/**
 * User & Sub-Account Audit Log — immutable record of user management
 * and sub-account lifecycle events.
 *
 * DATA CLASSIFICATION: Confidential - Audit Trail
 * TENANT ISOLATION: account_id + RLS
 *
 * User events: user_invited, invitation_accepted, invitation_revoked,
 *              user_role_changed, user_permissions_changed, user_sender_capability_changed,
 *              user_suspended, user_reactivated, ownership_transferred
 *
 * Sub-account events (module='sub_account'): sub_account_created, sub_account_edited,
 *              sub_account_limits_updated, sub_account_suspended, sub_account_reactivated,
 *              sub_account_archived
 */
class UserAuditLog extends Model
{
    use ImmutableAuditLog;

    protected $table = 'user_audit_log';

    protected $fillable = [
        'account_id',
        'target_user_id',
        'module',
        'action',
        'user_id',
        'user_name',
        'details',
        'metadata',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'target_user_id' => 'string',
        'user_id' => 'string',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'metadata' => '{}',
        'module' => 'user_management',
    ];

    protected static function boot()
    {
        parent::boot();
        static::applyTenantScope();
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeForTargetUser(Builder $query, string $userId): Builder
    {
        return $query->where('target_user_id', $userId);
    }

    public function scopeUserManagement(Builder $query): Builder
    {
        return $query->where('module', 'user_management');
    }

    public function scopeSubAccount(Builder $query): Builder
    {
        return $query->where('module', 'sub_account');
    }

    public function scopeForModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    // =====================================================
    // FACTORY METHODS
    // =====================================================

    public static function record(
        string $accountId,
        string $action,
        ?string $targetUserId = null,
        ?string $userId = null,
        ?string $userName = null,
        ?string $details = null,
        array $metadata = [],
        string $module = 'user_management',
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return static::withoutGlobalScopes()->create([
            'account_id' => $accountId,
            'target_user_id' => $targetUserId,
            'module' => $module,
            'action' => $action,
            'user_id' => $userId,
            'user_name' => $userName,
            'details' => $details,
            'metadata' => $metadata,
            'ip_address' => $ipAddress ?? request()?->ip(),
            'user_agent' => $userAgent ?? request()?->userAgent(),
        ]);
    }

    /**
     * Convenience method for sub-account events.
     */
    public static function recordSubAccountEvent(
        string $accountId,
        string $action,
        ?string $subAccountId = null,
        ?string $userId = null,
        ?string $userName = null,
        ?string $details = null,
        array $metadata = []
    ): self {
        return static::record(
            accountId: $accountId,
            action: $action,
            targetUserId: $subAccountId,
            userId: $userId,
            userName: $userName,
            details: $details,
            metadata: $metadata,
            module: 'sub_account'
        );
    }
}
