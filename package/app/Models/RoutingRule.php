<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoutingRule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_type',
        'destination_type',
        'destination_code',
        'destination_name',
        'primary_gateway_id',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function primaryGateway()
    {
        return $this->belongsTo(Gateway::class, 'primary_gateway_id');
    }

    public function gatewayWeights()
    {
        return $this->hasMany(RoutingGatewayWeight::class);
    }

    public function activeGateways()
    {
        return $this->hasMany(RoutingGatewayWeight::class)
            ->where('route_status', 'allowed')
            ->with('gateway');
    }

    public function auditLogs()
    {
        return $this->hasMany(RoutingAuditLog::class, 'entity_id')
            ->where('entity_type', 'routing_rule');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }

    public function scopeForProduct($query, $productType)
    {
        return $query->where('product_type', $productType);
    }

    public function scopeUkRoutes($query)
    {
        return $query->where('destination_type', 'UK_NETWORK');
    }

    public function scopeInternationalRoutes($query)
    {
        return $query->where('destination_type', 'INTERNATIONAL');
    }

    // Helper methods
    public function getEligibleGateways()
    {
        return $this->gatewayWeights()
            ->where('route_status', 'allowed')
            ->whereHas('gateway', function ($query) {
                $query->where('active', true);
            })
            ->orderBy('weight', 'desc')
            ->get();
    }

    public function getTotalWeight()
    {
        return $this->gatewayWeights()
            ->where('route_status', 'allowed')
            ->sum('weight');
    }

    public function validateWeights()
    {
        $total = $this->getTotalWeight();
        return $total === 100;
    }
}
