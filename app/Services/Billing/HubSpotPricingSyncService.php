<?php

namespace App\Services\Billing;

use App\Models\Account;
use App\Models\Billing\CustomerPrice;
use App\Models\Billing\PricingSyncLog;
use App\Models\Billing\FinancialAuditLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HubSpotPricingSyncService
{
    private string $baseUrl = 'https://api.hubapi.com';

    /**
     * Sync pricing FROM HubSpot deal line items TO platform.
     */
    public function syncFromHubSpot(string $hubspotDealId): array
    {
        $account = Account::where('hubspot_deal_id', $hubspotDealId)->first();
        if (!$account) {
            Log::warning('HubSpot sync: no account for deal', ['deal' => $hubspotDealId]);
            return ['synced' => 0, 'conflicts' => 0];
        }

        $lineItems = $this->fetchDealLineItems($hubspotDealId);

        $synced = 0;
        $conflicts = 0;

        foreach ($lineItems as $item) {
            $productType = $this->mapHubSpotProductToType($item);
            $countryIso = $this->extractCountryFromLineItem($item);
            $newPrice = $item['properties']['price'] ?? null;

            if (!$productType || !$newPrice) continue;

            // Check for conflict with admin override
            $existing = CustomerPrice::where('account_id', $account->id)
                ->where('product_type', $productType)
                ->where('country_iso', $countryIso)
                ->where('active', true)
                ->first();

            if ($existing && $existing->source === 'admin_override') {
                // CONFLICT detected
                PricingSyncLog::create([
                    'account_id' => $account->id,
                    'field_path' => "{$productType}.{$countryIso}.unit_price",
                    'old_value' => (string)$existing->unit_price,
                    'new_value' => (string)$newPrice,
                    'source' => 'hubspot',
                    'hubspot_timestamp' => now(),
                    'admin_timestamp' => $existing->set_at,
                    'conflict_detected' => true,
                ]);
                $conflicts++;
                continue;
            }

            // No conflict — apply the HubSpot price
            $this->upsertCustomerPrice($account, $productType, $countryIso, $newPrice, 'hubspot', $item['id'] ?? null);

            PricingSyncLog::create([
                'account_id' => $account->id,
                'field_path' => "{$productType}.{$countryIso}.unit_price",
                'old_value' => $existing ? (string)$existing->unit_price : null,
                'new_value' => (string)$newPrice,
                'source' => 'hubspot',
                'conflict_detected' => false,
            ]);

            $synced++;
        }

        return ['synced' => $synced, 'conflicts' => $conflicts];
    }

    /**
     * Sync pricing FROM platform TO HubSpot.
     */
    public function syncToHubSpot(Account $account): void
    {
        if (!$account->hubspot_deal_id) return;

        $prices = CustomerPrice::where('account_id', $account->id)
            ->where('active', true)
            ->get();

        foreach ($prices as $price) {
            // Map back to HubSpot line item format and update
            $this->updateHubSpotLineItem($account->hubspot_deal_id, $price);
        }

        PricingSyncLog::create([
            'account_id' => $account->id,
            'field_path' => 'full_sync_to_hubspot',
            'old_value' => null,
            'new_value' => 'sync_pushed',
            'source' => 'admin',
            'admin_timestamp' => now(),
            'conflict_detected' => false,
        ]);
    }

    /**
     * Handle HubSpot deal webhook (deal updated or closed won).
     */
    public function handleDealWebhook(array $payload): void
    {
        foreach ($payload as $event) {
            $dealId = (string)($event['objectId'] ?? '');
            $propertyName = $event['propertyName'] ?? '';

            if ($propertyName === 'dealstage') {
                $newStage = $event['propertyValue'] ?? '';
                if ($newStage === 'closedwon') {
                    $this->handleDealClosedWon($dealId);
                }
            }

            // Price-related changes trigger sync
            if (str_contains($propertyName, 'price') || str_contains($propertyName, 'amount')) {
                $this->syncFromHubSpot($dealId);
            }
        }
    }

    /**
     * Handle Deal Closed Won — activate account with deal pricing.
     */
    public function handleDealClosedWon(string $hubspotDealId): void
    {
        $deal = $this->fetchDeal($hubspotDealId);
        if (!$deal) return;

        $account = Account::where('hubspot_deal_id', $hubspotDealId)->first();

        if ($account) {
            // Account exists — sync pricing
            $this->syncFromHubSpot($hubspotDealId);
            return;
        }

        // New account — will be created by account provisioning service
        // This service just handles pricing; account creation is separate
        Log::info('HubSpot deal closed won — awaiting account creation', ['deal' => $hubspotDealId]);
    }

    /**
     * Resolve a pricing conflict.
     */
    public function resolveConflict(string $conflictId, string $resolution, string $resolvedBy, ?string $customValue = null): void
    {
        $conflict = PricingSyncLog::findOrFail($conflictId);

        $conflict->update([
            'conflict_resolved' => true,
            'resolved_by' => $resolvedBy,
            'resolved_at' => now(),
            'resolution' => $resolution,
        ]);

        $account = Account::findOrFail($conflict->account_id);

        // Parse field path to get product_type and country
        $parts = explode('.', $conflict->field_path);
        $productType = $parts[0] ?? null;
        $countryIso = $parts[1] ?? null;

        if ($productType === null) return;

        $priceToApply = match ($resolution) {
            'accept_hubspot' => $conflict->new_value,
            'accept_admin' => $conflict->old_value,
            'custom' => $customValue,
            default => null,
        };

        if ($priceToApply) {
            $source = $resolution === 'accept_hubspot' ? 'hubspot' : 'admin_override';
            $this->upsertCustomerPrice($account, $productType, $countryIso, $priceToApply, $source);
        }

        FinancialAuditLog::record(
            'pricing_conflict_resolved', 'pricing_sync_log', $conflictId,
            ['old_value' => $conflict->old_value, 'new_value' => $conflict->new_value],
            ['resolution' => $resolution, 'applied_value' => $priceToApply],
            $resolvedBy, 'admin'
        );
    }

    /**
     * Get unresolved pricing conflicts.
     */
    public function getUnresolvedConflicts()
    {
        return PricingSyncLog::unresolvedConflicts()
            ->with('account:id,company_name')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
    }

    private function upsertCustomerPrice(Account $account, string $productType, ?string $countryIso, string $price, string $source, ?string $hubspotLineItemId = null): void
    {
        // Deactivate existing
        CustomerPrice::where('account_id', $account->id)
            ->where('product_type', $productType)
            ->where('country_iso', $countryIso)
            ->where('active', true)
            ->update(['active' => false]);

        // Create new version
        CustomerPrice::create([
            'account_id' => $account->id,
            'product_type' => $productType,
            'country_iso' => $countryIso,
            'unit_price' => $price,
            'currency' => $account->currency,
            'source' => $source,
            'hubspot_deal_line_item_id' => $hubspotLineItemId,
            'set_by' => $source === 'admin_override' ? auth()->id() : null,
            'set_at' => now(),
            'valid_from' => now()->toDateString(),
            'active' => true,
            'version' => 1,
        ]);
    }

    private function fetchDeal(string $dealId): ?array
    {
        $response = Http::withToken(config('services.hubspot.api_key'))
            ->get("{$this->baseUrl}/crm/v3/objects/deals/{$dealId}", [
                'properties' => 'dealname,dealstage,amount,pipeline',
                'associations' => 'line_items,companies',
            ]);

        return $response->successful() ? $response->json() : null;
    }

    private function fetchDealLineItems(string $dealId): array
    {
        $response = Http::withToken(config('services.hubspot.api_key'))
            ->get("{$this->baseUrl}/crm/v3/objects/deals/{$dealId}/associations/line_items");

        if (!$response->successful()) return [];

        $lineItemIds = collect($response->json('results', []))->pluck('id');

        $items = [];
        foreach ($lineItemIds as $id) {
            $itemResponse = Http::withToken(config('services.hubspot.api_key'))
                ->get("{$this->baseUrl}/crm/v3/objects/line_items/{$id}", [
                    'properties' => 'name,price,quantity,hs_product_id,description',
                ]);

            if ($itemResponse->successful()) {
                $items[] = $itemResponse->json();
            }
        }

        return $items;
    }

    private function updateHubSpotLineItem(string $dealId, CustomerPrice $price): void
    {
        if (!$price->hubspot_deal_line_item_id) return;

        Http::withToken(config('services.hubspot.api_key'))
            ->patch("{$this->baseUrl}/crm/v3/objects/line_items/{$price->hubspot_deal_line_item_id}", [
                'properties' => [
                    'price' => $price->unit_price,
                ],
            ]);
    }

    private function mapHubSpotProductToType(array $item): ?string
    {
        $name = strtolower($item['properties']['name'] ?? '');

        if (str_contains($name, 'sms')) return 'sms';
        if (str_contains($name, 'rcs basic')) return 'rcs_basic';
        if (str_contains($name, 'rcs single') || str_contains($name, 'rcs rich')) return 'rcs_single';
        if (str_contains($name, 'ai') || str_contains($name, 'token')) return 'ai_query';
        if (str_contains($name, 'virtual number')) return 'virtual_number_monthly';
        if (str_contains($name, 'shortcode')) return 'shortcode_monthly';
        if (str_contains($name, 'inbound')) return 'inbound_sms';
        if (str_contains($name, 'support')) return 'support';

        return null;
    }

    private function extractCountryFromLineItem(array $item): ?string
    {
        // Country could be in description or custom property
        $desc = $item['properties']['description'] ?? '';

        // Try to extract ISO code like "(GB)" or "- GB"
        if (preg_match('/\b([A-Z]{2})\b/', $desc, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
