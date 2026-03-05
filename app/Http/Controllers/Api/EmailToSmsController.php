<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailToSmsAuditLog;
use App\Models\EmailToSmsSetup;
use App\Models\EmailToSmsReportingGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailToSmsController extends Controller
{
    private function tenantId(): string
    {
        return session('customer_tenant_id');
    }

    // =====================================================
    // SETUPS — LIST / SHOW / CREATE / UPDATE
    // =====================================================

    public function index(Request $request): JsonResponse
    {
        $query = EmailToSmsSetup::forAccount($this->tenantId())
            ->with(['subAccount', 'reportingGroup']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $statuses = is_array($request->input('status'))
                ? $request->input('status')
                : [$request->input('status')];
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('sub_account_id')) {
            $query->where('sub_account_id', $request->input('sub_account_id'));
        }

        if (!$request->boolean('include_archived')) {
            $query->where('status', '!=', 'archived');
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('dir', 'desc');
        $allowed = ['name', 'type', 'status', 'created_at', 'updated_at'];
        if (in_array($sortField, $allowed)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $setups = $query->get();

        return response()->json([
            'success' => true,
            'data' => $setups->map(fn ($s) => $this->transformSetup($s)),
            'total' => $setups->count(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::forAccount($this->tenantId())
            ->with(['subAccount', 'reportingGroup'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->transformSetup($setup),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sub_account_id' => 'nullable|string',
            'type' => 'required|in:standard,contact_list',
            'allowed_sender_emails' => 'nullable|array',
            'allowed_sender_emails.*' => 'string|max:255',
            'sender_id_template_id' => 'nullable|string',
            'sender_id' => 'nullable|string|max:11',
            'multiple_sms_enabled' => 'nullable|boolean',
            'delivery_reports_enabled' => 'nullable|boolean',
            'delivery_reports_email' => 'nullable|email|max:255',
            'reporting_group_id' => 'nullable|string',
            'contact_book_list_ids' => 'nullable|array',
            'opt_out_mode' => 'nullable|in:NONE,SELECTED',
            'opt_out_list_ids' => 'nullable|array',
        ]);

        $tenantId = $this->tenantId();

        // Check for duplicate name within tenant
        $nameExists = EmailToSmsSetup::forAccount($tenantId)
            ->where('name', $request->input('name'))
            ->exists();
        if ($nameExists) {
            return response()->json(['success' => false, 'error' => 'A setup with this name already exists'], 422);
        }

        // Validate sub_account belongs to this tenant
        if ($request->filled('sub_account_id')) {
            $exists = DB::table('sub_accounts')
                ->where('id', $request->input('sub_account_id'))
                ->where('account_id', $tenantId)
                ->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'error' => 'Invalid sub-account'], 422);
            }
        }

        // Validate reporting group belongs to this tenant
        if ($request->filled('reporting_group_id')) {
            $exists = EmailToSmsReportingGroup::forAccount($tenantId)
                ->where('id', $request->input('reporting_group_id'))
                ->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'error' => 'Invalid reporting group'], 422);
            }
        }

        // Generate unique email address with collision protection
        $emailDomain = '@sms.quicksms.io';
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '.', $request->input('name')));
        $generatedEmail = null;
        $maxRetries = 5;

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            $hash = substr(md5(uniqid((string) mt_rand(), true)), 0, 7);
            $candidate = $slug . '.' . $hash . $emailDomain;
            $emailExists = EmailToSmsSetup::where('generated_email_address', $candidate)->exists();
            if (!$emailExists) {
                $generatedEmail = $candidate;
                break;
            }
        }

        if (!$generatedEmail) {
            return response()->json(['success' => false, 'error' => 'Unable to generate unique email address. Please try again.'], 500);
        }

        $setup = DB::transaction(function () use ($request, $tenantId, $generatedEmail) {
            $setup = EmailToSmsSetup::create([
                'account_id' => $tenantId,
                'sub_account_id' => $request->input('sub_account_id'),
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'type' => $request->input('type'),
                'generated_email_address' => $generatedEmail,
                'originating_emails' => [$generatedEmail],
                'allowed_sender_emails' => $request->input('allowed_sender_emails', []),
                'sender_id_template_id' => $request->input('sender_id_template_id'),
                'sender_id' => $request->input('sender_id'),
                'multiple_sms_enabled' => $request->boolean('multiple_sms_enabled'),
                'delivery_reports_enabled' => $request->boolean('delivery_reports_enabled'),
                'delivery_reports_email' => $request->input('delivery_reports_email'),
                'status' => 'active',
                'reporting_group_id' => $request->input('reporting_group_id'),
                'contact_book_list_ids' => $request->input('contact_book_list_ids', []),
                'opt_out_mode' => $request->input('opt_out_mode', 'NONE'),
                'opt_out_list_ids' => $request->input('opt_out_list_ids', []),
            ]);

            EmailToSmsAuditLog::logAction(
                $tenantId,
                'created',
                'setup',
                $setup->id,
                null,
                ['name' => $setup->name, 'type' => $setup->type]
            );

            return $setup;
        });

        $setup->load(['subAccount', 'reportingGroup']);

        return response()->json([
            'success' => true,
            'data' => $this->transformSetup($setup),
            'message' => 'Setup created successfully',
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::forAccount($this->tenantId())->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sub_account_id' => 'nullable|string',
            'allowed_sender_emails' => 'nullable|array',
            'allowed_sender_emails.*' => 'string|max:255',
            'sender_id_template_id' => 'nullable|string',
            'sender_id' => 'nullable|string|max:11',
            'multiple_sms_enabled' => 'nullable|boolean',
            'delivery_reports_enabled' => 'nullable|boolean',
            'delivery_reports_email' => 'nullable|email|max:255',
            'reporting_group_id' => 'nullable|string',
            'contact_book_list_ids' => 'nullable|array',
            'opt_out_mode' => 'nullable|in:NONE,SELECTED',
            'opt_out_list_ids' => 'nullable|array',
        ]);

        $tenantId = $this->tenantId();

        // Check for duplicate name within tenant (if name is being changed)
        if ($request->filled('name') && $request->input('name') !== $setup->name) {
            $nameExists = EmailToSmsSetup::forAccount($tenantId)
                ->where('name', $request->input('name'))
                ->where('id', '!=', $setup->id)
                ->exists();
            if ($nameExists) {
                return response()->json(['success' => false, 'error' => 'A setup with this name already exists'], 422);
            }
        }

        if ($request->filled('sub_account_id')) {
            $exists = DB::table('sub_accounts')
                ->where('id', $request->input('sub_account_id'))
                ->where('account_id', $tenantId)
                ->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'error' => 'Invalid sub-account'], 422);
            }
        }

        if ($request->filled('reporting_group_id')) {
            $exists = EmailToSmsReportingGroup::forAccount($tenantId)
                ->where('id', $request->input('reporting_group_id'))
                ->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'error' => 'Invalid reporting group'], 422);
            }
        }

        $updateFields = $request->only([
            'name', 'description', 'sub_account_id', 'allowed_sender_emails',
            'sender_id_template_id', 'sender_id',
            'multiple_sms_enabled', 'delivery_reports_enabled', 'delivery_reports_email',
            'reporting_group_id', 'contact_book_list_ids', 'opt_out_mode', 'opt_out_list_ids',
        ]);

        DB::transaction(function () use ($setup, $updateFields, $tenantId) {
            $original = $setup->only(array_keys($updateFields));
            $setup->update($updateFields);

            EmailToSmsAuditLog::logAction(
                $tenantId,
                'updated',
                'setup',
                $setup->id,
                null,
                ['before' => $original, 'after' => $updateFields]
            );
        });

        $setup->load(['subAccount', 'reportingGroup']);

        return response()->json([
            'success' => true,
            'data' => $this->transformSetup($setup),
            'message' => 'Setup updated successfully',
        ]);
    }

    // =====================================================
    // STATE TRANSITIONS
    // =====================================================

    public function suspend(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::forAccount($this->tenantId())->findOrFail($id);

        if ($setup->status === 'suspended') {
            return response()->json(['success' => false, 'error' => 'Already suspended'], 422);
        }

        $previousStatus = $setup->status;
        $setup->update(['status' => 'suspended']);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'suspended', 'setup', $setup->id, null,
            ['previous_status' => $previousStatus]
        );

        return response()->json([
            'success' => true,
            'data' => $this->transformSetup($setup),
            'message' => 'Setup suspended successfully',
        ]);
    }

    public function reactivate(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::forAccount($this->tenantId())->findOrFail($id);

        if ($setup->status === 'active') {
            return response()->json(['success' => false, 'error' => 'Already active'], 422);
        }

        $previousStatus = $setup->status;
        $setup->update(['status' => 'active']);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'reactivated', 'setup', $setup->id, null,
            ['previous_status' => $previousStatus]
        );

        return response()->json([
            'success' => true,
            'data' => $this->transformSetup($setup),
            'message' => 'Setup reactivated successfully',
        ]);
    }

    public function archive(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::forAccount($this->tenantId())->findOrFail($id);
        $previousStatus = $setup->status;
        $setup->update(['status' => 'archived']);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'archived', 'setup', $setup->id, null,
            ['previous_status' => $previousStatus]
        );

        return response()->json([
            'success' => true,
            'data' => $this->transformSetup($setup),
            'message' => 'Setup archived successfully',
        ]);
    }

    public function unarchive(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::forAccount($this->tenantId())->findOrFail($id);
        $setup->update(['status' => 'active']);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'unarchived', 'setup', $setup->id
        );

        return response()->json([
            'success' => true,
            'data' => $this->transformSetup($setup),
            'message' => 'Setup unarchived successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::forAccount($this->tenantId())->findOrFail($id);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'deleted', 'setup', $setup->id, null,
            ['name' => $setup->name]
        );

        $setup->delete();

        return response()->json([
            'success' => true,
            'message' => 'Setup deleted successfully',
        ]);
    }

    // =====================================================
    // OVERVIEW — Unified listing for both standard + contact_list
    // =====================================================

    public function overview(Request $request): JsonResponse
    {
        $query = EmailToSmsSetup::forAccount($this->tenantId())
            ->with(['subAccount', 'reportingGroup']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $statuses = is_array($request->input('status'))
                ? $request->input('status')
                : [$request->input('status')];
            $mapped = array_map('strtolower', $statuses);
            $query->whereIn('status', $mapped);
        }

        if (!$request->boolean('include_archived')) {
            $query->where('status', '!=', 'archived');
        }

        $setups = $query->orderBy('created_at', 'desc')->get();

        // Fetch message counts from message_logs if available
        $setupIds = $setups->pluck('id');
        $messageCounts = collect();
        try {
            $messageCounts = DB::table('message_logs')
                ->whereIn('email_to_sms_setup_id', $setupIds)
                ->groupBy('email_to_sms_setup_id')
                ->select('email_to_sms_setup_id', DB::raw('COUNT(*) as total'))
                ->get()
                ->keyBy('email_to_sms_setup_id');
        } catch (\Exception $e) {
            Log::debug('message_logs query failed for email-to-sms overview', ['error' => $e->getMessage()]);
        }

        // Get daily limit from account flags
        $dailyLimit = null;
        try {
            $flags = DB::table('account_flags')
                ->where('account_id', $this->tenantId())
                ->select('daily_message_limit')
                ->first();
            $dailyLimit = $flags->daily_message_limit ?? null;
        } catch (\Exception $e) {
            // account_flags table may not exist
        }

        $data = $setups->map(function ($setup) use ($messageCounts, $dailyLimit) {
            $type = $setup->type === 'contact_list' ? 'Contact List' : 'Standard';
            $msgCount = $messageCounts->get($setup->id);

            return [
                'id' => $setup->id,
                'name' => $setup->name,
                'description' => $setup->description,
                'type' => $type,
                'originatingEmails' => $setup->originating_emails ?? [],
                'allowedSenders' => $setup->allowed_sender_emails ?? [],
                'senderId' => $setup->sender_id,
                'subAccount' => $setup->subAccount?->name ?? 'Main Account',
                'reportingGroup' => $setup->reportingGroup?->name,
                'status' => ucfirst($setup->status),
                'created' => $setup->created_at?->format('Y-m-d'),
                'lastUsed' => $setup->updated_at?->format('Y-m-d H:i'),
                'messagesSent' => $msgCount->total ?? 0,
                'dailyLimit' => $dailyLimit,
                'optOut' => $setup->opt_out_mode !== 'NONE',
                'optOutMode' => $setup->opt_out_mode,
                'sourceType' => $setup->type,
                'sourceId' => $setup->id,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    // =====================================================
    // HELPERS — SenderID templates + subaccounts
    // =====================================================

    public function senderIdTemplates(): JsonResponse
    {
        $tenantId = $this->tenantId();

        $senderIds = DB::table('sender_ids')
            ->where('account_id', $tenantId)
            ->where('workflow_status', 'approved')
            ->whereNull('deleted_at')
            ->select('id', 'sender_id_value as senderId', 'brand_name as name', 'workflow_status as status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $senderIds,
        ]);
    }

    public function subaccounts(): JsonResponse
    {
        $tenantId = $this->tenantId();

        $subaccounts = DB::table('sub_accounts')
            ->where('account_id', $tenantId)
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subaccounts,
        ]);
    }

    public function accountFlags(): JsonResponse
    {
        $tenantId = $this->tenantId();

        // Read actual account configuration from account_settings
        $settings = DB::table('account_settings')
            ->where('account_id', $tenantId)
            ->first();

        // Check if the account has approved sender IDs (indicates dynamic_senderid access)
        $hasApprovedSenderIds = DB::table('sender_ids')
            ->where('account_id', $tenantId)
            ->where('workflow_status', 'approved')
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'dynamic_senderid_allowed' => $hasApprovedSenderIds,
                'wildcard_email_allowed' => false,
                'max_contact_lists_per_setup' => 10,
                'max_allowed_sender_emails' => 20,
                'delivery_reports_enabled' => (bool) ($settings->notify_failed_messages ?? true),
            ],
        ]);
    }

    // =====================================================
    // PRIVATE
    // =====================================================

    private function transformSetup(EmailToSmsSetup $setup): array
    {
        return [
            'id' => $setup->id,
            'name' => $setup->name,
            'description' => $setup->description,
            'subaccountId' => $setup->sub_account_id,
            'subaccountName' => $setup->subAccount?->name ?? 'Main Account',
            'generatedEmailAddress' => $setup->generated_email_address,
            'originatingEmails' => $setup->originating_emails ?? [],
            'allowedEmails' => $setup->allowed_sender_emails ?? [],
            'senderIdTemplateId' => $setup->sender_id_template_id,
            'senderId' => $setup->sender_id,
            'multipleSmsEnabled' => $setup->multiple_sms_enabled,
            'deliveryReportsEnabled' => $setup->delivery_reports_enabled,
            'deliveryReportsEmail' => $setup->delivery_reports_email,
            'status' => $setup->status,
            'type' => $setup->type,
            'reportingGroupId' => $setup->reporting_group_id,
            'reportingGroupName' => $setup->reportingGroup?->name,
            'contactBookListIds' => $setup->contact_book_list_ids ?? [],
            'optOutMode' => $setup->opt_out_mode,
            'optOutListIds' => $setup->opt_out_list_ids ?? [],
            'createdAt' => $setup->created_at?->toIso8601String(),
            'updatedAt' => $setup->updated_at?->toIso8601String(),
            'created' => $setup->created_at?->format('Y-m-d'),
            'lastUpdated' => $setup->updated_at?->format('Y-m-d'),
        ];
    }
}
