<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Admin Audit Log — immutable record of admin console operations.
 *
 * DATA CLASSIFICATION: Restricted - Security Audit Trail
 * SIDE: RED (never accessible to customer portal)
 * TENANT ISOLATION: None (RED zone, admin-only)
 *
 * Events: All AdminAuditService event types including impersonation,
 *         admin user management, approval decisions, config changes.
 */
class AdminAuditLog extends Model
{
    protected $table = 'admin_audit_log';

    public $incrementing = false;
    public $timestamps = false;
    protected $keyType = 'string';

    protected $fillable = [
        'admin_user_id',
        'admin_user_name',
        'action',
        'category',
        'severity',
        'target_type',
        'target_id',
        'target_account_id',
        'details',
        'metadata',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'id' => 'string',
        'admin_user_id' => 'string',
        'target_id' => 'string',
        'target_account_id' => 'string',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'metadata' => '{}',
    ];

    // =====================================================
    // IMMUTABILITY (application layer)
    // =====================================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });

        static::updating(function () {
            throw new RuntimeException('Admin audit log entries are immutable — updates are prohibited.');
        });

        static::deleting(function () {
            throw new RuntimeException('Admin audit log entries are immutable — deletes are prohibited.');
        });
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeOfAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeOfCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeOfSeverity(Builder $query, string $severity): Builder
    {
        return $query->where('severity', $severity);
    }

    public function scopeByAdmin(Builder $query, string $adminUserId): Builder
    {
        return $query->where('admin_user_id', $adminUserId);
    }

    public function scopeForTargetAccount(Builder $query, string $accountId): Builder
    {
        return $query->where('target_account_id', $accountId);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }
        return $query;
    }

    // =====================================================
    // FACTORY METHOD
    // =====================================================

    public static function record(
        string $action,
        string $category,
        string $severity,
        ?string $adminUserId = null,
        ?string $adminUserName = null,
        ?string $targetType = null,
        ?string $targetId = null,
        ?string $targetAccountId = null,
        ?string $details = null,
        array $metadata = [],
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): self {
        return static::create([
            'admin_user_id' => $adminUserId,
            'admin_user_name' => $adminUserName,
            'action' => $action,
            'category' => $category,
            'severity' => $severity,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'target_account_id' => $targetAccountId,
            'details' => $details,
            'metadata' => $metadata,
            'ip_address' => $ipAddress ?? request()?->ip(),
            'user_agent' => $userAgent ?? request()?->userAgent(),
        ]);
    }

    // =====================================================
    // SERIALIZATION
    // =====================================================

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'admin_user_id' => $this->admin_user_id,
            'admin_user_name' => $this->admin_user_name,
            'action' => $this->action,
            'category' => $this->category,
            'severity' => $this->severity,
            'target_type' => $this->target_type,
            'target_id' => $this->target_id,
            'details' => $this->details,
            'metadata' => $this->metadata,
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
