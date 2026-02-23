<?php

namespace App\Models\Billing;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pricing Event — groups multiple price changes into a single scheduled event
 *
 * Status lifecycle: draft → scheduled → applied (at midnight) or cancelled
 */
class PricingEvent extends Model
{
    use HasUuids;

    protected $table = 'pricing_events';

    const STATUS_DRAFT = 'draft';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_APPLIED = 'applied';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'name',
        'description',
        'effective_date',
        'reason',
        'status',
        'created_by',
        'applied_at',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'applied_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function items(): HasMany
    {
        return $this->hasMany(PricingEventItem::class, 'pricing_event_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function changeLogEntries(): HasMany
    {
        return $this->hasMany(PricingChangeLog::class, 'pricing_event_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeScheduled($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_SCHEDULED]);
    }

    public function scopeDueForApplication($query, $date = null)
    {
        $date = $date ?? now()->toDateString();
        return $query->where('status', self::STATUS_SCHEDULED)
            ->where('effective_date', '<=', $date);
    }

    // =====================================================
    // STATUS HELPERS
    // =====================================================

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isApplied(): bool
    {
        return $this->status === self::STATUS_APPLIED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED]);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SCHEDULED]);
    }

    /**
     * Count how many Starter/Enterprise accounts will be affected
     */
    public function getAffectedAccountCounts(): array
    {
        $tiers = $this->items->pluck('tier')->unique();
        $counts = [];

        foreach ($tiers as $tier) {
            $counts[$tier] = \App\Models\Account::where('product_tier', $tier)->count();
        }

        $bespokeCount = \App\Models\Account::where('product_tier', 'bespoke')->count();

        return [
            'affected' => $counts,
            'bespoke_unaffected' => $bespokeCount,
        ];
    }
}
