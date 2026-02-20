<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Billing\StripeCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TopUpController extends Controller
{
    public function __construct(
        private StripeCheckoutService $stripeService,
    ) {}

    /**
     * POST /api/v1/topup/checkout-session
     * Create a Stripe Checkout session for a top-up.
     */
    public function createCheckoutSession(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:5|max:50000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $account = $request->user()->account;
        $amount = number_format((float)$request->input('amount'), 4, '.', '');

        $session = $this->stripeService->createCheckoutSession($account, $amount, $account->currency);

        return response()->json([
            'success' => true,
            'data' => $session,
        ]);
    }

    /**
     * GET /api/v1/topup/auto-topup
     */
    public function getAutoTopUp(Request $request): JsonResponse
    {
        $config = $this->stripeService->getAutoTopUpConfig($request->user()->account_id);

        return response()->json([
            'success' => true,
            'data' => $config ? [
                'enabled' => $config->enabled,
                'threshold_amount' => $config->threshold_amount,
                'topup_amount' => $config->topup_amount,
                'max_topups_per_day' => $config->max_topups_per_day,
            ] : null,
        ]);
    }

    /**
     * PUT /api/v1/topup/auto-topup
     */
    public function updateAutoTopUp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean',
            'threshold_amount' => 'required_if:enabled,true|numeric|min:1',
            'topup_amount' => 'required_if:enabled,true|numeric|min:5',
            'max_topups_per_day' => 'sometimes|integer|min:1|max:10',
            'stripe_payment_method_id' => 'required_if:enabled,true|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $config = $this->stripeService->updateAutoTopUpConfig(
            $request->user()->account_id,
            $request->only(['enabled', 'threshold_amount', 'topup_amount', 'max_topups_per_day', 'stripe_customer_id', 'stripe_payment_method_id'])
        );

        return response()->json([
            'success' => true,
            'data' => $config,
        ]);
    }
}
