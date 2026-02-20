<?php

namespace App\Models\Billing;

use App\Models\Account;
use App\Models\SubAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use RuntimeException;

class LedgerEntry extends Model
{
    use HasUuids;

    protected $table = 'ledger_entries';
    public $timestamps = false;
    const UPDATED_AT = null;

    protected $fillable = [
        'account_id', 'sub_account_id', 'entry_type',
        'reference_type', 'reference_id', 'currency', 'amount',
        'description', 'metadata', 'idempotency_key', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    // IMMUTABILITY GUARDS
    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException('Ledger entries are immutable and cannot be updated.');
        });
        static::deleting(function () {
            throw new RuntimeException('Ledger entries are immutable and cannot be deleted.');
        });
    }

    // --- Relationships ---

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function subAccount()
    {
        return $this->belongsTo(SubAccount::class);
    }

    public function lines()
    {
        return $this->hasMany(LedgerLine::class);
    }

    // --- Scopes ---

    public function scopeForAccount($query, string $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeOfType($query, string $entryType)
    {
        return $query->where('entry_type', $entryType);
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
