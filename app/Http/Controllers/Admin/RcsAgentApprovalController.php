<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Notification;
use App\Models\RcsAgent;
use App\Models\RcsAgentComment;
use App\Models\RcsAgentStatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Admin RCS Agent Approval Controller
 *
 * Handles admin-side approval workflow for RCS Agent requests.
 * All routes protected by AdminIpAllowlist + AdminAuthenticate middleware.
 *
 * Follows the SenderIdApprovalController pattern exactly.
 *
 * Transitions handled:
 * - submitted -> in_review (admin picks up)
 * - in_review -> approved (admin approves)
 * - in_review -> rejected (admin rejects with reason)
 * - in_review -> pending_info (admin requests more info)
 * - approved -> suspended (admin suspends)
 * - suspended -> approved (admin reactivates)
 * - suspended -> revoked (admin permanently removes)
 */
class RcsAgentApprovalController extends Controller
{
    /**
     * List all RCS Agent requests (with filtering)
     * GET /admin/api/rcs-agents
     */
    public function index(Request $request): JsonResponse
    {
        $query = RcsAgent::withoutGlobalScope('tenant')
            ->with(['account:id,company_name,account_number', 'createdBy:id,email,first_name,last_name']);

        if ($status = $request->input('status')) {
            $query->where('workflow_status', $status);
        }

        if ($accountId = $request->input('account_id')) {
            $query->where('account_id', $accountId);
        }

        if ($billingCategory = $request->input('billing_category')) {
            $query->where('billing_category', $billingCategory);
        }

        if ($useCase = $request->input('use_case')) {
            $query->where('use_case', $useCase);
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
     * Get single RCS Agent detail (admin view with RED side data)
     * GET /admin/api/rcs-agents/{uuid}
     */
    public function show(string $uuid): JsonResponse
    {
        $agent = RcsAgent::withoutGlobalScope('tenant')
            ->where('uuid', $uuid)
            ->with([
                'account:id,company_name,account_number,status,created_at',
                'createdBy:id,email,first_name,last_name',
                'reviewedBy:id,email,first_name,last_name',
                'statusHistory',
                'assignments',
            ])
            ->first();

        if (!$agent) {
            return response()->json(['success' => false, 'error' => 'RCS Agent not found.'], 404);
        }

        $accountData = $agent->account?->only(['id', 'company_name', 'account_number', 'status']);
        if ($accountData && $agent->account) {
            $accountData['created_at'] = $agent->account->created_at;
            $tenantId = $agent->account->id;
            $accountData['approved_rcs_agents'] = RcsAgent::withoutGlobalScope('tenant')
                ->where('account_id', $tenantId)
                ->where('workflow_status', RcsAgent::STATUS_APPROVED)
                ->count();
            $accountData['rejected_rcs_agents'] = RcsAgent::withoutGlobalScope('tenant')
                ->where('account_id', $tenantId)
                ->where('workflow_status', RcsAgent::STATUS_REJECTED)
                ->count();
        }

        $comments = $agent->comments()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($c) => $c->toAdminArray());

        return response()->json([
            'success' => true,
            'data' => $agent->toAdminArray(),
            'status_history' => $agent->statusHistory,
            'comments' => $comments,
            'account' => $accountData,
        ]);
    }

    /**
     * Start review (submitted -> in_review)
     * POST /admin/api/rcs-agents/{uuid}/review
     */
    public function startReview(Request $request, string $uuid): JsonResponse
    {
        return $this->performTransition($request, $uuid, RcsAgent::STATUS_IN_REVIEW);
    }

    /**
     * Approve an RCS Agent (in_review -> approved)
     * POST /admin/api/rcs-agents/{uuid}/approve
     */
    public function approve(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        return $this->performTransition($request, $uuid, RcsAgent::STATUS_APPROVED, null, $request->input('notes'));
    }

    /**
     * Reject an RCS Agent (in_review -> rejected)
     * POST /admin/api/rcs-agents/{uuid}/reject
     */
    public function reject(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ]);

