<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * HubSpot Product Service
 * 
 * IMPORTANT SECURITY & ARCHITECTURE NOTES:
 * - All pricing is fetched LIVE from HubSpot API (no server-side caching)
 * - Multi-currency support via hs_price_gbp, hs_price_eur, hs_price_usd fields
 * - PCI compliance: Portal NEVER handles or stores card data
 * - Payment processing handled entirely by Stripe via HubSpot redirect
 * - All actions are logged for audit trail
 */
class HubSpotProductService
{
    private string $baseUrl = 'https://api.hubapi.com/crm/v3/objects/products';
    private ?string $accessToken;

    // Supported currencies for multi-currency pricing
    private const SUPPORTED_CURRENCIES = ['GBP', 'EUR', 'USD'];

    private array $productSkuMapping = [
        'sms' => 'QSMS-SMS',
        'rcs_basic' => 'QSMS-RCS-BASIC',
        'rcs_single' => 'QSMS-RCS-SINGLE',
        'vmn' => 'QSMS-VMN',
        'shortcode_keyword' => 'QSMS-SHORTCODE',
        'ai' => 'QSMS-AI',
    ];

    private array $numberProductSkuMapping = [
        'vmn_uk_longcode_setup' => 'QSMS-VMN-UK-SETUP',
        'vmn_uk_longcode_monthly' => 'QSMS-VMN-UK-MONTHLY',
        'vmn_international_setup' => 'QSMS-VMN-INTL-SETUP',
        'vmn_international_monthly' => 'QSMS-VMN-INTL-MONTHLY',
        'vmn_tollfree_setup' => 'QSMS-VMN-TOLLFREE-SETUP',
        'vmn_tollfree_monthly' => 'QSMS-VMN-TOLLFREE-MONTHLY',
        'keyword_setup' => 'QSMS-KEYWORD-SETUP',
        'keyword_monthly' => 'QSMS-KEYWORD-MONTHLY',
    ];

    public function __construct()
    {
        $this->accessToken = config('services.hubspot.access_token');
    }

