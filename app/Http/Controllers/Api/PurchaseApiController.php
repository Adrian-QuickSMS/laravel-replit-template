<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Billing\Payment;
use App\Models\Billing\ProductTierPrice;
use App\Models\Billing\ServiceCatalogue;
use App\Services\Billing\BalanceService;
use App\Services\Billing\InvoiceService;
use App\Services\VatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PurchaseApiController extends Controller
{
    private VatService $vatService;
    private BalanceService $balanceService;
    private InvoiceService $invoiceService;

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

    public function __construct(VatService $vatService, BalanceService $balanceService, InvoiceService $invoiceService)
    {
        $this->vatService = $vatService;
        $this->balanceService = $balanceService;
        $this->invoiceService = $invoiceService;
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

        $tenantId = session('customer_tenant_id');
        $account = $tenantId ? Account::withoutGlobalScopes()->find($tenantId) : null;
        $isBespoke = $account && $account->product_tier === 'bespoke';

        $bespokePrices = collect();
        if ($isBespoke && $tenantId) {
            $bespokePrices = \App\Models\Billing\CustomerPrice::where('account_id', $tenantId)
                ->whereIn('product_type', $productTypes)
                ->whereNull('country_iso')
                ->where('active', true)
                ->whereRaw("valid_from <= CURRENT_DATE")
                ->where(function ($q) {
                    $q->whereNull('valid_to')->orWhereRaw("valid_to >= CURRENT_DATE");
                })
                ->get()
                ->keyBy('product_type');
        }

        $products = [];
        foreach (self::PRODUCT_TYPE_TO_KEY as $dbType => $frontendKey) {
            $starterPrice = $starterPrices->get($dbType);
            $enterprisePrice = $enterprisePrices->get($dbType);
            $bespokePrice = $bespokePrices->get($dbType);

            if (!$starterPrice && !$enterprisePrice && !$bespokePrice) {
                continue;
            }

            $price = $starterPrice ? (float) $starterPrice->unit_price : 0;
            $priceEnterprise = $enterprisePrice ? (float) $enterprisePrice->unit_price : null;
            $priceBespoke = $bespokePrice ? (float) $bespokePrice->unit_price : null;

            $displayPrice = $priceBespoke ?? $price;
            $vatCalc = $this->vatService->calculateVat($displayPrice, $vatApplicable);

            $products[$frontendKey] = [
                'id' => $bespokePrice->id ?? $starterPrice->id ?? $enterprisePrice->id,
                'name' => self::PRODUCT_NAMES[$frontendKey] ?? $frontendKey,
                'sku' => 'QSMS-' . strtoupper(str_replace('_', '-', $frontendKey)),
                'price' => $price,
                'price_enterprise' => $priceEnterprise,
                'price_bespoke' => $priceBespoke,
                'description' => self::PRODUCT_DESCRIPTIONS[$frontendKey] ?? '',
                'billing_period' => self::BILLING_PERIODS[$frontendKey] ?? null,
                'currency' => $currency,
                'pricing' => $vatCalc,
            ];
        }

        return response()->json([
            'success' => true,
            'products' => $products,
            'is_bespoke' => $isBespoke,
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

        $account = Account::withoutGlobalScopes()->find($accountId);
        if (!$account) {
            return response()->json([
                'success' => false,
                'error' => 'Account not found.',
            ], 404);
        }

        $currency = $validated['currency'];
        $tier = $validated['tier'];
        $volume = $validated['volume'];

        $serverUnitPrice = $this->getServerSideUnitPrice($accountId, $tier);
        if ($serverUnitPrice === null) {
            return response()->json([
                'success' => false,
                'error' => 'Unable to verify pricing for the selected tier.',
            ], 400);
        }

        $amount = bcmul((string) $serverUnitPrice, (string) $volume, 2);

        if (bccomp($amount, '0', 2) <= 0) {
            return response()->json([
                'success' => false,
                'error' => 'Calculated amount must be greater than zero.',
            ], 400);
        }

        try {
            $result = DB::transaction(function () use ($account, $amount, $currency, $tier, $volume, $serverUnitPrice) {
                $invoice = $this->invoiceService->createTopUpInvoice(
                    $account, $amount, $currency
                );

                $idempotencyKey = "purchase-{$invoice->id}";
                $referenceId = (string) Str::uuid();

                $this->balanceService->processTopUp(
                    $account->id, $amount, $currency, $idempotencyKey,
                    'purchase_payment', $referenceId,
                    [
                        'tier' => $tier,
                        'volume' => $volume,
                        'sms_unit_price' => (string) $serverUnitPrice,
                        'invoice_id' => $invoice->id,
                    ]
                );

                Payment::create([
                    'account_id' => $account->id,
                    'invoice_id' => $invoice->id,
                    'payment_method' => 'stripe_checkout',
                    'stripe_payment_intent_id' => $referenceId,
                    'currency' => $currency,
                    'amount' => $amount,
                    'status' => 'succeeded',
                    'paid_at' => now(),
                    'metadata' => [
                        'tier' => $tier,
                        'volume' => $volume,
                        'source' => 'purchase_page',
                    ],
                ]);

                return $invoice;
            });

            Log::info('Purchase invoice created and balance credited', [
                'account_id' => $account->id,
                'invoice_id' => $result->id,
                'amount' => $amount,
                'currency' => $currency,
                'tier' => $validated['tier'],
                'volume' => $validated['volume'],
            ]);

            return response()->json([
                'success' => true,
                'invoice_id' => $result->id,
                'invoice_number' => $result->invoice_number,
                'amount' => $amount,
                'currency' => $currency,
                'payment_completed' => true,
                'message' => 'Purchase completed successfully. Your balance has been credited.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Purchase invoice creation failed', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Unable to process your purchase. Please try again or contact support.',
            ], 500);
        }
    }

    private function getServerSideUnitPrice(string $accountId, string $tier): ?string
    {
        if ($tier === 'bespoke') {
            $price = \App\Models\Billing\CustomerPrice::where('account_id', $accountId)
                ->where('product_type', 'sms')
                ->whereNull('country_iso')
                ->where('active', true)
                ->whereRaw("valid_from <= CURRENT_DATE")
                ->where(function ($q) {
                    $q->whereNull('valid_to')->orWhereRaw("valid_to >= CURRENT_DATE");
                })
                ->first();

            return $price ? (string) $price->unit_price : null;
        }

        $price = ProductTierPrice::where('product_tier', $tier)
            ->where('product_type', 'sms')
            ->whereNull('country_iso')
            ->active()
            ->validAt()
            ->first();

        return $price ? (string) $price->unit_price : null;
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
