<?php

namespace App\Http\Controllers;

use App\Models\AccountAuditLog;
use App\Models\AccountIpAllowlist;
use App\Models\AccountSettings;
use App\Services\IpAllowlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * GREEN SIDE: Security Settings API for the Customer Portal.
 *
 * RESTful endpoints for:
 *   - Message Data Retention
 *   - Data Visibility & Masking
 *   - Anti-Flood Protection
 *   - Out-of-Hours Sending Restriction
 *   - Login IP Allowlist
 *
 * All endpoints require customer.auth + manage_security permission.
 * All settings changes are audit-logged.
 */
class SecuritySettingsController extends Controller
{
    public function __construct(
        private IpAllowlistService $ipAllowlistService,
    ) {}

    // =========================================================================
    // READ — Get all security settings for the account
    // =========================================================================

    /**
     * GET /api/account/security/settings
     */
    public function index(Request $request): JsonResponse
    {
        $accountId = session('customer_tenant_id');

        if (!$accountId) {
            return response()->json(['status' => 'error', 'message' => 'Not authenticated'], 401);
        }

        $settings = AccountSettings::withoutGlobalScopes()->find($accountId);

        if (!$settings) {
            return response()->json(['status' => 'error', 'message' => 'Account settings not found'], 404);
        }

        $ipEntries = AccountIpAllowlist::withoutGlobalScopes()
            ->where('tenant_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map->toPortalArray();

        return response()->json([
            'status' => 'success',
            'data' => [
                'retention' => [
                    'message_retention_days' => $settings->message_retention_days ?? 180,
                ],
                'masking' => [
                    'config' => is_string($settings->data_masking_config)
                        ? json_decode($settings->data_masking_config, true)
                        : ($settings->data_masking_config ?? [
                            'mask_mobile' => false,
                            'mask_content' => false,
                            'mask_sent_time' => false,
                            'mask_delivered_time' => false,
                        ]),
                    'owner_bypass_masking' => $settings->owner_bypass_masking ?? true,
                ],
                'anti_flood' => [
                    'enabled' => $settings->anti_flood_enabled ?? false,
                    'mode' => $settings->anti_flood_mode ?? 'off',
                    'window_hours' => $settings->anti_flood_window_hours ?? 2,
                ],
                'out_of_hours' => [
                    'enabled' => $settings->out_of_hours_enabled ?? false,
                    'start' => $settings->out_of_hours_start ?? '21:00',
                    'end' => $settings->out_of_hours_end ?? '08:00',
                    'action' => $settings->out_of_hours_action ?? 'reject',
                    'timezone' => $settings->timezone ?? 'Europe/London',
                ],
                'ip_allowlist' => [
                    'enabled' => $settings->ip_allowlist_enabled ?? false,
                    'entries' => $ipEntries,
                    'limit' => 50,
                ],
            ],
        ]);
    }

    // =========================================================================
    // MESSAGE DATA RETENTION
    // =========================================================================

    /**
     * PUT /api/account/security/retention
     */
    public function updateRetention(Request $request): JsonResponse
    {
        $request->validate([
            'message_retention_days' => 'required|integer|in:30,60,90,120,150,180',
        ]);

        $accountId = session('customer_tenant_id');
        $userId = session('customer_user_id');

        $settings = AccountSettings::withoutGlobalScopes()->find($accountId);
        if (!$settings) {
            return response()->json(['status' => 'error', 'message' => 'Account settings not found'], 404);
        }

        $oldValue = $settings->message_retention_days;
        $newValue = (int) $request->input('message_retention_days');

        DB::table('account_settings')
            ->where('account_id', $accountId)
            ->update([
                'message_retention_days' => $newValue,
                'updated_at' => now(),
            ]);

        $this->auditLog($accountId, $userId, 'retention_policy_changed', "Retention changed from {$oldValue} to {$newValue} days", [
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => ['message_retention_days' => $newValue],
        ]);
    }

    // =========================================================================
    // DATA VISIBILITY & MASKING
    // =========================================================================

    /**
     * PUT /api/account/security/masking
     */
    public function updateMasking(Request $request): JsonResponse
    {
        $request->validate([
            'mask_mobile' => 'required|boolean',
            'mask_content' => 'required|boolean',
            'mask_sent_time' => 'required|boolean',
            'mask_delivered_time' => 'required|boolean',
            'owner_bypass_masking' => 'sometimes|boolean',
        ]);

        $accountId = session('customer_tenant_id');
        $userId = session('customer_user_id');

        $settings = AccountSettings::withoutGlobalScopes()->find($accountId);
        if (!$settings) {
            return response()->json(['status' => 'error', 'message' => 'Account settings not found'], 404);
        }

        $maskingConfig = [
            'mask_mobile' => (bool) $request->input('mask_mobile'),
            'mask_content' => (bool) $request->input('mask_content'),
            'mask_sent_time' => (bool) $request->input('mask_sent_time'),
            'mask_delivered_time' => (bool) $request->input('mask_delivered_time'),
        ];

        $updates = ['data_masking_config' => json_encode($maskingConfig)];

        if ($request->has('owner_bypass_masking')) {
            $updates['owner_bypass_masking'] = (bool) $request->input('owner_bypass_masking');
        }

        // Use raw SQL for JSONB column
        DB::table('account_settings')
            ->where('account_id', $accountId)
            ->update([
                'data_masking_config' => json_encode($maskingConfig),
                'owner_bypass_masking' => $updates['owner_bypass_masking'] ?? $settings->owner_bypass_masking,
                'updated_at' => now(),
            ]);

        $this->auditLog($accountId, $userId, 'masking_config_changed', 'Data masking settings updated', [
            'config' => $maskingConfig,
            'owner_bypass' => $updates['owner_bypass_masking'] ?? $settings->owner_bypass_masking,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'config' => $maskingConfig,
                'owner_bypass_masking' => $updates['owner_bypass_masking'] ?? $settings->owner_bypass_masking,
            ],
        ]);
    }

