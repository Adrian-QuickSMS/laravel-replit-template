<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MccMnc extends Model
{
    protected $table = 'mcc_mnc_master';

    protected $fillable = [
        'mcc',
        'mnc',
        'country_name',
        'country_iso',
        'network_name',
        'country_prefix',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function rateCards()
    {
        return $this->hasMany(RateCard::class, 'mcc_mnc_id');
    }
}
