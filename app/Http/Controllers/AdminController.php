<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Admin\ImpersonationService;
use App\Services\Admin\AdminLoginPolicyService;
use App\Services\Admin\AdminAuditService;
use App\Services\Admin\MessageEnforcementService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\MccMnc;
use App\Models\Gateway;
use App\Models\Supplier;
use App\Models\RateCard;
use App\Models\RoutingRule;
use App\Models\RoutingGatewayWeight;
use App\Models\RoutingCustomerOverride;
use App\Models\Account;
use App\Models\User;

class AdminController extends Controller
{
    protected ImpersonationService $impersonationService;
    protected AdminLoginPolicyService $loginPolicyService;
    protected MessageEnforcementService $enforcementService;
    
    public function __construct(
        ImpersonationService $impersonationService, 
        AdminLoginPolicyService $loginPolicyService,
        MessageEnforcementService $enforcementService
    ) {
        $this->impersonationService = $impersonationService;
        $this->loginPolicyService = $loginPolicyService;
        $this->enforcementService = $enforcementService;
    }
    
    public function startImpersonation(Request $request)
    {
        $request->validate([
            'target_user_id' => 'required|string',
            'duration_minutes' => 'required|integer|in:15,30,60,120',
            'reason' => 'required|string|min:10',
        ]);
        
        $adminEmail = session('admin_auth.email', session('admin_email', 'unknown'));

        if (!$this->impersonationService->canImpersonate($adminEmail)) {
            Log::warning('[AdminController] Unauthorized impersonation attempt', [
                'admin_email' => $adminEmail,
                'target_user_id' => $request->input('target_user_id'),
            ]);
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        try {
            $result = $this->impersonationService->startSession(
                $adminEmail,
                $request->input('target_user_id'),
                $request->input('duration_minutes'),
                $request->input('reason')
            );
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('[AdminController] Impersonation failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Impersonation failed. Please try again or contact support.'], 403);
        }
    }

    public function endImpersonation(Request $request)
    {
        $sessionId = $request->input('session_id');
        
        if (!$sessionId) {
            $session = $this->impersonationService->getCurrentSession();
            $sessionId = $session ? $session['session_id'] : null;
        }
        
        if (!$sessionId) {
            return response()->json(['error' => 'No active session'], 400);
        }
        
        $result = $this->impersonationService->endSession($sessionId, 'manual');
        return response()->json($result);
    }
    
    public function getImpersonationStatus()
    {
        $session = $this->impersonationService->getCurrentSession();
        
        if (!$session) {
            return response()->json([
                'active' => false,
            ]);
        }
        
        return response()->json([
            'active' => true,
            'session' => $session,
            'remaining_seconds' => $this->impersonationService->getRemainingTime(),
            'pii_masked' => $this->impersonationService->isPiiMasked(),
        ]);
    }
    
    public function validateLoginPolicy(Request $request)
    {
        $email = $request->input('email', '');
        $ipAddress = $request->ip();
        
        $result = $this->loginPolicyService->validateLoginPolicy($email, $ipAddress);
        
        if (!$result['allowed']) {
            return response()->json(['error' => 'Access denied'], 403);
        }
        
        return response()->json([
            'allowed' => true,
            'mfa_required' => $result['mfa_required'],
            'allowed_mfa_methods' => $result['allowed_mfa_methods'],
        ]);
    }
    
