<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UkPrefix;
use App\Models\MccMnc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UkPrefixController extends Controller
{
    public function index(Request $request)
    {
        $query = UkPrefix::with('mccMnc');

        if ($request->filled('match_status')) {
            $query->where('match_status', $request->match_status);
        }
        if ($request->filled('cp_name')) {
            $query->where('cp_name', $request->cp_name);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('prefix', 'like', "%{$search}%")
                  ->orWhere('cp_name', 'like', "%{$search}%");
            });
        }

        $prefixes = $query->orderBy('prefix')->paginate(50);

        $cpNames = UkPrefix::select('cp_name')
            ->distinct()
            ->orderBy('cp_name')
            ->pluck('cp_name');

        $ukNetworks = MccMnc::whereIn('mcc', ['234', '235'])
            ->where('active', true)
            ->orderBy('network_name')
            ->get();

        $stats = [
            'total' => UkPrefix::count(),
            'matched' => UkPrefix::where('match_status', 'confirmed')->count(),
            'predicted' => UkPrefix::where('match_status', 'predicted')->count(),
            'unmatched' => UkPrefix::where('match_status', 'unmatched')->count(),
        ];

        return response()->json([
            'prefixes' => $prefixes,
            'cpNames' => $cpNames,
            'ukNetworks' => $ukNetworks,
            'stats' => $stats,
        ]);
    }

    public function mapNetwork(Request $request, UkPrefix $ukPrefix)
    {
        $request->validate([
            'mcc_mnc_id' => 'required|exists:mcc_mnc_master,id',
        ]);

        $ukPrefix->update([
            'mcc_mnc_id' => $request->mcc_mnc_id,
            'match_status' => 'confirmed',
        ]);

        return response()->json(['success' => true, 'message' => 'Network mapping confirmed.']);
    }

    public function confirmPrediction(UkPrefix $ukPrefix)
    {
        if (!$ukPrefix->mcc_mnc_id) {
            return response()->json(['success' => false, 'message' => 'No prediction to confirm.'], 422);
        }

        $ukPrefix->update(['match_status' => 'confirmed']);
        return response()->json(['success' => true, 'message' => 'Prediction confirmed.']);
    }

    public function rejectPrediction(UkPrefix $ukPrefix)
    {
        $ukPrefix->update([
            'mcc_mnc_id' => null,
            'match_status' => 'unmatched',
        ]);
        return response()->json(['success' => true, 'message' => 'Prediction rejected.']);
    }

    public function bulkConfirm(Request $request)
    {
        $request->validate([
            'cp_name' => 'required|string',
            'mcc_mnc_id' => 'required|exists:mcc_mnc_master,id',
        ]);

        $count = UkPrefix::where('cp_name', $request->cp_name)
            ->whereIn('match_status', ['predicted', 'unmatched'])
            ->update([
                'mcc_mnc_id' => $request->mcc_mnc_id,
                'match_status' => 'confirmed',
            ]);

        return response()->json(['success' => true, 'message' => "{$count} prefixes mapped.", 'count' => $count]);
    }

    public function createAndMap(Request $request)
    {
        $request->validate([
            'network_name' => 'required|string|max:255',
            'mnc' => 'required|string|max:3',
            'mcc' => 'required|string|size:3',
            'network_type' => 'required|in:mobile,fixed,virtual',
            'cp_name' => 'required|string',
        ]);

        $network = MccMnc::create([
            'mcc' => $request->mcc,
            'mnc' => str_pad($request->mnc, 2, '0', STR_PAD_LEFT),
            'country_name' => 'United Kingdom',
            'country_iso' => 'GB',
            'network_name' => $request->network_name,
            'network_type' => $request->network_type,
            'active' => true,
        ]);

        $count = UkPrefix::where('cp_name', $request->cp_name)
            ->whereIn('match_status', ['predicted', 'unmatched'])
            ->update([
                'mcc_mnc_id' => $network->id,
                'match_status' => 'confirmed',
            ]);

        return response()->json([
            'success' => true,
            'message' => "Network '{$request->network_name}' created and {$count} prefixes mapped.",
            'network' => $network,
            'count' => $count,
        ]);
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
                'message' => 'Unsupported file type. Please upload a CSV or Excel file.',
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
        $request->session()->put('uk_prefix_import_file', $storedName);

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
            'mapping.number_block' => 'required|integer|min:0',
            'mapping.cp_name' => 'required|integer|min:0',
        ]);

        $sessionFile = $request->session()->get('uk_prefix_import_file');
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
            $request->session()->forget('uk_prefix_import_file');
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
        $statusCol = isset($mapping['status']) && $mapping['status'] !== '' ? (int) $mapping['status'] : null;
        $numberLengthCol = isset($mapping['number_length']) && $mapping['number_length'] !== '' ? (int) $mapping['number_length'] : null;
        $allocationDateCol = isset($mapping['allocation_date']) && $mapping['allocation_date'] !== '' ? (int) $mapping['allocation_date'] : null;

        $ukNetworks = MccMnc::whereIn('mcc', ['234', '235'])
            ->where('active', true)
            ->get();

        $imported = 0;
        $updated = 0;
        $errors = [];
        $predictions = [];
        $dataRows = array_slice($rows, 1);

        foreach ($dataRows as $index => $row) {
            $rowNum = $index + 2;
            try {
                $rawBlock = trim($row[$mapping['number_block']] ?? '');
                $cpName = trim($row[$mapping['cp_name']] ?? '');

                if (empty($rawBlock) || empty($cpName)) {
                    $errors[] = ['row' => $rowNum, 'error' => 'Missing required fields'];
                    continue;
                }

                $cleanNumber = preg_replace('/\s+/', '', $rawBlock);
                $cleanNumber = ltrim($cleanNumber, "'");

                if (!ctype_digit($cleanNumber)) {
                    $errors[] = ['row' => $rowNum, 'error' => "Non-numeric prefix: {$rawBlock}"];
                    continue;
                }

                if (strpos($cleanNumber, '0') === 0) {
                    $cleanNumber = ltrim($cleanNumber, '0');
                }

                if (strpos($cleanNumber, '44') !== 0) {
                    $cleanNumber = '44' . $cleanNumber;
                }

                $status = ($statusCol !== null) ? strtolower(trim($row[$statusCol] ?? 'allocated')) : 'allocated';
                $numberLength = ($numberLengthCol !== null) ? trim($row[$numberLengthCol] ?? '') : null;

                $allocationDate = null;
                if ($allocationDateCol !== null) {
                    $rawDate = trim($row[$allocationDateCol] ?? '');
                    if (!empty($rawDate)) {
                        if (is_numeric($rawDate)) {
                            try {
                                $allocationDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $rawDate)->format('Y-m-d');
                            } catch (\Exception $e) {
                                $allocationDate = null;
                            }
                        } else {
                            try {
                                $allocationDate = date('Y-m-d', strtotime($rawDate));
                                if ($allocationDate === '1970-01-01') $allocationDate = null;
                            } catch (\Exception $e) {
                                $allocationDate = null;
                            }
                        }
                    }
                }

                $matchedNetwork = $this->predictNetwork($cpName, $ukNetworks);
                $matchStatus = $matchedNetwork ? 'predicted' : 'unmatched';

                $existing = UkPrefix::where('prefix', $cleanNumber)->first();
                if ($existing) {
                    $existing->update([
                        'number_block_raw' => $rawBlock,
                        'status' => $status,
                        'cp_name' => $cpName,
                        'number_length' => $numberLength,
                        'allocation_date' => $allocationDate,
                        'mcc_mnc_id' => $matchedNetwork ? $matchedNetwork->id : $existing->mcc_mnc_id,
                        'match_status' => $existing->match_status === 'confirmed' ? 'confirmed' : $matchStatus,
                    ]);
                    $updated++;
                } else {
                    UkPrefix::create([
                        'prefix' => $cleanNumber,
                        'number_block_raw' => $rawBlock,
                        'status' => $status,
                        'cp_name' => $cpName,
                        'number_length' => $numberLength,
                        'allocation_date' => $allocationDate,
                        'mcc_mnc_id' => $matchedNetwork ? $matchedNetwork->id : null,
                        'match_status' => $matchStatus,
                        'active' => true,
                    ]);
                    $imported++;
                }

                if ($matchedNetwork && !isset($predictions[$cpName])) {
                    $predictions[$cpName] = $matchedNetwork->network_name;
                }

            } catch (\Exception $e) {
                $errors[] = ['row' => $rowNum, 'error' => $e->getMessage()];
            }
        }

        Storage::disk('local')->delete($sessionFile);
        $request->session()->forget('uk_prefix_import_file');

        $unmatchedCps = UkPrefix::where('match_status', 'unmatched')
            ->select('cp_name')
            ->selectRaw('count(*) as prefix_count')
            ->groupBy('cp_name')
            ->orderByDesc('prefix_count')
            ->get();

        $predictedCps = UkPrefix::where('match_status', 'predicted')
            ->join('mcc_mnc_master', 'uk_prefixes.mcc_mnc_id', '=', 'mcc_mnc_master.id')
            ->select('uk_prefixes.cp_name', 'mcc_mnc_master.network_name', 'uk_prefixes.mcc_mnc_id')
            ->selectRaw('count(*) as prefix_count')
            ->groupBy('uk_prefixes.cp_name', 'mcc_mnc_master.network_name', 'uk_prefixes.mcc_mnc_id')
            ->orderByDesc('prefix_count')
            ->get();

        return response()->json([
            'success' => true,
            'imported' => $imported,
            'updated' => $updated,
            'errors' => array_slice($errors, 0, 100),
            'total' => count($dataRows),
            'totalErrors' => count($errors),
            'unmatchedCps' => $unmatchedCps,
            'predictedCps' => $predictedCps,
        ]);
    }

    private function predictNetwork($cpName, $networks)
    {
        $cpLower = strtolower($cpName);

        $networkKeywords = [
            'vodafone' => ['vodafone'],
            'o2' => ['o2', 'telefonica', 'telefónica'],
            'three' => ['three', 'hutchison 3g', '3 uk', 'h3g'],
            'ee' => ['everything everywhere', 'ee ', 'orange', 't-mobile', 'bt'],
            'virgin' => ['virgin'],
            'sky' => ['sky'],
            'lycamobile' => ['lycamobile', 'lyca'],
            'lebara' => ['lebara'],
            'truphone' => ['truphone'],
            'jersey' => ['jersey telecom', 'jtl', 'jt '],
            'sure' => ['sure ', 'sure mobile', 'cable & wireless'],
            'manx' => ['manx'],
        ];

        foreach ($networks as $network) {
            $netLower = strtolower($network->network_name);
            foreach ($networkKeywords as $key => $keywords) {
                $cpMatches = false;
                $netMatches = false;

                foreach ($keywords as $kw) {
                    if (strpos($cpLower, $kw) !== false) $cpMatches = true;
                    if (strpos($netLower, $kw) !== false) $netMatches = true;
                }

                if ($cpMatches && $netMatches) {
                    return $network;
                }
            }
        }

        foreach ($networks as $network) {
            $netNameClean = strtolower(preg_replace('/\s*(ltd|limited|plc|uk|mnO|mvnO|mno|telecom|telecommunications|communications)\s*/i', ' ', $network->network_name));
            $netWords = array_filter(explode(' ', trim($netNameClean)), fn($w) => strlen($w) > 2);

            foreach ($netWords as $word) {
                if (strpos($cpLower, $word) !== false) {
                    return $network;
                }
            }
        }

        return null;
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
