<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutingGatewayWeight extends Model
{
    protected $fillable = [
        'routing_rule_id',
        'gateway_id',
        'supplier_id',
        'weight',
        'priority_order',
        'is_fallback',
        'status',
        'max_tps',
        'daily_cap',
        'current_daily_count',
        'cost_per_message_gbp',
        'performance_metrics',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'weight' => 'integer',
        'priority_order' => 'integer',
        'is_fallback' => 'boolean',
        'cost_per_message_gbp' => 'decimal:6',
        'performance_metrics' => 'array',
    ];

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
        return $this->belongsTo(Supplier::class);
    }
}