    public function logAdminUserEvent(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string',
            'target_admin_email' => 'required|string',
        ]);
        
        $actorAdmin = session('admin_auth.email', session('admin_email', 'unknown'));
        $eventType = $request->input('event_type');
        $targetEmail = $request->input('target_admin_email');
        $beforeValues = $request->input('before_values');
        $afterValues = $request->input('after_values');
        $reason = $request->input('reason');
        
        switch ($eventType) {
            case 'ADMIN_USER_INVITED':
                $role = $afterValues['role'] ?? 'Internal Support';
                AdminAuditService::logUserInvited($actorAdmin, $targetEmail, $role);
                break;
                
            case 'ADMIN_USER_INVITE_RESENT':
                AdminAuditService::logInviteResent($actorAdmin, $targetEmail);
                break;
                
            case 'ADMIN_USER_ACTIVATED':
                AdminAuditService::logUserActivated($actorAdmin, $targetEmail);
                break;
                
            case 'ADMIN_USER_SUSPENDED':
                $previousStatus = $beforeValues['status'] ?? 'Active';
                AdminAuditService::logUserSuspended($actorAdmin, $targetEmail, $previousStatus, $reason ?? 'No reason provided');
                break;
                
            case 'ADMIN_USER_REACTIVATED':
                AdminAuditService::logUserReactivated($actorAdmin, $targetEmail, $reason ?? 'No reason provided');
                break;
                
            case 'ADMIN_USER_ARCHIVED':
                $previousStatus = $beforeValues['status'] ?? 'Unknown';
                AdminAuditService::logUserArchived($actorAdmin, $targetEmail, $previousStatus, $reason ?? 'No reason provided');
                break;
                
            case 'ADMIN_USER_PASSWORD_RESET':
                AdminAuditService::logPasswordReset($actorAdmin, $targetEmail);
                break;
                
            case 'ADMIN_USER_MFA_RESET':
                $tempDisable = $afterValues['temporary_disable'] ?? false;
                $disableHours = $afterValues['disable_hours'] ?? null;
                AdminAuditService::logMfaReset($actorAdmin, $targetEmail, $tempDisable, $disableHours);
                break;
                
            case 'ADMIN_USER_MFA_UPDATED':
                $prevMethod = $beforeValues['mfa_method'] ?? 'Unknown';
                $newMethod = $afterValues['mfa_method'] ?? 'Unknown';
                $newPhone = $afterValues['mfa_phone'] ?? null;
                AdminAuditService::logMfaUpdated($actorAdmin, $targetEmail, $prevMethod, $newMethod, $newPhone);
                break;
                
            case 'ADMIN_USER_EMAIL_UPDATED':
                $prevEmail = $beforeValues['email'] ?? '';
                $newEmail = $afterValues['email'] ?? '';
                AdminAuditService::logEmailUpdated($actorAdmin, $targetEmail, $prevEmail, $newEmail, $reason ?? 'No reason provided');
                break;
                
            case 'ADMIN_USER_SESSIONS_REVOKED':
                $sessionsRevoked = $afterValues['sessions_revoked'] ?? 1;
                AdminAuditService::logSessionsRevoked($actorAdmin, $targetEmail, $sessionsRevoked);
                break;
                
            default:
                AdminAuditService::logAdminUserEvent($eventType, $actorAdmin, $targetEmail, $beforeValues, $afterValues, $reason);
        }
        
        return response()->json(['success' => true, 'event_logged' => $eventType]);
    }

    public function dashboard()
    {
        return view('admin.dashboard', [
            'page_title' => 'Admin Dashboard'
        ]);
    }

    public function approvalQueue()
    {
        return view('admin.approval-queue', [
            'page_title' => 'Approval Queue'
        ]);
    }

    public function accountsOverview()
    {
        $systemId = '00000000-0000-0000-0000-000000000001';

        $accounts = Account::where('accounts.id', '!=', $systemId)
            ->leftJoin('account_balances', 'accounts.id', '=', 'account_balances.account_id')
            ->select('accounts.*', 'account_balances.balance as current_balance')
            ->orderBy('accounts.created_at', 'desc')
            ->get();

        $counts = [
            'active' => Account::where('id', '!=', $systemId)->where('status', 'active')->count(),
            'trial' => Account::where('id', '!=', $systemId)->where('account_type', 'trial')->count(),
            'suspended' => Account::where('id', '!=', $systemId)->where('status', 'suspended')->count(),
            'pending' => Account::where('id', '!=', $systemId)->where('activation_complete', false)->where('status', 'active')->count(),
            'flagged' => DB::table('account_flags')
                ->where('account_id', '!=', $systemId)
                ->where(function($q) {
                    $q->where('fraud_risk_level', 'high')->orWhere('under_investigation', true);
                })->count(),
        ];

        return view('admin.accounts.overview', [
            'page_title' => 'Account Overview',
            'accounts' => $accounts,
            'counts' => $counts,
        ]);
    }

    public function accountsSubAccounts()
    {
        return view('admin.accounts.sub-accounts', [
            'page_title' => 'Sub Accounts'
        ]);
    }

    public function accountsBalances()
    {
        return view('admin.accounts.balances', [
            'page_title' => 'Balances & Credit'
        ]);
    }

    public function accountsDetails($accountId)
    {
        $account = Account::findOrFail($accountId);
        $owner = User::where('tenant_id', $account->id)->where('role', 'owner')->first();
        $flags = DB::table('account_flags')->where('account_id', $account->id)->first();
        $settings = DB::table('account_settings')->where('account_id', $account->id)->first();

        DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$accountId]);

        $customerPrices = DB::table('customer_prices')
            ->where('account_id', $accountId)
            ->where('active', true)
            ->orderBy('product_type')
            ->orderBy('country_iso')
            ->get();

        $productTier = $account->product_tier ?? 'starter';
        $tierPrices = DB::table('product_tier_prices')
            ->where('product_tier', $productTier)
            ->where('active', true)
            ->orderBy('product_type')
            ->orderBy('country_iso')
            ->get();

        return view('admin.accounts.details', [
            'page_title' => 'Account Details',
            'account_id' => $accountId,
            'account' => $account,
            'owner' => $owner,
            'flags' => $flags,
            'settings' => $settings,
            'customerPrices' => $customerPrices,
            'tierPrices' => $tierPrices,
            'productTier' => $productTier,
        ]);
    }

    public function accountsBilling($accountId)
    {
        return view('admin.accounts.billing', [
            'page_title' => 'Account Billing',
            'account_id' => $accountId
        ]);
    }

    public function reportingMessageLog()
    {
        return view('admin.reporting.message-log', [
            'page_title' => 'Message Log (Global)'
        ]);
    }

    public function reportingClient()
    {
        return view('admin.reporting.client', [
            'page_title' => 'Client Reporting'
        ]);
    }

    public function reportingSupplier()
    {
        return view('admin.reporting.supplier', [
            'page_title' => 'Supplier Reporting'
        ]);
    }

    public function reportingFinance()
    {
        return view('admin.reporting.finance', [
            'page_title' => 'Finance Reports'
        ]);
    }

    public function campaignsActive()
    {
        return view('admin.campaigns.active', [
            'page_title' => 'Active / Scheduled Campaigns'
        ]);
    }

    public function campaignsApprovals()
    {
        return view('admin.campaigns.approvals', [
            'page_title' => 'Approvals Queue'
        ]);
    }

    public function campaignsBlocked()
    {
        return view('admin.campaigns.blocked', [
            'page_title' => 'Blocked / Failed Campaigns'
        ]);
    }

    public function assetsSenderIds()
    {
        return view('admin.assets.sender-ids', [
            'page_title' => 'Sender ID Approvals'
        ]);
    }

    public function assetsSenderIdDetail($id)
    {
        return view('admin.assets.sender-id-detail', [
            'page_title' => 'SenderID Approval Detail',
            'sender_id' => $id
        ]);
    }

    public function assetsRcsAgents()
    {
        return view('admin.assets.rcs-agents', [
            'page_title' => 'RCS Agent Registration'
        ]);
    }

    public function assetsRcsAgentDetail($id)
    {
        return view('admin.assets.rcs-agent-detail', [
            'page_title' => 'RCS Agent Approval Detail',
            'agent_id' => $id
        ]);
    }

    public function assetsTemplates()
    {
        return view('admin.assets.templates', [
            'page_title' => 'Templates'
        ]);
    }

    public function assetsCampaigns()
    {
        return view('admin.assets.campaigns', [
            'page_title' => 'Campaign History'
        ]);
    }

    public function assetsNumbers()
    {
        return view('admin.assets.numbers', [
            'page_title' => 'Numbers'
        ]);
    }

    public function assetsNumberConfigure($id)
    {
        return view('admin.assets.number-configure', [
            'page_title' => 'Configure Number',
            'number_id' => $id
        ]);
    }

    public function assetsEmailToSms()
    {
        return view('admin.assets.email-to-sms', [
            'page_title' => 'Email-to-SMS'
        ]);
    }

    public function assetsEmailToSmsStandardEdit($id)
    {
        return view('admin.assets.email-to-sms-standard-edit', [
            'page_title' => 'Edit Standard Email-to-SMS',
            'id' => $id,
            'isAdmin' => true
        ]);
    }

    public function assetsEmailToSmsContactListEdit($id)
    {
        return view('admin.assets.email-to-sms-contact-list-edit', [
            'page_title' => 'Edit Contact List Email-to-SMS',
            'id' => $id,
            'isAdmin' => true
        ]);
    }

    public function apiConnections()
    {
        return view('admin.api.connections', [
            'page_title' => 'API Connections'
        ]);
    }

    public function apiConnectionCreate()
    {
        $accounts = \App\Models\Account::select('id', 'company_name', 'trading_name', 'account_number')
            ->where('status', 'active')
            ->orderBy('company_name')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'name' => $a->trading_name ?: $a->company_name,
                'account_number' => $a->account_number,
            ]);

        $subAccounts = \App\Models\SubAccount::select('id', 'name', 'account_id')
            ->orderBy('name')
            ->get()
            ->groupBy('account_id')
            ->map(fn($subs) => $subs->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values());

        return view('admin.api.connections-wizard', [
            'page_title' => 'Create API Connection',
            'accounts' => $accounts,
            'subAccountsByAccount' => $subAccounts,
        ]);
    }

    public function apiCallbacks()
    {
        return view('admin.api.callbacks', [
            'page_title' => 'Delivery Callbacks'
        ]);
    }

    public function apiHealth()
    {
        return view('admin.api.health', [
            'page_title' => 'Integration Health'
        ]);
    }

    public function billingInvoices()
    {
        return view('admin.billing.invoices', [
            'page_title' => 'Invoices (All Clients)'
        ]);
    }

    public function billingInvoicesApi(Request $request)
    {
        $query = DB::table('invoices')
            ->join('accounts', 'invoices.account_id', '=', 'accounts.id')
            ->select(
                'invoices.*',
                'accounts.company_name as account_name'
            )
            ->orderBy('invoices.issued_date', 'desc');

        if ($status = $request->input('status')) {
            if ($status === 'issued') {
                $query->where('invoices.status', 'sent');
            } else {
                $query->where('invoices.status', $status);
            }
        }

        if ($search = $request->input('search')) {
            $query->where('invoices.invoice_number', 'ilike', "%{$search}%");
        }

        if ($accountId = $request->input('accountId')) {
            $query->where('invoices.account_id', $accountId);
        }

        if ($year = $request->input('billingYear')) {
            $query->whereRaw('EXTRACT(YEAR FROM invoices.billing_period_start) = ?', [$year]);
        }

        if ($month = $request->input('billingMonth')) {
            $query->whereRaw('EXTRACT(MONTH FROM invoices.billing_period_start) = ?', [$month]);
        }

        $invoices = $query->get()->map(function ($inv) {
            $status = $inv->status;
            if ($status === 'sent') {
                $status = 'issued';
            }
            if (in_array($inv->status, ['sent', 'overdue']) && $inv->due_date && now()->isAfter($inv->due_date)) {
                $status = 'overdue';
            }

            return [
                'id' => $inv->id,
                'invoiceNumber' => $inv->invoice_number,
                'accountId' => $inv->account_id,
                'accountName' => $inv->account_name ?? 'Unknown',
                'billingPeriodStart' => $inv->billing_period_start,
                'billingPeriodEnd' => $inv->billing_period_end,
                'issueDate' => $inv->issued_date,
                'dueDate' => $inv->due_date,
                'status' => $status,
                'subtotal' => (float) $inv->subtotal,
                'vat' => (float) $inv->tax_amount,
                'total' => (float) $inv->total,
                'balanceDue' => (float) $inv->amount_due,
                'currency' => $inv->currency ?? 'GBP',
                'xeroInvoiceId' => $inv->xero_invoice_id,
            ];
        });

        $accounts = DB::table('accounts')
            ->select('id', 'company_name')
            ->orderBy('company_name')
            ->get()
            ->map(function ($a) {
                return ['id' => $a->id, 'name' => $a->company_name];
            });

        return response()->json([
            'success' => true,
            'invoices' => $invoices,
            'accounts' => $accounts,
        ]);
    }

    public function accountBillingApi($accountId)
    {
        DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$accountId]);

        $account = Account::withoutGlobalScopes()->find($accountId);
        if (!$account) {
            return response()->json(['success' => false, 'error' => 'Account not found'], 404);
        }

        $balance = \App\Models\Billing\AccountBalance::where('account_id', $accountId)->first();

        $billingMode = $account->billing_type === 'postpay' ? 'postpaid' : 'prepaid';
        $creditLimit = (float) ($account->credit_limit ?? 0);
        $currentBalance = $balance ? (float) $balance->balance : 0;
        $reserved = $balance ? (float) $balance->reserved : 0;
        $totalOutstanding = $balance ? (float) $balance->total_outstanding : 0;

        if ($billingMode === 'prepaid') {
            $availableCredit = max(0, $currentBalance - $reserved) + $creditLimit;
        } else {
            $availableCredit = $creditLimit - $totalOutstanding + $currentBalance - $reserved;
        }

        $paymentTermsDays = $account->payment_terms_days ?? 30;
        $paymentTermsLabel = $paymentTermsDays === 0 ? 'Immediate' : "Net {$paymentTermsDays}";

        return response()->json([
            'success' => true,
            'accountId' => $account->id,
            'name' => $account->company_name ?? 'Unknown',
            'status' => $account->status ?? 'active',
            'hubspotId' => $account->hubspot_company_id,
            'billingMode' => $billingMode,
            'currentBalance' => $currentBalance,
            'creditLimit' => $creditLimit,
            'availableCredit' => $availableCredit,
            'paymentTerms' => $paymentTermsLabel,
            'currency' => $account->currency ?? 'GBP',
            'vatRegistered' => (bool) $account->vat_registered,
            'vatRate' => 20,
            'reverseCharge' => (bool) $account->vat_reverse_charges,
            'vatCountry' => $account->tax_country ?? 'GB',
            'lastUpdated' => ($balance ? $balance->updated_at : $account->updated_at)?->toISOString(),
        ]);
    }

    public function updateAccountBillingMode(Request $request, $accountId)
    {
        $request->validate([
            'billingMode' => 'required|in:prepaid,postpaid',
        ]);

        $account = Account::withoutGlobalScopes()->find($accountId);
        if (!$account) {
            return response()->json(['success' => false, 'error' => 'Account not found'], 404);
        }

        $previousMode = $account->billing_type;
        $newDbMode = $request->input('billingMode') === 'postpaid' ? 'postpay' : 'prepay';

        DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$accountId]);
        $account->billing_type = $newDbMode;
        $account->save();

        $balance = \App\Models\Billing\AccountBalance::where('account_id', $accountId)->first();
        if ($balance) {
            $balance->recalculateEffectiveAvailable();
            $balance->save();
        }

        Log::info('Admin updated billing mode', [
            'account_id' => $accountId,
            'previous_mode' => $previousMode,
            'new_mode' => $newDbMode,
            'admin_user' => session('admin_user_email'),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'accountId' => $accountId,
                'billingMode' => $request->input('billingMode'),
                'previousMode' => $previousMode === 'postpay' ? 'postpaid' : 'prepaid',
                'hubspotSynced' => false,
                'syncTimestamp' => now()->toISOString(),
            ],
        ]);
    }

    public function updateAccountCreditLimit(Request $request, $accountId)
    {
        $request->validate([
            'creditLimit' => 'required|numeric|min:0|max:1000000',
        ]);

        $account = Account::withoutGlobalScopes()->find($accountId);
        if (!$account) {
            return response()->json(['success' => false, 'error' => 'Account not found'], 404);
        }

        $previousLimit = (float) ($account->credit_limit ?? 0);
        $newLimit = (float) $request->input('creditLimit');

        DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$accountId]);
        $account->credit_limit = $newLimit;
        $account->save();

        $balance = \App\Models\Billing\AccountBalance::where('account_id', $accountId)->first();
        if ($balance) {
            $balance->credit_limit = $newLimit;
            $balance->recalculateEffectiveAvailable();
            $balance->save();
        }

        Log::info('Admin updated credit limit', [
            'account_id' => $accountId,
            'previous_limit' => $previousLimit,
            'new_limit' => $newLimit,
            'admin_user' => session('admin_user_email'),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'accountId' => $accountId,
                'creditLimit' => $newLimit,
                'previousLimit' => $previousLimit,
                'hubspotSynced' => false,
                'syncTimestamp' => now()->toISOString(),
            ],
        ]);
    }

    public function accountPricingApi($accountId)
    {
        DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$accountId]);

        $account = Account::withoutGlobalScopes()->find($accountId);
        if (!$account) {
            return response()->json(['success' => false, 'error' => 'Account not found'], 404);
        }

        $services = \App\Models\Billing\ServiceCatalogue::active()->ordered()->get();
        $productTier = $account->product_tier ?? 'starter';

        $tierLookup = $productTier === 'bespoke' ? 'enterprise' : $productTier;
        $tierPrices = DB::table('product_tier_prices')
            ->where('product_tier', $tierLookup)
            ->where('active', true)
            ->whereNull('country_iso')
            ->get()
            ->keyBy('product_type');

        $starterPrices = collect();
        $enterprisePrices = collect();
        if ($productTier === 'bespoke') {
            $starterPrices = DB::table('product_tier_prices')
                ->where('product_tier', 'starter')
                ->where('active', true)
                ->whereNull('country_iso')
                ->get()
                ->keyBy('product_type');
            $enterprisePrices = $tierPrices;
        }

        $customerPrices = DB::table('customer_prices')
            ->where('account_id', $accountId)
            ->where('active', true)
            ->whereNull('valid_to')
            ->whereNull('country_iso')
            ->get()
            ->keyBy('product_type');

        $countryPrices = DB::table('customer_prices')
            ->where('account_id', $accountId)
            ->where('active', true)
            ->whereNull('valid_to')
            ->whereNotNull('country_iso')
            ->orderBy('country_iso')
            ->get()
            ->groupBy('product_type');

        $messagingTypes = ['sms', 'rcs_basic', 'rcs_single'];

        $items = [];
        foreach ($services as $service) {
            $slug = $service->slug;
            $tierPrice = $tierPrices->get($slug);
            $bespokePrice = $customerPrices->get($slug);
            $isMessaging = in_array($slug, $messagingTypes);
            $isInternational = $slug === 'sms_international';

            $item = [
                'slug' => $slug,
                'display_name' => $service->display_name,
                'display_format' => $service->display_format,
                'decimal_places' => $service->decimal_places,
                'unit_label' => $service->unit_label,
                'tier_price' => $tierPrice ? (float) $tierPrice->unit_price : null,
                'tier_price_formatted' => $tierPrice ? $service->formatPrice($tierPrice->unit_price) : 'N/A',
                'bespoke_price' => $bespokePrice ? (float) $bespokePrice->unit_price : null,
                'bespoke_price_formatted' => $bespokePrice ? $service->formatPrice($bespokePrice->unit_price) : null,
                'has_bespoke' => $bespokePrice !== null,
                'supports_billing_type' => $isMessaging,
                'billing_type' => $bespokePrice ? ($bespokePrice->billing_type ?? 'per_submitted') : ($tierPrice ? ($tierPrice->billing_type ?? 'per_submitted') : 'per_submitted'),
                'supports_country_pricing' => $isInternational,
                'country_prices' => [],
            ];

            if ($productTier === 'bespoke') {
                $sp = $starterPrices->get($slug);
                $ep = $enterprisePrices->get($slug);
                $item['starter_price'] = $sp ? (float) $sp->unit_price : null;
                $item['starter_price_formatted'] = $sp ? $service->formatPrice($sp->unit_price) : 'N/A';
                $item['enterprise_price'] = $ep ? (float) $ep->unit_price : null;
                $item['enterprise_price_formatted'] = $ep ? $service->formatPrice($ep->unit_price) : 'N/A';
            }

            if ($isInternational && $countryPrices->has($slug)) {
                foreach ($countryPrices->get($slug) as $cp) {
                    $item['country_prices'][] = [
                        'country_iso' => $cp->country_iso,
                        'unit_price' => (float) $cp->unit_price,
                        'billing_type' => $cp->billing_type ?? 'per_submitted',
                    ];
                }
            }

            $items[] = $item;
        }

        $mccCountries = DB::table('mcc_mnc_master')
            ->select('country_iso', 'country_name')
            ->where('active', true)
            ->whereNotNull('country_iso')
            ->where('country_iso', '!=', 'GB')
            ->groupBy('country_iso', 'country_name')
            ->orderBy('country_name')
            ->get()
            ->map(fn($c) => ['iso' => $c->country_iso, 'name' => $c->country_name])
            ->values()
            ->toArray();

        return response()->json([
            'success' => true,
            'product_tier' => $productTier,
            'account_name' => $account->company_name ?? 'Unknown',
            'items' => $items,
            'countries' => $mccCountries,
        ]);
    }

    public function updateAccountPricing(Request $request, $accountId)
    {
        $request->validate([
            'product_tier' => 'required|string|in:starter,enterprise,bespoke',
            'prices' => 'nullable|array',
            'prices.*.slug' => 'required|string',
            'prices.*.unit_price' => 'required|numeric|min:0',
            'prices.*.billing_type' => 'nullable|in:per_submitted,per_delivered',
            'prices.*.country_iso' => 'nullable|string|size:2',
            'change_reason' => 'nullable|string|max:500',
        ]);

        $account = Account::withoutGlobalScopes()->find($accountId);
        if (!$account) {
            return response()->json(['success' => false, 'error' => 'Account not found'], 404);
        }

        DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$accountId]);

        $adminEmail = session('admin_auth.email', 'admin');
        $adminId = session('admin_auth.admin_id');
        $changeReason = $request->input('change_reason', 'Admin pricing override');
        $newTier = $request->input('product_tier');
        $previousTier = $account->product_tier;
        $updatedCount = 0;

        DB::beginTransaction();
        try {
            if ($newTier !== 'bespoke') {
                DB::table('customer_prices')
                    ->where('account_id', $accountId)
                    ->where('active', true)
                    ->update(['active' => false, 'valid_to' => now()->toDateString()]);

                $account->product_tier = $newTier;
                $account->save();

                DB::commit();

                Log::info('Admin changed account tier', [
                    'account_id' => $accountId,
                    'previous_tier' => $previousTier,
                    'new_tier' => $newTier,
                    'admin_user' => $adminEmail,
                    'reason' => $changeReason,
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'accountId' => $accountId,
                        'pricesUpdated' => 0,
                        'productTier' => $newTier,
                        'previousTier' => $previousTier,
                        'bespokeDeactivated' => true,
                    ],
                ]);
            }

            $prices = $request->input('prices', []);
            foreach ($prices as $priceData) {
                $slug = $priceData['slug'];
                $newUnitPrice = $priceData['unit_price'];
                $billingType = $priceData['billing_type'] ?? 'per_submitted';
                $countryIso = $priceData['country_iso'] ?? null;

                $query = DB::table('customer_prices')
                    ->where('account_id', $accountId)
                    ->where('product_type', $slug)
                    ->where('active', true);

                if ($countryIso) {
                    $query->where('country_iso', $countryIso);
                } else {
                    $query->whereNull('country_iso');
                }

                $existing = $query->first();

                if ($existing) {
                    DB::table('customer_prices')
                        ->where('id', $existing->id)
                        ->update(['active' => false, 'valid_to' => now()->toDateString()]);
                    $previousVersionId = $existing->id;
                    $version = ($existing->version ?? 1) + 1;
                } else {
                    $previousVersionId = null;
                    $version = 1;
                }

                DB::table('customer_prices')->insert([
                    'id' => \Illuminate\Support\Str::uuid()->toString(),
                    'account_id' => $accountId,
                    'product_type' => $slug,
                    'country_iso' => $countryIso,
                    'unit_price' => $newUnitPrice,
                    'billing_type' => $billingType,
                    'currency' => $account->currency ?? 'GBP',
                    'source' => 'admin_override',
                    'set_by' => $adminId,
                    'set_at' => now(),
                    'valid_from' => now()->toDateString(),
                    'valid_to' => null,
                    'active' => true,
                    'version' => $version,
                    'previous_version_id' => $previousVersionId,
                    'change_reason' => $changeReason,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $updatedCount++;
            }

            if ($previousTier !== 'bespoke') {
                $account->product_tier = 'bespoke';
                $account->save();
            }

            DB::commit();

            Log::info('Admin updated account pricing', [
                'account_id' => $accountId,
                'previous_tier' => $previousTier,
                'new_tier' => 'bespoke',
                'prices_updated' => $updatedCount,
                'admin_user' => $adminEmail,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'accountId' => $accountId,
                    'pricesUpdated' => $updatedCount,
                    'productTier' => 'bespoke',
                    'previousTier' => $previousTier,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update account pricing', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'error' => 'Failed to update pricing'], 500);
        }
    }

    public function billingPayments()
    {
        return view('admin.billing.payments', [
            'page_title' => 'Payments'
        ]);
    }

    public function billingCredits()
    {
        return view('admin.billing.credits', [
            'page_title' => 'Credit Adjustments'
        ]);
    }

    public function securityAuditLogs()
    {
        return view('admin.security.audit-logs', [
            'page_title' => 'Audit Logs'
        ]);
    }

    public function securityCountryControls()
    {
        return view('admin.security.country-controls', [
            'page_title' => 'Country Controls'
        ]);
    }

    public function securityComplianceControls()
    {
        $enforcementData = [
            'senderidRules' => \App\Models\SenderidRule::byPriority()->get()->toArray(),
            'contentRules' => \App\Models\ContentRule::byPriority()->get()->toArray(),
            'urlRules' => \App\Models\UrlRule::byPriority()->get()->toArray(),
            'normalisationChars' => \App\Models\NormalisationCharacter::orderBy('base_character')->get()->toArray(),
            'domainAgeSettings' => \App\Models\SystemSetting::where('setting_group', 'domain_age')->get()->toArray(),
        ];

        return view('admin.security.security-compliance-controls', [
            'page_title' => 'Security & Compliance Controls',
            'enforcementData' => $enforcementData,
        ]);
    }

    public function securityAntiSpam()
    {
        return view('admin.security.anti-spam', [
            'page_title' => 'Anti-Spam Rules'
        ]);
    }

    public function securityIpAllowlists()
    {
        return view('admin.security.ip-allowlists', [
            'page_title' => 'IP Allow Lists'
        ]);
    }

    public function securityAdminUsers()
    {
        $allowedRoles = ['super_admin', 'admin'];
        $currentRole = session('admin_auth.role', 'super_admin');

        if (!in_array($currentRole, $allowedRoles)) {
            abort(403, 'Access denied. This module is restricted to Super Admin and Admin roles.');
        }

        try {
            $dbUsers = \App\Models\AdminUser::orderBy('created_at', 'desc')->get();

            $adminUsers = $dbUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->full_name,
                    'email' => $user->email,
                    'role' => $user->getRoleDisplayName(),
                    'department' => $user->department ?? 'N/A',
                    'status' => ucfirst($user->status),
                    'mfa_status' => $user->mfa_enabled ? 'Enrolled' : 'Not Enrolled',
                    'mfa_method' => $user->mfa_method ? ucfirst($user->mfa_method) : null,
                    'last_login' => $user->last_login_at?->format('Y-m-d H:i:s'),
                    'last_activity' => $user->last_login_at?->format('Y-m-d H:i:s'),
                    'failed_logins_24h' => $user->failed_login_attempts,
                    'created_at' => $user->created_at?->format('Y-m-d'),
                    'created_by' => $user->created_by ?? 'System',
                    'active_sessions' => 0,
                    'invite_sent_at' => $user->invite_sent_at?->format('Y-m-d H:i:s'),
                ];
            })->toArray();
        } catch (\Exception $e) {
            \Log::warning('[AdminUsers] DB query failed, returning empty list', ['error' => $e->getMessage()]);
            $adminUsers = [];
        }

        return view('admin.security.admin-users', [
            'page_title' => 'Admin Users',
            'adminUsers' => $adminUsers
        ]);
    }

    public function systemPricing()
    {
        return view('admin.system.pricing', [
            'page_title' => 'Supplier Pricing'
        ]);
    }

    public function systemRouting(Request $request)
    {
        $tab = $request->get('tab', 'uk');

        $viewMap = [
            'uk' => 'admin.routing-rules.uk-routes',
            'international' => 'admin.routing-rules.international-routes',
            'overrides' => 'admin.routing-rules.customer-overrides',
        ];

        $view = $viewMap[$tab] ?? $viewMap['uk'];
        $data = ['page_title' => 'Routing Rules'];

        $gateways = Gateway::active()->with('supplier')->get();
        $productTypes = RateCard::active()->distinct()->pluck('product_type')->filter()->values();

        if ($tab === 'uk' || !isset($viewMap[$tab])) {
            $data = array_merge($data, $this->getUkRoutingData($gateways, $productTypes));
        } elseif ($tab === 'international') {
            $data = array_merge($data, $this->getInternationalRoutingData($gateways, $productTypes));
        } elseif ($tab === 'overrides') {
            $data = array_merge($data, $this->getOverridesData($gateways));
        }

        return view($view, $data);
    }

    private function getUkRoutingData($gateways, $productTypes)
    {
        $ukMccMnc = MccMnc::where('country_iso', 'GB')
            ->where('active', true)
            ->orderBy('network_name')
            ->get();

        $ukRateCards = RateCard::active()
            ->whereIn('mcc', ['234', '235'])
            ->where('country_iso', 'GB')
            ->with(['gateway.supplier'])
            ->get();

        $ratesByNetwork = $ukRateCards->groupBy(function ($rc) {
            return $rc->mcc . '-' . $rc->mnc;
        });

        $ukRoutingRules = RoutingRule::where('country_iso', 'GB')
            ->whereNull('deleted_at')
            ->with('gatewayWeights')
            ->get()
            ->keyBy(function ($rule) {
                return $rule->mcc . '-' . $rule->mnc;
            });

        $ukNetworks = $ukMccMnc->map(function ($network) use ($ratesByNetwork, $ukRoutingRules, $gateways) {
            $key = $network->mcc . '-' . $network->mnc;
            $rates = $ratesByNetwork->get($key, collect());
            $routingRule = $ukRoutingRules->get($key);

            $networkGateways = $rates->groupBy('gateway_id')->map(function ($gwRates) use ($routingRule) {
                $rate = $gwRates->sortBy('gbp_rate')->first();
                $gateway = $rate->gateway;
                $supplier = $gateway ? $gateway->supplier : null;

                $weight = null;
                $isPrimary = false;
                $gwStatus = 'active';

                if ($routingRule) {
                    $weightEntry = $routingRule->gatewayWeights
                        ->where('gateway_id', $rate->gateway_id)
                        ->first();
                    if ($weightEntry) {
                        $weight = $weightEntry->weight;
                        $isPrimary = $weightEntry->priority_order === 1 && !$weightEntry->is_fallback;
                        $gwStatus = $weightEntry->status ?? 'active';
                    }
                }

                return [
                    'name' => $gateway ? $gateway->name : 'Unknown',
                    'supplier' => $supplier ? $supplier->name : 'Unknown',
                    'code' => $gateway ? $gateway->gateway_code : '',
                    'weight' => $weight,
                    'primary' => $isPrimary,
                    'status' => $gwStatus === 'active' ? 'online' : $gwStatus,
                    'rate' => number_format($rate->gbp_rate, 4),
                    'billing' => ucfirst($rate->billing_method ?? 'delivered'),
                ];
            })->values();

            if ($networkGateways->isNotEmpty() && !$networkGateways->contains('primary', true)) {
                $networkGateways = $networkGateways->map(function ($gw, $index) {
                    if ($index === 0) {
                        $gw['primary'] = true;
                    }
                    return $gw;
                });
            }

            $primaryGw = $networkGateways->firstWhere('primary', true);
            $cheapestRate = $rates->min('gbp_rate');

            return [
                'id' => $network->id,
                'network' => $network->network_name,
                'prefix' => $network->country_prefix ? '+' . $network->country_prefix : $network->mcc . '/' . $network->mnc,
                'mcc' => $network->mcc,
                'mnc' => $network->mnc,
                'gateway_count' => $networkGateways->count(),
                'primary_gw' => $primaryGw ? $primaryGw['name'] : '—',
                'billing' => $primaryGw ? $primaryGw['billing'] : '—',
                'rate' => $cheapestRate !== null ? number_format($cheapestRate, 4) : '—',
                'status' => $routingRule ? $routingRule->status : 'active',
                'gateways' => $networkGateways->toArray(),
            ];
        });

        return [
            'ukNetworks' => $ukNetworks,
            'gateways' => $gateways,
            'productTypes' => $productTypes,
        ];
    }

    private function getInternationalRoutingData($gateways, $productTypes)
    {
        $intlRates = RateCard::active()
            ->where('country_iso', '!=', 'GB')
            ->with(['gateway.supplier'])
            ->get();

        $byCountry = $intlRates->groupBy('country_iso');

        $intlRoutingRules = RoutingRule::where('country_iso', '!=', 'GB')
            ->whereNotNull('country_iso')
            ->whereNull('deleted_at')
            ->with('gatewayWeights')
            ->get()
            ->keyBy('country_iso');

        $countries = $byCountry->map(function ($rates, $iso) use ($intlRoutingRules) {
            $countryName = $rates->first()->country_name;
            $routingRule = $intlRoutingRules->get($iso);

            $countryGateways = $rates->groupBy('gateway_id')->map(function ($gwRates) use ($routingRule) {
                $rate = $gwRates->sortBy('gbp_rate')->first();
                $gateway = $rate->gateway;
                $supplier = $gateway ? $gateway->supplier : null;

                $weight = null;
                $isPrimary = false;
                $gwStatus = 'active';

                if ($routingRule) {
                    $weightEntry = $routingRule->gatewayWeights
                        ->where('gateway_id', $rate->gateway_id)
                        ->first();
                    if ($weightEntry) {
                        $weight = $weightEntry->weight;
                        $isPrimary = $weightEntry->priority_order === 1 && !$weightEntry->is_fallback;
                        $gwStatus = $weightEntry->status ?? 'active';
                    }
                }

                return [
                    'name' => $gateway ? $gateway->name : 'Unknown',
                    'supplier' => $supplier ? $supplier->name : 'Unknown',
                    'code' => $gateway ? $gateway->gateway_code : '',
                    'weight' => $weight,
                    'primary' => $isPrimary,
                    'status' => $gwStatus === 'active' ? 'online' : $gwStatus,
                    'rate' => number_format($rate->gbp_rate, 4),
                    'billing' => ucfirst($rate->billing_method ?? 'delivered'),
                ];
            })->values();

            if ($countryGateways->isNotEmpty() && !$countryGateways->contains('primary', true)) {
                $countryGateways = $countryGateways->map(function ($gw, $index) {
                    if ($index === 0) {
                        $gw['primary'] = true;
                    }
                    return $gw;
                });
            }

            $primaryGw = $countryGateways->firstWhere('primary', true);
            $cheapestRate = $rates->min('gbp_rate');

            return [
                'country' => $countryName,
                'iso' => $iso,
                'letter' => strtoupper(substr($countryName, 0, 1)),
                'gateway_count' => $countryGateways->count(),
                'primary_gw' => $primaryGw ? $primaryGw['name'] : '—',
                'billing' => $primaryGw ? $primaryGw['billing'] : '—',
                'rate' => $cheapestRate !== null ? number_format($cheapestRate, 4) : '—',
                'status' => $routingRule ? $routingRule->status : 'active',
                'gateways' => $countryGateways->toArray(),
            ];
        })->sortBy('country')->values();

        $activeLetters = $countries->pluck('letter')->unique()->sort()->values();

        return [
            'countries' => $countries,
            'activeLetters' => $activeLetters,
            'gateways' => $gateways,
            'productTypes' => $productTypes,
        ];
    }

    private function getOverridesData($gateways)
    {
        $overrides = RoutingCustomerOverride::whereNull('deleted_at')
            ->with(['forcedGateway.supplier', 'blockedGateway.supplier', 'routingRule'])
            ->orderByDesc('created_at')
            ->get();

        $ukNetworks = MccMnc::where('country_iso', 'GB')
            ->where('active', true)
            ->orderBy('network_name')
            ->get();

        $countries = RateCard::active()
            ->where('country_iso', '!=', 'GB')
            ->select('country_iso', 'country_name')
            ->distinct()
            ->orderBy('country_name')
            ->get();

        return [
            'overrides' => $overrides,
            'gateways' => $gateways,
            'ukNetworks' => $ukNetworks,
            'countries' => $countries,
        ];
    }

    public function systemFlags()
    {
        return view('admin.system.flags', [
            'page_title' => 'Platform Flags'
        ]);
    }

    public function managementTemplates()
    {
        return view('admin.management.templates', [
            'page_title' => 'Global Templates Library'
        ]);
    }

    public function managementTemplateEdit($accountId, $templateId)
    {
        $sender_ids = [
            ['id' => 1, 'name' => 'QuickSMS', 'type' => 'alphanumeric'],
            ['id' => 2, 'name' => 'ALERTS', 'type' => 'alphanumeric'],
            ['id' => 3, 'name' => '+447700900100', 'type' => 'numeric'],
        ];

        $rcs_agents = [
            ['id' => 1, 'name' => 'QuickSMS Brand', 'logo' => '/images/rcs-agents/quicksms-brand.svg', 'tagline' => 'Fast messaging for everyone', 'brand_color' => '#886CC0', 'status' => 'approved'],
            ['id' => 2, 'name' => 'Promotions Agent', 'logo' => '/images/rcs-agents/promotions-agent.svg', 'tagline' => 'Exclusive deals & offers', 'brand_color' => '#E91E63', 'status' => 'approved'],
        ];

        // TODO: Replace with API call - adminTemplatesService.getTemplate(accountId, templateId)
        $template = $this->getAdminMockTemplate($accountId, $templateId);
        $accountName = $this->getAccountName($accountId);

        return view('shared.template-wizard', [
            'page_title' => 'Edit Template',
            'mode' => 'edit',
            'isAdminMode' => true,
            'isEditMode' => true,
            'showRichRcs' => false,
            'accountId' => $accountId,
            'accountName' => $accountName,
            'templateId' => $templateId,
            'template' => $template,
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents
        ]);
    }

    private function getAdminMockTemplate($accountId, $templateId)
    {
        // TODO: Replace with API call - adminTemplatesService.getTemplate(accountId, templateId)
        $mockTemplates = [
            'TPL-12345678' => [
                'id' => 1,
                'name' => 'Winter Sale 2026',
                'templateId' => 'TPL-12345678',
                'trigger' => 'portal',
                'channel' => 'sms',
                'content' => 'Hi {FirstName}! Our Winter Sale is here. Get 40% off all items. Shop now: {Link}',
                'senderId' => '1',
                'rcsAgent' => '',
                'trackableLink' => true,
                'optOut' => true
            ],
            'TPL-23456789' => [
                'id' => 2,
                'name' => 'Appointment Confirmation',
                'templateId' => 'TPL-23456789',
                'trigger' => 'api',
                'channel' => 'sms',
                'content' => 'Hi {FirstName}, your appointment is confirmed for {AppointmentDate} at {AppointmentTime}.',
                'senderId' => '2',
                'rcsAgent' => '',
                'trackableLink' => false,
                'optOut' => false
            ],
            'TPL-34567890' => [
                'id' => 3,
                'name' => 'Delivery Update',
                'templateId' => 'TPL-34567890',
                'trigger' => 'api',
                'channel' => 'basic_rcs',
                'content' => 'Your order #{OrderId} is on its way! Track here: {TrackingLink}',
                'senderId' => '1',
                'rcsAgent' => '1',
                'trackableLink' => true,
                'optOut' => false
            ]
        ];

        return $mockTemplates[$templateId] ?? [
            'id' => 999,
            'name' => 'Template ' . $templateId,
            'templateId' => $templateId,
            'trigger' => 'api',
            'channel' => 'sms',
            'content' => 'Template content for ' . $templateId,
            'senderId' => '1',
            'rcsAgent' => '',
            'trackableLink' => false,
            'optOut' => false
        ];
    }

    private function getAccountName($accountId)
    {
        // TODO: Replace with API call
        $accounts = [
            'ACC-001' => 'Acme Corporation',
            'ACC-002' => 'TechStart Ltd',
            'ACC-003' => 'GlobalRetail Inc'
        ];

        return $accounts[$accountId] ?? 'Account ' . $accountId;
    }

    public function adminTemplateEditStep1($accountId, $templateId)
    {
        // TODO: Replace with API call - adminTemplatesService.getTemplate(accountId, templateId)
        $template = $this->getAdminMockTemplate($accountId, $templateId);
        $accountName = $this->getAccountName($accountId);

        return view('quicksms.management.templates.create-step1', [
            'page_title' => 'Edit Template - Metadata',
            'isEditMode' => true,
            'isAdminMode' => true,
            'templateId' => $templateId,
            'accountId' => $accountId,
            'account' => ['id' => $accountId, 'name' => $accountName],
            'template' => $template
        ]);
    }

    public function adminTemplateEditStep2($accountId, $templateId)
    {
        $sender_ids = [
            ['id' => 1, 'name' => 'QuickSMS', 'type' => 'alphanumeric'],
            ['id' => 2, 'name' => 'ALERTS', 'type' => 'alphanumeric'],
            ['id' => 3, 'name' => '+447700900100', 'type' => 'numeric'],
        ];

        $rcs_agents = [
            ['id' => 1, 'name' => 'QuickSMS Brand', 'logo' => '/images/rcs-agents/quicksms-brand.svg', 'tagline' => 'Fast messaging for everyone', 'brand_color' => '#886CC0', 'status' => 'approved'],
            ['id' => 2, 'name' => 'Promotions Agent', 'logo' => '/images/rcs-agents/promotions-agent.svg', 'tagline' => 'Exclusive deals & offers', 'brand_color' => '#E91E63', 'status' => 'approved'],
        ];

        // TODO: Replace with API call - optOutService.getLists(accountId)
        $opt_out_lists = [
            ['id' => 1, 'name' => 'Marketing Opt-outs', 'count' => 1250],
            ['id' => 2, 'name' => 'Transactional Opt-outs', 'count' => 89],
        ];

        // TODO: Replace with API call - numbersService.getVirtualNumbers(accountId)
        $virtual_numbers = [
            ['id' => 1, 'number' => '+447700900200', 'label' => 'Customer Support'],
            ['id' => 2, 'number' => '+447700900201', 'label' => 'Sales'],
        ];

        // TODO: Replace with API call - optOutService.getDomains(accountId)
        $optout_domains = [
            ['id' => 1, 'domain' => 'optout.quicksms.co.uk', 'is_default' => true],
            ['id' => 2, 'domain' => 'stop.quicksms.co.uk', 'is_default' => false],
        ];

        // TODO: Replace with API call - adminTemplatesService.getTemplate(accountId, templateId)
        $template = $this->getAdminMockTemplate($accountId, $templateId);
        $accountName = $this->getAccountName($accountId);

        return view('quicksms.management.templates.create-step2', [
            'page_title' => 'Edit Template - Content',
            'isEditMode' => true,
            'isAdminMode' => true,
            'templateId' => $templateId,
            'accountId' => $accountId,
            'account' => ['id' => $accountId, 'name' => $accountName],
            'template' => $template,
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents,
            'opt_out_lists' => $opt_out_lists,
            'virtual_numbers' => $virtual_numbers,
            'optout_domains' => $optout_domains
        ]);
    }

    public function adminTemplateEditStep3($accountId, $templateId)
    {
        // TODO: Replace with API call - adminTemplatesService.getTemplate(accountId, templateId)
        $template = $this->getAdminMockTemplate($accountId, $templateId);
        $accountName = $this->getAccountName($accountId);

        return view('quicksms.management.templates.create-step3', [
            'page_title' => 'Edit Template - Settings',
            'isEditMode' => true,
            'isAdminMode' => true,
            'templateId' => $templateId,
            'accountId' => $accountId,
            'account' => ['id' => $accountId, 'name' => $accountName],
            'template' => $template
        ]);
    }

    public function adminTemplateEditReview($accountId, $templateId)
    {
        // TODO: Replace with API call - adminTemplatesService.getTemplate(accountId, templateId)
        $template = $this->getAdminMockTemplate($accountId, $templateId);
        $accountName = $this->getAccountName($accountId);

        return view('quicksms.management.templates.create-review', [
            'page_title' => 'Edit Template - Review',
            'isEditMode' => true,
            'isAdminMode' => true,
            'templateId' => $templateId,
            'accountId' => $accountId,
            'account' => ['id' => $accountId, 'name' => $accountName],
            'template' => $template
        ]);
    }
    
    /**
     * Test enforcement rules against input
     * 
     * POST /admin/enforcement/test
     * 
     * This endpoint uses the SAME shared enforcement logic as production.
     * It normalises the input and evaluates it against the selected engine's rules.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testEnforcement(Request $request)
    {
        $request->validate([
            'engine' => 'required|string|in:senderid,content,url',
            'input' => 'required|string|max:1000',
        ]);
        
        $engine = $request->input('engine');
        $input = $request->input('input');
        
        try {
            $result = $this->enforcementService->testEnforcement($engine, $input);
            
            // M12 FIX: Do not log raw user input (PII risk) — service already logs normalised form
            Log::info('[AdminController] Enforcement test executed', [
                'engine' => $engine,
                'input_length' => strlen($input),
                'result' => $result['result'],
                'admin_email' => session('admin_email', 'unknown'),
            ]);
            
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('[AdminController] Enforcement test failed', [
                'engine' => $engine,
                'input_length' => strlen($input),
                'error' => $e->getMessage(),
            ]);
            
            // M9 FIX: Do not leak exception details to client
            return response()->json([
                'error' => 'Enforcement test failed. Please try again or contact support.',
            ], 500);
        }
    }

    /**
     * Normalise input only (without rule evaluation)
     *
     * POST /admin/enforcement/normalise
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function normaliseInput(Request $request)
    {
        $request->validate([
            'input' => 'required|string|max:1000',
        ]);
        
        $input = $request->input('input');
        
        try {
            $result = $this->enforcementService->normalise($input);
            
            return response()->json([
                'normalised' => $result['normalised'],
                'mappingHits' => $result['mappingHits'],
            ]);
        } catch (\Exception $e) {
            Log::error('[AdminController] Normalisation failed', [
                'input_length' => strlen($input),
                'error' => $e->getMessage(),
            ]);
            
            // M9 FIX: Do not leak exception details to client
            return response()->json([
                'error' => 'Normalisation failed. Please try again or contact support.',
            ], 500);
        }
    }
    
    /**
     * Hot reload enforcement rules
     * 
     * POST /admin/enforcement/reload
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reloadEnforcementRules(Request $request)
    {
        try {
            $this->enforcementService->hotReloadRules();
            
            Log::info('[AdminController] Enforcement rules reloaded', [
                'admin_email' => session('admin_email', 'unknown'),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Enforcement rules reloaded successfully',
            ]);
        } catch (\Exception $e) {
            // M9 FIX: Do not leak exception details to client
            return response()->json([
                'error' => 'Failed to reload rules. Please try again or contact support.',
            ], 500);
        }
    }

    public function routingAddGateway(Request $request)
    {
        $request->validate([
            'route_id' => 'required|string',
            'gateway_code' => 'required|string',
            'weight' => 'nullable|integer|min:0|max:100',
            'set_primary' => 'nullable|boolean',
            'route_type' => 'required|string|in:uk,international',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $gateway = Gateway::where('gateway_code', $request->input('gateway_code'))->firstOrFail();

                if ($request->input('route_type') === 'uk') {
                    $mccMnc = MccMnc::findOrFail($request->input('route_id'));
                    $routingRule = RoutingRule::firstOrCreate(
                        ['mcc' => $mccMnc->mcc, 'mnc' => $mccMnc->mnc, 'country_iso' => 'GB'],
                        [
                            'name' => 'UK Route - ' . $mccMnc->network_name,
                            'rule_type' => 'network_route',
                            'selection_strategy' => 'weighted',
                            'status' => 'active',
                            'is_default' => false,
                            'priority' => 100,
                            'country_name' => 'United Kingdom',
                        ]
                    );
                } else {
                    $countryIso = $request->input('route_id');
                    $routingRule = RoutingRule::firstOrCreate(
                        ['country_iso' => $countryIso, 'mcc' => null, 'mnc' => null],
                        [
                            'name' => 'International Route - ' . $countryIso,
                            'rule_type' => 'network_route',
                            'selection_strategy' => 'weighted',
                            'status' => 'active',
                            'is_default' => false,
                            'priority' => 100,
                        ]
                    );
                }

                $routingRule->update([
                    'rule_type' => 'network_route',
                    'selection_strategy' => 'weighted',
                    'status' => 'active',
                    'is_default' => false,
                    'priority' => 100,
                ]);

                $maxOrder = RoutingGatewayWeight::where('routing_rule_id', $routingRule->id)->max('priority_order') ?? 0;
                $priorityOrder = $request->input('set_primary') ? 1 : $maxOrder + 1;

                if ($request->input('set_primary')) {
                    RoutingGatewayWeight::where('routing_rule_id', $routingRule->id)
                        ->increment('priority_order');
                }

                $weight = $request->input('weight', 0);

                RoutingGatewayWeight::create([
                    'routing_rule_id' => $routingRule->id,
                    'gateway_id' => $gateway->id,
                    'supplier_id' => $gateway->supplier_id,
                    'weight' => $weight,
                    'priority_order' => $priorityOrder,
                    'status' => 'active',
                    'is_fallback' => $weight === 0,
                    'created_by' => session('admin_email', 'admin'),
                ]);

                return response()->json(['success' => true, 'message' => 'Gateway added successfully']);
            });
        } catch (\Exception $e) {
            Log::error('[AdminController] routingAddGateway failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to add gateway. Please try again.'], 500);
        }
    }

    public function routingChangeWeight(Request $request)
    {
        $request->validate([
            'route_id' => 'required|string',
            'gateway_code' => 'required|string',
            'new_weight' => 'required|integer|min:1|max:100',
            'route_type' => 'required|string|in:uk,international',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $gateway = Gateway::where('gateway_code', $request->input('gateway_code'))->firstOrFail();
                $routingRule = $this->findOrCreateRoutingRule($request->input('route_id'), $request->input('route_type'));

                if (!$routingRule) {
                    return response()->json(['success' => false, 'message' => 'Route not found'], 404);
                }

                $weight = $this->findOrCreateGatewayWeight($routingRule, $gateway);
                $weight->update(['weight' => $request->input('new_weight'), 'is_fallback' => false, 'updated_by' => session('admin_email', 'admin')]);

                return response()->json(['success' => true, 'message' => 'Weight updated successfully']);
            });
        } catch (\Exception $e) {
            Log::error('[AdminController] routingChangeWeight failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to update weight. Please try again.'], 500);
        }
    }

    public function routingSetPrimary(Request $request)
    {
        $request->validate([
            'route_id' => 'required|string',
            'gateway_code' => 'required|string',
            'route_type' => 'required|string|in:uk,international',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $gateway = Gateway::where('gateway_code', $request->input('gateway_code'))->firstOrFail();
                $routingRule = $this->findOrCreateRoutingRule($request->input('route_id'), $request->input('route_type'));

                if (!$routingRule) {
                    return response()->json(['success' => false, 'message' => 'Route not found'], 404);
                }

                $this->findOrCreateGatewayWeight($routingRule, $gateway);

                RoutingGatewayWeight::where('routing_rule_id', $routingRule->id)
                    ->where('priority_order', '>=', 1)
                    ->increment('priority_order');

                RoutingGatewayWeight::where('routing_rule_id', $routingRule->id)
                    ->where('gateway_id', $gateway->id)
                    ->update(['priority_order' => 1, 'is_fallback' => false, 'updated_by' => session('admin_email', 'admin')]);

                return response()->json(['success' => true, 'message' => 'Primary gateway updated']);
            });
        } catch (\Exception $e) {
            Log::error('[AdminController] routingSetPrimary failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to set primary. Please try again.'], 500);
        }
    }

    public function routingToggleBlock(Request $request)
    {
        $request->validate([
            'route_id' => 'required|string',
            'gateway_code' => 'required|string',
            'route_type' => 'required|string|in:uk,international',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $gateway = Gateway::where('gateway_code', $request->input('gateway_code'))->firstOrFail();
                $routingRule = $this->findOrCreateRoutingRule($request->input('route_id'), $request->input('route_type'));

                if (!$routingRule) {
                    return response()->json(['success' => false, 'message' => 'Route not found'], 404);
                }

                $weight = $this->findOrCreateGatewayWeight($routingRule, $gateway);

                $newStatus = $weight->status === 'active' ? 'blocked' : 'active';
                $weight->update(['status' => $newStatus, 'updated_by' => session('admin_email', 'admin')]);

                return response()->json(['success' => true, 'message' => 'Gateway ' . $newStatus, 'new_status' => $newStatus]);
            });
        } catch (\Exception $e) {
            Log::error('[AdminController] routingToggleBlock failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to toggle block. Please try again.'], 500);
        }
    }

    public function routingRemoveGateway(Request $request)
    {
        $request->validate([
            'route_id' => 'required|string',
            'gateway_code' => 'required|string',
            'route_type' => 'required|string|in:uk,international',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $gateway = Gateway::where('gateway_code', $request->input('gateway_code'))->firstOrFail();
                $routingRule = $this->findOrCreateRoutingRule($request->input('route_id'), $request->input('route_type'));

                if (!$routingRule) {
                    return response()->json(['success' => false, 'message' => 'Route not found'], 404);
                }

                RoutingGatewayWeight::where('routing_rule_id', $routingRule->id)
                    ->where('gateway_id', $gateway->id)
                    ->delete();

                $remaining = RoutingGatewayWeight::where('routing_rule_id', $routingRule->id)->count();
                if ($remaining === 0) {
                    $routingRule->delete();
                }

                return response()->json(['success' => true, 'message' => 'Gateway removed from route']);
            });
        } catch (\Exception $e) {
            Log::error('[AdminController] routingRemoveGateway failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to remove gateway. Please try again.'], 500);
        }
    }

    public function routingCreateOverride(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'gateway_id' => 'required|integer',
            'scope' => 'required|string|in:global,uk_network,country',
            'scope_value' => 'nullable|string',
            'product_type' => 'nullable|string',
            'reason' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'sub_account_name' => 'nullable|string|max:255',
            'sender_id' => 'nullable|string|max:15',
        ]);

        try {
            $overrideData = [
                'account_id' => 0,
                'account_name' => $request->input('customer_name'),
                'sub_account_name' => $request->input('sub_account_name') ?: null,
                'forced_gateway_id' => $request->input('gateway_id'),
                'override_type' => 'force_gateway',
                'status' => 'active',
                'product_type' => $request->input('product_type') !== 'all' ? $request->input('product_type') : null,
                'sender_id' => $request->input('sender_id') ?: null,
                'reason' => $request->input('reason'),
                'valid_from' => $request->input('start_date'),
                'valid_to' => $request->input('end_date'),
                'created_by' => session('admin_email', 'admin'),
            ];

            $scope = $request->input('scope');
            $scopeValue = $request->input('scope_value');

            if ($scope === 'uk_network' && $scopeValue) {
                $parts = explode('/', $scopeValue);
                if (count($parts) === 2) {
                    $overrideData['mcc'] = $parts[0];
                    $overrideData['mnc'] = $parts[1];
                    $overrideData['country_iso'] = 'GB';
                }
            } elseif ($scope === 'country' && $scopeValue) {
                $overrideData['country_iso'] = $scopeValue;
            }

            RoutingCustomerOverride::create($overrideData);

            return response()->json(['success' => true, 'message' => 'Override created successfully']);
        } catch (\Exception $e) {
            Log::error('[AdminController] routingCreateOverride failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to create override. Please try again.'], 500);
        }
    }

    public function routingCancelOverride(Request $request)
    {
        $request->validate([
            'override_id' => 'required|integer',
        ]);

        try {
            $override = RoutingCustomerOverride::findOrFail($request->input('override_id'));
            $override->update(['status' => 'cancelled']);

            return response()->json(['success' => true, 'message' => 'Override cancelled successfully']);
        } catch (\Exception $e) {
            Log::error('[AdminController] routingCancelOverride failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Failed to cancel override. Please try again.'], 500);
        }
    }

    private function findRoutingRule($routeId, $routeType)
    {
        if ($routeType === 'uk') {
            $mccMnc = MccMnc::find($routeId);
            if (!$mccMnc) return null;
            return RoutingRule::where('mcc', $mccMnc->mcc)
                ->where('mnc', $mccMnc->mnc)
                ->where('country_iso', 'GB')
                ->first();
        } else {
            return RoutingRule::where('country_iso', $routeId)
                ->whereNull('mcc')
                ->whereNull('mnc')
                ->first();
        }
    }

    private function findOrCreateRoutingRule($routeId, $routeType)
    {
        $existing = $this->findRoutingRule($routeId, $routeType);
        if ($existing) {
            return $existing;
        }

        if ($routeType === 'uk') {
            $mccMnc = MccMnc::find($routeId);
            if (!$mccMnc) return null;
            return RoutingRule::create([
                'mcc' => $mccMnc->mcc,
                'mnc' => $mccMnc->mnc,
                'country_iso' => 'GB',
                'name' => 'UK Route - ' . $mccMnc->network_name,
                'country_name' => 'United Kingdom',
                'rule_type' => 'NETWORK',
                'selection_strategy' => 'weighted',
                'status' => 'active',
                'is_default' => false,
                'priority' => 100,
            ]);
        } else {
            $countryName = RateCard::where('country_iso', $routeId)->value('country_name') ?? $routeId;
            return RoutingRule::create([
                'country_iso' => $routeId,
                'mcc' => null,
                'mnc' => null,
                'name' => 'International Route - ' . $countryName,
                'country_name' => $countryName,
                'rule_type' => 'COUNTRY',
                'selection_strategy' => 'weighted',
                'status' => 'active',
                'is_default' => false,
                'priority' => 100,
            ]);
        }
    }

    private function findOrCreateGatewayWeight($routingRule, $gateway)
    {
        $weight = RoutingGatewayWeight::where('routing_rule_id', $routingRule->id)
            ->where('gateway_id', $gateway->id)
            ->first();

        if ($weight) {
            return $weight;
        }

        $maxOrder = RoutingGatewayWeight::where('routing_rule_id', $routingRule->id)->max('priority_order') ?? 0;
        $isFirst = $maxOrder === 0;

        return RoutingGatewayWeight::create([
            'routing_rule_id' => $routingRule->id,
            'gateway_id' => $gateway->id,
            'supplier_id' => $gateway->supplier_id,
            'weight' => 0,
            'priority_order' => $maxOrder + 1,
            'is_fallback' => !$isFirst,
            'status' => 'active',
            'created_by' => session('admin_email', 'admin'),
        ]);
    }
}
