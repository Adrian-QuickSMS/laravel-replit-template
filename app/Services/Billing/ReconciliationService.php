<?php

namespace App\Services\Billing;

use App\Models\Account;
use App\Models\Billing\AccountBalance;
use App\Models\Billing\FinancialAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReconciliationService
{
    public function __construct(
        private LedgerService $ledger,
        private BalanceService $balanceService,
    ) {}

    /**
     * Process daily DLR reconciliation (RCS fallback + delivered billing refunds).
     */
    public function processDlrReconciliation(): array
    {
        $batchId = (string)\Illuminate\Support\Str::uuid();
        $processed = 0;
        $failed = 0;

        $records = DB::table('dlr_reconciliation_queue')
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->limit(5000) // Process in batches
            ->get();

        foreach ($records as $record) {
            try {
                DB::transaction(function () use ($record, $batchId) {
                    $account = Account::find($record->account_id);
                    if (!$account) return;

                    $isPrepay = $account->billing_type === 'prepay';

                    if ($record->adjustment_type === 'rcs_to_sms_fallback') {
                        $this->ledger->recordRcsFallbackAdjustment(
                            $record->account_id,
                            (string)$record->adjustment_amount,
                            $account->currency,
                            $isPrepay,
                            "dlr-recon-{$record->id}",
                            $record->message_log_id
                        );
                    } elseif ($record->adjustment_type === 'delivered_billing_refund') {
                        $this->ledger->recordCreditNote(
                            $record->account_id,
                            (string)$record->adjustment_amount,
                            $account->currency,
                            $isPrepay,
                            "dlr-refund-{$record->id}",
                            null, null
                        );
                    }

                    // Update balance
                    $balance = AccountBalance::lockForAccount($record->account_id);
                    if ($isPrepay) {
                        $balance->balance = bcadd($balance->balance, (string)$record->adjustment_amount, 4);
                    } else {
                        $balance->total_outstanding = bcsub($balance->total_outstanding, (string)$record->adjustment_amount, 4);
                    }
                    $balance->recalculateEffectiveAvailable();
                    $balance->save();

                    DB::table('dlr_reconciliation_queue')
                        ->where('id', $record->id)
                        ->update([
                            'status' => 'processed',
                            'processed_at' => now(),
                            'batch_id' => $batchId,
                        ]);
                });

                $processed++;
            } catch (\Exception $e) {
                Log::error('DLR reconciliation failed', [
                    'record_id' => $record->id,
                    'error' => $e->getMessage(),
                ]);

                DB::table('dlr_reconciliation_queue')
                    ->where('id', $record->id)
                    ->update(['status' => 'failed']);

                $failed++;
            }
        }

        Log::info('DLR reconciliation complete', [
            'batch_id' => $batchId,
            'processed' => $processed,
            'failed' => $failed,
        ]);

        return ['batch_id' => $batchId, 'processed' => $processed, 'failed' => $failed];
    }

    /**
     * Reconcile all account balances against ledger (daily).
     */
    public function reconcileAllBalances(): array
    {
        $results = ['matched' => 0, 'mismatched' => 0, 'details' => []];

        Account::where('status', '!=', 'closed')
            ->select('id')
            ->chunk(100, function ($accounts) use (&$results) {
                foreach ($accounts as $account) {
                    $result = $this->balanceService->reconcileBalance($account->id);

                    if ($result['mismatch']) {
                        $results['mismatched']++;
                        $results['details'][] = $result;

                        Log::critical('Balance reconciliation mismatch', $result);
                    } else {
                        $results['matched']++;
                    }
                }
            });

        Log::info('Balance reconciliation complete', [
            'matched' => $results['matched'],
            'mismatched' => $results['mismatched'],
        ]);

        return $results;
    }

    /**
     * Process recurring monthly charges for prepay accounts.
     */
    public function processRecurringCharges(): array
    {
        $processed = 0;
        $failed = 0;

        $charges = DB::table('recurring_charges')
            ->where('active', true)
            ->where('next_charge_date', '<=', now()->toDateString())
            ->get();

        foreach ($charges as $charge) {
            try {
                $account = Account::find($charge->account_id);
                if (!$account) continue;

                if ($account->billing_type === 'prepay') {
                    DB::transaction(function () use ($charge, $account) {
                        $balance = AccountBalance::lockForAccount($account->id);

                        if (bccomp($balance->effective_available, (string)$charge->amount, 4) < 0) {
                            // Insufficient balance — suspend account
                            $account->update(['status' => 'suspended']);
                            Log::warning('Account suspended: insufficient balance for recurring charge', [
                                'account_id' => $account->id,
                                'charge' => $charge->amount,
                                'balance' => $balance->effective_available,
                            ]);
                            return;
                        }

                        $idempotencyKey = "recurring-{$charge->id}-" . now()->format('Ym');

                        $this->ledger->createEntry(
                            entryType: 'recurring_charge_prepay',
                            accountId: $account->id,
                            amount: (string)$charge->amount,
                            description: $charge->description,
                            idempotencyKey: $idempotencyKey,
                            lines: [
                                ['account_code' => 'DEFERRED_REV', 'debit' => (string)$charge->amount, 'credit' => '0'],
                                ['account_code' => 'REVENUE_RECURRING', 'debit' => '0', 'credit' => (string)$charge->amount],
                            ],
                            currency: $account->currency,
                        );

                        $balance->balance = bcsub($balance->balance, (string)$charge->amount, 4);
                        $balance->recalculateEffectiveAvailable();
                        $balance->save();

                        DB::table('recurring_charges')
                            ->where('id', $charge->id)
                            ->update(['next_charge_date' => now()->addMonth()->toDateString()]);
                    });
                } else {
                    // Postpay: just advance next_charge_date — charge goes on invoice
                    DB::table('recurring_charges')
                        ->where('id', $charge->id)
                        ->update(['next_charge_date' => now()->addMonth()->toDateString()]);
                }

                $processed++;
            } catch (\Exception $e) {
                Log::error('Recurring charge failed', [
                    'charge_id' => $charge->id,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        return ['processed' => $processed, 'failed' => $failed];
    }
}