    // =========================================================================
    // ANTI-FLOOD PROTECTION
    // =========================================================================

    /**
     * PUT /api/account/security/anti-flood
     */
    public function updateAntiFlood(Request $request): JsonResponse
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'mode' => 'required|string|in:enforce,monitor,off',
            'window_hours' => 'required|integer|min:2|max:48',
        ]);

        $accountId = session('customer_tenant_id');
        $userId = session('customer_user_id');

        $settings = AccountSettings::withoutGlobalScopes()->find($accountId);
        if (!$settings) {
            return response()->json(['status' => 'error', 'message' => 'Account settings not found'], 404);
        }

        $enabled = (bool) $request->input('enabled');
        $mode = $request->input('mode');
        $windowHours = (int) $request->input('window_hours');

        // If disabling, force mode to 'off'
        if (!$enabled) {
            $mode = 'off';
        }

        DB::table('account_settings')
            ->where('account_id', $accountId)
            ->update([
                'anti_flood_enabled' => $enabled,
                'anti_flood_mode' => $mode,
                'anti_flood_window_hours' => $windowHours,
                'updated_at' => now(),
            ]);

        $this->auditLog($accountId, $userId, 'anti_flood_settings_changed', "Anti-flood {$mode}, window: {$windowHours}h", [
            'enabled' => $enabled,
            'mode' => $mode,
            'window_hours' => $windowHours,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'enabled' => $enabled,
                'mode' => $mode,
                'window_hours' => $windowHours,
            ],
        ]);
    }

    // =========================================================================
    // OUT-OF-HOURS RESTRICTION
    // =========================================================================

    /**
     * PUT /api/account/security/out-of-hours
     */
    public function updateOutOfHours(Request $request): JsonResponse
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'start' => 'sometimes|date_format:H:i',
            'end' => 'sometimes|date_format:H:i',
            'action' => 'sometimes|string|in:reject,hold',
        ]);

        $accountId = session('customer_tenant_id');
        $userId = session('customer_user_id');

        $settings = AccountSettings::withoutGlobalScopes()->find($accountId);
        if (!$settings) {
            return response()->json(['status' => 'error', 'message' => 'Account settings not found'], 404);
        }

        $enabled = (bool) $request->input('enabled');
        $start = $request->input('start', $settings->out_of_hours_start ?? '21:00');
        $end = $request->input('end', $settings->out_of_hours_end ?? '08:00');
        $action = $request->input('action', $settings->out_of_hours_action ?? 'reject');

        // Validate that start != end
        if ($start === $end) {
            return response()->json([
                'status' => 'error',
                'message' => 'Start and end times cannot be the same',
            ], 422);
        }

        DB::table('account_settings')
            ->where('account_id', $accountId)
            ->update([
                'out_of_hours_enabled' => $enabled,
                'out_of_hours_start' => $start,
                'out_of_hours_end' => $end,
                'out_of_hours_action' => $action,
                'updated_at' => now(),
            ]);

        $this->auditLog($accountId, $userId, 'out_of_hours_settings_changed',
            $enabled ? "Out-of-hours enabled: {$start}–{$end} ({$action})" : 'Out-of-hours disabled',
            [
                'enabled' => $enabled,
                'start' => $start,
                'end' => $end,
                'action' => $action,
            ]
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'enabled' => $enabled,
                'start' => $start,
                'end' => $end,
                'action' => $action,
            ],
        ]);
    }

    // =========================================================================
    // IP ALLOWLIST
    // =========================================================================

    /**
     * GET /api/account/security/ip-allowlist
     */
    public function listIps(Request $request): JsonResponse
    {
        $accountId = session('customer_tenant_id');

        $entries = AccountIpAllowlist::withoutGlobalScopes()
            ->where('tenant_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map->toPortalArray();

        $settings = AccountSettings::withoutGlobalScopes()->find($accountId);

        return response()->json([
            'status' => 'success',
            'data' => [
                'enabled' => $settings->ip_allowlist_enabled ?? false,
                'entries' => $entries,
                'limit' => 50,
            ],
        ]);
    }

    /**
     * POST /api/account/security/ip-allowlist
     */
    public function addIp(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address' => 'required|string|max:45',
            'label' => 'nullable|string|max:100',
        ]);

        $accountId = session('customer_tenant_id');
        $userId = session('customer_user_id');

        try {
            $entry = $this->ipAllowlistService->addIp(
                $accountId,
                $request->input('ip_address'),
                $request->input('label'),
                $userId
            );

            $this->auditLog($accountId, $userId, 'ip_allowlist_added',
                "IP added: {$entry->ip_address}" . ($entry->label ? " ({$entry->label})" : ''),
                ['ip_address' => $entry->ip_address, 'label' => $entry->label]
            );

            return response()->json([
                'status' => 'success',
                'data' => $entry->toPortalArray(),
            ], 201);

        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409);
        }
    }

    /**
     * DELETE /api/account/security/ip-allowlist/{id}
     */
    public function removeIp(Request $request, string $id): JsonResponse
    {
        $accountId = session('customer_tenant_id');
        $userId = session('customer_user_id');

        $entry = AccountIpAllowlist::withoutGlobalScopes()
            ->where('tenant_id', $accountId)
            ->where('id', $id)
            ->first();

        if (!$entry) {
            return response()->json(['status' => 'error', 'message' => 'IP entry not found'], 404);
        }

        $this->ipAllowlistService->removeIp($accountId, $id);

        $this->auditLog($accountId, $userId, 'ip_allowlist_removed',
            "IP removed: {$entry->ip_address}" . ($entry->label ? " ({$entry->label})" : ''),
            ['ip_address' => $entry->ip_address, 'label' => $entry->label]
        );

        return response()->json(['status' => 'success']);
    }

    /**
     * PUT /api/account/security/ip-allowlist/toggle
     */
    public function toggleIpAllowlist(Request $request): JsonResponse
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $accountId = session('customer_tenant_id');
        $userId = session('customer_user_id');
        $enabled = (bool) $request->input('enabled');
        $clientIp = $request->ip();

        try {
            $this->ipAllowlistService->toggleEnabled($accountId, $enabled, $clientIp, $userId);

            $this->auditLog($accountId, $userId,
                $enabled ? 'ip_allowlist_enabled' : 'ip_allowlist_disabled',
                $enabled ? "IP allowlist enabled (caller IP: {$clientIp})" : 'IP allowlist disabled',
                ['enabled' => $enabled, 'caller_ip' => $clientIp]
            );

            return response()->json([
                'status' => 'success',
                'data' => ['enabled' => $enabled],
            ]);

        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 409);
        }
    }

    /**
     * GET /api/account/security/ip-allowlist/current-ip
     */
    public function getCurrentIp(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => ['ip_address' => $request->ip()],
        ]);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Record an audit log entry. Wrapped in try/catch — never blocks business logic.
     */
    private function auditLog(string $accountId, ?string $userId, string $action, string $details, array $metadata = []): void
    {
        try {
            $user = $userId ? \App\Models\User::withoutGlobalScopes()->find($userId) : null;

            AccountAuditLog::record(
                accountId: $accountId,
                action: $action,
                userId: $userId,
                userName: $user ? ($user->first_name . ' ' . $user->last_name) : null,
                details: $details,
                metadata: $metadata,
            );
        } catch (\Throwable $e) {
            Log::warning('[SecuritySettingsController] Audit log failed', [
                'error' => $e->getMessage(),
                'action' => $action,
            ]);
        }
    }
}
