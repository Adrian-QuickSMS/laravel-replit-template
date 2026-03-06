<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailToSmsAddress;
use App\Models\EmailToSmsAllowedSender;
use App\Models\EmailToSmsAuditLog;
use App\Models\EmailToSmsOptOutConfig;
use App\Models\EmailToSmsRecipient;
use App\Models\EmailToSmsSetup;
use App\Models\EmailToSmsReportingGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmailToSmsController extends Controller
{
    private function tenantId(): string
    {
        return session('customer_tenant_id');
    }

    public function index(Request $request): JsonResponse
    {
        $query = EmailToSmsSetup::with(['subAccount', 'reportingGroup', 'addresses', 'allowedSenders', 'recipients', 'optOutConfig']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        $type = $request->input('type') ?? $request->route('type');
        if ($type) {
            $query->where('type', $type);
        }

        if ($request->filled('status')) {
            $statuses = is_array($request->input('status'))
                ? $request->input('status')
                : [$request->input('status')];
            $query->whereIn('status', array_map('strtolower', $statuses));
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
            'data' => $setups->map(fn ($s) => $s->toPortalArray()),
            'total' => $setups->count(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::with(['subAccount', 'reportingGroup', 'addresses', 'allowedSenders', 'recipients', 'optOutConfig'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $setup->toPortalArray(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $type = $request->input('type') ?? $request->route('type') ?? 'standard';

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];

        if ($type === 'contact_list') {
            $rules['allowedSenderEmails'] = 'nullable|array|max:20';
            $rules['allowedSenderEmails.*'] = 'string|max:255';
        } else {
            $rules['allowedEmails'] = 'nullable|array|max:20';
            $rules['allowedEmails.*'] = 'string|max:255';
        }

        $request->validate($rules);

        $tenantId = $this->tenantId();

        $nameExists = EmailToSmsSetup::where('name', $request->input('name'))->exists();
        if ($nameExists) {
            return response()->json(['success' => false, 'error' => 'A setup with this name already exists'], 422);
        }

        $subAccountId = $request->input('subaccountId') ?? $request->input('sub_account_id');
        if ($subAccountId && $subAccountId === $tenantId) {
            $subAccountId = null;
        }
        if ($subAccountId) {
            $exists = DB::table('sub_accounts')
                ->where('id', $subAccountId)
                ->where('account_id', $tenantId)
                ->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'error' => 'Invalid sub-account'], 422);
            }
        }

        $reportingGroupId = $request->input('reportingGroupId') ?? $request->input('reporting_group_id');
        if ($reportingGroupId) {
            $exists = EmailToSmsReportingGroup::where('id', $reportingGroupId)->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'error' => 'Invalid reporting group'], 422);
            }
        }

        $senderIdTemplateId = $request->input('senderIdTemplateId') ?? $request->input('sender_id_template_id');
        $senderIdLabel = null;
        if ($senderIdTemplateId) {
            $senderRecord = DB::table('sender_ids')
                ->where('id', $senderIdTemplateId)
                ->where('account_id', $tenantId)
                ->where('workflow_status', 'approved')
                ->whereNull('deleted_at')
                ->first();
            if ($senderRecord) {
                $senderIdLabel = $senderRecord->sender_id_value ?? null;
            }
        }
        if (!$senderIdLabel) {
            $senderIdLabel = $request->input('senderId') ?? $request->input('sender_id');
        }

        $generatedEmail = $this->generateEmailAddress($request->input('name'));
        if (!$generatedEmail) {
            return response()->json(['success' => false, 'error' => 'Unable to generate unique email address. Please try again.'], 500);
        }

        $rcsAgentId = $request->input('rcsAgentId') ?? $request->input('rcs_agent_id');

        $setup = DB::transaction(function () use ($request, $tenantId, $type, $subAccountId, $reportingGroupId, $senderIdTemplateId, $senderIdLabel, $rcsAgentId, $generatedEmail) {
            $setup = EmailToSmsSetup::withoutGlobalScopes()->create([
                'account_id' => $tenantId,
                'sub_account_id' => $subAccountId,
                'type' => $type,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'status' => 'active',
                'reporting_group_id' => $reportingGroupId,
                'sender_id_template_id' => $senderIdTemplateId,
                'sender_id_label' => $senderIdLabel,
                'rcs_agent_id' => $rcsAgentId,
                'multiple_sms_enabled' => $request->boolean('multipleSmsEnabled', true),
                'delivery_reports_enabled' => $request->boolean('deliveryReportsEnabled', false),
                'delivery_report_email' => $request->input('deliveryReportsEmail') ?? $request->input('delivery_report_email'),
                'daily_limit' => $request->input('dailyLimit', 5000),
            ]);

            EmailToSmsAddress::withoutGlobalScopes()->create([
                'setup_id' => $setup->id,
                'account_id' => $tenantId,
                'email_address' => $generatedEmail,
                'is_primary' => true,
                'status' => 'active',
            ]);

            $allowedEmails = $type === 'contact_list'
                ? ($request->input('allowedSenderEmails') ?? [])
                : ($request->input('allowedEmails') ?? []);
            foreach ($allowedEmails as $email) {
                EmailToSmsAllowedSender::withoutGlobalScopes()->create([
                    'setup_id' => $setup->id,
                    'account_id' => $tenantId,
                    'email_pattern' => $email,
                ]);
            }

            if ($type === 'contact_list') {
                $this->syncRecipients($setup, $request, $tenantId);
                $this->syncOptOutConfig($setup, $request, $tenantId);
            }

            EmailToSmsAuditLog::logAction(
                $tenantId,
                'created',
                $setup->id,
                null,
                "Created {$type} setup: {$setup->name}",
                ['name' => $setup->name, 'type' => $type]
            );

            return $setup;
        });

        $setup->load(['subAccount', 'reportingGroup', 'addresses', 'allowedSenders', 'recipients', 'optOutConfig']);

        return response()->json([
            'success' => true,
            'data' => $setup->toPortalArray(),
            'message' => 'Setup created successfully',
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::findOrFail($id);

        $type = $setup->type;

        $rules = [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ];

        if ($type === 'contact_list') {
            $rules['allowedSenderEmails'] = 'nullable|array|max:20';
            $rules['allowedSenderEmails.*'] = 'string|max:255';
        } else {
            $rules['allowedEmails'] = 'nullable|array|max:20';
            $rules['allowedEmails.*'] = 'string|max:255';
        }

        $request->validate($rules);

        $tenantId = $this->tenantId();

        $newName = $request->input('name');
        if ($newName && $newName !== $setup->name) {
            $nameExists = EmailToSmsSetup::where('name', $newName)
                ->where('id', '!=', $setup->id)
                ->exists();
            if ($nameExists) {
                return response()->json(['success' => false, 'error' => 'A setup with this name already exists'], 422);
            }
        }

        $subAccountId = $request->input('subaccountId') ?? $request->input('sub_account_id');
        if ($subAccountId && $subAccountId === $tenantId) {
            $subAccountId = null;
        }
        if ($subAccountId) {
            $exists = DB::table('sub_accounts')
                ->where('id', $subAccountId)
                ->where('account_id', $tenantId)
                ->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'error' => 'Invalid sub-account'], 422);
            }
        }

        $reportingGroupId = $request->input('reportingGroupId') ?? $request->input('reporting_group_id');
        if ($reportingGroupId && $reportingGroupId !== $setup->reporting_group_id) {
            $exists = EmailToSmsReportingGroup::where('id', $reportingGroupId)->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'error' => 'Invalid reporting group'], 422);
            }
        }

        $senderIdTemplateId = $request->input('senderIdTemplateId') ?? $request->input('sender_id_template_id');
        $senderIdLabel = $setup->sender_id_label;
        if ($senderIdTemplateId && $senderIdTemplateId !== $setup->sender_id_template_id) {
            $senderRecord = DB::table('sender_ids')
                ->where('id', $senderIdTemplateId)
                ->where('account_id', $tenantId)
                ->where('workflow_status', 'approved')
                ->whereNull('deleted_at')
                ->first();
            if ($senderRecord) {
                $senderIdLabel = $senderRecord->sender_id_value ?? null;
            }
        }
        if (!$senderIdLabel) {
            $senderIdLabel = $request->input('senderId') ?? $request->input('sender_id') ?? $setup->sender_id_label;
        }

        $rcsAgentId = $request->input('rcsAgentId') ?? $request->input('rcs_agent_id');

        DB::transaction(function () use ($request, $setup, $tenantId, $type, $subAccountId, $reportingGroupId, $senderIdTemplateId, $senderIdLabel, $rcsAgentId) {
            $originalData = $setup->toPortalArray();

            $updateFields = [
                'sender_id_template_id' => $senderIdTemplateId ?? $setup->sender_id_template_id,
                'sender_id_label' => $senderIdLabel,
                'rcs_agent_id' => $request->has('rcsAgentId') || $request->has('rcs_agent_id') ? $rcsAgentId : $setup->rcs_agent_id,
                'multiple_sms_enabled' => $request->has('multipleSmsEnabled') ? $request->boolean('multipleSmsEnabled') : $setup->multiple_sms_enabled,
                'delivery_reports_enabled' => $request->has('deliveryReportsEnabled') ? $request->boolean('deliveryReportsEnabled') : $setup->delivery_reports_enabled,
                'delivery_report_email' => $request->input('deliveryReportsEmail') ?? $request->input('delivery_report_email') ?? $setup->delivery_report_email,
            ];

            if ($request->has('name')) {
                $updateFields['name'] = $request->input('name');
            }
            if ($request->has('description')) {
                $updateFields['description'] = $request->input('description');
            }
            if ($subAccountId !== null) {
                $updateFields['sub_account_id'] = $subAccountId;
            }
            if ($reportingGroupId !== null) {
                $updateFields['reporting_group_id'] = $reportingGroupId ?: null;
            }
            if ($request->has('dailyLimit')) {
                $updateFields['daily_limit'] = $request->input('dailyLimit');
            }

            $setup->update($updateFields);

            $allowedEmails = $type === 'contact_list'
                ? $request->input('allowedSenderEmails')
                : $request->input('allowedEmails');

            if ($allowedEmails !== null) {
                EmailToSmsAllowedSender::withoutGlobalScopes()
                    ->where('setup_id', $setup->id)
                    ->delete();
                foreach ($allowedEmails as $email) {
                    EmailToSmsAllowedSender::withoutGlobalScopes()->create([
                        'setup_id' => $setup->id,
                        'account_id' => $tenantId,
                        'email_pattern' => $email,
                    ]);
                }
            }

            if ($type === 'contact_list') {
                if ($request->has('contactBookListIds')) {
                    EmailToSmsRecipient::withoutGlobalScopes()
                        ->where('setup_id', $setup->id)
                        ->delete();
                    $this->syncRecipients($setup, $request, $tenantId);
                }

                if ($request->has('optOutListIds')) {
                    EmailToSmsOptOutConfig::withoutGlobalScopes()
                        ->where('setup_id', $setup->id)
                        ->delete();
                    $this->syncOptOutConfig($setup, $request, $tenantId);
                }
            }

            EmailToSmsAuditLog::logAction(
                $tenantId,
                'updated',
                $setup->id,
                null,
                "Updated setup: {$setup->name}",
                ['before' => $originalData, 'after' => $updateFields]
            );
        });

        $setup->load(['subAccount', 'reportingGroup', 'addresses', 'allowedSenders', 'recipients', 'optOutConfig']);

        return response()->json([
            'success' => true,
            'data' => $setup->toPortalArray(),
            'message' => 'Setup updated successfully',
        ]);
    }

    public function suspend(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::findOrFail($id);

        if ($setup->status === 'suspended') {
            return response()->json(['success' => false, 'error' => 'Already suspended'], 422);
        }

        $previousStatus = $setup->status;
        $setup->update(['status' => 'suspended']);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'suspended', $setup->id, null,
            "Suspended setup: {$setup->name}",
            ['previous_status' => $previousStatus]
        );

        $setup->load(['subAccount', 'reportingGroup', 'addresses', 'allowedSenders', 'recipients', 'optOutConfig']);

        return response()->json([
            'success' => true,
            'data' => $setup->toPortalArray(),
            'message' => 'Setup suspended successfully',
        ]);
    }

    public function reactivate(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::findOrFail($id);

        if ($setup->status === 'active') {
            return response()->json(['success' => false, 'error' => 'Already active'], 422);
        }

        $previousStatus = $setup->status;
        $setup->update(['status' => 'active']);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'reactivated', $setup->id, null,
            "Reactivated setup: {$setup->name}",
            ['previous_status' => $previousStatus]
        );

        $setup->load(['subAccount', 'reportingGroup', 'addresses', 'allowedSenders', 'recipients', 'optOutConfig']);

        return response()->json([
            'success' => true,
            'data' => $setup->toPortalArray(),
            'message' => 'Setup reactivated successfully',
        ]);
    }

    public function archive(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::findOrFail($id);
        $previousStatus = $setup->status;
        $setup->update(['status' => 'archived']);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'archived', $setup->id, null,
            "Archived setup: {$setup->name}",
            ['previous_status' => $previousStatus]
        );

        $setup->load(['subAccount', 'reportingGroup', 'addresses', 'allowedSenders', 'recipients', 'optOutConfig']);

        return response()->json([
            'success' => true,
            'data' => $setup->toPortalArray(),
            'message' => 'Setup archived successfully',
        ]);
    }

    public function unarchive(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::findOrFail($id);
        $setup->update(['status' => 'active']);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'unarchived', $setup->id, null,
            "Unarchived setup: {$setup->name}"
        );

        $setup->load(['subAccount', 'reportingGroup', 'addresses', 'allowedSenders', 'recipients', 'optOutConfig']);

        return response()->json([
            'success' => true,
            'data' => $setup->toPortalArray(),
            'message' => 'Setup unarchived successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::findOrFail($id);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'deleted', $setup->id, null,
            "Deleted setup: {$setup->name}",
            ['name' => $setup->name, 'type' => $setup->type]
        );

        $setup->delete();

        return response()->json([
            'success' => true,
            'message' => 'Setup deleted successfully',
        ]);
    }

    public function overview(Request $request): JsonResponse
    {
        $query = EmailToSmsSetup::with(['subAccount', 'reportingGroup', 'addresses', 'allowedSenders', 'optOutConfig']);

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

        if ($request->filled('type')) {
            $typeFilter = $request->input('type');
            if ($typeFilter === 'contactList') {
                $query->where('type', 'contact_list');
            } else {
                $query->where('type', $typeFilter);
            }
        }

        if (!$request->boolean('include_archived')) {
            $query->where('status', '!=', 'archived');
        }

        $setups = $query->orderBy('created_at', 'desc')->get();

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

        $data = $setups->map(function ($setup) use ($messageCounts) {
            $type = $setup->type === 'contact_list' ? 'Contact List' : 'Standard';
            $sourceType = $setup->type === 'contact_list' ? 'contactList' : 'standard';
            $msgCount = $messageCounts->get($setup->id);

            $originatingEmails = $setup->addresses->pluck('email_address')->values()->toArray();
            $allowedSenders = $setup->allowedSenders->pluck('email_pattern')->values()->toArray();

            $optOutLabel = null;
            if ($setup->type === 'contact_list') {
                $optOutConfigs = $setup->optOutConfig;
                if ($optOutConfigs->isNotEmpty()) {
                    $optOutLabel = $optOutConfigs->pluck('opt_out_list_name')->filter()->implode(', ');
                    if (!$optOutLabel) {
                        $optOutLabel = 'Specific Lists (' . $optOutConfigs->count() . ')';
                    }
                }
            }

            return [
                'id' => $setup->id,
                'sourceId' => $setup->id,
                'sourceType' => $sourceType,
                'name' => $setup->name,
                'description' => $setup->description,
                'type' => $type,
                'originatingEmails' => $originatingEmails,
                'senderId' => $setup->sender_id_label,
                'optOut' => $optOutLabel,
                'subAccount' => $setup->subAccount?->name ?? 'Main Account',
                'reportingGroup' => $setup->reportingGroup?->name,
                'allowedSenders' => $allowedSenders,
                'dailyLimit' => $setup->daily_limit ?? 5000,
                'status' => ucfirst($setup->status),
                'created' => $setup->created_at?->format('Y-m-d'),
                'lastUsed' => $setup->updated_at?->format('Y-m-d H:i'),
                'messagesSent' => $msgCount->total ?? 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

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

        $mainAccount = DB::table('accounts')
            ->where('id', $tenantId)
            ->select('id', 'company_name as name')
            ->first();

        $subaccounts = DB::table('sub_accounts')
            ->where('account_id', $tenantId)
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $accounts = collect();
        if ($mainAccount) {
            $accounts->push((object) ['id' => $mainAccount->id, 'name' => $mainAccount->name ?: 'Main Account', 'is_main' => true]);
        }
        foreach ($subaccounts as $sub) {
            $accounts->push((object) ['id' => $sub->id, 'name' => $sub->name, 'is_main' => false]);
        }

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    public function accountFlags(): JsonResponse
    {
        $tenantId = $this->tenantId();

        $settings = null;
        try {
            $settings = DB::table('account_settings')
                ->where('account_id', $tenantId)
                ->first();
        } catch (\Exception $e) {
            Log::debug('account_settings query failed', ['error' => $e->getMessage()]);
        }

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

    public function contactBookData(): JsonResponse
    {
        $tenantId = $this->tenantId();

        $lists = collect();
        $dynamicLists = collect();
        $contacts = collect();
        $tags = collect();
        $optOutLists = collect();

        try {
            $lists = DB::table('contact_lists')
                ->where('account_id', $tenantId)
                ->whereNull('deleted_at')
                ->select('id', 'name', DB::raw("'static' as type"), DB::raw("0 as recipientCount"), DB::raw("'active' as status"))
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            Log::debug('contact_lists query failed', ['error' => $e->getMessage()]);
        }

        try {
            $contacts = DB::table('contacts')
                ->where('account_id', $tenantId)
                ->whereNull('deleted_at')
                ->select('id', 'first_name', 'last_name', 'mobile_number as mobile', 'email', DB::raw("'active' as status"))
                ->limit(100)
                ->orderBy('first_name')
                ->get()
                ->map(function ($c) {
                    $c->name = trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? ''));
                    return $c;
                });
        } catch (\Exception $e) {
            Log::debug('contacts query failed', ['error' => $e->getMessage()]);
        }

        try {
            $tags = DB::table('tags')
                ->where('account_id', $tenantId)
                ->whereNull('deleted_at')
                ->select('id', 'name', DB::raw("0 as recipientCount"))
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            Log::debug('tags query failed', ['error' => $e->getMessage()]);
        }

        try {
            $optOutLists = DB::table('opt_out_lists')
                ->where('account_id', $tenantId)
                ->select('id', 'name', DB::raw("COALESCE(count, 0) as \"recipientCount\""))
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            Log::debug('opt_out_lists query failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'lists' => $lists->values(),
                'dynamicLists' => $dynamicLists->values(),
                'contacts' => $contacts->values(),
                'tags' => $tags->values(),
                'optOutLists' => $optOutLists->values(),
            ],
        ]);
    }

    public function contactBooks(): JsonResponse
    {
        return $this->contactBookData();
    }

    public function contacts(): JsonResponse
    {
        $tenantId = $this->tenantId();
        $contacts = collect();

        try {
            $contacts = DB::table('contacts')
                ->where('account_id', $tenantId)
                ->whereNull('deleted_at')
                ->select('id', 'first_name', 'last_name', 'mobile_number as mobile', 'email', DB::raw("'active' as status"))
                ->limit(200)
                ->orderBy('first_name')
                ->get()
                ->map(function ($c) {
                    $c->name = trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? ''));
                    return $c;
                });
        } catch (\Exception $e) {
            Log::debug('contacts query failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'data' => $contacts->values(),
        ]);
    }

    public function tags(): JsonResponse
    {
        $tenantId = $this->tenantId();
        $tags = collect();

        try {
            $tags = DB::table('tags')
                ->where('account_id', $tenantId)
                ->whereNull('deleted_at')
                ->select('id', 'name', DB::raw("0 as recipientCount"))
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            Log::debug('tags query failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'data' => $tags->values(),
        ]);
    }

    public function optOutLists(): JsonResponse
    {
        $tenantId = $this->tenantId();
        $optOutLists = collect();

        try {
            $optOutLists = DB::table('opt_out_lists')
                ->where('account_id', $tenantId)
                ->whereNull('deleted_at')
                ->select('id', 'name', 'description', DB::raw("0 as recipientCount"))
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            Log::debug('opt_out_lists query failed', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'success' => true,
            'data' => $optOutLists->values(),
        ]);
    }

    public function approvedSmsTemplates(): JsonResponse
    {
        return $this->senderIdTemplates();
    }

    private function generateEmailAddress(string $name): ?string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $slug = trim($slug, '-');
        $slug = substr($slug, 0, 30);
        $emailDomain = '@sms.quicksms.com';

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $suffix = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $candidate = $slug . '-' . $suffix . $emailDomain;

            $exists = EmailToSmsAddress::withoutGlobalScopes()
                ->where('email_address', $candidate)
                ->exists();

            if (!$exists) {
                return $candidate;
            }
        }

        return null;
    }

    private function syncRecipients(EmailToSmsSetup $setup, Request $request, string $tenantId): void
    {
        $contactBookListIds = $request->input('contactBookListIds', []);
        $contactBookListNames = $request->input('contactBookListNames', []);

        foreach ($contactBookListIds as $index => $recipientId) {
            $recipientName = $contactBookListNames[$index] ?? null;
            EmailToSmsRecipient::withoutGlobalScopes()->create([
                'setup_id' => $setup->id,
                'account_id' => $tenantId,
                'recipient_type' => 'list',
                'recipient_id' => $recipientId,
                'recipient_name' => $recipientName,
            ]);
        }

        $recipients = $request->input('recipients', []);
        if (!empty($recipients)) {
            $typeMap = ['contacts' => 'contact', 'lists' => 'list', 'dynamic_lists' => 'dynamic_list', 'tags' => 'tag'];
            foreach ($typeMap as $key => $recipientType) {
                foreach ($recipients[$key] ?? [] as $item) {
                    $itemId = is_array($item) ? ($item['id'] ?? null) : $item;
                    $itemName = is_array($item) ? ($item['name'] ?? null) : null;
                    if ($itemId) {
                        EmailToSmsRecipient::withoutGlobalScopes()->create([
                            'setup_id' => $setup->id,
                            'account_id' => $tenantId,
                            'recipient_type' => $recipientType,
                            'recipient_id' => $itemId,
                            'recipient_name' => $itemName,
                        ]);
                    }
                }
            }
        }
    }

    private function syncOptOutConfig(EmailToSmsSetup $setup, Request $request, string $tenantId): void
    {
        $optOutMode = $request->input('optOutMode', 'NONE');
        if ($optOutMode === 'NONE') {
            return;
        }

        $optOutListIds = $request->input('optOutListIds', []);
        $optOutListNames = $request->input('optOutListNames', []);

        foreach ($optOutListIds as $index => $listId) {
            $listName = $optOutListNames[$index] ?? null;
            EmailToSmsOptOutConfig::withoutGlobalScopes()->create([
                'setup_id' => $setup->id,
                'account_id' => $tenantId,
                'opt_out_list_id' => $listId,
                'opt_out_list_name' => $listName,
            ]);
        }
    }
}
