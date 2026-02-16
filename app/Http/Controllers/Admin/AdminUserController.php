<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\AuthAuditLog;
use App\Services\Admin\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = AdminUser::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'ilike', "%{$search}%")
                  ->orWhere('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name', 'ilike', "%{$search}%");
            });
        }

        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('dir', 'desc');
        $allowedSorts = ['email', 'first_name', 'last_name', 'role', 'status', 'last_login_at', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $users = $query->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $users->getCollection()->map->toAdminArray(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
            'stats' => [
                'total' => AdminUser::count(),
                'active' => AdminUser::where('status', 'active')->count(),
                'suspended' => AdminUser::where('status', 'suspended')->count(),
                'locked' => AdminUser::where('status', 'locked')->orWhere(function ($q) {
                    $q->whereNotNull('locked_until')->where('locked_until', '>', now());
                })->count(),
                'mfa_enrolled' => AdminUser::where('mfa_enabled', true)->count(),
                'invited' => AdminUser::whereNotNull('invite_token')->whereNotNull('invite_expires_at')->where('invite_expires_at', '>', now())->count(),
            ],
        ]);
    }

    public function show(string $id)
    {
        $user = AdminUser::findOrFail($id);

        $recentActivity = [];
        try {
            $recentActivity = AuthAuditLog::where('actor_id', $user->id)
                ->where('actor_type', 'admin_user')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
        } catch (\Exception $e) {
            Log::warning('[AdminUser] Failed to load audit logs', ['error' => $e->getMessage()]);
        }

        return response()->json([
            'user' => $user->toAdminArray(),
            'ip_whitelist' => $user->ip_whitelist ?? [],
            'recent_activity' => $recentActivity,
        ]);
    }

    public function store(Request $request)
    {
        $currentRole = session('admin_auth.role');
        if ($currentRole !== 'super_admin') {
            return response()->json(['error' => 'Only super admins can create users'], 403);
        }

        $request->validate([
            'email' => 'required|email|unique:admin_users,email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'role' => 'required|in:super_admin,admin,support,finance,readonly',
            'department' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
        ]);

        $email = strtolower($request->email);
        if (!str_ends_with($email, '@quicksms.com')) {
            return response()->json(['error' => 'Email must be @quicksms.com'], 422);
        }

        try {
            $tempPassword = bin2hex(random_bytes(16));

            $user = AdminUser::create([
                'email' => $email,
                'password' => $tempPassword,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'role' => $request->role,
                'department' => $request->department,
                'phone' => $request->phone,
                'status' => 'active',
                'force_password_change' => true,
                'created_by' => session('admin_auth.email'),
            ]);

            $inviteToken = $user->generateInviteToken();

            AdminAuditService::log('admin_user_created', [
                'created_user_id' => $user->id,
                'created_email' => $user->email,
                'role' => $user->role,
            ]);

            return response()->json([
                'success' => true,
                'user' => $user->toAdminArray(),
            ], 201);
        } catch (\Exception $e) {
            Log::error('[AdminUser] Failed to create user', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to create user'], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $currentRole = session('admin_auth.role');
        if (!in_array($currentRole, ['super_admin', 'admin'])) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        $user = AdminUser::findOrFail($id);

        $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'role' => 'sometimes|in:super_admin,admin,support,finance,readonly',
            'department' => 'sometimes|nullable|string|max:100',
            'phone' => 'sometimes|nullable|string|max:20',
        ]);

        if ($request->role === 'super_admin' && $currentRole !== 'super_admin') {
            return response()->json(['error' => 'Only super admins can assign super admin role'], 403);
        }

        try {
            $user->update(array_merge(
                $request->only(['first_name', 'last_name', 'role', 'department', 'phone']),
                ['updated_by' => session('admin_auth.email')]
            ));

            AdminAuditService::log('admin_user_updated', [
                'updated_user_id' => $user->id,
                'changes' => $request->only(['first_name', 'last_name', 'role', 'department']),
            ]);

            return response()->json(['success' => true, 'user' => $user->fresh()->toAdminArray()]);
        } catch (\Exception $e) {
            Log::error('[AdminUser] Failed to update user', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to update user'], 500);
        }
    }

    public function suspend(string $id)
    {
        $user = AdminUser::findOrFail($id);

        if ($user->id === session('admin_auth.admin_id')) {
            return response()->json(['error' => 'Cannot suspend your own account'], 422);
        }

        $user->update(['status' => 'suspended', 'updated_by' => session('admin_auth.email')]);
        AdminAuditService::log('admin_user_suspended', ['user_id' => $user->id, 'email' => $user->email]);

        return response()->json(['success' => true]);
    }

    public function activate(string $id)
    {
        $user = AdminUser::findOrFail($id);
        $user->update(['status' => 'active', 'updated_by' => session('admin_auth.email')]);
        AdminAuditService::log('admin_user_activated', ['user_id' => $user->id, 'email' => $user->email]);

        return response()->json(['success' => true]);
    }

    public function unlock(string $id)
    {
        $user = AdminUser::findOrFail($id);
        $user->unlockAccount();
        AdminAuditService::log('admin_user_unlocked', ['user_id' => $user->id, 'email' => $user->email]);

        return response()->json(['success' => true]);
    }

    public function resetMfa(string $id)
    {
        $currentRole = session('admin_auth.role');
        if ($currentRole !== 'super_admin') {
            return response()->json(['error' => 'Only super admins can reset MFA'], 403);
        }

        $user = AdminUser::findOrFail($id);

        $user->update([
            'mfa_secret' => null,
            'mfa_method' => null,
            'mfa_recovery_codes' => null,
            'mfa_enabled_at' => null,
            'updated_by' => session('admin_auth.email'),
        ]);

        AdminAuditService::log('admin_mfa_reset', ['user_id' => $user->id, 'email' => $user->email]);

        return response()->json(['success' => true]);
    }

    public function resendInvite(string $id)
    {
        $user = AdminUser::findOrFail($id);
        $token = $user->generateInviteToken();

        AdminAuditService::log('admin_invite_resent', ['user_id' => $user->id, 'email' => $user->email]);

        return response()->json(['success' => true]);
    }

    public function destroy(string $id)
    {
        $currentRole = session('admin_auth.role');
        if ($currentRole !== 'super_admin') {
            return response()->json(['error' => 'Only super admins can delete users'], 403);
        }

        $user = AdminUser::findOrFail($id);

        if ($user->id === session('admin_auth.admin_id')) {
            return response()->json(['error' => 'Cannot delete your own account'], 422);
        }

        $user->update(['updated_by' => session('admin_auth.email')]);
        $user->delete();

        AdminAuditService::log('admin_user_deleted', ['user_id' => $user->id, 'email' => $user->email]);

        return response()->json(['success' => true]);
    }
}
