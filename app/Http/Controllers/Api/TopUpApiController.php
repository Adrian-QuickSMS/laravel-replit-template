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

        Log::info('Top-up checkout session requested', [
            'tier' => $tier,
            'amount' => $amount,
            'currency' => $currency,
            'effective_rate' => $effectiveRate,
        ]);

        $result = $this->stripeService->createTopUpSession([
            'tier' => $tier,
            'amount' => $amount,
            'currency' => $currency,
            'vatRate' => 0.20,
            'effectiveRate' => $effectiveRate,
            'accountId' => 'demo_account',
        ]);

        if (!$result['success']) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }
}
