<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Shortcode Keyword — keyword reservation on a shared short code.
 *
 * DATA CLASSIFICATION: Internal - Messaging Asset
 * SIDE: GREEN (customer portal accessible for own account)
 * TENANT ISOLATION: account_id scoped via RLS + global scope
 */
class ShortcodeKeyword extends Model
{
    use SoftDeletes;

    protected $table = 'shortcode_keywords';

    protected $keyType = 'string';
    public $incrementing = false;

    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_RELEASED = 'released';

    protected $fillable = [
        'account_id',
        'purchased_number_id',
        'keyword',
        'status',
        'setup_fee',
        'monthly_fee',
        'currency',
        'purchased_at',
        'released_at',
    ];

    protected $casts = [
        'account_id' => 'string',
        'purchased_number_id' => 'string',
        'setup_fee' => 'decimal:4',
        'monthly_fee' => 'decimal:4',
        'purchased_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            // Store keywords uppercase for case-insensitive matching
            $model->keyword = strtoupper(trim($model->keyword));
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('shortcode_keywords.account_id', $tenantId);
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

    public function purchasedNumber(): BelongsTo
    {
        return $this->belongsTo(PurchasedNumber::class, 'purchased_number_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForShortcode($query, string $purchasedNumberId)
    {
        return $query->where('purchased_number_id', $purchasedNumberId);
    }

    /**
     * Check if a keyword is already taken on a specific shortcode (cross-tenant check).
     */
    public static function isKeywordTaken(string $purchasedNumberId, string $keyword): bool
    {
        return static::withoutGlobalScopes()
            ->where('purchased_number_id', $purchasedNumberId)
            ->where('keyword', strtoupper(trim($keyword)))
            ->whereNull('deleted_at')
            ->where('status', '!=', self::STATUS_RELEASED)
            ->exists();
    }

    // =====================================================
    // PORTAL METHODS
    // =====================================================

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'keyword' => $this->keyword,
            'status' => $this->status,
            'monthly_fee' => $this->monthly_fee,
            'currency' => $this->currency,
            'purchased_at' => $this->purchased_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
