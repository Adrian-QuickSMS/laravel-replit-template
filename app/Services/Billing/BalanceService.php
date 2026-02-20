<?php

namespace App\Services\Billing;

use App\Models\Account;
use App\Models\SubAccount;
use App\Models\Billing\AccountBalance;
use App\Models\Billing\CampaignReservation;
use App\Models\Billing\LedgerEntry;
use App\Exceptions\Billing\InsufficientBalanceException;
use App\Exceptions\Billing\SubAccountSpendingLimitException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BalanceService
{
    public function __construct(
        private LedgerService $ledger,
        private BalanceAlertService $alertService,
    ) {}

    /**
     * Get current balance summary for an account.
     */
    public function getBalance(string $accountId): AccountBalance
    {
        return AccountBalance::where('account_id', $accountId)->firstOrFail();
    }

    /**
     * Initialize balance row for a new account.
     */
    public function initializeBalance(string $accountId, string $currency = 'GBP', string $creditLimit = '0'): AccountBalance
    {
        return AccountBalance::create([
            'account_id' => $accountId,
            'currency' => $currency,
            'balance' => '0',
            'reserved' => '0',
            'credit_limit' => $creditLimit,
            'effective_available' => $creditLimit, // Postpay starts with credit limit available
            'total_outstanding' => '0',
        ]);
    }

    /**
     * Process a prepay top-up (called after Stripe payment confirmed).
     */
    public function processTopUp(
        string $accountId,
        string $amount,
        string $currency,
        string $idempotencyKey,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?array $metadata = null
    ): LedgerEntry {
        return DB::transaction(function () use ($accountId, $amount, $currency, $idempotencyKey, $referenceType, $referenceId, $metadata) {
            $balance = AccountBalance::lockForAccount($accountId);

            $entry = $this->ledger->recordTopUp(
                $accountId, $amount, $currency, $idempotencyKey,
                $referenceType, $referenceId, $metadata
            );

            $balance->balance = bcadd($balance->balance, $amount, 4);
            $balance->recalculateEffectiveAvailable();
            $balance->save();

            return $entry;
        });
    }

    /**
     * Deduct balance for a single message (API / Email-to-SMS path).
     * Synchronous, per-transaction.
     */
    public function deductForMessage(
        string $accountId,
        string $amount,
        string $currency,
        string $productType,
        string $messageLogId,
        ?string $subAccountId = null,
        bool $isPostpay = false
    ): LedgerEntry {
        return DB::transaction(function () use ($accountId, $amount, $currency, $productType, $messageLogId, $subAccountId, $isPostpay) {
            $balance = AccountBalance::lockForAccount($accountId);

            // Check sufficient balance
            if (!$balance->hasSufficientBalance($amount)) {
                throw new InsufficientBalanceException($accountId, $amount, $balance->effective_available);
            }

            // Check sub-account spending cap
            if ($subAccountId) {
                $this->checkSubAccountCap($subAccountId, $amount);
            }

            // Create ledger entry
            $idempotencyKey = "msg-{$messageLogId}";
            if ($isPostpay) {
                $entry = $this->ledger->recordPostpayMessageCharge(
                    $accountId, $amount, $currency, $productType,
                    $idempotencyKey, $subAccountId, $messageLogId
                );
                $balance->total_outstanding = bcadd($balance->total_outstanding, $amount, 4);
            } else {
                $entry = $this->ledger->recordPrepayMessageCharge(
                    $accountId, $amount, $currency, $productType,
                    $idempotencyKey, $subAccountId, $messageLogId
                );
                $balance->balance = bcsub($balance->balance, $amount, 4);
            }

            // Update sub-account spending
            if ($subAccountId) {
                SubAccount::where('id', $subAccountId)
                    ->increment('spending_used_current_period', $amount);
            }

            $balance->recalculateEffectiveAvailable();
            $balance->save();

            // Check balance alerts (async-safe: won't block message send)
            $this->alertService->checkAlerts($accountId, $balance);

            return $entry;
        });
    }

    /**
     * Reserve full campaign cost upfront (Portal UI path).
     */
    public function reserveForCampaign(
        string $accountId,
        string $campaignId,
        string $estimatedTotal,
        ?string $subAccountId = null
    ): CampaignReservation {
        return DB::transaction(function () use ($accountId, $campaignId, $estimatedTotal, $subAccountId) {
            $balance = AccountBalance::lockForAccount($accountId);

            if (!$balance->hasSufficientBalance($estimatedTotal)) {
                throw new InsufficientBalanceException($accountId, $estimatedTotal, $balance->effective_available);
            }

            $reservation = CampaignReservation::create([
                'account_id' => $accountId,
                'sub_account_id' => $subAccountId,
                'campaign_id' => $campaignId,
                'reserved_amount' => $estimatedTotal,
                'used_amount' => '0',
                'released_amount' => '0',
                'status' => 'active',
                'expires_at' => now()->addHours(24),
            ]);

            $balance->reserved = bcadd($balance->reserved, $estimatedTotal, 4);
            $balance->recalculateEffectiveAvailable();
            $balance->save();

            return $reservation;
        });
    }

    /**
     * Consume from an existing campaign reservation (per-message during campaign send).
     */
    public function consumeFromReservation(
        string $reservationId,
        string $amount,
        string $currency,
        string $productType,
        string $messageLogId,
        bool $isPostpay = false
    ): LedgerEntry {
        return DB::transaction(function () use ($reservationId, $amount, $currency, $productType, $messageLogId, $isPostpay) {
            $reservation = CampaignReservation::where('id', $reservationId)
                ->lockForUpdate()
                ->firstOrFail();

            $reservation->used_amount = bcadd($reservation->used_amount, $amount, 4);
            $reservation->save();

            // Create the actual ledger entry
            $idempotencyKey = "msg-{$messageLogId}";
            $accountId = $reservation->account_id;

            if ($isPostpay) {
                $entry = $this->ledger->recordPostpayMessageCharge(
                    $accountId, $amount, $currency, $productType,
                    $idempotencyKey, $reservation->sub_account_id, $messageLogId
                );
            } else {
                $entry = $this->ledger->recordPrepayMessageCharge(
                    $accountId, $amount, $currency, $productType,
                    $idempotencyKey, $reservation->sub_account_id, $messageLogId
                );
            }

            // Note: balance was already deducted via reservation
            // The actual balance row doesn't change here — it moves from reserved to spent

            return $entry;
        });
    }

    /**
     * Release unused reservation when campaign completes.
     */
    public function releaseReservation(string $reservationId): void
    {
        DB::transaction(function () use ($reservationId) {
            $reservation = CampaignReservation::where('id', $reservationId)
                ->lockForUpdate()
                ->firstOrFail();

            $unused = bcsub(
                bcsub($reservation->reserved_amount, $reservation->used_amount, 4),
                $reservation->released_amount,
                4
            );

            if (bccomp($unused, '0', 4) > 0) {
                $reservation->released_amount = bcadd($reservation->released_amount, $unused, 4);
                $reservation->status = 'completed';
                $reservation->save();

                $balance = AccountBalance::lockForAccount($reservation->account_id);
                $balance->reserved = bcsub($balance->reserved, $unused, 4);
                $balance->balance = bcadd($balance->balance, $unused, 4);
                $balance->recalculateEffectiveAvailable();
                $balance->save();
            } else {
                $reservation->status = 'completed';
                $reservation->save();
            }
        });
    }

    /**
     * Process postpay mid-month top-up (advance against AR).
     */
    public function processPostpayAdvance(
        string $accountId,
        string $amount,
        string $currency,
        string $idempotencyKey,
        ?array $metadata = null
    ): LedgerEntry {
        return DB::transaction(function () use ($accountId, $amount, $currency, $idempotencyKey, $metadata) {
            $balance = AccountBalance::lockForAccount($accountId);

            $entry = $this->ledger->recordInvoicePayment(
                $accountId, $amount, $currency, $idempotencyKey, null, $metadata
            );

            // Reduce outstanding AR
            $balance->total_outstanding = bcsub($balance->total_outstanding, $amount, 4);
            if (bccomp($balance->total_outstanding, '0', 4) < 0) {
                // Overpayment becomes a credit balance
                $balance->balance = bcadd($balance->balance, abs($balance->total_outstanding), 4);
                $balance->total_outstanding = '0';
            }
            $balance->recalculateEffectiveAvailable();
            $balance->save();

            return $entry;
        });
    }

    /**
     * Reconcile cached balance against ledger (daily job).
     */
    public function reconcileBalance(string $accountId): array
    {
        $account = Account::findOrFail($accountId);
        $balance = AccountBalance::where('account_id', $accountId)->firstOrFail();

        $ledgerBalance = $this->ledger->calculatePrepayBalanceFromLedger($accountId);
        $ledgerAr = $this->ledger->calculateArFromLedger($accountId);

        $balanceDelta = bcsub($ledgerBalance, $balance->balance, 4);
        $arDelta = bcsub($ledgerAr, $balance->total_outstanding, 4);

        $hasMismatch = bccomp(abs($balanceDelta), '0.01', 4) > 0
            || bccomp(abs($arDelta), '0.01', 4) > 0;

        if ($hasMismatch) {
            // Auto-correct to match ledger (ledger is source of truth)
            $balance->balance = $ledgerBalance;
            $balance->total_outstanding = $ledgerAr;
            $balance->recalculateEffectiveAvailable();
        }

        $balance->last_reconciled_at = now();
        $balance->save();

        return [
            'account_id' => $accountId,
            'mismatch' => $hasMismatch,
            'balance_delta' => $balanceDelta,
            'ar_delta' => $arDelta,
        ];
    }

    private function checkSubAccountCap(string $subAccountId, string $amount): void
    {
        $subAccount = SubAccount::findOrFail($subAccountId);

        if ($subAccount->spending_limit === null) {
            return; // Unlimited
        }

        $newSpend = bcadd($subAccount->spending_used_current_period, $amount, 4);
        if (bccomp($newSpend, $subAccount->spending_limit, 4) > 0) {
            throw new SubAccountSpendingLimitException(
                $subAccountId, $amount, $subAccount->spending_limit
            );
        }
    }
}
