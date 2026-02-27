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
    // NUMBER LISTING FOR OPT-OUT SELECTOR (Red Circle)
    // =====================================================

    /**
     * Get numbers available for opt-out reply, filtered by user access.
     *
     * Returns VMNs, shortcodes, and shortcode keywords usable by the current user.
     * Respects NumberAssignment (same as SenderIdAssignment pattern).
     *
     * @return array ['vmns' => [...], 'shortcodes' => [...], 'keywords' => [...]]
     */
    public function getAvailableOptOutNumbers(string $accountId, $user): array
    {
        // VMNs usable by this user
        $vmns = PurchasedNumber::usableByUser($user)
            ->vmns()
            ->active()
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

        // Shortcodes usable by this user
        $shortcodes = PurchasedNumber::usableByUser($user)
            ->shortcodes()
            ->active()
            ->with('keywords')
            ->select('id', 'number', 'friendly_name', 'number_type', 'country_iso')
            ->orderBy('number')
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'number' => $n->number,
                'friendly_name' => $n->friendly_name,
                'country_iso' => $n->country_iso,
                'type' => $n->number_type,
                'is_dedicated' => $n->number_type === PurchasedNumber::TYPE_DEDICATED_SHORTCODE,
                'keywords' => $n->keywords->where('status', 'active')->map(fn($k) => [
                    'id' => $k->id,
                    'keyword' => $k->keyword,
                ])->values()->toArray(),
            ])
            ->toArray();

        return [
            'vmns' => $vmns,
            'shortcodes' => $shortcodes,
        ];
    }

    // =====================================================
    // KEYWORD VALIDATION (Blue Circle)
    // =====================================================

    /**
     * Validate an opt-out keyword for a campaign.
     *
     * Rules:
     * - 4-10 characters, alphanumeric only
     * - Dedicated VMN/shortcode: any keyword, as long as not in-flight on same number
     * - Shared shortcode: must be a keyword the account has purchased on that shortcode
     * - Not already used by another in-flight campaign on the same number
     *
     * @throws \RuntimeException with validation error message
     */
    public function validateOptOutKeyword(
        string $keyword,
        string $numberId,
        string $accountId,
        ?string $excludeCampaignId = null
    ): void {
        // Format validation
        $keyword = strtoupper(trim($keyword));

        if (strlen($keyword) < 4 || strlen($keyword) > 10) {
            throw new \RuntimeException('Opt-out keyword must be 4-10 characters.');
        }

        if (!preg_match('/^[A-Z0-9]+$/', $keyword)) {
            throw new \RuntimeException('Opt-out keyword must be alphanumeric only (no spaces or special characters).');
        }

        // Load the number
        $number = PurchasedNumber::withoutGlobalScopes()->findOrFail($numberId);

        // Shared shortcode: keyword must be purchased by this account
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

        // In-flight uniqueness: check no other active campaign uses this keyword on this number
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

        // Filter out keywords in use by in-flight campaigns
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

    /**
     * Generate the suggested opt-out text for a campaign.
     */
    public function generateOptOutText(string $keyword, string $number): string
    {
        return "Reply {$keyword} to {$number} to opt out";
    }

    // =====================================================
    // OPT-OUT URL GENERATION (per-recipient)
    // =====================================================

    /**
     * Generate opt-out URLs for a batch of recipients.
     *
     * Called during content resolution (ResolveRecipientContentJob).
     * Creates CampaignOptOutUrl records and returns a map of mobile_number → URL.
     *
     * @param string $campaignId
     * @param string $accountId
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

        // Bulk insert for performance
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
     *
     * Called by HandleInboundSms when a message arrives on a purchased number.
     * Checks if the message matches any active campaign's opt-out keyword on this number.
     *
     * @return bool True if opt-out was processed
     */
    public function processInboundOptOut(
        string $destinationNumber,
        string $senderMobile,
        string $messageBody
    ): bool {
        $normalizedKeyword = strtoupper(trim($messageBody));

        // Find the purchased number
        $number = PurchasedNumber::withoutGlobalScopes()
            ->where('number', $destinationNumber)
            ->where('status', PurchasedNumber::STATUS_ACTIVE)
            ->first();

        if (!$number) {
            return false;
        }

        // Find in-flight campaigns using this number with this keyword
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

        // Record the opt-out
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

        // Record the click (first click only)
        $optOutUrl->recordClick($ip);

        // Confirm unsubscribe
        $optOutUrl->confirmUnsubscribe($ip);

        // Load campaign to get opt-out list
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
    // OPT-OUT RECORD CREATION (shared by reply + URL flows)
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
        ?string $campaignRef
    ): bool {
        $accountId = $campaign->account_id;
        $isDuplicate = false;

        DB::transaction(function () use ($campaign, $mobileNumber, $source, $campaignRef, $accountId, &$isDuplicate) {
            // 1. Add to the campaign's designated opt-out list (if set)
            if ($campaign->opt_out_list_id) {
                $created = $this->insertOptOutRecord(
                    $accountId,
                    $campaign->opt_out_list_id,
                    $mobileNumber,
                    $source,
                    $campaignRef
                );
                if (!$created) {
                    $isDuplicate = true;
                }
            }

            // 2. Also add to the account's master opt-out list
            $masterList = OptOutList::withoutGlobalScopes()
                ->where('account_id', $accountId)
                ->where('is_master', true)
                ->first();

            if ($masterList && $masterList->id !== $campaign->opt_out_list_id) {
                $this->insertOptOutRecord(
                    $accountId,
                    $masterList->id,
                    $mobileNumber,
                    $source,
                    $campaignRef
                );
            }
        });

        Log::info('[OptOutService] Opt-out recorded', [
            'campaign_id' => $campaign->id,
            'mobile_number' => substr($mobileNumber, 0, 6) . '***',
            'source' => $source,
            'is_duplicate' => $isDuplicate,
        ]);

        return !$isDuplicate;
    }

    /**
     * Insert an opt-out record, handling duplicates gracefully.
     *
     * @return bool True if newly created, false if duplicate
     */
    private function insertOptOutRecord(
        string $accountId,
        string $listId,
        string $mobileNumber,
        string $source,
        ?string $campaignRef
    ): bool {
        // Check for existing record (duplicate)
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

        // Refresh denormalized count
        DB::table('opt_out_lists')
            ->where('id', $listId)
            ->update([
                'count' => DB::raw('(SELECT COUNT(*) FROM opt_out_records WHERE opt_out_list_id = opt_out_lists.id)'),
                'updated_at' => now(),
            ]);

        return true;
    }

    // =====================================================
    // CLEANUP
    // =====================================================

    /**
     * Clean up expired opt-out URL tokens.
     * Should be run daily via scheduler.
     */
    public static function cleanupExpiredTokens(): int
    {
        return DB::table('campaign_opt_out_urls')
            ->where('expires_at', '<', now())
            ->where('unsubscribed', false)
            ->delete();
    }
}
