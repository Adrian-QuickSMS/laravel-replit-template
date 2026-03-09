<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailToSmsAuditLog;
use App\Models\EmailToSmsReportingGroup;
use App\Models\EmailToSmsSetup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmailToSmsReportingGroupController extends Controller
{
    private function tenantId(): string
    {
        return session('customer_tenant_id');
    }

    public function index(Request $request): JsonResponse
    {
        $query = EmailToSmsReportingGroup::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if (!$request->boolean('include_archived')) {
            $query->active();
        }

        $groups = $query->orderBy('name')->get();

        $groupIds = $groups->pluck('id');
        $linkedSetups = EmailToSmsSetup::whereIn('reporting_group_id', $groupIds)
            ->select('id', 'name', 'reporting_group_id')
            ->get()
            ->groupBy('reporting_group_id');

        $aggregates = collect();
        try {
            $aggregates = DB::table('message_logs')
                ->join('email_to_sms_setups', 'message_logs.email_to_sms_setup_id', '=', 'email_to_sms_setups.id')
                ->whereIn('email_to_sms_setups.reporting_group_id', $groupIds)
                ->where('email_to_sms_setups.account_id', $this->tenantId())
                ->groupBy('email_to_sms_setups.reporting_group_id')
                ->select(
                    'email_to_sms_setups.reporting_group_id',
                    DB::raw('COUNT(*) as messages_sent'),
                    DB::raw('MAX(message_logs.created_at) as last_activity')
                )
                ->get()
                ->keyBy('reporting_group_id');
        } catch (\Exception $e) {
            Log::warning('Email-to-SMS reporting group aggregates query failed', [
                'error' => $e->getMessage(),
            ]);
        }

        $data = $groups->map(function ($group) use ($linkedSetups, $aggregates) {
            $setups = $linkedSetups->get($group->id, collect());
            $agg = $aggregates->get($group->id);

            return [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'linkedAddresses' => $setups->pluck('name')->values()->toArray(),
                'messagesSent' => $agg->messages_sent ?? 0,
                'lastActivity' => $agg->last_activity ?? null,
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

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $tenantId = $this->tenantId();

        $nameExists = EmailToSmsReportingGroup::where('name', $request->input('name'))->exists();
        if ($nameExists) {
            return response()->json(['success' => false, 'error' => 'A reporting group with this name already exists'], 422);
        }

        $group = DB::transaction(function () use ($request, $tenantId) {
            $group = EmailToSmsReportingGroup::withoutGlobalScopes()->create([
                'account_id' => $tenantId,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'status' => 'active',
            ]);

            EmailToSmsAuditLog::logAction(
                $tenantId, 'created', null, $group->id,
                "Created reporting group: {$group->name}",
                ['name' => $group->name]
            );

            return $group;
        });

        return response()->json([
            'success' => true,
            'data' => $group->toPortalArray(),
            'message' => 'Reporting group created successfully',
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $group = EmailToSmsReportingGroup::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $group->toPortalArray(),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $group = EmailToSmsReportingGroup::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $tenantId = $this->tenantId();

        if ($request->filled('name') && $request->input('name') !== $group->name) {
            $nameExists = EmailToSmsReportingGroup::where('name', $request->input('name'))
                ->where('id', '!=', $group->id)
                ->exists();
            if ($nameExists) {
                return response()->json(['success' => false, 'error' => 'A reporting group with this name already exists'], 422);
            }
        }

        $updateFields = $request->only(['name', 'description']);

        DB::transaction(function () use ($group, $updateFields, $tenantId) {
            $original = $group->only(array_keys($updateFields));
            $group->update($updateFields);

            EmailToSmsAuditLog::logAction(
                $tenantId, 'updated', null, $group->id,
                "Updated reporting group: {$group->name}",
                ['before' => $original, 'after' => $updateFields]
            );
        });

        return response()->json([
            'success' => true,
            'data' => $group->toPortalArray(),
            'message' => 'Reporting group updated successfully',
        ]);
    }

    public function archive(string $id): JsonResponse
    {
        $group = EmailToSmsReportingGroup::findOrFail($id);
        $group->update(['status' => 'archived']);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'archived', null, $group->id,
            "Archived reporting group: {$group->name}"
        );

        return response()->json([
            'success' => true,
            'data' => $group->toPortalArray(),
            'message' => 'Reporting group archived successfully',
        ]);
    }

    public function unarchive(string $id): JsonResponse
    {
        $group = EmailToSmsReportingGroup::findOrFail($id);
        $group->update(['status' => 'active']);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'unarchived', null, $group->id,
            "Unarchived reporting group: {$group->name}"
        );

        return response()->json([
            'success' => true,
            'data' => $group->toPortalArray(),
            'message' => 'Reporting group unarchived successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $group = EmailToSmsReportingGroup::findOrFail($id);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'deleted', null, $group->id,
            "Deleted reporting group: {$group->name}",
            ['name' => $group->name]
        );

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reporting group deleted successfully',
        ]);
    }
}
