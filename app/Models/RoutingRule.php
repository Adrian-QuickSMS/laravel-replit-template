<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoutingRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'rule_type',
        'status',
        'priority',
        'country_iso',
        'country_name',
        'mcc',
        'mnc',
        'product_type',
        'supplier_id',
        'gateway_id',
        'selection_strategy',
        'conditions',
        'time_restrictions',
        'rate_cap_gbp',
        'daily_volume_cap',
        'is_default',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'conditions' => 'array',
        'time_restrictions' => 'array',
        'rate_cap_gbp' => 'decimal:6',
        'is_default' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }

    public function gatewayWeights()
    {
        return $this->hasMany(RoutingGatewayWeight::class);
    }
}
