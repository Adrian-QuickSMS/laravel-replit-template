<?php

namespace App\Services\Numbers;

use App\Models\Account;
use App\Models\NumberAssignment;
use App\Models\NumberAutoReplyRule;
use App\Models\PurchasedNumber;
use App\Models\SenderId;
use App\Models\ShortcodeKeyword;
use App\Models\SubAccount;
use App\Models\User;
use App\Models\VmnPoolNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * NumberService — core domain logic for purchasing, managing, and configuring numbers.
 *
 * Handles:
 * - VMN purchase from pool (claim + billing + SenderId auto-create)
 * - Shortcode keyword purchase (availability check + claim)
 * - Number release (return to pool + cancel recurring charge)
 * - Number suspension/reactivation
 * - Number configuration (forwarding, auto-reply)
 * - Number assignment (polymorphic to sub-accounts/users)
 * - Bulk operations (bulk assign, bulk release, CSV export)
 */
class NumberService
{
    public function __construct(
        private NumberBillingService $billing,
    ) {}

    // =====================================================
    // VMN PURCHASE
    // =====================================================

    /**
     * Purchase VMNs from the pool.
     *
     * Flow: Validate pool → Lock numbers → Debit setup fee → Create purchased_numbers
     *       → Mark pool sold → Create recurring charges → Auto-create SenderIds
     *       → Audit log
     *
     * @param string $accountId Tenant ID
     * @param array $poolNumberIds UUIDs of VmnPoolNumber records to purchase
     * @param User $purchaser User performing the purchase
     * @return array ['purchased_numbers' => PurchasedNumber[], 'audit_log' => PurchaseAuditLog]
     * @throws \RuntimeException if numbers unavailable or insufficient balance
     */
    public function purchaseVmns(
        string $accountId,
        array $poolNumberIds,
        User $purchaser
    ): array {
        return DB::transaction(function () use ($accountId, $poolNumberIds, $purchaser) {
            $account = Account::findOrFail($accountId);

            // Step 1: Lock and validate pool numbers
            $poolNumbers = VmnPoolNumber::whereIn('id', $poolNumberIds)
                ->lockForUpdate()
                ->get();

            if ($poolNumbers->count() !== count($poolNumberIds)) {
                throw new \RuntimeException('One or more selected numbers are no longer available.');
            }

            foreach ($poolNumbers as $pn) {
                if (!$pn->isAvailableForPurchase()) {
                    throw new \RuntimeException("Number {$pn->number} is no longer available.");
                }
            }

            // Step 2: Calculate pricing
            $pricing = $this->billing->calculateVmnPricing($account, $poolNumbers);

            // Step 3: Debit setup fee (immediate debit, refund on failure)
            $this->billing->debitSetupFee(
                $account,
                $pricing['total_setup_fee'],
                'vmn_purchase',
                count($poolNumbers) . ' VMN(s)'
            );

            // Step 4: Create purchased_number records + mark pool as sold + auto-create SenderIds
            $purchasedNumbers = [];
            foreach ($poolNumbers as $poolNumber) {
                $itemPricing = $pricing['items'][$poolNumber->id];

                $purchased = PurchasedNumber::withoutGlobalScopes()->create([
                    'account_id' => $accountId,
                    'vmn_pool_id' => $poolNumber->id,
                    'number' => $poolNumber->number,
                    'number_type' => PurchasedNumber::TYPE_VMN,
                    'country_iso' => $poolNumber->country_iso,
                    'friendly_name' => null,
                    'status' => PurchasedNumber::STATUS_ACTIVE,
                    'setup_fee' => $itemPricing['setup_fee'],
                    'monthly_fee' => $itemPricing['monthly_fee'],
                    'currency' => $account->currency ?? 'GBP',
                    'purchased_at' => now(),
                ]);

                // Mark pool number as sold
                $poolNumber->markSold();

                // Auto-create SenderId record
                $senderId = $this->autoCreateSenderId($purchased, $accountId, $purchaser);
                if ($senderId) {
                    $purchased->update(['sender_id_id' => $senderId->id]);
                }

                // Create recurring charge for monthly fee
                $this->billing->createRecurringCharge($account, $purchased);

                $purchasedNumbers[] = $purchased;
            }

            // Step 5: Audit log
            $auditLog = $this->billing->createPurchaseAuditLog(
                'vmn',
                $account,
                $purchaser,
                $purchasedNumbers,
                $pricing
            );

            Log::info('[NumberService] VMN purchase completed', [
                'account_id' => $accountId,
                'count' => count($purchasedNumbers),
                'total_setup' => $pricing['total_setup_fee'],
                'total_monthly' => $pricing['total_monthly_fee'],
            ]);

            return [
                'purchased_numbers' => $purchasedNumbers,
                'audit_log' => $auditLog,
                'pricing' => $pricing,
            ];
        });
    }

