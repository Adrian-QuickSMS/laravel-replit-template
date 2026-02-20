<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;

class AccountBalance extends Model
{
    protected $table = 'account_balances';
    protected $primaryKey = 'account_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'account_id', 'currency', 'balance', 'reserved', 'credit_limit',
        'effective_available', 'total_outstanding', 'last_reconciled_at',
    ];

    protected $casts = [
        'balance' => 'decimal:4',
        'reserved' => 'decimal:4',
        'credit_limit' => 'decimal:4',
        'effective_available' => 'decimal:4',
        'total_outstanding' => 'decimal:4',
        'last_reconciled_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Lock this row for atomic balance operations.
     */
    public static function lockForAccount(string $accountId): self
    {
        return static::where('account_id', $accountId)->lockForUpdate()->firstOrFail();
    }

    public function hasSufficientBalance(string $amount): bool
    {
        return bccomp($this->effective_available, $amount, 4) >= 0;
    }

    public function recalculateEffectiveAvailable(): void
    {
        // For prepay: effective = balance - reserved
        // For postpay: effective = credit_limit - total_outstanding + balance - reserved
        $account = $this->account;

        if ($account->billing_type === 'postpay') {
            $this->effective_available = bcadd(
                bcsub(
                    bcsub($this->credit_limit, $this->total_outstanding, 4),
                    $this->reserved,
                    4
                ),
                $this->balance,
                4
            );
        } else {
            $this->effective_available = bcsub($this->balance, $this->reserved, 4);
        }
    }
}
