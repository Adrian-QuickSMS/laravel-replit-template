<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Billing\BalanceAlertConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BalanceAlertController extends Controller
{
    /**
     * GET /api/v1/alerts/balance
     */
    public function index(Request $request): JsonResponse
    {
        $alerts = BalanceAlertConfig::where('account_id', $request->user()->account_id)
            ->orderBy('threshold_percentage')
            ->get();

        return response()->json(['success' => true, 'data' => $alerts]);
    }

    /**
     * POST /api/v1/alerts/balance
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'threshold_percentage' => 'required|integer|min:1|max:99',
            'notify_customer' => 'sometimes|boolean',
            'notify_admin' => 'sometimes|boolean',
            'cooldown_hours' => 'sometimes|integer|min:1|max:168',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $alert = BalanceAlertConfig::create([
            'account_id' => $request->user()->account_id,
            'threshold_percentage' => $request->input('threshold_percentage'),
            'notify_customer' => $request->input('notify_customer', true),
            'notify_admin' => $request->input('notify_admin', true),
            'cooldown_hours' => $request->input('cooldown_hours', 24),
        ]);

        return response()->json(['success' => true, 'data' => $alert], 201);
    }

    /**
     * PUT /api/v1/alerts/balance/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $alert = BalanceAlertConfig::where('id', $id)
            ->where('account_id', $request->user()->account_id)
            ->firstOrFail();

        $alert->update($request->only([
            'threshold_percentage', 'notify_customer', 'notify_admin', 'cooldown_hours',
        ]));

        return response()->json(['success' => true, 'data' => $alert]);
    }

    /**
     * DELETE /api/v1/alerts/balance/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $alert = BalanceAlertConfig::where('id', $id)
            ->where('account_id', $request->user()->account_id)
            ->firstOrFail();

        $alert->delete();

        return response()->json(['success' => true]);
    }
}
