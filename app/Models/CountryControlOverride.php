<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryControlOverride extends Model
{
    protected $fillable = [
        'country_control_id',
        'account_id',
        'override_status',
        'reason',
        'created_by',
    ];

    public function countryControl()
    {
        return $this->belongsTo(CountryControl::class);
    }
}
