<?php

namespace App\Services\Numbers;

use App\Models\Account;
use App\Models\Billing\AccountBalance;
use App\Models\Billing\ProductTierPrice;
use App\Models\PurchaseAuditLog;
use App\Models\PurchasedNumber;
use App\Models\ShortcodeKeyword;
use App\Models\User;
use App\Exceptions\Billing\InsufficientBalanceException;
use App\Services\Billing\BalanceService;
use App\Services\Billing\LedgerService;
use App\Services\Billing\PricingEngine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * NumberBillingService — handles all billing operations for number purchases.
 *
 * Responsibilities:
 * - Price resolution for VMN and keyword purchases (via PricingEngine waterfall)
 * - Immediate debit for setup fees
 * - Recurring charge creation for monthly fees
 * - Recurring charge cancellation on release
 * - Purchase audit logging (via existing PurchaseAuditLog)
 * - Refund on purchase failure
 * - Monthly renewal processing
 */
class NumberBillingService
{
    public function __construct(
        private BalanceService $balanceService,
        private LedgerService $ledgerService,
        private PricingEngine $pricingEngine,
    ) {}

    // =====================================================
    // PRICING
    // =====================================================

    /**
     * Calculate VMN purchase pricing for a set of pool numbers.
     *
     * Uses PricingEngine waterfall:
     * 1. Pool number override prices (if set by admin)
     * 2. Account tier pricing (virtual_number_setup / virtual_number_monthly)
     *
     * @return array ['total_setup_fee', 'total_monthly_fee', 'items' => [...per-number pricing]]
     */
    public function calculateVmnPricing(Account $account, Collection $poolNumbers): array
    {
        $items = [];
        $totalSetup = '0';
        $totalMonthly = '0';

        foreach ($poolNumbers as $poolNumber) {
            // Setup fee: pool override → tier pricing
            if ($poolNumber->setup_cost_override !== null) {
                $setupFee = (string) $poolNumber->setup_cost_override;
            } else {
                try {
                    $price = $this->pricingEngine->resolvePrice($account, 'virtual_number_setup', $poolNumber->country_iso);
                    $setupFee = $price->unitPrice;
                } catch (\Exception $e) {
                    $setupFee = '10.0000'; // Fallback
                }
            }

            // Monthly fee: pool override → tier pricing
            if ($poolNumber->monthly_cost_override !== null) {
                $monthlyFee = (string) $poolNumber->monthly_cost_override;
            } else {
                try {
                    $price = $this->pricingEngine->resolvePrice($account, 'virtual_number_monthly', $poolNumber->country_iso);
                    $monthlyFee = $price->unitPrice;
                } catch (\Exception $e) {
                    $monthlyFee = '8.0000'; // Fallback
                }
            }

            $items[$poolNumber->id] = [
                'number' => $poolNumber->number,
                'country_iso' => $poolNumber->country_iso,
                'setup_fee' => $setupFee,
                'monthly_fee' => $monthlyFee,
            ];

            $totalSetup = bcadd($totalSetup, $setupFee, 4);
            $totalMonthly = bcadd($totalMonthly, $monthlyFee, 4);
        }

        return [
            'total_setup_fee' => $totalSetup,
            'total_monthly_fee' => $totalMonthly,
            'currency' => $account->currency ?? 'GBP',
            'items' => $items,
        ];
    }

    /**
     * Calculate keyword purchase pricing.
     */
    public function calculateKeywordPricing(Account $account): array
    {
        try {
            $setupPrice = $this->pricingEngine->resolvePrice($account, 'shortcode_keyword_setup', null);
            $setupFee = $setupPrice->unitPrice;
        } catch (\Exception $e) {
            $setupFee = '2.0000';
        }

        try {
            $monthlyPrice = $this->pricingEngine->resolvePrice($account, 'shortcode_keyword_monthly', null);
            $monthlyFee = $monthlyPrice->unitPrice;
        } catch (\Exception $e) {
            $monthlyFee = '2.0000';
        }

        return [
            'setup_fee' => $setupFee,
            'monthly_fee' => $monthlyFee,
            'currency' => $account->currency ?? 'GBP',
        ];
    }

    // =====================================================
    // BILLING OPERATIONS
    // =====================================================

