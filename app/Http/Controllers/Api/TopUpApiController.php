<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use App\Services\HubSpotProductService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TopUpApiController extends Controller
{
    private StripeService $stripeService;
    private HubSpotProductService $productService;

    public function __construct(StripeService $stripeService, HubSpotProductService $productService)
    {
        $this->stripeService = $stripeService;
        $this->productService = $productService;
    }

    public function createCheckoutSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tier' => 'required|string|in:starter,enterprise,bespoke',
            'amount' => 'required|numeric|min:100|max:50000',
            'currency' => 'nullable|string|in:GBP,EUR,USD',
        ]);

        $tier = $validated['tier'];
        $amount = $validated['amount'];
        $currency = $validated['currency'] ?? 'GBP';

        $productsResult = $this->productService->fetchProducts();
        $products = $productsResult['products'] ?? [];
        $smsProduct = $products['sms'] ?? null;

        if ($smsProduct) {
            $effectiveRate = ($tier === 'enterprise' || $tier === 'bespoke')
                ? ($smsProduct['price_enterprise'] ?? $smsProduct['price'])
                : $smsProduct['price'];
        } else {
            $effectiveRate = $tier === 'enterprise' ? 0.0285 : 0.035;
        }

        $accountId = $this->getCurrentAccountId();

        $this->logAudit('topup_attempt', [
            'tier' => $tier,
            'amount' => $amount,
            'currency' => $currency,
            'effective_rate' => $effectiveRate,
            'account_id' => $accountId,
        ]);

        $result = $this->stripeService->createTopUpSession([
            'tier' => $tier,
            'amount' => $amount,
            'currency' => $currency,
            'vatRate' => 0.20,
            'effectiveRate' => $effectiveRate,
            'accountId' => $accountId,
        ]);

        if (!$result['success']) {
            $this->logAudit('topup_attempt_failed', [
                'tier' => $tier,
                'amount' => $amount,
                'currency' => $currency,
                'account_id' => $accountId,
                'reason' => 'Stripe session creation failed',
                'error' => $result['error'] ?? 'Unknown error',
            ]);

            return response()->json($result, 500);
        }

        $this->logAudit('topup_session_created', [
            'tier' => $tier,
            'amount' => $amount,
            'currency' => $currency,
            'vat_amount' => $amount * 0.20,
            'total_payable' => $amount * 1.20,
            'account_id' => $accountId,
            'session_id' => $result['sessionId'] ?? null,
            'is_mock' => $result['isMock'] ?? false,
        ]);

        return response()->json($result);
    }

    private function logAudit(string $action, array $data): void
    {
        $userId = $this->getCurrentUserId();
        
        Log::channel('single')->info('[AUDIT] ' . strtoupper($action), array_merge([
            'action' => $action,
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ], $data));
    }

    private function getCurrentUserId(): string
    {
        return 'user_demo_001';
    }

    private function getCurrentAccountId(): string
    {
        return 'ACC-001';
    }
}
