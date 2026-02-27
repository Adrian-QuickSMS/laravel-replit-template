<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignOptOutUrl;
use App\Models\OptOutList;
use App\Models\OptOutRecord;
use App\Models\PurchasedNumber;
use App\Models\ShortcodeKeyword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * OptOutService — core business logic for campaign opt-out management.
 *
 * Handles:
 * - Keyword validation (availability, in-flight uniqueness, shared shortcode rules)
 * - Inbound SMS opt-out processing (keyword match → OptOutRecord creation)
 * - Opt-out URL generation per recipient during content resolution
 * - Landing page click processing
 * - Available numbers listing for opt-out number selector
 * - Opt-out text generation
 */
class OptOutService
{
    // =====================================================
    // NUMBER LISTING FOR OPT-OUT SELECTOR
    // =====================================================

    public function getAvailableOptOutNumbers(string $accountId, $user): array
    {
        $baseQuery = PurchasedNumber::withoutGlobalScope('tenant')
            ->where('purchased_numbers.account_id', $accountId)
            ->where('status', PurchasedNumber::STATUS_ACTIVE)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($user) {
                $q->whereDoesntHave('assignments')
                    ->orWhereHas('assignments', function ($aq) use ($user) {
                        $aq->where(function ($inner) use ($user) {
                            $inner->where('assignable_type', \App\Models\User::class)
                                ->where('assignable_id', $user->id);
                        });
                        if ($user->sub_account_id) {
                            $aq->orWhere(function ($inner) use ($user) {
                                $inner->where('assignable_type', \App\Models\SubAccount::class)
                                    ->where('assignable_id', $user->sub_account_id);
                            });
                        }
                    });
            });

