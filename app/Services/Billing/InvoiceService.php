<?php

namespace App\Services\Billing;

use App\Models\Account;
use App\Models\Billing\Invoice;
use App\Models\Billing\InvoiceLineItem;
use App\Models\Billing\RecurringCharge;
use App\Models\Billing\CreditNote;
use App\Models\Billing\AccountBalance;
use App\Models\Billing\FinancialAuditLog;
use App\Models\MessageLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    public function __construct(
        private LedgerService $ledger,
    ) {}

    /**
     * Generate monthly usage invoice for a postpay account.
     */
    public function generateMonthlyInvoice(Account $account, Carbon $periodStart, Carbon $periodEnd): Invoice
    {
        return DB::transaction(function () use ($account, $periodStart, $periodEnd) {
            // Aggregate billable messages for the period
            $usageLines = DB::table('message_logs')
                ->where('account_id', $account->id)
                ->where('billable_flag', true)
                ->whereBetween('sent_time', [$periodStart, $periodEnd])
                ->whereNull('invoice_id')
                ->selectRaw("
                    country,
                    type as product_type,
                    COUNT(*) as message_count,
                    SUM(fragments) as total_segments,
                    SUM(cost) as total_cost
                ")
                ->groupBy('country', 'type')
                ->get();

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'account_id' => $account->id,
                'invoice_type' => 'usage',
                'status' => 'draft',
                'currency' => $account->currency,
                'billing_period_start' => $periodStart,
                'billing_period_end' => $periodEnd,
                'issued_date' => now()->toDateString(),
                'due_date' => now()->addDays($account->payment_terms_days)->toDateString(),
                'payment_terms_days' => $account->payment_terms_days,
            ]);

            $subtotal = '0';

            // Usage line items (per country, per product)
            foreach ($usageLines as $line) {
                $lineCost = $line->total_cost ?? '0';
                $taxRate = $this->getVatRate($account);
                $taxAmount = bcmul($lineCost, bcdiv($taxRate, '100', 6), 4);
                $lineTotal = bcadd($lineCost, $taxAmount, 4);

                $unitPrice = $line->total_segments > 0
                    ? bcdiv($lineCost, (string)$line->total_segments, 6)
                    : '0';

                InvoiceLineItem::create([
                    'invoice_id' => $invoice->id,
                    'product_type' => $line->product_type,
                    'country_iso' => $line->country,
                    'description' => $this->formatLineDescription($line),
                    'quantity' => $line->total_segments,
                    'unit_price' => $unitPrice,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'line_total' => $lineTotal,
                ]);

                $subtotal = bcadd($subtotal, $lineCost, 4);
            }

            // Add recurring charges
            $subtotal = $this->addRecurringChargeLines($invoice, $account, $periodStart, $periodEnd, $subtotal);

            // Calculate totals
            $taxRate = $this->getVatRate($account);
            $totalTax = bcmul($subtotal, bcdiv($taxRate, '100', 6), 4);
            $total = bcadd($subtotal, $totalTax, 4);

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'total' => $total,
                'amount_due' => $total,
            ]);

            // Mark messages as invoiced
            DB::table('message_logs')
                ->where('account_id', $account->id)
                ->where('billable_flag', true)
                ->whereBetween('sent_time', [$periodStart, $periodEnd])
                ->whereNull('invoice_id')
                ->update(['invoice_id' => $invoice->id]);

            FinancialAuditLog::record(
                'invoice_generated', 'invoice', $invoice->id,
                null, ['total' => $total, 'line_count' => $usageLines->count()],
                null, 'system'
            );

            return $invoice;
        });
    }

    /**
     * Create a prepay top-up invoice (single line, marked paid immediately).
     */
    public function createTopUpInvoice(
        Account $account,
        string $amount,
        string $currency,
        ?string $stripeSessionId = null
    ): Invoice {
        return DB::transaction(function () use ($account, $amount, $currency, $stripeSessionId) {
            $taxRate = $this->getVatRate($account);
            $taxAmount = bcmul($amount, bcdiv($taxRate, '100', 6), 4);
            $total = bcadd($amount, $taxAmount, 4);

            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'account_id' => $account->id,
                'invoice_type' => 'top_up',
                'status' => 'paid',
                'currency' => $currency,
                'subtotal' => $amount,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'amount_paid' => $total,
                'amount_due' => '0',
                'issued_date' => now()->toDateString(),
                'due_date' => now()->toDateString(),
                'paid_date' => now()->toDateString(),
                'payment_terms_days' => 0,
            ]);

            InvoiceLineItem::create([
                'invoice_id' => $invoice->id,
                'product_type' => 'sms', // Generic — top-ups aren't product-specific
                'description' => "Account Top-Up — {$currency} {$amount}",
                'quantity' => 1,
                'unit_price' => $amount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_total' => $total,
                'metadata' => $stripeSessionId ? ['stripe_session' => $stripeSessionId] : null,
            ]);

            return $invoice;
        });
    }

    /**
     * Issue a credit note against an account.
     */
    public function issueCreditNote(
        Account $account,
        string $amount,
        string $reason,
        string $issuedBy,
        ?string $originalInvoiceId = null
    ): CreditNote {
        return DB::transaction(function () use ($account, $amount, $reason, $issuedBy, $originalInvoiceId) {
            $taxRate = $this->getVatRate($account);
            $taxAmount = bcmul($amount, bcdiv($taxRate, '100', 6), 4);
            $total = bcadd($amount, $taxAmount, 4);

            $creditNote = CreditNote::create([
                'credit_note_number' => CreditNote::generateCreditNoteNumber(),
                'account_id' => $account->id,
                'original_invoice_id' => $originalInvoiceId,
                'reason' => $reason,
                'currency' => $account->currency,
                'status' => 'draft',
                'subtotal' => $amount,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'issued_date' => now()->toDateString(),
                'issued_by' => $issuedBy,
            ]);

            // Create ledger entry
            $isPrepay = $account->billing_type === 'prepay';
            $this->ledger->recordCreditNote(
                $account->id, $amount, $account->currency,
                $isPrepay, "cn-{$creditNote->id}", $creditNote->id, $issuedBy
            );

            // Update balance
            $balance = AccountBalance::lockForAccount($account->id);
            if ($isPrepay) {
                $balance->balance = bcadd($balance->balance, $amount, 4);
            } else {
                $balance->total_outstanding = bcsub($balance->total_outstanding, $amount, 4);
            }
            $balance->recalculateEffectiveAvailable();
            $balance->save();

            FinancialAuditLog::record(
                'credit_note_issued', 'credit_note', $creditNote->id,
                null, ['amount' => $amount, 'reason' => $reason],
                $issuedBy, 'admin'
            );

            return $creditNote;
        });
    }

    /**
     * Get invoices for an account (paginated).
     */
    public function getInvoicesForAccount(string $accountId, int $perPage = 25)
    {
        return Invoice::where('account_id', $accountId)
            ->with('lineItems')
            ->orderBy('issued_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get a single invoice with line items.
     */
    public function getInvoiceDetail(string $invoiceId): Invoice
    {
        return Invoice::with('lineItems', 'payments', 'creditNotes')->findOrFail($invoiceId);
    }

    /**
     * Void an invoice (admin action).
     */
    public function voidInvoice(string $invoiceId, string $adminId): Invoice
    {
        return DB::transaction(function () use ($invoiceId, $adminId) {
            $invoice = Invoice::findOrFail($invoiceId);

            if ($invoice->status === 'paid') {
                throw new \RuntimeException('Cannot void a paid invoice. Issue a credit note instead.');
            }

            $oldStatus = $invoice->status;
            $invoice->update(['status' => 'void']);

            FinancialAuditLog::record(
                'invoice_voided', 'invoice', $invoiceId,
                ['status' => $oldStatus], ['status' => 'void'],
                $adminId, 'admin'
            );

            return $invoice;
        });
    }

    private function addRecurringChargeLines(Invoice $invoice, Account $account, Carbon $start, Carbon $end, string $subtotal): string
    {
        $charges = RecurringCharge::where('account_id', $account->id)
            ->active()
            ->get();

        foreach ($charges as $charge) {
            $taxRate = $this->getVatRate($account);
            $taxAmount = bcmul($charge->amount, bcdiv($taxRate, '100', 6), 4);
            $lineTotal = bcadd($charge->amount, $taxAmount, 4);

            InvoiceLineItem::create([
                'invoice_id' => $invoice->id,
                'product_type' => $this->mapChargeTypeToProduct($charge->charge_type),
                'description' => $charge->description,
                'quantity' => 1,
                'unit_price' => $charge->amount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'line_total' => $lineTotal,
            ]);

            $subtotal = bcadd($subtotal, $charge->amount, 4);
        }

        return $subtotal;
    }

    private function formatLineDescription(object $line): string
    {
        $type = strtoupper(str_replace('_', ' ', $line->product_type));
        $country = $line->country ?? 'Unknown';
        return "{$country} {$type}: {$line->total_segments} segments";
    }

    private function getVatRate(Account $account): string
    {
        // UK VAT standard rate. Can be extended per-country.
        if ($account->country === 'GB') {
            return '20.00';
        }
        // EU reverse charge, etc.
        return '0.00';
    }

    private function mapChargeTypeToProduct(string $chargeType): string
    {
        return match ($chargeType) {
            'virtual_number' => 'virtual_number_monthly',
            'shortcode' => 'shortcode_monthly',
            'platform_fee', 'support_fee' => 'support',
            default => 'support',
        };
    }
}
