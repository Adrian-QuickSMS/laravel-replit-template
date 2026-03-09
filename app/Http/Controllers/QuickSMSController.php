<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\OptOutList;
use App\Models\OptOutRecord;
use App\Models\Tag;
use App\Models\Account;
use App\Models\SenderId;
use App\Models\User;

class QuickSMSController extends Controller
{
    private function getApprovedSenderIds(): array
    {
        $tenantId = session('customer_tenant_id');
        if (!$tenantId) {
            return [['id' => 0, 'name' => 'QuickSMS', 'type' => 'alphanumeric']];
        }

        $account = \App\Models\Account::withoutGlobalScope('tenant')->find($tenantId);

        $senderIds = SenderId::where('account_id', $tenantId)
            ->where('workflow_status', 'approved')
            ->orderByDesc('is_default')
            ->orderBy('sender_id_value')
            ->get();

        $result = [];

        if ($account && $account->isTestStandard()) {
            $result[] = ['id' => 0, 'name' => 'QuickSMS', 'type' => 'alphanumeric'];
        }

        if ($senderIds->isEmpty() && empty($result)) {
            return [['id' => 0, 'name' => 'QuickSMS', 'type' => 'alphanumeric']];
        }

        foreach ($senderIds as $s) {
            $result[] = [
                'id' => $s->uuid,
                'name' => $s->sender_id_value,
                'type' => strtolower($s->sender_type === 'ALPHA' ? 'alphanumeric' : ($s->sender_type === 'NUMERIC' ? 'numeric' : 'shortcode')),
            ];
        }

        return $result;
    }

    public function login()
    {
        // If already logged in, redirect to dashboard
        if (session('customer_logged_in')) {
            return redirect()->route('dashboard');
        }
        
        return view('quicksms.auth.login', [
            'page_title' => 'Login'
        ]);
    }
    