        $vmns = (clone $baseQuery)
            ->where('number_type', PurchasedNumber::TYPE_VMN)
            ->select('id', 'number', 'friendly_name', 'country_iso')
            ->orderBy('number')
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'number' => $n->number,
                'friendly_name' => $n->friendly_name,
                'country_iso' => $n->country_iso,
                'type' => 'vmn',
                'is_dedicated' => true,
            ])
            ->toArray();

        // Dedicated shortcodes owned directly by the account
        $dedicated = (clone $baseQuery)
            ->where('number_type', PurchasedNumber::TYPE_DEDICATED_SHORTCODE)
            ->select('id', 'number', 'friendly_name', 'country_iso')
            ->orderBy('number')
            ->get()
            ->map(fn($n) => [
                'id'           => $n->id,
                'number'       => $n->number,
                'friendly_name'=> $n->friendly_name,
                'country_iso'  => $n->country_iso,
                'type'         => PurchasedNumber::TYPE_DEDICATED_SHORTCODE,
                'is_dedicated' => true,
                'keyword'      => null,
            ])
            ->toArray();

        // Shared shortcodes: one entry per active keyword the account has registered
        $sharedEntries = ShortcodeKeyword::withoutGlobalScope('tenant')
            ->where('account_id', $accountId)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->with(['purchasedNumber' => fn($q) => $q->withoutGlobalScopes()])
            ->orderBy('keyword')
            ->get()
            ->filter(fn($k) => $k->purchasedNumber !== null)
            ->map(fn($k) => [
                'id'           => $k->purchasedNumber->id,
                'number'       => $k->purchasedNumber->number,
                'friendly_name'=> $k->purchasedNumber->friendly_name,
                'country_iso'  => $k->purchasedNumber->country_iso,
                'type'         => PurchasedNumber::TYPE_SHARED_SHORTCODE,
                'is_dedicated' => false,
                'keyword'      => $k->keyword,
            ])
            ->values()
            ->toArray();

        $shortcodes = array_merge($dedicated, $sharedEntries);

        return [
            'vmns'       => $vmns,
            'shortcodes' => $shortcodes,
        ];
    }

    // =====================================================
    // KEYWORD VALIDATION
    // =====================================================

    /**
     * Validate an opt-out keyword for a campaign.
     *
     * Rules:
     * - 4-10 characters, alphanumeric only
     * - Dedicated VMN/shortcode: any keyword not in-flight on same number
     * - Shared shortcode: must be a purchased keyword on that shortcode
     *
     * @throws \RuntimeException with validation error message
     */
    public function validateOptOutKeyword(
        string $keyword,
        string $numberId,
        string $accountId,
        ?string $excludeCampaignId = null
    ): void {
        $keyword = strtoupper(trim($keyword));

        if (strlen($keyword) < 4 || strlen($keyword) > 10) {
            throw new \RuntimeException('Opt-out keyword must be 4-10 characters.');
        }

        if (!preg_match('/^[A-Z0-9]+$/', $keyword)) {
            throw new \RuntimeException('Opt-out keyword must be alphanumeric only (no spaces or special characters).');
        }

        $number = PurchasedNumber::withoutGlobalScopes()->findOrFail($numberId);

        if ($number->number_type === PurchasedNumber::TYPE_SHARED_SHORTCODE) {
            $hasPurchasedKeyword = ShortcodeKeyword::withoutGlobalScopes()
                ->where('purchased_number_id', $numberId)
                ->where('account_id', $accountId)
                ->where('keyword', $keyword)
                ->where('status', ShortcodeKeyword::STATUS_ACTIVE)
                ->exists();

            if (!$hasPurchasedKeyword) {
                throw new \RuntimeException(
                    "Keyword '{$keyword}' is not purchased on this shared short code. "
                    . "You can only use keywords you have purchased."
                );
            }
        }

        $conflictQuery = Campaign::withoutGlobalScopes()
            ->where('opt_out_number_id', $numberId)
            ->where('opt_out_keyword', $keyword)
            ->whereNotIn('status', [
                Campaign::STATUS_COMPLETED,
                Campaign::STATUS_CANCELLED,
                Campaign::STATUS_FAILED,
            ]);

        if ($excludeCampaignId) {
            $conflictQuery->where('id', '!=', $excludeCampaignId);
        }

        if ($conflictQuery->exists()) {
            throw new \RuntimeException(
                "Keyword '{$keyword}' is already in use by an active campaign on this number."
            );
        }
    }

    /**
     * Get available keywords for a shared shortcode (purchased by this account, not in-flight).
     */
    public function getAvailableKeywords(string $numberId, string $accountId): array
    {
        $purchasedKeywords = ShortcodeKeyword::withoutGlobalScopes()
            ->where('purchased_number_id', $numberId)
            ->where('account_id', $accountId)
            ->where('status', ShortcodeKeyword::STATUS_ACTIVE)
            ->pluck('keyword')
            ->toArray();

        $inFlightKeywords = Campaign::withoutGlobalScopes()
            ->where('opt_out_number_id', $numberId)
            ->whereNotNull('opt_out_keyword')
            ->whereNotIn('status', [
                Campaign::STATUS_COMPLETED,
                Campaign::STATUS_CANCELLED,
                Campaign::STATUS_FAILED,
            ])
            ->pluck('opt_out_keyword')
            ->map(fn($k) => strtoupper($k))
            ->toArray();

        return array_values(array_diff($purchasedKeywords, $inFlightKeywords));
    }

    // =====================================================
    // OPT-OUT TEXT GENERATION
    // =====================================================

    public function generateOptOutText(string $keyword, string $number): string
    {
        return "OptOut, {$keyword} to {$number}";
    }

    // =====================================================
    // OPT-OUT URL GENERATION (per-recipient)
    // =====================================================

    /**
     * Generate opt-out URLs for a batch of recipients.
     * Called during content resolution.
     *
     * @param array $mobileNumbers Array of E.164 numbers
     * @return array<string, string> mobile_number => full URL
     */
    public function generateOptOutUrls(
        string $campaignId,
        string $accountId,
        array $mobileNumbers
    ): array {
        $urlMap = [];
        $rows = [];

        foreach ($mobileNumbers as $mobile) {
            $token = CampaignOptOutUrl::generateToken();
            $rows[] = [
                'id' => (string) Str::uuid(),
                'account_id' => $accountId,
                'campaign_id' => $campaignId,
                'mobile_number' => $mobile,
                'token' => $token,
                'expires_at' => now()->addDays(CampaignOptOutUrl::TTL_DAYS),
                'created_at' => now(),
            ];
            $urlMap[$mobile] = CampaignOptOutUrl::BASE_URL . $token;
        }

        if (!empty($rows)) {
            foreach (array_chunk($rows, 1000) as $chunk) {
                DB::table('campaign_opt_out_urls')->insert($chunk);
            }
        }

        return $urlMap;
    }

    // =====================================================
    // INBOUND SMS OPT-OUT PROCESSING
    // =====================================================

    /**
     * Process an inbound SMS for opt-out keyword matching.
     * Called by HandleInboundSms — Step 1 (highest priority).
     *
     * @return bool True if opt-out was processed
     */
    public function processInboundOptOut(
        string $destinationNumber,
        string $senderMobile,
        string $messageBody
    ): bool {
        $normalizedKeyword = strtoupper(trim($messageBody));

        $number = PurchasedNumber::withoutGlobalScopes()
            ->where('number', $destinationNumber)
            ->where('status', PurchasedNumber::STATUS_ACTIVE)
            ->first();

        if (!$number) {
            return false;
        }

        $campaign = Campaign::withoutGlobalScopes()
            ->where('opt_out_number_id', $number->id)
            ->where('opt_out_enabled', true)
            ->whereIn('opt_out_method', ['reply', 'both'])
            ->whereNotIn('status', [
                Campaign::STATUS_COMPLETED,
                Campaign::STATUS_CANCELLED,
                Campaign::STATUS_FAILED,
            ])
            ->get()
            ->first(function ($c) use ($normalizedKeyword) {
                return strtoupper($c->opt_out_keyword) === $normalizedKeyword;
            });

        if (!$campaign) {
            return false;
        }

        return $this->recordOptOut(
            $campaign,
            $senderMobile,
            'campaign_sms_reply',
            $campaign->id
        );
    }

    // =====================================================
    // URL CLICK OPT-OUT PROCESSING
    // =====================================================

    /**
     * Process an unsubscribe confirmation from the landing page.
     *
     * @return array ['success' => bool, 'message' => string]
     */
    public function processUrlOptOut(string $token, string $ip): array
    {
        $optOutUrl = CampaignOptOutUrl::where('token', $token)->first();

        if (!$optOutUrl) {
            return ['success' => false, 'message' => 'Invalid link.'];
        }

        if ($optOutUrl->isExpired()) {
            return ['success' => false, 'message' => 'This opt-out link has expired.'];
        }

        if ($optOutUrl->isAlreadyUnsubscribed()) {
            return ['success' => true, 'message' => 'You have already been unsubscribed.'];
        }

        $optOutUrl->recordClick($ip);
        $optOutUrl->confirmUnsubscribe($ip);

        $campaign = Campaign::withoutGlobalScopes()->find($optOutUrl->campaign_id);

        if ($campaign) {
            $this->recordOptOut(
                $campaign,
                $optOutUrl->mobile_number,
                'campaign_url_click',
                $campaign->id
            );
        }

        return ['success' => true, 'message' => 'Unsubscribed'];
    }

    // =====================================================
    // OPT-OUT RECORD CREATION
    // =====================================================

    /**
     * Record an opt-out in the designated list + master list.
     *
     * @return bool True if new opt-out was created (not a duplicate)
     */
    private function recordOptOut(
        Campaign $campaign,
        string $mobileNumber,
        string $source,
        string $campaignRef
    ): bool {
        $listId = $campaign->opt_out_list_id;

        if (!$listId) {
            Log::warning('[OptOutService] Campaign has no opt-out list — cannot record opt-out', [
                'campaign_id' => $campaign->id,
            ]);
            return false;
        }

        $accountId = $campaign->account_id;

        return DB::transaction(function () use ($listId, $accountId, $mobileNumber, $source, $campaignRef) {
            $exists = DB::table('opt_out_records')
                ->where('opt_out_list_id', $listId)
                ->where('mobile_number', $mobileNumber)
                ->exists();

            if ($exists) {
                Log::info('[OptOutService] Duplicate opt-out skipped', [
                    'list_id' => $listId,
                    'mobile' => substr($mobileNumber, 0, 6) . '***',
                ]);
                return false;
            }

            DB::table('opt_out_records')->insert([
                'id' => (string) Str::uuid(),
                'account_id' => $accountId,
                'opt_out_list_id' => $listId,
                'mobile_number' => $mobileNumber,
                'source' => $source,
                'campaign_ref' => $campaignRef,
                'created_at' => now(),
            ]);

            DB::table('opt_out_lists')
                ->where('id', $listId)
                ->update([
                    'count' => DB::raw('(SELECT COUNT(*) FROM opt_out_records WHERE opt_out_list_id = opt_out_lists.id)'),
                    'updated_at' => now(),
                ]);

            return true;
        });
    }

    // =====================================================
    // CLEANUP
    // =====================================================

    public static function cleanupExpiredTokens(): int
    {
        return DB::table('campaign_opt_out_urls')
            ->where('expires_at', '<', now())
            ->where('unsubscribed', false)
            ->delete();
    }
}
