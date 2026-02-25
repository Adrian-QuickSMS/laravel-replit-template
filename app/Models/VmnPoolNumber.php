<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * VMN Pool — admin-seeded available virtual mobile numbers.
 *
 * DATA CLASSIFICATION: Internal - Platform Inventory
 * SIDE: RED (admin-only management)
 * TENANT ISOLATION: Not tenant-scoped (global pool)
 */
class VmnPoolNumber extends Model
{
    protected $table = 'vmn_pool';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'number',
        'country_iso',
        'number_type',
        'capabilities',
        'provider',
        'provider_reference',
        'monthly_cost_override',
        'setup_cost_override',
        'is_available',
        'reserved_by_account_id',
        'reserved_until',
        'added_by',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'monthly_cost_override' => 'decimal:4',
        'setup_cost_override' => 'decimal:4',
        'reserved_until' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function purchasedNumber()
    {
        return $this->hasOne(PurchasedNumber::class, 'vmn_pool_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
            ->where(function ($q) {
                $q->whereNull('reserved_until')
                    ->orWhere('reserved_until', '<', now());
            });
    }

    public function scopeForCountry($query, string $countryIso)
    {
        return $query->where('country_iso', $countryIso);
    }

    // =====================================================
    // RESERVATION (temporary lock during purchase flow)
    // =====================================================

    public function reserve(string $accountId, int $minutes = 10): bool
    {
        if (!$this->isAvailableForPurchase()) {
            return false;
        }

        $this->update([
            'reserved_by_account_id' => $accountId,
            'reserved_until' => now()->addMinutes($minutes),
        ]);

        return true;
    }

    public function releaseReservation(): void
    {
        $this->update([
            'reserved_by_account_id' => null,
            'reserved_until' => null,
        ]);
    }

    public function markSold(): void
    {
        $this->update([
            'is_available' => false,
            'reserved_by_account_id' => null,
            'reserved_until' => null,
        ]);
    }

    public function markAvailable(): void
    {
        $this->update([
            'is_available' => true,
            'reserved_by_account_id' => null,
            'reserved_until' => null,
        ]);
    }

    public function isAvailableForPurchase(): bool
    {
        if (!$this->is_available) {
            return false;
        }

        // Check if reservation has expired
        if ($this->reserved_until && $this->reserved_until->isFuture()) {
            return false;
        }

        return true;
    }
}
