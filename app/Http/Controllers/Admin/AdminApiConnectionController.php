<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiConnection;
use App\Models\ApiConnectionAuditEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin Portal API Connection Controller
 *
 * Cross-tenant — admins can view and manage connections across all accounts.
 * Uses withoutGlobalScopes() with explicit account_id filtering.
 */
class AdminApiConnectionController extends Controller
{
    private function actorId(): string
    {
        return session('admin_user_id', 'admin');
    }

    private function actorName(): string
    {
        return session('admin_email', session('admin_user_id', 'admin'));
    }

    /**
     * Query builder without tenant scoping (admin cross-tenant access).
     */
    private function query()
    {
        return ApiConnection::withoutGlobalScopes();
    }

    // =====================================================
    // LISTING & SHOW
    // =====================================================

    public function index(Request $request): JsonResponse
    {
        $query = $this->query()->with(['subAccount', 'account']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Account filter (admin-specific)
        if ($request->filled('account_id')) {
            $accountIds = is_array($request->input('account_id')) ? $request->input('account_id') : [$request->input('account_id')];
            $query->whereIn('account_id', $accountIds);
        }

        // Standard filters
        if ($request->filled('type')) {
            $types = is_array($request->input('type')) ? $request->input('type') : [$request->input('type')];
            $query->whereIn(DB::raw("type::text"), $types);
        }
        if ($request->filled('environment')) {
            $envs = is_array($request->input('environment')) ? $request->input('environment') : [$request->input('environment')];
            $query->whereIn(DB::raw("environment::text"), $envs);
        }
        if ($request->filled('status')) {
            $statuses = is_array($request->input('status')) ? $request->input('status') : [$request->input('status')];
            $query->whereIn(DB::raw("status::text"), $statuses);
        }
        if ($request->filled('auth_type')) {
            $authTypes = is_array($request->input('auth_type')) ? $request->input('auth_type') : [$request->input('auth_type')];
            $query->whereIn(DB::raw("auth_type::text"), $authTypes);
        }
        if ($request->filled('sub_account_id')) {
            $query->where('sub_account_id', $request->input('sub_account_id'));
        }

        // Hide archived by default
        if (!$request->boolean('show_archived')) {
            $query->where('status', '!=', 'archived');
        }

        // Sorting
        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('dir', 'desc');
        $allowedSorts = ['name', 'created_at', 'last_used_at', 'status', 'type', 'environment'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $connections = $query->get();

        return response()->json([
            'data' => $connections->map(fn($c) => $c->toAdminArray()),
            'total' => $connections->count(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $connection = $this->query()->with(['subAccount', 'account'])->find($id);

        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        // Load audit events without global scope
        $auditEvents = ApiConnectionAuditEvent::withoutGlobalScopes()
            ->where('api_connection_id', $id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $data = $connection->toAdminArray();
        $data['audit_events'] = $auditEvents->map(fn($e) => $e->toPortalArray());

        return response()->json(['data' => $data]);
    }

    // =====================================================
    // CREATE (on behalf of any account)
    // =====================================================

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|uuid',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sub_account_id' => 'nullable|uuid',
            'type' => 'required|string|in:bulk,campaign,integration',
            'auth_type' => 'required|string|in:api_key,basic_auth',
            'environment' => 'nullable|string|in:test,live',
            'ip_allowlist_enabled' => 'nullable|boolean',
            'ip_allowlist' => 'nullable|array',
            'ip_allowlist.*' => 'string|max:45',
            'webhook_dlr_url' => 'nullable|url|max:2048',
            'webhook_inbound_url' => 'nullable|url|max:2048',
            'partner_name' => 'nullable|string|in:systmone,rio,emis,accurx',
            'partner_config' => 'nullable|array',
        ]);

        $validated['environment'] = $validated['environment'] ?? 'test';
        $validated['partner_config'] = $validated['partner_config'] ?? [];
        $validated['created_by'] = $this->actorName();

        // Set tenant context for RLS during creation
        DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$validated['account_id']]);

        $result = DB::transaction(function () use ($validated) {
            $connection = ApiConnection::create($validated);

            $credentials = [];
            if ($validated['auth_type'] === 'api_key') {
                $credentials['api_key'] = $connection->generateApiKey();
            } else {
                $credentials = $connection->generateBasicAuth();
            }

            $connection->activate();

            ApiConnectionAuditEvent::record(
                $connection, 'created', 'admin',
                $this->actorId(), $this->actorName(),
                ['type' => $validated['type'], 'auth_type' => $validated['auth_type'], 'environment' => $validated['environment']]
            );

            return ['connection' => $connection->fresh(), 'credentials' => $credentials];
        });

        $connection = $this->query()->with(['subAccount', 'account'])->find($result['connection']->id);
        $data = $connection->toAdminArray();
        $data['credentials'] = $result['credentials'];

        return response()->json(['data' => $data], 201);
    }

    // =====================================================
    // STATE TRANSITIONS
    // =====================================================

    public function suspend(Request $request, string $id): JsonResponse
    {
        $connection = $this->query()->find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        try {
            $this->withTenantContext($connection->account_id, function () use ($connection, $request) {
                $connection->suspend(
                    $request->input('reason', 'Suspended by admin'),
                    $this->actorId(),
                    $this->actorName()
                );

                ApiConnectionAuditEvent::record(
                    $connection, 'suspended', 'admin',
                    $this->actorId(), $this->actorName(),
                    ['reason' => $request->input('reason')]
                );
            });
        } catch (\LogicException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $this->query()->with(['subAccount', 'account'])->find($id)->toAdminArray()]);
    }

