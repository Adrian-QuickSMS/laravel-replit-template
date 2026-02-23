<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Service Catalogue — reference table for all billable services
 *
 * New services can be added via the admin UI without database migrations.
 * The `slug` maps to billable_product_type ENUM for backward compatibility.
 */
class ServiceCatalogue extends Model
{
    protected $table = 'service_catalogue';

    protected $fillable = [
        'slug',
        'display_name',
        'description',
        'unit_label',
        'display_format',
        'decimal_places',
        'is_per_message',
        'is_recurring',
        'is_one_off',
        'available_on_starter',
        'available_on_enterprise',
        'bespoke_only',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'decimal_places' => 'integer',
        'is_per_message' => 'boolean',
        'is_recurring' => 'boolean',
        'is_one_off' => 'boolean',
        'available_on_starter' => 'boolean',
        'available_on_enterprise' => 'boolean',
        'bespoke_only' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function tierPrices(): HasMany
    {
        return $this->hasMany(ProductTierPrice::class, 'service_catalogue_id');
    }

    public function customerPrices(): HasMany
    {
        return $this->hasMany(CustomerPrice::class, 'service_catalogue_id');
    }

    public function pricingEventItems(): HasMany
    {
        return $this->hasMany(PricingEventItem::class, 'service_catalogue_id');
    }

    public function changeLog(): HasMany
    {
        return $this->hasMany(PricingChangeLog::class, 'service_catalogue_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTier($query, string $tier)
    {
        $column = $tier === 'starter' ? 'available_on_starter' : 'available_on_enterprise';
        return $query->where($column, true)->where('bespoke_only', false);
    }

    public function scopeBespokeOnly($query)
    {
        return $query->where('bespoke_only', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('display_name');
    }

    // =====================================================
    // HELPERS
    // =====================================================

    /**
     * Format a raw price value for display based on this service's format
     */
    public function formatPrice($rawPrice): string
    {
        if ($this->display_format === 'pounds') {
            return '£' . number_format((float) $rawPrice, $this->decimal_places);
        }

        // Pence: convert from pounds to pence for display
        $pence = bcmul((string) $rawPrice, '100', $this->decimal_places);
        return number_format((float) $pence, $this->decimal_places) . 'p';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'formatted_unit' => $this->unit_label,
        ]);
    }
}
