<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainAgeCache extends Model
{
    protected $table = 'domain_age_cache';

    protected $fillable = [
        'domain',
        'first_seen_at',
        'age_hours',
        'lookup_status',
        'last_checked_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'age_hours' => 'integer',
    ];

    public function scopeFresh($query)
    {
        return $query->where('last_checked_at', '>', now()->subHours(24));
    }

    public function scopeStale($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('last_checked_at')
              ->orWhere('last_checked_at', '<=', now()->subHours(24));
        });
    }

    public function scopeSuccessful($query)
    {
        return $query->where('lookup_status', 'success');
    }

    public function isYoung($thresholdHours = 72): bool
    {
        return $this->age_hours !== null && $this->age_hours < $thresholdHours;
    }

    public function needsRefresh($ttlHours = 24): bool
    {
        return $this->last_checked_at === null || $this->last_checked_at->lt(now()->subHours($ttlHours));
    }

    public static function findOrCreateForDomain($domain)
    {
        return static::firstOrCreate(['domain' => $domain]);
    }
}