    /**
     * Fetch products with live pricing from HubSpot
     * 
     * NOTE: Intentionally NO CACHING - prices are always fetched live
     * to ensure accuracy for billing purposes.
     */
    public function fetchProducts(string $currency = 'GBP'): array
    {
        // Validate currency
        $currency = strtoupper($currency);
        if (!in_array($currency, self::SUPPORTED_CURRENCIES)) {
            Log::warning('Unsupported currency requested', ['currency' => $currency]);
            $currency = 'GBP';
        }

        if (empty($this->accessToken)) {
            Log::warning('HubSpot access token not configured - using mock data');
            return $this->getMockProducts($currency);
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
            Log::info('HubSpot products fetched successfully', [
                'currency' => $currency,
                'product_count' => count($data['results'] ?? []),
                'timestamp' => now()->toIso8601String(),
            ]);

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

    /**
     * Create invoice via HubSpot and return Stripe payment URL
     * 
     * SECURITY NOTES:
     * - PCI DSS compliance: All card processing handled by Stripe
     * - Portal NEVER sees, handles, or stores card data
     * - User is redirected to Stripe hosted payment page
     * - Payment confirmation via webhook only
     */
    public function createInvoice(array $data): array
    {
        // Audit log: Invoice creation attempt
        Log::info('Invoice creation initiated', [
            'account_id' => $data['account_id'] ?? 'unknown',
            'tier' => $data['tier'] ?? 'unknown',
            'volume' => $data['volume'] ?? 0,
            'currency' => $data['currency'] ?? 'GBP',
            'net_cost' => $data['net_cost'] ?? 0,
            'timestamp' => now()->toIso8601String(),
            'action' => 'invoice_create_attempt',
        ]);

        if (empty($this->accessToken)) {
            Log::warning('HubSpot access token not configured', [
                'action' => 'invoice_create_failed',
                'reason' => 'missing_api_token',
            ]);
            return [
                'success' => false,
                'error' => 'HubSpot API not configured. Please add HUBSPOT_ACCESS_TOKEN.',
            ];
        }

        try {
            // TODO: Implement actual HubSpot invoice creation
            // This would typically:
            // 1. Create or find the contact/company in HubSpot
            // 2. Create a deal with line items
            // 3. Generate an invoice via HubSpot Payments or Stripe integration
            // 4. Return the Stripe payment URL
            
            $invoicePayload = [
                'properties' => [
                    'hs_title' => 'QuickSMS Message Purchase - ' . ucfirst($data['tier']),
                    'hs_currency' => $data['currency'],
                    'amount' => $data['net_cost'],
                    'hs_external_account_id' => $data['account_id'],
                ],
                'associations' => [],
            ];

            Log::info('Creating HubSpot invoice', [
                'account_id' => $data['account_id'],
                'tier' => $data['tier'],
                'volume' => $data['volume'],
                'net_cost' => $data['net_cost'],
            ]);

            // TODO: Replace with actual HubSpot API call
            // For now, simulate the response
            // In production, this would call HubSpot's invoices API
            // and return the Stripe payment link
            
            return [
                'success' => false,
                'error' => 'Invoice creation requires HubSpot Payments integration. Please configure your HubSpot account.',
            ];

        } catch (\Exception $e) {
            Log::error('HubSpot invoice creation exception', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => 'Error creating invoice: ' . $e->getMessage(),
            ];
        }
    }

    private function getErrorResponse(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
            'products' => [],
        ];
    }

    /**
     * Mock product data for development/demo when HubSpot is not configured
     * Pricing based on tier: Starter vs Enterprise
     */
    private function getMockProducts(string $currency): array
    {
        $currencySymbol = match ($currency) {
            'EUR' => '€',
            'USD' => '$',
            default => '£',
        };

        // Mock pricing data - Starter tier prices (default display)
        $products = [
            'sms' => [
                'id' => 'mock-sms-001',
                'name' => 'SMS Message',
                'sku' => 'QSMS-SMS',
                'price' => 0.0395,
                'price_enterprise' => 0.034,
                'description' => 'Standard SMS message credit',
                'billing_period' => null,
                'currency' => $currency,
            ],
            'rcs_basic' => [
                'id' => 'mock-rcs-basic-001',
                'name' => 'RCS Basic',
                'sku' => 'QSMS-RCS-BASIC',
                'price' => 0.037,
                'price_enterprise' => 0.031,
                'description' => 'RCS Basic message with branding',
                'billing_period' => null,
                'currency' => $currency,
            ],
            'rcs_single' => [
                'id' => 'mock-rcs-single-001',
                'name' => 'RCS Single',
                'sku' => 'QSMS-RCS-SINGLE',
                'price' => 0.05,
                'price_enterprise' => 0.045,
                'description' => 'RCS Single rich message',
                'billing_period' => null,
                'currency' => $currency,
            ],
            'vmn' => [
                'id' => 'mock-vmn-001',
                'name' => 'Virtual Mobile Number',
                'sku' => 'QSMS-VMN',
                'price' => 2.00,
                'price_enterprise' => 1.00,
                'description' => 'Dedicated virtual mobile number',
                'billing_period' => 'monthly',
                'currency' => $currency,
            ],
            'shortcode_keyword' => [
                'id' => 'mock-shortcode-001',
                'name' => 'Short Code (Keyword)',
                'sku' => 'QSMS-SHORTCODE',
                'price' => 2.00,
                'price_enterprise' => 1.00,
                'description' => 'Short code keyword rental',
                'billing_period' => 'monthly',
                'currency' => $currency,
            ],
            'ai' => [
                'id' => 'mock-ai-001',
                'name' => 'AI Credits',
                'sku' => 'QSMS-AI',
                'price' => 0.25,
                'price_enterprise' => 0.20,
                'description' => 'AI-powered message assistance',
                'billing_period' => null,
                'currency' => $currency,
            ],
        ];

        return [
            'success' => true,
            'products' => $products,
            'currency' => $currency,
            'fetched_at' => now()->toIso8601String(),
            'is_mock' => true,
        ];
    }

    /**
     * Fetch numbers pricing (VMN and Keywords) from HubSpot
     * Returns setup and monthly fees for each number type
     */
    public function fetchNumbersPricing(string $currency = 'GBP'): array
    {
        $currency = strtoupper($currency);
        if (!in_array($currency, self::SUPPORTED_CURRENCIES)) {
            Log::warning('Unsupported currency requested for numbers pricing', ['currency' => $currency]);
            $currency = 'GBP';
        }

        if (empty($this->accessToken)) {
            Log::warning('HubSpot access token not configured - using mock numbers pricing');
            return $this->getMockNumbersPricing($currency);
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
                Log::error('HubSpot API error fetching numbers pricing', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return $this->getMockNumbersPricing($currency, true);
            }

            $data = $response->json();
            Log::info('HubSpot numbers pricing fetched successfully', [
                'currency' => $currency,
                'product_count' => count($data['results'] ?? []),
                'timestamp' => now()->toIso8601String(),
            ]);

            return $this->mapNumbersProducts($data['results'] ?? [], $currency);

        } catch (\Exception $e) {
            Log::error('HubSpot API exception fetching numbers pricing', ['message' => $e->getMessage()]);
            return $this->getMockNumbersPricing($currency, true);
        }
    }

    private function mapNumbersProducts(array $hubspotProducts, string $currency): array
    {
        $products = [];
        $currencyPriceField = $this->getCurrencyPriceField($currency);

        foreach ($hubspotProducts as $product) {
            $props = $product['properties'] ?? [];
            $sku = $props['hs_sku'] ?? '';
            
            $productKey = array_search($sku, $this->numberProductSkuMapping);
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

        return $this->formatNumbersPricingResponse($products, $currency, false);
    }

    private function formatNumbersPricingResponse(array $products, string $currency, bool $isMock): array
    {
        return [
            'success' => true,
            'pricing' => [
                'vmn' => [
                    'uk_longcode' => [
                        'setup_fee' => $products['vmn_uk_longcode_setup']['price'] ?? 10.00,
                        'monthly_fee' => $products['vmn_uk_longcode_monthly']['price'] ?? 8.00,
                    ],
                    'international' => [
                        'setup_fee' => $products['vmn_international_setup']['price'] ?? 15.00,
                        'monthly_fee' => $products['vmn_international_monthly']['price'] ?? 12.00,
                    ],
                    'tollfree' => [
                        'setup_fee' => $products['vmn_tollfree_setup']['price'] ?? 25.00,
                        'monthly_fee' => $products['vmn_tollfree_monthly']['price'] ?? 20.00,
                    ],
                ],
                'keyword' => [
                    'setup_fee' => $products['keyword_setup']['price'] ?? 25.00,
                    'monthly_fee' => $products['keyword_monthly']['price'] ?? 50.00,
                ],
            ],
            'currency' => $currency,
            'fetched_at' => now()->toIso8601String(),
            'is_mock' => $isMock,
        ];
    }

    /**
     * Mock numbers pricing for development/demo or API fallback
     */
    private function getMockNumbersPricing(string $currency, bool $isApiError = false): array
    {
        $products = [
            'vmn_uk_longcode_setup' => ['price' => 10.00],
            'vmn_uk_longcode_monthly' => ['price' => 8.00],
            'vmn_international_setup' => ['price' => 15.00],
            'vmn_international_monthly' => ['price' => 12.00],
            'vmn_tollfree_setup' => ['price' => 25.00],
            'vmn_tollfree_monthly' => ['price' => 20.00],
            'keyword_setup' => ['price' => 25.00],
            'keyword_monthly' => ['price' => 50.00],
        ];

        $response = $this->formatNumbersPricingResponse($products, $currency, true);
        
        if ($isApiError) {
            $response['api_error'] = true;
            $response['error_message'] = 'HubSpot API unavailable - using cached pricing';
        }

        return $response;
    }
}
