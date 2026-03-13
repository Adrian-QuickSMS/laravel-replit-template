<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CountryControl;
use App\Models\SubAccountCountryPermission;
use App\Services\CountryPermissionCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubAccountCountryPermissionController extends Controller
{
    public function __construct(
        private CountryPermissionCacheService $cacheService
    ) {}

    /**
     * List all country permissions for a sub-account, showing effective permission
     * (sub-account override > account override > global default).
     */
    public function index(Request $request, string $subAccountId)
    {
        $subAccount = DB::table('sub_accounts')->where('id', $subAccountId)->first();
        if (!$subAccount) {
            return response()->json(['success' => false, 'error' => 'Sub-account not found.'], 404);
        }

        $permissions = $this->cacheService->getPermissionsForEntity($subAccount->account_id, $subAccountId);

        // Enrich with country details
        $countries = CountryControl::orderBy('country_name')->get();
        $subOverrides = SubAccountCountryPermission::where('sub_account_id', $subAccountId)
            ->pluck('permission_status', 'country_control_id');
        $accountOverrides = DB::table('country_control_overrides')
            ->where('account_id', $subAccount->account_id)
            ->pluck('override_status', 'country_control_id');

        $result = $countries->map(function ($country) use ($permissions, $subOverrides, $accountOverrides) {
            return [
                'country_control_id' => $country->id,
                'country_iso' => $country->country_iso,
                'country_name' => $country->country_name,
                'country_prefix' => $country->country_prefix,
                'global_default' => $country->default_status,
                'account_override' => $accountOverrides[$country->id] ?? null,
                'sub_account_override' => $subOverrides[$country->id] ?? null,
                'effective_permission' => $permissions[$country->country_iso] ?? 'blocked',
            ];
        });

        return response()->json([
            'success' => true,
            'sub_account_id' => $subAccountId,
            'data' => $result,
        ]);
    }

    /**
     * Set a sub-account country permission override.
     */
    public function setPermission(Request $request)
    {
        $request->validate([
            'sub_account_id' => 'required|string',
            'country_control_id' => 'required|exists:country_controls,id',
            'permission_status' => 'required|in:allowed,blocked',
            'reason' => 'nullable|string|max:500',
        ]);

        $subAccount = DB::table('sub_accounts')->where('id', $request->sub_account_id)->first();
        if (!$subAccount) {
            return response()->json(['success' => false, 'error' => 'Sub-account not found.'], 404);
        }

        $adminEmail = session('admin_auth.email', 'admin');

        $existing = SubAccountCountryPermission::where('sub_account_id', $request->sub_account_id)
            ->where('country_control_id', $request->country_control_id)
            ->first();

        if ($existing) {
            $existing->update([
                'permission_status' => $request->permission_status,
                'reason' => $request->reason,
                'created_by' => $adminEmail,
            ]);
        } else {
            SubAccountCountryPermission::create([
                'sub_account_id' => $request->sub_account_id,
                'country_control_id' => $request->country_control_id,
                'permission_status' => $request->permission_status,
                'reason' => $request->reason,
                'created_by' => $adminEmail,
            ]);
        }

        // Invalidate cache
        $this->cacheService->invalidateAccount($subAccount->account_id, $request->sub_account_id);

        $country = CountryControl::find($request->country_control_id);

        return response()->json([
            'success' => true,
            'message' => "Sub-account permission for {$country->country_name} set to {$request->permission_status}.",
        ]);
    }

    /**
     * Remove a sub-account country permission override (falls back to account/global).
     */
    public function removePermission(Request $request, int $permissionId)
    {
        $permission = SubAccountCountryPermission::findOrFail($permissionId);
        $subAccountId = $permission->sub_account_id;

        $subAccount = DB::table('sub_accounts')->where('id', $subAccountId)->first();
        $permission->delete();

        // Invalidate cache
        if ($subAccount) {
            $this->cacheService->invalidateAccount($subAccount->account_id, $subAccountId);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sub-account country permission override removed.',
        ]);
    }

    /**
     * Bulk-set permissions for a sub-account (e.g. restrict to specific countries only).
     */
    public function bulkSetPermissions(Request $request)
    {
        $request->validate([
            'sub_account_id' => 'required|string',
            'permissions' => 'required|array',
            'permissions.*.country_control_id' => 'required|exists:country_controls,id',
            'permissions.*.permission_status' => 'required|in:allowed,blocked',
            'reason' => 'nullable|string|max:500',
        ]);

        $subAccount = DB::table('sub_accounts')->where('id', $request->sub_account_id)->first();
        if (!$subAccount) {
            return response()->json(['success' => false, 'error' => 'Sub-account not found.'], 404);
        }

        $adminEmail = session('admin_auth.email', 'admin');

        DB::transaction(function () use ($request, $adminEmail) {
            foreach ($request->permissions as $perm) {
                SubAccountCountryPermission::updateOrCreate(
                    [
                        'sub_account_id' => $request->sub_account_id,
                        'country_control_id' => $perm['country_control_id'],
                    ],
                    [
                        'permission_status' => $perm['permission_status'],
                        'reason' => $request->reason,
                        'created_by' => $adminEmail,
                    ]
                );
            }
        });

        // Invalidate cache
        $this->cacheService->invalidateAccount($subAccount->account_id, $request->sub_account_id);

        return response()->json([
            'success' => true,
            'message' => count($request->permissions) . ' sub-account country permissions updated.',
        ]);
    }
}
