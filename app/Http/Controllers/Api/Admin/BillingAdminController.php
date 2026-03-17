<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Billing\AccountBalance;
use App\Models\Billing\CustomerPrice;
use App\Models\Billing\RecurringCharge;
use App\Models\Billing\TestCreditWallet;
use App\Models\AdminAuditLog;
use App\Models\Billing\FinancialAuditLog;
use App\Services\Billing\BalanceService;
use App\Services\Billing\LedgerService;
use App\Services\Billing\InvoiceService;
use App\Services\Billing\PricingEngine;
use App\Services\Billing\HubSpotPricingSyncService;
use App\Services\Billing\ReconciliationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BillingAdminController extends Controller
{
    public function __construct(
        private BalanceService $balanceService,
        private LedgerService $ledgerService,
        private InvoiceService $invoiceService,
        private PricingEngine $pricingEngine,
        private HubSpotPricingSyncService $hubspotSync,
        private ReconciliationService $reconciliationService,
    ) {}

    // ───── Account Balance ─────

    /**
     * GET /api/admin/v1/accounts/{id}/balance
     */
    public function accountBalance(string $id): JsonResponse
    {
        $balance = $this->balanceService->getBalance($id);
        $account = Account::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'account_id' => $id,
                'company_name' => $account->company_name,
                'billing_type' => $account->billing_type,
                'product_tier' => $account->product_tier,
                'currency' => $balance->currency,
                'balance' => $balance->balance,
                'reserved' => $balance->reserved,
                'credit_limit' => $balance->credit_limit,
                'effective_available' => $balance->effective_available,
                'total_outstanding' => $balance->total_outstanding,
                'last_reconciled_at' => $balance->last_reconciled_at?->toIso8601String(),
            ],
        ]);
    }

    /**
     * GET /api/admin/v1/accounts/{id}/transactions
     */
    public function accountTransactions(Request $request, string $id): JsonResponse
    {
        $perPage = min((int)$request->input('per_page', 50), 200);
        $transactions = $this->ledgerService->getTransactionHistory($id, $perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions->items(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * POST /api/admin/v1/accounts/{id}/adjustment
     * Manual balance adjustment.
     */
    public function manualAdjustment(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:100000',
            'direction' => 'required|in:credit,debit',
            'reason' => 'required|string|min:10|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $account = Account::findOrFail($id);
        $adminId = $this->getAdminId($request);
        $amount = number_format((float)$request->input('amount'), 4, '.', '');
        $idempotencyKey = 'adj-' . Str::uuid();

        $entry = DB::transaction(function () use ($id, $amount, $account, $request, $idempotencyKey, $adminId) {
            $entry = $this->ledgerService->recordManualAdjustment(
                $id, $amount, $account->currency,
                $request->input('direction'),
                $request->input('reason'),
                $idempotencyKey, $adminId
            );

            // Update cached balance atomically within same transaction
            $balance = AccountBalance::lockForAccount($id);
            if ($request->input('direction') === 'credit') {
                $balance->balance = bcadd($balance->balance, $amount, 4);
            } else {
                $balance->balance = bcsub($balance->balance, $amount, 4);
            }
            $balance->recalculateEffectiveAvailable();
            $balance->save();

            return $entry;
        });

        return response()->json([
            'success' => true,
            'data' => $entry,
        ]);
    }

    // ───── Account Settings ─────

    /**
     * PUT /api/admin/v1/accounts/{id}/billing-type
     */
    public function updateBillingType(Request $request, string $id): JsonResponse
    {
        $request->validate(['billing_type' => 'required|in:prepay,postpay']);

        $account = Account::findOrFail($id);
        $old = $account->billing_type;

        DB::statement("UPDATE accounts SET billing_type = ? WHERE id = ?", [$request->input('billing_type'), $id]);

        FinancialAuditLog::record(
            'billing_type_changed', 'account', $id,
            ['billing_type' => $old], ['billing_type' => $request->input('billing_type')],
            $this->getAdminId($request), 'admin'
        );

        return response()->json(['success' => true]);
    }

    /**
     * PUT /api/admin/v1/accounts/{id}/billing-method
     */
    public function updateBillingMethod(Request $request, string $id): JsonResponse
    {
        $request->validate(['billing_method' => 'required|in:submitted,delivered']);

        DB::statement("UPDATE accounts SET billing_method = ? WHERE id = ?", [$request->input('billing_method'), $id]);

        FinancialAuditLog::record(
            'billing_method_changed', 'account', $id,
            null, ['billing_method' => $request->input('billing_method')],
            $this->getAdminId($request), 'admin'
        );

        return response()->json(['success' => true]);
    }

    /**
     * PUT /api/admin/v1/accounts/{id}/credit-limit
     */
    public function updateCreditLimit(Request $request, string $id): JsonResponse
    {
        $request->validate(['credit_limit' => 'required|numeric|min:0|max:1000000']);

        $account = Account::findOrFail($id);
        $old = $account->credit_limit;
        $new = number_format((float)$request->input('credit_limit'), 4, '.', '');
        $adminId = $this->getAdminId($request);

        DB::transaction(function () use ($account, $id, $new, $old, $adminId) {
            $account->update(['credit_limit' => $new]);

            // Sync to account_balances atomically within same transaction
            $balance = AccountBalance::lockForAccount($id);
            $balance->credit_limit = $new;
            $balance->recalculateEffectiveAvailable();
            $balance->save();

            FinancialAuditLog::record(
                'credit_limit_changed', 'account', $id,
                ['credit_limit' => $old], ['credit_limit' => $new],
                $adminId, 'admin'
            );
        });

        return response()->json(['success' => true]);
    }

    /**
     * PUT /api/admin/v1/accounts/{id}/payment-terms
     */
    public function updatePaymentTerms(Request $request, string $id): JsonResponse
    {
        $request->validate(['payment_terms_days' => 'required|integer|in:15,20,30,40,60']);

        $account = Account::findOrFail($id);
        $previousDays = $account->payment_terms_days;
        $newDays = (int) $request->input('payment_terms_days');

        $account->update(['payment_terms_days' => $newDays]);

        try {
            AdminAuditLog::record(
                'payment_terms_updated', 'billing', 'medium',
                session('admin_auth.admin_id'), session('admin_auth.name'),
                'account', $account->id, $account->id,
                "Payment terms changed from Net {$previousDays} to Net {$newDays}",
                ['previous_days' => $previousDays, 'new_days' => $newDays]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to audit payment terms change', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * PUT /api/admin/v1/accounts/{id}/overdue-enforcement
     */
    public function updateOverdueEnforcement(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'overdue_enforcement_mode' => 'required|string|in:hard,soft,none',
            'overdue_grace_days' => 'required_if:overdue_enforcement_mode,soft|integer|min:0|max:90',
            'overdue_email_frequency' => 'required|string|in:daily,every_3_days,weekly,fortnightly,none',
        ]);

        $account = Account::findOrFail($id);

        $previousMode = $account->overdue_enforcement_mode ?? 'hard';
        $previousGraceDays = $account->overdue_grace_days ?? 0;
        $previousEmailFreq = $account->overdue_email_frequency ?? 'weekly';

        $newMode = $request->input('overdue_enforcement_mode');
        $newGraceDays = $newMode === 'soft' ? (int) $request->input('overdue_grace_days', 0) : 0;
        $newEmailFreq = $request->input('overdue_email_frequency');

        $account->update([
            'overdue_enforcement_mode' => $newMode,
            'overdue_grace_days' => $newGraceDays,
            'overdue_email_frequency' => $newEmailFreq,
        ]);

        try {
            AdminAuditLog::record(
                'overdue_enforcement_updated', 'billing', 'medium',
                session('admin_auth.admin_id'), session('admin_auth.name'),
                'account', $account->id, $account->id,
                "Overdue enforcement updated: mode={$newMode}, grace={$newGraceDays}d, emails={$newEmailFreq}",
                [
                    'previous' => ['mode' => $previousMode, 'grace_days' => $previousGraceDays, 'email_frequency' => $previousEmailFreq],
                    'new' => ['mode' => $newMode, 'grace_days' => $newGraceDays, 'email_frequency' => $newEmailFreq],
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to audit overdue enforcement change', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true]);
    }

    // ───── Pricing ─────

    /**
     * GET /api/admin/v1/accounts/{id}/pricing
     */
    public function accountPricing(string $id): JsonResponse
    {
        $account = Account::findOrFail($id);
        $prices = $this->pricingEngine->getCustomerPricing($account);

        return response()->json([
            'success' => true,
            'data' => [
                'product_tier' => $account->product_tier,
                'prices' => $prices,
            ],
        ]);
    }

    /**
     * PUT /api/admin/v1/accounts/{id}/pricing
     * Set/override customer pricing (bespoke only).
     */
    public function updateAccountPricing(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prices' => 'required|array|min:1',
            'prices.*.product_type' => 'required|string',
            'prices.*.country_iso' => 'nullable|string|size:2',
            'prices.*.unit_price' => 'required|numeric|min:0.0001',
            'change_reason' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $account = Account::findOrFail($id);

        if ($account->product_tier !== 'bespoke') {
            return response()->json([
                'success' => false,
                'error' => 'Pricing overrides only available for bespoke accounts.',
            ], 422);
        }

        $adminId = $this->getAdminId($request);

        DB::transaction(function () use ($request, $id, $account, $adminId) {
            foreach ($request->input('prices') as $priceData) {
                // Deactivate existing
                CustomerPrice::where('account_id', $id)
                    ->where('product_type', $priceData['product_type'])
                    ->where('country_iso', $priceData['country_iso'] ?? null)
                    ->where('active', true)
                    ->update(['active' => false]);

                CustomerPrice::create([
                    'account_id' => $id,
                    'product_type' => $priceData['product_type'],
                    'country_iso' => $priceData['country_iso'] ?? null,
                    'unit_price' => $priceData['unit_price'],
                    'currency' => $account->currency,
                    'source' => 'admin_override',
                    'set_by' => $adminId,
                    'set_at' => now(),
                    'valid_from' => now()->toDateString(),
                    'active' => true,
                    'version' => 1,
                    'change_reason' => $request->input('change_reason'),
                ]);
            }

            FinancialAuditLog::record(
                'pricing_override', 'account', $id,
                null, ['prices' => $request->input('prices')],
                $adminId, 'admin'
            );
        });

        // Queue HubSpot sync (outside transaction — non-critical)
        dispatch(function () use ($account) {
            app(HubSpotPricingSyncService::class)->syncToHubSpot($account);
        })->afterResponse();

        return response()->json(['success' => true]);
    }

    /**
     * GET /api/admin/v1/pricing/conflicts
     */
    public function pricingConflicts(): JsonResponse
    {
        $conflicts = $this->hubspotSync->getUnresolvedConflicts();

        return response()->json([
            'success' => true,
            'data' => $conflicts->items(),
            'meta' => [
                'total' => $conflicts->total(),
            ],
        ]);
    }

    /**
     * POST /api/admin/v1/pricing/conflicts/{id}/resolve
     */
    public function resolveConflict(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'resolution' => 'required|in:accept_hubspot,accept_admin,custom',
            'custom_value' => 'required_if:resolution,custom|numeric',
        ]);

        $this->hubspotSync->resolveConflict(
            $id,
            $request->input('resolution'),
            $this->getAdminId($request),
            $request->input('custom_value')
        );

        return response()->json(['success' => true]);
    }

    // ───── Invoices ─────

    /**
     * GET /api/admin/v1/invoices
     */
    public function invoices(Request $request): JsonResponse
    {
        $query = \App\Models\Billing\Invoice::with('account:id,company_name');

        if ($request->has('account_id')) {
            $query->where('account_id', $request->input('account_id'));
        }
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = min((int)$request->input('per_page', 50), 200);
        $invoices = $query->orderBy('issued_date', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $invoices->items(),
            'meta' => ['total' => $invoices->total()],
        ]);
    }

    /**
     * POST /api/admin/v1/accounts/{id}/invoices/generate
     * Manual invoice generation.
     */
    public function generateInvoice(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
        ]);

        $account = Account::findOrFail($id);
        $invoice = $this->invoiceService->generateMonthlyInvoice(
            $account,
            Carbon::parse($request->input('period_start')),
            Carbon::parse($request->input('period_end'))
        );

        return response()->json(['success' => true, 'data' => $invoice]);
    }

    /**
     * POST /api/admin/v1/invoices/{id}/void
     */
    public function voidInvoice(Request $request, string $id): JsonResponse
    {
        $invoice = $this->invoiceService->voidInvoice($id, $this->getAdminId($request));
        return response()->json(['success' => true, 'data' => $invoice]);
    }

    /**
     * POST /api/admin/v1/invoices/{id}/record-payment
     * Manually record a payment against an invoice (full or partial).
     */
    public function recordPayment(Request $request, string $id): JsonResponse
    {
        $invoice = \App\Models\Billing\Invoice::findOrFail($id);

        if (in_array($invoice->status, ['paid', 'void', 'written_off'])) {
            return response()->json([
                'success' => false,
                'error' => "Cannot record payment against a {$invoice->status} invoice.",
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:bank_transfer,stripe_checkout,stripe_dd',
            'reference' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $amount = number_format((float) $request->input('amount'), 4, '.', '');
        $amountDue = $invoice->amount_due;

        if (bccomp($amount, $amountDue, 4) > 0) {
            return response()->json([
                'success' => false,
                'error' => "Payment amount ({$amount}) exceeds amount due ({$amountDue}).",
            ], 422);
        }

        $adminId = $this->getAdminId($request);
        $adminName = session('admin_user_name', 'Admin');

        $payment = DB::transaction(function () use ($invoice, $amount, $amountDue, $request, $adminId) {
            $payment = \App\Models\Billing\Payment::create([
                'account_id' => $invoice->account_id,
                'invoice_id' => $invoice->id,
                'payment_method' => $request->input('payment_method'),
                'currency' => $invoice->currency,
                'amount' => $amount,
                'status' => 'succeeded',
                'paid_at' => now(),
                'metadata' => array_filter([
                    'reference' => $request->input('reference'),
                    'recorded_by' => $adminId,
                    'source' => 'admin_manual',
                ]),
            ]);

            $idempotencyKey = "admin-payment-{$payment->id}";
            $this->ledgerService->recordInvoicePayment(
                $invoice->account_id,
                $amount,
                $invoice->currency,
                $idempotencyKey,
                $invoice->id,
                ['admin_id' => $adminId, 'reference' => $request->input('reference')]
            );

            $newAmountPaid = bcadd($invoice->amount_paid, $amount, 4);
            $newAmountDue = bcsub($amountDue, $amount, 4);
            $isFullyPaid = bccomp($newAmountDue, '0', 4) <= 0;

            $invoice->update([
                'amount_paid' => $newAmountPaid,
                'amount_due' => $isFullyPaid ? '0' : $newAmountDue,
                'status' => $isFullyPaid ? 'paid' : 'partially_paid',
                'paid_date' => $isFullyPaid ? now()->toDateString() : $invoice->paid_date,
            ]);

            $balance = AccountBalance::lockForAccount($invoice->account_id);
            $balance->total_outstanding = bcsub($balance->total_outstanding, $amount, 4);
            if (bccomp($balance->total_outstanding, '0', 4) < 0) {
                $balance->balance = bcadd($balance->balance, bcmul($balance->total_outstanding, '-1', 4), 4);
                $balance->total_outstanding = '0';
            }
            $balance->recalculateEffectiveAvailable();
            $balance->save();

            return $payment;
        });

        try {
            \App\Models\AdminAuditLog::record(
                action: 'invoice_payment_recorded',
                category: 'billing',
                severity: 'high',
                adminUserId: $adminId,
                adminUserName: $adminName,
                targetType: 'invoice',
                targetId: $invoice->id,
                targetAccountId: $invoice->account_id,
                details: "Recorded {$invoice->currency} {$amount} payment against invoice {$invoice->invoice_number}",
                metadata: [
                    'payment_id' => $payment->id,
                    'amount' => $amount,
                    'payment_method' => $request->input('payment_method'),
                    'reference' => $request->input('reference'),
                    'new_status' => $invoice->fresh()->status,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('[AdminBilling] Audit log failed for record-payment', ['error' => $e->getMessage()]);
        }

        $invoice->refresh();

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
                'amount' => $amount,
                'new_status' => $invoice->status,
                'amount_paid' => $invoice->amount_paid,
                'amount_due' => $invoice->amount_due,
            ],
        ]);
    }

    /**
     * GET /api/admin/v1/invoices/{id}/payments
     * Get payment history for an invoice.
     */
    public function invoicePayments(string $id): JsonResponse
    {
        $invoice = \App\Models\Billing\Invoice::findOrFail($id);

        $payments = \App\Models\Billing\Payment::where('invoice_id', $id)
            ->where('status', 'succeeded')
            ->orderBy('paid_at', 'desc')
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'amount' => (float) $p->amount,
                    'currency' => $p->currency,
                    'payment_method' => $p->payment_method,
                    'reference' => $p->metadata['reference'] ?? null,
                    'source' => $p->metadata['source'] ?? ($p->xero_payment_id ? 'xero' : ($p->stripe_payment_intent_id ? 'stripe' : 'unknown')),
                    'paid_at' => $p->paid_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    // ───── Credit Notes ─────

    /**
     * POST /api/admin/v1/accounts/{id}/credit-notes
     */
    public function issueCreditNote(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:100000',
            'reason' => 'required|string|min:10|max:500',
            'original_invoice_id' => 'nullable|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $account = Account::findOrFail($id);
        $amount = number_format((float)$request->input('amount'), 4, '.', '');

        $creditNote = $this->invoiceService->issueCreditNote(
            $account, $amount, $request->input('reason'),
            $this->getAdminId($request), $request->input('original_invoice_id')
        );

        return response()->json(['success' => true, 'data' => $creditNote], 201);
    }

    // ───── Recurring Charges ─────

    /**
     * GET /api/admin/v1/accounts/{id}/recurring-charges
     */
    public function recurringCharges(string $id): JsonResponse
    {
        $charges = RecurringCharge::where('account_id', $id)->orderBy('charge_type')->get();
        return response()->json(['success' => true, 'data' => $charges]);
    }

    /**
     * POST /api/admin/v1/accounts/{id}/recurring-charges
     */
    public function createRecurringCharge(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'charge_type' => 'required|in:virtual_number,shortcode,platform_fee,support_fee',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $account = Account::findOrFail($id);

        $charge = RecurringCharge::create([
            'account_id' => $id,
            'charge_type' => $request->input('charge_type'),
            'description' => $request->input('description'),
            'amount' => $request->input('amount'),
            'currency' => $account->currency,
            'next_charge_date' => now()->startOfMonth()->addMonth()->toDateString(),
            'active' => true,
        ]);

        return response()->json(['success' => true, 'data' => $charge], 201);
    }

    // ───── Test Credits ─────

    /**
     * GET /api/admin/v1/accounts/{id}/test-credits
     */
    public function testCredits(string $id): JsonResponse
    {
        $wallet = TestCreditWallet::where('account_id', $id)->first();
        return response()->json(['success' => true, 'data' => $wallet]);
    }

    /**
     * POST /api/admin/v1/accounts/{id}/test-credits
     * Award additional test credits. No upper limit - admin discretion.
     * Only accounts in test mode (test_standard, test_dynamic) can receive test credits.
     * Customers cannot purchase test credits; they must activate and fund prepay wallet.
     */
    public function awardTestCredits(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'credits' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        $account = Account::findOrFail($id);

        if (!$account->isTestMode()) {
            return response()->json([
                'success' => false,
                'message' => 'Test credits can only be awarded to accounts in test mode (test_standard or test_dynamic). '
                    . "Current status: {$account->status}",
            ], 422);
        }

        $wallet = TestCreditWallet::where('account_id', $id)->first();

        if (!$wallet) {
            $wallet = TestCreditWallet::create([
                'account_id' => $id,
                'credits_total' => $request->input('credits'),
                'credits_remaining' => $request->input('credits'),
                'awarded_by' => $this->getAdminId($request),
                'awarded_reason' => $request->input('reason'),
                'expires_at' => now()->addDays(30),
            ]);
        } else {
            $wallet->credits_total += $request->input('credits');
            $wallet->credits_remaining += $request->input('credits');
            $wallet->save();
        }

        Log::info('[AdminBilling] Test credits awarded', [
            'account_id' => $id,
            'credits' => $request->input('credits'),
            'new_total' => $wallet->credits_total,
            'new_remaining' => $wallet->credits_remaining,
            'admin_id' => $this->getAdminId($request),
            'reason' => $request->input('reason'),
        ]);

        return response()->json(['success' => true, 'data' => $wallet]);
    }

    // ───── Reconciliation ─────

    /**
     * POST /api/admin/v1/reconciliation/balance/run
     */
    public function runReconciliation(): JsonResponse
    {
        $results = $this->reconciliationService->reconcileAllBalances();
        return response()->json(['success' => true, 'data' => $results]);
    }

    // ───── Margin Reporting ─────

    /**
     * GET /api/admin/v1/reporting/margin
     */
    public function marginReport(Request $request): JsonResponse
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $margin = DB::table('supplier_cost_log')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("
                SUM(customer_price) as total_revenue,
                SUM(supplier_cost_gbp) as total_cost,
                SUM(margin_amount) as total_margin,
                AVG(margin_percentage) as avg_margin_pct,
                COUNT(*) as total_messages
            ")
            ->first();

        return response()->json(['success' => true, 'data' => $margin]);
    }

    /**
     * GET /api/admin/v1/reporting/margin/by-account
     */
    public function marginByAccount(Request $request): JsonResponse
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $data = DB::table('supplier_cost_log')
            ->join('accounts', 'accounts.id', '=', 'supplier_cost_log.account_id')
            ->whereBetween('supplier_cost_log.created_at', [$from, $to])
            ->selectRaw("
                accounts.id as account_id,
                accounts.company_name,
                SUM(customer_price) as revenue,
                SUM(supplier_cost_gbp) as cost,
                SUM(margin_amount) as margin,
                AVG(margin_percentage) as avg_margin_pct,
                COUNT(*) as messages
            ")
            ->groupBy('accounts.id', 'accounts.company_name')
            ->orderByDesc('margin')
            ->limit(100)
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * GET /api/admin/v1/reporting/margin/by-country
     */
    public function marginByCountry(Request $request): JsonResponse
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());

        $data = DB::table('supplier_cost_log')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("
                country_iso,
                product_type,
                SUM(customer_price) as revenue,
                SUM(supplier_cost_gbp) as cost,
                SUM(margin_amount) as margin,
                AVG(margin_percentage) as avg_margin_pct,
                COUNT(*) as messages
            ")
            ->groupBy('country_iso', 'product_type')
            ->orderByDesc('margin')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    // ───── Audit ─────

    /**
     * GET /api/admin/v1/audit/financial
     */
    public function auditLog(Request $request): JsonResponse
    {
        $perPage = min((int)$request->input('per_page', 50), 200);

        $logs = FinancialAuditLog::orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'meta' => ['total' => $logs->total()],
        ]);
    }

    private function getAdminId(Request $request): string
    {
        $adminId = session('admin_user_id') ?? session('admin_auth.id');
        if (!$adminId) {
            throw new \RuntimeException('Admin identity could not be determined. Financial operations require authenticated admin.');
        }
        return $adminId;
    }
}
