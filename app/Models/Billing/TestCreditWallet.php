<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestCreditWallet extends Model
{
    use HasUuids;

    protected $table = 'test_credit_wallets';

    protected $fillable = [
        'account_id', 'credits_total', 'credits_used', 'credits_remaining',
        'awarded_by', 'awarded_reason', 'expires_at', 'expired',
    ];

    protected $casts = [
        'credits_total' => 'integer',
        'credits_used' => 'integer',
        'credits_remaining' => 'integer',
        'expires_at' => 'datetime',
        'expired' => 'boolean',
    ];

    // Credits consumed per product/destination
    const COST_UK_SMS = 1;
    const COST_UK_RCS = 2;
    const COST_INTL_SMS = 10;
    const COST_INTL_RCS = 20;

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions()
    {
        return $this->hasMany(TestCreditTransaction::class, 'wallet_id');
    }

    public function isValid(): bool
    {
        return !$this->expired && $this->credits_remaining > 0;
    }

    public function isExpired(): bool
    {
        if ($this->expired) return true;
        if ($this->expires_at && $this->expires_at->isPast()) return true;
        return false;
    }

    public static function creditCostFor(string $productType, string $destinationType): int
    {
        $isInternational = $destinationType === 'international';
        $isRcs = in_array($productType, ['rcs_basic', 'rcs_single']);

        if ($isInternational && $isRcs) return self::COST_INTL_RCS;
        if ($isInternational) return self::COST_INTL_SMS;
        if ($isRcs) return self::COST_UK_RCS;
        return self::COST_UK_SMS;
    }

    public function deductCredits(int $amount): bool
    {
        if ($this->credits_remaining < $amount) return false;

        $this->credits_used += $amount;
        $this->credits_remaining -= $amount;
        $this->save();
        return true;
    }

    public function expire(): void
    {
        $this->expired = true;
        $this->credits_remaining = 0;
        $this->save();
    }
}
