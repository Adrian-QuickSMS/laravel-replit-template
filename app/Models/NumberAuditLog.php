<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\ImmutableAuditLog;

/**
 * Number Audit Log — immutable record of number management events
 * (beyond purchases, which are covered by purchase_audit_logs).
 *
 * DATA CLASSIFICATION: Confidential - Audit Trail
 * TENANT ISOLATION: account_id + RLS
 *
 * Events: vmn_assigned, vmn_bulk_assigned, vmn_released, vmn_bulk_released,
 *         auto_reply_created, auto_reply_updated, auto_reply_deleted,
 *         number_returned_to_pool, number_configured, number_suspended,
 *         number_reactivated
 */
class NumberAuditLog extends Model
{
    use ImmutableAuditLog;

    protected $table = 'number_audit_log';

    protected $fillable = [
        'account_id',
        'number_id',
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
        'number_id' => 'string',
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

    public function number(): BelongsTo
    {
        return $this->belongsTo(PurchasedNumber::class, 'number_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeForNumber(Builder $query, string $numberId): Builder
    {
        return $query->where('number_id', $numberId);
    }

    // =====================================================
    // FACTORY METHOD
    // =====================================================

    public static function record(
        string $accountId,
        string $action,
        ?string $numberId = null,
        ?string $userId = null,
        ?string $userName = null,
        ?string $details = null,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return static::withoutGlobalScopes()->create([
            'account_id' => $accountId,
            'number_id' => $numberId,
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
