<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailToSmsReportingGroup;
use App\Models\EmailToSmsSetup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Get computed aggregates from message_logs if table exists
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

        $group = EmailToSmsReportingGroup::create([
            'account_id' => $this->tenantId(),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'status' => 'active',
        ]);

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

        $group->update($request->only(['name', 'description']));

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

        return response()->json([
            'success' => true,
            'data' => $this->transformGroup($group),
            'message' => 'Reporting group unarchived successfully',
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $group = EmailToSmsReportingGroup::forAccount($this->tenantId())->findOrFail($id);

        // Soft delete
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
