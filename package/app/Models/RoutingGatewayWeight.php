<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutingGatewayWeight extends Model
{
    use HasFactory;

    protected $fillable = [
        'routing_rule_id',
        'gateway_id',
        'weight',
        'route_status',
        'is_primary',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'weight' => 'integer',
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function routingRule()
    {
        return $this->belongsTo(RoutingRule::class);
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }

    public function supplier()
    {
        return $this->hasOneThrough(
            Supplier::class,
            Gateway::class,
            'id',
            'id',
            'gateway_id',
            'supplier_id'
        );
    }

    // Scopes
    public function scopeAllowed($query)
    {
        return $query->where('route_status', 'allowed');
    }

    public function scopeBlocked($query)
    {
        return $query->where('route_status', 'blocked');
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeEligible($query)
    {
        return $query->where('route_status', 'allowed')
            ->whereHas('gateway', function ($q) {
                $q->where('active', true);
            });
    }

    // Helper methods
    public function isEligible()
    {
        return $this->route_status === 'allowed' &&
               $this->gateway &&
               $this->gateway->active;
    }

    public function getSupplierAttribute()
    {
        return $this->gateway ? $this->gateway->supplier : null;
    }

    public function getRateAttribute()
    {
        if (!$this->gateway || !$this->routingRule) {
            return null;
        }

        // Get latest active rate for this gateway and destination
        return RateCard::where('gateway_id', $this->gateway_id)
            ->where('product_type', $this->routingRule->product_type)
            ->where('active', true)
            ->first();
    }
}
