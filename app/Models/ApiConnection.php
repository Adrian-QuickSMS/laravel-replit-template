<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * GREEN SIDE: API Connection (customer API credentials & config)
 *
 * DATA CLASSIFICATION: Confidential - Security Credentials
 * SIDE: GREEN (customer portal accessible) + ADMIN (cross-tenant)
 * TENANT ISOLATION: MANDATORY account_id on every row + RLS
 *
 * State machine: draft → active → suspended → archived (terminal)
 * Credentials: SHA-256 hashed, shown once at creation, immediate revocation on regeneration
 */
class ApiConnection extends Model
{
    protected $table = 'api_connections';

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted(): void
    {
        static::creating(function (ApiConnection $model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    protected $fillable = [
        'account_id',
        'sub_account_id',
        'name',
        'description',
        'type',
        'auth_type',
        'environment',
        'status',
        'ip_allowlist_enabled',
        'ip_allowlist',
        'webhook_dlr_url',
        'webhook_inbound_url',
        'partner_name',
        'partner_config',
        'rate_limit_per_minute',
        'created_by',
    ];

    /**
     * Credential fields are NOT in $fillable — only set via dedicated methods.
     */
    protected $hidden = [
        'api_key_hash',
        'basic_auth_password_hash',
    ];

    protected $casts = [
        'id' => 'string',
        'account_id' => 'string',
        'sub_account_id' => 'string',
        'ip_allowlist_enabled' => 'boolean',
        'ip_allowlist' => 'array',
        'partner_config' => 'array',
        'capabilities' => 'array',
        'rate_limit_per_minute' => 'integer',
        'last_used_at' => 'datetime',
        'suspended_at' => 'datetime',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $attributes = [
        'ip_allowlist' => '[]',
        'partner_config' => '{}',
        'capabilities' => '[]',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('api_connections.account_id', $tenantId);
            }
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function subAccount(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class, 'sub_account_id');
    }

    public function auditEvents(): HasMany
    {
        return $this->hasMany(ApiConnectionAuditEvent::class, 'api_connection_id');
    }

    // =====================================================
    // CREDENTIAL GENERATION
    // =====================================================

    /**
     * Generate a new API key. Stores SHA-256 hash + prefix + last 4 chars.
     * Returns the raw key (shown once, never retrievable again).
     */
    public function generateApiKey(): string
    {
        $prefix = $this->getRawOriginal('environment') === 'live' ? 'sk_live_' : 'sk_test_';
        $random = bin2hex(random_bytes(16)); // 32 hex chars
        $rawKey = $prefix . $random;

        $this->forceFill([
            'api_key_hash' => hash('sha256', $rawKey),
            'api_key_prefix' => $prefix,
            'api_key_last4' => substr($rawKey, -4),
        ])->save();

        return $rawKey;
    }

    /**
     * Generate basic auth credentials. Stores SHA-256 hash of password.
     * Returns ['username' => ..., 'password' => ...] (shown once).
     */
    public function generateBasicAuth(): array
    {
        $username = 'api_' . substr(str_replace('-', '', $this->account_id), 0, 8) . '_' . Str::random(8);
        $password = Str::random(24);

        $this->forceFill([
            'basic_auth_username' => $username,
            'basic_auth_password_hash' => hash('sha256', $password),
        ])->save();

        return ['username' => $username, 'password' => $password];
    }

    /**
     * Regenerate API key. Old key is immediately revoked.
     */
    public function regenerateApiKey(): string
    {
        return $this->generateApiKey();
    }

    /**
     * Regenerate basic auth password. Old password is immediately revoked.
     * Username remains the same.
     */
    public function regeneratePassword(): string
    {
        $password = Str::random(24);

        $this->forceFill([
            'basic_auth_password_hash' => hash('sha256', $password),
        ])->save();

        return $password;
    }

    /**
     * Verify an API key against the stored hash.
     */
    public function verifyApiKey(string $key): bool
    {
        return hash_equals($this->api_key_hash, hash('sha256', $key));
    }

    /**
     * Verify a basic auth password against the stored hash.
     */
    public function verifyPassword(string $password): bool
    {
        return hash_equals($this->basic_auth_password_hash, hash('sha256', $password));
    }

    // =====================================================
    // STATE MACHINE
    // =====================================================

    public function suspend(string $reason, string $actorId, string $actorName = null): void
    {
        if (!$this->isActive()) {
            throw new \LogicException('Only active connections can be suspended.');
        }

        $this->forceFill([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_by' => $actorName ?? $actorId,
            'suspended_reason' => $reason,
        ])->save();
    }

    public function reactivate(): void
    {
        if (!$this->isSuspended()) {
            throw new \LogicException('Only suspended connections can be reactivated.');
        }

        $this->forceFill([
            'status' => 'active',
            'suspended_at' => null,
            'suspended_by' => null,
            'suspended_reason' => null,
        ])->save();
    }

    public function archive(string $actorId, string $actorName = null): void
    {
        if (!$this->isSuspended()) {
            throw new \LogicException('Only suspended connections can be archived.');
        }

        $this->forceFill([
            'status' => 'archived',
            'archived_at' => now(),
            'archived_by' => $actorName ?? $actorId,
        ])->save();
    }

    public function convertToLive(): void
    {
        if (!$this->isTestEnvironment()) {
            throw new \LogicException('Only test connections can be converted to live.');
        }
        if ($this->isArchived()) {
            throw new \LogicException('Archived connections cannot be converted.');
        }

        $this->forceFill(['environment' => 'live'])->save();

        // Update key prefix if API Key auth
        if ($this->getRawOriginal('auth_type') === 'api_key' && $this->api_key_prefix === 'sk_test_') {
            $this->forceFill(['api_key_prefix' => 'sk_live_'])->save();
        }
    }

    public function activate(): void
    {
        if (!$this->isDraft()) {
            throw new \LogicException('Only draft connections can be activated.');
        }

        $this->forceFill(['status' => 'active'])->save();
    }

    // =====================================================
    // STATE CHECKS
    // =====================================================

    public function isActive(): bool
    {
        return $this->getRawOriginal('status') === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->getRawOriginal('status') === 'suspended';
    }

    public function isArchived(): bool
    {
        return $this->getRawOriginal('status') === 'archived';
    }

    public function isDraft(): bool
    {
        return $this->getRawOriginal('status') === 'draft';
    }

    public function isTestEnvironment(): bool
    {
        return $this->getRawOriginal('environment') === 'test';
    }

    public function isLiveEnvironment(): bool
    {
        return $this->getRawOriginal('environment') === 'live';
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('status', 'suspended');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->where('status', '!=', 'archived');
    }

    public function scopeForEnvironment(Builder $query, string $env): Builder
    {
        return $query->whereRaw("environment = ?", [$env]);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->whereRaw("type = ?", [$type]);
    }

    // =====================================================
    // SERIALIZATION
    // =====================================================

    /**
     * Customer portal representation (masked credentials).
     */
    public function toPortalArray(): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sub_account_id' => $this->sub_account_id,
            'sub_account_name' => $this->subAccount?->name,
            'type' => $this->getRawOriginal('type'),
            'auth_type' => $this->getRawOriginal('auth_type'),
            'environment' => $this->getRawOriginal('environment'),
            'status' => $this->getRawOriginal('status'),
            'ip_allowlist_enabled' => $this->ip_allowlist_enabled,
            'ip_allowlist' => $this->ip_allowlist,
            'webhook_dlr_url' => $this->webhook_dlr_url,
            'webhook_inbound_url' => $this->webhook_inbound_url,
            'partner_name' => $this->partner_name,
            'partner_config' => $this->partner_config,
            'rate_limit_per_minute' => $this->rate_limit_per_minute,
            'capabilities' => $this->capabilities,
            'last_used_at' => $this->last_used_at?->toIso8601String(),
            'last_used_ip' => $this->last_used_ip,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'updated_at' => $this->updated_at?->format('Y-m-d'),
            'suspended_at' => $this->suspended_at?->toIso8601String(),
            'suspended_reason' => $this->suspended_reason,
            'archived_at' => $this->archived_at?->toIso8601String(),
        ];

        // Masked credential display
        if ($this->getRawOriginal('auth_type') === 'api_key') {
            $data['credential_display'] = $this->api_key_prefix
                ? $this->api_key_prefix . '••••••••' . $this->api_key_last4
                : null;
        } else {
            $data['credential_display'] = $this->basic_auth_username
                ? $this->basic_auth_username . ':••••••••'
                : null;
        }

        return $data;
    }

    /**
     * Admin portal representation (includes account info).
     */
    public function toAdminArray(): array
    {
        $data = $this->toPortalArray();
        $data['account_id'] = $this->account_id;
        $data['account_name'] = $this->account?->name ?? $this->account?->company_name;
        return $data;
    }

    /**
     * Update last_used tracking (called from auth middleware).
     */
    public function touchLastUsed(string $ip): void
    {
        $this->forceFill([
            'last_used_at' => now(),
            'last_used_ip' => $ip,
        ])->save();
    }
}
