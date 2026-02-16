<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * RED SIDE: Cached WHOIS domain registration data
 *
 * Used by URL enforcement to detect freshly registered domains.
 * Caches WHOIS lookups to avoid repeated external calls.
 *
 * DATA CLASSIFICATION: Internal - External Lookup Cache
 * SIDE: RED (system-level)
 */
class DomainAgeCache extends Model
{
    protected $table = 'domain_age_cache';

    protected $fillable = [
        'domain',
        'registered_at',
        'checked_at',
        'age_hours',
        'whois_raw',
        'lookup_status',
    ];

    // L3 FIX: Prevent large WHOIS payloads from leaking in default serialization
    protected $hidden = ['whois_raw'];

    protected $casts = [
        'registered_at' => 'datetime',
        'checked_at' => 'datetime',
        'age_hours' => 'integer',
        'whois_raw' => 'array',
    ];

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeFresh($query, int $maxAgeHours = 24)
    {
        return $query->where('checked_at', '>=', now()->subHours($maxAgeHours));
    }

    public function scopeStale($query, int $maxAgeHours = 24)
    {
        return $query->where('checked_at', '<', now()->subHours($maxAgeHours));
    }

    public function scopeSuccessful($query)
    {
        return $query->where('lookup_status', 'success');
    }

    // =====================================================
    // HELPERS
    // =====================================================

    /**
     * Check if this domain is considered "young" based on the threshold.
     */
    public function isYoung(int $thresholdHours = 72): bool
    {
        if ($this->lookup_status !== 'success' || $this->age_hours === null) {
            return true; // Unknown domains treated as suspicious
        }

        return $this->age_hours < $thresholdHours;
    }

    /**
     * Check if cache entry needs refreshing.
     */
    public function needsRefresh(int $cacheTtlHours = 24): bool
    {
        return $this->checked_at < now()->subHours($cacheTtlHours);
    }

    /**
     * Look up or create a cache entry for a domain.
     */
    public static function findOrCreateForDomain(string $domain): self
    {
        $domain = strtolower(trim($domain));

        return static::firstOrCreate(
            ['domain' => $domain],
            [
                'checked_at' => now(),
                'lookup_status' => 'unknown',
            ]
        );
    }
}