    /**
     * Debit setup fee from account balance.
     * Uses immediate debit pattern (Option 2 from spec).
     *
     * @throws InsufficientBalanceException
     */
    public function debitSetupFee(
        Account $account,
        string $amount,
        string $productType,
        string $description
    ): void {
        if (bccomp($amount, '0', 4) <= 0) {
            return; // No fee to charge
        }

        $balance = AccountBalance::lockForAccount($account->id);

        if (!$balance->hasSufficientBalance($amount)) {
            throw new InsufficientBalanceException($account->id, $amount, $balance->effective_available);
        }

        $idempotencyKey = 'number-setup-' . Str::uuid();
        $isPostpay = $account->billing_type === 'postpay';

        if ($isPostpay) {
            $this->ledgerService->createEntry(
                entryType: 'number_setup_postpay',
                accountId: $account->id,
                amount: $amount,
                description: "Number setup fee ({$description})",
                idempotencyKey: $idempotencyKey,
                lines: [
                    ['account_code' => 'AR', 'debit' => $amount, 'credit' => '0'],
                    ['account_code' => 'REVENUE_RECURRING', 'debit' => '0', 'credit' => $amount],
                ],
                currency: $account->currency ?? 'GBP',
            );
            $balance->total_outstanding = bcadd($balance->total_outstanding, $amount, 4);
        } else {
            $this->ledgerService->createEntry(
                entryType: 'number_setup_prepay',
                accountId: $account->id,
                amount: $amount,
                description: "Number setup fee ({$description})",
                idempotencyKey: $idempotencyKey,
                lines: [
                    ['account_code' => 'DEFERRED_REV', 'debit' => $amount, 'credit' => '0'],
                    ['account_code' => 'REVENUE_RECURRING', 'debit' => '0', 'credit' => $amount],
                ],
                currency: $account->currency ?? 'GBP',
            );
            $balance->balance = bcsub($balance->balance, $amount, 4);
        }

        $balance->recalculateEffectiveAvailable();
        $balance->save();
    }

    /**
     * Refund a setup fee (if purchase fails after debit).
     */
    public function refundSetupFee(Account $account, string $amount, string $description): void
    {
        if (bccomp($amount, '0', 4) <= 0) {
            return;
        }

        $balance = AccountBalance::lockForAccount($account->id);
        $isPostpay = $account->billing_type === 'postpay';
        $idempotencyKey = 'number-refund-' . Str::uuid();

        if ($isPostpay) {
            $this->ledgerService->createEntry(
                entryType: 'number_setup_refund',
                accountId: $account->id,
                amount: $amount,
                description: "Number setup fee refund ({$description})",
                idempotencyKey: $idempotencyKey,
                lines: [
                    ['account_code' => 'REVENUE_RECURRING', 'debit' => $amount, 'credit' => '0'],
                    ['account_code' => 'AR', 'debit' => '0', 'credit' => $amount],
                ],
                currency: $account->currency ?? 'GBP',
            );
            $balance->total_outstanding = bcsub($balance->total_outstanding, $amount, 4);
        } else {
            $this->ledgerService->createEntry(
                entryType: 'number_setup_refund',
                accountId: $account->id,
                amount: $amount,
                description: "Number setup fee refund ({$description})",
                idempotencyKey: $idempotencyKey,
                lines: [
                    ['account_code' => 'REVENUE_RECURRING', 'debit' => $amount, 'credit' => '0'],
                    ['account_code' => 'DEFERRED_REV', 'debit' => '0', 'credit' => $amount],
                ],
                currency: $account->currency ?? 'GBP',
            );
            $balance->balance = bcadd($balance->balance, $amount, 4);
        }

        $balance->recalculateEffectiveAvailable();
        $balance->save();
    }

    // =====================================================
    // RECURRING CHARGES
    // =====================================================

