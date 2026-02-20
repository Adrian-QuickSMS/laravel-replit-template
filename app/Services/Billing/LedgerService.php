<?php

namespace App\Services\Billing;

use App\Models\Billing\LedgerEntry;
use App\Models\Billing\LedgerLine;
use App\Models\Billing\LedgerAccount;
use App\Models\Billing\AccountBalance;
use App\Models\Billing\FinancialAuditLog;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LedgerService
{
    /**
     * Create a balanced double-entry ledger entry.
     *
     * @param array $lines Array of ['account_code' => string, 'debit' => string, 'credit' => string]
     */
    public function createEntry(
        string $entryType,
        string $accountId,
        string $amount,
        string $description,
        string $idempotencyKey,
        array $lines,
        string $currency = 'GBP',
        ?string $subAccountId = null,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?array $metadata = null,
        ?string $createdBy = null
    ): LedgerEntry {
        // Validate balance: sum of debits must equal sum of credits
        $totalDebit = '0';
        $totalCredit = '0';
        foreach ($lines as $line) {
            $totalDebit = bcadd($totalDebit, $line['debit'] ?? '0', 4);
            $totalCredit = bcadd($totalCredit, $line['credit'] ?? '0', 4);
        }

        if (bccomp($totalDebit, $totalCredit, 4) !== 0) {
            throw new InvalidArgumentException(
                "Ledger entry is unbalanced: debit={$totalDebit}, credit={$totalCredit}"
            );
        }

        if (bccomp($totalDebit, '0', 4) === 0) {
            throw new InvalidArgumentException('Ledger entry has zero value.');
        }

        $entry = LedgerEntry::create([
            'entry_type' => $entryType,
            'account_id' => $accountId,
            'sub_account_id' => $subAccountId,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'currency' => $currency,
            'amount' => $amount,
            'description' => $description,
            'metadata' => $metadata,
            'idempotency_key' => $idempotencyKey,
            'created_by' => $createdBy,
        ]);

        foreach ($lines as $line) {
            LedgerLine::create([
                'ledger_entry_id' => $entry->id,
                'ledger_account_code' => $line['account_code'],
                'debit' => $line['debit'] ?? '0',
                'credit' => $line['credit'] ?? '0',
            ]);
        }

        return $entry;
    }

    /**
     * Prepay top-up: DR CASH, CR DEFERRED_REV
     */
    public function recordTopUp(
        string $accountId,
        string $amount,
        string $currency,
        string $idempotencyKey,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?array $metadata = null
    ): LedgerEntry {
        return $this->createEntry(
            entryType: 'top_up',
            accountId: $accountId,
            amount: $amount,
            description: "Account top-up: {$currency} {$amount}",
            idempotencyKey: $idempotencyKey,
            lines: [
                ['account_code' => LedgerAccount::CASH, 'debit' => $amount, 'credit' => '0'],
                ['account_code' => LedgerAccount::DEFERRED_REV, 'debit' => '0', 'credit' => $amount],
            ],
            currency: $currency,
            referenceType: $referenceType,
            referenceId: $referenceId,
            metadata: $metadata,
        );
    }

    /**
     * Prepay message charge: DR DEFERRED_REV, CR REVENUE
     */
    public function recordPrepayMessageCharge(
        string $accountId,
        string $amount,
        string $currency,
        string $productType,
        string $idempotencyKey,
        ?string $subAccountId = null,
        ?string $messageLogId = null,
        ?array $metadata = null
    ): LedgerEntry {
        $revenueAccount = $this->revenueAccountForProduct($productType);

        return $this->createEntry(
            entryType: 'message_charge_prepay',
            accountId: $accountId,
            amount: $amount,
            description: "Message charge ({$productType}): {$currency} {$amount}",
            idempotencyKey: $idempotencyKey,
            lines: [
                ['account_code' => LedgerAccount::DEFERRED_REV, 'debit' => $amount, 'credit' => '0'],
                ['account_code' => $revenueAccount, 'debit' => '0', 'credit' => $amount],
            ],
            currency: $currency,
            subAccountId: $subAccountId,
            referenceType: 'message_log',
            referenceId: $messageLogId,
            metadata: $metadata,
        );
    }

    /**
     * Postpay message charge: DR AR, CR REVENUE
     */
    public function recordPostpayMessageCharge(
        string $accountId,
        string $amount,
        string $currency,
        string $productType,
        string $idempotencyKey,
        ?string $subAccountId = null,
        ?string $messageLogId = null,
        ?array $metadata = null
    ): LedgerEntry {
        $revenueAccount = $this->revenueAccountForProduct($productType);

        return $this->createEntry(
            entryType: 'message_charge_postpay',
            accountId: $accountId,
            amount: $amount,
            description: "Message charge ({$productType}): {$currency} {$amount}",
            idempotencyKey: $idempotencyKey,
            lines: [
                ['account_code' => LedgerAccount::AR, 'debit' => $amount, 'credit' => '0'],
                ['account_code' => $revenueAccount, 'debit' => '0', 'credit' => $amount],
            ],
            currency: $currency,
            subAccountId: $subAccountId,
            referenceType: 'message_log',
            referenceId: $messageLogId,
            metadata: $metadata,
        );
    }

    /**
     * Supplier cost: DR COGS, CR SUPPLIER_PAY
     */
    public function recordSupplierCost(
        string $accountId,
        string $amount,
        string $currency,
        string $idempotencyKey,
        ?string $messageLogId = null
    ): LedgerEntry {
        return $this->createEntry(
            entryType: 'supplier_cost',
            accountId: $accountId,
            amount: $amount,
            description: "Supplier cost: {$currency} {$amount}",
            idempotencyKey: $idempotencyKey,
            lines: [
                ['account_code' => LedgerAccount::COGS, 'debit' => $amount, 'credit' => '0'],
                ['account_code' => LedgerAccount::SUPPLIER_PAY, 'debit' => '0', 'credit' => $amount],
            ],
            currency: $currency,
            referenceType: 'message_log',
            referenceId: $messageLogId,
        );
    }

    /**
     * Invoice payment (postpay): DR CASH, CR AR
     */
    public function recordInvoicePayment(
        string $accountId,
        string $amount,
        string $currency,
        string $idempotencyKey,
        ?string $invoiceId = null,
        ?array $metadata = null
    ): LedgerEntry {
        return $this->createEntry(
            entryType: 'invoice_payment',
            accountId: $accountId,
            amount: $amount,
            description: "Invoice payment: {$currency} {$amount}",
            idempotencyKey: $idempotencyKey,
            lines: [
                ['account_code' => LedgerAccount::CASH, 'debit' => $amount, 'credit' => '0'],
                ['account_code' => LedgerAccount::AR, 'debit' => '0', 'credit' => $amount],
            ],
            currency: $currency,
            referenceType: 'invoice',
            referenceId: $invoiceId,
            metadata: $metadata,
        );
    }

    /**
     * Credit note: DR REFUND, CR DEFERRED_REV (prepay) or AR (postpay)
     */
    public function recordCreditNote(
        string $accountId,
        string $amount,
        string $currency,
        bool $isPrepay,
        string $idempotencyKey,
        ?string $creditNoteId = null,
        ?string $createdBy = null
    ): LedgerEntry {
        $creditAccount = $isPrepay ? LedgerAccount::DEFERRED_REV : LedgerAccount::AR;

        return $this->createEntry(
            entryType: 'credit_note',
            accountId: $accountId,
            amount: $amount,
            description: "Credit note: {$currency} {$amount}",
            idempotencyKey: $idempotencyKey,
            lines: [
                ['account_code' => LedgerAccount::REFUND, 'debit' => $amount, 'credit' => '0'],
                ['account_code' => $creditAccount, 'debit' => '0', 'credit' => $amount],
            ],
            currency: $currency,
            referenceType: 'credit_note',
            referenceId: $creditNoteId,
            createdBy: $createdBy,
        );
    }

    /**
     * RCS→SMS fallback adjustment: DR REVENUE_RCS, CR DEFERRED_REV/AR
     */
    public function recordRcsFallbackAdjustment(
        string $accountId,
        string $amount,
        string $currency,
        bool $isPrepay,
        string $idempotencyKey,
        ?string $messageLogId = null
    ): LedgerEntry {
        $creditAccount = $isPrepay ? LedgerAccount::DEFERRED_REV : LedgerAccount::AR;

        return $this->createEntry(
            entryType: 'rcs_fallback_adjustment',
            accountId: $accountId,
            amount: $amount,
            description: "RCS→SMS fallback adjustment: {$currency} {$amount}",
            idempotencyKey: $idempotencyKey,
            lines: [
                ['account_code' => LedgerAccount::REVENUE_RCS, 'debit' => $amount, 'credit' => '0'],
                ['account_code' => $creditAccount, 'debit' => '0', 'credit' => $amount],
            ],
            currency: $currency,
            referenceType: 'message_log',
            referenceId: $messageLogId,
        );
    }

    /**
     * Manual adjustment (admin): configurable debit/credit
     */
    public function recordManualAdjustment(
        string $accountId,
        string $amount,
        string $currency,
        string $direction,
        string $reason,
        string $idempotencyKey,
        string $createdBy
    ): LedgerEntry {
        if ($direction === 'credit') {
            // Add money to customer: DR CASH, CR DEFERRED_REV
            $lines = [
                ['account_code' => LedgerAccount::CASH, 'debit' => $amount, 'credit' => '0'],
                ['account_code' => LedgerAccount::DEFERRED_REV, 'debit' => '0', 'credit' => $amount],
            ];
        } else {
            // Remove money from customer: DR DEFERRED_REV, CR CASH
            $lines = [
                ['account_code' => LedgerAccount::DEFERRED_REV, 'debit' => $amount, 'credit' => '0'],
                ['account_code' => LedgerAccount::CASH, 'debit' => '0', 'credit' => $amount],
            ];
        }

        return $this->createEntry(
            entryType: 'manual_adjustment',
            accountId: $accountId,
            amount: $amount,
            description: "Manual adjustment ({$direction}): {$reason}",
            idempotencyKey: $idempotencyKey,
            lines: $lines,
            currency: $currency,
            createdBy: $createdBy,
            metadata: ['direction' => $direction, 'reason' => $reason],
        );
    }

    /**
     * Calculate balance from ledger (source of truth for reconciliation).
     */
    public function calculatePrepayBalanceFromLedger(string $accountId): string
    {
        $result = DB::table('ledger_lines')
            ->join('ledger_entries', 'ledger_entries.id', '=', 'ledger_lines.ledger_entry_id')
            ->where('ledger_entries.account_id', $accountId)
            ->where('ledger_lines.ledger_account_code', LedgerAccount::DEFERRED_REV)
            ->selectRaw('COALESCE(SUM(credit), 0) - COALESCE(SUM(debit), 0) as balance')
            ->first();

        return $result->balance ?? '0.0000';
    }

    /**
     * Calculate AR outstanding from ledger.
     */
    public function calculateArFromLedger(string $accountId): string
    {
        $result = DB::table('ledger_lines')
            ->join('ledger_entries', 'ledger_entries.id', '=', 'ledger_lines.ledger_entry_id')
            ->where('ledger_entries.account_id', $accountId)
            ->where('ledger_lines.ledger_account_code', LedgerAccount::AR)
            ->selectRaw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as outstanding')
            ->first();

        return $result->outstanding ?? '0.0000';
    }

    /**
     * Get paginated transaction history for a customer account.
     */
    public function getTransactionHistory(string $accountId, int $perPage = 25)
    {
        return LedgerEntry::where('account_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    private function revenueAccountForProduct(string $productType): string
    {
        return match ($productType) {
            'sms' => LedgerAccount::REVENUE_SMS,
            'rcs_basic', 'rcs_single' => LedgerAccount::REVENUE_RCS,
            'ai_query' => LedgerAccount::REVENUE_AI,
            'virtual_number_monthly', 'shortcode_monthly', 'support', 'inbound_sms' => LedgerAccount::REVENUE_RECURRING,
            default => LedgerAccount::REVENUE_SMS,
        };
    }
}
