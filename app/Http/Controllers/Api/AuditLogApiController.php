<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountAuditLog;
use App\Models\AdminAuditLog;
use App\Models\ApiConnectionAuditEvent;
use App\Models\AuthAuditLog;
use App\Models\CampaignAuditLog;
use App\Models\EmailToSmsAuditLog;
use App\Models\MessageTemplateAuditLog;
use App\Models\NumberAuditLog;
use App\Models\PurchaseAuditLog;
use App\Models\UserAuditLog;
use App\Services\Audit\AuditContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Unified Audit Log API — aggregates events from all audit tables.
 *
 * Customer portal: GET /api/audit-logs (tenant-scoped via RLS)
 * Admin console:   GET /api/admin/audit-logs (cross-tenant, RED zone)
 */
class AuditLogApiController extends Controller
{
    /**
     * Module → table mapping for customer portal queries.
     * Each entry defines the table, its columns, and how to map them
     * to the unified response schema.
     */
    private const CUSTOMER_SOURCES = [
        'campaigns' => [
            'table' => 'campaign_audit_log',
            'entity_col' => 'campaign_id',
            'module' => 'campaigns',
            'category' => 'messaging',
        ],
        'user_management' => [
            'table' => 'user_audit_log',
            'entity_col' => 'target_user_id',
            'module' => 'users',
            'category' => 'user_management',
            'extra_where' => "module = 'user_management'",
        ],
        'sub_accounts' => [
            'table' => 'user_audit_log',
            'entity_col' => 'target_user_id',
            'module' => 'sub_accounts',
            'category' => 'account',
            'extra_where' => "module = 'sub_account'",
        ],
        'account' => [
            'table' => 'account_audit_log',
            'entity_col' => null,
            'module' => 'account',
            'category' => 'account',
        ],
        'numbers' => [
            'table' => 'number_audit_log',
            'entity_col' => 'number_id',
            'module' => 'numbers',
            'category' => 'account',
        ],
        'authentication' => [
            'table' => 'auth_audit_log',
            'entity_col' => null,
            'module' => 'authentication',
            'category' => 'authentication',
            'custom_select' => true,
        ],
        'api_connections' => [
            'table' => 'api_connection_audit_events',
            'entity_col' => 'api_connection_id',
            'module' => 'api',
            'category' => 'api',
            'custom_select' => true,
        ],
        'templates' => [
            'table' => 'message_template_audit_log',
            'entity_col' => 'template_id',
            'module' => 'messaging',
            'category' => 'messaging',
            'custom_select' => true,
        ],
        'email_to_sms' => [
            'table' => 'email_to_sms_audit_log',
            'entity_col' => 'setup_id',
            'module' => 'messaging',
            'category' => 'messaging',
            'custom_select' => true,
        ],
        // NOTE: financial_audit_log intentionally excluded from customer sources.
        // It has no account_id column and would leak cross-tenant data.
        // Financial events visible to customers come from purchase_audit_logs
        // (already accessible via the existing purchase history UI).
    ];

