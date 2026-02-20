<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Payment extends Model
{
    use HasUuids;

    protected $table = 'payments';

    protected $fillable = [
        'account_id', 'invoice_id', 'payment_method', 'status',
        'stripe_payment_intent_id', 'stripe_checkout_session_id',
        'xero_payment_id', 'currency', 'amount', 'paid_at', 'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function scopeSucceeded($query)
    {
        return $query->where('status', 'succeeded');
    }
}
