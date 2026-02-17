<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit trail for API Connection lifecycle events.
 *
 * DATA CLASSIFICATION: Internal - Audit Trail
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 *
 * Append-only — no updates or deletes from the application.
 *
 * Event types:
 * - created, activated, suspended, reactivated, archived
 * - key_regenerated, password_changed, converted_to_live
 * - endpoints_updated, security_updated, updated
 */
class ApiConnectionAuditEvent extends Model
{
    protected $table = 'api_connection_audit_events';

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (ApiConnectionAuditEvent $model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $fillable = [
        'account_id',
        'api_connection_id',
        'event_type',
        'actor_type',
        'actor_id',
        'actor_name',
        'metadata',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'api_connection_id' => 'string',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected $attributes = [
        'metadata' => '{}',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('api_connection_audit_events.account_id', $tenantId);
            }
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function apiConnection(): BelongsTo
    {
        return $this->belongsTo(ApiConnection::class, 'api_connection_id');
    }

    // =====================================================
    // FACTORY METHOD
    // =====================================================

    /**
     * Record an audit event for an API connection.
     */
    public static function record(
        ApiConnection $connection,
        string $eventType,
        string $actorType,
        ?string $actorId = null,
        ?string $actorName = null,
        array $metadata = [],
        ?string $ipAddress = null
    ): self {
        return static::create([
            'account_id' => $connection->account_id,
            'api_connection_id' => $connection->id,
            'event_type' => $eventType,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'actor_name' => $actorName,
            'metadata' => $metadata,
            'ip_address' => $ipAddress ?? request()->ip(),
            'created_at' => now(),
        ]);
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeForConnection(Builder $query, string $connectionId): Builder
    {
        return $query->where('api_connection_id', $connectionId);
    }

    public function scopeOfType(Builder $query, string $eventType): Builder
    {
        return $query->where('event_type', $eventType);
    }

    // =====================================================
    // SERIALIZATION
    // =====================================================

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'event_type' => $this->event_type,
            'actor_type' => $this->actor_type,
            'actor_id' => $this->actor_id,
            'actor_name' => $this->actor_name,
            'metadata' => $this->metadata,
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
