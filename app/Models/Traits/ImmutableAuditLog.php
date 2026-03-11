<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Trait for immutable, append-only audit log models.
 *
 * Provides:
 * - UUID auto-generation on create
 * - Update/delete prevention (application-layer immutability)
 * - Tenant isolation via global scope
 * - Standard static record() factory method
 * - Common scopes for querying
 *
 * Database-level immutability (REVOKE + trigger) is handled by migrations.
 */
trait ImmutableAuditLog
{
    public function initializeImmutableAuditLog(): void
    {
        $this->incrementing = false;
        $this->timestamps = false;
        $this->keyType = 'string';
    }

    protected static function bootImmutableAuditLog(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });

        static::updating(function () {
            throw new RuntimeException('Audit log entries are immutable — updates are prohibited.');
        });

        static::deleting(function () {
            throw new RuntimeException('Audit log entries are immutable — deletes are prohibited.');
        });
    }

    /**
     * Apply tenant isolation scope for GREEN-side audit tables.
     * Override in RED-side models (e.g. AdminAuditLog) to skip this.
     */
    protected static function applyTenantScope(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where(static::getTableName() . '.account_id', $tenantId);
            }
        });
    }

    /**
     * Get table name for scope qualification. Override if needed.
     */
    protected static function getTableName(): string
    {
        return (new static)->getTable();
    }

    // =====================================================
    // COMMON SCOPES
    // =====================================================

    public function scopeForAccount(Builder $query, string $accountId): Builder
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeOfAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
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

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByActor(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // =====================================================
    // CASTS
    // =====================================================

    protected function getDefaultCasts(): array
    {
        return [
            'id' => 'string',
            'account_id' => 'string',
            'user_id' => 'string',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    // =====================================================
    // SERIALIZATION
    // =====================================================

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'user_id' => $this->user_id,
            'user_name' => $this->user_name,
            'details' => $this->details,
            'metadata' => $this->metadata,
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
