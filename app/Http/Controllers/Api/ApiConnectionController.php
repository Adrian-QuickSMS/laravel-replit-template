<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiConnection;
use App\Models\ApiConnectionAuditEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Customer Portal API Connection Controller
 *
 * All queries are tenant-scoped via Eloquent global scope (session-based).
 * Handles connection CRUD, state transitions, and credential management.
 */
class ApiConnectionController extends Controller
{
    private function tenantId(): string
    {
        return session('customer_tenant_id');
    }

    private function actorId(): string
    {
        return session('customer_user_id', 'unknown');
    }

    private function actorName(): string
    {
        return session('customer_email', session('customer_user_id', 'unknown'));
    }

    // =====================================================
    // LISTING & SHOW
    // =====================================================

    public function index(Request $request): JsonResponse
    {
        $query = ApiConnection::with('subAccount');

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Filters
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
            $query->notArchived();
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
            'data' => $connections->map(fn($c) => $c->toPortalArray()),
            'total' => $connections->count(),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $connection = ApiConnection::with(['subAccount', 'auditEvents' => function ($q) {
            $q->orderByDesc('created_at')->limit(20);
        }])->find($id);

        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        $data = $connection->toPortalArray();
        $data['audit_events'] = $connection->auditEvents->map(fn($e) => $e->toPortalArray());

        return response()->json(['data' => $data]);
    }

    // =====================================================
    // CREATE
    // =====================================================

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
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

        $validated['account_id'] = $this->tenantId();
        $validated['environment'] = $validated['environment'] ?? 'test';
        $validated['partner_config'] = $validated['partner_config'] ?? [];
        $validated['created_by'] = $this->actorName();

        $connection = DB::transaction(function () use ($validated) {
            $connection = ApiConnection::create($validated);

            // Generate credentials
            $credentials = [];
            if ($validated['auth_type'] === 'api_key') {
                $credentials['api_key'] = $connection->generateApiKey();
            } else {
                $credentials = $connection->generateBasicAuth();
            }

            $connection->forceFill(['status' => 'active'])->save();

            // Audit
            ApiConnectionAuditEvent::record(
                $connection,
                'created',
                'customer',
                $this->actorId(),
                $this->actorName(),
                ['type' => $validated['type'], 'auth_type' => $validated['auth_type'], 'environment' => $validated['environment']]
            );

            return ['connection' => $connection->fresh()->load('subAccount'), 'credentials' => $credentials];
        });

        $data = $connection['connection']->toPortalArray();
        $data['credentials'] = $connection['credentials'];

        return response()->json(['data' => $data], 201);
    }

    // =====================================================
    // STATE TRANSITIONS
    // =====================================================

    public function suspend(Request $request, string $id): JsonResponse
    {
        $connection = ApiConnection::find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        $request->validate(['reason' => 'nullable|string|max:500']);

        try {
            DB::transaction(function () use ($connection, $request) {
                $connection->suspend(
                    $request->input('reason', 'Suspended by user'),
                    $this->actorId(),
                    $this->actorName()
                );

                ApiConnectionAuditEvent::record(
                    $connection, 'suspended', 'customer',
                    $this->actorId(), $this->actorName(),
                    ['reason' => $request->input('reason')]
                );
            });
        } catch (\LogicException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $connection->fresh()->toPortalArray()]);
    }

    public function reactivate(string $id): JsonResponse
    {
        $connection = ApiConnection::find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        try {
            DB::transaction(function () use ($connection) {
                $connection->reactivate();

                ApiConnectionAuditEvent::record(
                    $connection, 'reactivated', 'customer',
                    $this->actorId(), $this->actorName()
                );
            });
        } catch (\LogicException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $connection->fresh()->toPortalArray()]);
    }

    public function archive(string $id): JsonResponse
    {
        $connection = ApiConnection::find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        try {
            DB::transaction(function () use ($connection) {
                $connection->archive($this->actorId(), $this->actorName());

                ApiConnectionAuditEvent::record(
                    $connection, 'archived', 'customer',
                    $this->actorId(), $this->actorName()
                );
            });
        } catch (\LogicException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $connection->fresh()->toPortalArray()]);
    }

    public function convertToLive(string $id): JsonResponse
    {
        $connection = ApiConnection::find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        $credentials = null;

        try {
            DB::transaction(function () use ($connection, &$credentials) {
                $connection->convertToLive();

                // Regenerate key with live prefix
                if ($connection->getRawOriginal('auth_type') === 'api_key') {
                    $credentials = ['api_key' => $connection->regenerateApiKey()];
                }

                ApiConnectionAuditEvent::record(
                    $connection, 'converted_to_live', 'customer',
                    $this->actorId(), $this->actorName()
                );
            });
        } catch (\LogicException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        $data = $connection->fresh()->toPortalArray();
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
        $connection = ApiConnection::find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        if ($connection->getRawOriginal('auth_type') !== 'api_key') {
            return response()->json(['status' => 'error', 'message' => 'Connection does not use API Key auth'], 422);
        }

        $newKey = DB::transaction(function () use ($connection) {
            $newKey = $connection->regenerateApiKey();

            ApiConnectionAuditEvent::record(
                $connection, 'key_regenerated', 'customer',
                $this->actorId(), $this->actorName(),
                ['note' => 'Previous key immediately revoked']
            );

            return $newKey;
        });

        return response()->json([
            'data' => $connection->fresh()->toPortalArray(),
            'credentials' => ['api_key' => $newKey],
        ]);
    }

    public function changePassword(string $id): JsonResponse
    {
        $connection = ApiConnection::find($id);
        if (!$connection) {
            return response()->json(['status' => 'error', 'message' => 'Connection not found'], 404);
        }

        if ($connection->getRawOriginal('auth_type') !== 'basic_auth') {
            return response()->json(['status' => 'error', 'message' => 'Connection does not use Basic Auth'], 422);
        }

        $newPassword = DB::transaction(function () use ($connection) {
            $newPassword = $connection->regeneratePassword();

            ApiConnectionAuditEvent::record(
                $connection, 'password_changed', 'customer',
                $this->actorId(), $this->actorName(),
                ['note' => 'Previous password immediately revoked']
            );

            return $newPassword;
        });

        return response()->json([
            'data' => $connection->fresh()->toPortalArray(),
            'credentials' => [
                'username' => $connection->basic_auth_username,
                'password' => $newPassword,
            ],
        ]);
    }
}
