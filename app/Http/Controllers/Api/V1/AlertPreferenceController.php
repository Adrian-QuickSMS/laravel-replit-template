<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Alerting\AlertPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AlertPreferenceController extends Controller
{
    /**
     * GET /api/v1/alerts/preferences
     */
    public function index(Request $request): JsonResponse
    {
        $preferences = AlertPreference::forTenant($request->user()->account_id)
            ->forUser($request->user()->id)
            ->get()
            ->keyBy('category');

        // Include all categories with defaults for those not yet configured
        $allCategories = config('alerting.categories', []);
        $result = [];

        foreach ($allCategories as $key => $label) {
            $pref = $preferences->get($key);
            $result[] = [
                'category' => $key,
                'label' => $label,
                'channels' => $pref ? $pref->channels : ['in_app', 'email'],
                'is_muted' => $pref ? $pref->isCurrentlyMuted() : false,
                'muted_until' => $pref?->muted_until,
            ];
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    /**
     * PUT /api/v1/alerts/preferences
     *
     * Update preferences for a specific category.
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|string|in:' . implode(',', array_keys(config('alerting.categories', []))),
            'channels' => 'sometimes|array',
            'channels.*' => 'string|in:' . implode(',', config('alerting.channels', [])),
            'is_muted' => 'sometimes|boolean',
            'muted_until' => 'sometimes|nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $preference = AlertPreference::updateOrCreate(
            [
                'tenant_id' => $request->user()->account_id,
                'user_id' => $request->user()->id,
                'category' => $validated['category'],
            ],
            array_filter([
                'channels' => $validated['channels'] ?? null,
                'is_muted' => $validated['is_muted'] ?? null,
                'muted_until' => $validated['muted_until'] ?? null,
            ], fn ($v) => $v !== null)
        );

        return response()->json(['success' => true, 'data' => $preference]);
    }
}
