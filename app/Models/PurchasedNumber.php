<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Purchased Number — VMN or shortcode owned by a tenant account.
 *
 * DATA CLASSIFICATION: Internal - Messaging Asset
 * SIDE: GREEN (customer portal accessible for own account)
 * TENANT ISOLATION: account_id scoped via RLS + global scope
 */
class PurchasedNumber extends Model
{
    use SoftDeletes;

    protected $table = 'purchased_numbers';

    protected $keyType = 'string';
    public $incrementing = false;

    // =====================================================
    // STATUS CONSTANTS
    // =====================================================

    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_RELEASED = 'released';

    const TYPE_VMN = 'vmn';
    const TYPE_SHARED_SHORTCODE = 'shared_shortcode';
    const TYPE_DEDICATED_SHORTCODE = 'dedicated_shortcode';

    // =====================================================
    // MODEL CONFIGURATION
    // =====================================================

    protected $fillable = [
        'account_id',
        'vmn_pool_id',
        'number',
        'number_type',
        'country_iso',
        'friendly_name',
        'status',
        'setup_fee',
        'monthly_fee',
        'currency',
        'purchased_at',
        'suspended_at',
        'released_at',
        'last_used_at',
        'configuration',
        'sender_id_id',
    ];

    protected $casts = [
        'account_id' => 'string',
        'vmn_pool_id' => 'string',
        'sender_id_id' => 'string',
        'setup_fee' => 'decimal:4',
        'monthly_fee' => 'decimal:4',
        'configuration' => 'array',
        'purchased_at' => 'datetime',
        'suspended_at' => 'datetime',
        'released_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        // Tenant isolation global scope (same pattern as SenderId)
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('purchased_numbers.account_id', $tenantId);
            } else {
                $builder->whereRaw('1 = 0');
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

    public function poolNumber(): BelongsTo
    {
        return $this->belongsTo(VmnPoolNumber::class, 'vmn_pool_id');
    }

    public function senderId(): BelongsTo
    {
        return $this->belongsTo(SenderId::class, 'sender_id_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(NumberAssignment::class, 'purchased_number_id');
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(ShortcodeKeyword::class, 'purchased_number_id');
    }

    public function autoReplyRules(): HasMany
    {
        return $this->hasMany(NumberAutoReplyRule::class, 'purchased_number_id')
            ->orderBy('priority', 'desc');
    }

    // =====================================================
    // STATUS CHECKS
    // =====================================================

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isReleased(): bool
    {
        return $this->status === self::STATUS_RELEASED;
    }

    public function isVmn(): bool
    {
        return $this->number_type === self::TYPE_VMN;
    }

    public function isShortcode(): bool
    {
        return in_array($this->number_type, [self::TYPE_SHARED_SHORTCODE, self::TYPE_DEDICATED_SHORTCODE]);
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    public function scopeVmns($query)
    {
        return $query->where('number_type', self::TYPE_VMN);
    }

    public function scopeShortcodes($query)
    {
        return $query->whereIn('number_type', [self::TYPE_SHARED_SHORTCODE, self::TYPE_DEDICATED_SHORTCODE]);
    }

    public function scopeForAccount($query, string $accountId)
    {
        return $query->withoutGlobalScope('tenant')->where('account_id', $accountId);
    }

    /**
     * Numbers usable by a specific user (checks assignments like SenderId)
     */
    public function scopeUsableByUser($query, User $user)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('account_id', $user->tenant_id)
            ->where(function ($q) use ($user) {
                $q->whereDoesntHave('assignments')
                    ->orWhereHas('assignments', function ($assignQ) use ($user) {
                        $assignQ->where(function ($innerQ) use ($user) {
                            $innerQ->where('assignable_type', User::class)
                                ->where('assignable_id', $user->id);
                        });
                        if ($user->sub_account_id) {
                            $assignQ->orWhere(function ($innerQ) use ($user) {
                                $innerQ->where('assignable_type', SubAccount::class)
                                    ->where('assignable_id', $user->sub_account_id);
                            });
                        }
                    });
            });
    }

    // =====================================================
    // CONFIGURATION HELPERS
    // =====================================================

    public function getForwardingUrl(): ?string
    {
        return $this->configuration['forwarding_url'] ?? null;
    }

    public function getForwardingEmail(): ?string
    {
        return $this->configuration['forwarding_email'] ?? null;
    }

    public function getForwardingAuthHeaders(): ?array
    {
        return $this->configuration['forwarding_auth_headers'] ?? null;
    }

    public function getRetryPolicy(): array
    {
        return $this->configuration['retry_policy'] ?? [
            'max_retries' => 3,
            'retry_delay_seconds' => 5,
            'backoff_multiplier' => 2,
        ];
    }

    public function updateConfiguration(array $config): void
    {
        $this->update([
            'configuration' => array_merge($this->configuration ?? [], $config),
        ]);
    }

    // =====================================================
    // LAST USED AT
    // =====================================================

    public function touchLastUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    // =====================================================
    // PORTAL METHODS
    // =====================================================

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'number_type' => $this->number_type,
            'country_iso' => $this->country_iso,
            'friendly_name' => $this->friendly_name,
            'status' => $this->status,
            'monthly_fee' => $this->monthly_fee,
            'currency' => $this->currency,
            'purchased_at' => $this->purchased_at?->toIso8601String(),
            'last_used_at' => $this->last_used_at?->toIso8601String(),
            'configuration' => [
                'forwarding_url' => $this->getForwardingUrl(),
                'forwarding_email' => $this->getForwardingEmail(),
                'has_auth_headers' => !empty($this->getForwardingAuthHeaders()),
                'retry_policy' => $this->getRetryPolicy(),
            ],
            'sender_id_id' => $this->sender_id_id,
            'keywords' => $this->isShortcode() ? $this->keywords->map->toPortalArray()->toArray() : null,
            'assignments_count' => $this->assignments_count ?? $this->assignments()->count(),
            'auto_reply_rules_count' => $this->auto_reply_rules_count ?? $this->autoReplyRules()->count(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    public function toAdminArray(): array
    {
        return array_merge($this->toPortalArray(), [
            'account_id' => $this->account_id,
            'vmn_pool_id' => $this->vmn_pool_id,
            'setup_fee' => $this->setup_fee,
            'suspended_at' => $this->suspended_at?->toIso8601String(),
            'released_at' => $this->released_at?->toIso8601String(),
            'configuration' => $this->configuration,
        ]);
    }
}