    /**
     * GET /api/audit-logs — Customer portal unified audit log.
     *
     * Query params:
     *   module    - Filter by module (campaigns, users, account, etc.)
     *   category  - Filter by category (messaging, security, financial, etc.)
     *   action    - Filter by specific action string
     *   user_id   - Filter by actor user ID
     *   from      - ISO date start
     *   to        - ISO date end
     *   search    - Free-text search in details/action
     *   per_page  - Items per page (default 50, max 200)
     *   page      - Page number
     */
    public function index(Request $request): JsonResponse
    {
        $accountId = AuditContext::accountId();
        if (!$accountId) {
            return response()->json(['error' => 'No tenant context'], 403);
        }

        $module = $request->input('module');
        $category = $request->input('category');
        $action = $request->input('action');
        $userId = $request->input('user_id');
        $from = $request->input('from');
        $to = $request->input('to');
        $search = $request->input('search');
        $perPage = min((int) ($request->input('per_page', 50)), 200);
        $page = max((int) ($request->input('page', 1)), 1);
        $offset = ($page - 1) * $perPage;

        // Determine which sources to query
        $sources = self::CUSTOMER_SOURCES;
        if ($module) {
            $sources = array_filter($sources, fn($s) => $s['module'] === $module);
        }
        if ($category) {
            $sources = array_filter($sources, fn($s) => $s['category'] === $category);
        }

        if (empty($sources)) {
            return response()->json([
                'data' => [],
                'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => 0],
            ]);
        }

        // Build UNION ALL query across selected audit tables
        $unionParts = [];
        $bindings = [];

        foreach ($sources as $key => $src) {
            $sql = $this->buildSourceQuery($src, $accountId, $action, $userId, $from, $to, $search, $bindings);
            if ($sql) {
                $unionParts[] = $sql;
            }
        }

        if (empty($unionParts)) {
            return response()->json([
                'data' => [],
                'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => 0],
            ]);
        }

        $unionSql = implode(' UNION ALL ', $unionParts);

        // Count total
        $countSql = "SELECT COUNT(*) as total FROM ({$unionSql}) AS unified_audit";
        $total = DB::selectOne($countSql, $bindings)->total ?? 0;

        // Fetch page
        $dataSql = "SELECT * FROM ({$unionSql}) AS unified_audit ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $dataBindings = array_merge($bindings, [$perPage, $offset]);
        $rows = DB::select($dataSql, $dataBindings);

        // Format results
        $data = array_map(function ($row) {
            return [
                'id' => $row->id,
                'module' => $row->module,
                'category' => $row->category,
                'action' => $row->action,
                'user_id' => $row->user_id,
                'user_name' => $row->user_name,
                'details' => $row->details,
                'metadata' => json_decode($row->metadata ?? '{}', true),
                'ip_address' => $row->ip_address,
                'created_at' => $row->created_at,
            ];
        }, $rows);

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => (int) $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    /**
     * GET /api/audit-logs/modules — List available modules for filtering.
     */
    public function modules(): JsonResponse
    {
        $modules = collect(self::CUSTOMER_SOURCES)
            ->pluck('module')
            ->unique()
            ->values()
            ->all();

        return response()->json(['data' => $modules]);
    }

    /**
     * GET /api/audit-logs/stats — Quick stats for the audit dashboard.
     */
    public function stats(Request $request): JsonResponse
    {
        $accountId = AuditContext::accountId();
        if (!$accountId) {
            return response()->json(['error' => 'No tenant context'], 403);
        }

        $days = min((int) ($request->input('days', 30)), 365);
        $since = now()->subDays($days)->toIso8601String();
        $todayStart = now()->startOfDay()->toIso8601String();

        $moduleStats = [];

        // Per-module counts from the main new tables
        $tables = [
            'campaigns' => 'campaign_audit_log',
            'users' => 'user_audit_log',
            'account' => 'account_audit_log',
            'numbers' => 'number_audit_log',
        ];

        foreach ($tables as $label => $table) {
            try {
                $count = DB::selectOne(
                    "SELECT COUNT(*) as cnt FROM {$table} WHERE account_id = ? AND created_at >= ?",
                    [$accountId, $since]
                );
                $moduleStats[$label] = (int) ($count->cnt ?? 0);
            } catch (\Throwable $e) {
                $moduleStats[$label] = 0;
            }
        }

        // Auth events
        try {
            $authCount = DB::selectOne(
                "SELECT COUNT(*) as cnt FROM auth_audit_log WHERE tenant_id = ? AND created_at >= ?",
                [$accountId, $since]
            );
            $moduleStats['authentication'] = (int) ($authCount->cnt ?? 0);
        } catch (\Throwable $e) {
            $moduleStats['authentication'] = 0;
        }

        $total = array_sum($moduleStats);

        // Today's events (cross-table)
        $todayCount = 0;
        foreach ($tables as $table) {
            try {
                $c = DB::selectOne(
                    "SELECT COUNT(*) as cnt FROM {$table} WHERE account_id = ? AND created_at >= ?",
                    [$accountId, $todayStart]
                );
                $todayCount += (int) ($c->cnt ?? 0);
            } catch (\Throwable $e) {
                // skip
            }
        }
        try {
            $c = DB::selectOne(
                "SELECT COUNT(*) as cnt FROM auth_audit_log WHERE tenant_id = ? AND created_at >= ?",
                [$accountId, $todayStart]
            );
            $todayCount += (int) ($c->cnt ?? 0);
        } catch (\Throwable $e) {
            // skip
        }

        // Unique actors (period)
        $uniqueActors = 0;
        try {
            // Count distinct user_ids across the main tables
            $actorSql = "SELECT COUNT(DISTINCT user_id) as cnt FROM ("
                . "SELECT user_id FROM campaign_audit_log WHERE account_id = ? AND created_at >= ? AND user_id IS NOT NULL"
                . " UNION ALL SELECT user_id FROM user_audit_log WHERE account_id = ? AND created_at >= ? AND user_id IS NOT NULL"
                . " UNION ALL SELECT user_id FROM account_audit_log WHERE account_id = ? AND created_at >= ? AND user_id IS NOT NULL"
                . " UNION ALL SELECT user_id FROM number_audit_log WHERE account_id = ? AND created_at >= ? AND user_id IS NOT NULL"
                . ") AS actors";
            $actorResult = DB::selectOne($actorSql, [
                $accountId, $since, $accountId, $since,
                $accountId, $since, $accountId, $since,
            ]);
            $uniqueActors = (int) ($actorResult->cnt ?? 0);
        } catch (\Throwable $e) {
            $uniqueActors = 0;
        }

        return response()->json(['data' => [
            'total' => $total,
            'today' => $todayCount,
            'unique_actors' => $uniqueActors,
            'period_days' => $days,
            'modules' => $moduleStats,
        ]]);
    }

    /**
     * GET /api/admin/audit-logs — Admin console cross-tenant audit view.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->input('per_page', 50)), 200);
        $page = max((int) ($request->input('page', 1)), 1);
        $offset = ($page - 1) * $perPage;
        $accountId = $request->input('account_id');
        $category = $request->input('category');
        $severity = $request->input('severity');
        $action = $request->input('action');
        $from = $request->input('from');
        $to = $request->input('to');

        $query = AdminAuditLog::query()
            ->orderBy('created_at', 'desc');

        if ($category) {
            $query->ofCategory($category);
        }
        if ($severity) {
            $query->ofSeverity($severity);
        }
        if ($action) {
            $query->ofAction($action);
        }
        if ($accountId) {
            $query->forTargetAccount($accountId);
        }
        if ($from || $to) {
            $query->dateRange($from, $to);
        }

        $total = $query->count();
        $results = $query->skip($offset)->take($perPage)->get();

        return response()->json([
            'data' => $results->map->toPortalArray(),
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    /**
     * GET /api/admin/customer-audit-logs — Admin console view of customer audit events.
     *
     * Reuses the same UNION ALL query as the customer portal but accepts
     * an explicit account_id parameter instead of relying on tenant context.
     */
    public function adminCustomerIndex(Request $request): JsonResponse
    {
        $accountId = $request->input('account_id');
        if (!$accountId) {
            return response()->json(['error' => 'account_id is required'], 422);
        }

        $module = $request->input('module');
        $category = $request->input('category');
        $action = $request->input('action');
        $userId = $request->input('user_id');
        $from = $request->input('from');
        $to = $request->input('to');
        $search = $request->input('search');
        $perPage = min((int) ($request->input('per_page', 50)), 200);
        $page = max((int) ($request->input('page', 1)), 1);
        $offset = ($page - 1) * $perPage;

        $sources = self::CUSTOMER_SOURCES;
        if ($module) {
            $sources = array_filter($sources, fn($s) => $s['module'] === $module);
        }
        if ($category) {
            $sources = array_filter($sources, fn($s) => $s['category'] === $category);
        }

        if (empty($sources)) {
            return response()->json([
                'data' => [],
                'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => 0],
            ]);
        }

        $unionParts = [];
        $bindings = [];

        foreach ($sources as $key => $src) {
            $sql = $this->buildSourceQuery($src, $accountId, $action, $userId, $from, $to, $search, $bindings);
            if ($sql) {
                $unionParts[] = $sql;
            }
        }

        if (empty($unionParts)) {
            return response()->json([
                'data' => [],
                'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => 0],
            ]);
        }

        $unionSql = implode(' UNION ALL ', $unionParts);

        $countSql = "SELECT COUNT(*) as total FROM ({$unionSql}) AS unified_audit";
        $total = DB::selectOne($countSql, $bindings)->total ?? 0;

        $dataSql = "SELECT * FROM ({$unionSql}) AS unified_audit ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $dataBindings = array_merge($bindings, [$perPage, $offset]);
        $rows = DB::select($dataSql, $dataBindings);

        $data = array_map(function ($row) {
            return [
                'id' => $row->id,
                'module' => $row->module,
                'category' => $row->category,
                'action' => $row->action,
                'user_id' => $row->user_id,
                'user_name' => $row->user_name,
                'details' => $row->details,
                'metadata' => json_decode($row->metadata ?? '{}', true),
                'ip_address' => $row->ip_address,
                'created_at' => $row->created_at,
            ];
        }, $rows);

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => (int) $total,
                'last_page' => max(1, (int) ceil($total / $perPage)),
            ],
        ]);
    }

    /**
     * Build a SELECT ... UNION-compatible query for a single audit source.
     */
    private function buildSourceQuery(
        array $src,
        string $accountId,
        ?string $action,
        ?string $userId,
        ?string $from,
        ?string $to,
        ?string $search,
        array &$bindings
    ): ?string {
        $table = $src['table'];
        $module = $src['module'];
        $category = $src['category'];

        // Check if the table exists to avoid errors
        try {
            DB::selectOne("SELECT 1 FROM {$table} LIMIT 0");
        } catch (\Throwable $e) {
            return null;
        }

        // Build the normalized SELECT
        if (!empty($src['custom_select'])) {
            $select = $this->buildCustomSelect($table, $module, $category);
        } else {
            $entityCol = $src['entity_col'] ? ", {$src['entity_col']} AS entity_id" : ", NULL::UUID AS entity_id";
            $select = "SELECT id, '{$module}' AS module, '{$category}' AS category, action, user_id, user_name, details, metadata::TEXT AS metadata, ip_address::TEXT AS ip_address, created_at{$entityCol} FROM {$table}";
        }

        $conditions = [];

        // Account filter (most tables use account_id, auth_audit_log uses tenant_id)
        if ($table === 'auth_audit_log') {
            $conditions[] = 'tenant_id = ?';
        } else {
            $conditions[] = 'account_id = ?';
        }
        $bindings[] = $accountId;

        // Extra where clause (e.g., module filter for user_audit_log)
        if (!empty($src['extra_where'])) {
            $conditions[] = $src['extra_where'];
        }

        if ($action) {
            if ($table === 'auth_audit_log') {
                $conditions[] = 'event_type = ?';
            } else {
                $conditions[] = 'action = ?';
            }
            $bindings[] = $action;
        }

        if ($userId) {
            if ($table === 'auth_audit_log') {
                $conditions[] = 'actor_id = ?';
            } else {
                $conditions[] = 'user_id = ?';
            }
            $bindings[] = $userId;
        }

        if ($from) {
            $conditions[] = 'created_at >= ?';
            $bindings[] = $from;
        }
        if ($to) {
            $conditions[] = 'created_at <= ?';
            $bindings[] = $to;
        }

        if ($search) {
            // Escape LIKE wildcards to prevent pattern manipulation
            $escapedSearch = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $search);
            if ($table === 'auth_audit_log') {
                $conditions[] = "(event_type ILIKE ? ESCAPE '\\' OR actor_email ILIKE ? ESCAPE '\\')";
            } else {
                $conditions[] = "(action ILIKE ? ESCAPE '\\' OR COALESCE(details, '') ILIKE ? ESCAPE '\\')";
            }
            $bindings[] = "%{$escapedSearch}%";
            $bindings[] = "%{$escapedSearch}%";
        }

        $where = !empty($conditions) ? ' WHERE ' . implode(' AND ', $conditions) : '';

        return "({$select}{$where})";
    }

    /**
     * Build custom SELECT for tables with non-standard column names.
     */
    private function buildCustomSelect(string $table, string $module, string $category): string
    {
        return match ($table) {
            'auth_audit_log' => "SELECT id::TEXT AS id, '{$module}' AS module, '{$category}' AS category, event_type AS action, actor_id AS user_id, actor_email AS user_name, NULL AS details, COALESCE(metadata, '{}')::TEXT AS metadata, ip_address::TEXT AS ip_address, created_at, NULL::UUID AS entity_id FROM auth_audit_log",

            'api_connection_audit_events' => "SELECT id::TEXT AS id, '{$module}' AS module, '{$category}' AS category, event_type AS action, actor_id AS user_id, actor_name AS user_name, NULL AS details, COALESCE(metadata, '{}')::TEXT AS metadata, ip_address::TEXT AS ip_address, created_at, api_connection_id AS entity_id FROM api_connection_audit_events",

            'message_template_audit_log' => "SELECT id::TEXT AS id, '{$module}' AS module, '{$category}' AS category, action, user_id, user_name, details, '{}'::TEXT AS metadata, NULL AS ip_address, created_at, template_id AS entity_id FROM message_template_audit_log",

            'email_to_sms_audit_log' => "SELECT id::TEXT AS id, '{$module}' AS module, '{$category}' AS category, action, user_id, user_name, NULL AS details, COALESCE(metadata, '{}')::TEXT AS metadata, ip_address::TEXT AS ip_address, created_at, setup_id AS entity_id FROM email_to_sms_audit_log",

            default => "SELECT id::TEXT AS id, '{$module}' AS module, '{$category}' AS category, action, user_id, user_name, details, COALESCE(metadata, '{}')::TEXT AS metadata, ip_address::TEXT AS ip_address, created_at, NULL::UUID AS entity_id FROM {$table}",
        };
    }
}
