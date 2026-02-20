<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RecurringCharge extends Model
{
    use HasUuids;

    protected $table = 'recurring_charges';

    protected $fillable = [
        'account_id', 'charge_type', 'description', 'amount',
        'currency', 'frequency', 'next_charge_date', 'active',
        'reference_type', 'reference_id',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'next_charge_date' => 'date',
        'active' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeDue($query)
    {
        return $query->active()->where('next_charge_date', '<=', now()->toDateString());
    }
}
