<?php

namespace App\Services\Billing;

use App\Models\Account;
use App\Models\Billing\ProductTierPrice;
use App\Models\Billing\CustomerPrice;
use App\Exceptions\Billing\PriceNotFoundException;

class PricingEngine
{
    /**
     * Resolve the customer-facing price for a product/country combination.
     *
     * Waterfall:
     * 1. If Starter/Enterprise → fixed tier pricing
     * 2. If Bespoke → admin override → HubSpot deal → Enterprise fallback
     */
    public function resolvePrice(Account $account, string $productType, ?string $countryIso): PriceResult
    {
        if (in_array($account->product_tier, ['starter', 'enterprise'])) {
            return $this->lookupTierPrice($account->product_tier, $productType, $countryIso, $account->currency);
        }

        // Bespoke waterfall
        // Step 1: Admin override
        $override = CustomerPrice::where('account_id', $account->id)
            ->where('product_type', $productType)
            ->where('country_iso', $countryIso)
            ->where('source', 'admin_override')
            ->active()
            ->validAt()
            ->first();

        if ($override) {
            return new PriceResult($override->unit_price, $override->currency, 'admin_override', $override->id);
        }

        // Step 2: HubSpot deal pricing
        $hubspot = CustomerPrice::where('account_id', $account->id)
            ->where('product_type', $productType)
            ->where('country_iso', $countryIso)
            ->where('source', 'hubspot')
            ->active()
            ->validAt()
            ->first();

        if ($hubspot) {
            return new PriceResult($hubspot->unit_price, $hubspot->currency, 'hubspot', $hubspot->id);
        }

        // Step 3: Fallback to Enterprise tier
        return $this->lookupTierPrice('enterprise', $productType, $countryIso, $account->currency);
    }

    /**
     * Calculate total cost for a message.
     */
    public function calculateMessageCost(
        Account $account,
        string $productType,
        ?string $countryIso,
        int $segments = 1
    ): CostCalculation {
        $price = $this->resolvePrice($account, $productType, $countryIso);
        $totalCost = bcmul($price->unitPrice, (string)$segments, 6);

        return new CostCalculation(
            unitPrice: $price->unitPrice,
            segments: $segments,
            totalCost: $totalCost,
            currency: $price->currency,
            priceSource: $price->source,
            priceId: $price->priceId,
        );
    }

    /**
     * Get all pricing for a customer (for portal display).
     */
    public function getCustomerPricing(Account $account): array
    {
        if (in_array($account->product_tier, ['starter', 'enterprise'])) {
            $prices = ProductTierPrice::where('product_tier', $account->product_tier)
                ->active()
                ->validAt()
                ->orderBy('product_type')
                ->orderBy('country_iso')
                ->get();

            return $prices->map(fn($p) => [
                'product_type' => $p->product_type,
                'country_iso' => $p->country_iso,
                'unit_price' => $p->unit_price,
                'currency' => $p->currency,
                'source' => 'tier_' . $account->product_tier,
            ])->toArray();
        }

        // Bespoke: merge customer prices with enterprise fallback
        $customerPrices = CustomerPrice::where('account_id', $account->id)
            ->active()
            ->validAt()
            ->orderBy('product_type')
            ->orderBy('country_iso')
            ->get();

        $fallbackPrices = ProductTierPrice::where('product_tier', 'enterprise')
            ->active()
            ->validAt()
            ->get();

        // Index customer prices for fast lookup
        $indexed = [];
        foreach ($customerPrices as $p) {
            $key = $p->product_type . '.' . ($p->country_iso ?? 'default');
            $indexed[$key] = [
                'product_type' => $p->product_type,
                'country_iso' => $p->country_iso,
                'unit_price' => $p->unit_price,
                'currency' => $p->currency,
                'source' => $p->source,
            ];
        }

        // Fill gaps with enterprise fallback
        foreach ($fallbackPrices as $p) {
            $key = $p->product_type . '.' . ($p->country_iso ?? 'default');
            if (!isset($indexed[$key])) {
                $indexed[$key] = [
                    'product_type' => $p->product_type,
                    'country_iso' => $p->country_iso,
                    'unit_price' => $p->unit_price,
                    'currency' => $p->currency,
                    'source' => 'enterprise_fallback',
                ];
            }
        }

        return array_values($indexed);
    }

    private function lookupTierPrice(string $tier, string $productType, ?string $countryIso, string $currency): PriceResult
    {
        // Try exact country match first
        $price = ProductTierPrice::forLookup($tier, $productType, $countryIso)->first();

        // Fall back to default (null country) if no country-specific price
        if (!$price && $countryIso !== null) {
            $price = ProductTierPrice::forLookup($tier, $productType, null)->first();
        }

        if (!$price) {
            throw new PriceNotFoundException(
                "No price found for tier={$tier}, product={$productType}, country={$countryIso}"
            );
        }

        return new PriceResult($price->unit_price, $price->currency, "tier_{$tier}", $price->id);
    }
}

class PriceResult
{
    public function __construct(
        public readonly string $unitPrice,
        public readonly string $currency,
        public readonly string $source,
        public readonly ?string $priceId = null,
    ) {}
}

class CostCalculation
{
    public function __construct(
        public readonly string $unitPrice,
        public readonly int $segments,
        public readonly string $totalCost,
        public readonly string $currency,
        public readonly string $priceSource,
        public readonly ?string $priceId = null,
    ) {}
}
