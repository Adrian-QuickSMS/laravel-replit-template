<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HubSpotProductService
{
    private string $baseUrl = 'https://api.hubapi.com/crm/v3/objects/products';
    private ?string $accessToken;

    private array $productSkuMapping = [
        'sms' => 'QSMS-SMS',
        'rcs_basic' => 'QSMS-RCS-BASIC',
        'rcs_single' => 'QSMS-RCS-SINGLE',
        'vmn' => 'QSMS-VMN',
        'shortcode_keyword' => 'QSMS-SHORTCODE',
        'ai' => 'QSMS-AI',
    ];

    public function __construct()
    {
        $this->accessToken = env('HUBSPOT_ACCESS_TOKEN');
    }

    public function fetchProducts(string $currency = 'GBP'): array
    {
        if (empty($this->accessToken)) {
            Log::warning('HubSpot access token not configured');
            return $this->getErrorResponse('HubSpot API not configured');
        }

        try {
            $properties = implode(',', [
                'name',
                'price',
                'hs_sku',
                'description',
                'hs_price_gbp',
                'hs_price_eur',
                'hs_price_usd',
                'hs_recurring_billing_period',
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->accessToken,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl, [
                'properties' => $properties,
                'limit' => 100,
            ]);

            if ($response->failed()) {
                Log::error('HubSpot API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->getErrorResponse('Failed to fetch pricing data');
            }

            $data = $response->json();
            return $this->mapProducts($data['results'] ?? [], $currency);

        } catch (\Exception $e) {
            Log::error('HubSpot API exception', ['message' => $e->getMessage()]);
            return $this->getErrorResponse('Error connecting to pricing service');
        }
    }

    private function mapProducts(array $hubspotProducts, string $currency): array
    {
        $products = [];
        $currencyPriceField = $this->getCurrencyPriceField($currency);

        foreach ($hubspotProducts as $product) {
            $props = $product['properties'] ?? [];
            $sku = $props['hs_sku'] ?? '';
            
            $productKey = array_search($sku, $this->productSkuMapping);
            if ($productKey === false) {
                continue;
            }

            $price = $props[$currencyPriceField] ?? $props['price'] ?? '0.00';

            $products[$productKey] = [
                'id' => $product['id'],
                'name' => $props['name'] ?? $productKey,
                'sku' => $sku,
                'price' => (float) $price,
                'description' => $props['description'] ?? '',
                'billing_period' => $props['hs_recurring_billing_period'] ?? null,
                'currency' => $currency,
            ];
        }

        return [
            'success' => true,
            'products' => $products,
            'currency' => $currency,
            'fetched_at' => now()->toIso8601String(),
        ];
    }

    private function getCurrencyPriceField(string $currency): string
    {
        return match (strtoupper($currency)) {
            'GBP' => 'hs_price_gbp',
            'EUR' => 'hs_price_eur',
            'USD' => 'hs_price_usd',
            default => 'price',
        };
    }

    private function getErrorResponse(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
            'products' => [],
        ];
    }
}
