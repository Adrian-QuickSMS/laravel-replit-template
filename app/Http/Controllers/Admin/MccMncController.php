<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MccMnc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MccMncController extends Controller
{
    public function index(Request $request)
    {
        $query = MccMnc::orderBy('country_name')->orderBy('network_name');

        if ($request->filled('country')) {
            $query->where('country_iso', $request->country);
        }
        if ($request->filled('prefix')) {
            $query->where('country_prefix', $request->prefix);
        }
        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('network_name', 'ilike', "%{$search}%")
                  ->orWhere('mcc', 'like', "%{$search}%")
                  ->orWhere('mnc', 'like', "%{$search}%")
                  ->orWhere('country_name', 'ilike', "%{$search}%");
            });
        }

        $mccMncList = $query->paginate(50)->appends($request->query());

        $countries = MccMnc::select('country_iso', 'country_name')
            ->distinct()
            ->orderBy('country_name')
            ->get();

        return view('admin.supplier-management.mcc-mnc', compact('mccMncList', 'countries'));
    }

    public function show(MccMnc $mccMnc)
    {
        $siblings = MccMnc::where('network_name', $mccMnc->network_name)
            ->where('country_iso', $mccMnc->country_iso)
            ->orderBy('mcc')
            ->orderBy('mnc')
            ->get(['id', 'mcc', 'mnc', 'active']);

        $data = $mccMnc->toArray();
        $data['siblings'] = $siblings;

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'country_name' => 'required|string|max:255',
            'country_iso' => 'required|string|size:2',
            'network_name' => 'required|string|max:255',
            'country_prefix' => 'nullable|string|max:10',
            'entries' => 'nullable|array|min:1',
            'entries.*.mcc' => 'required_with:entries|string|max:3',
            'entries.*.mnc' => 'required_with:entries|string|max:3',
            'mcc' => 'required_without:entries|string|max:3',
            'mnc' => 'required_without:entries|string|max:3',
        ]);

        $countryName = $request->input('country_name');
        $countryIso = strtoupper($request->input('country_iso'));
        $networkName = $request->input('network_name');
        $countryPrefix = $request->input('country_prefix', '');

        if (empty($countryPrefix)) {
            $countryPrefix = self::isoToDialingCode($countryIso) ?? '';
        }

        $entries = $request->input('entries');

        if (!$entries || !is_array($entries)) {
            $mcc = $request->input('mcc');
            $mnc = $request->input('mnc');
            if (!$mcc || !$mnc) {
                $errorMsg = 'Please provide at least one MCC/MNC pair.';
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return redirect()->route('admin.mcc-mnc.index')->with('error', $errorMsg);
            }
            $entries = [['mcc' => $mcc, 'mnc' => $mnc]];
        }

        $created = [];
        $skipped = [];
        $errors = [];

        foreach ($entries as $index => $entry) {
            $rawMcc = trim($entry['mcc'] ?? '');
            $rawMnc = trim($entry['mnc'] ?? '');

            if (empty($rawMcc) || empty($rawMnc)) {
                $errors[] = "Row " . ($index + 1) . ": MCC and MNC are required.";
                continue;
            }

            $rawMcc = ltrim($rawMcc, "'");
            $rawMnc = ltrim($rawMnc, "'");

            if (!ctype_digit($rawMcc)) {
                $errors[] = "Row " . ($index + 1) . ": MCC must be numeric (got: {$rawMcc}).";
                continue;
            }
            if (!ctype_digit($rawMnc)) {
                $errors[] = "Row " . ($index + 1) . ": MNC must be numeric (got: {$rawMnc}).";
                continue;
            }

            $mcc = str_pad($rawMcc, 3, '0', STR_PAD_LEFT);
            $mnc = strlen($rawMnc) < 2 ? str_pad($rawMnc, 2, '0', STR_PAD_LEFT) : $rawMnc;

            $existing = MccMnc::where('mcc', $mcc)->where('mnc', $mnc)->first();
            if ($existing) {
                $skipped[] = "{$mcc}/{$mnc} (already exists as \"{$existing->network_name}\")";
                continue;
            }

            MccMnc::create([
                'mcc' => $mcc,
                'mnc' => $mnc,
                'country_name' => $countryName,
                'country_iso' => $countryIso,
                'network_name' => $networkName,
                'country_prefix' => $countryPrefix,
                'active' => true,
            ]);
            $created[] = "{$mcc}/{$mnc}";
        }

        if (count($errors) > 0 && count($created) === 0) {
            $errorMsg = 'Validation failed: ' . implode('; ', $errors);
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->route('admin.mcc-mnc.index')->with('error', $errorMsg);
        }

        $message = '';
        if (count($created) > 0) {
            $message = count($created) . ' MCC/MNC ' . (count($created) === 1 ? 'entry' : 'entries') . ' created for ' . $networkName . '.';
        }
        if (count($skipped) > 0) {
            $message .= (strlen($message) > 0 ? ' ' : '') . count($skipped) . ' skipped (duplicates): ' . implode(', ', $skipped);
        }
        if (count($errors) > 0) {
            $message .= (strlen($message) > 0 ? ' ' : '') . count($errors) . ' had errors: ' . implode('; ', $errors);
        }

        if (count($created) === 0 && count($skipped) > 0) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'All entries already exist: ' . implode(', ', $skipped)], 422);
            }
            return redirect()->route('admin.mcc-mnc.index')->with('error', $message);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'created' => $created, 'skipped' => $skipped]);
        }
        return redirect()->route('admin.mcc-mnc.index')->with('success', $message);
    }

    public function update(Request $request, MccMnc $mccMnc)
    {
        $request->validate([
            'network_name' => 'required|string|max:255',
            'country_prefix' => 'nullable|string|max:10',
            'new_mncs' => 'nullable|array',
            'new_mncs.*.mcc' => 'required_with:new_mncs|string|max:3',
            'new_mncs.*.mnc' => 'required_with:new_mncs|string|max:3',
        ]);

        $siblings = MccMnc::where('network_name', $mccMnc->network_name)
            ->where('country_iso', $mccMnc->country_iso)
            ->get();

        foreach ($siblings as $sibling) {
            $sibling->update([
                'network_name' => $request->input('network_name'),
                'country_prefix' => $request->input('country_prefix'),
            ]);
        }

        $newMncs = $request->input('new_mncs', []);
        $created = [];
        $skipped = [];

        foreach ($newMncs as $entry) {
            $rawMcc = ltrim(trim($entry['mcc'] ?? ''), "'");
            $rawMnc = ltrim(trim($entry['mnc'] ?? ''), "'");

            if (empty($rawMcc) || empty($rawMnc)) continue;
            if (!ctype_digit($rawMcc) || !ctype_digit($rawMnc)) continue;

            $mcc = str_pad($rawMcc, 3, '0', STR_PAD_LEFT);
            $mnc = strlen($rawMnc) < 2 ? str_pad($rawMnc, 2, '0', STR_PAD_LEFT) : $rawMnc;

            $existing = MccMnc::where('mcc', $mcc)->where('mnc', $mnc)->first();
            if ($existing) {
                $skipped[] = "{$mcc}/{$mnc}";
                continue;
            }

            MccMnc::create([
                'mcc' => $mcc,
                'mnc' => $mnc,
                'country_name' => $mccMnc->country_name,
                'country_iso' => $mccMnc->country_iso,
                'network_name' => $request->input('network_name'),
                'country_prefix' => $request->input('country_prefix'),
                'active' => true,
            ]);
            $created[] = "{$mcc}/{$mnc}";
        }

        $message = 'Network updated successfully.';
        if (count($created) > 0) {
            $message .= ' ' . count($created) . ' new MCC/MNC ' . (count($created) === 1 ? 'entry' : 'entries') . ' added.';
        }
        if (count($skipped) > 0) {
            $message .= ' ' . count($skipped) . ' skipped (already exist): ' . implode(', ', $skipped) . '.';
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'created' => $created, 'skipped' => $skipped]);
        }

        return redirect()->route('admin.mcc-mnc.index')->with('success', $message);
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
        $countryPrefixCol = isset($mapping['country_prefix']) && $mapping['country_prefix'] !== '' && $mapping['country_prefix'] !== null
            ? (int) $mapping['country_prefix']
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
                $countryPrefix = ($countryPrefixCol !== null)
                    ? preg_replace('/[^0-9]/', '', trim($row[$countryPrefixCol] ?? ''))
                    : '';

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
                    if (ctype_digit($countryIso)) {
                        $resolved = self::dialingCodeToIso($countryIso);
                        if ($resolved) {
                            $countryIso = $resolved;
                        } else {
                            $errors[] = ['row' => $rowNum, 'error' => "Unknown dialing code: {$countryIso}"];
                            continue;
                        }
                    } elseif (strlen($countryIso) === 3 && ctype_alpha($countryIso)) {
                        $resolved = self::iso3ToIso2($countryIso);
                        if ($resolved) {
                            $countryIso = $resolved;
                        } else {
                            $errors[] = ['row' => $rowNum, 'error' => "Unknown ISO3 code: {$countryIso}"];
                            continue;
                        }
                    } else {
                        $errors[] = ['row' => $rowNum, 'error' => "Invalid country ISO: {$countryIso}"];
                        continue;
                    }
                }

                if (empty($countryPrefix)) {
                    $countryPrefix = self::isoToDialingCode($countryIso) ?? '';
                }

                $existing = MccMnc::where('mcc', $mcc)->where('mnc', $mnc)->first();
                if ($existing) {
                    $existing->update([
                        'country_name' => $countryName,
                        'country_iso' => $countryIso,
                        'network_name' => $networkName,
                        'country_prefix' => $countryPrefix,
                    ]);
                    $updated++;
                } else {
                    MccMnc::create([
                        'mcc' => $mcc,
                        'mnc' => $mnc,
                        'country_name' => $countryName,
                        'country_iso' => $countryIso,
                        'network_name' => $networkName,
                        'country_prefix' => $countryPrefix,
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

    private static function dialingCodeToIso($code)
    {
        $map = [
            '1'=>'US','7'=>'RU','20'=>'EG','27'=>'ZA','30'=>'GR','31'=>'NL','32'=>'BE','33'=>'FR',
            '34'=>'ES','36'=>'HU','39'=>'IT','40'=>'RO','41'=>'CH','43'=>'AT','44'=>'GB','45'=>'DK',
            '46'=>'SE','47'=>'NO','48'=>'PL','49'=>'DE','51'=>'PE','52'=>'MX','53'=>'CU','54'=>'AR',
            '55'=>'BR','56'=>'CL','57'=>'CO','58'=>'VE','60'=>'MY','61'=>'AU','62'=>'ID','63'=>'PH',
            '64'=>'NZ','65'=>'SG','66'=>'TH','81'=>'JP','82'=>'KR','84'=>'VN','86'=>'CN','90'=>'TR',
            '91'=>'IN','92'=>'PK','93'=>'AF','94'=>'LK','95'=>'MM','98'=>'IR',
            '211'=>'SS','212'=>'MA','213'=>'DZ','216'=>'TN','218'=>'LY','220'=>'GM','221'=>'SN',
            '222'=>'MR','223'=>'ML','224'=>'GN','225'=>'CI','226'=>'BF','227'=>'NE','228'=>'TG',
            '229'=>'BJ','230'=>'MU','231'=>'LR','232'=>'SL','233'=>'GH','234'=>'NG','235'=>'TD',
            '236'=>'CF','237'=>'CM','238'=>'CV','239'=>'ST','240'=>'GQ','241'=>'GA','242'=>'CG',
            '243'=>'CD','244'=>'AO','245'=>'GW','246'=>'IO','247'=>'AC','248'=>'SC','249'=>'SD',
            '250'=>'RW','251'=>'ET','252'=>'SO','253'=>'DJ','254'=>'KE','255'=>'TZ','256'=>'UG',
            '257'=>'BI','258'=>'MZ','260'=>'ZM','261'=>'MG','262'=>'RE','263'=>'ZW','264'=>'NA',
            '265'=>'MW','266'=>'LS','267'=>'BW','268'=>'SZ','269'=>'KM',
            '290'=>'SH','291'=>'ER','297'=>'AW','298'=>'FO','299'=>'GL',
            '350'=>'GI','351'=>'PT','352'=>'LU','353'=>'IE','354'=>'IS','355'=>'AL','356'=>'MT',
            '357'=>'CY','358'=>'FI','359'=>'BG','370'=>'LT','371'=>'LV','372'=>'EE','373'=>'MD',
            '374'=>'AM','375'=>'BY','376'=>'AD','377'=>'MC','378'=>'SM','380'=>'UA','381'=>'RS',
            '382'=>'ME','383'=>'XK','385'=>'HR','386'=>'SI','387'=>'BA','389'=>'MK',
            '420'=>'CZ','421'=>'SK','423'=>'LI',
            '500'=>'FK','501'=>'BZ','502'=>'GT','503'=>'SV','504'=>'HN','505'=>'NI','506'=>'CR',
            '507'=>'PA','508'=>'PM','509'=>'HT',
            '590'=>'GP','591'=>'BO','592'=>'GY','593'=>'EC','594'=>'GF','595'=>'PY','596'=>'MQ',
            '597'=>'SR','598'=>'UY','599'=>'CW',
            '670'=>'TL','672'=>'NF','673'=>'BN','674'=>'NR','675'=>'PG','676'=>'TO','677'=>'SB',
            '678'=>'VU','679'=>'FJ','680'=>'PW','681'=>'WF','682'=>'CK','683'=>'NU','685'=>'WS',
            '686'=>'KI','687'=>'NC','688'=>'TV','689'=>'PF','690'=>'TK','691'=>'FM','692'=>'MH',
            '850'=>'KP','852'=>'HK','853'=>'MO','855'=>'KH','856'=>'LA',
            '880'=>'BD','886'=>'TW',
            '960'=>'MV','961'=>'LB','962'=>'JO','963'=>'SY','964'=>'IQ','965'=>'KW','966'=>'SA',
            '967'=>'YE','968'=>'OM','970'=>'PS','971'=>'AE','972'=>'IL','973'=>'BH','974'=>'QA',
            '975'=>'BT','976'=>'MN','977'=>'NP','992'=>'TJ','993'=>'TM','994'=>'AZ','995'=>'GE',
            '996'=>'KG','998'=>'UZ',
        ];
        return $map[$code] ?? null;
    }

    private static function isoToDialingCode($iso)
    {
        $map = [
            'US'=>'1','RU'=>'7','EG'=>'20','ZA'=>'27','GR'=>'30','NL'=>'31','BE'=>'32','FR'=>'33',
            'ES'=>'34','HU'=>'36','IT'=>'39','RO'=>'40','CH'=>'41','AT'=>'43','GB'=>'44','DK'=>'45',
            'SE'=>'46','NO'=>'47','PL'=>'48','DE'=>'49','PE'=>'51','MX'=>'52','CU'=>'53','AR'=>'54',
            'BR'=>'55','CL'=>'56','CO'=>'57','VE'=>'58','MY'=>'60','AU'=>'61','ID'=>'62','PH'=>'63',
            'NZ'=>'64','SG'=>'65','TH'=>'66','JP'=>'81','KR'=>'82','VN'=>'84','CN'=>'86','TR'=>'90',
            'IN'=>'91','PK'=>'92','AF'=>'93','LK'=>'94','MM'=>'95','IR'=>'98',
            'SS'=>'211','MA'=>'212','DZ'=>'213','TN'=>'216','LY'=>'218','GM'=>'220','SN'=>'221',
            'MR'=>'222','ML'=>'223','GN'=>'224','CI'=>'225','BF'=>'226','NE'=>'227','TG'=>'228',
            'BJ'=>'229','MU'=>'230','LR'=>'231','SL'=>'232','GH'=>'233','NG'=>'234','TD'=>'235',
            'CF'=>'236','CM'=>'237','CV'=>'238','ST'=>'239','GQ'=>'240','GA'=>'241','CG'=>'242',
            'CD'=>'243','AO'=>'244','GW'=>'245','IO'=>'246','SC'=>'248','SD'=>'249',
            'RW'=>'250','ET'=>'251','SO'=>'252','DJ'=>'253','KE'=>'254','TZ'=>'255','UG'=>'256',
            'BI'=>'257','MZ'=>'258','ZM'=>'260','MG'=>'261','RE'=>'262','ZW'=>'263','NA'=>'264',
            'MW'=>'265','LS'=>'266','BW'=>'267','SZ'=>'268','KM'=>'269',
            'SH'=>'290','ER'=>'291','AW'=>'297','FO'=>'298','GL'=>'299',
            'GI'=>'350','PT'=>'351','LU'=>'352','IE'=>'353','IS'=>'354','AL'=>'355','MT'=>'356',
            'CY'=>'357','FI'=>'358','BG'=>'359','LT'=>'370','LV'=>'371','EE'=>'372','MD'=>'373',
            'AM'=>'374','BY'=>'375','AD'=>'376','MC'=>'377','SM'=>'378','UA'=>'380','RS'=>'381',
            'ME'=>'382','XK'=>'383','HR'=>'385','SI'=>'386','BA'=>'387','MK'=>'389',
            'CZ'=>'420','SK'=>'421','LI'=>'423',
            'FK'=>'500','BZ'=>'501','GT'=>'502','SV'=>'503','HN'=>'504','NI'=>'505','CR'=>'506',
            'PA'=>'507','PM'=>'508','HT'=>'509',
            'GP'=>'590','BO'=>'591','GY'=>'592','EC'=>'593','GF'=>'594','PY'=>'595','MQ'=>'596',
            'SR'=>'597','UY'=>'598','CW'=>'599',
            'TL'=>'670','NF'=>'672','BN'=>'673','NR'=>'674','PG'=>'675','TO'=>'676','SB'=>'677',
            'VU'=>'678','FJ'=>'679','PW'=>'680','WF'=>'681','CK'=>'682','NU'=>'683','WS'=>'685',
            'KI'=>'686','NC'=>'687','TV'=>'688','PF'=>'689','TK'=>'690','FM'=>'691','MH'=>'692',
            'KP'=>'850','HK'=>'852','MO'=>'853','KH'=>'855','LA'=>'856',
            'BD'=>'880','TW'=>'886',
            'MV'=>'960','LB'=>'961','JO'=>'962','SY'=>'963','IQ'=>'964','KW'=>'965','SA'=>'966',
            'YE'=>'967','OM'=>'968','PS'=>'970','AE'=>'971','IL'=>'972','BH'=>'973','QA'=>'974',
            'BT'=>'975','MN'=>'976','NP'=>'977','TJ'=>'992','TM'=>'993','AZ'=>'994','GE'=>'995',
            'KG'=>'996','UZ'=>'998',
            'CA'=>'1','PR'=>'1','GU'=>'1','BS'=>'1','GD'=>'1','IM'=>'44','JE'=>'44',
        ];
        return $map[$iso] ?? null;
    }

    private static function iso3ToIso2($iso3)
    {
        $map = [
            'AFG'=>'AF','ALB'=>'AL','DZA'=>'DZ','AND'=>'AD','AGO'=>'AO','ARG'=>'AR','ARM'=>'AM',
            'AUS'=>'AU','AUT'=>'AT','AZE'=>'AZ','BHS'=>'BS','BHR'=>'BH','BGD'=>'BD','BRB'=>'BB',
            'BLR'=>'BY','BEL'=>'BE','BLZ'=>'BZ','BEN'=>'BJ','BTN'=>'BT','BOL'=>'BO','BIH'=>'BA',
            'BWA'=>'BW','BRA'=>'BR','BRN'=>'BN','BGR'=>'BG','BFA'=>'BF','BDI'=>'BI','KHM'=>'KH',
            'CMR'=>'CM','CAN'=>'CA','CPV'=>'CV','CAF'=>'CF','TCD'=>'TD','CHL'=>'CL','CHN'=>'CN',
            'COL'=>'CO','COM'=>'KM','COG'=>'CG','COD'=>'CD','CRI'=>'CR','CIV'=>'CI','HRV'=>'HR',
            'CUB'=>'CU','CYP'=>'CY','CZE'=>'CZ','DNK'=>'DK','DJI'=>'DJ','DOM'=>'DO','ECU'=>'EC',
            'EGY'=>'EG','SLV'=>'SV','GNQ'=>'GQ','ERI'=>'ER','EST'=>'EE','ETH'=>'ET','FJI'=>'FJ',
            'FIN'=>'FI','FRA'=>'FR','GAB'=>'GA','GMB'=>'GM','GEO'=>'GE','DEU'=>'DE','GHA'=>'GH',
            'GRC'=>'GR','GTM'=>'GT','GIN'=>'GN','GNB'=>'GW','GUY'=>'GY','HTI'=>'HT','HND'=>'HN',
            'HKG'=>'HK','HUN'=>'HU','ISL'=>'IS','IND'=>'IN','IDN'=>'ID','IRN'=>'IR','IRQ'=>'IQ',
            'IRL'=>'IE','ISR'=>'IL','ITA'=>'IT','JAM'=>'JM','JPN'=>'JP','JOR'=>'JO','KAZ'=>'KZ',
            'KEN'=>'KE','KIR'=>'KI','PRK'=>'KP','KOR'=>'KR','KWT'=>'KW','KGZ'=>'KG','LAO'=>'LA',
            'LVA'=>'LV','LBN'=>'LB','LSO'=>'LS','LBR'=>'LR','LBY'=>'LY','LIE'=>'LI','LTU'=>'LT',
            'LUX'=>'LU','MAC'=>'MO','MKD'=>'MK','MDG'=>'MG','MWI'=>'MW','MYS'=>'MY','MDV'=>'MV',
            'MLI'=>'ML','MLT'=>'MT','MHL'=>'MH','MRT'=>'MR','MUS'=>'MU','MEX'=>'MX','FSM'=>'FM',
            'MDA'=>'MD','MCO'=>'MC','MNG'=>'MN','MNE'=>'ME','MAR'=>'MA','MOZ'=>'MZ','MMR'=>'MM',
            'NAM'=>'NA','NRU'=>'NR','NPL'=>'NP','NLD'=>'NL','NZL'=>'NZ','NIC'=>'NI','NER'=>'NE',
            'NGA'=>'NG','NOR'=>'NO','OMN'=>'OM','PAK'=>'PK','PLW'=>'PW','PSE'=>'PS','PAN'=>'PA',
            'PNG'=>'PG','PRY'=>'PY','PER'=>'PE','PHL'=>'PH','POL'=>'PL','PRT'=>'PT','QAT'=>'QA',
            'ROU'=>'RO','RUS'=>'RU','RWA'=>'RW','SAU'=>'SA','SEN'=>'SN','SRB'=>'RS','SYC'=>'SC',
            'SLE'=>'SL','SGP'=>'SG','SVK'=>'SK','SVN'=>'SI','SLB'=>'SB','SOM'=>'SO','ZAF'=>'ZA',
            'SSD'=>'SS','ESP'=>'ES','LKA'=>'LK','SDN'=>'SD','SUR'=>'SR','SWZ'=>'SZ','SWE'=>'SE',
            'CHE'=>'CH','SYR'=>'SY','TWN'=>'TW','TJK'=>'TJ','TZA'=>'TZ','THA'=>'TH','TLS'=>'TL',
            'TGO'=>'TG','TON'=>'TO','TTO'=>'TT','TUN'=>'TN','TUR'=>'TR','TKM'=>'TM','TUV'=>'TV',
            'UGA'=>'UG','UKR'=>'UA','ARE'=>'AE','GBR'=>'GB','USA'=>'US','URY'=>'UY','UZB'=>'UZ',
            'VUT'=>'VU','VEN'=>'VE','VNM'=>'VN','YEM'=>'YE','ZMB'=>'ZM','ZWE'=>'ZW',
        ];
        return $map[$iso3] ?? null;
    }
}
