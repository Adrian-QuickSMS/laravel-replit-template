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

        $suppliers = Supplier::where('status', 'active')->orderBy('name')->get();

        return view('admin.supplier-management.gateways', compact('gateways', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'gateway_code' => 'required|string|max:50|unique:gateways,gateway_code',
            'name' => 'required|string|max:255',
            'currency' => 'required|string|size:3',
            'billing_method' => 'required|in:submitted,delivered',
            'fx_source' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $validated['active'] = true;

        $gateway = Gateway::create($validated);

        RateCardAuditLog::create([
            'supplier_id' => $gateway->supplier_id,
            'gateway_id' => $gateway->id,
            'action' => 'gateway_created',
            'admin_user' => session('admin_email', 'admin@quicksms.co.uk'),
            'admin_email' => session('admin_email', 'admin@quicksms.co.uk'),
            'ip_address' => $request->ip(),
            'new_value' => $validated,
        ]);

        return redirect()->route('admin.gateways.index')
            ->with('success', 'Gateway created successfully.');
    }

    public function update(Request $request, Gateway $gateway)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'currency' => 'required|string|size:3',
            'billing_method' => 'required|in:submitted,delivered',
            'fx_source' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $before = $gateway->toArray();
        $gateway->update($validated);

        RateCardAuditLog::create([
            'supplier_id' => $gateway->supplier_id,
            'gateway_id' => $gateway->id,
            'action' => 'gateway_updated',
            'admin_user' => session('admin_email', 'admin@quicksms.co.uk'),
            'admin_email' => session('admin_email', 'admin@quicksms.co.uk'),
            'ip_address' => $request->ip(),
            'old_value' => $before,
            'new_value' => $gateway->fresh()->toArray(),
        ]);

        return redirect()->route('admin.gateways.index')
            ->with('success', 'Gateway updated successfully.');
    }

    public function toggleStatus(Request $request, Gateway $gateway)
    {
        $old = $gateway->active;
        $gateway->update(['active' => !$gateway->active]);

        RateCardAuditLog::create([
            'supplier_id' => $gateway->supplier_id,
            'gateway_id' => $gateway->id,
            'action' => 'gateway_updated',
            'admin_user' => session('admin_email', 'admin@quicksms.co.uk'),
            'admin_email' => session('admin_email', 'admin@quicksms.co.uk'),
            'ip_address' => $request->ip(),
            'old_value' => ['active' => $old],
            'new_value' => ['active' => $gateway->active],
        ]);

        return redirect()->route('admin.gateways.index')
            ->with('success', 'Gateway status updated.');
    }

    public function destroy(Request $request, Gateway $gateway)
    {
        $gateway->delete();

        return redirect()->route('admin.gateways.index')
            ->with('success', 'Gateway deleted successfully.');
    }
}
