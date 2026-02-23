<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MccMnc;
use Illuminate\Http\Request;

class MccMncController extends Controller
{
    public function index(Request $request)
    {
        $query = MccMnc::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('mcc', 'like', "%{$request->search}%")
                  ->orWhere('mnc', 'like', "%{$request->search}%")
                  ->orWhere('country_name', 'like', "%{$request->search}%")
                  ->orWhere('network_name', 'like', "%{$request->search}%");
            });
        }

        if ($request->country_iso) {
            $query->where('country_iso', $request->country_iso);
        }

        if ($request->has('active')) {
            $query->where('active', $request->active);
        }

        $networks = $query->orderBy('country_name')
            ->orderBy('network_name')
            ->paginate(100);

        $countries = MccMnc::select('country_name', 'country_iso')
            ->groupBy('country_name', 'country_iso')
            ->orderBy('country_name')
            ->get();

        $mccMncList = $networks;

        return view('admin.supplier-management.mcc-mnc', compact('mccMncList', 'countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mcc' => 'required|string|size:3',
            'mnc' => 'required|string|max:3',
            'country_name' => 'required|string',
            'country_iso' => 'required|string|size:2',
            'network_name' => 'required|string',
            'network_type' => 'required|in:mobile,fixed,virtual',
        ]);

        // Check for duplicates
        $exists = MccMnc::where('mcc', $validated['mcc'])
            ->where('mnc', $validated['mnc'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'MCC/MNC combination already exists',
            ], 400);
        }

        $validated['active'] = true;

        $network = MccMnc::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Network added successfully',
            'network' => $network,
        ]);
    }

    public function update(Request $request, MccMnc $mccMnc)
    {
        $validated = $request->validate([
            'country_name' => 'required|string',
            'network_name' => 'required|string',
            'network_type' => 'required|in:mobile,fixed,virtual',
        ]);

        $mccMnc->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Network updated successfully',
            'network' => $mccMnc,
        ]);
    }

    public function toggleStatus(MccMnc $mccMnc)
    {
        $mccMnc->update([
            'active' => !$mccMnc->active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Network status updated successfully',
            'network' => $mccMnc,
        ]);
    }

    public function destroy(MccMnc $mccMnc)
    {
        if ($mccMnc->rateCards()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete network with existing rate cards',
            ], 400);
        }

        $mccMnc->delete();

        return response()->json([
            'success' => true,
            'message' => 'Network deleted successfully',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $imported = 0;
        $skipped = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            // Skip if already exists
            $exists = MccMnc::where('mcc', $data['mcc'])
                ->where('mnc', $data['mnc'])
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            try {
                MccMnc::create([
                    'mcc' => $data['mcc'],
                    'mnc' => $data['mnc'],
                    'country_name' => $data['country_name'],
                    'country_iso' => $data['country_iso'],
                    'network_name' => $data['network_name'],
                    'network_type' => $data['network_type'] ?? 'mobile',
                    'active' => true,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$data['mcc']}-{$data['mnc']}: " . $e->getMessage();
            }
        }

        fclose($handle);

        return response()->json([
            'success' => true,
            'message' => "Imported {$imported} networks, skipped {$skipped} duplicates",
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ]);
    }
}
