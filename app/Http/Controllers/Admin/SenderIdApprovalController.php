<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Notification;
use App\Models\SenderId;
use App\Models\SenderIdComment;
use App\Models\SenderIdStatusHistory;
use App\Services\SenderIdValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Admin SenderID Approval Controller
 *
 * Handles admin-side approval workflow for SenderID requests.
 * All routes protected by AdminIpAllowlist + AdminAuthenticate middleware.
 *
 * Transitions handled:
 * - submitted → in_review (admin picks up)
 * - in_review → approved (admin approves)
 * - in_review → rejected (admin rejects with reason)
 * - in_review → pending_info (admin requests more info)
 * - approved → suspended (admin suspends)
 * - suspended → approved (admin reactivates)
 * - suspended → revoked (admin permanently removes)
 */
class SenderIdApprovalController extends Controller
{
    protected SenderIdValidationService $validationService;

    public function __construct(SenderIdValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * List all SenderID requests (with filtering)
     * GET /admin/api/sender-ids
     */
    public function index(Request $request): JsonResponse
    {
        $query = SenderId::withoutGlobalScope('tenant')
            ->with(['account:id,company_name,account_number', 'createdBy:id,email,first_name,last_name']);

        if ($status = $request->input('status')) {
            $query->where('workflow_status', $status);
        }

        if ($accountId = $request->input('account_id')) {
            $query->where('account_id', $accountId);
        }

        if ($type = $request->input('sender_type')) {
            $query->where('sender_type', $type);
        }

        $requests = $query->orderByRaw("CASE WHEN workflow_status = 'info_provided' THEN 0 ELSE 1 END ASC")
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    /**
     * Get single SenderID detail (admin view with RED side data)
     * GET /admin/api/sender-ids/{uuid}
     */
    public function show(string $uuid): JsonResponse
    {
        $senderId = SenderId::withoutGlobalScope('tenant')
            ->where('uuid', $uuid)
            ->with([
                'account:id,company_name,account_number,status,created_at',
                'createdBy:id,email,first_name,last_name',
                'reviewedBy:id,email,first_name,last_name',
                'statusHistory',
                'assignments',
            ])
            ->first();

        if (!$senderId) {
            return response()->json(['success' => false, 'error' => 'SenderID not found.'], 404);
        }

        // Run anti-spoofing check for admin context
        $spoofingCheck = $this->validationService->checkAntiSpoofing($senderId->sender_id_value);

        $accountData = $senderId->account?->only(['id', 'company_name', 'account_number', 'status']);
        if ($accountData && $senderId->account) {
            $accountData['created_at'] = $senderId->account->created_at;
            $tenantId = $senderId->account->id;
            $accountData['approved_sender_ids'] = SenderId::withoutGlobalScope('tenant')
                ->where('account_id', $tenantId)
                ->where('workflow_status', SenderId::STATUS_APPROVED)
                ->count();
            $accountData['rejected_sender_ids'] = SenderId::withoutGlobalScope('tenant')
                ->where('account_id', $tenantId)
                ->where('workflow_status', SenderId::STATUS_REJECTED)
                ->count();
        }

        $comments = $senderId->comments()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($c) => $c->toAdminArray());

        return response()->json([
            'success' => true,
            'data' => $senderId->toAdminArray(),
            'spoofing_check' => $spoofingCheck,
            'status_history' => $senderId->statusHistory,
            'comments' => $comments,
            'account' => $accountData,
        ]);
    }

    /**
     * Start review (submitted → in_review)
     * POST /admin/api/sender-ids/{uuid}/review
     */
    public function startReview(Request $request, string $uuid): JsonResponse
    {
        return $this->performTransition($request, $uuid, SenderId::STATUS_IN_REVIEW);
    }

    /**
     * Approve a SenderID (in_review → approved)
     * POST /admin/api/sender-ids/{uuid}/approve
     */
    public function approve(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        return $this->performTransition($request, $uuid, SenderId::STATUS_APPROVED, null, $request->input('notes'));
    }

    /**
     * Reject a SenderID (in_review → rejected)
     * POST /admin/api/sender-ids/{uuid}/reject
     */
    public function reject(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ]);