    public function handleLogin(Request $request)
    {
        $email = strtolower(trim($request->input('email')));
        $password = $request->input('password');
        $isAjax = $request->expectsJson() || $request->ajax();

        if (!$email || !$password) {
            if ($isAjax) {
                return response()->json(['status' => 'error', 'message' => 'Please enter your email and password.'], 422);
            }
            return back()->withErrors(['email' => 'Please enter your email and password.']);
        }

        try {
            $user = \App\Models\User::withoutGlobalScope('tenant')
                ->where('email', $email)
                ->first();

            $passwordVerified = $user && \Illuminate\Support\Facades\Hash::check($password, $user->password);

            $result = \Illuminate\Support\Facades\DB::select(
                "SELECT * FROM sp_authenticate_user(?, ?::inet, ?::boolean)",
                [$email, $request->ip(), $passwordVerified ? 'true' : 'false']
            );

            if (empty($result) || empty($result[0]->user_id)) {
                if ($isAjax) {
                    return response()->json(['status' => 'error', 'message' => 'Invalid email or password.'], 401);
                }
                return back()->withErrors(['email' => 'Invalid email or password.'])->withInput(['email' => $email]);
            }

            $userData = $result[0];

            if (isset($userData->failed_attempts) && $userData->failed_attempts >= 5) {
                $msg = 'Account is locked due to too many failed attempts. Please try again later.';
                if ($isAjax) {
                    return response()->json(['status' => 'error', 'message' => $msg], 401);
                }
                return back()->withErrors(['email' => $msg]);
            }

            $user = \App\Models\User::withoutGlobalScope('tenant')->find($userData->user_id);

            if ($user->hasMfaEnabled()) {
                $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                session([
                    'mfa_pending_user_id' => $user->id,
                    'mfa_pending_tenant_id' => $user->tenant_id,
                    'mfa_otp' => hash('sha256', $otp),
                    'mfa_otp_expires' => now()->addMinutes(5)->timestamp,
                    'mfa_attempts' => 0,
                ]);

                $mobileHint = $user->mobile_number ? substr($user->mobile_number, -4) : '';

                $responseData = [
                    'status' => 'mfa_required',
                    'message' => 'MFA verification required',
                    'mobile_hint' => $mobileHint,
                ];

                if (config('app.env') === 'local') {
                    $responseData['otp_debug'] = $otp;
                }

                if ($isAjax) {
                    return response()->json($responseData);
                }

                return back()->withErrors(['mfa' => 'MFA verification required'])->withInput(['email' => $email]);
            }

            $this->completeLogin($request, $user);

            if ($isAjax) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'redirect' => route('dashboard'),
                ]);
            }
            return redirect()->route('dashboard')->with('success', 'Welcome back, ' . $user->first_name . '!');

        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            if ($isAjax) {
                return response()->json(['status' => 'error', 'message' => 'An error occurred during login. Please try again.'], 500);
            }
            return back()->withErrors(['email' => 'An error occurred during login. Please try again.'])->withInput(['email' => $email]);
        }
    }

    public function verifyMfa(Request $request)
    {
        $code = $request->input('code');
        $pendingUserId = session('mfa_pending_user_id');
        $pendingTenantId = session('mfa_pending_tenant_id');
        $storedOtp = session('mfa_otp');
        $otpExpires = session('mfa_otp_expires');
        $attempts = session('mfa_attempts', 0);

        if (!$pendingUserId || !$storedOtp) {
            return response()->json(['status' => 'error', 'message' => 'No MFA challenge pending. Please log in again.'], 401);
        }

        if ($attempts >= 5) {
            session()->forget(['mfa_pending_user_id', 'mfa_pending_tenant_id', 'mfa_otp', 'mfa_otp_expires', 'mfa_attempts']);
            return response()->json(['status' => 'error', 'message' => 'Too many failed attempts. Please log in again.'], 401);
        }

        if (now()->timestamp > $otpExpires) {
            session()->forget(['mfa_pending_user_id', 'mfa_pending_tenant_id', 'mfa_otp', 'mfa_otp_expires', 'mfa_attempts']);
            return response()->json(['status' => 'error', 'message' => 'Code has expired. Please log in again.'], 401);
        }

        if (hash('sha256', $code) !== $storedOtp) {
            session(['mfa_attempts' => $attempts + 1]);
            $remaining = 5 - ($attempts + 1);
            return response()->json(['status' => 'error', 'message' => "Invalid code. {$remaining} attempts remaining."], 401);
        }

        $user = \App\Models\User::withoutGlobalScope('tenant')
            ->where('id', $pendingUserId)
            ->where('tenant_id', $pendingTenantId)
            ->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found. Please log in again.'], 401);
        }

        session()->forget(['mfa_pending_user_id', 'mfa_pending_tenant_id', 'mfa_otp', 'mfa_otp_expires', 'mfa_attempts']);

        $this->completeLogin($request, $user);

        return response()->json([
            'status' => 'success',
            'message' => 'MFA verification successful',
            'redirect' => route('dashboard'),
        ]);
    }

    public function resendMfa(Request $request)
    {
        $pendingUserId = session('mfa_pending_user_id');
        if (!$pendingUserId) {
            return response()->json(['status' => 'error', 'message' => 'No MFA challenge pending.'], 401);
        }

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        session([
            'mfa_otp' => hash('sha256', $otp),
            'mfa_otp_expires' => now()->addMinutes(5)->timestamp,
            'mfa_attempts' => 0,
        ]);

        $responseData = [
            'status' => 'success',
            'message' => 'New code sent',
        ];

        if (config('app.env') === 'local') {
            $responseData['otp_debug'] = $otp;
        }

        return response()->json($responseData);
    }

    private function completeLogin(Request $request, $user)
    {
        session([
            'customer_logged_in' => true,
            'customer_email' => $user->email,
            'customer_name' => $user->first_name . ' ' . $user->last_name,
            'customer_user_id' => $user->id,
            'customer_tenant_id' => $user->tenant_id,
            'customer_account_id' => $user->tenant_id,
        ]);

        \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$user->tenant_id]);

        $request->session()->regenerate();
    }
    
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('auth.login')->with('success', 'You have been logged out.');
    }
    
    public function signup()
    {
        return view('quicksms.auth.signup', [
            'page_title' => 'Sign Up'
        ]);
    }
    
    public function verifyEmail()
    {
        return view('quicksms.auth.verify-email', [
            'page_title' => 'Verify Email'
        ]);
    }
    
    public function signupSecurity()
    {
        return view('quicksms.auth.security', [
            'page_title' => 'Security & Consent'
        ]);
    }
    
    public function dashboard()
    {
        $accountId = session('customer_tenant_id');
        $balanceData = [
            'balance' => 0,
            'effectiveAvailable' => 0,
            'creditLimit' => 0,
            'reserved' => 0,
            'currency' => 'GBP',
        ];

        if ($accountId) {
            $balance = \App\Models\Billing\AccountBalance::where('account_id', $accountId)->first();
            $account = \App\Models\Account::withoutGlobalScopes()->find($accountId);

            if ($account) {
                $currentBalance = $balance ? (float) $balance->balance : 0;
                $creditLimit = (float) ($account->credit_limit ?? 0);
                $reserved = $balance ? (float) $balance->reserved : 0;
                $effectiveAvailable = $balance ? (float) $balance->effective_available : $creditLimit;

                $balanceData = [
                    'balance' => $currentBalance,
                    'effectiveAvailable' => $effectiveAvailable,
                    'creditLimit' => $creditLimit,
                    'reserved' => $reserved,
                    'currency' => $account->currency ?? 'GBP',
                ];
            }
        }

        $pricingData = ['sms' => null, 'rcs_basic' => null, 'rcs_single' => null];
        if ($accountId && $account) {
            $productTypes = ['sms', 'rcs_basic', 'rcs_single'];

            $customerPrices = \App\Models\Billing\CustomerPrice::where('account_id', $accountId)
                ->whereIn('product_type', $productTypes)
                ->whereNull('country_iso')
                ->active()
                ->validAt()
                ->get()
                ->keyBy('product_type');

            foreach ($productTypes as $type) {
                if ($customerPrices->has($type)) {
                    $pricingData[$type] = (float) $customerPrices[$type]->unit_price;
                }
            }

            $missingTypes = array_filter($productTypes, fn($t) => $pricingData[$t] === null);
            if (!empty($missingTypes)) {
                $tier = $account->product_tier ?? 'starter';
                $tierPrices = \App\Models\Billing\ProductTierPrice::where('product_tier', $tier)
                    ->whereIn('product_type', $missingTypes)
                    ->whereNull('country_iso')
                    ->active()
                    ->validAt()
                    ->get()
                    ->keyBy('product_type');

                foreach ($missingTypes as $type) {
                    if ($tierPrices->has($type)) {
                        $pricingData[$type] = (float) $tierPrices[$type]->unit_price;
                    }
                }
            }
        }

        return view('quicksms.dashboard', [
            'page_title' => 'Dashboard',
            'balanceData' => $balanceData,
            'pricingData' => $pricingData,
        ]);
    }

    public function messages()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Messages',
            'purpose' => 'Manage all messaging activities including sending, receiving, and campaign management.',
            'sub_modules' => [
                'Send Message',
                'Inbox',
                'Campaign History'
            ]
        ]);
    }

    public function sendMessage()
    {
        $sender_ids = $this->getApprovedSenderIds();

        $userId = session('customer_user_id');
        $user = \App\Models\User::withoutGlobalScope('tenant')->find($userId);
        $rcs_agents = $user
            ? \App\Models\RcsAgent::usableByUser($user)
                ->select('id', 'uuid', 'name', 'description', 'brand_color', 'logo_url')
                ->get()
                ->map(fn($a) => [
                    'id'          => $a->uuid,
                    'name'        => $a->name,
                    'logo'        => $a->logo_url ?: null,
                    'tagline'     => $a->description ?? '',
                    'brand_color' => $a->brand_color ?? '#886CC0',
                ])
                ->toArray()
            : [];

        $templates = $this->getTemplatesForView();

        $lists = $this->getContactListsForView();
        $tags = $this->getTagsForView();
        $opt_out_lists = $this->getOptOutListsForView();
        $virtual_numbers = [];
        $optout_domains = [];

        $editConfig = null;
        if (request()->has('campaign_id')) {
            $editConfig = session('campaign_config', null);
        } else {
            session()->forget('campaign_config');
        }

        $tenantId = session('customer_tenant_id');
        $account = $tenantId ? \App\Models\Account::withoutGlobalScope('tenant')->find($tenantId) : null;
        $accountStatus = $account->status ?? null;
        $isTestMode = $account && $account->isTestMode();
        $isTestStandard = $account && $account->isTestStandard();

        $testCreditsRemaining = null;
        $approvedTestNumbers = [];
        if ($isTestMode) {
            $wallet = \App\Models\Billing\TestCreditWallet::where('account_id', $tenantId)
                ->where('expired', false)
                ->orderByDesc('created_at')
                ->first();
            $testCreditsRemaining = $wallet ? $wallet->credits_remaining : 0;
        }
        if ($isTestStandard) {
            $settings = \App\Models\AccountSettings::where('account_id', $tenantId)->first();
            $approvedTestNumbers = $settings->approved_test_numbers ?? [];
        }

        return view('quicksms.messages.send-message', [
            'page_title' => 'Send Message',
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents,
            'templates' => $templates,
            'lists' => $lists,
            'tags' => $tags,
            'opt_out_lists' => $opt_out_lists,
            'virtual_numbers' => $virtual_numbers,
            'optout_domains' => $optout_domains,
            'account_pricing' => $this->getAccountPricingForView(),
            'edit_campaign_config' => $editConfig,
            'account_status' => $accountStatus,
            'is_test_mode' => $isTestMode,
            'is_test_standard' => $isTestStandard,
            'test_credits_remaining' => $testCreditsRemaining,
            'approved_test_numbers' => $approvedTestNumbers,
        ]);
    }

    /**
     * Get real approved RCS agents for the current user, mapped for Blade views.
     */
    private function getTemplatesForLibrary(): array
    {
        $typeToChannel = [
            'sms' => 'sms',
            'rcs_basic' => 'basic_rcs',
            'rcs_single' => 'rich_rcs',
            'rcs_carousel' => 'rich_rcs',
        ];
        $typeToContentType = [
            'sms' => 'text',
            'rcs_basic' => 'text',
            'rcs_single' => 'rich_card',
            'rcs_carousel' => 'carousel',
        ];
        return \App\Models\MessageTemplate::orderByDesc('updated_at')
            ->get()
            ->values()
            ->map(fn($t, $i) => [
                'id' => $i + 1,
                'uuid' => $t->id,
                'templateId' => substr(md5($t->id), 0, 8),
                'name' => $t->name,
                'channel' => $typeToChannel[$t->type] ?? 'sms',
                'trigger' => $t->trigger_type ?? 'portal',
                'content' => $t->content ?? '',
                'rcsContent' => $t->rcs_content,
                'contentType' => $typeToContentType[$t->type] ?? 'text',
                'accessScope' => 'All Sub-accounts',
                'subAccounts' => ['all'],
                'status' => $t->status === 'active' ? 'live' : ($t->status === 'suspended' ? 'suspended' : $t->status),
                'suspendedBy' => $t->suspended_by,
                'version' => $t->version ?? 1,
                'lastUpdated' => $t->updated_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
            ])
            ->toArray();
    }

    private function getTemplatesForView(): array
    {
        $typeToChannel = [
            'sms' => 'SMS',
            'rcs_basic' => 'Basic RCS + SMS',
            'rcs_single' => 'Rich RCS + SMS',
            'rcs_carousel' => 'Rich RCS + SMS',
        ];
        return \App\Models\MessageTemplate::whereIn('status', ['active', 'draft'])
            ->where(function($q) {
                $q->where('trigger_type', 'portal')->orWhereNull('trigger_type');
            })
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'content' => $t->content ?? '',
                'trigger' => ucfirst($t->trigger_type ?? 'portal'),
                'channel' => $typeToChannel[$t->type] ?? 'SMS',
                'status' => $t->status === 'active' ? 'Live' : ucfirst($t->status),
                'version' => $t->version ?? 1,
                'rcs_payload' => $t->rcs_content,
                'sender_id_id' => $t->sender_id_id,
                'rcs_agent_id' => $t->rcs_agent_id,
                'opt_out_enabled' => (bool) $t->opt_out_enabled,
                'opt_out_method' => $t->opt_out_method,
                'opt_out_number_id' => $t->opt_out_number_id,
                'opt_out_keyword' => $t->opt_out_keyword,
                'opt_out_text' => $t->opt_out_text,
                'opt_out_list_id' => $t->opt_out_list_id,
                'opt_out_url_enabled' => (bool) $t->opt_out_url_enabled,
                'opt_out_screening_list_ids' => $t->opt_out_screening_list_ids ?? [],
                'trackable_link_enabled' => (bool) $t->trackable_link_enabled,
                'trackable_link_domain' => $t->trackable_link_domain,
                'message_expiry_enabled' => (bool) $t->message_expiry_enabled,
                'message_expiry_value' => $t->message_expiry_value,
                'social_hours_enabled' => (bool) $t->social_hours_enabled,
                'social_hours_from' => $t->social_hours_from,
                'social_hours_to' => $t->social_hours_to,
            ])
            ->toArray();
    }

    private function getRcsAgentsForView(): array
    {
        $userId = session('customer_user_id');
        $user = \App\Models\User::withoutGlobalScope('tenant')->find($userId);
        if (!$user) {
            return [];
        }
        return \App\Models\RcsAgent::usableByUser($user)
            ->select('id', 'uuid', 'name', 'description', 'brand_color', 'logo_url')
            ->get()
            ->map(fn($a) => [
                'id'          => $a->uuid,
                'name'        => $a->name,
                'logo'        => $a->logo_url ?: asset('images/rcs-agents/quicksms-brand.svg'),
                'tagline'     => $a->description ?? '',
                'brand_color' => $a->brand_color ?? '#886CC0',
                'status'      => 'approved',
            ])
            ->toArray();
    }

    /**
     * Get real opt-out lists for the current tenant, mapped for Blade views.
     * Creates a default master list if none exist yet.
     */
    private function getOptOutListsForView(): array
    {
        $tenantId = session('customer_tenant_id', '');
        if (!$tenantId) {
            return [];
        }
        $lists = \App\Models\OptOutList::where('account_id', $tenantId)
            ->orderByDesc('is_master')
            ->orderBy('name')
            ->get(['id', 'name', 'is_master', 'count']);

        if ($lists->isEmpty()) {
            try {
                $master = \App\Models\OptOutList::create([
                    'account_id'  => $tenantId,
                    'name'        => 'Master Opt-Out List',
                    'description' => 'Default opt-out list for this account',
                    'count'       => 0,
                ]);
                \DB::table('opt_out_lists')->where('id', $master->id)->update(['is_master' => true]);
                $master->refresh();
                $lists = collect([$master]);
            } catch (\Throwable $e) {
                return [];
            }
        }

        return $lists->map(fn($l) => [
            'id'         => $l->id,
            'name'       => $l->name,
            'count'      => $l->count ?? 0,
            'is_default' => (bool) $l->is_master,
        ])->toArray();
    }

    /**
     * Get real contact lists for the current tenant.
     */
    private function getContactListsForView(): array
    {
        $tenantId = session('customer_tenant_id', '');
        if (!$tenantId) {
            return [];
        }
        return \App\Models\ContactList::where('account_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name', 'contact_count'])
            ->map(fn($l) => [
                'id'    => $l->id,
                'name'  => $l->name,
                'count' => $l->contact_count ?? 0,
            ])
            ->toArray();
    }

    /**
     * Get real tags for the current tenant.
     */
    private function getTagsForView(): array
    {
        $tenantId = session('customer_tenant_id', '');
        if (!$tenantId) {
            return [];
        }
        return \App\Models\Tag::where('account_id', $tenantId)
            ->orderBy('name')
            ->get(['id', 'name', 'color', 'contact_count'])
            ->map(fn($t) => [
                'id'    => $t->id,
                'name'  => $t->name,
                'color' => $t->color ?? '#886CC0',
                'count' => $t->contact_count ?? 0,
            ])
            ->toArray();
    }

    /**
     * Build a simple channel → unit_price map for the send-message cost preview.
     * Reads from the account's product tier pricing; falls back to safe defaults.
     */
    private function getAccountPricingForView(): array
    {
        $defaults = [
            'sms'           => 0.0395,
            'rcs_basic'     => 0.0395,
            'rcs_single'    => 0.0600,
            'rcs_carousel'  => 0.0600,
            'rcs_rich'      => 0.0600,
            'currency'      => 'GBP',
        ];

        try {
            $tenantId = session('customer_tenant_id');
            if (!$tenantId) {
                return $defaults;
            }

            $account = \App\Models\Account::withoutGlobalScope('tenant')->find($tenantId);
            $tier = $account?->product_tier ?? 'starter';

            $rows = \DB::table('product_tier_prices')
                ->where('active', true)
                ->whereRaw('valid_from <= CURRENT_DATE')
                ->where(function ($q) {
                    $q->whereNull('valid_to')->orWhereRaw('valid_to >= CURRENT_DATE');
                })
                ->where('product_tier', $tier)
                ->whereNull('country_iso')
                ->whereIn('product_type', ['sms', 'rcs_basic', 'rcs_single'])
                ->pluck('unit_price', 'product_type');

            $pricing = $defaults;
            foreach ($rows as $type => $price) {
                $pricing[$type] = (float) $price;
            }

            $pricing['rcs_carousel'] = $pricing['rcs_single'];

            // rcs_rich maps to rcs_single pricing; currency from account record
            $pricing['rcs_rich'] = $pricing['rcs_single'];
            $pricing['currency'] = $account->currency ?? 'GBP';

            return $pricing;
        } catch (\Throwable $e) {
            return $defaults;
        }
    }

    private function getAccountVatStatus(): array
    {
        $defaults = ['vat_applicable' => false, 'vat_rate' => 0];

        try {
            $tenantId = session('customer_tenant_id');
            if (!$tenantId) {
                return $defaults;
            }

            $account = \App\Models\Account::withoutGlobalScope('tenant')->find($tenantId);
            if (!$account) {
                return $defaults;
            }

            if ($account->vat_reverse_charges) {
                return ['vat_applicable' => false, 'vat_rate' => 0];
            }

            if ($account->vat_registered) {
                return ['vat_applicable' => true, 'vat_rate' => 20];
            }

            return ['vat_applicable' => true, 'vat_rate' => 20];
        } catch (\Throwable $e) {
            return $defaults;
        }
    }

    private function calculateSmsSegments(string $content): int
    {
        if (empty($content)) {
            return 1;
        }

        $hasUnicode = preg_match('/[^\x20-\x7E£¥èéùìòÇØøÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#¤%&\'()*+,\-.\/:;<=>?¡ÄÖÑÜ§¿äöñüà@{}\[\]~\^|€\r\n\\\\]/', $content);
        $len = mb_strlen($content);

        if ($hasUnicode) {
            return $len <= 70 ? 1 : (int) ceil($len / 67);
        }

        return $len <= 160 ? 1 : (int) ceil($len / 153);
    }

    public function confirmCampaign(Request $request)
    {
        $sessionData = $request->session()->get('campaign_config', []);
        $campaignId = $request->query('campaign_id', $sessionData['campaign_id'] ?? null);

        $campaign = [
            'id' => $campaignId,
            'name' => $sessionData['campaign_name'] ?? 'Untitled Campaign',
            'created_by' => session('customer_name', 'Current User'),
            'created_at' => now()->format('d/m/Y H:i'),
            'scheduled_time' => isset($sessionData['scheduled_time']) && $sessionData['scheduled_time'] !== 'now' 
                ? $sessionData['scheduled_time'] 
                : 'Immediate',
            'message_validity' => isset($sessionData['message_expiry']) && $sessionData['message_expiry'] 
                ? $sessionData['message_expiry'] . ' hours' 
                : 'Default (48 hours)',
            'sending_window' => isset($sessionData['sending_window']) && $sessionData['sending_window'] 
                ? $sessionData['sending_window'] 
                : 'No restrictions',
        ];

        $channelType = $sessionData['channel'] ?? 'sms_only';

        $agentName = $sessionData['rcs_agent'] ?? 'Not selected';
        $agentLogo = null;
        $agentId = $sessionData['rcs_agent_id'] ?? null;
        if ($agentId) {
            $userId = session('customer_user_id');
            $accountId = session('customer_tenant_id');
            $user = $userId ? \App\Models\User::withoutGlobalScope('tenant')->find($userId) : null;
            if ($user) {
                $lookupCol = is_numeric($agentId) ? 'id' : 'uuid';
                $agentRecord = \App\Models\RcsAgent::withoutGlobalScope('tenant')
                    ->where('account_id', $accountId)
                    ->where($lookupCol, $agentId)
                    ->whereNull('deleted_at')
                    ->first();
                if ($agentRecord) {
                    $agentName = $agentRecord->name;
                    $agentLogo = $agentRecord->logo_url;
                }
            }
        }

        $channel = [
            'type' => $channelType,
            'sms_sender_id' => $sessionData['sender_id'] ?? 'Not selected',
            'rcs_agent' => [
                'name' => $agentName,
                'logo' => $agentLogo ?: asset('images/rcs-agents/quicksms-brand.svg'),
            ],
        ];

        $recipientCount = $sessionData['recipient_count'] ?? 0;
        $validCount = $sessionData['valid_count'] ?? $recipientCount;
        $invalidCount = $sessionData['invalid_count'] ?? 0;
        $optedOutCount = $sessionData['opted_out_count'] ?? 0;
        
        $recipients = [
            'total_selected' => $recipientCount,
            'valid' => $validCount,
            'invalid' => $invalidCount,
            'opted_out' => $optedOutCount,
            'sources' => [
                'manual_input' => $sessionData['sources']['manual_input'] ?? 0,
                'file_upload' => $sessionData['sources']['file_upload'] ?? 0,
                'contacts' => $sessionData['sources']['contacts'] ?? 0,
                'lists' => $sessionData['sources']['lists'] ?? 0,
                'dynamic_lists' => $sessionData['sources']['dynamic_lists'] ?? 0,
                'tags' => $sessionData['sources']['tags'] ?? 0,
            ],
        ];
        
        if ($recipientCount > 0 && array_sum($recipients['sources']) === 0) {
            $recipients['sources']['manual_input'] = $recipientCount;
        }

        $accountPricing = $this->getAccountPricingForView();
        $accountVat = $this->getAccountVatStatus();

        $smsRate = (float) ($accountPricing['sms'] ?? 0.0395);
        $rcsBasicRate = (float) ($accountPricing['rcs_basic'] ?? 0.0395);
        $rcsSingleRate = (float) ($accountPricing['rcs_single'] ?? 0.0600);
        $rcsPenetration = 0.65;

        $pricing = [
            'sms_unit_price' => $smsRate,
            'rcs_basic_price' => $rcsBasicRate,
            'rcs_single_price' => $rcsSingleRate,
            'vat_applicable' => $accountVat['vat_applicable'],
            'vat_rate' => $accountVat['vat_rate'],
            'rcs_penetration' => $rcsPenetration,
        ];

        $segmentBreakdown = [];
        $totalSmsParts = 0;

        $accountId = session('customer_tenant_id');

        if ($campaignId && $accountId) {
            try {
                $ownsCampaign = \DB::table('campaigns')
                    ->where('id', $campaignId)
                    ->where('account_id', $accountId)
                    ->exists();

                if ($ownsCampaign) {
                    $segmentBreakdown = \DB::table('campaign_recipients')
                        ->where('campaign_id', $campaignId)
                        ->where('status', 'pending')
                        ->selectRaw('segments, count(*) as recipient_count')
                        ->groupBy('segments')
                        ->orderBy('segments')
                        ->get()
                        ->all();

                    $totalSmsParts = array_reduce($segmentBreakdown, function ($carry, $group) {
                        return $carry + ($group->recipient_count * $group->segments);
                    }, 0);

                    if ($totalSmsParts === 0 && $validCount > 0) {
                        $campaignRecord = \DB::table('campaigns')->where('id', $campaignId)->first(['segment_count']);
                        $baseSegments = $campaignRecord->segment_count ?? 1;
                        $totalSmsParts = $validCount * $baseSegments;
                        $segmentBreakdown = [(object) ['segments' => $baseSegments, 'recipient_count' => $validCount]];
                    }
                } else {
                    $campaignId = null;
                }
            } catch (\Throwable $e) {
                \Log::warning('[ConfirmCampaign] Failed to load segment breakdown', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($totalSmsParts === 0 && $validCount > 0 && $channelType === 'sms') {
            $msgContent = $sessionData['message_content'] ?? '';
            $baseSegments = $this->calculateSmsSegments($msgContent);
            $totalSmsParts = $validCount * $baseSegments;
            $segmentBreakdown = [(object) ['segments' => $baseSegments, 'recipient_count' => $validCount]];
        }


        $message = [
            'type' => $channelType,
            'sms_content' => $sessionData['message_content'] ?? '',
            'rcs_content' => $sessionData['rcs_content'] ?? null,
        ];

        // Get real cost estimate from backend if campaign has been prepared
        $realEstimate = null;
        if (!empty($sessionData['campaign_id'])) {
            $campaign_record = \App\Models\Campaign::find($sessionData['campaign_id']);
            if ($campaign_record && $campaign_record->preparation_status === 'ready') {
                try {
                    $campaignService = app(\App\Services\Campaign\CampaignService::class);
                    $costEstimate = $campaignService->estimateCost($campaign_record);
                    $realEstimate = $costEstimate->toArray();
                } catch (\Exception $e) {
                    // Fall back to session-based estimate
                }
            }
        }

        $isEditingExisting = !empty($sessionData['is_editing_existing']);

        $account = $accountId ? \App\Models\Account::withoutGlobalScope('tenant')->find($accountId) : null;
        $isTestMode = $account && $account->isTestMode();
        $isTestStandard = $account && $account->isTestStandard();
        $approvedTestNumbers = [];
        $testCreditsRemaining = null;

        if ($isTestMode) {
            $wallet = \App\Models\Billing\TestCreditWallet::where('account_id', $accountId)
                ->where('expired', false)
                ->orderByDesc('created_at')
                ->first();
            $testCreditsRemaining = $wallet ? $wallet->credits_remaining : 0;
        }
        if ($isTestStandard) {
            $settings = \App\Models\AccountSettings::where('account_id', $accountId)->first();
            $approvedTestNumbers = $settings->approved_test_numbers ?? [];
        }

        return view('quicksms.messages.confirm-campaign', [
            'page_title' => $isEditingExisting ? 'Update & Send Campaign' : 'Confirm & Send Campaign',
            'campaign' => $campaign,
            'channel' => $channel,
            'recipients' => $recipients,
            'pricing' => $pricing,
            'message' => $message,
            'segment_breakdown' => $segmentBreakdown,
            'total_sms_parts' => $totalSmsParts,
            'campaign_id' => $campaignId,
            'realEstimate' => $realEstimate,
            'is_editing_existing' => $isEditingExisting,
            'is_test_mode' => $isTestMode,
            'is_test_standard' => $isTestStandard,
            'test_credits_remaining' => $testCreditsRemaining,
            'approved_test_numbers' => $approvedTestNumbers,
        ]);
    }

    public function storeCampaignConfig(Request $request)
    {
        $validated = $request->validate([
            'campaign_id' => 'nullable|string|uuid',
            'campaign_name' => 'nullable|string|max:255',
            'channel' => 'nullable|string|in:sms_only,basic_rcs,rich_rcs',
            'sender_id' => 'nullable|string|max:50',
            'sender_id_id' => 'nullable',
            'rcs_agent' => 'nullable|string|max:100',
            'rcs_agent_id' => 'nullable',
            'campaign_type' => 'nullable|string|in:sms,rcs_basic,rcs_single,rcs_carousel',
            'message_content' => 'nullable|string|max:10000',
            'rcs_content' => 'nullable|array',
            'recipient_sources' => 'nullable|array|max:50',
            'recipient_state' => 'nullable|array',
            'scheduled_time' => 'nullable|string|max:50',
            'message_expiry' => 'nullable|string|max:50',
            'sending_window' => 'nullable|string|max:50',
            'trackable_link' => 'nullable|boolean',
            'trackable_link_domain' => 'nullable|string|max:100',
            'recipient_count' => 'nullable|integer|min:0|max:10000000',
            'valid_count' => 'nullable|integer|min:0|max:10000000',
            'invalid_count' => 'nullable|integer|min:0|max:10000000',
            'opted_out_count' => 'nullable|integer|min:0|max:10000000',
            'sources' => 'nullable|array',
            'optout_config' => 'nullable|array',
            'is_editing_existing' => 'nullable|boolean',
        ]);

        $request->session()->put('campaign_config', $validated);


        return response()->json(['success' => true]);
    }

    /**
     * Create a Campaign record from session data and initiate send.
     * Called by the Confirm & Send page.
     */
    public function confirmAndSend(Request $request)
    {
        $sessionData = $request->session()->get('campaign_config', []);

        if (empty($sessionData)) {
            return response()->json([
                'success' => false,
                'message' => 'No campaign configuration found. Please go back and configure your campaign.',
            ], 422);
        }

        $accountId = session('customer_tenant_id');
        if (!$accountId) {
            return response()->json([
                'success' => false,
                'message' => 'No account context. Please log in again.',
            ], 401);
        }

        // Idempotency: use client-provided key or derive from session config hash
        $idempotencyKey = $request->header('X-Idempotency-Key')
            ?? $request->input('idempotency_key')
            ?? 'campaign_send_' . md5($accountId . json_encode($sessionData));

        $cacheKey = "idempotent:{$accountId}:{$idempotencyKey}";

        // Check if this exact send was already processed (TTL 5 minutes)
        $existing = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($existing) {
            return response()->json($existing, 200);
        }

        // Acquire a lock to prevent concurrent duplicate submissions (10s timeout)
        $lock = \Illuminate\Support\Facades\Cache::lock("lock:{$cacheKey}", 10);
        if (!$lock->get()) {
            return response()->json([
                'success' => false,
                'message' => 'Your campaign is already being processed. Please wait.',
            ], 409);
        }

        try {
            // Re-check after acquiring lock (another request may have completed)
            $existing = \Illuminate\Support\Facades\Cache::get($cacheKey);
            if ($existing) {
                return response()->json($existing, 200);
            }

            $campaignService = app(\App\Services\Campaign\CampaignService::class);
            $campaignId = $sessionData['campaign_id'] ?? $request->input('campaign_id');
            $campaign = null;

            if ($campaignId) {
                $campaign = \App\Models\Campaign::where('id', $campaignId)
                    ->where('account_id', $accountId)
                    ->whereIn('status', ['draft', 'scheduled'])
                    ->first();
            }

            if (!$campaign) {
                $senderIdValue = $sessionData['sender_id_id'] ?? null;
                if ($senderIdValue === '0' || $senderIdValue === 0) {
                    $senderIdValue = null;
                }
                if ($senderIdValue && !is_numeric($senderIdValue)) {
                    $resolved = \DB::table('sender_ids')
                        ->where('uuid', $senderIdValue)
                        ->where('account_id', $accountId)
                        ->value('id');
                    if (!$resolved) {
                        return response()->json(['success' => false, 'message' => 'Sender ID not found.'], 422);
                    }
                    $senderIdValue = $resolved;
                }
                $rcsAgentValue = $sessionData['rcs_agent_id'] ?? null;
                if ($rcsAgentValue && !is_numeric($rcsAgentValue)) {
                    $resolved = \DB::table('rcs_agents')
                        ->where('uuid', $rcsAgentValue)
                        ->where('account_id', $accountId)
                        ->value('id');
                    if (!$resolved) {
                        return response()->json(['success' => false, 'message' => 'RCS Agent not found.'], 422);
                    }
                    $rcsAgentValue = $resolved;
                }

                $campaignData = [
                    'name' => $sessionData['campaign_name'] ?? 'Untitled Campaign',
                    'type' => $sessionData['campaign_type'] ?? 'sms',
                    'message_content' => $sessionData['message_content'] ?? null,
                    'rcs_content' => $sessionData['rcs_content'] ?? null,
                    'sender_id_id' => $senderIdValue,
                    'rcs_agent_id' => $rcsAgentValue,
                    'recipient_sources' => $sessionData['recipient_sources'] ?? [],
                ];
                $campaign = $campaignService->create($accountId, $campaignData);
            }

            // Handle scheduling vs immediate send
            $scheduledTime = $sessionData['scheduled_time'] ?? 'now';

            if ($scheduledTime !== 'now' && $scheduledTime !== '') {
                $timezone = $sessionData['timezone'] ?? config('app.timezone', 'Europe/London');
                $result = $campaignService->schedule($campaign, $scheduledTime, $timezone);

                $request->session()->forget('campaign_config');

                $responseData = [
                    'success' => true,
                    'message' => 'Campaign scheduled successfully.',
                    'campaign_id' => $campaign->id,
                    'status' => 'scheduled',
                ];
                \Illuminate\Support\Facades\Cache::put($cacheKey, $responseData, 300);

                return response()->json($responseData);
            }

            // Immediate send
            $result = $campaignService->sendNow($campaign);

            $request->session()->forget('campaign_config');

            $responseData = [
                'success' => true,
                'message' => 'Campaign queued for delivery.',
                'campaign_id' => $campaign->id,
                'status' => 'queued',
            ];
            \Illuminate\Support\Facades\Cache::put($cacheKey, $responseData, 300);

            return response()->json($responseData);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Campaign confirmAndSend failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'account_id' => $accountId,
                'session_data_keys' => array_keys($sessionData),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending your campaign. Please try again or contact support.',
            ], 500);
        } finally {
            $lock->release();
        }
    }

    public function inbox()
    {
        $sender_ids = $this->getApprovedSenderIds();

        // TODO: Replace with database query - GET /api/rcs-agents?status=approved
        // Only approved RCS agents are selectable in Inbox compose
        $rcs_agents = [
            ['id' => 'agent_1', 'name' => 'QuickSMS Brand', 'status' => 'approved'],
            ['id' => 'agent_2', 'name' => 'RetailBot', 'status' => 'approved'],
        ];

        $templates = $this->getTemplatesForView();

        // Extended mock conversations dataset for filter/sort testing
        // TODO: Replace with API call to GET /api/conversations
        $conversations = [
            [
                'id' => 'conv_001',
                'phone' => '+447700900111',
                'phone_masked' => '+44 77** ***111',
                'name' => 'Sarah Mitchell',
                'initials' => 'SM',
                'contact_id' => 'c_001',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'sender_id' => 'sid_4',
                'unread' => true,
                'unread_count' => 2,
                'last_message' => 'When will my order arrive?',
                'last_message_time' => '10:32 AM',
                'timestamp' => strtotime('today 10:32'),
                'first_contact' => '15 Dec 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'Hi Sarah, your order #12345 has been dispatched!', 'time' => '09:15 AM', 'date' => '22 Dec 2024'],
                    ['direction' => 'inbound', 'content' => 'Thanks! How long will delivery take?', 'time' => '09:45 AM', 'date' => '22 Dec 2024'],
                    ['direction' => 'outbound', 'content' => 'Usually 2-3 business days. You\'ll receive tracking soon.', 'time' => '09:48 AM', 'date' => '22 Dec 2024'],
                    ['direction' => 'inbound', 'content' => 'Great, thank you!', 'time' => '10:02 AM', 'date' => '23 Dec 2024'],
                    ['direction' => 'inbound', 'content' => 'When will my order arrive?', 'time' => '10:32 AM', 'date' => '23 Dec 2024'],
                ],
            ],
            [
                'id' => 'conv_002',
                'phone' => '+447700900222',
                'phone_masked' => '+44 77** ***222',
                'name' => 'James Wilson',
                'initials' => 'JW',
                'contact_id' => 'c_002',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'rcs_agent_id' => 'agent_1',
                'unread' => true,
                'unread_count' => 1,
                'last_message' => 'Can I change my delivery address?',
                'last_message_time' => '09:15 AM',
                'timestamp' => strtotime('today 09:15'),
                'first_contact' => '10 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Hi, I placed an order yesterday', 'time' => '08:30 AM'],
                    ['direction' => 'outbound', 'content' => 'Hello James! How can we help you today?', 'time' => '08:45 AM'],
                    ['direction' => 'inbound', 'content' => 'Can I change my delivery address?', 'time' => '09:15 AM'],
                ],
            ],
            [
                'id' => 'conv_003',
                'phone' => '+447700900333',
                'phone_masked' => '+44 77** ***333',
                'name' => '+44 7700 900333',
                'initials' => '??',
                'contact_id' => null,
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'sender_id' => 'sid_3',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'STOP',
                'last_message_time' => 'Yesterday',
                'timestamp' => strtotime('yesterday 10:15'),
                'first_contact' => '20 Dec 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'Flash sale! 50% off everything today only. Shop now at example.com', 'time' => '10:00 AM'],
                    ['direction' => 'inbound', 'content' => 'STOP', 'time' => '10:15 AM'],
                ],
            ],
            [
                'id' => 'conv_004',
                'phone' => '+447700900444',
                'phone_masked' => '+44 77** ***444',
                'name' => 'Emma Thompson',
                'initials' => 'ET',
                'contact_id' => 'c_003',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'rcs_agent_id' => 'agent_2',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Perfect, see you then!',
                'last_message_time' => 'Yesterday',
                'timestamp' => strtotime('yesterday 15:48'),
                'first_contact' => '01 Nov 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'Reminder: Your appointment is tomorrow at 2pm', 'time' => '3:00 PM'],
                    ['direction' => 'inbound', 'content' => 'Thanks for the reminder!', 'time' => '3:30 PM'],
                    ['direction' => 'inbound', 'content' => 'Can I reschedule to 3pm instead?', 'time' => '3:32 PM'],
                    ['direction' => 'outbound', 'content' => 'Of course! Your appointment has been moved to 3pm.', 'time' => '3:45 PM'],
                    ['direction' => 'inbound', 'content' => 'Perfect, see you then!', 'time' => '3:48 PM'],
                ],
            ],
            [
                'id' => 'conv_005',
                'phone' => '+447700900555',
                'phone_masked' => '+44 77** ***555',
                'name' => 'Michael Brown',
                'initials' => 'MB',
                'contact_id' => 'c_004',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'sender_id' => 'sid_4',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Your refund has been processed.',
                'last_message_time' => '2 days ago',
                'timestamp' => strtotime('-2 days 16:30'),
                'first_contact' => '05 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'I need to return my order', 'time' => '11:00 AM'],
                    ['direction' => 'outbound', 'content' => 'Sorry to hear that. What\'s the issue?', 'time' => '11:15 AM'],
                    ['direction' => 'inbound', 'content' => 'Wrong size', 'time' => '11:20 AM'],
                    ['direction' => 'outbound', 'content' => 'No problem. Please return to our store or use the prepaid label in your package.', 'time' => '11:25 AM'],
                    ['direction' => 'inbound', 'content' => 'Done, dropped it off today', 'time' => '2:00 PM'],
                    ['direction' => 'outbound', 'content' => 'Your refund has been processed.', 'time' => '4:30 PM'],
                ],
            ],
            [
                'id' => 'conv_006',
                'phone' => '+447700900666',
                'phone_masked' => '+44 77** ***666',
                'name' => 'Sophie Brown',
                'initials' => 'SB',
                'contact_id' => 'c_005',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'rcs_agent_id' => 'agent_1',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Can I reschedule to next week?',
                'last_message_time' => '15 Dec',
                'timestamp' => strtotime('-14 days 11:00'),
                'first_contact' => '01 Dec 2024',
                'messages' => [
                    [
                        'direction' => 'outbound',
                        'type' => 'rich_card',
                        'time' => '15 Dec 09:00',
                        'rich_card' => [
                            'image' => '/images/placeholder-newsletter.jpg',
                            'title' => 'Weekly Newsletter',
                            'description' => 'Important updates about upcoming events and school closures.',
                            'button' => 'Read More',
                        ],
                        'caption' => 'School newsletter for this week',
                    ],
                    ['direction' => 'inbound', 'content' => 'Can I reschedule to next week?', 'time' => '15 Dec 11:00'],
                ],
            ],
            // Additional conversations for filter/sort testing
            [
                'id' => 'conv_007',
                'phone' => '+447700900777',
                'phone_masked' => '+44 77** ***777',
                'name' => 'Alice Henderson',
                'initials' => 'AH',
                'contact_id' => 'c_006',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => true,
                'unread_count' => 3,
                'last_message' => 'Is my package still on the way?',
                'last_message_time' => '3 days ago',
                'timestamp' => strtotime('-3 days 11:45'),
                'first_contact' => '18 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Is my package still on the way?', 'time' => '11:45 AM', 'date' => '3 days ago'],
                ],
            ],
            [
                'id' => 'conv_008',
                'phone' => '+447700900888',
                'phone_masked' => '+44 77** ***888',
                'name' => 'Benjamin Clarke',
                'initials' => 'BC',
                'contact_id' => 'c_007',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Thanks for the quick response!',
                'last_message_time' => '3 days ago',
                'timestamp' => strtotime('-3 days 14:20'),
                'first_contact' => '10 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Thanks for the quick response!', 'time' => '2:20 PM'],
                ],
            ],
            [
                'id' => 'conv_009',
                'phone' => '+447700900999',
                'phone_masked' => '+44 77** ***999',
                'name' => 'Charlotte Davies',
                'initials' => 'CD',
                'contact_id' => 'c_008',
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => true,
                'unread_count' => 1,
                'last_message' => 'Can you call me back please?',
                'last_message_time' => '4 days ago',
                'timestamp' => strtotime('-4 days 08:22'),
                'first_contact' => '22 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Can you call me back please?', 'time' => '8:22 AM', 'date' => '4 days ago'],
                ],
            ],
            [
                'id' => 'conv_010',
                'phone' => '+447700901010',
                'phone_masked' => '+44 77** ***010',
                'name' => 'Daniel Evans',
                'initials' => 'DE',
                'contact_id' => 'c_009',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Order received, thank you!',
                'last_message_time' => '4 days ago',
                'timestamp' => strtotime('-4 days 09:30'),
                'first_contact' => '05 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Order received, thank you!', 'time' => '9:30 AM'],
                ],
            ],
            [
                'id' => 'conv_011',
                'phone' => '+447700901111',
                'phone_masked' => '+44 77** ***111',
                'name' => 'Eleanor Foster',
                'initials' => 'EF',
                'contact_id' => 'c_010',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Perfect, I\'ll be there at 10am',
                'last_message_time' => '5 days ago',
                'timestamp' => strtotime('-5 days 16:45'),
                'first_contact' => '01 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Perfect, I\'ll be there at 10am', 'time' => '4:45 PM'],
                ],
            ],
            [
                'id' => 'conv_012',
                'phone' => '+447700901212',
                'phone_masked' => '+44 77** ***212',
                'name' => 'Frederick Grant',
                'initials' => 'FG',
                'contact_id' => 'c_011',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => true,
                'unread_count' => 2,
                'last_message' => 'Where is my refund?',
                'last_message_time' => '5 days ago',
                'timestamp' => strtotime('-5 days 07:55'),
                'first_contact' => '15 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Where is my refund?', 'time' => '7:55 AM', 'date' => 'Today'],
                ],
            ],
            [
                'id' => 'conv_013',
                'phone' => '+447700901313',
                'phone_masked' => '+44 77** ***313',
                'name' => 'Georgia Harris',
                'initials' => 'GH',
                'contact_id' => 'c_012',
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Appointment confirmed for Friday',
                'last_message_time' => '1 week ago',
                'timestamp' => strtotime('-7 days 11:00'),
                'first_contact' => '28 Nov 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'Appointment confirmed for Friday', 'time' => '11:00 AM'],
                ],
            ],
            [
                'id' => 'conv_014',
                'phone' => '+447700901414',
                'phone_masked' => '+44 77** ***414',
                'name' => 'Henry Irving',
                'initials' => 'HI',
                'contact_id' => 'c_013',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Got it, thanks!',
                'last_message_time' => '6 days ago',
                'timestamp' => strtotime('-6 days 13:15'),
                'first_contact' => '20 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Got it, thanks!', 'time' => '1:15 PM'],
                ],
            ],
            [
                'id' => 'conv_015',
                'phone' => '+447700901515',
                'phone_masked' => '+44 77** ***515',
                'name' => 'Isabelle Jones',
                'initials' => 'IJ',
                'contact_id' => 'c_014',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => true,
                'unread_count' => 1,
                'last_message' => 'Do you have this in blue?',
                'last_message_time' => '12:10 PM',
                'timestamp' => strtotime('today 12:10'),
                'first_contact' => '19 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Do you have this in blue?', 'time' => '12:10 PM', 'date' => 'Today'],
                ],
            ],
            [
                'id' => 'conv_016',
                'phone' => '+447700901616',
                'phone_masked' => '+44 77** ***616',
                'name' => 'Jack Kelly',
                'initials' => 'JK',
                'contact_id' => 'c_015',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'I\'ll check and get back to you',
                'last_message_time' => '2 weeks ago',
                'timestamp' => strtotime('-14 days 10:30'),
                'first_contact' => '01 Nov 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'I\'ll check and get back to you', 'time' => '10:30 AM'],
                ],
            ],
            [
                'id' => 'conv_017',
                'phone' => '+447700901717',
                'phone_masked' => '+44 77** ***717',
                'name' => 'Katie Lewis',
                'initials' => 'KL',
                'contact_id' => 'c_016',
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Thanks for letting me know',
                'last_message_time' => '8 days ago',
                'timestamp' => strtotime('-8 days 15:20'),
                'first_contact' => '10 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Thanks for letting me know', 'time' => '3:20 PM'],
                ],
            ],
            [
                'id' => 'conv_018',
                'phone' => '+447700901818',
                'phone_masked' => '+44 77** ***818',
                'name' => 'Liam Morgan',
                'initials' => 'LM',
                'contact_id' => 'c_017',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => true,
                'unread_count' => 4,
                'last_message' => 'URGENT: Need to speak to someone now',
                'last_message_time' => '06:30 AM',
                'timestamp' => strtotime('today 06:30'),
                'first_contact' => '25 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'URGENT: Need to speak to someone now', 'time' => '6:30 AM', 'date' => 'Today'],
                ],
            ],
            [
                'id' => 'conv_019',
                'phone' => '+447700901919',
                'phone_masked' => '+44 77** ***919',
                'name' => 'Mia Nelson',
                'initials' => 'MN',
                'contact_id' => 'c_018',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Wonderful, looking forward to it',
                'last_message_time' => '9 days ago',
                'timestamp' => strtotime('-9 days 12:00'),
                'first_contact' => '15 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Wonderful, looking forward to it', 'time' => '12:00 PM'],
                ],
            ],
            [
                'id' => 'conv_020',
                'phone' => '+447700902020',
                'phone_masked' => '+44 77** ***020',
                'name' => 'Noah Owen',
                'initials' => 'NO',
                'contact_id' => 'c_019',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Payment received',
                'last_message_time' => '10 days ago',
                'timestamp' => strtotime('-10 days 14:45'),
                'first_contact' => '01 Dec 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'Payment received', 'time' => '2:45 PM'],
                ],
            ],
            [
                'id' => 'conv_021',
                'phone' => '+447700902121',
                'phone_masked' => '+44 77** ***121',
                'name' => 'Olivia Parker',
                'initials' => 'OP',
                'contact_id' => 'c_020',
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => true,
                'unread_count' => 1,
                'last_message' => 'Is the store open on Boxing Day?',
                'last_message_time' => 'Yesterday',
                'timestamp' => strtotime('yesterday 18:30'),
                'first_contact' => '20 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Is the store open on Boxing Day?', 'time' => '6:30 PM', 'date' => 'Yesterday'],
                ],
            ],
            [
                'id' => 'conv_022',
                'phone' => '+447700902222',
                'phone_masked' => '+44 77** ***222',
                'name' => '+44 7700 902222',
                'initials' => '??',
                'contact_id' => null,
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'INFO',
                'last_message_time' => '11 days ago',
                'timestamp' => strtotime('-11 days 09:00'),
                'first_contact' => '15 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'INFO', 'time' => '9:00 AM'],
                ],
            ],
            [
                'id' => 'conv_023',
                'phone' => '+447700902323',
                'phone_masked' => '+44 77** ***323',
                'name' => 'Peter Quinn',
                'initials' => 'PQ',
                'contact_id' => 'c_021',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'That works for me',
                'last_message_time' => '12 days ago',
                'timestamp' => strtotime('-12 days 16:00'),
                'first_contact' => '10 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'That works for me', 'time' => '4:00 PM'],
                ],
            ],
            [
                'id' => 'conv_024',
                'phone' => '+447700902424',
                'phone_masked' => '+44 77** ***424',
                'name' => 'Quinn Roberts',
                'initials' => 'QR',
                'contact_id' => 'c_022',
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Delivery confirmed',
                'last_message_time' => '13 days ago',
                'timestamp' => strtotime('-13 days 11:30'),
                'first_contact' => '05 Dec 2024',
                'messages' => [
                    ['direction' => 'outbound', 'content' => 'Delivery confirmed', 'time' => '11:30 AM'],
                ],
            ],
            [
                'id' => 'conv_025',
                'phone' => '+447700902525',
                'phone_masked' => '+44 77** ***525',
                'name' => 'Rachel Smith',
                'initials' => 'RS',
                'contact_id' => 'c_023',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => true,
                'unread_count' => 2,
                'last_message' => 'Why hasn\'t anyone replied?',
                'last_message_time' => 'Yesterday',
                'timestamp' => strtotime('yesterday 20:15'),
                'first_contact' => '18 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Why hasn\'t anyone replied?', 'time' => '8:15 PM', 'date' => 'Yesterday'],
                ],
            ],
            [
                'id' => 'conv_026',
                'phone' => '+447700902626',
                'phone_masked' => '+44 77** ***626',
                'name' => 'Samuel Taylor',
                'initials' => 'ST',
                'contact_id' => 'c_024',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'See you next week',
                'last_message_time' => '2 weeks ago',
                'timestamp' => strtotime('-14 days 17:45'),
                'first_contact' => '01 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'See you next week', 'time' => '5:45 PM'],
                ],
            ],
            [
                'id' => 'conv_027',
                'phone' => '+447700902727',
                'phone_masked' => '+44 77** ***727',
                'name' => 'Tina Underwood',
                'initials' => 'TU',
                'contact_id' => 'c_025',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Confirmed, thank you',
                'last_message_time' => '15 days ago',
                'timestamp' => strtotime('-15 days 10:00'),
                'first_contact' => '25 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Confirmed, thank you', 'time' => '10:00 AM'],
                ],
            ],
            [
                'id' => 'conv_028',
                'phone' => '+447700902828',
                'phone_masked' => '+44 77** ***828',
                'name' => '+44 7700 902828',
                'initials' => '??',
                'contact_id' => null,
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => true,
                'unread_count' => 1,
                'last_message' => 'HELP',
                'last_message_time' => 'Yesterday',
                'timestamp' => strtotime('yesterday 22:00'),
                'first_contact' => '26 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'HELP', 'time' => '10:00 PM', 'date' => 'Yesterday'],
                ],
            ],
            [
                'id' => 'conv_029',
                'phone' => '+447700902929',
                'phone_masked' => '+44 77** ***929',
                'name' => 'Uma Vance',
                'initials' => 'UV',
                'contact_id' => 'c_026',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Great service as always',
                'last_message_time' => '16 days ago',
                'timestamp' => strtotime('-16 days 14:30'),
                'first_contact' => '20 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Great service as always', 'time' => '2:30 PM'],
                ],
            ],
            [
                'id' => 'conv_030',
                'phone' => '+447700903030',
                'phone_masked' => '+44 77** ***030',
                'name' => 'Victor Watson',
                'initials' => 'VW',
                'contact_id' => 'c_027',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Will do, cheers',
                'last_message_time' => '17 days ago',
                'timestamp' => strtotime('-17 days 09:15'),
                'first_contact' => '15 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Will do, cheers', 'time' => '9:15 AM'],
                ],
            ],
            [
                'id' => 'conv_031',
                'phone' => '+447700903131',
                'phone_masked' => '+44 77** ***131',
                'name' => 'Wendy Xavier',
                'initials' => 'WX',
                'contact_id' => 'c_028',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => true,
                'unread_count' => 1,
                'last_message' => 'What time do you close?',
                'last_message_time' => '01:15 PM',
                'timestamp' => strtotime('today 13:15'),
                'first_contact' => '22 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'What time do you close?', 'time' => '1:15 PM', 'date' => 'Today'],
                ],
            ],
            [
                'id' => 'conv_032',
                'phone' => '+447700903232',
                'phone_masked' => '+44 77** ***232',
                'name' => 'Xander Young',
                'initials' => 'XY',
                'contact_id' => 'c_029',
                'channel' => 'sms',
                'source' => '+447700900100',
                'source_type' => 'vmn',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'No problem at all',
                'last_message_time' => '18 days ago',
                'timestamp' => strtotime('-18 days 16:20'),
                'first_contact' => '10 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'No problem at all', 'time' => '4:20 PM'],
                ],
            ],
            [
                'id' => 'conv_033',
                'phone' => '+447700903333',
                'phone_masked' => '+44 77** ***333',
                'name' => 'Yara Zane',
                'initials' => 'YZ',
                'contact_id' => 'c_030',
                'channel' => 'rcs',
                'source' => 'RetailBot',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Perfect, thanks for confirming',
                'last_message_time' => '19 days ago',
                'timestamp' => strtotime('-19 days 11:45'),
                'first_contact' => '05 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Perfect, thanks for confirming', 'time' => '11:45 AM'],
                ],
            ],
            [
                'id' => 'conv_034',
                'phone' => '+447700903434',
                'phone_masked' => '+44 77** ***434',
                'name' => 'Zoe Adams',
                'initials' => 'ZA',
                'contact_id' => 'c_031',
                'channel' => 'sms',
                'source' => '60777',
                'source_type' => 'shortcode',
                'unread' => true,
                'unread_count' => 2,
                'last_message' => 'Still waiting for my order!',
                'last_message_time' => '02:30 PM',
                'timestamp' => strtotime('today 14:30'),
                'first_contact' => '23 Dec 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Still waiting for my order!', 'time' => '2:30 PM', 'date' => 'Today'],
                ],
            ],
            [
                'id' => 'conv_035',
                'phone' => '+447700903535',
                'phone_masked' => '+44 77** ***535',
                'name' => 'Aaron Baker',
                'initials' => 'AB',
                'contact_id' => 'c_032',
                'channel' => 'rcs',
                'source' => 'QuickSMS Brand',
                'source_type' => 'rcs_agent',
                'unread' => false,
                'unread_count' => 0,
                'last_message' => 'Got the package today',
                'last_message_time' => '20 days ago',
                'timestamp' => strtotime('-20 days 15:00'),
                'first_contact' => '01 Nov 2024',
                'messages' => [
                    ['direction' => 'inbound', 'content' => 'Got the package today', 'time' => '3:00 PM'],
                ],
            ],
        ];

        $unread_count = collect($conversations)->where('unread', true)->count();

        // Calculate awaiting_reply_48h for each conversation
        // Logic: unread AND timestamp older than 48 hours
        $now = time();
        $fortyEightHours = 48 * 60 * 60; // 48 hours in seconds
        
        foreach ($conversations as &$conv) {
            $conv['awaiting_reply_48h'] = false;
            
            // Only flag if conversation is unread AND over 48 hours old
            $isUnread = isset($conv['unread']) && $conv['unread'] === true;
            
            if ($isUnread && isset($conv['timestamp'])) {
                $timeDiff = $now - $conv['timestamp'];
                $conv['awaiting_reply_48h'] = $timeDiff >= $fortyEightHours;
            }
        }
        unset($conv); // Break reference

        return view('quicksms.messages.inbox', [
            'page_title' => 'Inbox',
            'conversations' => $conversations,
            'unread_count' => $unread_count,
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents,
            'templates' => $templates,
        ]);
    }

    public function campaignHistory(Request $request)
    {
        $accountId = session('customer_tenant_id');

        $typeToChannel = [
            'sms' => 'sms_only',
            'rcs_basic' => 'basic_rcs',
            'rcs_single' => 'rich_rcs',
            'rcs_carousel' => 'rich_rcs',
        ];
        $statusMap = [
            'queued' => 'pending',
            'completed' => 'complete',
            'failed' => 'complete',
            'paused' => 'sending',
            'archived' => 'archived',
        ];

        $query = \App\Models\Campaign::withoutGlobalScope('tenant')
            ->where('account_id', $accountId)
            ->where('status', '!=', 'archived')
            ->with(['senderId', 'rcsAgent', 'messageTemplate']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $statuses = explode(',', $request->input('status'));
            $dbStatuses = [];
            $reverseStatusMap = ['pending' => 'queued', 'complete' => 'completed'];
            foreach ($statuses as $s) {
                $dbStatuses[] = $reverseStatusMap[$s] ?? $s;
                if ($s === 'complete') {
                    $dbStatuses[] = 'failed';
                }
                if ($s === 'sending') {
                    $dbStatuses[] = 'paused';
                }
            }
            $query->whereIn('status', array_unique($dbStatuses));
        }

        if ($request->filled('channel')) {
            $channels = explode(',', $request->input('channel'));
            $channelToType = ['sms_only' => ['sms'], 'basic_rcs' => ['rcs_basic'], 'rich_rcs' => ['rcs_single', 'rcs_carousel']];
            $dbTypes = [];
            foreach ($channels as $ch) {
                if (isset($channelToType[$ch])) {
                    $dbTypes = array_merge($dbTypes, $channelToType[$ch]);
                }
            }
            if (!empty($dbTypes)) {
                $query->whereIn('type', array_unique($dbTypes));
            }
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from') . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        $paginated = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        $campaigns = $paginated->getCollection()->map(function ($c) use ($typeToChannel, $statusMap) {
            $sendDate = $c->scheduled_at ?? $c->started_at ?? $c->created_at;
            return [
                'id' => $c->id,
                'name' => $c->name,
                'channel' => $typeToChannel[$c->type] ?? 'sms_only',
                'status' => $statusMap[$c->status] ?? $c->status,
                'recipients_total' => $c->total_unique_recipients ?? $c->total_recipients ?? 0,
                'recipients_delivered' => $c->delivered_count,
                'recipients_failed' => $c->failed_count ?? 0,
                'send_date' => $sendDate ? $sendDate->format('Y-m-d H:i') : null,
                'sender_id' => $c->getSenderDisplayName() ?? '-',
                'rcs_agent' => $c->rcsAgent?->name,
                'rcs_agent_logo' => $c->rcsAgent?->logo_url ?? '',
                'rcs_agent_tagline' => $c->rcsAgent?->description ?? '',
                'rcs_agent_brand_color' => $c->rcsAgent?->brand_color ?? '#886CC0',
                'tags' => $c->tags ?? [],
                'template' => $c->messageTemplate?->name,
                'has_tracking' => false,
                'has_optout' => (bool) $c->opt_out_enabled,
                'message_content' => $c->message_content,
                'rcs_content' => $c->rcs_content,
                'estimated_cost' => $c->estimated_cost,
                'actual_cost' => $c->actual_cost,
                'currency' => $c->currency ?? 'GBP',
                'segment_count' => $c->segment_count ?? 1,
                'total_recipients_raw' => $c->total_recipients ?? 0,
                'total_opted_out' => $c->total_opted_out ?? 0,
                'total_invalid' => $c->total_invalid ?? 0,
                'recipient_sources' => $c->recipient_sources,
                'fallback_sms_count' => $c->fallback_sms_count ?? 0,
                'sent_count' => $c->sent_count ?? 0,
                'pending_count' => $c->pending_count ?? 0,
            ];
        })->toArray();

        return view('quicksms.messages.campaign-history', [
            'page_title' => 'Campaign History',
            'campaigns' => $campaigns,
            'paginator' => $paginated,
        ]);
    }
    
    public function campaignApprovals()
    {
        $accountId = session('customer_tenant_id');

        if (!$accountId) {
            return view('quicksms.messages.campaign-approvals', [
                'page_title' => 'Campaign Approvals',
                'pending_approvals' => [],
                'recent_decisions' => [],
            ]);
        }

        $channelLabel = fn($type) => in_array($type, ['rcs_basic', 'rcs_single', 'rcs_carousel']) ? 'RCS' : 'SMS';

        // Pending approvals: draft campaigns from sub-accounts awaiting review
        $pendingApprovals = \App\Models\Campaign::where('account_id', $accountId)
            ->whereIn('status', ['draft', 'scheduled'])
            ->whereNotNull('sub_account_id')
            ->with(['subAccount', 'createdByUser'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'sub_account' => $c->subAccount->name ?? 'Unknown',
                'created_by' => $c->createdByUser->name ?? 'Unknown',
                'message_volume' => $c->total_recipients ?? 0,
                'estimated_cost' => (float) ($c->estimated_cost ?? 0),
                'scheduled_time' => $c->scheduled_at?->format('Y-m-d H:i') ?? 'Immediate',
                'status' => 'pending',
                'channel' => $channelLabel($c->type),
                'created_at' => $c->created_at->format('Y-m-d H:i'),
            ])
            ->toArray();

        // Recent decisions: completed/cancelled campaigns that were from sub-accounts
        $recentDecisions = \App\Models\Campaign::where('account_id', $accountId)
            ->whereIn('status', ['queued', 'sending', 'completed', 'cancelled'])
            ->whereNotNull('sub_account_id')
            ->with(['subAccount', 'createdByUser', 'updatedByUser'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'sub_account' => $c->subAccount->name ?? 'Unknown',
                'created_by' => $c->createdByUser->name ?? 'Unknown',
                'decision' => $c->status === 'cancelled' ? 'rejected' : 'approved',
                'approver' => $c->updatedByUser->name ?? 'System',
                'decided_at' => $c->updated_at->format('Y-m-d H:i'),
                'rejection_reason' => $c->status === 'cancelled' ? ($c->metadata['cancellation_reason'] ?? null) : null,
            ])
            ->toArray();

        return view('quicksms.messages.campaign-approvals', [
            'page_title' => 'Campaign Approvals',
            'pending_approvals' => $pendingApprovals,
            'recent_decisions' => $recentDecisions,
        ]);
    }

    public function contacts()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Contact Book',
            'purpose' => 'Manage your contacts, organize them into lists, and handle opt-out preferences.',
            'sub_modules' => [
                'All Contacts',
                'Lists',
                'Tags',
                'Opt-Out Lists'
            ]
        ]);
    }

    public function allContacts()
    {
        $contacts = Contact::with(['tags', 'lists'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($c) => $c->toPortalArray())
            ->toArray();

        $totalContacts = Contact::count();
        $availableTags = Tag::orderBy('name')->pluck('name')->toArray();
        $availableLists = ContactList::orderBy('name')->pluck('name')->toArray();
        $optOutLists = OptOutList::orderBy('name')->get()->map(fn($o) => ['id' => $o->id, 'name' => $o->name])->toArray();

        return view('quicksms.contacts.all-contacts', [
            'page_title' => 'All Contacts',
            'contacts' => $contacts,
            'total_contacts' => $totalContacts,
            'available_tags' => $availableTags,
            'available_lists' => $availableLists,
            'opt_out_lists' => $optOutLists,
        ]);
    }

    public function lists()
    {
        $allLists = ContactList::orderBy('name')->get();

        $staticLists = $allLists
            ->filter(fn($l) => $l->isStatic())
            ->map(fn($l) => $l->toPortalArray())
            ->values()
            ->toArray();

        $dynamicLists = $allLists
            ->filter(fn($l) => $l->isDynamic())
            ->map(fn($l) => $l->toPortalArray())
            ->values()
            ->toArray();

        $availableContacts = Contact::orderBy('first_name')
            ->limit(100)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => trim($c->first_name . ' ' . $c->last_name),
                'mobile' => $c->mobile_number,
            ])
            ->toArray();

        $availableTags = Tag::orderBy('name')->pluck('name')->toArray();

        return view('quicksms.contacts.lists', [
            'page_title' => 'Lists',
            'static_lists' => $staticLists,
            'dynamic_lists' => $dynamicLists,
            'available_contacts' => $availableContacts,
            'available_tags' => $availableTags,
        ]);
    }

    public function tags()
    {
        $tags = Tag::orderBy('name')
            ->get()
            ->map(fn($t) => $t->toPortalArray())
            ->toArray();

        $availableColors = [
            '#6f42c1' => 'Purple',
            '#0d6efd' => 'Blue',
            '#198754' => 'Green',
            '#fd7e14' => 'Orange',
            '#dc3545' => 'Red',
            '#212529' => 'Black',
            '#20c997' => 'Teal',
            '#6c757d' => 'Gray',
            '#0dcaf0' => 'Cyan',
            '#d63384' => 'Pink',
        ];

        return view('quicksms.contacts.tags', [
            'page_title' => 'Tags',
            'tags' => $tags,
            'available_colors' => $availableColors,
        ]);
    }

    public function optOutLists()
    {
        $optOutLists = OptOutList::orderByDesc('is_master')
            ->orderBy('name')
            ->get()
            ->map(fn($l) => $l->toPortalArray())
            ->toArray();

        $optOuts = OptOutRecord::with('optOutList')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn($r) => $r->toPortalArray())
            ->toArray();

        $totalOptOuts = OptOutRecord::count();

        return view('quicksms.contacts.opt-out-lists', [
            'page_title' => 'Opt-Out Lists',
            'opt_out_lists' => $optOutLists,
            'opt_outs' => $optOuts,
            'total_opt_outs' => $totalOptOuts,
        ]);
    }

    public function reporting()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Reporting',
            'purpose' => 'Access comprehensive reports and analytics for your messaging activities.',
            'sub_modules' => [
                'Dashboard',
                'Message Log',
                'Finance Data',
                'Invoices',
                'Download Area'
            ]
        ]);
    }

    public function reportingDashboard()
    {
        return view('quicksms.reporting.dashboard', [
            'page_title' => 'Reporting Dashboard'
        ]);
    }

    public function messageLog()
    {
        // TODO: Replace with database query - GET /api/messages?page=X&limit=Y&filters=Z
        return view('quicksms.reporting.message-log', [
            'page_title' => 'Message Log'
        ]);
    }

    public function financeData()
    {
        return view('quicksms.reporting.finance-data', [
            'page_title' => 'Finance Data'
        ]);
    }

    public function invoices()
    {
        return view('quicksms.reporting.invoices', [
            'page_title' => 'Invoices'
        ]);
    }

    public function downloadArea()
    {
        return view('quicksms.reporting.download-area', [
            'page_title' => 'Download Area'
        ]);
    }

    public function purchase()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Purchase',
            'purpose' => 'Purchase message credits, packages, and additional services.',
            'sub_modules' => []
        ]);
    }

    public function purchaseMessages()
    {
        $accountId = session('customer_tenant_id');
        $account = \App\Models\Account::withoutGlobalScopes()->find($accountId);
        $productTier = $account ? $account->product_tier : 'starter';

        return view('quicksms.purchase.messages', [
            'page_title' => 'Purchase Messages',
            'account_id' => $accountId,
            'productTier' => $productTier,
        ]);
    }

    public function purchaseNumbers()
    {
        $accountBalance = 0;
        $tenantId = session('customer_tenant_id');
        if ($tenantId) {
            $bal = \DB::table('account_balances')
                ->where('account_id', $tenantId)
                ->value('effective_available');
            $accountBalance = (float) ($bal ?? 0);
        }

        return view('quicksms.purchase.numbers', [
            'page_title' => 'Purchase Numbers',
            'accountBalance' => $accountBalance,
        ]);
    }

    public function management()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Management',
            'purpose' => 'Configure and manage your messaging infrastructure and integrations.',
            'sub_modules' => [
                'RCS Agent Registrations',
                'SMS SenderID Registration',
                'Templates',
                'API Connections',
                'Email-to-SMS',
                'Numbers'
            ]
        ]);
    }

    public function rcsAgentRegistrations()
    {
        // TODO: Replace with Auth::id() when authentication is integrated
        $currentUserId = auth()->id() ?? 1;
        
        return view('quicksms.management.rcs-agent', [
            'page_title' => 'RCS Agent Library',
            'currentUserId' => $currentUserId
        ]);
    }

    public function rcsAgentCreate()
    {
        $tenantId = session('customer_tenant_id');
        $account = Account::find($tenantId);
        $owner = $account ? $account->getOwner() : null;

        $companyDefaults = [];
        if ($account) {
            $companyDefaults = [
                'company_name' => $account->company_name ?? '',
                'company_number' => $account->company_number ?? '',
                'company_website' => $account->website ?? '',
                'sector' => $account->business_sector ?? '',
                'address_line1' => $account->address_line1 ?? '',
                'address_line2' => $account->address_line2 ?? '',
                'city' => $account->city ?? '',
                'post_code' => $account->postcode ?? '',
                'country' => $account->country ?? '',
            ];
        }

        $approverDefaults = [];
        if ($owner) {
            $approverDefaults = [
                'name' => trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? '')),
                'job_title' => $owner->job_title ?? '',
                'email' => $owner->email ?? '',
            ];
        }

        return view('quicksms.management.rcs-agent-wizard', [
            'page_title' => 'Register RCS Agent',
            'company_defaults' => $companyDefaults,
            'approver_defaults' => $approverDefaults,
        ]);
    }

    public function rcsAgentEdit(string $uuid)
    {
        $tenantId = session('customer_tenant_id');
        $account = Account::find($tenantId);
        $owner = $account ? $account->getOwner() : null;

        $agent = \App\Models\RcsAgent::where('uuid', $uuid)
            ->where('account_id', $tenantId)
            ->firstOrFail();

        $companyDefaults = [];
        if ($account) {
            $companyDefaults = [
                'company_name' => $account->company_name ?? '',
                'company_number' => $account->company_number ?? '',
                'company_website' => $account->website ?? '',
                'sector' => $account->business_sector ?? '',
                'address_line1' => $account->address_line1 ?? '',
                'address_line2' => $account->address_line2 ?? '',
                'city' => $account->city ?? '',
                'post_code' => $account->postcode ?? '',
                'country' => $account->country ?? '',
            ];
        }

        $approverDefaults = [];
        if ($owner) {
            $approverDefaults = [
                'name' => trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? '')),
                'job_title' => $owner->job_title ?? '',
                'email' => $owner->email ?? '',
            ];
        }

        return view('quicksms.management.rcs-agent-wizard', [
            'page_title' => 'Edit RCS Agent',
            'company_defaults' => $companyDefaults,
            'approver_defaults' => $approverDefaults,
            'editing_agent' => $agent,
        ]);
    }

    public function templates()
    {
        $sender_ids = $this->getApprovedSenderIds();
        $rcs_agents = $this->getRcsAgentsForView();
        $opt_out_lists = $this->getOptOutListsForView();
        $virtual_numbers = [];
        $optout_domains = [];

        $templates = $this->getTemplatesForLibrary();

        return view('quicksms.management.templates', [
            'page_title' => 'Message Templates',
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents,
            'opt_out_lists' => $opt_out_lists,
            'virtual_numbers' => $virtual_numbers,
            'optout_domains' => $optout_domains,
            'templates' => $templates,
        ]);
    }

    public function templateCreateStep1()
    {
        return view('quicksms.management.templates.create-step1', [
            'page_title' => 'Create Template - Metadata',
            'isEditMode' => false,
            'isAdminMode' => false,
            'template' => null
        ]);
    }

    public function templateCreateStep2()
    {
        $sender_ids = $this->getApprovedSenderIds();
        $rcs_agents = $this->getRcsAgentsForView();
        $opt_out_lists = $this->getOptOutListsForView();
        $virtual_numbers = [];
        $optout_domains = [];

        return view('quicksms.management.templates.create-step2', [
            'page_title' => 'Create Template - Content',
            'isEditMode' => false,
            'isAdminMode' => false,
            'template' => null,
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents,
            'opt_out_lists' => $opt_out_lists,
            'virtual_numbers' => $virtual_numbers,
            'optout_domains' => $optout_domains
        ]);
    }

    public function templateCreateStep3()
    {
        $tenantId = session('customer_tenant_id');
        $subAccounts = [];
        if ($tenantId) {
            try {
                \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$tenantId]);
                $subAccounts = \App\Models\SubAccount::where('account_id', $tenantId)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get()
                    ->toArray();
            } catch (\Exception $e) {}
        }

        return view('quicksms.management.templates.create-step3', [
            'page_title' => 'Create Template - Settings',
            'isEditMode' => false,
            'isAdminMode' => false,
            'template' => null,
            'sub_accounts' => $subAccounts,
        ]);
    }

    public function templateCreateReview()
    {
        $tenantId = session('customer_tenant_id');
        $subAccounts = [];
        if ($tenantId) {
            try {
                \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$tenantId]);
                $subAccounts = \App\Models\SubAccount::where('account_id', $tenantId)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get()
                    ->toArray();
            } catch (\Exception $e) {}
        }

        return view('quicksms.management.templates.create-review', [
            'page_title' => 'Create Template - Review',
            'isEditMode' => false,
            'isAdminMode' => false,
            'template' => null,
            'sub_accounts' => $subAccounts,
        ]);
    }

    public function templateEditStep1($templateId)
    {
        $template = $this->getTemplateForEdit($templateId);
        if (!$template) {
            return redirect()->route('management.templates')->with('error', 'Template not found.');
        }

        return view('quicksms.management.templates.create-step1', [
            'page_title' => 'Edit Template - Metadata',
            'isEditMode' => true,
            'isAdminMode' => false,
            'templateId' => $templateId,
            'template' => $template
        ]);
    }

    public function templateEditStep2($templateId)
    {
        $sender_ids = $this->getApprovedSenderIds();
        $rcs_agents = $this->getRcsAgentsForView();
        $opt_out_lists = $this->getOptOutListsForView();
        $virtual_numbers = [];
        $optout_domains = [];

        $template = $this->getTemplateForEdit($templateId);
        if (!$template) {
            return redirect()->route('management.templates')->with('error', 'Template not found.');
        }

        return view('quicksms.management.templates.create-step2', [
            'page_title' => 'Edit Template - Content',
            'isEditMode' => true,
            'isAdminMode' => false,
            'templateId' => $templateId,
            'template' => $template,
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents,
            'opt_out_lists' => $opt_out_lists,
            'virtual_numbers' => $virtual_numbers,
            'optout_domains' => $optout_domains
        ]);
    }

    public function templateEditStep3($templateId)
    {
        $template = $this->getTemplateForEdit($templateId);
        if (!$template) {
            return redirect()->route('management.templates')->with('error', 'Template not found.');
        }

        $tenantId = session('customer_tenant_id');
        $subAccounts = [];
        if ($tenantId) {
            try {
                \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$tenantId]);
                $subAccounts = \App\Models\SubAccount::where('account_id', $tenantId)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get()
                    ->toArray();
            } catch (\Exception $e) {}
        }

        return view('quicksms.management.templates.create-step3', [
            'page_title' => 'Edit Template - Settings',
            'isEditMode' => true,
            'isAdminMode' => false,
            'templateId' => $templateId,
            'template' => $template,
            'sub_accounts' => $subAccounts,
        ]);
    }

    public function templateEditReview($templateId)
    {
        $template = $this->getTemplateForEdit($templateId);
        if (!$template) {
            return redirect()->route('management.templates')->with('error', 'Template not found.');
        }

        $tenantId = session('customer_tenant_id');
        $subAccounts = [];
        if ($tenantId) {
            try {
                \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$tenantId]);
                $subAccounts = \App\Models\SubAccount::where('account_id', $tenantId)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get()
                    ->toArray();
            } catch (\Exception $e) {}
        }

        return view('quicksms.management.templates.create-review', [
            'page_title' => 'Edit Template - Review',
            'isEditMode' => true,
            'isAdminMode' => false,
            'templateId' => $templateId,
            'template' => $template,
            'sub_accounts' => $subAccounts,
        ]);
    }

    public function adminTemplateEditStep1($accountId, $templateId)
    {
        // TODO: Replace with API call - templatesService.getTemplate(templateId)
        $template = $this->getMockTemplate($templateId);
        
        // TODO: Replace with API call - accountsService.getAccount(accountId)
        $account = [
            'id' => $accountId,
            'name' => 'Acme Corp'
        ];

        return view('quicksms.management.templates.create-step1', [
            'page_title' => 'Edit Template - Metadata',
            'isEditMode' => true,
            'isAdminMode' => true,
            'templateId' => $templateId,
            'accountId' => $accountId,
            'account' => $account,
            'template' => $template
        ]);
    }

    public function adminTemplateEditStep2($accountId, $templateId)
    {
        $approvedIds = SenderId::where('account_id', $accountId)
            ->where('workflow_status', 'approved')
            ->orderByDesc('is_default')
            ->orderBy('sender_id_value')
            ->get();

        $sender_ids = $approvedIds->isEmpty()
            ? [['id' => 0, 'name' => 'QuickSMS', 'type' => 'alphanumeric']]
            : $approvedIds->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->sender_id_value,
                'type' => strtolower($s->sender_type === 'ALPHA' ? 'alphanumeric' : ($s->sender_type === 'NUMERIC' ? 'numeric' : 'shortcode')),
            ])->toArray();

        $rcs_agents = \App\Models\RcsAgent::where('account_id', $accountId)
            ->where('workflow_status', 'approved')
            ->select('uuid', 'name', 'description', 'brand_color', 'logo_url')
            ->get()
            ->map(fn($a) => [
                'id'          => $a->uuid,
                'name'        => $a->name,
                'logo'        => $a->logo_url ?: null,
                'tagline'     => $a->description ?? '',
                'brand_color' => $a->brand_color ?? '#886CC0',
                'status'      => 'approved',
            ])
            ->toArray();

        $template = $this->getMockTemplate($templateId);

        $accountModel = \App\Models\Account::withoutGlobalScope('tenant')->find($accountId);
        $account = $accountModel ? ['id' => $accountModel->id, 'name' => $accountModel->company_name ?? $accountModel->trading_name ?? 'Unknown'] : ['id' => $accountId, 'name' => 'Unknown'];

        return view('quicksms.management.templates.create-step2', [
            'page_title' => 'Edit Template - Content',
            'isEditMode' => true,
            'isAdminMode' => true,
            'templateId' => $templateId,
            'accountId' => $accountId,
            'account' => $account,
            'template' => $template,
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents
        ]);
    }

    public function adminTemplateEditStep3($accountId, $templateId)
    {
        // TODO: Replace with API call - templatesService.getTemplate(templateId)
        $template = $this->getMockTemplate($templateId);
        
        // TODO: Replace with API call - accountsService.getAccount(accountId)
        $account = [
            'id' => $accountId,
            'name' => 'Acme Corp'
        ];

        return view('quicksms.management.templates.create-step3', [
            'page_title' => 'Edit Template - Settings',
            'isEditMode' => true,
            'isAdminMode' => true,
            'templateId' => $templateId,
            'accountId' => $accountId,
            'account' => $account,
            'template' => $template
        ]);
    }

    public function adminTemplateEditReview($accountId, $templateId)
    {
        // TODO: Replace with API call - templatesService.getTemplate(templateId)
        $template = $this->getMockTemplate($templateId);
        
        // TODO: Replace with API call - accountsService.getAccount(accountId)
        $account = [
            'id' => $accountId,
            'name' => 'Acme Corp'
        ];

        return view('quicksms.management.templates.create-review', [
            'page_title' => 'Edit Template - Review',
            'isEditMode' => true,
            'isAdminMode' => true,
            'templateId' => $templateId,
            'accountId' => $accountId,
            'account' => $account,
            'template' => $template
        ]);
    }

    private function getTemplateForEdit($templateId)
    {
        $typeToChannel = [
            'sms' => 'sms',
            'rcs_basic' => 'basic_rcs',
            'rcs_single' => 'rich_rcs',
            'rcs_carousel' => 'rich_rcs',
        ];

        $t = \App\Models\MessageTemplate::find($templateId);
        if (!$t) {
            return null;
        }

        return [
            'id' => $t->id,
            'name' => $t->name,
            'templateId' => $t->id,
            'trigger' => $t->trigger_type ?? 'portal',
            'channel' => $typeToChannel[$t->type] ?? 'sms',
            'type' => $t->type,
            'content' => $t->content ?? '',
            'description' => $t->description ?? '',
            'senderId' => $t->sender_id_id ?? '',
            'rcsAgent' => $t->rcs_agent_id ?? '',
            'trackableLink' => (bool) ($t->trackable_link_enabled ?? false),
            'trackableLinkDomain' => $t->trackable_link_domain ?? '',
            'optOut' => (bool) ($t->opt_out_enabled ?? false),
            'optOutMethod' => $t->opt_out_method ?? '',
            'optOutNumberId' => $t->opt_out_number_id ?? '',
            'optOutKeyword' => $t->opt_out_keyword ?? '',
            'optOutText' => $t->opt_out_text ?? '',
            'optOutListId' => $t->opt_out_list_id ?? '',
            'optOutUrlEnabled' => (bool) ($t->opt_out_url_enabled ?? false),
            'optOutScreeningListIds' => $t->opt_out_screening_list_ids ?? [],
            'messageExpiry' => (bool) ($t->message_expiry_enabled ?? false),
            'messageExpiryHours' => $t->message_expiry_hours ?? null,
            'socialHoursEnabled' => (bool) ($t->social_hours_enabled ?? false),
            'socialHoursFrom' => $t->social_hours_from ?? '',
            'socialHoursTo' => $t->social_hours_to ?? '',
            'rcs_content' => $t->rcs_content,
            'status' => $t->status,
            'sub_account_id' => $t->sub_account_id,
        ];
    }

    public function apiConnections()
    {
        $tenantId = session('customer_tenant_id');
        $subAccounts = $tenantId
            ? \App\Models\SubAccount::where('account_id', $tenantId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
            : collect();

        return view('quicksms.management.api-connections', [
            'page_title' => 'API Connections',
            'subAccounts' => $subAccounts,
        ]);
    }

    public function apiConnectionCreate()
    {
        $tenantId = session('customer_tenant_id');
        $subAccounts = $tenantId
            ? \App\Models\SubAccount::where('account_id', $tenantId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
            : collect();

        return view('quicksms.management.api-connection-wizard', [
            'page_title' => 'Create API Connection',
            'subAccounts' => $subAccounts,
        ]);
    }

    public function emailToSms()
    {
        return view('quicksms.management.email-to-sms', [
            'page_title' => 'Email-to-SMS'
        ]);
    }

    public function emailToSmsCreateMapping()
    {
        return view('quicksms.management.email-to-sms-mapping-wizard', [
            'page_title' => 'Create Email-to-SMS Mapping'
        ]);
    }

    public function emailToSmsStandardCreate()
    {
        return view('quicksms.management.email-to-sms-standard-wizard', [
            'page_title' => 'Create Standard Email-to-SMS'
        ]);
    }

    public function emailToSmsStandardEdit($id)
    {
        return view('quicksms.management.standard-email-to-sms-form', [
            'page_title' => 'Edit Standard Email-to-SMS',
            'id' => $id
        ]);
    }

    public function emailToSmsContactListEdit($id)
    {
        return view('quicksms.management.email-to-sms-mapping-wizard', [
            'page_title' => 'Edit Email-to-SMS Mapping',
            'id' => $id,
            'editMode' => true
        ]);
    }

    public function numbers()
    {
        $tenantId = session('customer_tenant_id');
        $subAccounts = [];
        if ($tenantId) {
            $subAccounts = \DB::table('sub_accounts')
                ->where('account_id', $tenantId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->toArray();
        }

        return view('quicksms.management.numbers', [
            'page_title' => 'Numbers',
            'subAccounts' => $subAccounts,
        ]);
    }

    public function numbersConfigure(Request $request)
    {
        // Get selected number IDs from query string
        $selectedIds = $request->query('ids', '');
        
        return view('quicksms.management.numbers-configure', [
            'page_title' => 'Configure Numbers',
            'selectedIds' => $selectedIds
        ]);
    }

    public function myProfile()
    {
        $userId = session('customer_user_id');
        $tenantId = session('customer_tenant_id');

        if (!$userId || !$tenantId) {
            return redirect()->route('auth.login');
        }

        $user = \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $userId)
            ->where('tenant_id', $tenantId)
            ->first();

        $account = \Illuminate\Support\Facades\DB::table('accounts')
            ->where('id', $tenantId)
            ->first();

        if (!$user) {
            session()->flush();
            return redirect()->route('auth.login');
        }

        $securityEvents = \Illuminate\Support\Facades\DB::table('auth_audit_log')
            ->where('actor_id', $userId)
            ->where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($event) {
                $iconMap = [
                    'login_success' => ['icon' => 'fa-sign-in-alt', 'color' => 'success', 'label' => 'Successful login'],
                    'login_failed' => ['icon' => 'fa-exclamation-triangle', 'color' => 'warning', 'label' => 'Failed login attempt'],
                    'password_changed' => ['icon' => 'fa-key', 'color' => 'info', 'label' => 'Password changed'],
                    'mfa_verified' => ['icon' => 'fa-shield-alt', 'color' => 'primary', 'label' => 'MFA verified'],
                    'mfa_enabled' => ['icon' => 'fa-shield-alt', 'color' => 'success', 'label' => 'MFA enabled'],
                    'mfa_disabled' => ['icon' => 'fa-shield-alt', 'color' => 'danger', 'label' => 'MFA disabled'],
                    'profile_updated' => ['icon' => 'fa-user-edit', 'color' => 'info', 'label' => 'Profile updated'],
                    'logout' => ['icon' => 'fa-sign-out-alt', 'color' => 'secondary', 'label' => 'Logged out'],
                ];
                $mapped = $iconMap[$event->event_type] ?? ['icon' => 'fa-circle', 'color' => 'secondary', 'label' => ucfirst(str_replace('_', ' ', $event->event_type ?? 'Unknown'))];
                return [
                    'date' => $event->created_at ? \Carbon\Carbon::parse($event->created_at)->format('d M Y, H:i') : 'N/A',
                    'event' => $mapped['label'],
                    'ip' => $event->ip_address ?? 'N/A',
                    'icon' => $mapped['icon'],
                    'color' => $mapped['color'],
                ];
            })
            ->toArray();

        $roleLabels = [
            'owner' => 'Account Owner',
            'admin' => 'Account Administrator',
            'manager' => 'Manager',
            'user' => 'Standard User',
            'viewer' => 'Viewer',
        ];

        return view('quicksms.my-profile', [
            'page_title' => 'My Profile',
            'user' => $user,
            'account' => $account,
            'securityEvents' => $securityEvents,
            'roleLabel' => $roleLabels[$user->role ?? 'user'] ?? ucfirst($user->role ?? 'User'),
        ]);
    }

    public function saveProfile(Request $request)
    {
        $userId = session('customer_user_id');
        $tenantId = session('customer_tenant_id');

        if (!$userId || !$tenantId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $user = \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $userId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $firstName = trim($request->input('first_name', ''));
        $lastName = trim($request->input('last_name', ''));
        $mobileNumber = trim($request->input('mobile_number', ''));

        if (empty($firstName) || empty($lastName)) {
            return response()->json(['success' => false, 'message' => 'First name and last name are required'], 422);
        }

        if (!empty($mobileNumber) && !preg_match('/^\+[1-9]\d{6,14}$/', str_replace(' ', '', $mobileNumber))) {
            return response()->json(['success' => false, 'message' => 'Invalid mobile number format. Use E.164 format (e.g., +447700900123)'], 422);
        }

        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'mobile_number' => $mobileNumber ?: null,
            'updated_at' => now(),
            'updated_by' => $userId,
        ];

        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $userId)
            ->where('tenant_id', $tenantId)
            ->update($updateData);

        try {
            \Illuminate\Support\Facades\DB::table('auth_audit_log')->insert([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'actor_id' => $userId,
                'actor_email' => $user->email,
                'tenant_id' => $tenantId,
                'event_type' => 'profile_updated',
                'result' => 'success',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => json_encode(['fields_changed' => array_keys($updateData)]),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
        }

        session(['customer_name' => $firstName . ' ' . $lastName]);

        return response()->json(['success' => true, 'message' => 'Profile updated successfully']);
    }

    public function changePassword(Request $request)
    {
        $userId = session('customer_user_id');
        $tenantId = session('customer_tenant_id');

        if (!$userId || !$tenantId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $user = \App\Models\User::withoutGlobalScope('tenant')
            ->where('id', $userId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $currentPassword = $request->input('current_password', '');
        $newPassword = $request->input('new_password', '');
        $confirmPassword = $request->input('confirm_password', '');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return response()->json(['success' => false, 'message' => 'All password fields are required'], 422);
        }

        if (!\Illuminate\Support\Facades\Hash::check($currentPassword, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect'], 422);
        }

        if ($newPassword !== $confirmPassword) {
            return response()->json(['success' => false, 'message' => 'New passwords do not match'], 422);
        }

        if (strlen($newPassword) < 12) {
            return response()->json(['success' => false, 'message' => 'Password must be at least 12 characters'], 422);
        }
        if (!preg_match('/[A-Z]/', $newPassword)) {
            return response()->json(['success' => false, 'message' => 'Password must contain at least one uppercase letter'], 422);
        }
        if (!preg_match('/[a-z]/', $newPassword)) {
            return response()->json(['success' => false, 'message' => 'Password must contain at least one lowercase letter'], 422);
        }
        if (!preg_match('/[0-9]/', $newPassword)) {
            return response()->json(['success' => false, 'message' => 'Password must contain at least one number'], 422);
        }
        if (!preg_match('/[^A-Za-z0-9]/', $newPassword)) {
            return response()->json(['success' => false, 'message' => 'Password must contain at least one special character'], 422);
        }

        if (\Illuminate\Support\Facades\Hash::check($newPassword, $user->password)) {
            return response()->json(['success' => false, 'message' => 'New password cannot be the same as current password'], 422);
        }

        try {
            $user->changePassword(\Illuminate\Support\Facades\Hash::make($newPassword));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        try {
            \Illuminate\Support\Facades\DB::table('auth_audit_log')->insert([
                'id' => \Illuminate\Support\Str::uuid()->toString(),
                'actor_id' => $userId,
                'actor_email' => $user->email,
                'tenant_id' => $tenantId,
                'event_type' => 'password_changed',
                'result' => 'success',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
        }

        return response()->json(['success' => true, 'message' => 'Password changed successfully']);
    }
    
    public function account()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Account',
            'purpose' => 'Manage your account settings, users, and security preferences.',
            'sub_modules' => [
                'Details',
                'Users and Access',
                'Sub Accounts',
                'Audit Logs',
                'Security Settings'
            ]
        ]);
    }

    public function accountDetails()
    {
        $user = null;
        $account = null;
        $userId = session('customer_user_id');
        $tenantId = session('customer_tenant_id');
        if ($userId && $tenantId) {
            $user = \App\Models\User::withoutGlobalScope('tenant')
                ->where('id', $userId)
                ->where('tenant_id', $tenantId)
                ->first();
            if ($user) {
                $account = \App\Models\Account::withoutGlobalScope('tenant')->find($user->tenant_id);
            }
        }

        $approvedTestNumbers = [];
        if ($account && $account->isTestStandard()) {
            $settings = \App\Models\AccountSettings::where('account_id', $tenantId)->first();
            $approvedTestNumbers = $settings->approved_test_numbers ?? [];
        }

        return view('quicksms.account.details', [
            'page_title' => 'Account Details',
            'user' => $user,
            'account' => $account,
            'approved_test_numbers' => $approvedTestNumbers,
        ]);
    }

    public function saveApprovedTestNumbers(Request $request)
    {
        $tenantId = session('customer_tenant_id');
        if (!$tenantId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $account = \App\Models\Account::withoutGlobalScope('tenant')->find($tenantId);
        if (!$account || !$account->isTestStandard()) {
            return response()->json(['error' => 'This feature is only available for Test Standard accounts'], 403);
        }

        $rawNumbers = $request->input('numbers', []);
        if (!is_array($rawNumbers) || count($rawNumbers) > 10 || count($rawNumbers) === 0) {
            return response()->json(['message' => 'Please provide between 1 and 10 numbers'], 422);
        }

        $normalized = [];
        foreach ($rawNumbers as $n) {
            if (!is_string($n)) {
                return response()->json(['message' => 'Invalid input'], 422);
            }
            $n = preg_replace('/[\s\-\(\)]/', '', trim($n));
            if (preg_match('/^0[7]\d{9}$/', $n)) {
                $n = '+44' . substr($n, 1);
            } elseif (preg_match('/^44[7]\d{9}$/', $n)) {
                $n = '+' . $n;
            } elseif (preg_match('/^\+44[7]\d{9}$/', $n)) {
                // already E.164 UK
            } else {
                return response()->json(['message' => 'Invalid number format. Use 07XXX, 447XXX, or +447XXX for UK mobile numbers'], 422);
            }
            if (!preg_match('/^\+447\d{9}$/', $n)) {
                return response()->json(['message' => 'Only UK mobile numbers (+447...) are accepted'], 422);
            }
            $normalized[] = $n;
        }
        $numbers = array_values(array_unique($normalized));

        $settings = \App\Models\AccountSettings::firstOrCreate(
            ['account_id' => $tenantId],
            []
        );

        $settings->approved_test_numbers = $numbers;
        $settings->save();

        return response()->json([
            'success' => true,
            'message' => 'Approved test numbers saved successfully',
            'numbers' => $numbers,
        ]);
    }
    
    public function accountPricingApi(Request $request)
    {
        $tenantId = session('customer_tenant_id');
        if (!$tenantId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $account = \App\Models\Account::withoutGlobalScope('tenant')->find($tenantId);
        if (!$account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        $currentTier = $account->product_tier ?? 'starter';
        $isBespoke = ($currentTier === 'bespoke');

        $services = \App\Models\Billing\ServiceCatalogue::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('display_name')
            ->get();

        $tierPrices = \App\Models\Billing\ProductTierPrice::where('active', true)
            ->whereRaw("valid_from <= CURRENT_DATE")
            ->where(function ($q) {
                $q->whereNull('valid_to')->orWhereRaw("valid_to >= CURRENT_DATE");
            })
            ->get();

        $customerPrices = [];
        if ($isBespoke) {
            $customerPrices = \App\Models\Billing\CustomerPrice::where('account_id', $tenantId)
                ->where('active', true)
                ->whereRaw("valid_from <= CURRENT_DATE")
                ->where(function ($q) {
                    $q->whereNull('valid_to')->orWhereRaw("valid_to >= CURRENT_DATE");
                })
                ->get()
                ->keyBy('product_type')
                ->toArray();
        }

        $starterPrices = $tierPrices->where('product_tier', 'starter')->keyBy('product_type');
        $enterprisePrices = $tierPrices->where('product_tier', 'enterprise')->keyBy('product_type');

        $result = [];
        foreach ($services as $service) {
            $slug = $service->slug;
            $starterPrice = $starterPrices->get($slug);
            $enterprisePrice = $enterprisePrices->get($slug);

            $item = [
                'slug' => $slug,
                'display_name' => $service->display_name,
                'unit_label' => $service->unit_label,
                'display_format' => $service->display_format,
                'decimal_places' => $service->decimal_places ?? 2,
                'is_per_message' => $service->is_per_message,
                'is_recurring' => $service->is_recurring,
                'bespoke_only' => $service->bespoke_only,
                'available_on_starter' => $service->available_on_starter,
                'available_on_enterprise' => $service->available_on_enterprise,
                'starter_price' => $starterPrice ? (float) $starterPrice->unit_price : null,
                'starter_formatted' => $starterPrice ? $service->formatPrice($starterPrice->unit_price) : null,
                'enterprise_price' => $enterprisePrice ? (float) $enterprisePrice->unit_price : null,
                'enterprise_formatted' => $enterprisePrice ? $service->formatPrice($enterprisePrice->unit_price) : null,
            ];

            if ($isBespoke) {
                $cp = $customerPrices[$slug] ?? null;
                $item['bespoke_price'] = $cp ? (float) $cp['unit_price'] : null;
                $item['bespoke_formatted'] = $cp ? $service->formatPrice($cp['unit_price']) : null;
                $item['bespoke_billing_type'] = $cp ? ($cp['billing_type'] ?? 'per_submitted') : null;
            }

            $result[] = $item;
        }

        return response()->json([
            'current_tier' => $currentTier,
            'is_bespoke' => $isBespoke,
            'services' => $result,
        ]);
    }

    public function accountActivate()
    {
        $user = null;
        $account = null;
        $userId = session('customer_user_id');
        $tenantId = session('customer_tenant_id');
        if ($userId && $tenantId) {
            $user = \App\Models\User::withoutGlobalScope('tenant')
                ->where('id', $userId)
                ->where('tenant_id', $tenantId)
                ->first();
            if ($user) {
                $account = \App\Models\Account::withoutGlobalScope('tenant')->find($user->tenant_id);
            }
        }

        return view('quicksms.account.activate', [
            'page_title' => 'Activate Your Account',
            'user' => $user,
            'account' => $account,
        ]);
    }

    public function saveActivation(\Illuminate\Http\Request $request)
    {
        $userId = session('customer_user_id');
        $tenantId = session('customer_tenant_id');

        if (!$userId || !$tenantId) {
            return response()->json(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        $user = \App\Models\User::withoutGlobalScope('tenant')
            ->where('id', $userId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $account = \App\Models\Account::withoutGlobalScope('tenant')
            ->where('id', $user->tenant_id)
            ->first();
        if (!$account || $account->id !== $tenantId) {
            return response()->json(['status' => 'error', 'message' => 'Account not found'], 404);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'company_type' => 'required|string|in:uk_limited,sole_trader,government_nhs,government,other',
            'company_name' => 'required|string|max:255',
            'trading_name' => 'nullable|string|max:255',
            'company_number' => 'nullable|string|max:20',
            'sector' => 'required|string|max:100',
            'website' => 'required|url|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'county' => 'nullable|string|max:100',
            'postcode' => 'required|string|max:20',
            'country' => 'required|string|max:2',
            'billing_email' => 'required|email|max:255',
            'support_email' => 'required|email|max:255',
            'incident_email' => 'required|email|max:255',
            'signatory_name' => 'required|string|max:255',
            'signatory_title' => 'required|string|max:255',
            'signatory_email' => 'required|email|max:255',
            'vat_registered' => 'required|string|in:yes,no',
            'vat_country' => 'nullable|string|max:2',
            'vat_number' => 'nullable|string|max:50',
            'reverse_charges' => 'nullable|string|in:yes,no',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        $companyType = $data['company_type'] === 'government' ? 'government_nhs' : $data['company_type'];

        $account->update([
            'company_type' => $companyType,
            'company_name' => $data['company_name'],
            'trading_name' => $data['trading_name'] ?? null,
            'company_number' => $data['company_number'] ?? null,
            'business_sector' => $data['sector'],
            'website' => $data['website'],
            'address_line1' => $data['address_line1'],
            'address_line2' => $data['address_line2'] ?? null,
            'city' => $data['city'],
            'county' => $data['county'] ?? null,
            'postcode' => $data['postcode'],
            'country' => $data['country'],
            'accounts_billing_email' => $data['billing_email'],
            'support_contact_email' => $data['support_email'],
            'incident_email' => $data['incident_email'],
            'support_contact_name' => $data['signatory_name'],
            'support_contact_phone' => $account->phone ?? '',
            'operations_contact_name' => $data['signatory_name'],
            'operations_contact_email' => $data['support_email'],
            'operations_contact_phone' => $account->phone ?? '',
            'signatory_name' => $data['signatory_name'],
            'signatory_title' => $data['signatory_title'],
            'signatory_email' => $data['signatory_email'],
            'vat_registered' => $data['vat_registered'] === 'yes',
            'vat_number' => $data['vat_number'] ?? null,
            'vat_reverse_charges' => ($data['reverse_charges'] ?? 'no') === 'yes',
            'tax_country' => $data['vat_country'] ?? null,
            'payment_terms' => 'immediate',
            'contract_agreed' => true,
            'contract_signed_at' => now(),
            'contract_signed_ip' => $request->ip(),
        ]);

        $account->updateActivationStatus();

        return response()->json([
            'status' => 'success',
            'message' => 'Account details saved successfully',
            'activation' => $account->getActivationProgress(),
        ]);
    }

    private function getAuthenticatedUserAndAccount()
    {
        $userId = session('customer_user_id');
        $tenantId = session('customer_tenant_id');

        if (!$userId || !$tenantId) {
            return [null, null];
        }

        $user = \App\Models\User::withoutGlobalScope('tenant')
            ->where('id', $userId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$user) {
            return [null, null];
        }

        $account = \App\Models\Account::withoutGlobalScope('tenant')
            ->where('id', $user->tenant_id)
            ->first();

        return [$user, $account];
    }

    public function saveSignUpDetails(\Illuminate\Http\Request $request)
    {
        [$user, $account] = $this->getAuthenticatedUserAndAccount();
        if (!$user || !$account) {
            return response()->json(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'job_title' => 'required|string|max:100',
            'business_name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$user->tenant_id]);

            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'job_title' => $request->job_title,
                'mobile_number' => $request->mobile_number,
            ]);

            $account->update([
                'company_name' => $request->business_name,
                'signup_details_complete' => true,
            ]);

            session(['customer_name' => $request->first_name . ' ' . $request->last_name]);

            return response()->json(['status' => 'success', 'message' => 'Sign up details saved successfully']);
        } catch (\Exception $e) {
            \Log::error('Save signup details error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to save changes'], 500);
        }
    }

    public function saveCompanyInfo(\Illuminate\Http\Request $request)
    {
        [$user, $account] = $this->getAuthenticatedUserAndAccount();
        if (!$user || !$account) {
            return response()->json(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'company_type' => 'required|string|in:uk_limited,sole_trader,government',
            'company_name' => 'required|string|max:255',
            'trading_name' => 'nullable|string|max:255',
            'company_number' => 'nullable|string|max:20',
            'sector' => 'required|string|max:100',
            'website' => 'required|url|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'county' => 'nullable|string|max:100',
            'postcode' => 'required|string|max:20',
            'country' => 'required|string|max:2',
            'operating_same' => 'required|boolean',
            'operating_address_line1' => 'nullable|string|max:255',
            'operating_address_line2' => 'nullable|string|max:255',
            'operating_city' => 'nullable|string|max:100',
            'operating_county' => 'nullable|string|max:100',
            'operating_postcode' => 'nullable|string|max:20',
            'operating_country' => 'nullable|string|max:2',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$user->tenant_id]);

            $companyType = $request->company_type === 'government' ? 'government_nhs' : $request->company_type;

            $updateData = [
                'company_type' => $companyType,
                'company_name' => $request->company_name,
                'trading_name' => $request->trading_name,
                'company_number' => $request->company_number,
                'business_sector' => $request->sector,
                'website' => $request->website,
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2,
                'city' => $request->city,
                'county' => $request->county,
                'postcode' => $request->postcode,
                'country' => $request->country,
                'operating_address_same_as_registered' => $request->operating_same,
            ];

            if (!$request->operating_same) {
                $updateData['operating_address_line1'] = $request->operating_address_line1;
                $updateData['operating_address_line2'] = $request->operating_address_line2;
                $updateData['operating_city'] = $request->operating_city;
                $updateData['operating_county'] = $request->operating_county;
                $updateData['operating_postcode'] = $request->operating_postcode;
                $updateData['operating_country'] = $request->operating_country;
            }

            $updateData['company_info_complete'] = true;
            $account->update($updateData);

            return response()->json(['status' => 'success', 'message' => 'Company information saved successfully']);
        } catch (\Exception $e) {
            \Log::error('Save company info error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to save changes'], 500);
        }
    }

    public function saveSupportOps(\Illuminate\Http\Request $request)
    {
        [$user, $account] = $this->getAuthenticatedUserAndAccount();
        if (!$user || !$account) {
            return response()->json(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'billing_email' => 'required|email|max:255',
            'support_email' => 'required|email|max:255',
            'incident_email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$user->tenant_id]);

            $account->update([
                'accounts_billing_email' => $request->billing_email,
                'support_contact_email' => $request->support_email,
                'incident_email' => $request->incident_email,
                'support_operations_complete' => true,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Support & operations contacts saved successfully']);
        } catch (\Exception $e) {
            \Log::error('Save support ops error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to save changes'], 500);
        }
    }

    public function saveSignatory(\Illuminate\Http\Request $request)
    {
        [$user, $account] = $this->getAuthenticatedUserAndAccount();
        if (!$user || !$account) {
            return response()->json(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'signatory_name' => 'required|string|max:255',
            'signatory_title' => 'required|string|max:255',
            'signatory_email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$user->tenant_id]);

            $account->update([
                'signatory_name' => $request->signatory_name,
                'signatory_title' => $request->signatory_title,
                'signatory_email' => $request->signatory_email,
                'contract_signatory_complete' => true,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Contract signatory details saved successfully']);
        } catch (\Exception $e) {
            \Log::error('Save signatory error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to save changes'], 500);
        }
    }

    public function saveVatInfo(\Illuminate\Http\Request $request)
    {
        [$user, $account] = $this->getAuthenticatedUserAndAccount();
        if (!$user || !$account) {
            return response()->json(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'vat_registered' => 'required|string|in:yes,no',
            'vat_number' => 'nullable|string|max:50',
            'vat_country' => 'nullable|string|max:2',
            'reverse_charges' => 'nullable|string|in:yes,no',
            'purchase_order_number' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$user->tenant_id]);

            $account->update([
                'vat_registered' => $request->vat_registered === 'yes',
                'vat_number' => $request->vat_number,
                'tax_country' => $request->vat_country,
                'vat_reverse_charges' => ($request->reverse_charges ?? 'no') === 'yes',
                'purchase_order_number' => $request->purchase_order_number,
                'billing_vat_complete' => true,
            ]);

            return response()->json(['status' => 'success', 'message' => 'VAT & tax information saved successfully']);
        } catch (\Exception $e) {
            \Log::error('Save VAT info error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Failed to save changes'], 500);
        }
    }

    public function usersAndAccess()
    {
        $tenantId = session('customer_tenant_id');
        $accountName = 'My Account';
        if ($tenantId) {
            try {
                $account = \App\Models\Account::find($tenantId);
                if ($account) {
                    $accountName = $account->company_name ?? $account->trading_name ?? 'My Account';
                }
            } catch (\Exception $e) {}
        }

        return view('quicksms.account.users-access', [
            'page_title' => 'Users and Access',
            'account_name' => $accountName,
        ]);
    }

    public function subAccounts()
    {
        $tenantId = session('customer_tenant_id');
        $user = \Illuminate\Support\Facades\Auth::user();

        $accountName = 'My Account';
        $currentUserData = [];
        if ($tenantId) {
            try {
                $account = \App\Models\Account::find($tenantId);
                if ($account) {
                    $accountName = $account->company_name ?? $account->trading_name ?? 'My Account';
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to load account: ' . $e->getMessage());
            }
        }

        if ($user) {
            $currentUserData = [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'is_account_owner' => $user->is_account_owner ?? false,
                'sub_account_id' => $user->sub_account_id,
            ];
        }

        return view('quicksms.account.users-access', [
            'page_title' => 'Sub Accounts, Users and Permissions',
            'account_name' => $accountName,
            'current_user' => $currentUserData,
        ]);
    }

    public function accountOverview(\Illuminate\Http\Request $request)
    {
        $tenantId = session('customer_tenant_id');

        if ($tenantId) {
            try {
                \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$tenantId]);
                $account = \App\Models\Account::find($tenantId);

                if ($account) {
                    $subAccounts = \App\Models\SubAccount::where('account_id', $tenantId)->get();
                    $totalSubAccounts = $subAccounts->count();
                    $totalUsers = \App\Models\User::where('tenant_id', $tenantId)->count();

                    $totalSpend = $subAccounts->sum('monthly_spend_used');
                    $totalMessages = $subAccounts->sum('monthly_messages_used');
                    $totalSpendCap = $subAccounts->sum('monthly_spending_cap');
                    $totalMessageCap = $subAccounts->sum('monthly_message_cap');

                    $mainAccountUsers = \App\Models\User::where('tenant_id', $tenantId)
                        ->whereNull('sub_account_id')
                        ->get()
                        ->map(function ($u) {
                            return [
                                'id' => $u->id,
                                'name' => trim($u->first_name . ' ' . $u->last_name),
                                'email' => $u->email,
                                'role' => $u->role,
                                'role_label' => $u->getRoleLabel(),
                                'status' => $u->status,
                                'sender_capability' => $u->sender_capability ?? 'none',
                                'is_account_owner' => $u->is_account_owner ?? false,
                                'last_login' => $u->last_login_at?->format('d M Y H:i'),
                            ];
                        })
                        ->toArray();

                    $subAccountsList = $subAccounts->map(function ($s) {
                        return ['id' => $s->id, 'name' => $s->name];
                    })->toArray();

                    $accountData = [
                        'id' => $account->id,
                        'name' => $account->company_name ?? $account->trading_name ?? 'Main Account',
                        'status' => $account->status,
                        'account_number' => $account->account_number,
                        'created_at' => $account->created_at ? $account->created_at->format('Y-m-d') : null,
                        'total_sub_accounts' => $totalSubAccounts,
                        'total_users' => $totalUsers,
                        'monthly_spend' => (float) $totalSpend,
                        'monthly_messages' => (int) $totalMessages,
                        'limits' => [
                            'spend_cap' => (float) $totalSpendCap,
                            'message_cap' => (int) $totalMessageCap,
                            'credit_limit' => (float) ($account->credit_limit ?? 0),
                        ],
                    ];

                    $currentUser = $request->user();
                    $canManageUsers = $currentUser && in_array($currentUser->role, ['owner', 'admin']);

                    return view('quicksms.account.account-overview', [
                        'page_title' => $accountData['name'],
                        'account' => $accountData,
                        'main_account_users' => $mainAccountUsers,
                        'sub_accounts_list' => $subAccountsList,
                        'can_manage_users' => $canManageUsers,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to load account overview: ' . $e->getMessage());
            }
        }

        abort(404, 'Account not found');
    }

    public function subAccountDetail($id)
    {
        $tenantId = session('customer_tenant_id');

        if ($tenantId) {
            try {
                \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$tenantId]);
                $subAccountModel = \App\Models\SubAccount::find($id);

                if ($subAccountModel) {
                    $subAccount = [
                        'id' => $subAccountModel->id,
                        'name' => $subAccountModel->name,
                        'description' => $subAccountModel->description,
                        'status' => $subAccountModel->sub_account_status ?? 'live',
                        'created_at' => $subAccountModel->created_at->format('Y-m-d'),
                        'user_count' => $subAccountModel->users()->count(),
                        'monthly_spend' => (float)($subAccountModel->monthly_spend_used ?? 0),
                        'monthly_messages' => $subAccountModel->monthly_messages_used ?? 0,
                        'limits' => [
                            'spend_cap' => (float)($subAccountModel->monthly_spending_cap ?? 0),
                            'message_cap' => $subAccountModel->monthly_message_cap ?? 0,
                            'daily_limit' => $subAccountModel->daily_send_limit ?? 0,
                            'enforcement_type' => $subAccountModel->enforcement_type ?? 'warn',
                            'hard_stop' => $subAccountModel->hard_stop_enabled ?? false,
                        ],
                    ];

                    $subAccountId = $subAccountModel->id;
                    $morphType = 'App\\Models\\SubAccount';

                    $senderIdAssignments = \App\Models\SenderIdAssignment::where('assignable_type', $morphType)
                        ->where('assignable_id', $subAccountId)
                        ->with('senderId')
                        ->get()
                        ->map(function ($a) {
                            $s = $a->senderId;
                            return $s ? [
                                'id' => $s->id,
                                'value' => $s->sender_id_value,
                                'status' => $s->workflow_status ?? 'draft',
                                'assigned_at' => $a->created_at ? $a->created_at->format('d M Y') : '-',
                            ] : null;
                        })->filter()->values()->toArray();

                    $numberAssignments = \App\Models\NumberAssignment::where('assignable_type', $morphType)
                        ->where('assignable_id', $subAccountId)
                        ->with('purchasedNumber')
                        ->get()
                        ->map(function ($a) {
                            $n = $a->purchasedNumber;
                            return $n ? [
                                'id' => $n->id,
                                'number' => $n->number,
                                'type' => $n->number_type ?? 'vmn',
                                'country' => $n->country_iso ?? 'GB',
                                'assigned_at' => $a->created_at ? $a->created_at->format('d M Y') : '-',
                            ] : null;
                        })->filter()->values()->toArray();

                    $rcsAssignments = \App\Models\RcsAgentAssignment::where('assignable_type', $morphType)
                        ->where('assignable_id', $subAccountId)
                        ->with('rcsAgent')
                        ->get()
                        ->map(function ($a) {
                            $r = $a->rcsAgent;
                            return $r ? [
                                'id' => $r->id,
                                'name' => $r->agent_name ?? $r->name ?? 'Unnamed Agent',
                                'status' => $r->workflow_status ?? 'draft',
                                'assigned_at' => $a->created_at ? $a->created_at->format('d M Y') : '-',
                            ] : null;
                        })->filter()->values()->toArray();

                    $templates = \App\Models\MessageTemplate::where('sub_account_id', $subAccountId)
                        ->select('id', 'name', 'type', 'status', 'created_at')
                        ->orderBy('name')
                        ->get()
                        ->map(function ($t) {
                            return [
                                'id' => $t->id,
                                'name' => $t->name,
                                'type' => $t->type,
                                'status' => $t->status,
                                'created_at' => $t->created_at ? $t->created_at->format('d M Y') : '-',
                            ];
                        })->toArray();

                    $emailSetups = \App\Models\EmailToSmsSetup::where('sub_account_id', $subAccountId)
                        ->select('id', 'name', 'status', 'created_at')
                        ->orderBy('name')
                        ->get()
                        ->map(function ($e) {
                            return [
                                'id' => $e->id,
                                'name' => $e->name,
                                'status' => $e->status,
                                'created_at' => $e->created_at ? $e->created_at->format('d M Y') : '-',
                            ];
                        })->toArray();

                    $apiConnections = \App\Models\ApiConnection::where('sub_account_id', $subAccountId)
                        ->select('id', 'name', 'status', 'created_at')
                        ->orderBy('name')
                        ->get()
                        ->map(function ($c) {
                            return [
                                'id' => $c->id,
                                'name' => $c->name,
                                'status' => $c->status,
                                'created_at' => $c->created_at ? $c->created_at->format('d M Y') : '-',
                            ];
                        })->toArray();

                    $assets = [
                        'sender_ids' => $senderIdAssignments,
                        'numbers' => $numberAssignments,
                        'rcs_agents' => $rcsAssignments,
                        'templates' => $templates,
                        'email_setups' => $emailSetups,
                        'api_connections' => $apiConnections,
                    ];

                    return view('quicksms.account.sub-account-detail', [
                        'page_title' => $subAccount['name'],
                        'sub_account' => $subAccount,
                        'assets' => $assets,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to load sub-account: ' . $e->getMessage());
            }
        }

        abort(404, 'Sub-Account not found');
    }

    public function userDetail($subId, $userId)
    {
        $tenantId = session('customer_tenant_id');

        if ($tenantId) {
            try {
                \Illuminate\Support\Facades\DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$tenantId]);

                $subAccountModel = \App\Models\SubAccount::find($subId);
                $userModel = \App\Models\User::find($userId);

                if ($subAccountModel && $userModel) {
                    $subAccount = ['id' => $subAccountModel->id, 'name' => $subAccountModel->name];
                    $user = [
                        'id' => $userModel->id,
                        'name' => $userModel->first_name . ' ' . $userModel->last_name,
                        'email' => $userModel->email,
                        'role' => $userModel->role,
                        'role_label' => $userModel->getRoleLabel(),
                        'status' => $userModel->status,
                        'sender_capability' => $userModel->sender_capability ?? 'none',
                        'created_at' => $userModel->created_at->format('Y-m-d'),
                        'last_login' => $userModel->last_login_at?->format('Y-m-d H:i'),
                        'mfa_enabled' => $userModel->mfa_enabled,
                        'monthly_spend' => (float)($userModel->monthly_spend_used ?? 0),
                        'monthly_messages' => $userModel->monthly_messages_used ?? 0,
                        'message_cap' => $userModel->monthly_message_cap,
                        'sub_account_id' => $userModel->sub_account_id,
                    ];

                    return view('quicksms.account.user-detail', [
                        'page_title' => $user['name'],
                        'sub_account' => $subAccount,
                        'user' => $user,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to load user detail: ' . $e->getMessage());
            }
        }

        abort(404, 'User not found');
    }

    public function auditLogs()
    {
        return view('quicksms.account.audit-logs', [
            'page_title' => 'Audit Logs'
        ]);
    }

    public function securitySettings()
    {
        return view('quicksms.account.security', [
            'page_title' => 'Security Settings'
        ]);
    }

    public function support()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Support',
            'purpose' => 'Access support resources, submit tickets, and browse documentation.',
            'sub_modules' => [
                'Dashboard',
                'Create a Ticket',
                'Knowledge Base'
            ]
        ]);
    }

    public function supportDashboard()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Support Dashboard',
            'purpose' => 'Overview of support tickets and help resources.',
            'sub_modules' => []
        ]);
    }

    public function createTicket()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Create a Ticket',
            'purpose' => 'Submit a new support request or issue.',
            'sub_modules' => []
        ]);
    }

    public function knowledgeBase()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Knowledge Base',
            'purpose' => 'Browse help articles, guides, and FAQs.',
            'sub_modules' => []
        ]);
    }
    
    public function knowledgeBaseTestMode()
    {
        return view('quicksms.support.knowledge-base-test-mode', [
            'page_title' => 'Understanding Test Mode'
        ]);
    }

    public function rcsPreviewDemo()
    {
        return view('rcs.preview', [
            'page_title' => 'RCS Preview Demo'
        ]);
    }

    /**
     * API: Get numbers pricing from HubSpot
     * Returns setup and monthly fees for VMNs and keywords
     */
    public function getNumbersPricing(Request $request)
    {
        $currency = $request->query('currency', 'GBP');
        
        $hubspotService = new \App\Services\HubSpotProductService();
        $pricing = $hubspotService->fetchNumbersPricing($currency);
        
        return response()->json($pricing);
    }

    /**
     * API: Lock numbers/keywords for purchase
     * Prevents race conditions during checkout
     */
    public function lockNumbersForPurchase(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:vmn,keyword',
            'items.*.identifier' => 'required|string',
            'purchase_type' => 'required|in:vmn,keyword',
        ]);

        $purchaseService = new \App\Services\NumberPurchaseService();
        
        try {
            $sessionId = \Illuminate\Support\Str::uuid()->toString();
            $userId = 1;

            $result = $purchaseService->acquireLocks(
                $request->input('items'),
                $sessionId,
                $userId
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    /**
     * API: Process numbers/keywords purchase
     * Atomic transaction with audit logging
     */
    public function processNumbersPurchase(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'sub_account_id' => 'required|string',
            'sub_account_name' => 'nullable|string',
            'purchase_type' => 'required|in:vmn,keyword',
            'items' => 'required|array|min:1',
            'items.*.identifier' => 'required|string',
        ]);

        $purchaseService = new \App\Services\NumberPurchaseService();

        try {
            $result = $purchaseService->processPurchase([
                'session_id' => $request->input('session_id'),
                'user_id' => 1,
                'user_email' => 'demo@quicksms.com',
                'user_name' => 'Demo User',
                'sub_account_id' => $request->input('sub_account_id'),
                'sub_account_name' => $request->input('sub_account_name'),
                'purchase_type' => $request->input('purchase_type'),
                'items' => $request->input('items'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * API: Release locked numbers/keywords
     * Called when user cancels or times out
     */
    public function releaseNumberLocks(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $purchaseService = new \App\Services\NumberPurchaseService();
        
        try {
            $purchaseService->releaseLocks($request->input('session_id'));
            
            return response()->json([
                'success' => true,
                'message' => 'Locks released successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
