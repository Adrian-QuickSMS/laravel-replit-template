<?php

namespace App\Models\Billing;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasUuids;

    protected $table = 'invoices';

    protected $fillable = [
        'invoice_number', 'account_id', 'invoice_type', 'status',
        'xero_invoice_id', 'xero_invoice_number', 'currency',
        'subtotal', 'tax_amount', 'total', 'amount_paid', 'amount_due',
        'issued_date', 'due_date', 'paid_date',
        'billing_period_start', 'billing_period_end',
        'payment_terms_days', 'notes', 'xero_pdf_url',
    ];

    protected $casts = [
        'subtotal' => 'decimal:4',
        'tax_amount' => 'decimal:4',
        'total' => 'decimal:4',
        'amount_paid' => 'decimal:4',
        'amount_due' => 'decimal:4',
        'issued_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function lineItems()
    {
        return $this->hasMany(InvoiceLineItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class, 'original_invoice_id');
    }

    // --- Scopes ---

    public function scopeForAccount($query, string $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    public function scopeOverdue($query)
    {
        return $query->whereIn('status', ['sent', 'overdue'])
            ->where('due_date', '<', now()->toDateString());
    }

    public function scopeUnpaid($query)
    {
        return $query->whereNotIn('status', ['paid', 'void', 'written_off']);
    }

    // --- Helpers ---

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'QS-' . now()->format('Ym') . '-';
        do {
            $number = $prefix . strtoupper(Str::random(6));
        } while (static::where('invoice_number', $number)->exists());
        return $number;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return in_array($this->status, ['sent', 'overdue']) && $this->due_date->isPast();
    }
}
