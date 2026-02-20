<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Billing\AccountBalance;
use App\Models\Billing\CustomerPrice;
use App\Models\Billing\RecurringCharge;
use App\Models\Billing\TestCreditWallet;
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

        $entry = $this->ledgerService->recordManualAdjustment(
            $id, $amount, $account->currency,
            $request->input('direction'),
            $request->input('reason'),
            $idempotencyKey, $adminId
        );

        // Update cached balance
        $balance = AccountBalance::lockForAccount($id);
        if ($request->input('direction') === 'credit') {
            $balance->balance = bcadd($balance->balance, $amount, 4);
        } else {
            $balance->balance = bcsub($balance->balance, $amount, 4);
        }
        $balance->recalculateEffectiveAvailable();
        $balance->save();

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

        $account->update(['credit_limit' => $new]);

        // Sync to account_balances
        $balance = AccountBalance::lockForAccount($id);
        $balance->credit_limit = $new;
        $balance->recalculateEffectiveAvailable();
        $balance->save();

        FinancialAuditLog::record(
            'credit_limit_changed', 'account', $id,
            ['credit_limit' => $old], ['credit_limit' => $new],
            $this->getAdminId($request), 'admin'
        );

        return response()->json(['success' => true]);
    }

    /**
     * PUT /api/admin/v1/accounts/{id}/payment-terms
     */
    public function updatePaymentTerms(Request $request, string $id): JsonResponse
    {
        $request->validate(['payment_terms_days' => 'required|integer|in:15,30,60']);

        Account::findOrFail($id)->update(['payment_terms_days' => $request->input('payment_terms_days')]);

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
            'prices.*.unit_price' => 'required|numeric|min:0',
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

        // Queue HubSpot sync
        dispatch(function () use ($account) {
            app(HubSpotPricingSyncService::class)->syncToHubSpot($account);
        })->afterResponse();

        FinancialAuditLog::record(
            'pricing_override', 'account', $id,
            null, ['prices' => $request->input('prices')],
            $adminId, 'admin'
        );

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
     * Award additional test credits.
     */
    public function awardTestCredits(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'credits' => 'required|integer|min:1|max:10000',
            'reason' => 'required|string|max:255',
        ]);

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
        return session('admin_user_id', 'system');
    }
}
