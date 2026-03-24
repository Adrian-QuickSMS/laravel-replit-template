<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alerting\AlertChannelConfig;
use App\Models\Alerting\AlertHistory;
use App\Models\Alerting\AlertPreference;
use App\Models\Alerting\AlertRule;
use App\Validation\WebhookUrlValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminAlertRuleController extends Controller
{
    /**
     * GET /admin/api/alerts/rules
     *
     * List all system-level and admin alert rules.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AlertRule::whereNull('tenant_id');

        if ($category = $request->input('category')) {
            $query->forCategory($category);
        }

        $rules = $query->orderBy('category')
            ->orderBy('trigger_key')
            ->get();

        return response()->json(['success' => true, 'data' => $rules]);
    }

    /**
     * POST /admin/api/alerts/rules
     */
    public function store(Request $request): JsonResponse
    {
        $allCategories = array_merge(
            array_keys(config('alerting.categories', [])),
            array_keys(config('alerting.admin_categories', []))
        );

        $validator = Validator::make($request->all(), [
            'category' => 'required|string|in:' . implode(',', $allCategories),
            'trigger_type' => 'required|string|in:' . implode(',', config('alerting.trigger_types', [])),
            'trigger_key' => 'required|string|max:100',
            'condition_operator' => 'required|string|in:' . implode(',', config('alerting.condition_operators', [])),
            'condition_value' => 'nullable|numeric',
            'channels' => 'sometimes|array',
            'channels.*' => 'string|in:' . implode(',', config('alerting.channels', [])),
            'frequency' => 'sometimes|string|in:' . implode(',', array_keys(config('alerting.frequencies', []))),
            'cooldown_minutes' => 'sometimes|integer|min:0|max:10080',
            'escalation_rules' => 'sometimes|nullable|array',
            'metadata' => 'sometimes|nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $rule = AlertRule::create([
            'tenant_id' => null, // Admin/system rule
            'user_id' => session('admin_user_id'),
            'category' => $request->input('category'),
            'trigger_type' => $request->input('trigger_type'),
            'trigger_key' => $request->input('trigger_key'),
            'condition_operator' => $request->input('condition_operator'),
            'condition_value' => $request->input('condition_value'),
            'channels' => $request->input('channels', ['in_app']),
            'frequency' => $request->input('frequency', 'instant'),
            'cooldown_minutes' => $request->input('cooldown_minutes', 60),
            'escalation_rules' => $request->input('escalation_rules'),
            'metadata' => $request->input('metadata'),
            'is_enabled' => true,
            'is_system_default' => false,
        ]);

        return response()->json(['success' => true, 'data' => $rule], 201);
    }

    /**
     * PUT /admin/api/alerts/rules/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $rule = AlertRule::whereNull('tenant_id')
            ->where('id', $id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'condition_value' => 'sometimes|numeric',
            'channels' => 'sometimes|array',
            'frequency' => 'sometimes|string',
            'cooldown_minutes' => 'sometimes|integer|min:0|max:10080',
            'escalation_rules' => 'sometimes|nullable|array',
            'is_enabled' => 'sometimes|boolean',
            'metadata' => 'sometimes|nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $rule->update($validator->validated());

        return response()->json(['success' => true, 'data' => $rule]);
    }

    /**
     * DELETE /admin/api/alerts/rules/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $rule = AlertRule::whereNull('tenant_id')
            ->where('id', $id)
            ->firstOrFail();

        if ($rule->is_system_default) {
            return response()->json([
                'success' => false,
                'error' => 'System default rules cannot be deleted. Disable them instead.',
            ], 403);
        }

        $rule->delete();

        return response()->json(['success' => true]);
    }

    /**
     * GET /admin/api/alerts/history
     *
     * Full alert history across all tenants.
     */
    public function history(Request $request): JsonResponse
    {
        $query = AlertHistory::orderBy('created_at', 'desc');

        if ($tenantId = $request->input('tenant_id')) {
            $query->forTenant($tenantId);
        }

        if ($category = $request->input('category')) {
            $query->forCategory($category);
        }

        if ($severity = $request->input('severity')) {
            $query->ofSeverity($severity);
        }

        if ($since = $request->input('since')) {
            $query->since($since);
        }

        $perPage = min((int) $request->input('per_page', 50), 200);
        $history = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $history->items(),
            'pagination' => [
                'total' => $history->total(),
                'per_page' => $history->perPage(),
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
            ],
        ]);
    }

    /**
     * GET /admin/api/alerts/dashboard
     *
     * Alert analytics and insights for admin dashboard.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $since = $request->input('since', now()->subDays(7)->toDateTimeString());

        // Most triggered alerts
        $mostTriggered = AlertHistory::since($since)
            ->selectRaw('trigger_key, category, count(*) as count')
            ->groupBy('trigger_key', 'category')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Alerts by severity
        $bySeverity = AlertHistory::since($since)
            ->selectRaw('severity, count(*) as count')
            ->groupBy('severity')
            ->get()
            ->pluck('count', 'severity');

        // Suppressed vs dispatched
        $dispatched = AlertHistory::since($since)->ofStatus('dispatched')->count();
        $suppressed = AlertHistory::since($since)
            ->where('status', 'like', 'suppressed%')
            ->count();
        $batched = AlertHistory::since($since)->ofStatus('batched')->count();

        // Recent critical alerts
        $recentCritical = AlertHistory::since($since)
            ->ofSeverity('critical')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'most_triggered' => $mostTriggered,
                'by_severity' => $bySeverity,
                'dispatched_count' => $dispatched,
                'suppressed_count' => $suppressed,
                'batched_count' => $batched,
                'recent_critical' => $recentCritical,
                'period_since' => $since,
            ],
        ]);
    }

    public function preferences(Request $request): JsonResponse
    {
        $adminUserId = session('admin_user_id');

        $preferences = AlertPreference::whereNull('tenant_id')
            ->where('user_id', $adminUserId)
            ->get()
            ->keyBy('category');

        $allCategories = array_merge(
            config('alerting.categories', []),
            config('alerting.admin_categories', [])
        );
        $result = [];

        foreach ($allCategories as $key => $label) {
            $pref = $preferences->get($key);
            $result[] = [
                'category' => $key,
                'label' => $label,
                'channels' => $pref ? $pref->channels : ['in_app', 'email'],
                'is_muted' => $pref ? $pref->isCurrentlyMuted() : false,
                'muted_until' => $pref?->muted_until?->toIso8601String(),
            ];
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    public function updatePreference(Request $request): JsonResponse
    {
        $allCategories = array_merge(
            array_keys(config('alerting.categories', [])),
            array_keys(config('alerting.admin_categories', []))
        );

        $validator = Validator::make($request->all(), [
            'category' => 'required|string|in:' . implode(',', $allCategories),
            'channels' => 'sometimes|array',
            'channels.*' => 'string|in:' . implode(',', config('alerting.channels', [])),
            'is_muted' => 'sometimes|boolean',
            'muted_until' => 'sometimes|nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $adminUserId = session('admin_user_id');

        $preference = AlertPreference::updateOrCreate(
            [
                'tenant_id' => null,
                'user_id' => $adminUserId,
                'category' => $validated['category'],
            ],
            array_filter([
                'channels' => $validated['channels'] ?? null,
                'is_muted' => $validated['is_muted'] ?? null,
                'muted_until' => $validated['muted_until'] ?? null,
            ], fn ($v) => $v !== null)
        );

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $preference->category,
                'channels' => $preference->channels,
                'is_muted' => $preference->isCurrentlyMuted(),
                'muted_until' => $preference->muted_until?->toIso8601String(),
            ],
        ]);
    }

    public function channels(Request $request): JsonResponse
    {
        $configs = AlertChannelConfig::whereNull('tenant_id')
            ->whereNull('user_id')
            ->get()
            ->map(function ($config) {
                return [
                    'id' => $config->id,
                    'channel' => $config->channel,
                    'config' => $config->safe_config,
                    'is_enabled' => $config->is_enabled,
                    'updated_at' => $config->updated_at,
                ];
            });

        return response()->json(['success' => true, 'data' => $configs]);
    }

    public function updateChannel(Request $request, string $channel): JsonResponse
    {
        $availableChannels = config('alerting.channels', []);
        if (!in_array($channel, $availableChannels)) {
            return response()->json(['success' => false, 'error' => 'Invalid channel.'], 422);
        }

        $existingConfig = AlertChannelConfig::whereNull('tenant_id')
            ->whereNull('user_id')
            ->where('channel', $channel)
            ->first();

        $hasConfigPayload = $request->has('config') && !empty($request->input('config'));

        if ($hasConfigPayload) {
            $rules = match ($channel) {
                'webhook' => [
                    'config.webhook_url' => 'required|url|max:500',
                    'config.hmac_secret' => 'sometimes|string|min:16|max:128',
                ],
                'slack' => [
                    'config.slack_webhook_url' => 'required|url|max:500',
                ],
                'teams' => [
                    'config.teams_webhook_url' => 'required|url|max:500',
                ],
                'email' => [
                    'config.email' => 'required|email|max:255',
                ],
                'sms' => [
                    'config.phone' => 'required|string|regex:/^[0-9+\s]{10,20}$/',
                ],
                default => [],
            };
        } else {
            $rules = [];
        }

        $rules['is_enabled'] = 'sometimes|boolean';

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $updateData = ['is_enabled' => $request->input('is_enabled', true)];

        if ($hasConfigPayload) {
            $configData = $request->input('config', []);

            $urlFields = ['webhook_url', 'slack_webhook_url', 'teams_webhook_url'];
            foreach ($urlFields as $field) {
                if (!empty($configData[$field])) {
                    $validation = WebhookUrlValidator::validate($configData[$field]);
                    if (!$validation['valid']) {
                        return response()->json([
                            'success' => false,
                            'error' => "Invalid {$field}: {$validation['error']}",
                        ], 422);
                    }
                }
            }

            if ($channel === 'webhook' && empty($configData['hmac_secret'])) {
                if ($existingConfig && !empty($existingConfig->config['hmac_secret'])) {
                    $configData['hmac_secret'] = $existingConfig->config['hmac_secret'];
                } else {
                    $configData['hmac_secret'] = Str::random(64);
                }
            }

            $updateData['config'] = $configData;
        }

        $channelConfig = AlertChannelConfig::updateOrCreate(
            [
                'tenant_id' => null,
                'user_id' => null,
                'channel' => $channel,
            ],
            $updateData
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $channelConfig->id,
                'channel' => $channelConfig->channel,
                'config' => $channelConfig->safe_config,
                'is_enabled' => $channelConfig->is_enabled,
            ],
        ]);
    }

    public function destroyChannel(string $channel): JsonResponse
    {
        $config = AlertChannelConfig::whereNull('tenant_id')
            ->whereNull('user_id')
            ->where('channel', $channel)
            ->first();

        if (!$config) {
            return response()->json(['success' => false, 'error' => 'Channel config not found.'], 404);
        }

        $config->delete();

        return response()->json(['success' => true]);
    }
}
