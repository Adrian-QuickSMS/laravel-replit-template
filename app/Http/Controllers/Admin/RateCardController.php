<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RateCard;
use App\Models\Gateway;
use App\Models\Supplier;
use App\Models\MccMnc;
use App\Models\FxRate;
use App\Models\RateCardAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class RateCardController extends Controller
{
    public function index(Request $request)
    {
        $query = RateCard::with(['supplier', 'gateway', 'mccMnc'])
            ->where('active', true);

        // Filters
        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->gateway_id) {
            $query->where('gateway_id', $request->gateway_id);
        }

        if ($request->country_iso) {
            $query->where('country_iso', $request->country_iso);
        }

        if ($request->product_type) {
            $query->where('product_type', $request->product_type);
        }

        if ($request->status) {
            $query->where('active', $request->status === 'active');
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('mcc', 'like', "%{$search}%")
                  ->orWhere('mnc', 'like', "%{$search}%")
                  ->orWhere('network_name', 'ilike', "%{$search}%");
            });
        }

        $rateCards = $query->orderBy('country_name')
            ->orderBy('network_name')
            ->paginate(50)
            ->appends($request->query());

        $suppliers = Supplier::active()->orderBy('name')->get();
        $gateways = Gateway::active()->with('supplier')->orderBy('name')->get();
        $countries = RateCard::select('country_iso', 'country_name')
            ->distinct()
            ->orderBy('country_name')
            ->get();

        return view('admin.supplier-management.rate-cards', compact('rateCards', 'suppliers', 'gateways', 'countries'));
    }

    public function uploadForm()
    {
        $suppliers = Supplier::active()->with('gateways')->orderBy('name')->get();

        return view('admin.supplier-management.upload-rates', compact('suppliers'));
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
                'message' => 'The file must contain at least 2 rows (a header and at least one data row).',
            ], 422);
        }

        if (count($rows) > 50000) {
            return response()->json([
                'success' => false,
                'message' => 'File exceeds the maximum of 50,000 rows.',
            ], 422);
        }

        $storedName = $file->store('imports', 'local');
        $importId = basename($storedName);
        $request->session()->put('rate_card_import_file', $storedName);
        $request->session()->put('rate_card_import_extension', $extension);

        $previewRows = array_slice($rows, 0, 20);

        return response()->json([
            'success' => true,
            'preview' => $previewRows,
            'totalRows' => count($rows),
            'importId' => $importId,
            'fileName' => $file->getClientOriginalName(),
        ]);
    }

    public function validateMapping(Request $request)
    {
        $request->validate([
            'importId' => 'required|string',
            'headerRow' => 'required|integer|min:0',
            'mapping' => 'required|array',
            'mapping.mcc' => 'required|integer|min:0',
            'mapping.mnc' => 'required|integer|min:0',
            'mapping.rate' => 'required|integer|min:0',
            'gateway_id' => 'required|exists:gateways,id',
        ]);

        $sessionFile = $request->session()->get('rate_card_import_file');
        $extension = $request->session()->get('rate_card_import_extension', 'csv');
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

        $fullPath = Storage::disk('local')->path($sessionFile);
        if (!file_exists($fullPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Upload file not found. Please upload again.',
            ], 422);
        }

        try {
            $rows = $this->readFileRows($fullPath, $extension);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not read the file: ' . $e->getMessage(),
            ], 500);
        }

        $headerRow = (int) $request->headerRow;
        $mapping = $request->mapping;
        $gateway = Gateway::with('supplier')->findOrFail($request->gateway_id);

        if ($headerRow >= count($rows) - 1) {
            return response()->json([
                'success' => false,
                'message' => 'Header row is out of range. There must be at least one data row after the header.',
            ], 422);
        }

        $headers = $rows[$headerRow] ?? [];
        $maxColIndex = count($headers) - 1;

        foreach (['mcc', 'mnc', 'rate'] as $requiredField) {
            if ((int) $mapping[$requiredField] > $maxColIndex) {
                return response()->json([
                    'success' => false,
                    'message' => "Column index for '{$requiredField}' is out of range (max column: {$maxColIndex}).",
                ], 422);
            }
        }

        foreach (['country_name', 'network_name'] as $optField) {
            if (isset($mapping[$optField]) && $mapping[$optField] !== '' && $mapping[$optField] !== null) {
                if ((int) $mapping[$optField] > $maxColIndex) {
                    return response()->json([
                        'success' => false,
                        'message' => "Column index for '{$optField}' is out of range (max column: {$maxColIndex}).",
                    ], 422);
                }
            }
        }

        $dataRows = array_slice($rows, $headerRow + 1);

        $productType = $request->input('product_type', 'SMS');
        $currency = $gateway->currency ?? 'GBP';
        $countryCol = isset($mapping['country_name']) && $mapping['country_name'] !== '' && $mapping['country_name'] !== null
            ? (int) $mapping['country_name'] : null;
        $networkCol = isset($mapping['network_name']) && $mapping['network_name'] !== '' && $mapping['network_name'] !== null
            ? (int) $mapping['network_name'] : null;

        $validRows = [];
        $errors = [];
        $newRates = 0;
        $updateRates = 0;

        foreach ($dataRows as $index => $row) {
            $rowNum = $headerRow + $index + 2;
            $mcc = trim($row[$mapping['mcc']] ?? '');
            $mnc = trim($row[$mapping['mnc']] ?? '');
            $rate = trim($row[$mapping['rate']] ?? '');
            $countryName = $countryCol !== null ? trim($row[$countryCol] ?? '') : '';
            $networkName = $networkCol !== null ? trim($row[$networkCol] ?? '') : '';

            $mcc = ltrim($mcc, "'");
            $mnc = ltrim($mnc, "'");

            if (empty($mcc) && empty($mnc) && empty($rate)) {
                continue;
            }

            $rowErrors = [];

            if (empty($mcc) || !ctype_digit($mcc)) {
                $rowErrors[] = 'Valid MCC is required';
            } else {
                $mcc = str_pad($mcc, 3, '0', STR_PAD_LEFT);
            }
            if (empty($mnc) || !ctype_digit($mnc)) {
                $rowErrors[] = 'Valid MNC is required';
            } else {
                $mnc = str_pad($mnc, 2, '0', STR_PAD_LEFT);
            }
            if (empty($rate) || !is_numeric($rate)) {
                $rowErrors[] = 'Valid rate is required';
            } elseif ((float) $rate < 0) {
                $rowErrors[] = 'Rate cannot be negative';
            }

            $mccMnc = null;
            if (empty($rowErrors)) {
                $mccMnc = MccMnc::byMccMnc($mcc, $mnc)->first();
                if (!$mccMnc) {
                    $rowErrors[] = "MCC/MNC {$mcc}/{$mnc} not found in master reference";
                }
            }

            if (!empty($rowErrors)) {
                $errors[] = ['row' => $rowNum, 'errors' => $rowErrors, 'data' => compact('mcc', 'mnc', 'rate')];
            } else {
                $existing = RateCard::where('gateway_id', $gateway->id)
                    ->where('mcc', $mcc)
                    ->where('mnc', $mnc)
                    ->where('product_type', $productType ?: 'SMS')
                    ->where('active', true)
                    ->first();

                if ($existing) {
                    $updateRates++;
                } else {
                    $newRates++;
                }

                $validRows[] = [
                    'mcc' => $mcc,
                    'mnc' => $mnc,
                    'rate' => $rate,
                    'currency' => $currency ?: 'GBP',
                    'product_type' => $productType ?: 'SMS',
                    'country_name' => $countryName ?: ($mccMnc->country_name ?? ''),
                    'network_name' => $networkName ?: ($mccMnc->network_name ?? ''),
                ];
            }
        }

        $preview = array_slice($validRows, 0, 20);

        return response()->json([
            'success' => true,
            'totalRows' => count($dataRows),
            'validRows' => count($validRows),
            'newRates' => $newRates,
            'updateRates' => $updateRates,
            'errors' => $errors,
            'preview' => $preview,
            'rates' => $validRows,
        ]);
    }

    public function processUpload(Request $request)
    {
        $request->validate([
            'gateway_id' => 'required|exists:gateways,id',
            'rates' => 'required|array',
            'valid_from' => 'required|date',
        ]);

        $gateway = Gateway::with('supplier')->findOrFail($request->gateway_id);

        DB::beginTransaction();

        try {
            $imported = 0;
            $updated = 0;
            $errors = [];
            $importedRateIds = [];

            foreach ($request->rates as $rateData) {
                try {
                    $result = $this->importRate($rateData, $gateway, $request->valid_from);
                    $importedRateIds[] = $result['rate']->id;
                    if ($result['action'] === 'created') {
                        $imported++;
                    } else {
                        $updated++;
                    }
                } catch (\Exception $e) {
                    $errors[] = [
                        'mcc' => $rateData['mcc'],
                        'mnc' => $rateData['mnc'],
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $gateway->update(['last_rate_update' => now()]);

            RateCardAuditLog::logAction('rate_uploaded', [
                'supplier_id' => $gateway->supplier_id,
                'gateway_id' => $gateway->id,
                'new_value' => [
                    'imported' => $imported,
                    'updated' => $updated,
                    'errors' => count($errors),
                ],
            ]);

            DB::commit();

            $importedRates = RateCard::whereIn('id', $importedRateIds)
                ->select('id', 'mcc', 'mnc', 'country_name', 'country_iso', 'network_name', 'billing_method', 'native_rate', 'currency', 'product_type')
                ->orderBy('country_name')
                ->orderBy('network_name')
                ->get();

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} rates and updated {$updated} rates",
                'imported' => $imported,
                'updated' => $updated,
                'errors' => $errors,
                'importedRates' => $importedRates,
                'gatewayBillingMethod' => $gateway->billing_method,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error processing upload: ' . $e->getMessage(),
            ], 500);
        }
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
                $value = $cell->getValue();
                if ($value instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                    $value = $value->getPlainText();
                }
                if (is_float($value) && \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                    try {
                        $value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                    } catch (\Exception $e) {
                    }
                }
                $rowData[] = $value !== null ? (string) $value : '';
            }

            $rows[] = $rowData;
        }

        if (empty($rows)) {
            throw new \RuntimeException('The file appears to be empty.');
        }

        return $rows;
    }

    private function importRate($rateData, $gateway, $validFrom)
    {
        // Find MCC/MNC
        $mccMnc = MccMnc::byMccMnc($rateData['mcc'], $rateData['mnc'])->firstOrFail();

        // Get FX rate if needed
        $currency = $rateData['currency'] ?? $gateway->currency;
        $nativeRate = $rateData['rate'];

        if ($currency !== 'GBP') {
            $fxRate = FxRate::getRate($currency, 'GBP');
            if (!$fxRate) {
                throw new \Exception("FX rate not found for {$currency}");
            }
            $gbpRate = $nativeRate * $fxRate;
            $fxTimestamp = now();
        } else {
            $fxRate = 1.0;
            $gbpRate = $nativeRate;
            $fxTimestamp = now();
        }

        // Check for existing active rate
        $existingRate = RateCard::where('gateway_id', $gateway->id)
            ->where('mcc', $rateData['mcc'])
            ->where('mnc', $rateData['mnc'])
            ->where('product_type', $rateData['product_type'] ?? 'SMS')
            ->where('active', true)
            ->first();

        if ($existingRate) {
            // Create new version
            $newRate = $existingRate->createNewVersion([
                'billing_method' => $gateway->billing_method,
                'currency' => $currency,
                'native_rate' => $nativeRate,
                'gbp_rate' => $gbpRate,
                'fx_rate' => $fxRate,
                'fx_timestamp' => $fxTimestamp,
                'valid_from' => $validFrom,
                'active' => true,
            ], auth()->user()->name ?? 'System', 'Rate upload');

            RateCardAuditLog::logAction('rate_updated', [
                'rate_card_id' => $newRate->id,
                'supplier_id' => $gateway->supplier_id,
                'gateway_id' => $gateway->id,
                'old_value' => $existingRate->toArray(),
                'new_value' => $newRate->toArray(),
            ]);

            return ['action' => 'updated', 'rate' => $newRate];
        } else {
            // Create new rate
            $newRate = RateCard::create([
                'supplier_id' => $gateway->supplier_id,
                'gateway_id' => $gateway->id,
                'mcc_mnc_id' => $mccMnc->id,
                'mcc' => $rateData['mcc'],
                'mnc' => $rateData['mnc'],
                'country_name' => $mccMnc->country_name,
                'country_iso' => $mccMnc->country_iso,
                'network_name' => $mccMnc->network_name,
                'product_type' => $rateData['product_type'] ?? 'SMS',
                'billing_method' => $gateway->billing_method,
                'currency' => $currency,
                'native_rate' => $nativeRate,
                'gbp_rate' => $gbpRate,
                'fx_rate' => $fxRate,
                'fx_timestamp' => $fxTimestamp,
                'valid_from' => $validFrom,
                'active' => true,
                'version' => 1,
                'created_by' => auth()->user()->name ?? 'System',
            ]);

            RateCardAuditLog::logAction('rate_created', [
                'rate_card_id' => $newRate->id,
                'supplier_id' => $gateway->supplier_id,
                'gateway_id' => $gateway->id,
                'new_value' => $newRate->toArray(),
            ]);

            return ['action' => 'created', 'rate' => $newRate];
        }
    }

    public function show(RateCard $rateCard)
    {
        $rateCard->load(['supplier', 'gateway']);

        return response()->json([
            'id' => $rateCard->id,
            'network_name' => $rateCard->network_name,
            'country_name' => $rateCard->country_name,
            'country_iso' => $rateCard->country_iso,
            'mcc' => $rateCard->mcc,
            'mnc' => $rateCard->mnc,
            'product_type' => $rateCard->product_type,
            'native_rate' => $rateCard->native_rate,
            'gbp_rate' => $rateCard->gbp_rate,
            'currency' => $rateCard->currency,
            'billing_method' => $rateCard->billing_method,
            'supplier_name' => $rateCard->supplier->name ?? '',
            'gateway_name' => $rateCard->gateway->name ?? '',
            'valid_from' => $rateCard->valid_from,
            'valid_to' => $rateCard->valid_to,
        ]);
    }

    public function update(Request $request, RateCard $rateCard)
    {
        $validated = $request->validate([
            'native_rate' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
            'reason' => 'required|string',
            'billing_method' => 'nullable|in:submitted,delivered',
        ]);

        // Recalculate GBP rate
        if ($rateCard->currency !== 'GBP') {
            $fxRate = FxRate::getRate($rateCard->currency, 'GBP');
            $validated['gbp_rate'] = $validated['native_rate'] * $fxRate;
            $validated['fx_rate'] = $fxRate;
            $validated['fx_timestamp'] = now();
        } else {
            $validated['gbp_rate'] = $validated['native_rate'];
            $validated['fx_rate'] = 1.0;
            $validated['fx_timestamp'] = now();
        }

        // Create new version
        $newRate = $rateCard->createNewVersion(
            $validated,
            auth()->user()->name ?? 'System',
            $validated['reason']
        );

        return response()->json([
            'success' => true,
            'message' => 'Rate updated successfully',
            'rate' => $newRate,
        ]);
    }

    public function updateBillingMethods(Request $request)
    {
        $request->validate([
            'gateway_id' => 'required|exists:gateways,id',
            'updates' => 'required|array',
            'updates.*.id' => 'required|exists:rate_cards,id',
            'updates.*.billing_method' => 'required|in:submitted,delivered',
        ]);

        $gatewayId = $request->gateway_id;
        $updatedCount = 0;
        $auditEntries = [];

        DB::beginTransaction();

        try {
            foreach ($request->updates as $update) {
                $rateCard = RateCard::where('id', $update['id'])
                    ->where('gateway_id', $gatewayId)
                    ->firstOrFail();
                $oldMethod = $rateCard->billing_method;
                $newMethod = $update['billing_method'];

                if ($oldMethod !== $newMethod) {
                    $rateCard->update(['billing_method' => $newMethod]);
                    $updatedCount++;

                    $auditEntries[] = [
                        'rate_card_id' => $rateCard->id,
                        'mcc' => $rateCard->mcc,
                        'mnc' => $rateCard->mnc,
                        'network_name' => $rateCard->network_name,
                        'old_method' => $oldMethod,
                        'new_method' => $newMethod,
                    ];
                }
            }

            if ($updatedCount > 0) {
                $gateway = Gateway::find($gatewayId);
                RateCardAuditLog::logAction('billing_method_changed', [
                    'gateway_id' => $gatewayId,
                    'supplier_id' => $gateway ? $gateway->supplier_id : null,
                    'updated_count' => $updatedCount,
                    'changes' => $auditEntries,
                    'updated_by' => auth()->user()->name ?? 'System',
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$updatedCount} billing method(s) updated",
                'updatedCount' => $updatedCount,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update billing methods: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function history(RateCard $rateCard)
    {
        $history = collect([$rateCard]);
        $current = $rateCard;

        while ($current->previous_version_id) {
            $current = RateCard::withTrashed()->find($current->previous_version_id);
            if ($current) {
                $history->push($current);
            }
        }

        return response()->json([
            'success' => true,
            'history' => $history,
        ]);
    }
}
