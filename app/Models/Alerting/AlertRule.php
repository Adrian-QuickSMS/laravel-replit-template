<?php

namespace App\Models\Alerting;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlertRule extends Model
{
    use HasUuids;

    protected $table = 'alert_rules';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'category',
        'trigger_type',
        'trigger_key',
        'condition_operator',
        'condition_value',
        'channels',
        'frequency',
        'cooldown_minutes',
        'escalation_rules',
        'recipients',
        'is_enabled',
        'is_system_default',
        'last_triggered_at',
        'last_value_snapshot',
        'metadata',
    ];

    protected $casts = [
        'condition_value' => 'decimal:4',
        'channels' => 'array',
        'escalation_rules' => 'array',
        'recipients' => 'array',
        'is_enabled' => 'boolean',
        'is_system_default' => 'boolean',
        'last_triggered_at' => 'datetime',
        'last_value_snapshot' => 'decimal:4',
        'metadata' => 'array',
    ];

    // --- Relationships ---

    public function alertHistory(): HasMany
    {
        return $this->hasMany(AlertHistory::class);
    }

    // --- Scopes ---

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeForTenant(Builder $query, ?string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForTriggerKey(Builder $query, string $triggerKey): Builder
    {
        return $query->where('trigger_key', $triggerKey);
    }

    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeSystemDefaults(Builder $query): Builder
    {
        return $query->where('is_system_default', true);
    }

    // --- Business Logic ---

    public function isOnCooldown(): bool
    {
        if (!$this->last_triggered_at) {
            return false;
        }

        return $this->last_triggered_at->diffInMinutes(now()) < $this->cooldown_minutes;
    }

    public function evaluateCondition(float $triggerValue): bool
    {
        if ($this->trigger_type === 'event') {
            return true; // Event-based triggers always match
        }

        $conditionValue = (float) $this->condition_value;

        return match ($this->condition_operator) {
            'lt' => $triggerValue < $conditionValue,
            'gt' => $triggerValue > $conditionValue,
            'lte' => $triggerValue <= $conditionValue,
            'gte' => $triggerValue >= $conditionValue,
            'eq' => abs($triggerValue - $conditionValue) < 0.0001,
            'drops_by' => $this->last_value_snapshot !== null
                && ($this->last_value_snapshot - $triggerValue) >= $conditionValue,
            'increases_by' => $this->last_value_snapshot !== null
                && ($triggerValue - $this->last_value_snapshot) >= $conditionValue,
            default => false,
        };
    }

    public function getEscalationChannels(float $triggerValue): array
    {
        if (!$this->escalation_rules) {
            return [];
        }

        $extraChannels = [];
        foreach ($this->escalation_rules as $rule) {
            $threshold = (float) ($rule['condition_value'] ?? 0);
            $shouldEscalate = match ($this->condition_operator) {
                'lt', 'lte' => $triggerValue <= $threshold,
                'gt', 'gte' => $triggerValue >= $threshold,
                default => true,
            };

            if ($shouldEscalate) {
                $extraChannels = array_merge($extraChannels, $rule['channels'] ?? []);
            }
        }

        return array_unique($extraChannels);
    }

    /**
     * Atomically mark the rule as triggered.
     * Uses raw UPDATE to prevent race conditions with concurrent evaluations.
     */
    public function markTriggered(float $currentValue): void
    {
        \Illuminate\Support\Facades\DB::table('alert_rules')
            ->where('id', $this->id)
            ->update([
                'last_triggered_at' => now(),
                'last_value_snapshot' => $currentValue,
                'updated_at' => now(),
            ]);

        $this->refresh();
    }

    /**
     * Update the value snapshot without marking as triggered.
     * Used by once_per_breach to track recovery.
     */
    public function updateSnapshot(float $currentValue): void
    {
        $this->update(['last_value_snapshot' => $currentValue]);
    }

    /**
     * Return a safe representation for customer portal API responses.
     */
    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'trigger_type' => $this->trigger_type,
            'trigger_key' => $this->trigger_key,
            'condition_operator' => $this->condition_operator,
            'condition_value' => $this->condition_value,
            'channels' => $this->channels,
            'frequency' => $this->frequency,
            'cooldown_minutes' => $this->cooldown_minutes,
            'escalation_rules' => $this->escalation_rules,
            'is_enabled' => $this->is_enabled,
            'is_system_default' => $this->is_system_default,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
