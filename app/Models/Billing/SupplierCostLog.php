<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SupplierCostLog extends Model
{
    use HasUuids;

    protected $table = 'supplier_cost_log';
    public $timestamps = false;

    protected $fillable = [
        'message_log_id', 'account_id', 'rate_card_id',
        'country_iso', 'mcc', 'mnc', 'gateway_id', 'product_type',
        'segments', 'customer_price', 'supplier_cost_native',
        'supplier_cost_gbp', 'fx_rate', 'margin_amount', 'margin_percentage',
    ];

    protected $casts = [
        'segments' => 'integer',
        'customer_price' => 'decimal:6',
        'supplier_cost_native' => 'decimal:6',
        'supplier_cost_gbp' => 'decimal:6',
        'fx_rate' => 'decimal:6',
        'margin_amount' => 'decimal:6',
        'margin_percentage' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
