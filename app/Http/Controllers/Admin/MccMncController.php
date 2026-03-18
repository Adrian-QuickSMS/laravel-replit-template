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

        if ($request->country) {
            $query->where('country_iso', $request->country);
        }

        if ($request->prefix) {
            $query->where('mcc', $request->prefix);
        }

        if ($request->status) {
            $query->where('active', $request->status === 'active');
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
        if ($request->has('entries')) {
            $validated = $request->validate([
                'country_name' => 'required|string',
                'country_iso' => 'required|string|size:2',
                'country_prefix' => 'nullable|string|max:10',
                'network_name' => 'required|string',
                'network_type' => 'required|in:mobile,fixed,virtual',
                'entries' => 'required|array|min:1',
                'entries.*.mcc' => 'required|string|size:3',
                'entries.*.mnc' => 'required|string|max:3',
            ]);

            $created = 0;
            $duplicates = [];

            $countryPrefix = isset($validated['country_prefix']) ? ltrim($validated['country_prefix'], '+') : null;

            foreach ($validated['entries'] as $entry) {
                $exists = MccMnc::where('mcc', $entry['mcc'])
                    ->where('mnc', $entry['mnc'])
                    ->exists();

                if ($exists) {
                    $duplicates[] = $entry['mcc'] . '/' . $entry['mnc'];
                    continue;
                }

                MccMnc::create([
                    'mcc' => $entry['mcc'],
                    'mnc' => $entry['mnc'],
                    'country_name' => $validated['country_name'],
                    'country_iso' => $validated['country_iso'],
                    'country_prefix' => $countryPrefix ?: null,
                    'network_name' => $validated['network_name'],
                    'network_type' => $validated['network_type'],
                    'active' => true,
                ]);
                $created++;
            }

            if ($created === 0 && !empty($duplicates)) {
                return response()->json([
                    'success' => false,
                    'message' => 'All MCC/MNC combinations already exist: ' . implode(', ', $duplicates),
                ], 400);
            }

            $msg = "{$created} network(s) added successfully";
            if (!empty($duplicates)) {
                $msg .= '. Skipped duplicates: ' . implode(', ', $duplicates);
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        $validated = $request->validate([
            'mcc' => 'required|string|size:3',
            'mnc' => 'required|string|max:3',
            'country_name' => 'required|string',
            'country_iso' => 'required|string|size:2',
            'country_prefix' => 'nullable|string|max:10',
            'network_name' => 'required|string',
            'network_type' => 'required|in:mobile,fixed,virtual',
        ]);

        $exists = MccMnc::where('mcc', $validated['mcc'])
            ->where('mnc', $validated['mnc'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'MCC/MNC combination already exists',
            ], 400);
        }

        if (isset($validated['country_prefix'])) {
            $validated['country_prefix'] = ltrim($validated['country_prefix'], '+');
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
            'country_name' => 'sometimes|required|string',
            'country_prefix' => 'nullable|string|max:10',
            'network_name' => 'required|string',
            'network_type' => 'required|in:mobile,fixed,virtual',
        ]);

        if (isset($validated['country_prefix'])) {
            $validated['country_prefix'] = ltrim($validated['country_prefix'], '+');
        }

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

    public function parseFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls,txt',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        try {
            if (in_array($ext, ['csv', 'txt'])) {
                $handle = fopen($file->getRealPath(), 'r');
                $headers = fgetcsv($handle);
                $rows = [];
                while (($row = fgetcsv($handle)) !== false) {
                    $rows[] = $row;
                }
                fclose($handle);
            } else {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $sheet = $reader->getActiveSheet();
                $allRows = $sheet->toArray();
                $headers = array_shift($allRows);
                $rows = $allRows;
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to parse file: ' . $e->getMessage()]);
        }

        $importId = uniqid('mcc_import_');
        $path = storage_path("app/imports/{$importId}." . $ext);
        if (!is_dir(storage_path('app/imports'))) {
            mkdir(storage_path('app/imports'), 0755, true);
        }
        $file->move(storage_path('app/imports'), "{$importId}.{$ext}");

        session(["import_{$importId}" => ['path' => $path, 'ext' => $ext]]);

        $cleanHeaders = array_map(function($h, $i) {
            return ($h === null || $h === '') ? ('Column ' . ($i + 1)) : (string) $h;
        }, $headers, array_keys($headers));

        return response()->json([
            'success' => true,
            'headers' => array_values($cleanHeaders),
            'preview' => array_slice($rows, 0, 10),
            'totalRows' => count($rows),
            'importId' => $importId,
        ]);
    }

    public function import(Request $request)
    {
        $importId = $request->input('importId');
        $mapping = $request->input('mapping');

        if (!$importId || !$mapping) {
            return response()->json(['success' => false, 'message' => 'Missing import ID or column mapping.'], 400);
        }

        $sessionData = session("import_{$importId}");
        if (!$sessionData || !file_exists($sessionData['path'])) {
            return response()->json(['success' => false, 'message' => 'Import session expired. Please re-upload the file.'], 400);
        }

        $ext = $sessionData['ext'];

        try {
            if (in_array($ext, ['csv', 'txt'])) {
                $handle = fopen($sessionData['path'], 'r');
                $headers = fgetcsv($handle);
                $rows = [];
                while (($row = fgetcsv($handle)) !== false) {
                    $rows[] = $row;
                }
                fclose($handle);
            } else {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::load($sessionData['path']);
                $sheet = $reader->getActiveSheet();
                $allRows = $sheet->toArray();
                $headers = array_shift($allRows);
                $rows = $allRows;
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to read file: ' . $e->getMessage()]);
        }

        $imported = 0;
        $updated = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;

            $mcc = trim($row[$mapping['mcc']] ?? '');
            $mnc = trim($row[$mapping['mnc']] ?? '');
            $countryName = trim($row[$mapping['country_name']] ?? '');
            $countryIso = strtoupper(trim($row[$mapping['country_iso']] ?? ''));
            $networkName = trim($row[$mapping['network_name']] ?? '');
            $countryPrefix = isset($mapping['country_prefix']) && $mapping['country_prefix'] !== '' ? trim($row[$mapping['country_prefix']] ?? '') : null;
            $networkType = isset($mapping['network_type']) && $mapping['network_type'] !== '' ? (trim($row[$mapping['network_type']] ?? '') ?: 'mobile') : 'mobile';

            if (!$mcc || !$mnc || !$countryName || !$countryIso || !$networkName) {
                $errors[] = ['row' => $rowNum, 'error' => 'Missing required field(s)'];
                continue;
            }

            if ($countryPrefix) {
                $countryPrefix = ltrim($countryPrefix, '+');
            }

            try {
                $existing = MccMnc::where('mcc', $mcc)->where('mnc', $mnc)->first();

                if ($existing) {
                    $existing->update([
                        'country_name' => $countryName,
                        'country_iso' => $countryIso,
                        'country_prefix' => $countryPrefix ?: $existing->country_prefix,
                        'network_name' => $networkName,
                        'network_type' => $networkType,
                    ]);
                    $updated++;
                } else {
                    MccMnc::create([
                        'mcc' => $mcc,
                        'mnc' => $mnc,
                        'country_name' => $countryName,
                        'country_iso' => $countryIso,
                        'country_prefix' => $countryPrefix ?: null,
                        'network_name' => $networkName,
                        'network_type' => $networkType,
                        'active' => true,
                    ]);
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = ['row' => $rowNum, 'error' => "MCC {$mcc}/MNC {$mnc}: " . $e->getMessage()];
            }
        }

        @unlink($sessionData['path']);
        session()->forget("import_{$importId}");

        return response()->json([
            'success' => true,
            'message' => "Imported {$imported} new, updated {$updated} existing networks",
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
        ]);
    }
}
