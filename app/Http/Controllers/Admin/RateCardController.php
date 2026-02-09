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
use Illuminate\Support\Facades\Validator;
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

        $rateCards = $query->orderBy('country_name')
            ->orderBy('network_name')
            ->paginate(50);

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

    public function validateUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx',
            'gateway_id' => 'required|exists:gateways,id',
        ]);

        $file = $request->file('file');
        $gateway = Gateway::with('supplier')->findOrFail($request->gateway_id);

        // Parse file
        $data = $this->parseUploadFile($file);

        // Validate data
        $validationResults = $this->validateRateData($data, $gateway);

        return response()->json([
            'success' => true,
            'data' => $data,
            'validation' => $validationResults,
            'gateway' => $gateway,
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

            foreach ($request->rates as $rateData) {
                try {
                    $result = $this->importRate($rateData, $gateway, $request->valid_from);
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

            // Update gateway last rate update
            $gateway->update(['last_rate_update' => now()]);

            // Log action
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

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} rates and updated {$updated} rates",
                'imported' => $imported,
                'updated' => $updated,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error processing upload: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function parseUploadFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        $data = [];

        if ($extension === 'csv') {
            $handle = fopen($file->getRealPath(), 'r');
            $header = fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== false) {
                $data[] = array_combine($header, $row);
            }

            fclose($handle);
        } elseif ($extension === 'xlsx') {
            // Use PhpSpreadsheet for Excel files
            // Implementation would go here
        }

        return $data;
    }

    private function validateRateData($data, $gateway)
    {
        $errors = [];
        $warnings = [];
        $validRows = 0;

        foreach ($data as $index => $row) {
            $rowErrors = [];

            // Validate required fields
            if (empty($row['mcc'])) {
                $rowErrors[] = 'MCC is required';
            }

            if (empty($row['mnc'])) {
                $rowErrors[] = 'MNC is required';
            }

            if (empty($row['rate']) || !is_numeric($row['rate'])) {
                $rowErrors[] = 'Valid rate is required';
            }

            if (isset($row['rate']) && $row['rate'] < 0) {
                $rowErrors[] = 'Rate cannot be negative';
            }

            // Check if MCC/MNC exists in master table
            if (!empty($row['mcc']) && !empty($row['mnc'])) {
                $mccMnc = MccMnc::byMccMnc($row['mcc'], $row['mnc'])->first();
                if (!$mccMnc) {
                    $rowErrors[] = 'MCC/MNC not found in master reference';
                }
            }

            if (empty($rowErrors)) {
                $validRows++;
            } else {
                $errors[] = [
                    'row' => $index + 2, // +2 for header and 0-index
                    'errors' => $rowErrors,
                ];
            }
        }

        return [
            'valid' => count($errors) === 0,
            'total_rows' => count($data),
            'valid_rows' => $validRows,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
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

    public function update(Request $request, RateCard $rateCard)
    {
        $validated = $request->validate([
            'native_rate' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
            'reason' => 'required|string',
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
