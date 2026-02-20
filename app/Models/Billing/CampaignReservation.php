<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CampaignReservation extends Model
{
    use HasUuids;

    protected $table = 'campaign_reservations';

    protected $fillable = [
        'account_id', 'sub_account_id', 'campaign_id',
        'reserved_amount', 'used_amount', 'released_amount',
        'status', 'expires_at',
    ];

    protected $casts = [
        'reserved_amount' => 'decimal:4',
        'used_amount' => 'decimal:4',
        'released_amount' => 'decimal:4',
        'expires_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function remainingAmount(): string
    {
        return bcsub(bcsub($this->reserved_amount, $this->used_amount, 4), $this->released_amount, 4);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