    // =====================================================
    // SHORTCODE KEYWORD PURCHASE
    // =====================================================

    /**
     * Purchase a keyword on a shared short code.
     *
     * @param string $accountId Tenant ID
     * @param string $shortcodeNumberId The purchased_number ID for the shared shortcode
     * @param string $keyword The keyword to reserve
     * @param User $purchaser User performing the purchase
     * @return array ['keyword' => ShortcodeKeyword, 'audit_log' => PurchaseAuditLog]
     */
    public function purchaseKeyword(
        string $accountId,
        string $shortcodeNumberId,
        string $keyword,
        User $purchaser
    ): array {
        return DB::transaction(function () use ($accountId, $shortcodeNumberId, $keyword, $purchaser) {
            $account = Account::findOrFail($accountId);

            // Validate shortcode exists
            $shortcode = PurchasedNumber::withoutGlobalScopes()
                ->where('id', $shortcodeNumberId)
                ->whereIn('number_type', [PurchasedNumber::TYPE_SHARED_SHORTCODE, PurchasedNumber::TYPE_DEDICATED_SHORTCODE])
                ->firstOrFail();

            // Check keyword availability (cross-tenant)
            if (ShortcodeKeyword::isKeywordTaken($shortcodeNumberId, $keyword)) {
                throw new \RuntimeException("Keyword '{$keyword}' is already taken on this short code.");
            }

            // Calculate pricing
            $pricing = $this->billing->calculateKeywordPricing($account);

            // Debit setup fee
            $this->billing->debitSetupFee(
                $account,
                $pricing['setup_fee'],
                'keyword_purchase',
                "Keyword '{$keyword}'"
            );

            // Create keyword record
            $keywordRecord = ShortcodeKeyword::withoutGlobalScopes()->create([
                'account_id' => $accountId,
                'purchased_number_id' => $shortcodeNumberId,
                'keyword' => $keyword,
                'status' => ShortcodeKeyword::STATUS_ACTIVE,
                'setup_fee' => $pricing['setup_fee'],
                'monthly_fee' => $pricing['monthly_fee'],
                'currency' => $account->currency ?? 'GBP',
                'purchased_at' => now(),
            ]);

            // Create recurring charge
            $this->billing->createKeywordRecurringCharge($account, $keywordRecord, $shortcode);

            // Audit log
            $auditLog = $this->billing->createKeywordPurchaseAuditLog(
                $account,
                $purchaser,
                $keywordRecord,
                $shortcode,
                $pricing
            );

            Log::info('[NumberService] Keyword purchase completed', [
                'account_id' => $accountId,
                'keyword' => $keyword,
                'shortcode' => $shortcode->number,
            ]);

            return [
                'keyword' => $keywordRecord,
                'audit_log' => $auditLog,
                'pricing' => $pricing,
            ];
        });
    }

    // =====================================================
    // RELEASE
    // =====================================================