        return $this->performTransition($request, $uuid, SenderId::STATUS_REJECTED, $validated['reason'], $validated['notes'] ?? null);
    }

    /**
     * Request more information (in_review → pending_info)
     * POST /admin/api/sender-ids/{uuid}/request-info
     */
    public function requestInfo(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:2000',
        ]);

        $senderId = SenderId::withoutGlobalScope('tenant')
            ->where('uuid', $uuid)
            ->first();

        if (!$senderId) {
            return response()->json(['success' => false, 'error' => 'SenderID not found.'], 404);
        }

        $response = $this->performTransition($request, $uuid, SenderId::STATUS_PENDING_INFO, null, $validated['notes']);

        $responseData = $response->getData(true);
        if ($responseData['success'] ?? false) {
            try {
                Notification::create([
                    'tenant_id' => $senderId->account_id,
                    'type' => 'SENDERID_RETURNED',
                    'severity' => 'warning',
                    'title' => "Sender ID needs more information",
                    'body' => "Your Sender ID '{$senderId->sender_id_value}' was returned with comments. Please review and resubmit.",
                    'deep_link' => "/management/sms-sender-id?view={$senderId->uuid}",
                    'meta' => [
                        'sender_id_value' => $senderId->sender_id_value,
                        'request_uuid' => $senderId->uuid,
                        'request_id' => $senderId->id,
                    ],
                ]);

                $adminUser = $request->user();
                SenderIdComment::create([
                    'sender_id_id' => $senderId->id,
                    'comment_type' => SenderIdComment::TYPE_CUSTOMER,
                    'comment_text' => $validated['notes'],
                    'created_by_actor_type' => SenderIdComment::ACTOR_ADMIN,
                    'created_by_actor_id' => $adminUser->id ?? null,
                    'created_by_actor_name' => $adminUser ? (($adminUser->first_name ?? '') . ' ' . ($adminUser->last_name ?? '')) : 'Admin',
                ]);

                $this->logGovernanceEvent(
                    $senderId,
                    ['workflow_status' => 'in_review'],
                    'SENDERID_RETURNED_TO_CUSTOMER',
                    $request,
                    $validated['notes']
                );
            } catch (\Exception $e) {
                Log::error('[SenderIdApproval] Failed to create notification/comment: ' . $e->getMessage());
            }
        }

        return $response;
    }

    /**
     * Suspend an approved SenderID (approved → suspended)
     * POST /admin/api/sender-ids/{uuid}/suspend
     */
    public function suspend(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        return $this->performTransition($request, $uuid, SenderId::STATUS_SUSPENDED, $validated['reason']);
    }

    /**
     * Reactivate a suspended SenderID (suspended → approved)
     * POST /admin/api/sender-ids/{uuid}/reactivate
     */
    public function reactivate(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        return $this->performTransition($request, $uuid, SenderId::STATUS_APPROVED, null, $request->input('notes'));
    }

    /**
     * Permanently revoke a SenderID (suspended → revoked)
     * POST /admin/api/sender-ids/{uuid}/revoke
     */
    public function revoke(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        return $this->performTransition($request, $uuid, SenderId::STATUS_REVOKED, $validated['reason']);
    }

    // =====================================================
    // PRIVATE HELPERS
    // =====================================================

    /**
     * Generic transition handler with governance audit logging
     */
    private function performTransition(
        Request $request,
        string $uuid,
        string $targetStatus,
        ?string $reason = null,
        ?string $notes = null
    ): JsonResponse {
        $senderId = SenderId::withoutGlobalScope('tenant')
            ->where('uuid', $uuid)
            ->first();

        if (!$senderId) {
            return response()->json(['success' => false, 'error' => 'SenderID not found.'], 404);
        }

        $beforeState = $senderId->toAdminArray();

        try {
            $adminUser = $request->user();
            $adminId = $adminUser->id ?? null;

            $senderId->transitionTo(
                $targetStatus,
                $adminId,
                $reason,
                $notes,
                $adminUser
            );

            // Also update admin_notes if provided
            if ($notes && $targetStatus !== SenderId::STATUS_PENDING_INFO) {
                $existingNotes = $senderId->admin_notes ?? '';
                $senderId->update([
                    'admin_notes' => trim($existingNotes . "\n[" . now()->toIso8601String() . "] " . $notes),
                ]);
            }

            // Log governance audit event
            $this->logGovernanceEvent($senderId, $beforeState, $targetStatus, $request);

            return response()->json([
                'success' => true,
                'data' => $senderId->fresh()->toAdminArray(),
                'message' => "SenderID status changed to '{$targetStatus}'.",
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[SenderIdApproval] Transition failed', [
                'uuid' => $uuid,
                'target' => $targetStatus,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update SenderID status.',
            ], 500);
        }
    }

    /**
     * Log approval event to governance_audit_events table
     */
    private function logGovernanceEvent(
        SenderId $senderId,
        array $beforeState,
        string $newStatusOrEventType,
        Request $request,
        ?string $reason = null
    ): void {
        try {
            $customEventTypes = [
                'SENDERID_RETURNED_TO_CUSTOMER',
                'SENDERID_ADMIN_REVIEW_REOPENED',
                'SENDERID_COMMENT_ADDED',
            ];

            $isCustomEvent = in_array($newStatusOrEventType, $customEventTypes);
            $eventType = $isCustomEvent ? $newStatusOrEventType : 'SENDER_ID_STATUS_CHANGED';
            $afterStatus = $isCustomEvent ? ($senderId->workflow_status) : $newStatusOrEventType;

            DB::table('governance_audit_events')->insert([
                'event_uuid' => Str::uuid()->toString(),
                'event_type' => $eventType,
                'entity_type' => 'sender_id',
                'entity_id' => $senderId->id,
                'account_id' => null,
                'sub_account_id' => null,
                'actor_id' => $request->user()->id ?? null,
                'actor_type' => 'ADMIN',
                'actor_email' => $request->user()->email ?? 'admin@quicksms.co.uk',
                'before_state' => json_encode([
                    'workflow_status' => $beforeState['workflow_status'] ?? null,
                    'sender_id_value' => $beforeState['sender_id_value'] ?? $senderId->sender_id_value,
                    'uuid' => $beforeState['uuid'] ?? $senderId->uuid,
                ]),
                'after_state' => json_encode([
                    'workflow_status' => $afterStatus,
                    'sender_id_value' => $senderId->sender_id_value,
                    'uuid' => $senderId->uuid,
                ]),
                'reason' => $reason ?? $senderId->rejection_reason ?? $senderId->suspension_reason ?? $senderId->revocation_reason ?? null,
                'source_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('[SenderIdApproval] Failed to log governance event: ' . $e->getMessage());
        }
    }
}
