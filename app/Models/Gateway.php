<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gateway extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'gateway_code',
        'name',
        'currency',
        'billing_method',
        'fx_source',
        'active',
        'last_rate_update',
        'notes',
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_rate_update' => 'datetime',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function rateCards()
    {
        return $this->hasMany(RateCard::class);
    }
}
