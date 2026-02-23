<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Billing\ProductTierPrice;
use App\Models\Billing\ServiceCatalogue;
use App\Services\HubSpotProductService;
use App\Services\VatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseApiController extends Controller
{
    private HubSpotProductService $hubSpotService;
    private VatService $vatService;

    private const PRODUCT_TYPE_TO_KEY = [
        'sms' => 'sms',
        'rcs_basic' => 'rcs_basic',
        'rcs_single' => 'rcs_single',
        'virtual_number_monthly' => 'vmn',
        'shortcode_keyword' => 'shortcode_keyword',
        'ai_query' => 'ai',
    ];

    private const PRODUCT_NAMES = [
        'sms' => 'SMS Message',
        'rcs_basic' => 'RCS Basic',
        'rcs_single' => 'RCS Single',
        'vmn' => 'Virtual Mobile Number',
        'shortcode_keyword' => 'Short Code (Keyword)',
        'ai' => 'AI Credits',
    ];

    private const PRODUCT_DESCRIPTIONS = [
        'sms' => 'Standard SMS message credit',
        'rcs_basic' => 'RCS Basic message with branding',
        'rcs_single' => 'RCS Single rich message',
        'vmn' => 'Dedicated virtual mobile number',
        'shortcode_keyword' => 'Short code keyword rental',
        'ai' => 'AI-powered message assistance',
    ];

    private const BILLING_PERIODS = [
        'vmn' => 'monthly',
        'shortcode_keyword' => 'monthly',
    ];

    public function __construct(HubSpotProductService $hubSpotService, VatService $vatService)
    {
        $this->hubSpotService = $hubSpotService;
        $this->vatService = $vatService;
    }

    public function getProducts(Request $request): JsonResponse
    {
        $currency = $request->get('currency', $this->getAccountCurrency());
        $vatApplicable = $this->isVatApplicable();

        $productTypes = array_keys(self::PRODUCT_TYPE_TO_KEY);

        $starterPrices = ProductTierPrice::where('product_tier', 'starter')
            ->whereIn('product_type', $productTypes)
            ->whereNull('country_iso')
            ->active()
            ->validAt()
            ->get()
            ->keyBy('product_type');

        $enterprisePrices = ProductTierPrice::where('product_tier', 'enterprise')
            ->whereIn('product_type', $productTypes)
            ->whereNull('country_iso')
            ->active()
            ->validAt()
            ->get()
            ->keyBy('product_type');

        $products = [];
        foreach (self::PRODUCT_TYPE_TO_KEY as $dbType => $frontendKey) {
            $starterPrice = $starterPrices->get($dbType);
            $enterprisePrice = $enterprisePrices->get($dbType);

            if (!$starterPrice && !$enterprisePrice) {
                continue;
            }

            $price = $starterPrice ? (float) $starterPrice->unit_price : 0;
            $priceEnterprise = $enterprisePrice ? (float) $enterprisePrice->unit_price : null;

            $vatCalc = $this->vatService->calculateVat($price, $vatApplicable);

            $products[$frontendKey] = [
                'id' => $starterPrice->id ?? $enterprisePrice->id,
                'name' => self::PRODUCT_NAMES[$frontendKey] ?? $frontendKey,
                'sku' => 'QSMS-' . strtoupper(str_replace('_', '-', $frontendKey)),
                'price' => $price,
                'price_enterprise' => $priceEnterprise,
                'description' => self::PRODUCT_DESCRIPTIONS[$frontendKey] ?? '',
                'billing_period' => self::BILLING_PERIODS[$frontendKey] ?? null,
                'currency' => $currency,
                'pricing' => $vatCalc,
            ];
        }

        return response()->json([
            'success' => true,
            'products' => $products,
            'currency' => $currency,
            'vat_applicable' => $vatApplicable,
            'vat_rate' => $vatApplicable ? $this->vatService->getVatRatePercentage() : 0,
            'fetched_at' => now()->toIso8601String(),
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

    public function createInvoice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'account_id' => 'required|string',
            'tier' => 'required|string|in:starter,enterprise,bespoke',
            'volume' => 'required|integer|min:1',
            'sms_unit_price' => 'required|numeric|min:0',
            'net_cost' => 'required|numeric|min:0',
            'vat_applicable' => 'required|boolean',
            'currency' => 'required|string|in:GBP,EUR,USD',
        ]);

        $accountId = $this->getAccountId();
        $validated['account_id'] = $accountId;
        $validated['selected_tier'] = $validated['tier'];

        $invoiceData = $this->hubSpotService->createInvoice($validated);

        if (!$invoiceData['success']) {
            return response()->json($invoiceData, 500);
        }

        return response()->json([
            'success' => true,
            'invoice_id' => $invoiceData['invoice_id'],
            'payment_url' => $invoiceData['payment_url'],
            'message' => 'Invoice created successfully',
        ]);
    }

    private function isVatApplicable(): bool
    {
        $accountId = session('customer_tenant_id');
        if ($accountId) {
            $account = Account::withoutGlobalScopes()->find($accountId);
            if ($account && isset($account->vat_registered)) {
                return (bool) $account->vat_registered;
            }
        }
        return true;
    }

    private function getAccountCurrency(): string
    {
        $accountId = session('customer_tenant_id');
        if ($accountId) {
            $account = Account::withoutGlobalScopes()->find($accountId);
            if ($account) {
                return $account->currency ?? 'GBP';
            }
        }
        return 'GBP';
    }

    private function getAccountId(): string
    {
        return session('customer_tenant_id') ?? 'ACC-001';
    }
}
