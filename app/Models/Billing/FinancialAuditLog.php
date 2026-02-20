<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use RuntimeException;

class FinancialAuditLog extends Model
{
    use HasUuids;

    protected $table = 'financial_audit_log';
    public $timestamps = false;

    protected $fillable = [
        'actor_id', 'actor_type', 'action', 'entity_type',
        'entity_id', 'old_values', 'new_values',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException('Financial audit log entries are immutable.');
        });
        static::deleting(function () {
            throw new RuntimeException('Financial audit log entries are immutable.');
        });
    }

    public static function record(
        string $action,
        string $entityType,
        ?string $entityId,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $actorId = null,
        string $actorType = 'system'
    ): self {
        return static::create([
            'actor_id' => $actorId,
            'actor_type' => $actorType,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
