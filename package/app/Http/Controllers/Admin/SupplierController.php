<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\RateCardAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('gateways')
            ->with('gateways')
            ->orderBy('name')
            ->get()
            ->map(function ($supplier) {
                $supplier->gateway_count = $supplier->gateways_count;
                $supplier->last_rate_update = $supplier->rateCards()
                    ->latest('created_at')
                    ->value('created_at');
                return $supplier;
            });

        return view('admin.supplier-management.suppliers', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_currency' => 'required|string|size:3',
            'default_billing_method' => 'required|in:submitted,delivered',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        // Generate supplier code
        $validated['supplier_code'] = 'SUP-' . strtoupper(Str::random(8));
        $validated['status'] = 'active';

        $supplier = Supplier::create($validated);

        // Log action
        RateCardAuditLog::logAction('supplier_created', [
            'supplier_id' => $supplier->id,
            'new_value' => $supplier->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Supplier created successfully',
            'supplier' => $supplier,
        ]);
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'default_currency' => 'required|string|size:3',
            'default_billing_method' => 'required|in:submitted,delivered',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $oldValue = $supplier->toArray();
        $supplier->update($validated);

        // Log action
        RateCardAuditLog::logAction('supplier_updated', [
            'supplier_id' => $supplier->id,
            'old_value' => $oldValue,
            'new_value' => $supplier->fresh()->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Supplier updated successfully',
            'supplier' => $supplier,
        ]);
    }

    public function suspend(Supplier $supplier)
    {
        $oldValue = $supplier->toArray();

        $supplier->update([
            'status' => $supplier->status === 'active' ? 'suspended' : 'active'
        ]);

        // Log action
        RateCardAuditLog::logAction('supplier_suspended', [
            'supplier_id' => $supplier->id,
            'old_value' => $oldValue,
            'new_value' => $supplier->fresh()->toArray(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Supplier status updated successfully',
            'supplier' => $supplier,
        ]);
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->gateways()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete supplier with existing gateways. Please delete gateways first.',
            ], 400);
        }

        $supplier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Supplier deleted successfully',
        ]);
    }
}
