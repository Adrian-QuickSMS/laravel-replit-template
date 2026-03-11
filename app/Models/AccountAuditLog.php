<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\ImmutableAuditLog;

/**
 * Account Audit Log — immutable record of account settings and status changes.
 *
 * DATA CLASSIFICATION: Confidential - Audit Trail
 * TENANT ISOLATION: account_id + RLS
 *
 * Events: account_details_updated, account_settings_changed, test_numbers_changed,
 *         billing_config_changed, account_status_transition
 */
class AccountAuditLog extends Model
{
    use ImmutableAuditLog;

    protected $table = 'account_audit_log';

    protected $fillable = [
        'account_id',
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
        'user_id' => 'string',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'metadata' => '{}',
    ];

    protected static function boot()
    {
        parent::boot();
        static::applyTenantScope();
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // =====================================================
    // FACTORY METHOD
    // =====================================================

    public static function record(
        string $accountId,
        string $action,
        ?string $userId = null,
        ?string $userName = null,
        ?string $details = null,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return static::withoutGlobalScopes()->create([
            'account_id' => $accountId,
            'action' => $action,
            'user_id' => $userId,
            'user_name' => $userName,
            'details' => $details,
            'metadata' => $metadata,
            'ip_address' => $ipAddress ?? request()?->ip(),
            'user_agent' => $userAgent ?? request()?->userAgent(),
        ]);
    }
}
