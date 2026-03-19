<?php

namespace App\Models\Alerting;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertHistory extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'alert_history';

    protected $fillable = [
        'alert_rule_id',
        'tenant_id',
        'trigger_key',
        'trigger_value',
        'condition_value',
        'severity',
        'category',
        'title',
        'body',
        'channels_dispatched',
        'status',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'trigger_value' => 'decimal:4',
        'condition_value' => 'decimal:4',
        'channels_dispatched' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->created_at)) {
                $model->created_at = now();
            }
        });

        // Immutable — prevent updates and deletes
        static::updating(function () {
            return false;
        });

        static::deleting(function () {
            return false;
        });
    }

    // --- Relationships ---

    public function alertRule(): BelongsTo
    {
        return $this->belongsTo(AlertRule::class);
    }

    // --- Scopes ---

    public function scopeForTenant(Builder $query, ?string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForTriggerKey(Builder $query, string $triggerKey): Builder
    {
        return $query->where('trigger_key', $triggerKey);
    }

    public function scopeOfStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeOfSeverity(Builder $query, string $severity): Builder
    {
        return $query->where('severity', $severity);
    }

    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeSince(Builder $query, $datetime): Builder
    {
        return $query->where('created_at', '>=', $datetime);
    }

    /**
     * Return a safe representation for customer portal API responses.
     */
    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'trigger_key' => $this->trigger_key,
            'trigger_value' => $this->trigger_value,
            'severity' => $this->severity,
            'category' => $this->category,
            'title' => $this->title,
            'body' => $this->body,
            'status' => $this->status,
            'channels_dispatched' => $this->channels_dispatched,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
