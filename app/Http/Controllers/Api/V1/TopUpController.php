<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Billing\AutoTopUpEvent;
use App\Services\Billing\AutoTopUpService;
use App\Services\Billing\StripeCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TopUpController extends Controller
{
    public function __construct(
        private StripeCheckoutService $stripeService,
        private AutoTopUpService $autoTopUpService,
    ) {}

    /**
     * POST /api/v1/topup/checkout-session
     * Create a Stripe Checkout session for a manual top-up.
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
     * Get auto top-up configuration with daily stats.
     */
    public function getAutoTopUp(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id ?? session('customer_tenant_id');
        $config = $this->autoTopUpService->getConfig($accountId);

        if (!$config) {
            return response()->json([
                'success' => true,
                'data' => null,
                'daily_stats' => ['count' => 0, 'value' => '0.00'],
            ]);
        }

        $dailyStats = $this->autoTopUpService->getDailyStats($accountId);

        return response()->json([
            'success' => true,
            'data' => $config->toPortalArray(),
            'daily_stats' => $dailyStats,
        ]);
    }

    /**
     * PUT /api/v1/topup/auto-topup
     * Update auto top-up configuration.
     */
    public function updateAutoTopUp(Request $request): JsonResponse
    {
        $account = $request->user()->account;

        // Only prepay accounts can use auto top-up
        if ($account->billing_type !== 'prepay') {
            return response()->json([
                'success' => false,
                'message' => 'Auto top-up is only available for prepay accounts.',
            ], 422);
        }

        // Check if admin-locked
        $existingConfig = $this->autoTopUpService->getConfig($account->id);
        if ($existingConfig && $existingConfig->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Auto top-up has been locked by support. Please contact us for assistance.',
            ], 403);
        }

        $minAmount = config('billing.auto_topup.min_amount', '5.00');
        $maxAmount = config('billing.auto_topup.max_amount', '50000.00');
        $maxPerDay = config('billing.auto_topup.max_per_day', 3);
        $maxDailyCap = config('billing.auto_topup.max_daily_cap', '100000.00');

        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean',
            'threshold_amount' => "required_if:enabled,true|nullable|numeric|min:1|max:{$maxAmount}",
            'topup_amount' => "required_if:enabled,true|nullable|numeric|min:{$minAmount}|max:{$maxAmount}",
            'max_topups_per_day' => "sometimes|integer|min:1|max:{$maxPerDay}",
            'daily_topup_cap' => "sometimes|nullable|numeric|min:0|max:{$maxDailyCap}",
            'min_minutes_between_topups' => 'sometimes|integer|min:0',
            'notify_email_success' => 'sometimes|boolean',
            'notify_email_failure' => 'sometimes|boolean',
            'notify_inapp_success' => 'sometimes|boolean',
            'notify_inapp_failure' => 'sometimes|boolean',
            'notify_requires_action' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // If enabling, validate payment method exists
        if ($request->boolean('enabled')) {
            if (!$existingConfig || !$existingConfig->hasValidPaymentMethod()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please add a payment method before enabling auto top-up.',
                ], 422);
            }
        }

        // Validate daily_topup_cap >= topup_amount if both set
        $topupAmount = $request->input('topup_amount', $existingConfig?->topup_amount ?? 0);
        $dailyCap = $request->input('daily_topup_cap');
        if ($dailyCap !== null && bccomp($dailyCap, $topupAmount, 4) < 0) {
            return response()->json([
                'success' => false,
                'errors' => ['daily_topup_cap' => ['Daily cap must be at least equal to the top-up amount.']],
            ], 422);
        }

        $data = $request->only([
            'enabled', 'threshold_amount', 'topup_amount',
            'max_topups_per_day', 'daily_topup_cap', 'min_minutes_between_topups',
            'notify_email_success', 'notify_email_failure',
            'notify_inapp_success', 'notify_inapp_failure', 'notify_requires_action',
        ]);

        // Reset failure count when re-enabling
        if ($request->boolean('enabled') && $existingConfig && !$existingConfig->enabled) {
            $data['consecutive_failure_count'] = 0;
        }

        $config = $this->autoTopUpService->updateConfig($account->id, $data, $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $config->toPortalArray(),
        ]);
    }

    /**
     * POST /api/v1/topup/auto-topup/disable
     * Disable auto top-up.
     */
    public function disableAutoTopUp(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id ?? session('customer_tenant_id');
        $config = $this->autoTopUpService->getConfig($accountId);

        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Auto top-up is not configured.'], 404);
        }

        $this->autoTopUpService->updateConfig($accountId, ['enabled' => false], $request->user()->id);

        return response()->json(['success' => true, 'message' => 'Auto top-up has been disabled.']);
    }

    /**
     * POST /api/v1/topup/auto-topup/setup-payment-method
     * Create a Stripe Checkout session in setup mode for saving a payment method.
     */
    public function setupPaymentMethod(Request $request): JsonResponse
    {
        $account = $request->user()->account;

        if ($account->billing_type !== 'prepay') {
            return response()->json([
                'success' => false,
                'message' => 'Auto top-up is only available for prepay accounts.',
            ], 422);
        }

        $session = $this->autoTopUpService->setupPaymentMethod($account->id, $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $session,
        ]);
    }

    /**
     * POST /api/v1/topup/auto-topup/payment-method/remove
     * Remove the stored payment method.
     */
    public function removePaymentMethod(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id ?? session('customer_tenant_id');

        $this->autoTopUpService->removePaymentMethod($accountId, $request->user()->id);

        return response()->json(['success' => true, 'message' => 'Payment method removed. Auto top-up has been disabled.']);
    }

    /**
     * GET /api/v1/topup/auto-topup/events
     * List auto top-up events for the customer.
     */
    public function listEvents(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id ?? session('customer_tenant_id');

        $events = AutoTopUpEvent::forAccount($accountId)
            ->orderByDesc('created_at')
            ->limit(min($request->integer('limit', 50), 100))
            ->get()
            ->map(fn ($e) => $e->toPortalArray());

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }
}
