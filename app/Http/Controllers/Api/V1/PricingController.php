<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Billing\PricingEngine;
use App\Exceptions\Billing\PriceNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function __construct(
        private PricingEngine $pricingEngine,
    ) {}

    /**
     * GET /api/v1/pricing
     * All pricing for authenticated customer.
     */
    public function index(Request $request): JsonResponse
    {
        $account = $request->user()->account;
        $prices = $this->pricingEngine->getCustomerPricing($account);

        return response()->json([
            'success' => true,
            'data' => [
                'product_tier' => $account->product_tier,
                'currency' => $account->currency,
                'prices' => $prices,
            ],
        ]);
    }

    /**
     * GET /api/v1/pricing/{country_iso}
     * Price for a specific country.
     */
    public function forCountry(Request $request, string $countryIso): JsonResponse
    {
        $account = $request->user()->account;
        $countryIso = strtoupper($countryIso);

        $products = ['sms', 'rcs_basic', 'rcs_single'];
        $prices = [];

        foreach ($products as $productType) {
            try {
                $result = $this->pricingEngine->resolvePrice($account, $productType, $countryIso);
                $prices[$productType] = [
                    'unit_price' => $result->unitPrice,
                    'currency' => $result->currency,
                    'source' => $result->source,
                ];
            } catch (PriceNotFoundException) {
                $prices[$productType] = null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'country_iso' => $countryIso,
                'prices' => $prices,
            ],
        ]);
    }

    /**
     * POST /api/v1/pricing/estimate
     * Estimate campaign cost.
     */
    public function estimate(Request $request): JsonResponse
    {
        $request->validate([
            'product_type' => 'required|in:sms,rcs_basic,rcs_single',
            'country_iso' => 'required|string|size:2',
            'segments' => 'required|integer|min:1',
            'recipient_count' => 'required|integer|min:1',
        ]);

        $account = $request->user()->account;

        try {
            $cost = $this->pricingEngine->calculateMessageCost(
                $account,
                $request->input('product_type'),
                strtoupper($request->input('country_iso')),
                $request->input('segments')
            );

            $totalMessages = $request->input('recipient_count');
            $estimatedTotal = bcmul($cost->totalCost, (string)$totalMessages, 4);

            return response()->json([
                'success' => true,
                'data' => [
                    'unit_price' => $cost->unitPrice,
                    'segments' => $cost->segments,
                    'cost_per_message' => $cost->totalCost,
                    'recipient_count' => $totalMessages,
                    'estimated_total' => $estimatedTotal,
                    'currency' => $cost->currency,
                    'price_source' => $cost->priceSource,
                ],
            ]);
        } catch (PriceNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'No pricing available for this destination.',
            ], 404);
        }
    }
}
