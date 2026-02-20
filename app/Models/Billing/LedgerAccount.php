<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LedgerAccount extends Model
{
    use HasUuids;

    protected $table = 'ledger_accounts';
    public $timestamps = false;

    protected $fillable = ['code', 'name', 'account_type', 'is_system'];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    // Well-known account codes
    const CASH = 'CASH';
    const AR = 'AR';
    const DEFERRED_REV = 'DEFERRED_REV';
    const REVENUE_SMS = 'REVENUE_SMS';
    const REVENUE_RCS = 'REVENUE_RCS';
    const REVENUE_AI = 'REVENUE_AI';
    const REVENUE_RECURRING = 'REVENUE_RECURRING';
    const COGS = 'COGS';
    const SUPPLIER_PAY = 'SUPPLIER_PAY';
    const REFUND = 'REFUND';

    public function lines()
    {
        return $this->hasMany(LedgerLine::class, 'ledger_account_code', 'code');
    }
}
