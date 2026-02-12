<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * GREEN SIDE: API Tokens (for programmatic access)
 *
 * DATA CLASSIFICATION: Restricted - API Authentication
 * SIDE: GREEN (customer portal accessible)
 * TENANT ISOLATION: MANDATORY tenant_id on every row
 *
 * SECURITY NOTES:
 * - Token hash stored (plain token shown ONCE on creation)
 * - Token prefix for identification (first 8 chars)
 * - Scopes limit what token can access
 * - IP whitelist optional
 * - Expiry date mandatory
 * - Portal users can create/revoke own account's tokens only
 */
class ApiToken extends Model
{
    protected $table = 'api_tokens';

    // UUID primary key
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'token_hash',
        'token_prefix',
        'scopes',
        'access_level',
        'ip_whitelist',
        'last_used_at',
        'last_used_ip',
        'expires_at',
        'revoked_at',
    ];

    protected $hidden = [
        'token_hash',
    ];

    protected $casts = [
        'id' => 'string',
        'tenant_id' => 'string',
        'user_id' => 'string',
        'scopes' => 'array',
        'ip_whitelist' => 'array',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method - apply global tenant scope
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-scope all queries by tenant_id if authenticated
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('api_tokens.tenant_id', auth()->user()->tenant_id);
            }
        });
    }

    /**
     * Convert UUID binary to string
     */
    public function getIdAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && strlen($value) === 36) {
            return $value;
        }

        $hex = bin2hex($value);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20)
        );
    }

    /**
     * Convert UUID string to binary
     */
    public function setIdAttribute($value)
    {
        if ($value === null) {
            return;
        }

        if (is_string($value) && strlen($value) === 16) {
            $this->attributes['id'] = $value;
            return;
        }

        $hex = str_replace('-', '', $value);
        $this->attributes['id'] = hex2bin($hex);
    }

    /**
     * Convert tenant_id binary to string
     */
    public function getTenantIdAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && strlen($value) === 36) {
            return $value;
        }

        $hex = bin2hex($value);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20)
        );
    }

    /**
     * Convert tenant_id string to binary
     */
    public function setTenantIdAttribute($value)
    {
        if ($value === null) {
            return;
        }

        if (is_string($value) && strlen($value) === 16) {
            $this->attributes['tenant_id'] = $value;
            return;
        }

        $hex = str_replace('-', '', $value);
        $this->attributes['tenant_id'] = hex2bin($hex);
    }

    /**
     * Convert user_id binary to string
     */
    public function getUserIdAttribute($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && strlen($value) === 36) {
            return $value;
        }

        $hex = bin2hex($value);
        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20)
        );
    }

    /**
     * Convert user_id string to binary
     */
    public function setUserIdAttribute($value)
    {
        if ($value === null) {
            return;
        }

        if (is_string($value) && strlen($value) === 16) {
            $this->attributes['user_id'] = $value;
            return;
        }

        $hex = str_replace('-', '', $value);
        $this->attributes['user_id'] = hex2bin($hex);
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * The account/tenant this token belongs to
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'tenant_id');
    }

    /**
     * The user who created this token
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope: Only active tokens (not expired or revoked)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at')
            ->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: Revoked tokens
     */
    public function scopeRevoked($query)
    {
        return $query->whereNotNull('revoked_at');
    }

    /**
     * Scope: Expired tokens
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<=', now());
    }

    /**
     * Scope: By access level
     */
    public function scopeByAccessLevel($query, $level)
    {
        return $query->where('access_level', $level);
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Check if token is active
     */
    public function isActive(): bool
    {
        if ($this->revoked_at) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if token is revoked
     */
    public function isRevoked(): bool
    {
        return !is_null($this->revoked_at);
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Revoke this token
     */
    public function revoke(): bool
    {
        return $this->update([
            'revoked_at' => now(),
        ]);
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
     * Check if IP is whitelisted
     */
    public function isIpWhitelisted(string $ip): bool
    {
        // If no whitelist, allow all
        if (empty($this->ip_whitelist)) {
            return true;
        }

        return in_array($ip, $this->ip_whitelist);
    }

    /**
     * Check if token has specific scope
     */
    public function hasScope(string $scope): bool
    {
        if (empty($this->scopes)) {
            return false;
        }

        return in_array('*', $this->scopes) || in_array($scope, $this->scopes);
    }

    /**
     * Check if token has any of the specified scopes
     */
    public function hasAnyScope(array $scopes): bool
    {
        if (empty($this->scopes)) {
            return false;
        }

        if (in_array('*', $this->scopes)) {
            return true;
        }

        return !empty(array_intersect($scopes, $this->scopes));
    }

    /**
     * Format for portal display (never show token hash)
     */
    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'token_prefix' => $this->token_prefix,
            'scopes' => $this->scopes,
            'access_level' => $this->access_level,
            'has_ip_whitelist' => !empty($this->ip_whitelist),
            'ip_count' => count($this->ip_whitelist ?? []),
            'last_used_at' => $this->last_used_at?->toIso8601String(),
            'last_used_ip' => $this->last_used_ip,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'is_active' => $this->isActive(),
            'is_expired' => $this->isExpired(),
            'is_revoked' => $this->isRevoked(),
            'created_at' => $this->created_at->toIso8601String(),
            'created_by' => $this->user?->first_name . ' ' . $this->user?->last_name,
        ];
    }
}
