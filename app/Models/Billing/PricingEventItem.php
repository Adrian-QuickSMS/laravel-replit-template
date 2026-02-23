<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pricing Event Item — a single price change within a pricing event
 */
class PricingEventItem extends Model
{
    use HasUuids;

    protected $table = 'pricing_event_items';

    protected $fillable = [
        'pricing_event_id',
        'service_catalogue_id',
        'tier',
        'country_iso',
        'old_price',
        'new_price',
        'currency',
    ];

    protected $casts = [
        'old_price' => 'decimal:6',
        'new_price' => 'decimal:6',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function event(): BelongsTo
    {
        return $this->belongsTo(PricingEvent::class, 'pricing_event_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceCatalogue::class, 'service_catalogue_id');
    }
}
