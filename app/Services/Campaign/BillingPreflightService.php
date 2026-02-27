<?php

namespace App\Services\Campaign;

use App\Models\Account;
use App\Models\Campaign;
use App\Models\Billing\AccountBalance;
use App\Services\Billing\BalanceService;
use App\Services\Billing\PricingEngine;
use App\Services\Billing\CostCalculation;
use App\Exceptions\Billing\InsufficientBalanceException;
use App\Exceptions\Billing\PriceNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BillingPreflightService — validates billing before campaign send.
 *
 * Responsibilities:
 * 1. Estimate total campaign cost based on recipient count x unit price per country
 * 2. Check account balance sufficiency (prepay balance or postpay credit limit)
 * 3. Reserve funds via BalanceService (creates campaign_reservation row)
 * 4. Support cost preview (without reserving) for the UI
 *
 * Integrates with existing:
 * - PricingEngine: resolves unit price per product/country
 * - BalanceService: reserves funds, checks balance
 * - AccountBalance: current balance/credit state
 */
class BillingPreflightService
{
    public function __construct(
        private PricingEngine $pricingEngine,
        private BalanceService $balanceService,
    ) {}

    /**
     * Estimate the total cost of a campaign without reserving funds.
     *
     * Used by the UI to show "Estimated cost: X" before the user confirms send.
     *
     * @param Account $account The account sending the campaign
     * @param string $productType sms, rcs_basic, or rcs_single
     * @param array $countryBreakdown ['GB' => 500, 'US' => 200, ...]
     * @param int $segmentsPerMessage Average segments per SMS (1 for RCS)
     * @return CostEstimate
     */
    public function estimateCost(
        Account $account,
        string $productType,
        array $countryBreakdown,
        int $segmentsPerMessage = 1
    ): CostEstimate {
        $totalCost = '0';
        $perCountryCosts = [];
        $errors = [];

        foreach ($countryBreakdown as $countryIso => $recipientCount) {
            if ($countryIso === 'unknown' || $countryIso === null) {
                // For numbers with undetected country, use default (null country) pricing
                $countryIso = null;
            }

            try {
                $calculation = $this->pricingEngine->calculateMessageCost(
                    $account,
                    $productType,
                    $countryIso,
                    $segmentsPerMessage
                );

                $countryCost = bcmul($calculation->totalCost, (string) $recipientCount, 6);
                $totalCost = bcadd($totalCost, $countryCost, 4);

                $perCountryCosts[$countryIso ?? 'default'] = [
                    'country_iso' => $countryIso,
                    'recipient_count' => $recipientCount,
                    'unit_price' => $calculation->unitPrice,
                    'segments' => $calculation->segments,
                    'cost_per_message' => $calculation->totalCost,
                    'total_cost' => $countryCost,
                    'currency' => $calculation->currency,
                    'price_source' => $calculation->priceSource,
                ];
            } catch (PriceNotFoundException $e) {
                Log::warning('[BillingPreflight] No price found for country', [
                    'account_id' => $account->id,
                    'product_type' => $productType,
                    'country_iso' => $countryIso,
                ]);
                $errors[] = [
                    'country_iso' => $countryIso,
                    'recipient_count' => $recipientCount,
                    'error' => 'No pricing configured for this destination',
                ];
            }
        }

        $balance = AccountBalance::where('account_id', $account->id)->first();

        return new CostEstimate(
            totalCost: $totalCost,
            currency: $account->currency ?? 'GBP',
            perCountryCosts: $perCountryCosts,
            errors: $errors,
            availableBalance: $balance?->effective_available ?? '0',
            hasSufficientBalance: $balance ? $balance->hasSufficientBalance($totalCost) : false,
            isPostpay: $account->billing_type === 'postpay',
        );
    }

