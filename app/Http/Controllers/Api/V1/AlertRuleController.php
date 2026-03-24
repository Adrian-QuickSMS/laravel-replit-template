<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Alerting\AlertRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AlertRuleController extends Controller
{
    /**
     * GET /api/v1/alerts/rules
     *
     * List alert rules for the authenticated user's account.
     * Includes both tenant-specific and system default rules.
     */
    public function index(Request $request): JsonResponse
    {
        $accountId = $request->user()->tenant_id;

        $query = AlertRule::where(function ($q) use ($accountId) {
            $q->where('tenant_id', $accountId)
                ->orWhere(function ($q2) {
                    $q2->whereNull('tenant_id')
                        ->where('is_system_default', true);
                });
        });

        if ($category = $request->input('category')) {
            $query->forCategory($category);
        }

        if ($triggerKey = $request->input('trigger_key')) {
            $query->forTriggerKey($triggerKey);
        }

        $perPage = min((int) $request->input('per_page', 25), 100);
        $rules = $query->orderBy('category')
            ->orderBy('trigger_key')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => collect($rules->items())->map->toPortalArray(),
            'pagination' => [
                'total' => $rules->total(),
                'per_page' => $rules->perPage(),
                'current_page' => $rules->currentPage(),
                'last_page' => $rules->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/v1/alerts/rules
     *
     * Create a custom alert rule.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|string|in:' . implode(',', array_keys(config('alerting.categories', []))),
            'trigger_type' => 'required|string|in:' . implode(',', config('alerting.trigger_types', [])),
            'trigger_key' => 'required|string|max:100',
            'condition_operator' => 'required|string|in:' . implode(',', config('alerting.condition_operators', [])),
            'condition_value' => 'nullable|numeric',
            'channels' => 'sometimes|array',
            'channels.*' => 'string|in:' . implode(',', config('alerting.channels', [])),
            'frequency' => 'sometimes|string|in:' . implode(',', array_keys(config('alerting.frequencies', []))),
            'cooldown_minutes' => 'sometimes|integer|min:0|max:10080', // max 1 week
            'escalation_rules' => 'sometimes|array',
            'escalation_rules.*.condition_value' => 'required|numeric',
            'escalation_rules.*.channels' => 'required|array',
            'metadata' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $rule = AlertRule::create([
            'tenant_id' => $request->user()->tenant_id,
            'user_id' => $request->user()->id,
            'category' => $request->input('category'),
            'trigger_type' => $request->input('trigger_type'),
            'trigger_key' => $request->input('trigger_key'),
            'condition_operator' => $request->input('condition_operator'),
            'condition_value' => $request->input('condition_value'),
            'channels' => $request->input('channels', ['in_app', 'email']),
            'frequency' => $request->input('frequency', 'instant'),
            'cooldown_minutes' => $request->input('cooldown_minutes', config('alerting.default_cooldown_minutes', 60)),
            'escalation_rules' => $request->input('escalation_rules'),
            'metadata' => $request->input('metadata'),
            'is_enabled' => true,
            'is_system_default' => false,
        ]);

        return response()->json(['success' => true, 'data' => $rule->toPortalArray()], 201);
    }

    /**
     * GET /api/v1/alerts/rules/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $rule = AlertRule::where('id', $id)
            ->where(function ($q) use ($request) {
                $q->where('tenant_id', $request->user()->tenant_id)
                    ->orWhere(function ($q2) {
                        $q2->whereNull('tenant_id')->where('is_system_default', true);
                    });
            })
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $rule->toPortalArray()]);
    }

    /**
     * PUT /api/v1/alerts/rules/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $rule = AlertRule::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'condition_value' => 'sometimes|numeric',
            'channels' => 'sometimes|array',
            'channels.*' => 'string|in:' . implode(',', config('alerting.channels', [])),
            'frequency' => 'sometimes|string|in:' . implode(',', array_keys(config('alerting.frequencies', []))),
            'cooldown_minutes' => 'sometimes|integer|min:0|max:10080',
            'escalation_rules' => 'sometimes|nullable|array',
            'is_enabled' => 'sometimes|boolean',
            'metadata' => 'sometimes|nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $rule->update($validator->validated());

        return response()->json(['success' => true, 'data' => $rule->toPortalArray()]);
    }

    /**
     * DELETE /api/v1/alerts/rules/{id}
     *
     * Cannot delete system default rules — only disable them.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $rule = AlertRule::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
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
}
