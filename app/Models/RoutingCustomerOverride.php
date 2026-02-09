<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoutingCustomerOverride extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'sub_account_id',
        'routing_rule_id',
        'country_iso',
        'mcc',
        'mnc',
        'product_type',
        'forced_gateway_id',
        'forced_supplier_id',
        'blocked_gateway_id',
        'override_type',
        'status',
        'reason',
        'valid_from',
        'valid_to',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'approved_at' => 'datetime',
    ];

    public function routingRule()
    {
        return $this->belongsTo(RoutingRule::class);
    }

    public function forcedGateway()
    {
        return $this->belongsTo(Gateway::class, 'forced_gateway_id');
    }

    public function forcedSupplier()
    {
        return $this->belongsTo(Supplier::class, 'forced_supplier_id');
    }

    public function blockedGateway()
    {
        return $this->belongsTo(Gateway::class, 'blocked_gateway_id');
    }
}