    /**
     * Estimate cost with per-recipient segment accuracy.
     *
     * Instead of assuming every recipient has the same segment count,
     * this method accepts rows grouped by (country_iso, segments) and
     * prices each group independently.
     *
     * Example: 3,000 GB recipients at 1 segment + 200 GB recipients at 2 segments
     * produces a different total than 3,200 GB recipients at 1.06 avg segments.
     *
     * @param Account $account
     * @param string $productType sms, rcs_basic, or rcs_single
     * @param \Illuminate\Support\Collection $breakdown Rows with country_iso, segments, recipient_count
     * @return CostEstimate
     */
    public function estimateCostPerSegmentGroup(
        Account $account,
        string $productType,
        $breakdown
    ): CostEstimate {
        $totalCost = '0';
        $perCountryCosts = [];
        $errors = [];

        foreach ($breakdown as $row) {
            $countryIso = $row->country_iso;
            if ($countryIso === 'unknown' || $countryIso === null) {
                $countryIso = null;
            }

            $segments = $row->segments ?: 1;
            $recipientCount = $row->recipient_count;

            try {
                $calculation = $this->pricingEngine->calculateMessageCost(
                    $account,
                    $productType,
                    $countryIso,
                    $segments
                );

                $groupCost = bcmul($calculation->totalCost, (string) $recipientCount, 6);
                $totalCost = bcadd($totalCost, $groupCost, 4);

                $countryKey = $countryIso ?? 'default';
                if (!isset($perCountryCosts[$countryKey])) {
                    $perCountryCosts[$countryKey] = [
                        'country_iso' => $countryIso,
                        'recipient_count' => 0,
                        'total_cost' => '0',
                        'currency' => $calculation->currency,
                        'price_source' => $calculation->priceSource,
                        'unit_price' => $calculation->unitPrice,
                        'segment_breakdown' => [],
                    ];
                }

                $perCountryCosts[$countryKey]['recipient_count'] += $recipientCount;
                $perCountryCosts[$countryKey]['total_cost'] = bcadd(
                    $perCountryCosts[$countryKey]['total_cost'],
                    $groupCost,
                    6
                );
                $perCountryCosts[$countryKey]['segment_breakdown'][] = [
                    'segments' => $segments,
                    'count' => $recipientCount,
                    'cost_per_message' => $calculation->totalCost,
                ];
            } catch (PriceNotFoundException $e) {
                Log::warning('[BillingPreflight] No price found for country (segment group)', [
                    'account_id' => $account->id,
                    'product_type' => $productType,
                    'country_iso' => $countryIso,
                    'segments' => $segments,
                ]);
                $errors[] = [
                    'country_iso' => $countryIso,
                    'recipient_count' => $recipientCount,
                    'error' => 'No pricing configured for this destination',
                ];
            }
        }

        // Calculate effective cost_per_message as weighted average per country
        foreach ($perCountryCosts as $key => &$country) {
            $country['cost_per_message'] = $country['recipient_count'] > 0
                ? bcdiv($country['total_cost'], (string) $country['recipient_count'], 6)
                : '0';
            // segments field is null because it varies per recipient
            $country['segments'] = null;
        }
        unset($country);

        $balance = AccountBalance::where('account_id', $account->id)->first();

        return new CostEstimate(
            totalCost: $totalCost,
            currency: $account->currency ?? 'GBP',
            perCountryCosts: $perCountryCosts,
            errors: $errors,
            availableBalance: $balance?->effective_available ?? '0',
            hasSufficientBalance: $balance ? $balance->hasSufficientBalance($totalCost) : false,
            isPostpay: $account->billing_type === 'postpay',
        );
    }