        return $this->performTransition($request, $uuid, RcsAgent::STATUS_REJECTED, $validated['reason'], $validated['notes'] ?? null);
    }

    /**
     * Request more information (in_review -> pending_info)
     * POST /admin/api/rcs-agents/{uuid}/request-info
     */
    public function requestInfo(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:2000',
        ]);

        $agent = RcsAgent::withoutGlobalScope('tenant')
            ->where('uuid', $uuid)
            ->first();

        if (!$agent) {
            return response()->json(['success' => false, 'error' => 'RCS Agent not found.'], 404);
        }

        $response = $this->performTransition($request, $uuid, RcsAgent::STATUS_PENDING_INFO, null, $validated['notes']);

        $responseData = $response->getData(true);
        if ($responseData['success'] ?? false) {
            try {
                // Customer notification
                Notification::create([
                    'tenant_id' => $agent->account_id,
                    'type' => 'RCS_AGENT_RETURNED',
                    'severity' => 'warning',
                    'title' => "RCS Agent needs more information",
                    'body' => "Your RCS Agent '{$agent->name}' was returned with comments. Please review and resubmit.",
                    'deep_link' => "/management/rcs-agent?view={$agent->uuid}",
                    'meta' => [
                        'agent_name' => $agent->name,
                        'request_uuid' => $agent->uuid,
                        'request_id' => $agent->id,
                    ],
                ]);

                // Create customer-visible comment with admin's notes
                $adminUser = $request->user();
                RcsAgentComment::create([
                    'rcs_agent_id' => $agent->id,
                    'comment_type' => RcsAgentComment::TYPE_CUSTOMER,
                    'comment_text' => $validated['notes'],
                    'created_by_actor_type' => RcsAgentComment::ACTOR_ADMIN,
                    'created_by_actor_id' => $adminUser->id ?? null,
                    'created_by_actor_name' => $adminUser ? (($adminUser->first_name ?? '') . ' ' . ($adminUser->last_name ?? '')) : 'Admin',
                ]);

                $this->logGovernanceEvent(
                    $agent,
                    ['workflow_status' => 'in_review'],
                    'RCS_AGENT_RETURNED_TO_CUSTOMER',
                    $request,
                    $validated['notes']
                );
            } catch (\Exception $e) {
                Log::error('[RcsAgentApproval] Failed to create notification/comment: ' . $e->getMessage());
            }
        }

        return $response;
    }

    /**
     * Suspend an approved RCS Agent (approved -> suspended)
     * POST /admin/api/rcs-agents/{uuid}/suspend
     */
    public function suspend(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        return $this->performTransition($request, $uuid, RcsAgent::STATUS_SUSPENDED, $validated['reason']);
    }

    /**
     * Reactivate a suspended RCS Agent (suspended -> approved)
     * POST /admin/api/rcs-agents/{uuid}/reactivate
     */
    public function reactivate(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:2000',
        ]);

        return $this->performTransition($request, $uuid, RcsAgent::STATUS_APPROVED, null, $request->input('notes'));
    }

    /**
     * Permanently revoke an RCS Agent (suspended -> revoked)
     * POST /admin/api/rcs-agents/{uuid}/revoke
     */
    public function revoke(Request $request, string $uuid): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        return $this->performTransition($request, $uuid, RcsAgent::STATUS_REVOKED, $validated['reason']);
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
        $agent = RcsAgent::withoutGlobalScope('tenant')
            ->where('uuid', $uuid)
            ->first();

        if (!$agent) {
            return response()->json(['success' => false, 'error' => 'RCS Agent not found.'], 404);
        }

        $beforeState = $agent->toAdminArray();

        try {
            $adminUser = $request->user();
            $adminId = $adminUser->id ?? null;

            $agent->transitionTo(
                $targetStatus,
                $adminId,
                $reason,
                $notes,
                $adminUser
            );

            // Also update admin_notes if provided
            if ($notes && $targetStatus !== RcsAgent::STATUS_PENDING_INFO) {
                $existingNotes = $agent->admin_notes ?? '';
                $agent->update([
                    'admin_notes' => trim($existingNotes . "\n[" . now()->toIso8601String() . "] " . $notes),
                ]);
            }

            // Log governance audit event
            $this->logGovernanceEvent($agent, $beforeState, $targetStatus, $request);

            return response()->json([
                'success' => true,
                'data' => $agent->fresh()->toAdminArray(),
                'message' => "RCS Agent status changed to '{$targetStatus}'.",
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('[RcsAgentApproval] Transition failed', [
                'uuid' => $uuid,
                'target' => $targetStatus,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to update RCS Agent status.',
            ], 500);
        }
    }

    /**
     * Log approval event to governance_audit_events table
     */
    private function logGovernanceEvent(
        RcsAgent $agent,
        array $beforeState,
        string $newStatusOrEventType,
        Request $request,
        ?string $reason = null
    ): void {
        try {
            $customEventTypes = [
                'RCS_AGENT_RETURNED_TO_CUSTOMER',
                'RCS_AGENT_ADMIN_REVIEW_REOPENED',
                'RCS_AGENT_COMMENT_ADDED',
            ];

            $isCustomEvent = in_array($newStatusOrEventType, $customEventTypes);
            $eventType = $isCustomEvent ? $newStatusOrEventType : 'RCS_AGENT_STATUS_CHANGED';
            $afterStatus = $isCustomEvent ? ($agent->workflow_status) : $newStatusOrEventType;

            DB::table('governance_audit_events')->insert([
                'event_uuid' => Str::uuid()->toString(),
                'event_type' => $eventType,
                'entity_type' => 'rcs_agent',
                'entity_id' => $agent->id,
                'account_id' => null,
                'sub_account_id' => null,
                'actor_id' => $request->user()->id ?? null,
                'actor_type' => 'ADMIN',
                'actor_email' => $request->user()->email ?? 'admin@quicksms.co.uk',
                'before_state' => json_encode([
                    'workflow_status' => $beforeState['workflow_status'] ?? null,
                    'agent_name' => $beforeState['name'] ?? $agent->name,
                    'uuid' => $beforeState['uuid'] ?? $agent->uuid,
                ]),
                'after_state' => json_encode([
                    'workflow_status' => $afterStatus,
                    'agent_name' => $agent->name,
                    'uuid' => $agent->uuid,
                ]),
                'reason' => $reason ?? $agent->rejection_reason ?? $agent->suspension_reason ?? $agent->revocation_reason ?? null,
                'source_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('[RcsAgentApproval] Failed to log governance event: ' . $e->getMessage());
        }
    }
}
