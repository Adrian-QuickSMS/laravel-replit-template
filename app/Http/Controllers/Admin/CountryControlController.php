<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CountryControl;
use App\Models\CountryControlOverride;
use Illuminate\Http\Request;

class CountryControlController extends Controller
{
    public function index()
    {
        $countries = CountryControl::withCount('overrides')
            ->orderBy('country_name')
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'code' => $c->country_iso,
                    'name' => $c->country_name,
                    'dialCode' => '+' . $c->country_prefix,
                    'status' => $c->default_status,
                    'risk' => $c->risk_level,
                    'overrides' => $c->overrides_count,
                    'networkCount' => $c->network_count,
                    'lastUpdated' => $c->updated_at ? $c->updated_at->format('Y-m-d H:i') : null,
                ];
            });

        return response()->json(['success' => true, 'countries' => $countries]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:country_controls,id',
            'default_status' => 'required|in:allowed,blocked,restricted',
        ]);

        $country = CountryControl::findOrFail($request->country_id);
        $country->update(['default_status' => $request->default_status]);

        return response()->json([
            'success' => true,
            'message' => "{$country->country_name} status updated to {$request->default_status}.",
        ]);
    }

    public function updateRisk(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:country_controls,id',
            'risk_level' => 'required|in:low,medium,high,critical',
        ]);

        $country = CountryControl::findOrFail($request->country_id);
        $country->update(['risk_level' => $request->risk_level]);

        return response()->json([
            'success' => true,
            'message' => "{$country->country_name} risk level updated to {$request->risk_level}.",
        ]);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'country_ids' => 'required|array',
            'country_ids.*' => 'exists:country_controls,id',
            'default_status' => 'required|in:allowed,blocked,restricted',
        ]);

        CountryControl::whereIn('id', $request->country_ids)
            ->update(['default_status' => $request->default_status]);

        $count = count($request->country_ids);

        return response()->json([
            'success' => true,
            'message' => "{$count} countries updated to {$request->default_status}.",
        ]);
    }

    public function getOverrides($countryId)
    {
        $overrides = CountryControlOverride::where('country_control_id', $countryId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'overrides' => $overrides]);
    }

    public function addOverride(Request $request)
    {
        $request->validate([
            'country_control_id' => 'required|exists:country_controls,id',
            'account_id' => 'required|integer',
            'override_status' => 'required|in:allowed,blocked',
            'reason' => 'nullable|string|max:500',
        ]);

        $existing = CountryControlOverride::where('country_control_id', $request->country_control_id)
            ->where('account_id', $request->account_id)
            ->first();

        if ($existing) {
            $existing->update([
                'override_status' => $request->override_status,
                'reason' => $request->reason,
                'created_by' => 'admin',
            ]);
            return response()->json(['success' => true, 'message' => 'Override updated.']);
        }

        CountryControlOverride::create([
            'country_control_id' => $request->country_control_id,
            'account_id' => $request->account_id,
            'override_status' => $request->override_status,
            'reason' => $request->reason,
            'created_by' => 'admin',
        ]);

        return response()->json(['success' => true, 'message' => 'Override added.']);
    }

    public function deleteOverride($overrideId)
    {
        $override = CountryControlOverride::findOrFail($overrideId);
        $override->delete();

        return response()->json(['success' => true, 'message' => 'Override removed.']);
    }
}
