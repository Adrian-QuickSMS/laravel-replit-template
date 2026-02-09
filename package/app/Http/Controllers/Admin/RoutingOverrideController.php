<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoutingCustomerOverride;
use App\Models\RoutingAuditLog;
use App\Models\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoutingOverrideController extends Controller
{
    /**
     * Display customer overrides
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'active');

        $query = RoutingCustomerOverride::with(['forcedGateway.supplier', 'secondaryGateway.supplier']);

        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'expired') {
            $query->expired();
        }

        $overrides = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('admin.routing-rules.customer-overrides', compact('overrides', 'status'));
    }

    /**
     * Store new customer override
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer',
            'customer_name' => 'required|string',
            'product_type' => 'required|in:SMS,RCS_BASIC,RCS_SINGLE,ALL',
            'scope_type' => 'required|in:GLOBAL,UK_NETWORK,COUNTRY',
            'scope_value' => 'nullable|string|max:10',
            'forced_gateway_id' => 'required|exists:gateways,id',
            'secondary_gateway_id' => 'nullable|exists:gateways,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'nullable|date|after:start_datetime',
            'reason' => 'required|string',
            'notify_customer' => 'boolean',
        ]);

        // Validate scope_value is provided for non-global scopes
        if ($request->scope_type !== 'GLOBAL' && !$request->scope_value) {
            return response()->json([
                'success' => false,
                'message' => 'Scope value required for non-global overrides'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $override = RoutingCustomerOverride::create([
                'customer_id' => $request->customer_id,
                'customer_name' => $request->customer_name,
                'product_type' => $request->product_type,
                'scope_type' => $request->scope_type,
                'scope_value' => $request->scope_value,
                'forced_gateway_id' => $request->forced_gateway_id,
                'secondary_gateway_id' => $request->secondary_gateway_id,
                'start_datetime' => Carbon::parse($request->start_datetime),
                'end_datetime' => $request->end_datetime ? Carbon::parse($request->end_datetime) : null,
                'status' => 'active',
                'reason' => $request->reason,
                'notify_customer' => $request->notify_customer ?? false,
                'created_by' => auth()->user()->name ?? 'ADMIN',
            ]);

            // Log action
            RoutingAuditLog::logAction('override_created', [
                'entity_type' => 'routing_customer_override',
                'entity_id' => $override->id,
                'product_type' => $override->product_type,
                'destination' => $override->scope_type === 'GLOBAL' ? 'ALL' : $override->scope_value,
                'new_value' => [
                    'customer' => $override->customer_name,
                    'forced_gateway' => $override->forcedGateway->name,
                    'start' => $override->start_datetime->toDateTimeString(),
                    'end' => $override->end_datetime ? $override->end_datetime->toDateTimeString() : 'indefinite',
                ],
                'reason' => $request->reason,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'override' => $override]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update existing override
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'end_datetime' => 'nullable|date',
            'reason' => 'nullable|string',
        ]);

        $override = RoutingCustomerOverride::findOrFail($id);

        DB::beginTransaction();
        try {
            $oldValues = [
                'end_datetime' => $override->end_datetime,
            ];

            $override->update([
                'end_datetime' => $request->end_datetime ? Carbon::parse($request->end_datetime) : $override->end_datetime,
                'updated_by' => auth()->user()->name ?? 'ADMIN',
            ]);

            // Log action
            RoutingAuditLog::logAction('override_edited', [
                'entity_type' => 'routing_customer_override',
                'entity_id' => $id,
                'product_type' => $override->product_type,
                'destination' => $override->scope_type === 'GLOBAL' ? 'ALL' : $override->scope_value,
                'old_value' => $oldValues,
                'new_value' => [
                    'end_datetime' => $override->end_datetime,
                ],
                'reason' => $request->reason,
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancel override
     */
    public function cancel(Request $request, $id)
    {
        $override = RoutingCustomerOverride::findOrFail($id);

        if ($override->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Override is not active'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $override->update([
                'status' => 'cancelled',
                'end_datetime' => Carbon::now(),
                'updated_by' => auth()->user()->name ?? 'ADMIN',
            ]);

            // Log action
            RoutingAuditLog::logAction('override_cancelled', [
                'entity_type' => 'routing_customer_override',
                'entity_id' => $id,
                'product_type' => $override->product_type,
                'destination' => $override->scope_type === 'GLOBAL' ? 'ALL' : $override->scope_value,
                'old_value' => ['status' => 'active'],
                'new_value' => ['status' => 'cancelled'],
                'reason' => $request->reason,
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get override details
     */
    public function show($id)
    {
        $override = RoutingCustomerOverride::with(['forcedGateway.supplier', 'secondaryGateway.supplier'])
            ->findOrFail($id);

        return response()->json($override);
    }

    /**
     * Search customers (placeholder - would connect to actual customer API)
     */
    public function searchCustomers(Request $request)
    {
        $query = $request->get('query');

        // Placeholder - replace with actual customer search
        $customers = [
            ['id' => 1, 'name' => 'Test Customer 1', 'account_id' => 'ACC001'],
            ['id' => 2, 'name' => 'Test Customer 2', 'account_id' => 'ACC002'],
        ];

        return response()->json($customers);
    }
}
