<?php

namespace App\Http\Controllers;

use App\Models\SubAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\UserAuditLog;
use App\Services\Audit\AuditContext;

/**
 * Sub-Account Management Controller
 *
 * Handles CRUD, limits/enforcement configuration, and status management
 * for sub-accounts within the customer portal.
 *
 * SECURITY:
 * - All routes require authentication
 * - Tenant scoping enforced via SubAccount global scope
 * - Only owners/admins can create/modify sub-accounts
 * - Sub-account users can only view their own sub-account
 */
class SubAccountController extends Controller
{
    /**
     * List all sub-accounts for the current tenant.
     *
     * GET /api/sub-accounts
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Sub-account users can only see their own sub-account
            if ($user->sub_account_id && !$user->isAdmin()) {
                $subAccounts = SubAccount::where('id', $user->sub_account_id)->get();
            } else {
                $query = SubAccount::query();

                if ($request->has('status')) {
                    $query->where('sub_account_status', $request->input('status'));
                }

                if ($request->has('search')) {
                    $search = $request->input('search');
                    $query->where('name', 'ilike', "%{$search}%");
                }

                $subAccounts = $query->withCount('users')
                    ->orderBy('name')
                    ->paginate($request->integer('per_page', 50));

                return response()->json([
                    'status' => 'success',
                    'data' => $subAccounts->map(fn($sa) => $sa->toPortalArray()),
                    'meta' => [
                        'current_page' => $subAccounts->currentPage(),
                        'last_page' => $subAccounts->lastPage(),
                        'per_page' => $subAccounts->perPage(),
                        'total' => $subAccounts->total(),
                    ],
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $subAccounts->map(fn($sa) => $sa->toPortalArray()),
            ]);

        } catch (\Exception $e) {
            Log::error('List sub-accounts error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Get a single sub-account.
     *
     * GET /api/sub-accounts/{id}
     */
    public function show(Request $request, string $id)
    {
        try {
            $user = $request->user();
            $subAccount = SubAccount::findOrFail($id);

            // Enforce visibility: sub-account users can only view their own
            if (!$user->canViewSubAccount($subAccount->id)) {
                return response()->json(['status' => 'error', 'message' => 'Forbidden'], 403);
            }

            return response()->json([
                'status' => 'success',
                'data' => $subAccount->toPortalArray(),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Sub-account not found'], 404);
        } catch (\Exception $e) {
            Log::error('Show sub-account error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Create a new sub-account.
     *
     * POST /api/sub-accounts
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->canManageSubAccounts()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'monthly_spending_cap' => 'nullable|numeric|min:0',
                'monthly_message_cap' => 'nullable|integer|min:0',
                'daily_send_limit' => 'nullable|integer|min:0',
                'enforcement_type' => 'nullable|in:warn,block,approval',
                'hard_stop_enabled' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            $subAccount = SubAccount::create([
                'account_id' => $user->tenant_id,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'is_active' => true,
                'sub_account_status' => SubAccount::STATUS_LIVE,
                'created_by' => $user->id,
                'monthly_spending_cap' => $request->input('monthly_spending_cap'),
                'monthly_message_cap' => $request->input('monthly_message_cap'),
                'daily_send_limit' => $request->input('daily_send_limit'),
                'enforcement_type' => $request->input('enforcement_type', SubAccount::ENFORCEMENT_WARN),
                'hard_stop_enabled' => $request->boolean('hard_stop_enabled', false),
                'limits_updated_by' => $user->id,
                'limits_updated_at' => now(),
            ]);

            Log::info('Sub-account created', [
                'sub_account_id' => $subAccount->id,
                'account_id' => $user->tenant_id,
                'created_by' => $user->id,
            ]);

            try {
                $actor = AuditContext::actor();
                UserAuditLog::recordSubAccountEvent($actor['account_id'], 'sub_account_created', $subAccount->id, $actor['user_id'], $actor['user_name'], "Sub-account '{$subAccount->name}' created", ['name' => $subAccount->name, 'monthly_spending_cap' => $subAccount->monthly_spending_cap ?? null, 'enforcement_type' => $subAccount->enforcement_type ?? null]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[AuditLog] Failed to record sub_account_created', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Sub-account created successfully',
                'data' => $subAccount->toPortalArray(),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Create sub-account error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Update a sub-account's basic info.
     *
     * PUT /api/sub-accounts/{id}
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = $request->user();

            if (!$user->canManageSubAccounts()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $subAccount = SubAccount::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            $before = $subAccount->only(['name', 'description']);

            $subAccount->update($request->only(['name', 'description']));

            try {
                $actor = AuditContext::actor();
                UserAuditLog::recordSubAccountEvent($actor['account_id'], 'sub_account_edited', $subAccount->id, $actor['user_id'], $actor['user_name'], "Sub-account '{$subAccount->name}' updated", ['changes' => AuditContext::diff($before, $subAccount->only(['name', 'description']))]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[AuditLog] Failed to record sub_account_edited', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Sub-account updated',
                'data' => $subAccount->fresh()->toPortalArray(),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Sub-account not found'], 404);
        } catch (\Exception $e) {
            Log::error('Update sub-account error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Update sub-account limits and enforcement.
     *
     * PUT /api/sub-accounts/{id}/limits
     */
    public function updateLimits(Request $request, string $id)
    {
        try {
            $user = $request->user();

            // Only main account admins can increase limits
            if (!$user->isAdmin() || !$user->isMainAccountUser()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only main account admins can modify sub-account limits',
                ], 403);
            }

            $subAccount = SubAccount::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'monthly_spending_cap' => 'nullable|numeric|min:0',
                'monthly_message_cap' => 'nullable|integer|min:0',
                'daily_send_limit' => 'nullable|integer|min:0',
                'enforcement_type' => 'nullable|in:warn,block,approval',
                'hard_stop_enabled' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            $beforeLimits = $subAccount->only(['monthly_spending_cap', 'monthly_message_cap', 'daily_send_limit', 'enforcement_type', 'hard_stop_enabled']);

            $subAccount->updateLimits($request->only([
                'monthly_spending_cap', 'monthly_message_cap', 'daily_send_limit',
                'enforcement_type', 'hard_stop_enabled',
            ]), $user->id);

            try {
                $actor = AuditContext::actor();
                UserAuditLog::recordSubAccountEvent($actor['account_id'], 'sub_account_limits_updated', $subAccount->id, $actor['user_id'], $actor['user_name'], "Sub-account '{$subAccount->name}' limits updated", ['changes' => AuditContext::diff($beforeLimits, $subAccount->only(['monthly_spending_cap', 'monthly_message_cap', 'daily_send_limit', 'enforcement_type', 'hard_stop_enabled']))]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[AuditLog] Failed to record sub_account_limits_updated', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Limits updated successfully',
                'data' => $subAccount->fresh()->toPortalArray(),
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => 'error', 'message' => 'Sub-account not found'], 404);
        } catch (\Exception $e) {
            Log::error('Update sub-account limits error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Suspend a sub-account.
     *
     * PUT /api/sub-accounts/{id}/suspend
     */
    public function suspend(Request $request, string $id)
    {
        try {
            $user = $request->user();
            if (!$user->canManageSubAccounts()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $subAccount = SubAccount::findOrFail($id);
            $subAccount->suspend();

            Log::info('Sub-account suspended', [
                'sub_account_id' => $id,
                'suspended_by' => $user->id,
            ]);

            try {
                $actor = AuditContext::actor();
                UserAuditLog::recordSubAccountEvent($actor['account_id'], 'sub_account_suspended', $subAccount->id, $actor['user_id'], $actor['user_name'], "Sub-account '{$subAccount->name}' suspended");
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[AuditLog] Failed to record sub_account_suspended', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Sub-account suspended',
                'data' => $subAccount->fresh()->toPortalArray(),
            ]);

        } catch (\Exception $e) {
            Log::error('Suspend sub-account error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Reactivate a suspended sub-account.
     *
     * PUT /api/sub-accounts/{id}/reactivate
     */
    public function reactivate(Request $request, string $id)
    {
        try {
            $user = $request->user();
            if (!$user->canManageSubAccounts()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $subAccount = SubAccount::findOrFail($id);
            $subAccount->reactivate();

            Log::info('Sub-account reactivated', [
                'sub_account_id' => $id,
                'reactivated_by' => $user->id,
            ]);

            try {
                $actor = AuditContext::actor();
                UserAuditLog::recordSubAccountEvent($actor['account_id'], 'sub_account_reactivated', $subAccount->id, $actor['user_id'], $actor['user_name'], "Sub-account '{$subAccount->name}' reactivated");
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[AuditLog] Failed to record sub_account_reactivated', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Sub-account reactivated',
                'data' => $subAccount->fresh()->toPortalArray(),
            ]);

        } catch (\Exception $e) {
            Log::error('Reactivate sub-account error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }

    /**
     * Archive a suspended sub-account (terminal state).
     *
     * PUT /api/sub-accounts/{id}/archive
     */
    public function archive(Request $request, string $id)
    {
        try {
            $user = $request->user();
            if (!$user->canManageSubAccounts()) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $subAccount = SubAccount::findOrFail($id);
            $subAccount->archive();

            Log::info('Sub-account archived', [
                'sub_account_id' => $id,
                'archived_by' => $user->id,
            ]);

            try {
                $actor = AuditContext::actor();
                UserAuditLog::recordSubAccountEvent($actor['account_id'], 'sub_account_archived', $subAccount->id, $actor['user_id'], $actor['user_name'], "Sub-account '{$subAccount->name}' archived");
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[AuditLog] Failed to record sub_account_archived', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Sub-account archived',
                'data' => $subAccount->fresh()->toPortalArray(),
            ]);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Archive sub-account error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'An error occurred'], 500);
        }
    }
}
