<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AutoTopUpConfig extends Model
{
    use HasUuids;

    protected $table = 'auto_topup_configs';

    protected $fillable = [
        'account_id', 'enabled', 'threshold_amount', 'topup_amount',
        'stripe_customer_id', 'stripe_payment_method_id',
        'max_topups_per_day', 'last_triggered_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'threshold_amount' => 'decimal:4',
        'topup_amount' => 'decimal:4',
        'max_topups_per_day' => 'integer',
        'last_triggered_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
