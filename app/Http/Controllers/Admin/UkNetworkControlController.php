<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MccMnc;
use App\Models\UkNetworkControl;
use App\Models\UkNetworkOverride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UkNetworkControlController extends Controller
{
    public function index(Request $request)
    {
        $networks = MccMnc::where('active', true)
            ->where(function ($q) {
                $q->whereIn('country_iso', ['GB', 'GG', 'JE', 'IM'])
                  ->orWhereIn('mcc', ['234', '235']);
            })
            ->orderBy('network_name')
            ->get();

        $controls = UkNetworkControl::all()->keyBy('mcc_mnc_id');

        $overrideCounts = UkNetworkOverride::select('mcc_mnc_id', DB::raw('COUNT(*) as count'))
            ->groupBy('mcc_mnc_id')
            ->pluck('count', 'mcc_mnc_id');

        $prefixCounts = DB::table('uk_prefixes')
            ->where('active', true)
            ->select('mcc_mnc_id', DB::raw('COUNT(*) as count'))
            ->groupBy('mcc_mnc_id')
            ->pluck('count', 'mcc_mnc_id');

        $result = $networks->map(function ($network) use ($controls, $overrideCounts, $prefixCounts) {
            $control = $controls->get($network->id);
            return [
                'id' => $network->id,
                'mcc' => $network->mcc,
                'mnc' => $network->mnc,
                'network_name' => $network->network_name,
                'country_name' => $network->country_name,
                'country_iso' => $network->country_iso,
                'default_status' => $control ? $control->default_status : 'allowed',
                'override_count' => $overrideCounts->get($network->id, 0),
                'prefix_count' => $prefixCounts->get($network->id, 0),
                'updated_at' => $control ? $control->updated_at->format('d-m-Y H:i') : null,
                'updated_by' => $control ? $control->updated_by : null,
            ];
        });

        return response()->json([
            'success' => true,
            'networks' => $result->values(),
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'mcc_mnc_id' => 'required|exists:mcc_mnc_master,id',
            'default_status' => 'required|in:allowed,blocked',
        ]);

        $control = UkNetworkControl::updateOrCreate(
            ['mcc_mnc_id' => $request->mcc_mnc_id],
            [
                'default_status' => $request->default_status,
                'updated_by' => auth()->user()->name ?? 'Admin',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Network default status updated.',
            'control' => $control,
        ]);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'mcc_mnc_ids' => 'required|array|min:1',
            'mcc_mnc_ids.*' => 'exists:mcc_mnc_master,id',
            'default_status' => 'required|in:allowed,blocked',
        ]);

        $updatedBy = auth()->user()->name ?? 'Admin';

        foreach ($request->mcc_mnc_ids as $id) {
            UkNetworkControl::updateOrCreate(
                ['mcc_mnc_id' => $id],
                [
                    'default_status' => $request->default_status,
                    'updated_by' => $updatedBy,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => count($request->mcc_mnc_ids) . ' network(s) updated to ' . $request->default_status . '.',
        ]);
    }

    public function getOverrides(Request $request, $mccMncId)
    {
        $overrides = UkNetworkOverride::where('mcc_mnc_id', $mccMncId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'overrides' => $overrides,
        ]);
    }

    public function addOverride(Request $request)
    {
        $request->validate([
            'mcc_mnc_id' => 'required|exists:mcc_mnc_master,id',
            'account_id' => 'required|integer',
            'override_status' => 'required|in:allowed,blocked',
            'reason' => 'nullable|string|max:500',
        ]);

        $existing = UkNetworkOverride::where('mcc_mnc_id', $request->mcc_mnc_id)
            ->where('account_id', $request->account_id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'An override already exists for this account and network. Please edit the existing override.',
            ], 422);
        }

        $override = UkNetworkOverride::create([
            'mcc_mnc_id' => $request->mcc_mnc_id,
            'account_id' => $request->account_id,
            'override_status' => $request->override_status,
            'reason' => $request->reason,
            'created_by' => auth()->user()->name ?? 'Admin',
            'updated_by' => auth()->user()->name ?? 'Admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Override added successfully.',
            'override' => $override,
        ]);
    }

    public function updateOverride(Request $request, UkNetworkOverride $override)
    {
        $request->validate([
            'override_status' => 'required|in:allowed,blocked',
            'reason' => 'nullable|string|max:500',
        ]);

        $override->update([
            'override_status' => $request->override_status,
            'reason' => $request->reason,
            'updated_by' => auth()->user()->name ?? 'Admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Override updated successfully.',
            'override' => $override,
        ]);
    }

    public function deleteOverride(UkNetworkOverride $override)
    {
        $override->delete();

        return response()->json([
            'success' => true,
            'message' => 'Override removed successfully.',
        ]);
    }
}
