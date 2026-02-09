<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MccMnc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MccMncController extends Controller
{
    public function index()
    {
        $mccMncList = MccMnc::orderBy('country_name')
            ->orderBy('network_name')
            ->paginate(50);

        $countries = MccMnc::select('country_iso', 'country_name')
            ->distinct()
            ->orderBy('country_name')
            ->get();

        return view('admin.supplier-management.mcc-mnc', compact('mccMncList', 'countries'));
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

    public function parseFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, ['csv', 'xlsx', 'xls'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unsupported file type. Please upload a CSV or Excel (.xlsx/.xls) file.',
            ], 422);
        }

        try {
            $rows = $this->readFileRows($file->getRealPath(), $extension);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not read the file: ' . $e->getMessage(),
            ], 422);
        }

        if (count($rows) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'The file must contain a header row and at least one data row.',
            ], 422);
        }

        $headers = array_map('trim', $rows[0]);
        $previewRows = array_slice($rows, 1, 5);
        $totalDataRows = count($rows) - 1;

        $storedName = $file->store('imports', 'local');

        $importId = basename($storedName);
        $request->session()->put('mcc_mnc_import_file', $storedName);

        return response()->json([
            'success' => true,
            'headers' => $headers,
            'preview' => $previewRows,
            'totalRows' => $totalDataRows,
            'importId' => $importId,
            'fileName' => $file->getClientOriginalName(),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'importId' => 'required|string',
            'mapping' => 'required|array',
            'mapping.mcc' => 'required|integer|min:0',
            'mapping.mnc' => 'required|integer|min:0',
            'mapping.country_name' => 'required|integer|min:0',
            'mapping.country_iso' => 'required|integer|min:0',
            'mapping.network_name' => 'required|integer|min:0',
        ]);

        $sessionFile = $request->session()->get('mcc_mnc_import_file');
        if (!$sessionFile) {
            return response()->json([
                'success' => false,
                'message' => 'Upload session expired. Please upload the file again.',
            ], 422);
        }

        $importId = basename($request->importId);
        if (basename($sessionFile) !== $importId) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid import session. Please upload the file again.',
            ], 422);
        }

        if (!Storage::disk('local')->exists($sessionFile)) {
            $request->session()->forget('mcc_mnc_import_file');
            return response()->json([
                'success' => false,
                'message' => 'Upload session expired. Please upload the file again.',
            ], 422);
        }

        $fullPath = Storage::disk('local')->path($sessionFile);
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        try {
            $rows = $this->readFileRows($fullPath, $extension);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not read the file: ' . $e->getMessage(),
            ], 500);
        }

        $mapping = $request->mapping;
        $networkTypeCol = isset($mapping['network_type']) && $mapping['network_type'] !== '' && $mapping['network_type'] !== null
            ? (int) $mapping['network_type']
            : null;

        $imported = 0;
        $updated = 0;
        $errors = [];

        $dataRows = array_slice($rows, 1);

        foreach ($dataRows as $index => $row) {
            $rowNum = $index + 2;

            try {
                $mcc = trim($row[$mapping['mcc']] ?? '');
                $mnc = trim($row[$mapping['mnc']] ?? '');
                $countryName = trim($row[$mapping['country_name']] ?? '');
                $countryIso = strtoupper(trim($row[$mapping['country_iso']] ?? ''));
                $networkName = trim($row[$mapping['network_name']] ?? '');
                $networkType = ($networkTypeCol !== null)
                    ? strtolower(trim($row[$networkTypeCol] ?? 'mobile'))
                    : 'mobile';

                if (empty($mcc) || empty($mnc) || empty($countryName) || empty($countryIso) || empty($networkName)) {
                    $errors[] = ['row' => $rowNum, 'error' => 'Missing required fields'];
                    continue;
                }

                $mcc = ltrim($mcc, "'");
                $mnc = ltrim($mnc, "'");

                if (!ctype_digit($mcc)) {
                    $errors[] = ['row' => $rowNum, 'error' => "Invalid MCC (non-numeric): {$mcc}"];
                    continue;
                }
                $mcc = str_pad($mcc, 3, '0', STR_PAD_LEFT);

                if (!ctype_digit($mnc)) {
                    $errors[] = ['row' => $rowNum, 'error' => "Invalid MNC (non-numeric): {$mnc}"];
                    continue;
                }
                if (strlen($mnc) > 3) {
                    $errors[] = ['row' => $rowNum, 'error' => "Invalid MNC (too long): {$mnc}"];
                    continue;
                }
                $mnc = str_pad($mnc, 2, '0', STR_PAD_LEFT);

                if (strlen($countryIso) !== 2) {
                    $errors[] = ['row' => $rowNum, 'error' => "Invalid country ISO: {$countryIso}"];
                    continue;
                }

                if (!in_array($networkType, ['mobile', 'fixed', 'virtual'])) {
                    $networkType = 'mobile';
                }

                $existing = MccMnc::where('mcc', $mcc)->where('mnc', $mnc)->first();
                if ($existing) {
                    $existing->update([
                        'country_name' => $countryName,
                        'country_iso' => $countryIso,
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
                        'network_name' => $networkName,
                        'network_type' => $networkType,
                        'active' => true,
                    ]);
                    $imported++;
                }
            } catch (\Exception $e) {
                $errors[] = ['row' => $rowNum, 'error' => $e->getMessage()];
            }
        }

        Storage::disk('local')->delete($sessionFile);
        $request->session()->forget('mcc_mnc_import_file');

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'errors' => $errors,
            'total' => count($dataRows),
        ]);
    }

    private function readFileRows($filePath, $extension)
    {
        if ($extension === 'csv') {
            return $this->readCsv($filePath);
        }

        return $this->readExcel($filePath);
    }

    private function readCsv($filePath)
    {
        $handle = @fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Unable to open the file for reading.');
        }

        $rows = [];
        $maxRows = 50001;

        while (($row = fgetcsv($handle)) !== false && count($rows) < $maxRows) {
            if (count($rows) === 0 && count($row) === 1 && empty(trim($row[0]))) {
                continue;
            }
            $rows[] = $row;
        }

        fclose($handle);

        if (empty($rows)) {
            throw new \RuntimeException('The file appears to be empty.');
        }

        return $rows;
    }

    private function readExcel($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = [];
        $maxRows = 50001;

        foreach ($worksheet->getRowIterator() as $row) {
            if (count($rows) >= $maxRows) break;

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowData = [];

            foreach ($cellIterator as $cell) {
                $rowData[] = (string) $cell->getValue();
            }

            $rows[] = $rowData;
        }

        if (empty($rows)) {
            throw new \RuntimeException('The spreadsheet appears to be empty.');
        }

        return $rows;
    }
}
