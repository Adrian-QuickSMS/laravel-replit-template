<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Notification;
use App\Models\SenderId;
use App\Models\SenderIdAssignment;
use App\Models\SenderIdComment;
use App\Models\SubAccount;
use App\Models\User;
use App\Services\SenderIdValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Customer Portal SenderID Controller
 *
 * Handles CRUD operations for SenderID registration and management.
 * All routes are protected by customer.auth middleware.
 * Tenant isolation enforced via SenderId global scope + RLS.
 */
class SenderIdController extends Controller
{
    protected SenderIdValidationService $validationService;

    public function __construct(SenderIdValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    // =====================================================
    // VIEW ROUTES (render existing blade templates)
    // =====================================================

    /**
     * SenderID list page
     */
    public function index(Request $request)
    {
        $senderIds = SenderId::where('account_id', session('customer_tenant_id'))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('quicksms.management.sms-sender-id', [
            'page_title' => 'SMS SenderID Registration',
            'sender_ids' => $senderIds,
        ]);
    }

    /**
     * SenderID registration wizard
     */
    public function create(Request $request)
    {
        $subAccounts = SubAccount::where('account_id', session('customer_tenant_id'))
            ->active()
            ->get();

        return view('quicksms.management.sms-sender-id-wizard', [
            'page_title' => 'Register SenderID',
            'sub_accounts' => $subAccounts,
        ]);
    }

    // =====================================================
    // API ENDPOINTS (JSON)
    // =====================================================

    /**
     * Store a new SenderID (draft or submitted)
     * POST /api/sender-ids
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sender_id_value' => 'required|string|max:15',
            'sender_type' => 'required|in:ALPHA,NUMERIC,SHORTCODE',
            'brand_name' => 'required|string|max:255',
            'country_code' => 'sometimes|string|size:2',
            'use_case' => 'required|in:transactional,promotional,otp,mixed',
            'use_case_description' => 'nullable|string|max:2000',
            'permission_confirmed' => 'required|boolean',
            'permission_explanation' => 'nullable|string|max:2000',
            'submit' => 'sometimes|boolean',
            'sub_account_ids' => 'nullable|array',
            'sub_account_ids.*' => 'uuid',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'uuid',
        ]);

        // Permission must be confirmed
        if (!$validated['permission_confirmed']) {
            return response()->json([
                'success' => false,
                'error' => 'You must confirm you have permission to use this SenderID.',
            ], 422);
        }

        // Run full validation (type + anti-spoofing)
        $validation = $this->validationService->fullValidation(
            $validated['sender_id_value'],
            $validated['sender_type']
        );

        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'errors' => $validation['errors'],
                'spoofing' => $validation['spoofing'],
            ], 422);
        }

        $accountId = session('customer_tenant_id');
        $userId = session('customer_user_id');

        try {
            DB::beginTransaction();

            $senderId = SenderId::create([
                'account_id' => $accountId,
                'sender_id_value' => $validated['sender_id_value'],
                'sender_type' => $validated['sender_type'],
                'brand_name' => $validated['brand_name'],
                'country_code' => $validated['country_code'] ?? 'GB',
                'use_case' => $validated['use_case'],
                'use_case_description' => $validated['use_case_description'] ?? null,
                'permission_confirmed' => true,
                'permission_explanation' => $validated['permission_explanation'] ?? null,
                'workflow_status' => SenderId::STATUS_DRAFT,
                'created_by' => $userId,
            ]);

            // Handle sub-account assignments
            if (!empty($validated['sub_account_ids'])) {
                foreach ($validated['sub_account_ids'] as $subAccountId) {
                    // Verify sub-account belongs to this account
                    $subAccount = SubAccount::where('id', $subAccountId)
                        ->where('account_id', $accountId)
                        ->first();

                    if ($subAccount) {
                        SenderIdAssignment::create([
                            'sender_id_id' => $senderId->id,
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
                    // Verify user belongs to this account
                    $user = User::where('id', $assignUserId)
                        ->where('tenant_id', $accountId)
                        ->first();

                    if ($user) {
                        SenderIdAssignment::create([
                            'sender_id_id' => $senderId->id,
                            'assignable_type' => User::class,
                            'assignable_id' => $assignUserId,
                            'assigned_by' => $userId,
                        ]);
                    }
                }
            }

            // If spoofing check returned 'quarantine', store in admin_notes for reviewer
            if ($validation['spoofing'] && $validation['spoofing']['action'] === 'quarantine') {
                $senderId->update([
                    'admin_notes' => 'AUTO-FLAGGED: SenderID triggered quarantine rule. ' .
                        'Matched: ' . ($validation['spoofing']['matched_rule']['name'] ?? 'unknown') . '. ' .
                        'Normalised form: ' . $validation['spoofing']['normalised'],
                ]);
            }

            // Auto-submit if requested
            if (!empty($validated['submit'])) {
                $actingUser = User::withoutGlobalScope('tenant')->find($userId);
                $senderId->transitionTo(
                    SenderId::STATUS_SUBMITTED,
                    $userId,
                    null,
                    null,
                    $actingUser
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $senderId->toPortalArray(),
                'message' => !empty($validated['submit'])
                    ? 'SenderID submitted for review.'
                    : 'SenderID saved as draft.',
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[SenderIdController] Failed to create SenderID', [
                'error' => $e->getMessage(),
                'account_id' => $accountId,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create SenderID. Please try again.',
            ], 500);
        }
    }

    /**
     * Get a single SenderID by UUID
     * GET /api/sender-ids/{uuid}
     */
    public function show(string $uuid): JsonResponse
    {
        $senderId = SenderId::where('uuid', $uuid)
            ->where('account_id', session('customer_tenant_id'))
            ->first();

        if (!$senderId) {
            return response()->json(['success' => false, 'error' => 'SenderID not found.'], 404);
        }

        $customerComments = $senderId->customerComments()
            ->get()
            ->map(fn($c) => $c->toPortalArray());

        $latestReturnHistory = null;
        if ($senderId->workflow_status === SenderId::STATUS_PENDING_INFO) {
            $latestReturnHistory = $senderId->statusHistory()
                ->where('to_status', 'pending_info')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        return response()->json([
            'success' => true,
            'data' => $senderId->toPortalArray(),
            'assignments' => $senderId->assignments->map(function ($a) {
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
     * Update a draft SenderID
     * PUT /api/sender-ids/{uuid}
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $senderId = SenderId::where('uuid', $uuid)
            ->where('account_id', session('customer_tenant_id'))
            ->first();

        if (!$senderId) {
            return response()->json(['success' => false, 'error' => 'SenderID not found.'], 404);
        }

        if (!$senderId->isEditable()) {
            return response()->json([
                'success' => false,
                'error' => 'This SenderID cannot be edited in its current status.',
            ], 422);
        }

        $validated = $request->validate([
            'sender_id_value' => 'sometimes|string|max:15',
            'sender_type' => 'sometimes|in:ALPHA,NUMERIC,SHORTCODE',
            'brand_name' => 'sometimes|string|max:255',
            'use_case' => 'sometimes|in:transactional,promotional,otp,mixed',
            'use_case_description' => 'nullable|string|max:2000',
            'permission_confirmed' => 'sometimes|boolean',
            'permission_explanation' => 'nullable|string|max:2000',
            'sub_account_ids' => 'nullable|array',
            'sub_account_ids.*' => 'uuid',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'uuid',
        ]);

        // If sender_id_value or sender_type changed, re-validate
        $senderValue = $validated['sender_id_value'] ?? $senderId->sender_id_value;
        $senderType = $validated['sender_type'] ?? $senderId->sender_type;

        if (isset($validated['sender_id_value']) || isset($validated['sender_type'])) {
            $validation = $this->validationService->fullValidation($senderValue, $senderType);
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'errors' => $validation['errors'],
                    'spoofing' => $validation['spoofing'],
                ], 422);
            }
        }

        try {
            DB::beginTransaction();

            $senderId->update($validated);

            // Update assignments if provided
            $accountId = session('customer_tenant_id');
            $userId = session('customer_user_id');

            if (array_key_exists('sub_account_ids', $validated)) {
                // Remove existing sub-account assignments
                $senderId->assignments()
                    ->where('assignable_type', SubAccount::class)
                    ->delete();

                // Add new ones
                foreach ($validated['sub_account_ids'] ?? [] as $subAccountId) {
                    $subAccount = SubAccount::where('id', $subAccountId)
                        ->where('account_id', $accountId)
                        ->first();

                    if ($subAccount) {
                        SenderIdAssignment::create([
                            'sender_id_id' => $senderId->id,
                            'assignable_type' => SubAccount::class,
                            'assignable_id' => $subAccountId,
                            'assigned_by' => $userId,
                        ]);
                    }
                }
            }

            if (array_key_exists('user_ids', $validated)) {
                $senderId->assignments()
                    ->where('assignable_type', User::class)
                    ->delete();

                foreach ($validated['user_ids'] ?? [] as $assignUserId) {
                    $user = User::where('id', $assignUserId)
                        ->where('tenant_id', $accountId)
                        ->first();

                    if ($user) {
                        SenderIdAssignment::create([
                            'sender_id_id' => $senderId->id,
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
                'data' => $senderId->fresh()->toPortalArray(),
                'message' => 'SenderID updated.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[SenderIdController] Failed to update SenderID', [
                'uuid' => $uuid,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update SenderID.',
            ], 500);
        }
    }

    /**
     * Submit a draft SenderID for review
     * POST /api/sender-ids/{uuid}/submit
     */
    public function submit(string $uuid): JsonResponse
    {
        $senderId = SenderId::where('uuid', $uuid)
            ->where('account_id', session('customer_tenant_id'))
            ->first();

        if (!$senderId) {
            return response()->json(['success' => false, 'error' => 'SenderID not found.'], 404);
        }

        if ($senderId->workflow_status !== SenderId::STATUS_DRAFT) {
            return response()->json([
                'success' => false,
                'error' => 'Only draft SenderIDs can be submitted for review.',
            ], 422);
        }

        // Final validation before submission
        $validation = $this->validationService->fullValidation(
            $senderId->sender_id_value,
            $senderId->sender_type
        );

        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'errors' => $validation['errors'],
            ], 422);
        }

        try {
            $userId = session('customer_user_id');
            $actingUser = User::withoutGlobalScope('tenant')->find($userId);

            $senderId->transitionTo(
                SenderId::STATUS_SUBMITTED,
                $userId,
                null,
                null,
                $actingUser
            );

            return response()->json([
                'success' => true,
                'data' => $senderId->toPortalArray(),
                'message' => 'SenderID submitted for review. Typically reviewed within 1-2 business days.',
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
     * POST /api/sender-ids/{uuid}/provide-info
     */
    public function provideInfo(Request $request, string $uuid): JsonResponse
    {
        $senderId = SenderId::where('uuid', $uuid)
            ->where('account_id', session('customer_tenant_id'))
            ->first();

        if (!$senderId) {
            return response()->json(['success' => false, 'error' => 'SenderID not found.'], 404);
        }

        if (!$senderId->canCustomerProvideInfo()) {
            return response()->json([
                'success' => false,
                'error' => 'This SenderID is not awaiting additional information.',
            ], 422);
        }

        $validated = $request->validate([
            'additional_info' => 'required|string|max:5000',
        ]);

        try {
            $userId = session('customer_user_id');
            $actingUser = User::withoutGlobalScope('tenant')->find($userId);

            $senderId->transitionTo(
                SenderId::STATUS_INFO_PROVIDED,
                $userId,
                $validated['additional_info'],
                null,
                $actingUser
            );

            SenderIdComment::create([
                'sender_id_id' => $senderId->id,
                'comment_type' => SenderIdComment::TYPE_CUSTOMER,
                'comment_text' => $validated['additional_info'],
                'created_by_actor_type' => SenderIdComment::ACTOR_CUSTOMER,
                'created_by_actor_id' => $userId,
                'created_by_actor_name' => $actingUser ? trim(($actingUser->first_name ?? '') . ' ' . ($actingUser->last_name ?? '')) : null,
            ]);

            Notification::where('type', 'SENDERID_RETURNED')
                ->where('tenant_id', $senderId->account_id)
                ->whereJsonContains('meta->request_uuid', $senderId->uuid)
                ->whereNull('resolved_at')
                ->update(['resolved_at' => now()]);

            try {
                $senderId->loadMissing('account');
                AdminNotification::create([
                    'type' => 'CUSTOMER_RESPONDED_SENDERID',
                    'severity' => 'info',
                    'title' => 'Customer responded to SenderID review',
                    'body' => ($senderId->account->company_name ?? 'Unknown') . ' has provided additional info for SenderID ' . $senderId->sender_id_value,
                    'deep_link' => '/admin/assets/sender-ids/' . $senderId->id,
                    'meta' => [
                        'request_uuid' => $senderId->uuid,
                        'sender_id_value' => $senderId->sender_id_value,
                        'account_name' => $senderId->account->company_name ?? 'Unknown',
                        'account_id' => $senderId->account_id,
                    ],
                ]);
            } catch (\Exception $e) {
                Log::error('[SenderIdController] Failed to create admin notification: ' . $e->getMessage());
            }

            try {
                DB::table('governance_audit_events')->insert([
                    'event_uuid' => Str::uuid()->toString(),
                    'event_type' => 'SENDERID_CUSTOMER_RESUBMITTED',
                    'entity_type' => 'sender_id',
                    'entity_id' => $senderId->id,
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
                Log::error('[SenderIdController] Failed to log governance event: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'data' => $senderId->toPortalArray(),
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
     * Get list of approved SenderIDs for the current user
     * Used by Send Message, Templates, Email-to-SMS dropdowns
     * GET /api/sender-ids/approved
     */
    public function approved(Request $request): JsonResponse
    {
        $userId = session('customer_user_id');
        $user = User::withoutGlobalScope('tenant')->find($userId);

        if (!$user) {
            return response()->json(['success' => false, 'error' => 'User not found.'], 404);
        }

        $senderIds = SenderId::usableByUser($user)->get();

        return response()->json([
            'success' => true,
            'data' => $senderIds->map(fn($sid) => [
                'id' => $sid->id,
                'uuid' => $sid->uuid,
                'sender_id_value' => $sid->sender_id_value,
                'sender_type' => $sid->sender_type,
                'brand_name' => $sid->brand_name,
                'is_default' => $sid->is_default,
            ]),
        ]);
    }

    /**
     * Validate a SenderID value (AJAX validation for the wizard)
     * POST /api/sender-ids/validate
     */
    public function validateSenderId(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sender_id_value' => 'required|string|max:15',
            'sender_type' => 'required|in:ALPHA,NUMERIC,SHORTCODE',
        ]);

        $result = $this->validationService->fullValidation(
            $validated['sender_id_value'],
            $validated['sender_type']
        );

        return response()->json([
            'valid' => $result['valid'],
            'errors' => $result['errors'],
            'spoofing' => $result['spoofing'] ? [
                'passed' => $result['spoofing']['passed'],
                'action' => $result['spoofing']['action'],
                'normalised' => $result['spoofing']['normalised'],
            ] : null,
        ]);
    }

    /**
     * Delete a draft SenderID (soft-delete)
     * DELETE /api/sender-ids/{uuid}
     */
    public function destroy(string $uuid): JsonResponse
    {
        $senderId = SenderId::where('uuid', $uuid)
            ->where('account_id', session('customer_tenant_id'))
            ->first();

        if (!$senderId) {
            return response()->json(['success' => false, 'error' => 'SenderID not found.'], 404);
        }

        if ($senderId->workflow_status !== SenderId::STATUS_DRAFT) {
            return response()->json([
                'success' => false,
                'error' => 'Only draft SenderIDs can be deleted.',
            ], 422);
        }

        $senderId->delete();

        return response()->json([
            'success' => true,
            'message' => 'SenderID deleted successfully.',
        ]);
    }

    /**
     * Get users for given sub-account IDs (tenant-validated)
     * POST /api/sub-accounts/users
     */
    public function subAccountUsers(Request $request): JsonResponse
    {
        $request->validate([
            'sub_account_ids' => 'required|array',
            'sub_account_ids.*' => 'integer',
        ]);

        $tenantId = session('customer_tenant_id');

        $subAccounts = SubAccount::where('account_id', $tenantId)
            ->whereIn('id', $request->input('sub_account_ids'))
            ->get();

        if ($subAccounts->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $validSubAccountIds = $subAccounts->pluck('id');

        $users = User::withoutGlobalScope('tenant')
            ->where('account_id', $tenantId)
            ->whereIn('sub_account_id', $validSubAccountIds)
            ->where('is_active', true)
            ->select('id', 'first_name', 'last_name', 'email', 'sub_account_id')
            ->orderBy('first_name')
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => trim($u->first_name . ' ' . $u->last_name),
                'email' => $u->email,
                'sub_account_id' => $u->sub_account_id,
            ]);

        return response()->json(['success' => true, 'data' => $users]);
    }

    /**
     * Re-edit a rejected SenderID (transition back to draft)
     * POST /api/sender-ids/{uuid}/resubmit
     */
    public function resubmit(string $uuid): JsonResponse
    {
        $senderId = SenderId::where('uuid', $uuid)
            ->where('account_id', session('customer_tenant_id'))
            ->first();

        if (!$senderId) {
            return response()->json(['success' => false, 'error' => 'SenderID not found.'], 404);
        }

        if ($senderId->workflow_status !== SenderId::STATUS_REJECTED) {
            return response()->json([
                'success' => false,
                'error' => 'Only rejected SenderIDs can be re-edited.',
            ], 422);
        }

        try {
            $userId = session('customer_user_id');
            $actingUser = User::withoutGlobalScope('tenant')->find($userId);

            $senderId->transitionTo(
                SenderId::STATUS_DRAFT,
                $userId,
                null,
                null,
                $actingUser
            );

            return response()->json([
                'success' => true,
                'data' => $senderId->toPortalArray(),
                'message' => 'SenderID returned to draft for editing.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