    public function reactivate(string $id): JsonResponse
    {
        $connection = $this->query()->find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        try {
            $this->withTenantContext($connection->account_id, function () use ($connection) {
                $connection->reactivate();

                ApiConnectionAuditEvent::record(
                    $connection, 'reactivated', 'admin',
                    $this->actorId(), $this->actorName()
                );
            });
        } catch (\LogicException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $this->query()->with(['subAccount', 'account'])->find($id)->toAdminArray()]);
    }

    public function archive(string $id): JsonResponse
    {
        $connection = $this->query()->find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        try {
            $this->withTenantContext($connection->account_id, function () use ($connection) {
                $connection->archive($this->actorId(), $this->actorName());

                ApiConnectionAuditEvent::record(
                    $connection, 'archived', 'admin',
                    $this->actorId(), $this->actorName()
                );
            });
        } catch (\LogicException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $this->query()->with(['subAccount', 'account'])->find($id)->toAdminArray()]);
    }

    public function convertToLive(string $id): JsonResponse
    {
        $connection = $this->query()->find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        $credentials = null;

        try {
            $this->withTenantContext($connection->account_id, function () use ($connection, &$credentials) {
                $connection->convertToLive();

                if ($connection->getRawOriginal('auth_type') === 'api_key') {
                    $credentials = ['api_key' => $connection->regenerateApiKey()];
                }

                ApiConnectionAuditEvent::record(
                    $connection, 'converted_to_live', 'admin',
                    $this->actorId(), $this->actorName()
                );
            });
        } catch (\LogicException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        $data = $this->query()->with(['subAccount', 'account'])->find($id)->toAdminArray();
        if ($credentials) {
            $data['credentials'] = $credentials;
        }

        return response()->json(['data' => $data]);
    }

    // =====================================================
    // CREDENTIAL MANAGEMENT
    // =====================================================

    public function regenerateKey(string $id): JsonResponse
    {
        $connection = $this->query()->find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        if ($connection->getRawOriginal('auth_type') !== 'api_key') {
            return response()->json(['status' => 'error', 'message' => 'Connection does not use API Key auth'], 422);
        }

        $newKey = null;
        $this->withTenantContext($connection->account_id, function () use ($connection, &$newKey) {
            $newKey = $connection->regenerateApiKey();

            ApiConnectionAuditEvent::record(
                $connection, 'key_regenerated', 'admin',
                $this->actorId(), $this->actorName(),
                ['note' => 'Previous key immediately revoked']
            );
        });

        return response()->json([
            'data' => $this->query()->with(['subAccount', 'account'])->find($id)->toAdminArray(),
            'credentials' => ['api_key' => $newKey],
        ]);
    }

    public function changePassword(string $id): JsonResponse
    {
        $connection = $this->query()->find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        if ($connection->getRawOriginal('auth_type') !== 'basic_auth') {
            return response()->json(['status' => 'error', 'message' => 'Connection does not use Basic Auth'], 422);
        }

        $newPassword = null;
        $this->withTenantContext($connection->account_id, function () use ($connection, &$newPassword) {
            $newPassword = $connection->regeneratePassword();

            ApiConnectionAuditEvent::record(
                $connection, 'password_changed', 'admin',
                $this->actorId(), $this->actorName(),
                ['note' => 'Previous password immediately revoked']
            );
        });

        return response()->json([
            'data' => $this->query()->with(['subAccount', 'account'])->find($id)->toAdminArray(),
            'credentials' => [
                'username' => $connection->basic_auth_username,
                'password' => $newPassword,
            ],
        ]);
    }

    // =====================================================
    // ADMIN-ONLY: EDIT ENDPOINTS & SECURITY
    // =====================================================

    public function updateEndpoints(Request $request, string $id): JsonResponse
    {
        $connection = $this->query()->find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        $validated = $request->validate([
            'webhook_dlr_url' => 'nullable|url|max:2048',
            'webhook_inbound_url' => 'nullable|url|max:2048',
        ]);

        $this->withTenantContext($connection->account_id, function () use ($connection, $validated) {
            $connection->update($validated);

            ApiConnectionAuditEvent::record(
                $connection, 'endpoints_updated', 'admin',
                $this->actorId(), $this->actorName(),
                $validated
            );
        });

        return response()->json(['data' => $this->query()->with(['subAccount', 'account'])->find($id)->toAdminArray()]);
    }

    public function updateSecurity(Request $request, string $id): JsonResponse
    {
        $connection = $this->query()->find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        $validated = $request->validate([
            'ip_allowlist_enabled' => 'required|boolean',
            'ip_allowlist' => 'nullable|array',
            'ip_allowlist.*' => 'string|max:45',
        ]);

        $this->withTenantContext($connection->account_id, function () use ($connection, $validated) {
            $connection->update($validated);

            ApiConnectionAuditEvent::record(
                $connection, 'security_updated', 'admin',
                $this->actorId(), $this->actorName(),
                ['ip_allowlist_enabled' => $validated['ip_allowlist_enabled'], 'ip_count' => count($validated['ip_allowlist'] ?? [])]
            );
        });

        return response()->json(['data' => $this->query()->with(['subAccount', 'account'])->find($id)->toAdminArray()]);
    }

    // =====================================================
    // HELPERS
    // =====================================================

    /**
     * Temporarily set tenant context for RLS-protected operations.
     */
    private function withTenantContext(string $accountId, callable $callback)
    {
        DB::select("SELECT set_config('app.current_tenant_id', ?, false)", [$accountId]);
        return DB::transaction($callback);
    }
}