    /**
     * Release a purchased number (return VMN to pool, cancel recurring charge).
     */
    public function releaseNumber(PurchasedNumber $number): void
    {
        DB::transaction(function () use ($number) {
            // Cancel recurring charge
            $this->billing->cancelRecurringCharge($number);

            // Remove all assignments
            NumberAssignment::where('purchased_number_id', $number->id)->delete();

            // Deactivate auto-reply rules
            NumberAutoReplyRule::withoutGlobalScopes()
                ->where('purchased_number_id', $number->id)
                ->update(['is_active' => false]);

            // Update number status
            $number->update([
                'status' => PurchasedNumber::STATUS_RELEASED,
                'released_at' => now(),
                'configuration' => null,
            ]);

            // Return VMN to pool if applicable
            if ($number->vmn_pool_id) {
                $poolNumber = VmnPoolNumber::find($number->vmn_pool_id);
                if ($poolNumber) {
                    $poolNumber->markAvailable();
                }
            }

            // Soft-delete the SenderId if it was auto-created
            if ($number->sender_id_id) {
                SenderId::withoutGlobalScopes()
                    ->where('id', $number->sender_id_id)
                    ->update([
                        'workflow_status' => SenderId::STATUS_REVOKED,
                        'revocation_reason' => 'Number released',
                        'deleted_at' => now(),
                    ]);
            }

            // Soft-delete the number itself
            $number->delete();

            Log::info('[NumberService] Number released', [
                'number_id' => $number->id,
                'number' => $number->number,
                'account_id' => $number->account_id,
            ]);
        });
    }

    /**
     * Release a shortcode keyword.
     */
    public function releaseKeyword(ShortcodeKeyword $keyword): void
    {
        DB::transaction(function () use ($keyword) {
            $this->billing->cancelKeywordRecurringCharge($keyword);

            $keyword->update([
                'status' => ShortcodeKeyword::STATUS_RELEASED,
                'released_at' => now(),
            ]);

            $keyword->delete();

            Log::info('[NumberService] Keyword released', [
                'keyword_id' => $keyword->id,
                'keyword' => $keyword->keyword,
                'account_id' => $keyword->account_id,
            ]);
        });
    }

    // =====================================================
    // SUSPEND / REACTIVATE
    // =====================================================

