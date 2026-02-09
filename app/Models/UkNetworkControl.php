<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UkNetworkControl extends Model
{
    protected $table = 'uk_network_controls';

    protected $fillable = [
        'mcc_mnc_id',
        'default_status',
        'updated_by',
    ];

    public function mccMnc()
    {
        return $this->belongsTo(MccMnc::class, 'mcc_mnc_id');
    }

    public function overrides()
    {
        return $this->hasMany(UkNetworkOverride::class, 'mcc_mnc_id', 'mcc_mnc_id');
    }
}
