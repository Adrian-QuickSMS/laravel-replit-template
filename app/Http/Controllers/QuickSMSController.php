<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\OptOutList;
use App\Models\OptOutRecord;
use App\Models\Tag;
use App\Models\Account;
use App\Models\SenderId;
use App\Models\User;
use App\Models\RcsAgent;
use App\Models\MessageTemplate;
use App\Models\Campaign;
use App\Services\Billing\PricingEngine;

class QuickSMSController extends Controller
{
    private function getApprovedSenderIds(): array
    {
        $tenantId = session('customer_tenant_id');
        if (!$tenantId) {
            return [['id' => 0, 'name' => 'QuickSMS', 'type' => 'alphanumeric']];
        }

        $senderIds = SenderId::where('account_id', $tenantId)
            ->where('workflow_status', 'approved')
            ->orderByDesc('is_default')
            ->orderBy('sender_id_value')
            ->get();

        if ($senderIds->isEmpty()) {
            return [['id' => 0, 'name' => 'QuickSMS', 'type' => 'alphanumeric']];
        }

        return $senderIds->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->sender_id_value,
            'type' => strtolower($s->sender_type === 'ALPHA' ? 'alphanumeric' : ($s->sender_type === 'NUMERIC' ? 'numeric' : 'shortcode')),
        ])->toArray();
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

        $rcs_agents = RcsAgent::where('workflow_status', 'approved')
            ->select('id', 'name', 'logo_url', 'description', 'brand_color', 'workflow_status')
            ->get()
            ->map(function($agent) {
                return [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'logo' => $agent->logo_url
                        ? (str_starts_with($agent->logo_url, 'data:') ? $agent->logo_url : asset('storage/' . $agent->logo_url))
                        : asset('images/default-agent-logo.png'),
                    'tagline' => $agent->description ?? '',
                    'brand_color' => $agent->brand_color ?? '#886CC0',
                    'status' => $agent->workflow_status,
                ];
            })
            ->toArray();

        $templates = MessageTemplate::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function($t) { return $t->toPortalArray(); })
            ->toArray();

        $lists = ContactList::orderBy('name')
            ->get()
            ->map(function($l) {
                return ['id' => $l->id, 'name' => $l->name, 'count' => $l->contact_count ?? 0];
            })
            ->toArray();

        $tags = Tag::orderBy('name')
            ->get()
            ->map(function($t) {
                return ['id' => $t->id, 'name' => $t->name, 'color' => $t->color ?? '#6f42c1', 'count' => $t->contacts_count ?? 0];
            })
            ->toArray();

        $opt_out_lists = OptOutList::orderBy('name')
            ->get()
            ->map(function($l) {
                return ['id' => $l->id, 'name' => $l->name, 'count' => $l->count ?? 0, 'is_default' => $l->is_master ?? false];
            })
            ->toArray();

        // Virtual numbers - not yet built, using mock data
        $virtual_numbers = [
            ['id' => 1, 'number' => '+447700900100', 'label' => 'Main'],
            ['id' => 2, 'number' => '+447700900200', 'label' => 'Marketing'],
        ];

        // Opt-out domains - not yet built, using mock data
        $optout_domains = [
            ['id' => 1, 'domain' => 'stop.uk', 'is_default' => true],
            ['id' => 2, 'domain' => 'unsubscribe.quicksms.uk', 'is_default' => false],
        ];

        $accountPricing = ['sms' => 0.035, 'rcs_basic' => 0.05, 'rcs_rich' => 0.08, 'currency' => 'GBP'];
        $tenantId = session('customer_tenant_id');
        if ($tenantId) {
            $account = Account::find($tenantId);
            if ($account) {
                try {
                    $pricingEngine = app(PricingEngine::class);
                    $smsPrice = $pricingEngine->resolvePrice($account, 'sms', null);
                    $rcsBasicPrice = $pricingEngine->resolvePrice($account, 'rcs_basic', null);
                    $rcsSinglePrice = $pricingEngine->resolvePrice($account, 'rcs_single', null);
                    $accountPricing = [
                        'sms' => (float) $smsPrice->unitPrice,
                        'rcs_basic' => (float) $rcsBasicPrice->unitPrice,
                        'rcs_rich' => (float) $rcsSinglePrice->unitPrice,
                        'currency' => $smsPrice->currency,
                    ];
                } catch (\Throwable $e) {
                    // Fall back to defaults if pricing lookup fails
                }
            }
        }

        $editCampaignId = request()->query('campaign_id');
        $editCampaignConfig = null;
        if ($editCampaignId) {
            $sessionConfig = session('campaign_config', []);
            if (!empty($sessionConfig) && ($sessionConfig['campaign_id'] ?? null) === $editCampaignId) {
                $editCampaignConfig = $sessionConfig;
            }
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
            'account_pricing' => $accountPricing,
            'edit_campaign_config' => $editCampaignConfig,
        ]);
    }

    public function confirmCampaign(Request $request)
    {
        $campaignId = $request->query('campaign_id');
        $sessionData = $request->session()->get('campaign_config', []);
        if ($campaignId && $campaignId !== 'null' && $campaignId !== 'undefined') {
            $dbCampaign = Campaign::find($campaignId);
            if ($dbCampaign) {
                $scheduledTime = 'Immediate';
                if ($dbCampaign->scheduled_at) {
                    $scheduledTime = $dbCampaign->scheduled_at->format('d/m/Y H:i');
                } elseif (isset($sessionData['scheduled_time']) && $sessionData['scheduled_time'] !== 'now') {
                    $scheduledTime = $sessionData['scheduled_time'];
                }

                $messageValidity = 'Default (48 hours)';
                if ($dbCampaign->validity_period) {
                    $messageValidity = $dbCampaign->validity_period . ' hours';
                } elseif (isset($sessionData['message_expiry']) && $sessionData['message_expiry']) {
                    $expiryVal = $sessionData['message_expiry'];
                    $messageValidity = stripos($expiryVal, 'hour') !== false ? $expiryVal : $expiryVal . ' hours';
                }

                $sendingWindow = 'No restrictions';
                if ($dbCampaign->sending_window_start && $dbCampaign->sending_window_end) {
                    $sendingWindow = 'Quiet hours: ' . $dbCampaign->sending_window_start . ' - ' . $dbCampaign->sending_window_end;
                } elseif (isset($sessionData['sending_window']) && $sessionData['sending_window']) {
                    $sendingWindow = $sessionData['sending_window'];
                }

                $campaign = [
                    'id' => $dbCampaign->id,
                    'name' => $dbCampaign->name,
                    'created_by' => session('customer_email', 'Current User'),
                    'created_at' => $dbCampaign->created_at->format('d/m/Y H:i'),
                    'scheduled_time' => $scheduledTime,
                    'message_validity' => $messageValidity,
                    'sending_window' => $sendingWindow,
                    'type' => $dbCampaign->type,
                    'status' => $dbCampaign->status,
                    'segment_count' => $dbCampaign->segment_count ?? 1,
                ];
                $channelTypeMap = ['sms' => 'sms_only', 'rcs_basic' => 'basic_rcs', 'rcs_single' => 'rich_rcs'];
                $channel = [
                    'type' => $channelTypeMap[$dbCampaign->type] ?? $dbCampaign->type,
                    'sms_sender_id' => $dbCampaign->senderId
                        ? $dbCampaign->senderId->sender_id_value
                        : ($sessionData['sender_id'] ?? ($dbCampaign->sender_id_id
                            ? (SenderId::withoutGlobalScopes()->find($dbCampaign->sender_id_id)->sender_id_value ?? 'Not selected')
                            : 'Not selected')),
                    'rcs_agent' => [
                        'name' => $dbCampaign->rcsAgent ? $dbCampaign->rcsAgent->name : 'Not selected',
                        'logo' => $dbCampaign->rcsAgent && $dbCampaign->rcsAgent->logo_url
                            ? (str_starts_with($dbCampaign->rcsAgent->logo_url, 'data:') ? $dbCampaign->rcsAgent->logo_url : asset('storage/' . $dbCampaign->rcsAgent->logo_url))
                            : asset('images/default-agent-logo.png'),
                    ],
                ];
                $message = [
                    'type' => $dbCampaign->type,
                    'sms_content' => $dbCampaign->message_content ?? '',
                    'rcs_content' => $dbCampaign->rcs_content ?? null,
                ];
                $sourceDefaults = ['manual_input' => 0, 'file_upload' => 0, 'contacts' => 0, 'lists' => 0, 'dynamic_lists' => 0, 'tags' => 0];
                if (!empty($sessionData['sources'])) {
                    $mappedSources = array_merge($sourceDefaults, $sessionData['sources']);
                } else {
                    $rawSources = $dbCampaign->recipient_sources ?? [];
                    if (!empty($rawSources) && isset($rawSources[0]['type'])) {
                        $mappedSources = $sourceDefaults;
                        foreach ($rawSources as $src) {
                            $type = $src['type'] ?? '';
                            if ($type === 'manual') $mappedSources['manual_input'] += count($src['numbers'] ?? []);
                            elseif ($type === 'csv') $mappedSources['file_upload'] += count($src['numbers'] ?? []);
                            elseif ($type === 'individual') $mappedSources['contacts'] += count($src['contact_ids'] ?? []);
                            elseif ($type === 'list') $mappedSources['lists']++;
                            elseif ($type === 'tag') $mappedSources['tags']++;
                        }
                    } else {
                        $mappedSources = array_merge($sourceDefaults, $rawSources);
                    }
                }
                $recipientCount = $sessionData['recipient_count'] ?? $dbCampaign->total_recipients ?? 0;
                $optedOutCount = $sessionData['opted_out_count'] ?? $dbCampaign->total_opted_out ?? 0;
                $invalidCount = $sessionData['invalid_count'] ?? $dbCampaign->total_invalid ?? 0;
                $validCount = $sessionData['valid_count'] ?? ($recipientCount - $optedOutCount - $invalidCount);
                $recipients = [
                    'total_selected' => $recipientCount,
                    'valid' => $validCount,
                    'invalid' => $invalidCount,
                    'opted_out' => $optedOutCount,
                    'sources' => $mappedSources,
                ];
                $account = \App\Models\Account::find($dbCampaign->account_id);
                $pricingEngine = app(PricingEngine::class);
                $smsPriceResult = $account ? $pricingEngine->resolvePrice($account, 'sms', null) : null;
                $rcsBasicResult = $account ? $pricingEngine->resolvePrice($account, 'rcs_basic', null) : null;
                $rcsSingleResult = $account ? $pricingEngine->resolvePrice($account, 'rcs_single', null) : null;
                $smsPrice = $smsPriceResult ? (float) $smsPriceResult->unitPrice : 0.023;
                $rcsBasicPrice = $rcsBasicResult ? (float) $rcsBasicResult->unitPrice : 0.035;
                $rcsSinglePrice = $rcsSingleResult ? (float) $rcsSingleResult->unitPrice : 0.045;
                $pricing = [
                    'sms_unit_price' => $smsPrice,
                    'rcs_basic_price' => $rcsBasicPrice,
                    'rcs_single_price' => $rcsSinglePrice,
                    'vat_applicable' => $account ? (bool) ($account->vat_registered ?? true) : true,
                    'vat_rate' => 20,
                ];

                $segmentBreakdown = [];
                $totalSmsParts = 0;
                if ($dbCampaign->content_resolved_at) {
                    $segmentBreakdown = DB::table('campaign_recipients')
                        ->where('campaign_id', $dbCampaign->id)
                        ->where('status', 'pending')
                        ->select('segments', DB::raw('COUNT(*) as recipient_count'))
                        ->groupBy('segments')
                        ->orderBy('segments')
                        ->get()
                        ->toArray();

                    foreach ($segmentBreakdown as $group) {
                        $totalSmsParts += ($group->segments ?? 1) * $group->recipient_count;
                    }
                }

                if (empty($segmentBreakdown)) {
                    $segCount = $dbCampaign->segment_count ?? 1;
                    $totalSmsParts = $validCount * $segCount;
                }

                return view('quicksms.messages.confirm-campaign', [
                    'page_title' => 'Confirm & Send Campaign',
                    'campaign' => $campaign,
                    'channel' => $channel,
                    'recipients' => $recipients,
                    'pricing' => $pricing,
                    'message' => $message,
                    'segment_breakdown' => $segmentBreakdown,
                    'total_sms_parts' => $totalSmsParts,
                    'content_resolved' => $dbCampaign->content_resolved_at !== null,
                ]);
            }
        }

        // Get campaign data from session (populated by Send Message Continue button via JavaScript POST)
        $sessionData = $request->session()->get('campaign_config', []);
        
        // Campaign summary - use session data with fallbacks
        $campaign = [
            'id' => $sessionData['campaign_id'] ?? null,
            'name' => $sessionData['campaign_name'] ?? 'Untitled Campaign',
            'created_by' => auth()->check() ? auth()->user()->name ?? 'Current User' : 'Current User',
            'created_at' => now()->format('d/m/Y H:i'),
            'scheduled_time' => isset($sessionData['scheduled_time']) && $sessionData['scheduled_time'] !== 'now' 
                ? $sessionData['scheduled_time'] 
                : 'Immediate',
            'message_validity' => isset($sessionData['message_expiry']) && $sessionData['message_expiry'] 
                ? (stripos($sessionData['message_expiry'], 'hour') !== false ? $sessionData['message_expiry'] : $sessionData['message_expiry'] . ' hours')
                : 'Default (48 hours)',
            'sending_window' => isset($sessionData['sending_window']) && $sessionData['sending_window'] 
                ? $sessionData['sending_window'] 
                : 'No restrictions',
        ];

        // Channel data from session
        $channelType = $sessionData['channel'] ?? 'sms_only';
        $channel = [
            'type' => $channelType,
            'sms_sender_id' => $sessionData['sender_id'] ?? 'Not selected',
            'rcs_agent' => [
                'name' => $sessionData['rcs_agent'] ?? 'Not selected',
                'logo' => asset('images/default-agent-logo.png'),
            ],
        ];

        // Recipients data from session
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
        
        // If no sources are set but we have recipients, set manual input
        if ($recipientCount > 0 && array_sum($recipients['sources']) === 0) {
            $recipients['sources']['manual_input'] = $recipientCount;
        }

        // Pricing data - use account pricing or defaults
        $pricing = [
            'sms_unit_price' => 0.023,
            'rcs_basic_price' => 0.035,
            'rcs_single_price' => 0.045,
            'vat_applicable' => true,
            'vat_rate' => 20,
        ];

        // Message content from session
        $message = [
            'type' => $channelType,
            'sms_content' => $sessionData['message_content'] ?? '',
            'rcs_content' => $sessionData['rcs_content'] ?? null,
        ];

        $segmentBreakdown = [];
        $totalSmsParts = $recipientCount * ($campaign['segment_count'] ?? 1);
        $contentResolved = false;

        $campaignId = $sessionData['campaign_id'] ?? null;
        if ($campaignId) {
            $campaignModel = \App\Models\Campaign::find($campaignId);
            if ($campaignModel && $campaignModel->content_resolved_at) {
                $contentResolved = true;
                $segmentBreakdown = DB::table('campaign_recipients')
                    ->join('campaigns', 'campaigns.id', '=', 'campaign_recipients.campaign_id')
                    ->where('campaign_recipients.campaign_id', $campaignId)
                    ->where('campaigns.account_id', auth()->user()->tenant_id)
                    ->whereNotNull('campaign_recipients.segments')
                    ->selectRaw('campaign_recipients.segments, COUNT(*) as recipient_count')
                    ->groupBy('campaign_recipients.segments')
                    ->orderBy('campaign_recipients.segments')
                    ->get()
                    ->all();

                if (!empty($segmentBreakdown)) {
                    $totalSmsParts = 0;
                    foreach ($segmentBreakdown as $group) {
                        $totalSmsParts += $group->segments * $group->recipient_count;
                    }
                }
            }
        }

        return view('quicksms.messages.confirm-campaign', [
            'page_title' => 'Confirm & Send Campaign',
            'campaign' => $campaign,
            'channel' => $channel,
            'recipients' => $recipients,
            'pricing' => $pricing,
            'segment_breakdown' => $segmentBreakdown,
            'total_sms_parts' => $totalSmsParts,
            'content_resolved' => $contentResolved,
            'message' => $message,
        ]);
    }

    public function storeCampaignConfig(Request $request)
    {
        $allowed = [
            'campaign_id', 'campaign_name', 'channel', 'sender_id', 'rcs_agent',
            'message_content', 'rcs_content', 'scheduled_time',
            'message_expiry', 'sending_window', 'recipient_count',
            'valid_count', 'invalid_count', 'opted_out_count', 'sources',
        ];
        $request->session()->put('campaign_config', $request->only($allowed));
        
        return response()->json(['success' => true]);
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

        // TODO: Replace with database query - GET /api/templates (excludes API-triggered for portal UI)
        $templates = [
            ['id' => 'tpl_1', 'name' => 'Quick Reply', 'content' => 'Thank you for your message. We will get back to you shortly.', 'trigger' => 'Portal', 'channel' => 'SMS', 'status' => 'Live', 'version' => 1],
            ['id' => 'tpl_2', 'name' => 'Order Update', 'content' => 'Hi {{firstName}}, your order #{{orderNumber}} is on its way!', 'trigger' => 'Portal', 'channel' => 'SMS', 'status' => 'Live', 'version' => 2],
            ['id' => 'tpl_3', 'name' => 'Appointment Confirm', 'content' => 'Your appointment is confirmed for {{date}} at {{time}}. Reply YES to confirm or NO to cancel.', 'trigger' => 'Portal', 'channel' => 'SMS', 'status' => 'Live', 'version' => 1],
            ['id' => 'tpl_4', 'name' => 'RCS Thank You', 'content' => 'Thanks for reaching out! Our team will respond shortly.', 'trigger' => 'Portal', 'channel' => 'Basic RCS + SMS', 'status' => 'Live', 'version' => 1],
            ['id' => 'tpl_5', 'name' => 'Rich Promo Card', 'content' => '', 'trigger' => 'Portal', 'channel' => 'Rich RCS + SMS', 'status' => 'Live', 'version' => 1, 'rcs_payload' => [
                'type' => 'standalone',
                'card' => [
                    'media' => ['url' => '', 'height' => 'MEDIUM'],
                    'title' => 'Special Offer',
                    'description' => 'Exclusive discount just for you!',
                    'suggestions' => [
                        ['type' => 'url', 'text' => 'Shop Now', 'url' => 'https://example.com/shop']
                    ]
                ],
                'fallback' => 'Special Offer! Exclusive discount at https://example.com/shop'
            ]],
        ];

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

    public function campaignHistory()
    {
        $campaigns = [];

        return view('quicksms.messages.campaign-history', [
            'page_title' => 'Campaign History',
            'campaigns' => $campaigns,
        ]);
    }
    
    public function campaignApprovals()
    {
        $pendingApprovals = [
            ['id' => 'camp_pa001', 'name' => 'January Promo Blast', 'sub_account' => 'Marketing Department', 'created_by' => 'Emma Thompson', 'message_volume' => 5200, 'estimated_cost' => 156.00, 'scheduled_time' => '2026-01-20 09:00', 'status' => 'pending', 'channel' => 'SMS', 'created_at' => '2026-01-15 14:32'],
            ['id' => 'camp_pa002', 'name' => 'Product Launch RCS', 'sub_account' => 'Marketing Department', 'created_by' => 'Michael Brown', 'message_volume' => 3800, 'estimated_cost' => 228.00, 'scheduled_time' => '2026-01-21 10:00', 'status' => 'pending', 'channel' => 'RCS', 'created_at' => '2026-01-16 09:15'],
            ['id' => 'camp_pa003', 'name' => 'Flash Sale Alert', 'sub_account' => 'Customer Support', 'created_by' => 'Chris Martinez', 'message_volume' => 1500, 'estimated_cost' => 45.00, 'scheduled_time' => '2026-01-18 12:00', 'status' => 'pending', 'channel' => 'SMS', 'created_at' => '2026-01-16 11:45'],
        ];
        
        $recentDecisions = [
            ['id' => 'camp_rd001', 'name' => 'Weekend Special', 'sub_account' => 'Marketing Department', 'created_by' => 'Emma Thompson', 'decision' => 'approved', 'approver' => 'Sarah Mitchell', 'decided_at' => '2026-01-14 16:20'],
            ['id' => 'camp_rd002', 'name' => 'Discount Code SMS', 'sub_account' => 'Marketing Department', 'created_by' => 'Michael Brown', 'decision' => 'rejected', 'approver' => 'James Wilson', 'decided_at' => '2026-01-13 10:05', 'rejection_reason' => 'Content requires compliance review before sending'],
        ];
        
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
        $optOutLists = OptOutList::orderBy('name')->get()->map(fn($l) => ['id' => $l->id, 'name' => $l->name])->toArray();

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
        $tenantId = session('customer_tenant_id');
        $accountBalance = 0;

        if ($tenantId) {
            try {
                $balance = app(\App\Services\Billing\BalanceService::class)->getBalance($tenantId);
                $accountBalance = (float) $balance->effective_available;
            } catch (\Exception $e) {
                $accountBalance = 0;
            }
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

        // TODO: Replace with database query - GET /api/rcs-agents?status=approved
        $rcs_agents = [
            ['id' => 1, 'name' => 'QuickSMS Brand', 'logo' => asset('images/rcs-agents/quicksms-brand.svg'), 'tagline' => 'Fast messaging for everyone', 'brand_color' => '#886CC0', 'status' => 'approved'],
            ['id' => 2, 'name' => 'Promotions Agent', 'logo' => asset('images/rcs-agents/promotions-agent.svg'), 'tagline' => 'Exclusive deals & offers', 'brand_color' => '#E91E63', 'status' => 'approved'],
        ];

        // TODO: Replace with database query - GET /api/opt-out-lists
        $opt_out_lists = [
            ['id' => 1, 'name' => 'Master Opt-Out List', 'count' => 2847, 'is_default' => true],
            ['id' => 2, 'name' => 'Marketing Opt-Outs', 'count' => 1245, 'is_default' => false],
            ['id' => 3, 'name' => 'Promotions Opt-Outs', 'count' => 892, 'is_default' => false],
        ];

        // TODO: Replace with database query - GET /api/virtual-numbers
        $virtual_numbers = [
            ['id' => 1, 'number' => '+447700900100', 'label' => 'Main Number'],
            ['id' => 2, 'number' => '+447700900200', 'label' => 'Marketing'],
        ];

        // TODO: Replace with database query - GET /api/optout-domains
        $optout_domains = [
            ['id' => 1, 'domain' => 'qsms.uk', 'is_default' => true],
            ['id' => 2, 'domain' => 'optout.quicksms.com', 'is_default' => false],
        ];

        return view('quicksms.management.templates', [
            'page_title' => 'Message Templates',
            'sender_ids' => $sender_ids,
            'rcs_agents' => $rcs_agents,
            'opt_out_lists' => $opt_out_lists,
            'virtual_numbers' => $virtual_numbers,
            'optout_domains' => $optout_domains
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

        $rcs_agents = [
            ['id' => 1, 'name' => 'QuickSMS Brand', 'logo' => asset('images/rcs-agents/quicksms-brand.svg'), 'tagline' => 'Fast messaging for everyone', 'brand_color' => '#886CC0', 'status' => 'approved'],
            ['id' => 2, 'name' => 'Promotions Agent', 'logo' => asset('images/rcs-agents/promotions-agent.svg'), 'tagline' => 'Exclusive deals & offers', 'brand_color' => '#E91E63', 'status' => 'approved'],
        ];

        // TODO: Replace with API call - optOutService.getLists()
        $opt_out_lists = [
            ['id' => 1, 'name' => 'Marketing Opt-outs', 'count' => 1250],
            ['id' => 2, 'name' => 'Transactional Opt-outs', 'count' => 89],
        ];

        // TODO: Replace with API call - numbersService.getVirtualNumbers()
        $virtual_numbers = [
            ['id' => 1, 'number' => '+447700900200', 'label' => 'Customer Support'],
            ['id' => 2, 'number' => '+447700900201', 'label' => 'Sales'],
        ];

        // TODO: Replace with API call - optOutService.getDomains()
        $optout_domains = [
            ['id' => 1, 'domain' => 'optout.quicksms.co.uk', 'is_default' => true],
            ['id' => 2, 'domain' => 'stop.quicksms.co.uk', 'is_default' => false],
        ];

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
        return view('quicksms.management.templates.create-step3', [
            'page_title' => 'Create Template - Settings',
            'isEditMode' => false,
            'isAdminMode' => false,
            'template' => null
        ]);
    }

    public function templateCreateReview()
    {
        return view('quicksms.management.templates.create-review', [
            'page_title' => 'Create Template - Review',
            'isEditMode' => false,
            'isAdminMode' => false,
            'template' => null
        ]);
    }

    public function templateEditStep1($templateId)
    {
        // TODO: Replace with API call - templatesService.getTemplate(templateId)
        $template = $this->getMockTemplate($templateId);

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

        $rcs_agents = [
            ['id' => 1, 'name' => 'QuickSMS Brand', 'logo' => asset('images/rcs-agents/quicksms-brand.svg'), 'tagline' => 'Fast messaging for everyone', 'brand_color' => '#886CC0', 'status' => 'approved'],
            ['id' => 2, 'name' => 'Promotions Agent', 'logo' => asset('images/rcs-agents/promotions-agent.svg'), 'tagline' => 'Exclusive deals & offers', 'brand_color' => '#E91E63', 'status' => 'approved'],
        ];

        // TODO: Replace with API call - optOutService.getLists()
        $opt_out_lists = [
            ['id' => 1, 'name' => 'Marketing Opt-outs', 'count' => 1250],
            ['id' => 2, 'name' => 'Transactional Opt-outs', 'count' => 89],
        ];

        // TODO: Replace with API call - numbersService.getVirtualNumbers()
        $virtual_numbers = [
            ['id' => 1, 'number' => '+447700900200', 'label' => 'Customer Support'],
            ['id' => 2, 'number' => '+447700900201', 'label' => 'Sales'],
        ];

        // TODO: Replace with API call - optOutService.getDomains()
        $optout_domains = [
            ['id' => 1, 'domain' => 'optout.quicksms.co.uk', 'is_default' => true],
            ['id' => 2, 'domain' => 'stop.quicksms.co.uk', 'is_default' => false],
        ];

        // TODO: Replace with API call - templatesService.getTemplate(templateId)
        $template = $this->getMockTemplate($templateId);

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
        // TODO: Replace with API call - templatesService.getTemplate(templateId)
        $template = $this->getMockTemplate($templateId);

        return view('quicksms.management.templates.create-step3', [
            'page_title' => 'Edit Template - Settings',
            'isEditMode' => true,
            'isAdminMode' => false,
            'templateId' => $templateId,
            'template' => $template
        ]);
    }

    public function templateEditReview($templateId)
    {
        // TODO: Replace with API call - templatesService.getTemplate(templateId)
        $template = $this->getMockTemplate($templateId);

        return view('quicksms.management.templates.create-review', [
            'page_title' => 'Edit Template - Review',
            'isEditMode' => true,
            'isAdminMode' => false,
            'templateId' => $templateId,
            'template' => $template
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

        $rcs_agents = [
            ['id' => 1, 'name' => 'QuickSMS Brand', 'logo' => asset('images/rcs-agents/quicksms-brand.svg'), 'tagline' => 'Fast messaging for everyone', 'brand_color' => '#886CC0', 'status' => 'approved'],
            ['id' => 2, 'name' => 'Promotions Agent', 'logo' => asset('images/rcs-agents/promotions-agent.svg'), 'tagline' => 'Exclusive deals & offers', 'brand_color' => '#E91E63', 'status' => 'approved'],
        ];

        // TODO: Replace with API call - templatesService.getTemplate(templateId)
        $template = $this->getMockTemplate($templateId);
        
        // TODO: Replace with API call - accountsService.getAccount(accountId)
        $account = [
            'id' => $accountId,
            'name' => 'Acme Corp'
        ];

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

    private function getMockTemplate($templateId)
    {
        // TODO: Replace with database query - GET /api/templates/{templateId}
        $mockTemplates = [
            '71829364' => [
                'id' => 1,
                'name' => 'Flash Sale Alert',
                'templateId' => 'TPL-71829364',
                'trigger' => 'portal',
                'channel' => 'basic_rcs',
                'content' => 'Flash Sale! 50% off all items today only. Shop now at {Link}',
                'senderId' => '1',
                'rcsAgent' => '1',
                'trackableLink' => true,
                'optOut' => false
            ],
            '38472615' => [
                'id' => 2,
                'name' => 'Product Showcase',
                'templateId' => 'TPL-38472615',
                'trigger' => 'portal',
                'channel' => 'rich_rcs',
                'content' => '',
                'senderId' => '1',
                'rcsAgent' => '2',
                'trackableLink' => false,
                'optOut' => false
            ],
            '10483726' => [
                'id' => 3,
                'name' => 'Welcome Message',
                'templateId' => 'TPL-10483726',
                'trigger' => 'api',
                'channel' => 'sms',
                'content' => 'Hi {FirstName}, welcome to QuickSMS! Your account is ready.',
                'senderId' => '1',
                'rcsAgent' => '',
                'trackableLink' => false,
                'optOut' => true
            ],
            'TPL-12345678' => [
                'id' => 4,
                'name' => 'Winter Sale 2026',
                'templateId' => 'TPL-12345678',
                'trigger' => 'portal',
                'channel' => 'sms',
                'content' => 'Hi {FirstName}! Our Winter Sale is here. Get 40% off all items. Shop now: {Link}',
                'senderId' => '1',
                'rcsAgent' => '',
                'trackableLink' => true,
                'optOut' => true,
                'description' => 'Promotional template for winter sale campaign'
            ],
            'TPL-23456789' => [
                'id' => 5,
                'name' => 'Appointment Confirmation',
                'templateId' => 'TPL-23456789',
                'trigger' => 'api',
                'channel' => 'sms',
                'content' => 'Hi {FirstName}, your appointment is confirmed for {AppointmentDate} at {AppointmentTime}.',
                'senderId' => '2',
                'rcsAgent' => '',
                'trackableLink' => false,
                'optOut' => false,
                'description' => 'API-triggered appointment confirmation message'
            ],
            'TPL-34567890' => [
                'id' => 6,
                'name' => 'Delivery Update',
                'templateId' => 'TPL-34567890',
                'trigger' => 'api',
                'channel' => 'basic_rcs',
                'content' => 'Your order #{OrderId} is on its way! Track here: {TrackingLink}',
                'senderId' => '1',
                'rcsAgent' => '1',
                'trackableLink' => true,
                'optOut' => false,
                'description' => 'RCS delivery notification with tracking link'
            ]
        ];

        return $mockTemplates[$templateId] ?? [
            'id' => 999,
            'name' => 'Unknown Template',
            'templateId' => 'TPL-' . $templateId,
            'trigger' => 'api',
            'channel' => 'sms',
            'content' => 'Template content here',
            'senderId' => '1',
            'rcsAgent' => '',
            'trackableLink' => false,
            'optOut' => false
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
        $subAccounts = $tenantId
            ? \App\Models\SubAccount::where('account_id', $tenantId)->get(['id', 'name'])->toArray()
            : [];

        return view('quicksms.management.numbers', [
            'page_title' => 'Numbers',
            'subAccounts' => $subAccounts,
        ]);
    }

    public function numbersConfigure(Request $request)
    {
        $selectedIds = $request->query('ids', '');
        $tenantId = session('customer_tenant_id');
        $subAccounts = $tenantId
            ? \App\Models\SubAccount::where('account_id', $tenantId)->get(['id', 'name'])->toArray()
            : [];

        return view('quicksms.management.numbers-configure', [
            'page_title' => 'Configure Numbers',
            'selectedIds' => $selectedIds,
            'subAccounts' => $subAccounts,
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

        return view('quicksms.account.details', [
            'page_title' => 'Account Details',
            'user' => $user,
            'account' => $account,
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
        return view('quicksms.account.users-access', [
            'page_title' => 'Users and Access'
        ]);
    }

    public function subAccounts()
    {
        return view('quicksms.placeholder', [
            'page_title' => 'Sub Accounts',
            'purpose' => 'Create and manage sub-accounts for organizational units.',
            'sub_modules' => []
        ]);
    }
    
    public function subAccountDetail($id)
    {
        $subAccounts = [
            'sub-001' => [
                'id' => 'sub-001',
                'name' => 'Marketing Department',
                'status' => 'live',
                'created_at' => '2024-06-15',
                'user_count' => 8,
                'monthly_spend' => 1250.00,
                'monthly_messages' => 42500,
                'limits' => [
                    'spend_cap' => 5000.00,
                    'message_cap' => 100000,
                    'daily_limit' => 5000,
                    'enforcement_type' => 'block',
                    'hard_stop' => false
                ]
            ],
            'sub-002' => [
                'id' => 'sub-002',
                'name' => 'Customer Support',
                'status' => 'live',
                'created_at' => '2024-08-22',
                'user_count' => 5,
                'monthly_spend' => 875.50,
                'monthly_messages' => 28000,
                'limits' => [
                    'spend_cap' => 2000.00,
                    'message_cap' => 50000,
                    'daily_limit' => 2000,
                    'enforcement_type' => 'warn',
                    'hard_stop' => false
                ]
            ],
            'sub-003' => [
                'id' => 'sub-003',
                'name' => 'Sales Team',
                'status' => 'suspended',
                'created_at' => '2024-09-10',
                'user_count' => 3,
                'monthly_spend' => 0,
                'monthly_messages' => 0,
                'limits' => [
                    'spend_cap' => 1000.00,
                    'message_cap' => 25000,
                    'daily_limit' => 1000,
                    'enforcement_type' => 'approval',
                    'hard_stop' => true
                ]
            ]
        ];
        
        $subAccount = $subAccounts[$id] ?? null;
        
        if (!$subAccount) {
            abort(404, 'Sub-Account not found');
        }
        
        return view('quicksms.account.sub-account-detail', [
            'page_title' => $subAccount['name'],
            'sub_account' => $subAccount
        ]);
    }
    
    public function userDetail($subId, $userId)
    {
        $subAccounts = [
            'sub-001' => ['id' => 'sub-001', 'name' => 'Marketing Department'],
            'sub-002' => ['id' => 'sub-002', 'name' => 'Customer Support'],
            'sub-003' => ['id' => 'sub-003', 'name' => 'Sales Team'],
        ];
        
        $users = [
            'user-001' => [
                'id' => 'user-001',
                'name' => 'Emma Thompson',
                'email' => 'emma.thompson@company.com',
                'role' => 'messaging-manager',
                'role_label' => 'Messaging Manager',
                'status' => 'active',
                'sender_capability' => 'advanced',
                'created_at' => '2024-06-20',
                'last_login' => '2026-01-16 09:45',
                'mfa_enabled' => true,
                'monthly_spend' => 450.00,
                'monthly_messages' => 15200,
                'sub_account_id' => 'sub-001'
            ],
            'user-002' => [
                'id' => 'user-002',
                'name' => 'Michael Brown',
                'email' => 'michael.brown@company.com',
                'role' => 'admin',
                'role_label' => 'Admin',
                'status' => 'active',
                'sender_capability' => 'advanced',
                'created_at' => '2024-07-15',
                'last_login' => '2026-01-15 14:20',
                'mfa_enabled' => true,
                'monthly_spend' => 320.50,
                'monthly_messages' => 10800,
                'sub_account_id' => 'sub-001'
            ],
            'user-003' => [
                'id' => 'user-003',
                'name' => 'Sarah Wilson',
                'email' => 'sarah.wilson@company.com',
                'role' => 'finance',
                'role_label' => 'Finance/Billing',
                'status' => 'active',
                'sender_capability' => null,
                'created_at' => '2024-08-01',
                'last_login' => '2026-01-14 11:30',
                'mfa_enabled' => true,
                'monthly_spend' => 0,
                'monthly_messages' => 0,
                'sub_account_id' => 'sub-002'
            ],
            'user-004' => [
                'id' => 'user-004',
                'name' => 'Chris Martinez',
                'email' => 'chris.martinez@company.com',
                'role' => 'messaging-manager',
                'role_label' => 'Messaging Manager',
                'status' => 'suspended',
                'sender_capability' => 'restricted',
                'created_at' => '2024-09-10',
                'last_login' => '2026-01-10 08:15',
                'mfa_enabled' => false,
                'monthly_spend' => 0,
                'monthly_messages' => 0,
                'sub_account_id' => 'sub-002'
            ],
        ];
        
        $subAccount = $subAccounts[$subId] ?? null;
        $user = $users[$userId] ?? null;
        
        if (!$subAccount || !$user) {
            abort(404, 'User not found');
        }
        
        return view('quicksms.account.user-detail', [
            'page_title' => $user['name'],
            'sub_account' => $subAccount,
            'user' => $user
        ]);
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
