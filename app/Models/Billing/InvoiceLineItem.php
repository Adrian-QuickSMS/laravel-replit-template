<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class InvoiceLineItem extends Model
{
    use HasUuids;

    protected $table = 'invoice_line_items';
    public $timestamps = false;

    protected $fillable = [
        'invoice_id', 'product_type', 'country_iso', 'description',
        'quantity', 'unit_price', 'tax_rate', 'tax_amount',
        'line_total', 'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:6',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:4',
        'line_total' => 'decimal:4',
        'metadata' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
