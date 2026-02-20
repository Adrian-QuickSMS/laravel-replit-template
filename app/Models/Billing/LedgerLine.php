<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use RuntimeException;

class LedgerLine extends Model
{
    use HasUuids;

    protected $table = 'ledger_lines';
    public $timestamps = false;

    protected $fillable = [
        'ledger_entry_id', 'ledger_account_code', 'debit', 'credit',
    ];

    protected $casts = [
        'debit' => 'decimal:4',
        'credit' => 'decimal:4',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException('Ledger lines are immutable and cannot be updated.');
        });
        static::deleting(function () {
            throw new RuntimeException('Ledger lines are immutable and cannot be deleted.');
        });
    }

    public function entry()
    {
        return $this->belongsTo(LedgerEntry::class, 'ledger_entry_id');
    }

    public function ledgerAccount()
    {
        return $this->belongsTo(LedgerAccount::class, 'ledger_account_code', 'code');
    }
}
