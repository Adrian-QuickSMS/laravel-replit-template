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

    public function subAccountPermissions()
    {
        return $this->hasMany(SubAccountCountryPermission::class);
    }

    /**
     * Scope: only countries with 'allowed' default status.
     */
    public function scopeAllowed($query)
    {
        return $query->where('default_status', 'allowed');
    }

    /**
     * Scope: only countries with 'blocked' default status.
     */
    public function scopeBlocked($query)
    {
        return $query->where('default_status', 'blocked');
    }

    /**
     * Scope: only countries with 'restricted' default status.
     */
    public function scopeRestricted($query)
    {
        return $query->where('default_status', 'restricted');
    }
}
