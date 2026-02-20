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
    ];

    protected $casts = [
        'unit_price' => 'decimal:6',
        'active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

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
}
