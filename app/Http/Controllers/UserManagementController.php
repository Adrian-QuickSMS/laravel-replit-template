<?php

namespace App\Http\Controllers;

use App\Models\SubAccount;
use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * User Management Controller
 *
 * Handles user CRUD, invitations, role/permission management,
 * and user-level caps within the customer portal.
 *
 * SECURITY:
 * - All routes require authentication
 * - Tenant scoping enforced via User global scope
 * - Owner/admin required for user management
 * - Sub-account users can only manage users within their sub-account
 * - User caps validated against sub-account caps at save time
 */
class UserManagementController extends Controller
{
    /**
     * List users for the current tenant/sub-account.
     *
     * GET /api/users
     */
    public function index(Request $request)
    {
        try {
            $currentUser = $request->user();
            $query = User::query();

            // Sub-account scoping: non-admin sub-account users see only their sub-account
            if ($currentUser->sub_account_id && !$currentUser->isAdmin()) {
                $query->where('sub_account_id', $currentUser->sub_account_id);
            }

            // Optional filters
            if ($request->has('sub_account_id')) {
                if (!$currentUser->canViewSubAccount($request->input('sub_account_id'))) {
                    return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
                }
                $query->where('sub_account_id', $request->input('sub_account_id'));
            }

            if ($request->has('role')) {
                $query->where('role', $request->input('role'));
            }

            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'ilike', "%{$search}%")
                      ->orWhere('first_name', 'ilike', "%{$search}%")
                      ->orWhere('last_name', 'ilike', "%{$search}%");
                });
            }

            $users = $query->orderBy('first_name')
                ->paginate($request->integer('per_page', 50));

            return response()->json([
                'status' => 'success',
                'data' => $users->map(fn($u) => $u->toPortalArray()),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('List users error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Get a single user.
     *
     * GET /api/users/{id}
     */
    public function show(Request $request, string $id)
    {
        try {
            $currentUser = $request->user();
            $user = User::findOrFail($id);

            // Enforce visibility
            if ($user->sub_account_id && !$currentUser->canViewSubAccount($user->sub_account_id)) {
                return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => $user->toPortalArray(),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        } catch (\Exception $e) {
            Log::error('Show user error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Update a user's role, permissions, caps, or sender capability.
     *
     * PUT /api/users/{id}
     */
    public function update(Request $request, string $id)
    {
        try {
            $currentUser = $request->user();

            if (!$currentUser->canManageUsers()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $user = User::findOrFail($id);

            // Cannot modify the account owner unless you are the account owner
            if ($user->isAccountOwner() && !$currentUser->isAccountOwner()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot modify the account owner',
                ], 403);
            }

            // Sub-account users cannot manage users outside their sub-account
            if ($currentUser->sub_account_id && $user->sub_account_id !== $currentUser->sub_account_id) {
                return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'job_title' => 'nullable|string|max:255',
                'role' => 'sometimes|in:' . implode(',', User::ROLES),
                'sender_capability' => 'sometimes|in:advanced,restricted,none',
                'monthly_spending_cap' => 'nullable|numeric|min:0',
                'monthly_message_cap' => 'nullable|integer|min:0',
                'daily_send_limit' => 'nullable|integer|min:0',
                'permission_toggles' => 'nullable|array',
                'sub_account_id' => ['nullable', 'string', Rule::exists('sub_accounts', 'id')->where('account_id', $currentUser->tenant_id)],
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            // Cannot assign owner role (ownership is transferred, not assigned)
            if ($request->input('role') === User::ROLE_OWNER && !$user->isOwner()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Owner role cannot be directly assigned. Use ownership transfer.',
                ], 422);
            }

            $updateData = $request->only([
                'first_name', 'last_name', 'job_title', 'role',
                'sender_capability', 'monthly_spending_cap', 'monthly_message_cap',
                'daily_send_limit', 'permission_toggles', 'sub_account_id',
            ]);

            $user->fill($updateData);

            // Enforce validation: user caps must not exceed sub-account caps
            $user->validateCapsAgainstSubAccount();

            $user->save();

            Log::info('User updated', [
                'user_id' => $id,
                'updated_by' => $currentUser->id,
                'changes' => array_keys($updateData),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User updated',
                'data' => $user->fresh()->toPortalArray(),
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        } catch (\Exception $e) {
            Log::error('Update user error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Suspend a user.
     *
     * PUT /api/users/{id}/suspend
     */
    public function suspend(Request $request, string $id)
    {
        try {
            $currentUser = $request->user();
            if (!$currentUser->canManageUsers()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $user = User::findOrFail($id);

            if ($user->isAccountOwner()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot suspend the account owner',
                ], 403);
            }

            $user->update(['status' => 'suspended']);

            Log::info('User suspended', ['user_id' => $id, 'suspended_by' => $currentUser->id]);

            return response()->json([
                'status' => 'success',
                'message' => 'User suspended',
                'data' => $user->fresh()->toPortalArray(),
            ]);

        } catch (\Exception $e) {
            Log::error('Suspend user error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Reactivate a suspended user.
     *
     * PUT /api/users/{id}/reactivate
     */
    public function reactivate(Request $request, string $id)
    {
        try {
            $currentUser = $request->user();
            if (!$currentUser->canManageUsers()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $user = User::findOrFail($id);
            $user->update(['status' => 'active']);

            Log::info('User reactivated', ['user_id' => $id, 'reactivated_by' => $currentUser->id]);

            return response()->json([
                'status' => 'success',
                'message' => 'User reactivated',
                'data' => $user->fresh()->toPortalArray(),
            ]);

        } catch (\Exception $e) {
            Log::error('Reactivate user error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Transfer account ownership.
     *
     * POST /api/users/{id}/transfer-ownership
     */
    public function transferOwnership(Request $request, string $id)
    {
        try {
            $currentUser = $request->user();

            if (!$currentUser->isAccountOwner()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only the current account owner can transfer ownership',
                ], 403);
            }

            $newOwner = User::findOrFail($id);
            $newOwner->transferOwnership($currentUser);

            Log::info('Account ownership transferred', [
                'account_id' => $currentUser->tenant_id,
                'from_user_id' => $currentUser->id,
                'to_user_id' => $newOwner->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Ownership transferred successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Transfer ownership error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    // =====================================================
    // INVITATIONS
    // =====================================================

    /**
     * List pending invitations.
     *
     * GET /api/invitations
     */
    public function listInvitations(Request $request)
    {
        try {
            $currentUser = $request->user();
            $query = UserInvitation::query();

            if ($currentUser->sub_account_id && !$currentUser->isAdmin()) {
                $query->where('sub_account_id', $currentUser->sub_account_id);
            }

            $invitations = $query->orderByDesc('created_at')->get();

            return response()->json([
                'status' => 'success',
                'data' => $invitations->map(fn($i) => $i->toPortalArray()),
            ]);

        } catch (\Exception $e) {
            Log::error('List invitations error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Create a new invitation.
     * Creates the record with a token and logs the event.
     * Actual email sending is deferred.
     *
     * POST /api/invitations
     *
     * TODO: Connect to email server to send the invitation email.
     * The invitation URL should be: {APP_URL}/invitation/accept?token={raw_token}
     */
    public function invite(Request $request)
    {
        try {
            $currentUser = $request->user();

            if (!$currentUser->canManageUsers()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|max:255',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'role' => 'nullable|in:' . implode(',', array_diff(User::ROLES, [User::ROLE_OWNER])),
                'sub_account_id' => ['nullable', 'string', Rule::exists('sub_accounts', 'id')->where('account_id', $currentUser->tenant_id)],
                'sender_capability' => 'nullable|in:advanced,restricted,none',
                'permission_toggles' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            // Check if user with this email already exists in this tenant
            $existingUser = User::where('email', $request->input('email'))->first();
            if ($existingUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'A user with this email already exists in this account',
                ], 422);
            }

            // Check for existing pending invitation
            $existingInvite = UserInvitation::pending()
                ->where('email', $request->input('email'))
                ->first();
            if ($existingInvite) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'A pending invitation already exists for this email',
                ], 422);
            }

            $token = UserInvitation::generateToken();

            $invitation = UserInvitation::create([
                'account_id' => $currentUser->tenant_id,
                'sub_account_id' => $request->input('sub_account_id'),
                'email' => strtolower(trim($request->input('email'))),
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'token' => $token['hash'],
                'role' => $request->input('role'),
                'sender_capability' => $request->input('sender_capability', User::SENDER_NONE),
                'permission_toggles' => $request->input('permission_toggles'),
                'expires_at' => now()->addHours(UserInvitation::TOKEN_EXPIRY_HOURS),
                'invited_by' => $currentUser->id,
                'invited_by_name' => $currentUser->first_name . ' ' . $currentUser->last_name,
            ]);

            Log::info('User invitation created', [
                'invitation_id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'invited_by' => $currentUser->id,
                'account_id' => $currentUser->tenant_id,
            ]);

            // TODO: Send invitation email via email server
            // The raw token should be included in the invitation URL:
            // $invitationUrl = config('app.url') . '/invitation/accept?token=' . $token['raw'];
            // Mail::to($invitation->email)->send(new UserInvitationMail($invitation, $invitationUrl));

            return response()->json([
                'status' => 'success',
                'message' => 'Invitation created successfully',
                'data' => $invitation->toPortalArray(),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Create invitation error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Revoke an invitation.
     *
     * PUT /api/invitations/{id}/revoke
     */
    public function revokeInvitation(Request $request, string $id)
    {
        try {
            $currentUser = $request->user();

            if (!$currentUser->canManageUsers()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $invitation = UserInvitation::findOrFail($id);

            if (!$invitation->isPending()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invitation is not in a revokable state',
                ], 422);
            }

            $invitation->revoke(
                $currentUser->id,
                $request->input('reason')
            );

            Log::info('Invitation revoked', [
                'invitation_id' => $id,
                'revoked_by' => $currentUser->id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Invitation revoked',
            ]);

        } catch (\Exception $e) {
            Log::error('Revoke invitation error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Accept an invitation (public endpoint, no auth required).
     *
     * POST /invitation/accept
     */
    public function acceptInvitation(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required|string',
                'password' => 'required|string|min:12|max:128',
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            $invitation = UserInvitation::findByToken($request->input('token'));

            if (!$invitation) {
                return response()->json(['status' => 'error', 'message' => 'Invalid invitation token'], 404);
            }

            if (!$invitation->isPending()) {
                $reason = $invitation->isExpired() ? 'expired' : ($invitation->isRevoked() ? 'revoked' : 'already accepted');
                return response()->json([
                    'status' => 'error',
                    'message' => "This invitation has been {$reason}",
                ], 410);
            }

            // Allow name override during acceptance
            if ($request->has('first_name')) {
                $invitation->first_name = $request->input('first_name');
            }
            if ($request->has('last_name')) {
                $invitation->last_name = $request->input('last_name');
            }

            $user = $invitation->accept($request->input('password'));

            Log::info('Invitation accepted', [
                'invitation_id' => $invitation->id,
                'user_id' => $user->id,
                'account_id' => $invitation->account_id,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully. You can now log in.',
                'data' => ['user_id' => $user->id],
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Accept invitation error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Get available roles with descriptions (for role selector UI).
     *
     * GET /api/roles
     */
    public function roles()
    {
        $roles = collect(User::ROLE_LABELS)->map(function ($label, $key) {
            return [
                'value' => $key,
                'label' => $label,
                'permissions' => User::ROLE_DEFAULT_PERMISSIONS[$key] ?? [],
                'can_assign' => $key !== User::ROLE_OWNER, // owner can only be transferred
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $roles,
        ]);
    }
}
