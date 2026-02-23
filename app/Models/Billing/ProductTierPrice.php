<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductTierPrice extends Model
{
    use HasUuids;

    protected $table = 'product_tier_prices';

    protected $fillable = [
        'product_tier', 'product_type', 'country_iso', 'unit_price',
        'currency', 'valid_from', 'valid_to', 'active', 'created_by',
        'service_catalogue_id', 'pricing_event_id',
    ];

    protected $casts = [
        'unit_price' => 'decimal:6',
        'active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'service_catalogue_id' => 'integer',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function service()
    {
        return $this->belongsTo(ServiceCatalogue::class, 'service_catalogue_id');
    }

    public function pricingEvent()
    {
        return $this->belongsTo(PricingEvent::class, 'pricing_event_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeValidAt($query, $date = null)
    {
        $date = $date ?? now()->toDateString();
        return $query->where('valid_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $date);
            });
    }

    public function scopeForLookup($query, string $tier, string $productType, ?string $countryIso)
    {
        return $query->where('product_tier', $tier)
            ->where('product_type', $productType)
            ->where('country_iso', $countryIso)
            ->active()
            ->validAt();
    }

    public function scopeForTier($query, string $tier)
    {
        return $query->where('product_tier', $tier);
    }

    public function scopeFuture($query, $date = null)
    {
        $date = $date ?? now()->toDateString();
        return $query->where('valid_from', '>', $date);
    }
}
