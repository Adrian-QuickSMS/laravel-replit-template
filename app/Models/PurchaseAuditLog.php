<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PurchaseAuditLog extends Model
{
    protected $table = 'purchase_audit_logs';

    protected $fillable = [
        'audit_id',
        'user_id',
        'user_email',
        'user_name',
        'sub_account_id',
        'sub_account_name',
        'purchase_type',
        'items_purchased',
        'pricing_details',
        'total_setup_fee',
        'total_monthly_fee',
        'balance_before',
        'balance_after',
        'currency',
        'status',
        'transaction_reference',
        'hubspot_order_id',
        'stripe_payment_id',
        'failure_reason',
        'ip_address',
        'user_agent',
        'purchased_at',
    ];

    protected $casts = [
        'items_purchased' => 'array',
        'pricing_details' => 'array',
        'total_setup_fee' => 'decimal:2',
        'total_monthly_fee' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->audit_id)) {
                $model->audit_id = (string) Str::uuid();
            }
            if (empty($model->purchased_at)) {
                $model->purchased_at = now();
            }
        });

        static::updating(function ($model) {
            $immutableFields = [
                'audit_id',
                'user_id',
                'user_email',
                'user_name',
                'sub_account_id',
                'sub_account_name',
                'purchase_type',
                'items_purchased',
                'pricing_details',
                'total_setup_fee',
                'total_monthly_fee',
                'balance_before',
                'balance_after',
                'currency',
                'purchased_at',
                'ip_address',
                'user_agent',
            ];

            foreach ($immutableFields as $field) {
                if ($model->isDirty($field) && $model->getOriginal($field) !== null) {
                    throw new \Exception("Cannot modify immutable field: {$field}");
                }
            }
        });
    }

    public static function logVmnPurchase(array $data): self
    {
        return self::create(array_merge($data, [
            'purchase_type' => 'vmn',
        ]));
    }

    public static function logKeywordPurchase(array $data): self
    {
        return self::create(array_merge($data, [
            'purchase_type' => 'keyword',
        ]));
    }

    public function markCompleted(?string $transactionReference = null): self
    {
        $this->status = 'completed';
        $this->transaction_reference = $transactionReference;
        $this->save();
        return $this;
    }

    public function markFailed(string $reason): self
    {
        $this->status = 'failed';
        $this->failure_reason = $reason;
        $this->save();
        return $this;
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSubAccount($query, string $subAccountId)
    {
        return $query->where('sub_account_id', $subAccountId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeVmn($query)
    {
        return $query->where('purchase_type', 'vmn');
    }

    public function scopeKeyword($query)
    {
        return $query->where('purchase_type', 'keyword');
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('purchased_at', [$startDate, $endDate]);
    }
}
