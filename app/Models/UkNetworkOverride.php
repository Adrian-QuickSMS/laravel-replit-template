<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UkNetworkOverride extends Model
{
    protected $table = 'uk_network_overrides';

    protected $fillable = [
        'mcc_mnc_id',
        'account_id',
        'override_status',
        'reason',
        'created_by',
        'updated_by',
    ];

    public function mccMnc()
    {
        return $this->belongsTo(MccMnc::class, 'mcc_mnc_id');
    }

    public function control()
    {
        return $this->belongsTo(UkNetworkControl::class, 'mcc_mnc_id', 'mcc_mnc_id');
    }
}
