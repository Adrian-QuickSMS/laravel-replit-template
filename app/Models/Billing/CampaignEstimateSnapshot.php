<?php

namespace App\Models\Billing;

use App\Models\Account;
use App\Models\Campaign;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignEstimateSnapshot extends Model
{
    use HasUuids;

    protected $table = 'campaign_estimate_snapshots';

    protected $fillable = [
        'campaign_id',
        'account_id',
        'product_type',
        'campaign_type',
        'currency',
        'total_recipients',
        'estimated_cost',
        'vat_rate',
        'vat_amount',
        'estimated_cost_inc_vat',
        'reserved_amount',
        'available_balance_at_send',
        'is_postpay',
        'product_tier',
        'country_breakdown',
        'pricing_snapshot',
        'estimation_errors',
        'rcs_penetration_rate',
        'expected_rcs_count',
        'expected_sms_fallback_count',
        'reservation_id',
        'created_by',
        'snapshot_at',
    ];

    protected $casts = [
        'total_recipients' => 'integer',
        'estimated_cost' => 'decimal:4',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:4',
        'estimated_cost_inc_vat' => 'decimal:4',
        'reserved_amount' => 'decimal:4',
        'available_balance_at_send' => 'decimal:4',
        'is_postpay' => 'boolean',
        'country_breakdown' => 'array',
        'pricing_snapshot' => 'array',
        'estimation_errors' => 'array',
        'rcs_penetration_rate' => 'decimal:2',
        'expected_rcs_count' => 'integer',
        'expected_sms_fallback_count' => 'integer',
        'snapshot_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public static function resolveBillableProductType(string $campaignType): string
    {
        return match ($campaignType) {
            Campaign::TYPE_RCS_CAROUSEL => 'rcs_single',
            default => $campaignType,
        };
    }

    public function toPortalArray(): array
    {
        return [
            'id' => $this->id,
            'campaign_id' => $this->campaign_id,
            'product_type' => $this->product_type,
            'campaign_type' => $this->campaign_type,
            'currency' => $this->currency,
            'total_recipients' => $this->total_recipients,
            'estimated_cost' => $this->estimated_cost,
            'vat_rate' => $this->vat_rate,
            'vat_amount' => $this->vat_amount,
            'estimated_cost_inc_vat' => $this->estimated_cost_inc_vat,
            'reserved_amount' => $this->reserved_amount,
            'available_balance_at_send' => $this->available_balance_at_send,
            'is_postpay' => $this->is_postpay,
            'product_tier' => $this->product_tier,
            'country_breakdown' => $this->country_breakdown,
            'estimation_errors' => $this->estimation_errors,
            'rcs_penetration_rate' => $this->rcs_penetration_rate,
            'expected_rcs_count' => $this->expected_rcs_count,
            'expected_sms_fallback_count' => $this->expected_sms_fallback_count,
            'snapshot_at' => $this->snapshot_at?->toIso8601String(),
        ];
    }

    public function toSummaryArray(): array
    {
        return [
            'estimated_cost' => $this->estimated_cost,
            'vat_amount' => $this->vat_amount,
            'estimated_cost_inc_vat' => $this->estimated_cost_inc_vat,
            'reserved_amount' => $this->reserved_amount,
            'currency' => $this->currency,
            'total_recipients' => $this->total_recipients,
            'snapshot_at' => $this->snapshot_at?->toIso8601String(),
        ];
    }
}
