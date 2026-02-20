<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class CreditNote extends Model
{
    use HasUuids;

    protected $table = 'credit_notes';

    protected $fillable = [
        'credit_note_number', 'account_id', 'original_invoice_id',
        'xero_credit_note_id', 'reason', 'currency', 'status',
        'subtotal', 'tax_amount', 'total',
        'applied_to_invoice_id', 'issued_date', 'issued_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'total' => 'decimal:4',
        'issued_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function originalInvoice()
    {
        return $this->belongsTo(Invoice::class, 'original_invoice_id');
    }

    public function appliedToInvoice()
    {
        return $this->belongsTo(Invoice::class, 'applied_to_invoice_id');
    }

    public static function generateCreditNoteNumber(): string
    {
        $prefix = 'CN-' . now()->format('Ym') . '-';
        do {
            $number = $prefix . strtoupper(Str::random(6));
        } while (static::where('credit_note_number', $number)->exists());
        return $number;
    }
}
