<?php

namespace App\Models\Alerting;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AlertPreference extends Model
{
    use HasUuids;

    protected $table = 'alert_preferences';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'category',
        'channels',
        'is_muted',
        'muted_until',
    ];

    protected $casts = [
        'channels' => 'array',
        'is_muted' => 'boolean',
        'muted_until' => 'datetime',
    ];

    // --- Scopes ---

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    // --- Business Logic ---

    public function isCurrentlyMuted(): bool
    {
        if (!$this->is_muted) {
            return false;
        }

        // If muted_until is set and has passed (or is exactly now), no longer muted
        if ($this->muted_until && $this->muted_until <= now()) {
            return false;
        }

        return true;
    }

    public function getEffectiveChannels(): array
    {
        if ($this->isCurrentlyMuted()) {
            return [];
        }

        return $this->channels ?? ['in_app', 'email'];
    }
}
