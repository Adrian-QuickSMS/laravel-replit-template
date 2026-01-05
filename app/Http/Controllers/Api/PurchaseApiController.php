<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HubSpotProductService;
use App\Services\VatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseApiController extends Controller
{
    private HubSpotProductService $hubSpotService;
    private VatService $vatService;

    public function __construct(HubSpotProductService $hubSpotService, VatService $vatService)
    {
        $this->hubSpotService = $hubSpotService;
        $this->vatService = $vatService;
    }

    public function getProducts(Request $request): JsonResponse
    {
        $currency = $request->get('currency', $this->getAccountCurrency());
        $vatApplicable = $this->isVatApplicable();

        $productsData = $this->hubSpotService->fetchProducts($currency);

        if (!$productsData['success']) {
            return response()->json($productsData, 500);
        }

        $productsWithVat = [];
        foreach ($productsData['products'] as $key => $product) {
            $vatCalc = $this->vatService->calculateVat($product['price'], $vatApplicable);
            $productsWithVat[$key] = array_merge($product, [
                'pricing' => $vatCalc,
            ]);
        }

        return response()->json([
            'success' => true,
            'products' => $productsWithVat,
            'currency' => $currency,
            'vat_applicable' => $vatApplicable,
            'vat_rate' => $vatApplicable ? $this->vatService->getVatRatePercentage() : 0,
            'fetched_at' => $productsData['fetched_at'],
        ]);
    }

    public function calculateOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_key' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $vatApplicable = $this->isVatApplicable();
        $currency = $request->get('currency', $this->getAccountCurrency());

        $lineItems = [];
        $netTotal = 0;

        foreach ($validated['items'] as $item) {
            $lineNet = $item['unit_price'] * $item['quantity'];
            $netTotal += $lineNet;

            $lineItems[] = [
                'product_key' => $item['product_key'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_net' => round($lineNet, 2),
            ];
        }

        $vatCalc = $this->vatService->calculateVat($netTotal, $vatApplicable);

        return response()->json([
            'success' => true,
            'line_items' => $lineItems,
            'summary' => [
                'net_total' => $vatCalc['net'],
                'vat_applicable' => $vatCalc['vat_applicable'],
                'vat_rate' => $vatCalc['vat_rate'],
                'vat_amount' => $vatCalc['vat_amount'],
                'total_payable' => $vatCalc['total'],
            ],
            'currency' => $currency,
        ]);
    }

    private function isVatApplicable(): bool
    {
        // TODO: Replace with actual account context from session/database
        // Example: return auth()->user()->account->vat_applicable ?? true;
        return true;
    }

    private function getAccountCurrency(): string
    {
        // TODO: Replace with actual account currency from session/database
        // Example: return auth()->user()->account->currency ?? 'GBP';
        return 'GBP';
    }
}
