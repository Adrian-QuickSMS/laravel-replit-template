<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\GovernanceEnforcementService;
use App\Traits\ChecksAdminLocks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApprovalQueueController extends Controller
{
    use ChecksAdminLocks;

    protected GovernanceEnforcementService $governanceService;

    public function __construct(GovernanceEnforcementService $governanceService)
    {
        $this->governanceService = $governanceService;
    }

    public function getSenderIdRequests(Request $request)
    {
        $query = DB::table('senderid_requests')
            ->whereNull('deleted_at');

        if ($status = $request->input('status')) {
            $query->where('workflow_status', $status);
        }

        if ($accountId = $request->input('account_id')) {
            $query->where('account_id', $accountId);
        }

        $requests = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    public function getRcsAgentRequests(Request $request)
    {
        $query = DB::table('rcs_agent_requests')
            ->whereNull('deleted_at');

        if ($status = $request->input('status')) {
            $query->where('workflow_status', $status);
        }

        if ($accountId = $request->input('account_id')) {
            $query->where('account_id', $accountId);
        }

        $requests = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    public function getCountryRequests(Request $request)
    {
        $query = DB::table('country_requests')
            ->whereNull('deleted_at');

        if ($status = $request->input('status')) {
            $query->where('workflow_status', $status);
        }

        if ($accountId = $request->input('account_id')) {
            $query->where('account_id', $accountId);
        }

        $requests = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    public function getQueueCounts()
    {
        $counts = $this->governanceService->getApprovalQueueCounts();

        return response()->json([
            'success' => true,
            'data' => $counts,
        ]);
    }

    public function updateRequestStatus(Request $request, string $type, int $id)
    {
        $validated = $request->validate([
            'workflow_status' => 'required|in:SUBMITTED,IN_REVIEW,RETURNED,RESUBMITTED,VALIDATION_IN_PROGRESS,APPROVED,REJECTED,PROVISIONING,LIVE,SUSPENDED,ARCHIVED',
            'review_notes' => 'nullable|string|max:2000',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $tableMap = [
            'senderid' => 'senderid_requests',
            'rcs_agent' => 'rcs_agent_requests',
            'country' => 'country_requests',
        ];

        $tableName = $tableMap[$type] ?? null;

        if (!$tableName) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid request type.',
            ], 400);
        }

        $existingRequest = DB::table($tableName)->where('id', $id)->first();

        if (!$existingRequest) {
            return response()->json([
                'success' => false,
                'error' => 'Request not found.',
            ], 404);
        }

        $beforeState = (array) $existingRequest;

        $updateData = [
            'workflow_status' => $validated['workflow_status'],
            'reviewed_by' => $request->user()->id ?? 1,
            'reviewed_at' => now(),
            'updated_at' => now(),
        ];

        if (!empty($validated['review_notes'])) {
            $updateData['review_notes'] = $validated['review_notes'];
        }

        if ($validated['workflow_status'] === 'REJECTED' && !empty($validated['rejection_reason'])) {
            $updateData['rejection_reason'] = $validated['rejection_reason'];
        }

        DB::table($tableName)->where('id', $id)->update($updateData);

        $this->logApprovalEvent(
            $type,
            $id,
            $beforeState,
            array_merge($beforeState, $updateData),
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Request status updated successfully.',
        ]);
    }

    public function applyEntityLock(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|in:template,sender_id,campaign,api_connection,number,rcs_agent,email_to_sms_config',
            'entity_id' => 'required|integer',
            'reason' => 'required|string|max:1000',
        ]);

        $result = $this->applyAdminLock(
            $validated['entity_type'],
            $validated['entity_id'],
            $validated['reason'],
            $request->user()->id ?? 1,
            $request
        );

        return response()->json($result);
    }

    public function removeEntityLock(Request $request)
    {
        $validated = $request->validate([
            'entity_type' => 'required|in:template,sender_id,campaign,api_connection,number,rcs_agent,email_to_sms_config',
            'entity_id' => 'required|integer',
            'reason' => 'nullable|string|max:1000',
        ]);

        $result = $this->removeAdminLock(
            $validated['entity_type'],
            $validated['entity_id'],
            $request->user()->id ?? 1,
            $validated['reason'] ?? null,
            $request
        );

        return response()->json($result);
    }

    public function getLockedEntities(Request $request)
    {
        $accountId = $request->input('account_id');
        $entityType = $request->input('entity_type');

        if (!$accountId) {
            return response()->json([
                'success' => false,
                'error' => 'Account ID is required.',
            ], 400);
        }

        $lockedEntities = $this->getLockedEntitiesForAccount((int) $accountId, $entityType);

        return response()->json([
            'success' => true,
            'data' => $lockedEntities,
        ]);
    }

    private function logApprovalEvent(
        string $type,
        int $requestId,
        array $beforeState,
        array $afterState,
        Request $request
    ): void {
        try {
            DB::table('governance_audit_events')->insert([
                'event_uuid' => Str::uuid()->toString(),
                'event_type' => 'APPROVAL_STATUS_CHANGED',
                'entity_type' => $type . '_request',
                'entity_id' => $requestId,
                'account_id' => $beforeState['account_id'] ?? null,
                'sub_account_id' => $beforeState['sub_account_id'] ?? null,
                'actor_id' => $request->user()->id ?? 1,
                'actor_type' => 'ADMIN',
                'actor_email' => $request->user()->email ?? 'admin@quicksms.co.uk',
                'before_state' => json_encode($this->sanitizeForAudit($beforeState)),
                'after_state' => json_encode($this->sanitizeForAudit($afterState)),
                'reason' => $afterState['review_notes'] ?? $afterState['rejection_reason'] ?? null,
                'source_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            \Log::error("[ApprovalQueueController] Failed to log approval event: " . $e->getMessage());
        }
    }

    private function sanitizeForAudit(array $data): array
    {
        $sensitiveKeys = [
            'password', 'token', 'secret', 'api_key', 'private_key',
            'email', 'phone', 'phone_number', 'recipient', 'recipient_full',
            'supporting_documents', 'full_message', 'message_content',
            'credit_card', 'bank_account', 'ssn', 'national_id'
        ];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                if (in_array($key, ['email', 'phone', 'phone_number', 'recipient', 'recipient_full'])) {
                    $data[$key] = '[PII_REDACTED]';
                } elseif (in_array($key, ['supporting_documents', 'full_message', 'message_content'])) {
                    $data[$key] = '[CONTENT_REDACTED]';
                } else {
                    $data[$key] = '[REDACTED]';
                }
            }
        }

        return $data;
    }
}
