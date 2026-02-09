<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class RoutingCustomerOverride extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'product_type',
        'scope_type',
        'scope_value',
        'forced_gateway_id',
        'secondary_gateway_id',
        'start_datetime',
        'end_datetime',
        'status',
        'reason',
        'notify_customer',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'notify_customer' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function forcedGateway()
    {
        return $this->belongsTo(Gateway::class, 'forced_gateway_id');
    }

    public function secondaryGateway()
    {
        return $this->belongsTo(Gateway::class, 'secondary_gateway_id');
    }

    public function auditLogs()
    {
        return $this->hasMany(RoutingAuditLog::class, 'entity_id')
            ->where('entity_type', 'routing_customer_override');
    }

    // Scopes
    public function scopeActive($query)
    {
        $now = Carbon::now();
        return $query->where('status', 'active')
            ->where('start_datetime', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('end_datetime')
                  ->orWhere('end_datetime', '>=', $now);
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->where('end_datetime', '<', Carbon::now())
                  ->where('status', 'active');
            });
    }

    public function scopeForCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForProduct($query, $productType)
    {
        return $query->where(function ($q) use ($productType) {
            $q->where('product_type', $productType)
              ->orWhere('product_type', 'ALL');
        });
    }

    public function scopeForDestination($query, $destinationType, $destinationCode)
    {
        return $query->where(function ($q) use ($destinationType, $destinationCode) {
            $q->where('scope_type', 'GLOBAL')
              ->orWhere(function ($sq) use ($destinationType, $destinationCode) {
                  if ($destinationType === 'UK_NETWORK') {
                      $sq->where('scope_type', 'UK_NETWORK')
                         ->where('scope_value', $destinationCode);
                  } else {
                      $sq->where('scope_type', 'COUNTRY')
                         ->where('scope_value', $destinationCode);
                  }
              });
        });
    }

    // Helper methods
    public function isActive()
    {
        $now = Carbon::now();
        return $this->status === 'active' &&
               $this->start_datetime <= $now &&
               ($this->end_datetime === null || $this->end_datetime >= $now);
    }

    public function markExpired()
    {
        $this->update(['status' => 'expired']);

        RoutingAuditLog::logAction('override_expired', [
            'entity_type' => 'routing_customer_override',
            'entity_id' => $this->id,
            'admin_user' => 'SYSTEM',
            'admin_ip' => '0.0.0.0',
            'old_value' => ['status' => 'active'],
            'new_value' => ['status' => 'expired'],
        ]);
    }

    /**
     * Find the best matching override for a customer message
     * Precedence: Most specific scope wins
     */
    public static function findOverride($customerId, $productType, $destinationType, $destinationCode)
    {
        $overrides = self::active()
            ->forCustomer($customerId)
            ->forProduct($productType)
            ->forDestination($destinationType, $destinationCode)
            ->get();

        if ($overrides->isEmpty()) {
            return null;
        }

        // Precedence: specific > product-specific > global
        $specificMatch = $overrides->where('scope_type', $destinationType === 'UK_NETWORK' ? 'UK_NETWORK' : 'COUNTRY')
            ->where('scope_value', $destinationCode)
            ->where('product_type', $productType)
            ->first();

        if ($specificMatch) {
            return $specificMatch;
        }

        $productGlobal = $overrides->where('scope_type', 'GLOBAL')
            ->where('product_type', $productType)
            ->first();

        if ($productGlobal) {
            return $productGlobal;
        }

        return $overrides->where('scope_type', 'GLOBAL')
            ->where('product_type', 'ALL')
            ->first();
    }
}
