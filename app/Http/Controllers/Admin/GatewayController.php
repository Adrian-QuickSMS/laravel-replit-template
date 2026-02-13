<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\Supplier;
use App\Models\RateCardAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GatewayController extends Controller
{
    public function index()
    {
        $gateways = Gateway::with('supplier')
            ->withCount('rateCards')
            ->orderBy('name')
            ->get();

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('admin.supplier-management.gateways', compact('gateways', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'name' => 'required|string|max:255',
            'currency' => 'required|string|size:3',
            'billing_method' => 'required|in:submitted,delivered',
            'fx_source' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Generate gateway code
        $validated['gateway_code'] = 'GW-' . strtoupper(Str::random(8));
        $validated['active'] = true;

        $gateway = Gateway::create($validated);

        // Log action
        RateCardAuditLog::logAction('gateway_created', [
            'supplier_id' => $gateway->supplier_id,
            'gateway_id' => $gateway->id,
            'new_value' => $gateway->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gateway created successfully',
            'gateway' => $gateway->load('supplier'),
        ]);
    }

    public function update(Request $request, Gateway $gateway)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'required|string|size:3',
            'billing_method' => 'required|in:submitted,delivered',
            'fx_source' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $oldValue = $gateway->toArray();
        $gateway->update($validated);

        // Log action
        RateCardAuditLog::logAction('gateway_updated', [
            'supplier_id' => $gateway->supplier_id,
            'gateway_id' => $gateway->id,
            'old_value' => $oldValue,
            'new_value' => $gateway->fresh()->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gateway updated successfully',
            'gateway' => $gateway->load('supplier'),
        ]);
    }

    public function toggleStatus(Gateway $gateway)
    {
        $oldValue = $gateway->toArray();

        $gateway->update([
            'active' => !$gateway->active
        ]);

        // Log action
        RateCardAuditLog::logAction('gateway_updated', [
            'supplier_id' => $gateway->supplier_id,
            'gateway_id' => $gateway->id,
            'old_value' => $oldValue,
            'new_value' => $gateway->fresh()->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gateway status updated successfully',
            'gateway' => $gateway,
        ]);
    }

    public function destroy(Gateway $gateway)
    {
        if ($gateway->rateCards()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete gateway with existing rate cards. Please archive rate cards first.',
            ], 400);
        }

        $gateway->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gateway deleted successfully',
        ]);
    }
}
