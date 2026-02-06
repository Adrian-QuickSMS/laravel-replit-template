<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MccMnc extends Model
{
    use HasFactory;

    protected $table = 'mcc_mnc_master';

    protected $fillable = [
        'mcc',
        'mnc',
        'country_name',
        'country_iso',
        'network_name',
        'network_type',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function rateCards()
    {
        return $this->hasMany(RateCard::class, 'mcc_mnc_id');
    }

    // Accessors
    public function getMccMncAttribute()
    {
        return $this->mcc . '-' . $this->mnc;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByCountry($query, $countryIso)
    {
        return $query->where('country_iso', $countryIso);
    }

    public function scopeByMccMnc($query, $mcc, $mnc)
    {
        return $query->where('mcc', $mcc)->where('mnc', $mnc);
    }
}
