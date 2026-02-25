<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Number Auto-Reply Rule — keyword-based auto-reply configuration.
 *
 * DATA CLASSIFICATION: Internal - Messaging Configuration
 * SIDE: GREEN (customer portal accessible for own account)
 * TENANT ISOLATION: account_id scoped via RLS + global scope
 */
class NumberAutoReplyRule extends Model
{
    protected $table = 'number_auto_reply_rules';

    protected $keyType = 'string';
    public $incrementing = false;

    const MATCH_EXACT = 'exact';
    const MATCH_STARTS_WITH = 'starts_with';
    const MATCH_CONTAINS = 'contains';

    protected $fillable = [
        'account_id',
        'purchased_number_id',
        'keyword',
        'reply_content',
        'match_type',
        'is_active',
        'priority',
        'charge_for_reply',
    ];

    protected $casts = [
        'account_id' => 'string',
        'purchased_number_id' => 'string',
        'is_active' => 'boolean',
        'priority' => 'integer',
        'charge_for_reply' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = auth()->check() && auth()->user()->tenant_id
                ? auth()->user()->tenant_id
                : session('customer_tenant_id');
            if ($tenantId) {
                $builder->where('number_auto_reply_rules.account_id', $tenantId);
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
    // MATCHING LOGIC
    // =====================================================

    /**
     * Find the matching auto-reply rule for an inbound message.
     */
    public static function findMatchingRule(string $purchasedNumberId, string $inboundText): ?self
    {
        $rules = static::where('purchased_number_id', $purchasedNumberId)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();

        $normalizedText = strtoupper(trim($inboundText));

        foreach ($rules as $rule) {
            $keyword = strtoupper($rule->keyword);

            if ($keyword === '*') {
                return $rule; // Catch-all
            }

            switch ($rule->match_type) {
                case self::MATCH_EXACT:
                    if ($normalizedText === $keyword) {
                        return $rule;
                    }
                    break;
                case self::MATCH_STARTS_WITH:
                    if (str_starts_with($normalizedText, $keyword)) {
                        return $rule;
                    }
                    break;
                case self::MATCH_CONTAINS:
                    if (str_contains($normalizedText, $keyword)) {
                        return $rule;
                    }
                    break;
            }
        }

        return null;
    }

    // =====================================================
    // PORTAL METHODS
    // =====================================================

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'keyword' => $this->keyword,
            'reply_content' => $this->reply_content,
            'match_type' => $this->match_type,
            'is_active' => $this->is_active,
            'priority' => $this->priority,
            'charge_for_reply' => $this->charge_for_reply,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
