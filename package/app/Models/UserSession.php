<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GREEN SIDE: User Sessions (Laravel Sanctum Tokens)
 *
 * DATA CLASSIFICATION: Restricted - Session Management
 * SIDE: GREEN (customer portal sessions)
 * TENANT ISOLATION: Scoped via user_id → users.tenant_id
 *
 * SECURITY NOTES:
 * - Token hash stored (never plain token)
 * - Auto-expires based on account settings
 * - Portal users can view/revoke own sessions only
 * - Abilities stored as JSON for fine-grained permissions
 */
class UserSession extends Model
{
    protected $table = 'user_sessions';

    protected $fillable = [
        'user_id',
        'token_hash',
        'name',
        'abilities',
        'last_used_at',
        'last_used_ip',
        'expires_at',
    ];

    protected $casts = [
        'user_id' => 'string',
        'abilities' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * The user this session belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope: Only active (not expired) sessions
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope: Expired sessions
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Scope: Sessions for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Check if session is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if session is active
     */
    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Update last used timestamp
     */
    public function updateLastUsed(string $ipAddress): bool
    {
        return $this->update([
            'last_used_at' => now(),
            'last_used_ip' => $ipAddress,
        ]);
    }

    /**
     * Revoke this session (soft delete by expiring)
     */
    public function revoke(): bool
    {
        return $this->update([
            'expires_at' => now(),
        ]);
    }

    /**
     * Check if session has specific ability
     */
    public function hasAbility(string $ability): bool
    {
        if (empty($this->abilities)) {
            return false;
        }

        return in_array('*', $this->abilities) || in_array($ability, $this->abilities);
    }

    /**
     * Format for portal display
     */
    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_used_at' => $this->last_used_at?->toIso8601String(),
            'last_used_ip' => $this->last_used_ip,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'is_active' => $this->isActive(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
