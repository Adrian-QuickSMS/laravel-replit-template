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
        if ($request->filled('type')) {
            $query->where('network_type', $request->type);
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

        $existing = MccMnc::where('mcc', $validated['mcc'])
            ->where('mnc', $validated['mnc'])
            ->first();

        if ($existing) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "MCC {$validated['mcc']} / MNC {$validated['mnc']} already exists as \"{$existing->network_name}\". Please use a different MNC code.",
                ], 422);
            }
            return redirect()->route('admin.mcc-mnc.index')
                ->with('error', "MCC {$validated['mcc']} / MNC {$validated['mnc']} already exists as \"{$existing->network_name}\". Please use a different MNC code.");
        }

        $validated['active'] = true;
        $network = MccMnc::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Network created successfully.', 'id' => $network->id]);
        }
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
