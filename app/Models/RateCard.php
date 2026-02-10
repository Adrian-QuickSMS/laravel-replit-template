<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class RateCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'gateway_id',
        'mcc_mnc_id',
        'mcc',
        'mnc',
        'country_name',
        'country_iso',
        'network_name',
        'product_type',
        'billing_method',
        'currency',
        'native_rate',
        'gbp_rate',
        'fx_rate',
        'fx_timestamp',
        'valid_from',
        'valid_to',
        'active',
        'version',
        'previous_version_id',
        'created_by',
        'change_reason',
    ];

    protected $casts = [
        'native_rate' => 'decimal:6',
        'gbp_rate' => 'decimal:6',
        'fx_rate' => 'decimal:6',
        'fx_timestamp' => 'datetime',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'active' => 'boolean',
        'version' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class);
    }

    public function mccMnc()
    {
        return $this->belongsTo(MccMnc::class, 'mcc_mnc_id');
    }

    public function previousVersion()
    {
        return $this->belongsTo(RateCard::class, 'previous_version_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(RateCardAuditLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeValidAt($query, $date = null)
    {
        $date = $date ?? Carbon::now();

        return $query->where('valid_from', '<=', $date)
                     ->where(function ($q) use ($date) {
                         $q->whereNull('valid_to')
                           ->orWhere('valid_to', '>=', $date);
                     });
    }

    public function scopeForNetwork($query, $mcc, $mnc, $gatewayId, $productType = 'SMS')
    {
        return $query->where('mcc', $mcc)
                     ->where('mnc', $mnc)
                     ->where('gateway_id', $gatewayId)
                     ->where('product_type', $productType)
                     ->where('active', true);
    }

    // Static methods for rate lookup
    public static function findRateForMessage($mcc, $mnc, $gatewayId, $productType = 'SMS', $sentAt = null)
    {
        $sentAt = $sentAt ?? Carbon::now();

        return self::forNetwork($mcc, $mnc, $gatewayId, $productType)
                   ->validAt($sentAt)
                   ->orderBy('valid_from', 'desc')
                   ->first();
    }

    // Create new version of rate
    public function createNewVersion($newData, $createdBy, $reason = null)
    {
        // Deactivate current version
        $this->update(['active' => false, 'valid_to' => Carbon::yesterday()]);

        // Create new version
        $newVersion = self::create(array_merge($newData, [
            'supplier_id' => $this->supplier_id,
            'gateway_id' => $this->gateway_id,
            'mcc_mnc_id' => $this->mcc_mnc_id,
            'mcc' => $this->mcc,
            'mnc' => $this->mnc,
            'country_name' => $this->country_name,
            'country_iso' => $this->country_iso,
            'network_name' => $this->network_name,
            'product_type' => $this->product_type,
            'billing_method' => $newData['billing_method'] ?? $this->billing_method ?? 'submitted',
            'currency' => $newData['currency'] ?? $this->currency ?? 'GBP',
            'version' => $this->version + 1,
            'previous_version_id' => $this->id,
            'created_by' => $createdBy,
            'change_reason' => $reason,
            'valid_from' => $newData['valid_from'] ?? Carbon::now(),
        ]));

        return $newVersion;
    }
}