    /**
     * Run full billing preflight checks for a campaign.
     *
     * This is called before a campaign transitions to sending.
     * Validates balance, reserves funds, and updates the campaign.
     *
     * @throws InsufficientBalanceException
     * @throws PreflightFailedException
     */
    public function runPreflight(Campaign $campaign): PreflightResult
    {
        $account = Account::findOrFail($campaign->account_id);

        Log::info('[BillingPreflight] Running preflight', [
            'campaign_id' => $campaign->id,
            'account_id' => $account->id,
            'type' => $campaign->type,
        ]);

        // Step 1: Build country breakdown from resolved recipients
        $countryBreakdown = DB::table('campaign_recipients')
            ->where('campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->select('country_iso', DB::raw('COUNT(*) as count'))
            ->groupBy('country_iso')
            ->pluck('count', 'country_iso')
            ->toArray();

        if (empty($countryBreakdown)) {
            throw new PreflightFailedException('No sendable recipients found for this campaign.');
        }

        // Step 2: Estimate cost — use per-recipient segments if content is resolved
        if ($campaign->content_resolved_at) {
            $segmentBreakdown = DB::table('campaign_recipients')
                ->where('campaign_id', $campaign->id)
                ->where('status', 'pending')
                ->select('country_iso', 'segments', DB::raw('COUNT(*) as recipient_count'))
                ->groupBy('country_iso', 'segments')
                ->get();

            $estimate = $this->estimateCostPerSegmentGroup($account, $campaign->type, $segmentBreakdown);
        } else {
            $segmentsPerMessage = $campaign->segment_count ?: 1;
            $estimate = $this->estimateCost($account, $campaign->type, $countryBreakdown, $segmentsPerMessage);
        }

        // Step 3: Check for pricing errors (destinations with no pricing)
        if (!empty($estimate->errors)) {
            $missingCountries = array_column($estimate->errors, 'country_iso');
            Log::warning('[BillingPreflight] Missing pricing for some destinations', [
                'campaign_id' => $campaign->id,
                'missing_countries' => $missingCountries,
            ]);
            // Don't fail the preflight — just exclude those from the estimate
            // The DeliveryService will handle per-message pricing at send time
        }

        // Step 4: Check balance sufficiency
        if (!$estimate->hasSufficientBalance) {
            throw new InsufficientBalanceException(
                $account->id,
                $estimate->totalCost,
                $estimate->availableBalance
            );
        }

        // Step 5: Reserve funds
        $reservation = $this->balanceService->reserveForCampaign(
            accountId: $account->id,
            campaignId: $campaign->id,
            estimatedTotal: $estimate->totalCost,
            subAccountId: $campaign->sub_account_id,
        );

        // Step 6: Update campaign with cost estimate and reservation
        $campaign->update([
            'estimated_cost' => $estimate->totalCost,
            'currency' => $estimate->currency,
            'reservation_id' => $reservation->id,
        ]);

        Log::info('[BillingPreflight] Preflight complete', [
            'campaign_id' => $campaign->id,
            'estimated_cost' => $estimate->totalCost,
            'currency' => $estimate->currency,
            'reservation_id' => $reservation->id,
        ]);

        return new PreflightResult(
            approved: true,
            estimatedCost: $estimate->totalCost,
            currency: $estimate->currency,
            reservationId: $reservation->id,
            costEstimate: $estimate,
        );
    }

    /**
     * Release a campaign's fund reservation (on cancel or completion).
     *
     * Delegates to BalanceService::releaseReservation which returns
     * unused funds to the account balance.
     */
    public function releaseReservation(Campaign $campaign): void
    {
        if (!$campaign->reservation_id) {
            return;
        }

        $this->balanceService->releaseReservation($campaign->reservation_id);

        Log::info('[BillingPreflight] Reservation released', [
            'campaign_id' => $campaign->id,
            'reservation_id' => $campaign->reservation_id,
        ]);
    }
}

/**
 * Cost estimate DTO for campaign billing preview.
 */
class CostEstimate
{
    public function __construct(
        public readonly string $totalCost,
        public readonly string $currency,
        public readonly array $perCountryCosts,
        public readonly array $errors,
        public readonly string $availableBalance,
        public readonly bool $hasSufficientBalance,
        public readonly bool $isPostpay,
    ) {}

    public function toArray(): array
    {
        return [
            'total_cost' => $this->totalCost,
            'currency' => $this->currency,
            'per_country_costs' => $this->perCountryCosts,
            'errors' => $this->errors,
            'available_balance' => $this->availableBalance,
            'has_sufficient_balance' => $this->hasSufficientBalance,
            'is_postpay' => $this->isPostpay,
        ];
    }
}

/**
 * Preflight result DTO.
 */
class PreflightResult
{
    public function __construct(
        public readonly bool $approved,
        public readonly string $estimatedCost,
        public readonly string $currency,
        public readonly ?string $reservationId,
        public readonly CostEstimate $costEstimate,
    ) {}

    public function toArray(): array
    {
        return [
            'approved' => $this->approved,
            'estimated_cost' => $this->estimatedCost,
            'currency' => $this->currency,
            'reservation_id' => $this->reservationId,
            'cost_estimate' => $this->costEstimate->toArray(),
        ];
    }
}

/**
 * Exception thrown when billing preflight fails for non-balance reasons.
 */
class PreflightFailedException extends \RuntimeException {}
