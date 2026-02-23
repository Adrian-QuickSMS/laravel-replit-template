<?php

namespace App\Models\Billing;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pricing Change Log — immutable audit trail for all price changes
 *
 * Records every price modification regardless of source:
 * - admin: Manual change via pricing management UI
 * - hubspot: Synced from HubSpot deal
 * - scheduled_event: Applied from a pricing event at its effective date
 */
class PricingChangeLog extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'pricing_change_log';

    protected $fillable = [
        'service_catalogue_id',
        'tier',
        'account_id',
        'country_iso',
        'old_price',
        'new_price',
        'currency',
        'effective_from',
        'source',
        'pricing_event_id',
        'reason',
        'changed_by',
        'ip_address',
        'user_agent',
        'hubspot_deal_id',
        'is_conflict',
    ];

    protected $casts = [
        'old_price' => 'decimal:6',
        'new_price' => 'decimal:6',
        'effective_from' => 'date',
        'is_conflict' => 'boolean',
        'created_at' => 'datetime',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalogue::class, 'service_catalogue_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function pricingEvent(): BelongsTo
    {
        return $this->belongsTo(PricingEvent::class, 'pricing_event_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeForTier($query, string $tier)
    {
        return $query->where('tier', $tier)->whereNull('account_id');
    }

    public function scopeForAccount($query, string $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeConflicts($query)
    {
        return $query->where('is_conflict', true);
    }

    public function scopeFromSource($query, string $source)
    {
        return $query->where('source', $source);
    }
}