    /**
     * Suspend a number (e.g. due to non-payment).
     */
    public function suspendNumber(PurchasedNumber $number, string $reason = 'Suspended'): void
    {
        if ($number->status !== PurchasedNumber::STATUS_ACTIVE) {
            throw new \RuntimeException("Cannot suspend number in '{$number->status}' status.");
        }

        $number->update([
            'status' => PurchasedNumber::STATUS_SUSPENDED,
            'suspended_at' => now(),
        ]);

        Log::info('[NumberService] Number suspended', [
            'number_id' => $number->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Reactivate a suspended number.
     */
    public function reactivateNumber(PurchasedNumber $number): void
    {
        if ($number->status !== PurchasedNumber::STATUS_SUSPENDED) {
            throw new \RuntimeException("Cannot reactivate number in '{$number->status}' status.");
        }

        $number->update([
            'status' => PurchasedNumber::STATUS_ACTIVE,
            'suspended_at' => null,
        ]);

        Log::info('[NumberService] Number reactivated', [
            'number_id' => $number->id,
        ]);
    }

    // =====================================================
    // CONFIGURATION
    // =====================================================

    /**
     * Update number configuration (forwarding URL, email, auth headers, retry policy).
     */
    public function configureNumber(PurchasedNumber $number, array $config): PurchasedNumber
    {
        $validKeys = [
            'forwarding_url',
            'forwarding_email',
            'forwarding_auth_headers',
            'retry_policy',
        ];

        $sanitized = array_intersect_key($config, array_flip($validKeys));

        // Validate webhook URL if provided
        if (!empty($sanitized['forwarding_url'])) {
            $this->validateWebhookUrl($sanitized['forwarding_url']);
        }

        $number->updateConfiguration($sanitized);

        Log::info('[NumberService] Number configuration updated', [
            'number_id' => $number->id,
            'keys_updated' => array_keys($sanitized),
        ]);

        return $number->fresh();
    }

    /**
     * Validate a webhook URL by sending a test ping.
     *
     * @throws \RuntimeException if URL is unreachable
     */
    private function validateWebhookUrl(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('Invalid URL format.');
        }

        // Ensure HTTPS
        if (!str_starts_with($url, 'https://')) {
            throw new \RuntimeException('Webhook URL must use HTTPS.');
        }

        try {
            $response = Http::timeout(10)->head($url);
            // Accept any non-5xx response as "reachable"
            if ($response->serverError()) {
                throw new \RuntimeException("Webhook URL returned server error ({$response->status()}).");
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new \RuntimeException('Webhook URL is unreachable: ' . $e->getMessage());
        }
    }

    // =====================================================
    // AUTO-REPLY RULES
    // =====================================================

    /**
     * Add an auto-reply rule to a number.
     */
    public function addAutoReplyRule(
        PurchasedNumber $number,
        string $keyword,
        string $replyContent,
        string $matchType = 'exact',
        int $priority = 0,
        bool $chargeForReply = true
    ): NumberAutoReplyRule {
        return NumberAutoReplyRule::withoutGlobalScopes()->create([
            'account_id' => $number->account_id,
            'purchased_number_id' => $number->id,
            'keyword' => strtoupper(trim($keyword)),
            'reply_content' => $replyContent,
            'match_type' => $matchType,
            'is_active' => true,
            'priority' => $priority,
            'charge_for_reply' => $chargeForReply,
        ]);
    }

    /**
     * Update an auto-reply rule.
     */
    public function updateAutoReplyRule(NumberAutoReplyRule $rule, array $data): NumberAutoReplyRule
    {
        $allowed = ['keyword', 'reply_content', 'match_type', 'is_active', 'priority', 'charge_for_reply'];
        $filtered = array_intersect_key($data, array_flip($allowed));

        if (isset($filtered['keyword'])) {
            $filtered['keyword'] = strtoupper(trim($filtered['keyword']));
        }

        $rule->update($filtered);
        return $rule->fresh();
    }

    /**
     * Delete an auto-reply rule.
     */
    public function deleteAutoReplyRule(NumberAutoReplyRule $rule): void
    {
        $rule->delete();
    }

    // =====================================================
    // ASSIGNMENTS
    // =====================================================

    /**
     * Assign a number to a sub-account or user.
     */
    public function assignNumber(
        PurchasedNumber $number,
        string $assignableType,
        string $assignableId,
        User $assignedBy
    ): NumberAssignment {
        // Validate assignable exists
        if ($assignableType === SubAccount::class) {
            SubAccount::findOrFail($assignableId);
        } elseif ($assignableType === User::class) {
            User::findOrFail($assignableId);
        } else {
            throw new \RuntimeException("Invalid assignable type: {$assignableType}");
        }

        // Prevent duplicate assignment
        $existing = NumberAssignment::where('purchased_number_id', $number->id)
            ->where('assignable_type', $assignableType)
            ->where('assignable_id', $assignableId)
            ->first();

        if ($existing) {
            return $existing;
        }

        return NumberAssignment::create([
            'purchased_number_id' => $number->id,
            'assignable_type' => $assignableType,
            'assignable_id' => $assignableId,
            'assigned_by' => $assignedBy->id,
        ]);
    }

    /**
     * Remove an assignment.
     */
    public function unassignNumber(string $assignmentId): void
    {
        NumberAssignment::findOrFail($assignmentId)->delete();
    }

    /**
     * Bulk assign numbers to a sub-account.
     *
     * @param array $numberIds Array of purchased_number IDs
     * @param string $assignableType SubAccount::class or User::class
     * @param string $assignableId UUID
     * @param User $assignedBy
     * @return int Number of new assignments created
     */
    public function bulkAssign(
        array $numberIds,
        string $assignableType,
        string $assignableId,
        User $assignedBy
    ): int {
        $created = 0;

        foreach ($numberIds as $numberId) {
            $number = PurchasedNumber::find($numberId);
            if ($number && $number->isActive()) {
                $existing = NumberAssignment::where('purchased_number_id', $numberId)
                    ->where('assignable_type', $assignableType)
                    ->where('assignable_id', $assignableId)
                    ->exists();

                if (!$existing) {
                    NumberAssignment::create([
                        'purchased_number_id' => $numberId,
                        'assignable_type' => $assignableType,
                        'assignable_id' => $assignableId,
                        'assigned_by' => $assignedBy->id,
                    ]);
                    $created++;
                }
            }
        }

        return $created;
    }

    /**
     * Bulk release numbers.
     *
     * @param array $numberIds Array of purchased_number IDs
     * @return int Number of numbers released
     */
    public function bulkRelease(array $numberIds): int
    {
        $released = 0;

        foreach ($numberIds as $numberId) {
            $number = PurchasedNumber::find($numberId);
            if ($number && $number->isActive()) {
                $this->releaseNumber($number);
                $released++;
            }
        }

        return $released;
    }

    // =====================================================
    // POOL MANAGEMENT (Admin)
    // =====================================================

    /**
     * Seed numbers into the VMN pool (admin operation).
     *
     * @param array $numbers Array of ['number' => string, 'country_iso' => string, ...]
     * @param string $addedBy Admin user UUID
     * @return int Count of numbers added
     */
    public function seedPool(array $numbers, string $addedBy): int
    {
        $added = 0;

        foreach ($numbers as $data) {
            // Skip if already in pool
            $exists = VmnPoolNumber::where('number', $data['number'])->exists();
            if ($exists) {
                continue;
            }

            VmnPoolNumber::create([
                'number' => $data['number'],
                'country_iso' => $data['country_iso'],
                'number_type' => $data['number_type'] ?? 'mobile',
                'capabilities' => $data['capabilities'] ?? 'sms',
                'provider' => $data['provider'] ?? null,
                'provider_reference' => $data['provider_reference'] ?? null,
                'monthly_cost_override' => $data['monthly_cost_override'] ?? null,
                'setup_cost_override' => $data['setup_cost_override'] ?? null,
                'is_available' => true,
                'added_by' => $addedBy,
            ]);
            $added++;
        }

        return $added;
    }

    // =====================================================
    // SENDER ID AUTO-CREATION
    // =====================================================

    /**
     * Auto-create a SenderId record for a purchased VMN.
     * Status is set to 'approved' since VMNs are platform-verified.
     */
    private function autoCreateSenderId(
        PurchasedNumber $purchased,
        string $accountId,
        User $purchaser
    ): ?SenderId {
        try {
            return DB::transaction(function () use ($purchased, $accountId, $purchaser) {
                return SenderId::withoutGlobalScopes()->create([
                    'account_id' => $accountId,
                    'sender_id_value' => $purchased->number,
                    'sender_type' => SenderId::TYPE_NUMERIC,
                    'brand_name' => $purchased->friendly_name ?? $purchased->number,
                    'country_code' => $purchased->country_iso,
                    'use_case' => 'Virtual mobile number',
                    'use_case_description' => 'Auto-registered from VMN purchase',
                    'permission_confirmed' => true,
                    'workflow_status' => SenderId::STATUS_APPROVED,
                    'is_default' => false,
                    'created_by' => $purchaser->id,
                    'submitted_at' => now(),
                    'reviewed_at' => now(),
                    'is_locked' => true,
                    'full_payload' => [
                        'source' => 'vmn_purchase',
                        'purchased_number_id' => $purchased->id,
                    ],
                ]);
            });
        } catch (\Exception $e) {
            Log::warning('[NumberService] Skipped auto-create SenderId (savepoint rolled back)', [
                'number' => $purchased->number,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    // =====================================================
    // EXPORT
    // =====================================================

    /**
     * Export numbers to CSV format.
     *
     * @return string CSV content
     */
    public function exportToCsv(string $accountId, array $filters = []): string
    {
        $query = PurchasedNumber::where('account_id', $accountId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['number_type'])) {
            $query->where('number_type', $filters['number_type']);
        }
        if (!empty($filters['country_iso'])) {
            $query->where('country_iso', $filters['country_iso']);
        }

        $numbers = $query->orderBy('number')->get();

        $csv = "Number,Type,Country,Status,Friendly Name,Monthly Fee,Currency,Purchased At,Last Used At\n";

        foreach ($numbers as $n) {
            $csv .= implode(',', [
                $n->number,
                $n->number_type,
                $n->country_iso,
                $n->status,
                '"' . str_replace('"', '""', $n->friendly_name ?? '') . '"',
                $n->monthly_fee,
                $n->currency,
                $n->purchased_at?->toIso8601String() ?? '',
                $n->last_used_at?->toIso8601String() ?? '',
            ]) . "\n";
        }

        return $csv;
    }

    // =====================================================
    // LAST USED AT (static helper for integration hooks)
    // =====================================================

    /**
     * Update last_used_at for a number by its E.164 number string.
     * Called from DeliveryService (outbound) and inbound webhook handler.
     */
    public static function touchLastUsedByNumber(string $e164Number): void
    {
        DB::table('purchased_numbers')
            ->where('number', $e164Number)
            ->where('status', PurchasedNumber::STATUS_ACTIVE)
            ->whereNull('deleted_at')
            ->update(['last_used_at' => now()]);
    }
}