    /**
     * Create a recurring charge for a purchased VMN.
     */
    public function createRecurringCharge(Account $account, PurchasedNumber $number): void
    {
        DB::table('recurring_charges')->insert([
            'id' => (string) Str::uuid(),
            'account_id' => $account->id,
            'charge_type' => 'virtual_number',
            'description' => "VMN {$number->number} monthly fee",
            'amount' => $number->monthly_fee,
            'currency' => $number->currency,
            'frequency' => 'monthly',
            'next_charge_date' => now()->addMonth()->startOfDay()->toDateString(),
            'active' => true,
            'reference_type' => 'purchased_number',
            'reference_id' => $number->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Create a recurring charge for a keyword.
     */
    public function createKeywordRecurringCharge(
        Account $account,
        ShortcodeKeyword $keyword,
        PurchasedNumber $shortcode
    ): void {
        DB::table('recurring_charges')->insert([
            'id' => (string) Str::uuid(),
            'account_id' => $account->id,
            'charge_type' => 'shortcode',
            'description' => "Keyword '{$keyword->keyword}' on {$shortcode->number} monthly fee",
            'amount' => $keyword->monthly_fee,
            'currency' => $keyword->currency,
            'frequency' => 'monthly',
            'next_charge_date' => now()->addMonth()->startOfDay()->toDateString(),
            'active' => true,
            'reference_type' => 'shortcode_keyword',
            'reference_id' => $keyword->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Cancel the recurring charge for a purchased number.
     */
    public function cancelRecurringCharge(PurchasedNumber $number): void
    {
        DB::table('recurring_charges')
            ->where('reference_type', 'purchased_number')
            ->where('reference_id', $number->id)
            ->update(['active' => false, 'updated_at' => now()]);
    }

    /**
     * Cancel the recurring charge for a keyword.
     */
    public function cancelKeywordRecurringCharge(ShortcodeKeyword $keyword): void
    {
        DB::table('recurring_charges')
            ->where('reference_type', 'shortcode_keyword')
            ->where('reference_id', $keyword->id)
            ->update(['active' => false, 'updated_at' => now()]);
    }

    // =====================================================
    // RENEWAL (Monthly recurring charge processing)
    // =====================================================

    /**
     * Process all due recurring charges for numbers/keywords.
     * Called by a scheduled job (e.g. daily).
     *
     * For each due charge:
     * - Check balance → debit → advance next_charge_date
     * - If insufficient balance → suspend the number immediately
     *
     * @return array ['processed' => int, 'suspended' => int, 'failed' => int]
     */
    public function processRecurringCharges(): array
    {
        $dueCharges = DB::table('recurring_charges')
            ->where('active', true)
            ->where('next_charge_date', '<=', now()->toDateString())
            ->whereIn('charge_type', ['virtual_number', 'shortcode'])
            ->get();

        $processed = 0;
        $suspended = 0;
        $failed = 0;

        foreach ($dueCharges as $charge) {
            try {
                DB::transaction(function () use ($charge, &$processed, &$suspended) {
                    $account = Account::find($charge->account_id);
                    if (!$account) {
                        return;
                    }

                    $balance = AccountBalance::lockForAccount($account->id);

                    if ($balance->hasSufficientBalance($charge->amount)) {
                        // Debit the charge
                        $isPostpay = $account->billing_type === 'postpay';
                        $idempotencyKey = "recurring-{$charge->id}-" . now()->format('Y-m');

                        if ($isPostpay) {
                            $this->ledgerService->createEntry(
                                entryType: 'recurring_charge_postpay',
                                accountId: $account->id,
                                amount: (string) $charge->amount,
                                description: $charge->description,
                                idempotencyKey: $idempotencyKey,
                                lines: [
                                    ['account_code' => 'AR', 'debit' => (string) $charge->amount, 'credit' => '0'],
                                    ['account_code' => 'REVENUE_RECURRING', 'debit' => '0', 'credit' => (string) $charge->amount],
                                ],
                                currency: $charge->currency,
                            );
                            $balance->total_outstanding = bcadd($balance->total_outstanding, (string) $charge->amount, 4);
                        } else {
                            $this->ledgerService->createEntry(
                                entryType: 'recurring_charge_prepay',
                                accountId: $account->id,
                                amount: (string) $charge->amount,
                                description: $charge->description,
                                idempotencyKey: $idempotencyKey,
                                lines: [
                                    ['account_code' => 'DEFERRED_REV', 'debit' => (string) $charge->amount, 'credit' => '0'],
                                    ['account_code' => 'REVENUE_RECURRING', 'debit' => '0', 'credit' => (string) $charge->amount],
                                ],
                                currency: $charge->currency,
                            );
                            $balance->balance = bcsub($balance->balance, (string) $charge->amount, 4);
                        }

                        $balance->recalculateEffectiveAvailable();
                        $balance->save();

                        // Advance next charge date
                        DB::table('recurring_charges')
                            ->where('id', $charge->id)
                            ->update([
                                'next_charge_date' => now()->addMonth()->startOfDay()->toDateString(),
                                'updated_at' => now(),
                            ]);

                        $processed++;
                    } else {
                        // Insufficient balance → suspend immediately
                        $this->suspendNumberForNonPayment($charge);
                        $suspended++;
                    }
                });
            } catch (\Exception $e) {
                Log::error('[NumberBillingService] Failed to process recurring charge', [
                    'charge_id' => $charge->id,
                    'error' => $e->getMessage(),
                ]);
                $failed++;
            }
        }

        Log::info('[NumberBillingService] Recurring charges processed', [
            'processed' => $processed,
            'suspended' => $suspended,
            'failed' => $failed,
        ]);

        return compact('processed', 'suspended', 'failed');
    }

    /**
     * Suspend a number due to non-payment of recurring charge.
     */
    private function suspendNumberForNonPayment(object $charge): void
    {
        if ($charge->reference_type === 'purchased_number') {
            PurchasedNumber::withoutGlobalScopes()
                ->where('id', $charge->reference_id)
                ->where('status', PurchasedNumber::STATUS_ACTIVE)
                ->update([
                    'status' => PurchasedNumber::STATUS_SUSPENDED,
                    'suspended_at' => now(),
                ]);
        }
        // Keywords on suspended shortcodes are implicitly suspended
    }

    // =====================================================
    // AUDIT LOGGING
    // =====================================================

    /**
     * Create a purchase audit log for VMN purchases.
     */
    public function createPurchaseAuditLog(
        string $type,
        Account $account,
        User $purchaser,
        array $purchasedNumbers,
        array $pricing
    ): PurchaseAuditLog {
        $balance = AccountBalance::where('account_id', $account->id)->first();

        $items = [];
        foreach ($purchasedNumbers as $pn) {
            $items[] = [
                'number' => $pn->number,
                'number_type' => $pn->number_type,
                'country_iso' => $pn->country_iso,
                'setup_fee' => $pn->setup_fee,
                'monthly_fee' => $pn->monthly_fee,
            ];
        }

        return PurchaseAuditLog::logVmnPurchase([
            'user_id' => $purchaser->id,
            'user_email' => $purchaser->email,
            'user_name' => trim($purchaser->first_name . ' ' . $purchaser->last_name),
            'sub_account_id' => $purchaser->sub_account_id,
            'items_purchased' => $items,
            'pricing_details' => $pricing,
            'total_setup_fee' => $pricing['total_setup_fee'],
            'total_monthly_fee' => $pricing['total_monthly_fee'],
            'balance_before' => bcadd($balance->balance ?? '0', $pricing['total_setup_fee'], 4),
            'balance_after' => $balance->balance ?? '0',
            'currency' => $pricing['currency'],
            'status' => 'completed',
            'transaction_reference' => 'VMN-' . strtoupper(Str::random(12)),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Create a purchase audit log for keyword purchases.
     */
    public function createKeywordPurchaseAuditLog(
        Account $account,
        User $purchaser,
        ShortcodeKeyword $keyword,
        PurchasedNumber $shortcode,
        array $pricing
    ): PurchaseAuditLog {
        $balance = AccountBalance::where('account_id', $account->id)->first();

        return PurchaseAuditLog::logKeywordPurchase([
            'user_id' => $purchaser->id,
            'user_email' => $purchaser->email,
            'user_name' => trim($purchaser->first_name . ' ' . $purchaser->last_name),
            'sub_account_id' => $purchaser->sub_account_id,
            'items_purchased' => [[
                'keyword' => $keyword->keyword,
                'shortcode' => $shortcode->number,
                'setup_fee' => $pricing['setup_fee'],
                'monthly_fee' => $pricing['monthly_fee'],
            ]],
            'pricing_details' => $pricing,
            'total_setup_fee' => $pricing['setup_fee'],
            'total_monthly_fee' => $pricing['monthly_fee'],
            'balance_before' => bcadd($balance->balance ?? '0', $pricing['setup_fee'], 4),
            'balance_after' => $balance->balance ?? '0',
            'currency' => $pricing['currency'],
            'status' => 'completed',
            'transaction_reference' => 'KWD-' . strtoupper(Str::random(12)),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
