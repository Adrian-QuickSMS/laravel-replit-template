<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BalanceAlertConfig extends Model
{
    use HasUuids;

    protected $table = 'balance_alert_configs';

    protected $fillable = [
        'account_id', 'alert_type', 'threshold_percentage',
        'notify_customer', 'notify_admin',
        'last_triggered_at', 'cooldown_hours',
    ];

    protected $casts = [
        'threshold_percentage' => 'integer',
        'notify_customer' => 'boolean',
        'notify_admin' => 'boolean',
        'last_triggered_at' => 'datetime',
        'cooldown_hours' => 'integer',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function isOnCooldown(): bool
    {
        if (!$this->last_triggered_at) return false;
        return $this->last_triggered_at->diffInHours(now()) < $this->cooldown_hours;
    }
}
