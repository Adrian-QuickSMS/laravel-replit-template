<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailToSmsSetup;
use App\Models\EmailToSmsReportingGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminEmailToSmsController extends Controller
{
    // =====================================================
    // OVERVIEW — Cross-account listing for admin panel
    // =====================================================

    public function overview(Request $request): JsonResponse
    {
        $query = EmailToSmsSetup::with(['account', 'subAccount', 'reportingGroup']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->input('account_id'));
        }

        if ($request->filled('type')) {
            $types = is_array($request->input('type'))
                ? $request->input('type')
                : [$request->input('type')];
            $typeMap = ['Standard' => 'standard', 'Contact List' => 'contact_list'];
            $mapped = array_map(fn ($t) => $typeMap[$t] ?? $t, $types);
            $query->whereIn('type', $mapped);
        }

        if ($request->filled('status')) {
            $statuses = is_array($request->input('status'))
                ? $request->input('status')
                : [$request->input('status')];
            $statusMap = ['Active' => 'active', 'Suspended' => 'suspended', 'Archived' => 'archived'];
            $mapped = array_map(fn ($s) => $statusMap[$s] ?? strtolower($s), $statuses);
            $query->whereIn('status', $mapped);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        if (!$request->boolean('include_archived')) {
            $query->where('status', '!=', 'archived');
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('dir', 'desc');
        $allowed = ['name', 'type', 'status', 'created_at'];
        if (in_array($sortField, $allowed)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $setups = $query->get();

        $data = $setups->map(function ($setup) {
            $type = $setup->type === 'contact_list' ? 'Contact List' : 'Standard';
            return [
                'id' => $setup->id,
                'accountId' => $setup->account_id,
                'accountName' => $setup->account?->company_name ?? $setup->account?->trading_name ?? 'Unknown',
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
    // SINGLE SETUP CRUD (admin)
    // =====================================================

    public function show(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::with(['account', 'subAccount', 'reportingGroup'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->transformSetup($setup),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'allowed_sender_emails' => 'nullable|array',
            'sender_id_template_id' => 'nullable|string',
            'sender_id' => 'nullable|string|max:11',
            'multiple_sms_enabled' => 'nullable|boolean',
            'delivery_reports_enabled' => 'nullable|boolean',
            'delivery_reports_email' => 'nullable|email|max:255',
            'reporting_group_id' => 'nullable|string',
            'status' => 'nullable|in:active,suspended,archived',
        ]);

        $setup->update($request->only([
            'name', 'description', 'allowed_sender_emails',
            'sender_id_template_id', 'sender_id',
            'multiple_sms_enabled', 'delivery_reports_enabled', 'delivery_reports_email',
            'reporting_group_id', 'status',
        ]));

        $setup->load(['account', 'subAccount', 'reportingGroup']);

        return response()->json([
            'success' => true,
            'data' => $this->transformSetup($setup),
            'message' => 'Setup updated successfully',
        ]);
    }

    public function suspend(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::findOrFail($id);
        $setup->update(['status' => 'suspended']);

        return response()->json([
            'success' => true,
            'message' => 'Setup suspended successfully',
        ]);
    }

    public function reactivate(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::findOrFail($id);
        $setup->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'Setup reactivated successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $setup = EmailToSmsSetup::findOrFail($id);
        // Soft delete per user requirement
        $setup->delete();

        return response()->json([
            'success' => true,
            'message' => 'Setup deleted successfully',
        ]);
    }

    // =====================================================
    // REPORTING GROUPS (admin)
    // =====================================================

    public function reportingGroups(Request $request): JsonResponse
    {
        $query = EmailToSmsReportingGroup::with('account');

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->input('account_id'));
        }

        if (!$request->boolean('include_archived')) {
            $query->active();
        }

        $groups = $query->orderBy('name')->get();

        // Compute aggregates per group
        $groupIds = $groups->pluck('id');
        $linkedSetups = EmailToSmsSetup::whereIn('reporting_group_id', $groupIds)
            ->select('id', 'name', 'reporting_group_id')
            ->get()
            ->groupBy('reporting_group_id');

        $data = $groups->map(function ($group) use ($linkedSetups) {
            $setups = $linkedSetups->get($group->id, collect());
            return [
                'id' => $group->id,
                'accountId' => $group->account_id,
                'accountName' => $group->account?->company_name ?? 'Unknown',
                'name' => $group->name,
                'description' => $group->description,
                'linkedAddresses' => $setups->pluck('name')->values()->toArray(),
                'created' => $group->created_at?->format('Y-m-d'),
                'status' => ucfirst($group->status),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    // =====================================================
    // ACCOUNTS LIST (for admin filter dropdown)
    // =====================================================

    public function accounts(): JsonResponse
    {
        $accounts = DB::table('accounts')
            ->whereIn('id', function ($q) {
                $q->select('account_id')->from('email_to_sms_setups')->distinct();
            })
            ->select('id', 'company_name as name')
            ->orderBy('company_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    private function transformSetup(EmailToSmsSetup $setup): array
    {
        $type = $setup->type === 'contact_list' ? 'Contact List' : 'Standard';
        return [
            'id' => $setup->id,
            'accountId' => $setup->account_id,
            'accountName' => $setup->account?->company_name ?? 'Unknown',
            'name' => $setup->name,
            'description' => $setup->description,
            'type' => $type,
            'subaccountId' => $setup->sub_account_id,
            'subaccountName' => $setup->subAccount?->name ?? 'Main Account',
            'originatingEmails' => $setup->originating_emails ?? [],
            'allowedEmails' => $setup->allowed_sender_emails ?? [],
            'senderIdTemplateId' => $setup->sender_id_template_id,
            'senderId' => $setup->sender_id,
            'multipleSmsEnabled' => $setup->multiple_sms_enabled,
            'deliveryReportsEnabled' => $setup->delivery_reports_enabled,
            'deliveryReportsEmail' => $setup->delivery_reports_email,
            'reportingGroupId' => $setup->reporting_group_id,
            'reportingGroupName' => $setup->reportingGroup?->name,
            'contactBookListIds' => $setup->contact_book_list_ids ?? [],
            'optOutMode' => $setup->opt_out_mode,
            'optOutListIds' => $setup->opt_out_list_ids ?? [],
            'status' => ucfirst($setup->status),
            'created' => $setup->created_at?->format('Y-m-d'),
            'lastUsed' => $setup->updated_at?->format('Y-m-d H:i'),
            'sourceType' => $setup->type,
            'sourceId' => $setup->id,
        ];
    }
}
