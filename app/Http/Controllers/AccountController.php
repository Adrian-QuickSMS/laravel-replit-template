<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountSettings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Account Management Controller
 *
 * Handles account details, settings, team members
 *
 * SECURITY:
 * - All routes require authentication
 * - Tenant scoping enforced automatically
 * - Only owners/admins can update settings
 */
class AccountController extends Controller
{
    /**
     * Get account details
     *
     * GET /api/account
     */
    public function show(Request $request)
    {
        try {
            $user = $request->user();
            $account = $user->account;
            $settings = $account->settings;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'account' => $account->toPortalArray(),
                    'settings' => $settings ? $settings->toPortalArray() : null,
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get account error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred'
            ], 500);
        }
    }

    /**
     * Update account details
     *
     * PUT /api/account
     */
    public function update(Request $request)
    {
        try {
            $user = $request->user();

            // Only owners and admins can update account
            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only account owners and admins can update account details.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'company_name' => 'sometimes|required|string|max:255',
                'phone' => 'nullable|string|max:20',
                'address_line1' => 'nullable|string|max:255',
                'address_line2' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'postcode' => 'nullable|string|max:20',
                'country' => 'nullable|string|size:2',
                'vat_number' => 'nullable|string|max:50',
                'billing_email' => 'nullable|email|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $account = $user->account;
            $account->update($request->only([
                'company_name',
                'phone',
                'address_line1',
                'address_line2',
                'city',
                'postcode',
                'country',
                'vat_number',
                'billing_email',
            ]));

            // TODO: Sync to HubSpot

            return response()->json([
                'status' => 'success',
                'message' => 'Account updated successfully',
                'data' => $account->toPortalArray()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Update account error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred'
            ], 500);
        }
    }

    /**
     * Update account settings
     *
     * PUT /api/account/settings
     */
    public function updateSettings(Request $request)
    {
        try {
            $user = $request->user();

            // Only owners and admins can update settings
            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only account owners and admins can update settings.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'notification_email_enabled' => 'sometimes|boolean',
                'notification_email_addresses' => 'sometimes|array',
                'notification_email_addresses.*' => 'email',
                'webhook_url_delivery' => 'nullable|url|max:255',
                'webhook_url_inbound' => 'nullable|url|max:255',
                'timezone' => 'sometimes|string|max:50',
                'currency' => 'sometimes|string|size:3',
                'session_timeout_minutes' => 'sometimes|integer|min:5|max:480',
                'require_mfa' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Call stored function to update settings (PostgreSQL uses SELECT, not CALL)
            DB::select('SELECT * FROM sp_update_account_settings(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $user->id,
                $user->tenant_id,
                $request->input('notification_email_enabled'),
                json_encode($request->input('notification_email_addresses')),
                $request->input('webhook_url_delivery'),
                $request->input('webhook_url_inbound'),
                $request->input('timezone'),
                $request->input('currency'),
                $request->input('session_timeout_minutes'),
                $request->input('require_mfa'),
            ]);

            // Refresh settings
            $settings = $user->account->settings()->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Settings updated successfully',
                'data' => $settings->toPortalArray()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Update settings error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get team members (users in account)
     *
     * GET /api/account/team
     */
    public function team(Request $request)
    {
        try {
            $user = $request->user();
            $users = $user->account->users()->get();

            return response()->json([
                'status' => 'success',
                'data' => $users->map(fn($u) => $u->toPortalArray())
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get team error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred'
            ], 500);
        }
    }

    /**
     * Invite team member
     *
     * POST /api/account/team/invite
     */
    public function inviteTeamMember(Request $request)
    {
        try {
            $user = $request->user();

            // Only owners and admins can invite
            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized. Only account owners and admins can invite team members.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255|unique:users,email',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'role' => 'required|in:admin,member,readonly',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Generate temporary password
            $tempPassword = bin2hex(random_bytes(16));

            // Create user
            $newUser = User::create([
                'tenant_id' => $user->tenant_id,
                'user_type' => 'customer',
                'email' => $request->email,
                'password' => $tempPassword, // Will be hashed automatically
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'role' => $request->role,
                'status' => 'active',
            ]);

            // TODO: Send invitation email with temp password

            return response()->json([
                'status' => 'success',
                'message' => 'Team member invited successfully',
                'data' => $newUser->toPortalArray()
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Invite team member error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred'
            ], 500);
        }
    }

    /**
     * Remove team member
     *
     * DELETE /api/account/team/{userId}
     */
    public function removeTeamMember(Request $request, $userId)
    {
        try {
            $user = $request->user();

            // Only owners and admins can remove
            if (!$user->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }

            // Cannot remove self
            if ($user->id === $userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You cannot remove yourself'
                ], 400);
            }

            // Find user (tenant scoping automatically applied)
            $teamMember = User::find($userId);

            if (!$teamMember) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Team member not found'
                ], 404);
            }

            // Cannot remove account owner
            if ($teamMember->isOwner()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot remove account owner'
                ], 400);
            }

            // Soft delete user
            $teamMember->update(['status' => 'inactive']);

            return response()->json([
                'status' => 'success',
                'message' => 'Team member removed successfully'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Remove team member error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred'
            ], 500);
        }
    }
}
