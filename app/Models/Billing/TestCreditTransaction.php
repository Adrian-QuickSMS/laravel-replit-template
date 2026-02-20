<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TestCreditTransaction extends Model
{
    use HasUuids;

    protected $table = 'test_credit_transactions';
    public $timestamps = false;

    protected $fillable = [
        'wallet_id', 'message_log_id', 'credits_consumed',
        'destination_type', 'product_type',
    ];

    protected $casts = [
        'credits_consumed' => 'integer',
        'created_at' => 'datetime',
    ];

    public function wallet()
    {
        return $this->belongsTo(TestCreditWallet::class, 'wallet_id');
    }
}
