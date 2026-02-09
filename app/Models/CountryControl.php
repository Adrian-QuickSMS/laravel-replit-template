<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryControl extends Model
{
    protected $fillable = [
        'country_iso',
        'country_name',
        'country_prefix',
        'default_status',
        'risk_level',
        'network_count',
    ];

    public function overrides()
    {
        return $this->hasMany(CountryControlOverride::class);
    }
}
