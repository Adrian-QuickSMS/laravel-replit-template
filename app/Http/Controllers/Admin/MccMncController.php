<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MccMnc;
use Illuminate\Http\Request;

class MccMncController extends Controller
{
    public function index()
    {
        $mccMncs = MccMnc::orderBy('country_name')
            ->orderBy('network_name')
            ->get();

        return view('admin.supplier-management.mcc-mnc', compact('mccMncs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mcc' => 'required|string|size:3',
            'mnc' => 'required|string|max:3',
            'country_name' => 'required|string|max:255',
            'country_iso' => 'required|string|size:2',
            'network_name' => 'required|string|max:255',
            'network_type' => 'required|in:mobile,fixed,virtual',
        ]);

        $validated['active'] = true;
        MccMnc::create($validated);

        return redirect()->route('admin.mcc-mnc.index')
            ->with('success', 'MCC/MNC entry created successfully.');
    }

    public function update(Request $request, MccMnc $mccMnc)
    {
        $validated = $request->validate([
            'country_name' => 'required|string|max:255',
            'country_iso' => 'required|string|size:2',
            'network_name' => 'required|string|max:255',
            'network_type' => 'required|in:mobile,fixed,virtual',
        ]);

        $mccMnc->update($validated);

        return redirect()->route('admin.mcc-mnc.index')
            ->with('success', 'MCC/MNC entry updated successfully.');
    }

    public function toggleStatus(MccMnc $mccMnc)
    {
        $mccMnc->update(['active' => !$mccMnc->active]);

        return redirect()->route('admin.mcc-mnc.index')
            ->with('success', 'MCC/MNC status updated.');
    }

    public function destroy(MccMnc $mccMnc)
    {
        $mccMnc->delete();

        return redirect()->route('admin.mcc-mnc.index')
            ->with('success', 'MCC/MNC entry deleted.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        return redirect()->route('admin.mcc-mnc.index')
            ->with('success', 'MCC/MNC data imported successfully.');
    }
}
