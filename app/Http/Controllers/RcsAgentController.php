<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Notification;
use App\Models\RcsAgent;
use App\Models\RcsAgentAssignment;
use App\Models\RcsAgentComment;
use App\Models\Account;
use App\Models\SubAccount;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Customer Portal RCS Agent Controller
 *
 * Handles CRUD operations for RCS Agent registration and management.
 * All routes are protected by customer.auth middleware.
 * Tenant isolation enforced via RcsAgent global scope + RLS.
 *
 * Follows the SenderIdController pattern exactly.
 */
class RcsAgentController extends Controller
{
    // =====================================================
    // VIEW ROUTES (render existing blade templates)
    // =====================================================

    /**
     * RCS Agent list page
     */
    public function index(Request $request)
    {
        $agents = RcsAgent::where('account_id', session('customer_tenant_id'))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('quicksms.management.rcs-agent', [
            'page_title' => 'RCS Agent Registration',
            'agents' => $agents,
        ]);
    }

    /**
     * RCS Agent registration wizard
     */
    public function create(Request $request)
    {
        $tenantId = session('customer_tenant_id');

        $subAccounts = SubAccount::where('account_id', $tenantId)
            ->active()
            ->get();

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
            'sub_accounts' => $subAccounts,
            'company_defaults' => $companyDefaults,
            'approver_defaults' => $approverDefaults,
        ]);
    }

    // =====================================================
    // API ENDPOINTS (JSON)
    // =====================================================

    /**
     * List all RCS Agents for the current account
     * GET /api/rcs-agents
     */
    public function list(Request $request): JsonResponse
    {
        $accountId = session('customer_tenant_id');
        if (!$accountId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $agents = RcsAgent::where('account_id', $accountId)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn($a) => $a->toPortalArray());

        return response()->json([
            'success' => true,
            'data' => $agents,
        ]);
    }

    /**
     * Store a new RCS Agent (draft or submitted)
     * POST /api/rcs-agents
     */
    public function store(Request $request): JsonResponse
    {
        $isSubmit = !empty($request->input('submit'));

        $rules = [
            'name' => 'required|string|max:25',
            'description' => ($isSubmit ? 'required' : 'nullable') . '|string|max:100',
            'brand_color' => 'sometimes|string|max:7',
            'logo_url' => 'nullable|string',
            'logo_crop_metadata' => 'nullable|array',
            'hero_url' => 'nullable|string',
            'hero_crop_metadata' => 'nullable|array',
            'support_phone' => ($isSubmit ? 'required' : 'nullable') . '|string|max:20',
            'website' => ($isSubmit ? 'required' : 'nullable') . '|string|max:255',
            'support_email' => ($isSubmit ? 'required|email' : 'nullable|email') . '|max:255',
            'privacy_url' => ($isSubmit ? 'required|url' : 'nullable') . '|max:500',
            'terms_url' => ($isSubmit ? 'required|url' : 'nullable') . '|max:500',
            'show_phone' => 'sometimes|boolean',
            'show_website' => 'sometimes|boolean',
            'show_email' => 'sometimes|boolean',
            'billing_category' => ($isSubmit ? 'required' : 'nullable') . '|in:conversational,non-conversational',
            'use_case' => ($isSubmit ? 'required' : 'nullable') . '|in:otp,transactional,promotional,multi-use',
            'campaign_frequency' => ($isSubmit ? 'required' : 'nullable') . '|string|max:50',
            'monthly_volume' => ($isSubmit ? 'required' : 'nullable') . '|string|max:50',
            'opt_in_description' => ($isSubmit ? 'required' : 'nullable') . '|string|max:5000',
            'opt_out_description' => ($isSubmit ? 'required' : 'nullable') . '|string|max:5000',
            'use_case_overview' => ($isSubmit ? 'required' : 'nullable') . '|string|max:5000',
            'test_numbers' => 'nullable|array',
            'test_numbers.*' => 'string|max:20',
            'company_number' => ($isSubmit ? 'required' : 'nullable') . '|string|max:20',
            'company_website' => ($isSubmit ? 'required' : 'nullable') . '|string|max:255',
            'registered_address' => ($isSubmit ? 'required' : 'nullable') . '|string|max:2000',
            'approver_name' => ($isSubmit ? 'required' : 'nullable') . '|string|max:100',
            'approver_job_title' => ($isSubmit ? 'required' : 'nullable') . '|string|max:100',
            'approver_email' => ($isSubmit ? 'required|email' : 'nullable|email') . '|max:255',
            'sector' => 'nullable|string|max:100',
            'submit' => 'sometimes|boolean',
            'sub_account_ids' => 'nullable|array',
            'sub_account_ids.*' => 'uuid',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'uuid',
        ];

        $validated = $request->validate($rules);

        $accountId = session('customer_tenant_id');
        $userId = session('customer_user_id');

        try {
            DB::beginTransaction();

            $createData = [
                'account_id' => $accountId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'brand_color' => $validated['brand_color'] ?? '#886CC0',
                'logo_url' => $validated['logo_url'] ?? null,
                'logo_crop_metadata' => $validated['logo_crop_metadata'] ?? null,
                'hero_url' => $validated['hero_url'] ?? null,
                'hero_crop_metadata' => $validated['hero_crop_metadata'] ?? null,
                'support_phone' => $validated['support_phone'] ?? null,
                'website' => $validated['website'] ?? null,
                'support_email' => $validated['support_email'] ?? null,
                'privacy_url' => $validated['privacy_url'] ?? null,
                'terms_url' => $validated['terms_url'] ?? null,
                'show_phone' => $validated['show_phone'] ?? true,
                'show_website' => $validated['show_website'] ?? true,
                'show_email' => $validated['show_email'] ?? true,
                'campaign_frequency' => $validated['campaign_frequency'] ?? null,
                'monthly_volume' => $validated['monthly_volume'] ?? null,
                'opt_in_description' => $validated['opt_in_description'] ?? null,
                'opt_out_description' => $validated['opt_out_description'] ?? null,
                'use_case_overview' => $validated['use_case_overview'] ?? null,
                'test_numbers' => $validated['test_numbers'] ?? null,
                'company_number' => $validated['company_number'] ?? null,
                'company_website' => $validated['company_website'] ?? null,
                'registered_address' => $validated['registered_address'] ?? null,
                'approver_name' => $validated['approver_name'] ?? null,
                'approver_job_title' => $validated['approver_job_title'] ?? null,
                'approver_email' => $validated['approver_email'] ?? null,
                'sector' => $validated['sector'] ?? null,
                'workflow_status' => RcsAgent::STATUS_DRAFT,
                'created_by' => $userId,
            ];

            if (!empty($validated['billing_category'])) {
                $createData['billing_category'] = $validated['billing_category'];
            }
            if (!empty($validated['use_case'])) {
                $createData['use_case'] = $validated['use_case'];
            }

            $agent = RcsAgent::create($createData);

            // Handle sub-account assignments
            if (!empty($validated['sub_account_ids'])) {
                foreach ($validated['sub_account_ids'] as $subAccountId) {
                    $subAccount = SubAccount::where('id', $subAccountId)
                        ->where('account_id', $accountId)
                        ->first();

                    if ($subAccount) {
                        RcsAgentAssignment::create([
                            'rcs_agent_id' => $agent->id,
                            'assignable_type' => SubAccount::class,
                            'assignable_id' => $subAccountId,
                            'assigned_by' => $userId,
                        ]);
                    }
                }
            }

            // Handle user assignments
            if (!empty($validated['user_ids'])) {
                foreach ($validated['user_ids'] as $assignUserId) {
                    $user = User::where('id', $assignUserId)
                        ->where('tenant_id', $accountId)
                        ->first();

                    if ($user) {
                        RcsAgentAssignment::create([
                            'rcs_agent_id' => $agent->id,
                            'assignable_type' => User::class,
                            'assignable_id' => $assignUserId,
                            'assigned_by' => $userId,
                        ]);
                    }
                }
            }

            // Auto-submit if requested
            if (!empty($validated['submit'])) {
                $actingUser = User::withoutGlobalScope('tenant')->find($userId);
                $agent->transitionTo(
                    RcsAgent::STATUS_SUBMITTED,
                    $userId,
                    null,
                    null,
                    $actingUser
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $agent->toPortalArray(),
                'message' => !empty($validated['submit'])
                    ? 'RCS Agent submitted for review.'
                    : 'RCS Agent saved as draft.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[RcsAgentController] Failed to create RCS Agent', [
                'error' => $e->getMessage(),
                'account_id' => $accountId,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create RCS Agent. Please try again.',
            ], 500);
        }
    }

    /**
     * Get a single RCS Agent by UUID
     * GET /api/rcs-agents/{uuid}
     */
    public function show(string $uuid): JsonResponse
    {
        $agent = RcsAgent::where('uuid', $uuid)
            ->where('account_id', session('customer_tenant_id'))
            ->first();

        if (!$agent) {
            return response()->json(['success' => false, 'error' => 'RCS Agent not found.'], 404);
        }

        $customerComments = $agent->customerComments()
            ->get()
            ->map(fn($c) => $c->toPortalArray());

        $latestReturnHistory = null;
        if ($agent->workflow_status === RcsAgent::STATUS_PENDING_INFO) {
            $latestReturnHistory = $agent->statusHistory()
                ->where('to_status', 'pending_info')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        return response()->json([
            'success' => true,
            'data' => $agent->toPortalArray(),
            'assignments' => $agent->assignments->map(function ($a) {
                return [
                    'type' => class_basename($a->assignable_type),
                    'id' => $a->assignable_id,
                ];
            }),
            'comments' => $customerComments,
            'return_info' => $latestReturnHistory ? [
                'reason' => $latestReturnHistory->reason ?? $latestReturnHistory->notes,
                'returned_at' => $latestReturnHistory->created_at?->toIso8601String(),
            ] : null,
        ]);
    }

    /**
     * Update a draft RCS Agent
     * PUT /api/rcs-agents/{uuid}
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $agent = RcsAgent::where('uuid', $uuid)
            ->where('account_id', session('customer_tenant_id'))
            ->first();

        if (!$agent) {
            return response()->json(['success' => false, 'error' => 'RCS Agent not found.'], 404);
        }

        if (!$agent->isEditable()) {
            return response()->json([
                'success' => false,
                'error' => 'This RCS Agent cannot be edited in its current status.',
            ], 422);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:25',
            'description' => 'nullable|string|max:100',
            'brand_color' => 'sometimes|string|max:7',
            'logo_url' => 'nullable|string',
            'logo_crop_metadata' => 'nullable|array',
            'hero_url' => 'nullable|string',
            'hero_crop_metadata' => 'nullable|array',
            'support_phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'support_email' => 'nullable|email|max:255',
            'privacy_url' => 'nullable|max:500',
            'terms_url' => 'nullable|max:500',
            'show_phone' => 'sometimes|boolean',
            'show_website' => 'sometimes|boolean',
            'show_email' => 'sometimes|boolean',
            'billing_category' => 'nullable|in:conversational,non-conversational',
            'use_case' => 'nullable|in:otp,transactional,promotional,multi-use',
            'campaign_frequency' => 'nullable|string|max:50',
            'monthly_volume' => 'nullable|string|max:50',
            'opt_in_description' => 'nullable|string|max:5000',
            'opt_out_description' => 'nullable|string|max:5000',
            'use_case_overview' => 'nullable|string|max:5000',
            'test_numbers' => 'nullable|array',
            'test_numbers.*' => 'string|max:20',
            'company_number' => 'nullable|string|max:20',
            'company_website' => 'nullable|string|max:255',
            'registered_address' => 'nullable|string|max:2000',
            'approver_name' => 'nullable|string|max:100',
            'approver_job_title' => 'nullable|string|max:100',
            'approver_email' => 'nullable|email|max:255',
            'sector' => 'nullable|string|max:100',
            'sub_account_ids' => 'nullable|array',
            'sub_account_ids.*' => 'uuid',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'uuid',
        ]);

        try {
            DB::beginTransaction();

            $updateData = $validated;
            if (array_key_exists('billing_category', $updateData) && empty($updateData['billing_category'])) {
                unset($updateData['billing_category']);
            }
            if (array_key_exists('use_case', $updateData) && empty($updateData['use_case'])) {
                unset($updateData['use_case']);
            }

            $agent->update($updateData);

            // Update assignments if provided
            $accountId = session('customer_tenant_id');
            $userId = session('customer_user_id');

            if (array_key_exists('sub_account_ids', $validated)) {
                $agent->assignments()
                    ->where('assignable_type', SubAccount::class)
                    ->delete();

                foreach ($validated['sub_account_ids'] ?? [] as $subAccountId) {
                    $subAccount = SubAccount::where('id', $subAccountId)
                        ->where('account_id', $accountId)
                        ->first();

                    if ($subAccount) {
                        RcsAgentAssignment::create([
                            'rcs_agent_id' => $agent->id,
                            'assignable_type' => SubAccount::class,
                            'assignable_id' => $subAccountId,
                            'assigned_by' => $userId,
                        ]);
                    }
                }
            }

            if (array_key_exists('user_ids', $validated)) {
                $agent->assignments()
                    ->where('assignable_type', User::class)
                    ->delete();

                foreach ($validated['user_ids'] ?? [] as $assignUserId) {
                    $user = User::where('id', $assignUserId)
                        ->where('tenant_id', $accountId)
                        ->first();

                    if ($user) {
                        RcsAgentAssignment::create([
                            'rcs_agent_id' => $agent->id,
                            'assignable_type' => User::class,
                            'assignable_id' => $assignUserId,
                            'assigned_by' => $userId,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $agent->fresh()->toPortalArray(),
                'message' => 'RCS Agent updated.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[RcsAgentController] Failed to update RCS Agent', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update RCS Agent.',
            ], 500);
        }
    }

    /**
     * Submit a draft RCS Agent for review
     * POST /api/rcs-agents/{uuid}/submit
     */
    public function submit(string $uuid): JsonResponse
    {
        $agent = RcsAgent::where('uuid', $uuid)
            ->where('account_id', session('customer_tenant_id'))
            ->first();

        if (!$agent) {
            return response()->json(['success' => false, 'error' => 'RCS Agent not found.'], 404);
        }

        if (!in_array($agent->workflow_status, [RcsAgent::STATUS_DRAFT, RcsAgent::STATUS_PENDING_INFO])) {
            return response()->json([
                'success' => false,
                'error' => 'Only draft or returned RCS Agents can be submitted for review.',
            ], 422);
        }

        // Validate required fields before submission
        $missingFields = [];
        foreach (['name', 'description', 'support_phone', 'website', 'support_email', 'privacy_url', 'terms_url',
                   'billing_category', 'use_case', 'campaign_frequency', 'monthly_volume',
                   'opt_in_description', 'opt_out_description', 'use_case_overview',
                   'company_number', 'company_website', 'registered_address',
                   'approver_name', 'approver_job_title', 'approver_email'] as $field) {
            if (empty($agent->$field)) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            return response()->json([
                'success' => false,
                'error' => 'Please complete all required fields before submitting.',
                'missing_fields' => $missingFields,
            ], 422);
        }

        try {
            $userId = session('customer_user_id');
            $actingUser = User::withoutGlobalScope('tenant')->find($userId);

            $agent->transitionTo(
                RcsAgent::STATUS_SUBMITTED,
                $userId,
                null,
                null,
                $actingUser
            );

            return response()->json([
                'success' => true,
                'data' => $agent->toPortalArray(),
                'message' => 'RCS Agent submitted for review. Typically reviewed within 2-3 business days.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Provide additional information requested by admin
     * POST /api/rcs-agents/{uuid}/provide-info
     */
    public function provideInfo(Request $request, string $uuid): JsonResponse
    {
        $agent = RcsAgent::where('uuid', $uuid)
            ->where('account_id', session('customer_tenant_id'))
            ->first();

        if (!$agent) {
            return response()->json(['success' => false, 'error' => 'RCS Agent not found.'], 404);
        }

        if (!$agent->canCustomerProvideInfo()) {
            return response()->json([
                'success' => false,
                'error' => 'This RCS Agent is not awaiting additional information.',
            ], 422);
        }

        $validated = $request->validate([
            'additional_info' => 'required|string|max:5000',
        ]);

        try {
            $userId = session('customer_user_id');
            $actingUser = User::withoutGlobalScope('tenant')->find($userId);

            $agent->transitionTo(
                RcsAgent::STATUS_INFO_PROVIDED,
                $userId,
                $validated['additional_info'],
                null,
                $actingUser
            );

            RcsAgentComment::create([
                'rcs_agent_id' => $agent->id,
                'comment_type' => RcsAgentComment::TYPE_CUSTOMER,
                'comment_text' => $validated['additional_info'],
                'created_by_actor_type' => RcsAgentComment::ACTOR_CUSTOMER,
                'created_by_actor_id' => $userId,
                'created_by_actor_name' => $actingUser ? trim(($actingUser->first_name ?? '') . ' ' . ($actingUser->last_name ?? '')) : null,
            ]);

            // Resolve any outstanding return notifications
            Notification::where('type', 'RCS_AGENT_RETURNED')
                ->where('tenant_id', $agent->account_id)
                ->whereJsonContains('meta->request_uuid', $agent->uuid)
                ->whereNull('resolved_at')
                ->update(['resolved_at' => now()]);

            // Notify admin that customer responded
            try {
                $agent->loadMissing('account');
                AdminNotification::create([
                    'type' => 'CUSTOMER_RESPONDED_RCS_AGENT',
                    'severity' => 'info',
                    'title' => 'Customer responded to RCS Agent review',
                    'body' => ($agent->account->company_name ?? 'Unknown') . ' has provided additional info for RCS Agent "' . $agent->name . '"',
                    'deep_link' => '/admin/assets/rcs-agents/' . $agent->id,
                    'meta' => [
                        'request_uuid' => $agent->uuid,
                        'agent_name' => $agent->name,
                        'account_name' => $agent->account->company_name ?? 'Unknown',
                        'account_id' => $agent->account_id,
                    ],
                ]);
            } catch (\Exception $e) {
                Log::error('[RcsAgentController] Failed to create admin notification: ' . $e->getMessage());
            }

            // Log governance audit event
            try {
                DB::table('governance_audit_events')->insert([
                    'event_uuid' => Str::uuid()->toString(),
                    'event_type' => 'RCS_AGENT_CUSTOMER_RESUBMITTED',
                    'entity_type' => 'rcs_agent',
                    'entity_id' => $agent->id,
                    'account_id' => null,
                    'sub_account_id' => null,
                    'actor_id' => $userId,
                    'actor_type' => 'CUSTOMER',
                    'actor_email' => $actingUser->email ?? null,
                    'before_state' => json_encode(['workflow_status' => 'pending_info']),
                    'after_state' => json_encode(['workflow_status' => 'info_provided']),
                    'reason' => null,
                    'source_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                Log::error('[RcsAgentController] Failed to log governance event: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'data' => $agent->toPortalArray(),
                'message' => 'Your response has been submitted for review.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get list of approved RCS Agents for the current user
     * Used by RCS Send Message dropdowns
     * GET /api/rcs-agents/approved
     */
    public function approved(Request $request): JsonResponse
    {
        $userId = session('customer_user_id');
        $user = User::withoutGlobalScope('tenant')->find($userId);

        if (!$user) {
            return response()->json(['success' => false, 'error' => 'User not found.'], 404);
        }

        $agents = RcsAgent::usableByUser($user)->get();

        return response()->json([
            'success' => true,
            'data' => $agents->map(fn($a) => [
                'id' => $a->id,
                'uuid' => $a->uuid,
                'name' => $a->name,
                'description' => $a->description,
                'brand_color' => $a->brand_color,
                'logo_url' => $a->logo_url,
            ]),
        ]);
    }

    /**
     * Delete a draft RCS Agent (soft-delete)
     * DELETE /api/rcs-agents/{uuid}
     */
    public function destroy(string $uuid): JsonResponse
    {
        $agent = RcsAgent::where('uuid', $uuid)
            ->where('account_id', session('customer_tenant_id'))
            ->first();

        if (!$agent) {
            return response()->json(['success' => false, 'error' => 'RCS Agent not found.'], 404);
        }

        if ($agent->workflow_status !== RcsAgent::STATUS_DRAFT) {
            return response()->json([
                'success' => false,
                'error' => 'Only draft RCS Agents can be deleted.',
            ], 422);
        }

        $agent->delete();

        return response()->json([
            'success' => true,
            'message' => 'RCS Agent deleted successfully.',
        ]);
    }

    /**
     * Re-edit a rejected RCS Agent (transition back to draft)
     * POST /api/rcs-agents/{uuid}/resubmit
     */
    public function resubmit(string $uuid): JsonResponse
    {
        $agent = RcsAgent::where('uuid', $uuid)
            ->where('account_id', session('customer_tenant_id'))
            ->first();

        if (!$agent) {
            return response()->json(['success' => false, 'error' => 'RCS Agent not found.'], 404);
        }

        if (!in_array($agent->workflow_status, [RcsAgent::STATUS_REJECTED, RcsAgent::STATUS_PENDING_INFO])) {
            return response()->json([
                'success' => false,
                'error' => 'Only rejected or returned RCS Agents can be re-edited.',
            ], 422);
        }

        try {
            $userId = session('customer_user_id');
            $actingUser = User::withoutGlobalScope('tenant')->find($userId);

            $agent->transitionTo(
                RcsAgent::STATUS_DRAFT,
                $userId,
                null,
                null,
                $actingUser
            );

            return response()->json([
                'success' => true,
                'data' => $agent->toPortalArray(),
                'message' => 'RCS Agent returned to draft for editing.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
