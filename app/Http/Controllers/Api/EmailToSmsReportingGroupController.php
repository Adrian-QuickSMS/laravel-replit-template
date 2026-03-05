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
        $query = EmailToSmsReportingGroup::forAccount($this->tenantId());

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

        // Compute aggregates: messagesSent and lastActivity from message_logs
        $groupIds = $groups->pluck('id');
        $linkedSetups = EmailToSmsSetup::forAccount($this->tenantId())
            ->whereIn('reporting_group_id', $groupIds)
            ->select('id', 'name', 'reporting_group_id')
            ->get()
            ->groupBy('reporting_group_id');

        // Get computed aggregates from message_logs if table/column exists
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
            // message_logs may not have email_to_sms_setup_id column yet
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
            'description' => 'nullable|string|max:1000',
        ]);

        $tenantId = $this->tenantId();

        // Check for duplicate name within tenant
        $nameExists = EmailToSmsReportingGroup::forAccount($tenantId)
            ->where('name', $request->input('name'))
            ->exists();
        if ($nameExists) {
            return response()->json(['success' => false, 'error' => 'A reporting group with this name already exists'], 422);
        }

        $group = DB::transaction(function () use ($request, $tenantId) {
            $group = EmailToSmsReportingGroup::create([
                'account_id' => $tenantId,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'status' => 'active',
            ]);

            EmailToSmsAuditLog::logAction(
                $tenantId, 'created', 'reporting_group', null, $group->id,
                ['name' => $group->name]
            );

            return $group;
        });

        return response()->json([
            'success' => true,
            'data' => $this->transformGroup($group),
            'message' => 'Reporting group created successfully',
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $group = EmailToSmsReportingGroup::forAccount($this->tenantId())->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->transformGroup($group),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $group = EmailToSmsReportingGroup::forAccount($this->tenantId())->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $tenantId = $this->tenantId();

        // Check for duplicate name within tenant (if name is being changed)
        if ($request->filled('name') && $request->input('name') !== $group->name) {
            $nameExists = EmailToSmsReportingGroup::forAccount($tenantId)
                ->where('name', $request->input('name'))
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
                $tenantId, 'updated', 'reporting_group', null, $group->id,
                ['before' => $original, 'after' => $updateFields]
            );
        });

        return response()->json([
            'success' => true,
            'data' => $this->transformGroup($group),
            'message' => 'Reporting group updated successfully',
        ]);
    }

    public function archive(string $id): JsonResponse
    {
        $group = EmailToSmsReportingGroup::forAccount($this->tenantId())->findOrFail($id);
        $group->update(['status' => 'archived']);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'archived', 'reporting_group', null, $group->id
        );

        return response()->json([
            'success' => true,
            'data' => $this->transformGroup($group),
            'message' => 'Reporting group archived successfully',
        ]);
    }

    public function unarchive(string $id): JsonResponse
    {
        $group = EmailToSmsReportingGroup::forAccount($this->tenantId())->findOrFail($id);
        $group->update(['status' => 'active']);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'unarchived', 'reporting_group', null, $group->id
        );

        return response()->json([
            'success' => true,
            'data' => $this->transformGroup($group),
            'message' => 'Reporting group unarchived successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $group = EmailToSmsReportingGroup::forAccount($this->tenantId())->findOrFail($id);

        EmailToSmsAuditLog::logAction(
            $this->tenantId(), 'deleted', 'reporting_group', null, $group->id,
            ['name' => $group->name]
        );

        $group->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reporting group deleted successfully',
        ]);
    }

    private function transformGroup(EmailToSmsReportingGroup $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'description' => $group->description,
            'status' => ucfirst($group->status),
            'created' => $group->created_at?->format('Y-m-d'),
            'createdAt' => $group->created_at?->toIso8601String(),
            'updatedAt' => $group->updated_at?->toIso8601String(),
        ];
    }
}
