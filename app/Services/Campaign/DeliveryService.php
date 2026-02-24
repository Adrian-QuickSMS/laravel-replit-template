<?php

namespace App\Services\Campaign;

use App\Contracts\SmsGateway;
use App\Contracts\GatewayException;
use App\Contracts\GatewayMessage;
use App\Contracts\GatewayResponse;
use App\Models\Account;
use App\Models\Campaign;
use App\Models\CampaignRecipient;
use App\Models\Gateway;
use App\Models\MessageLog;
use App\Models\RoutingRule;
use App\Services\Billing\BalanceService;
use App\Services\Billing\PricingEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * DeliveryService — dispatches individual messages through the gateway layer.
 *
 * Responsibilities:
 * 1. Route selection (select gateway via routing rules + weighted distribution)
 * 2. Gateway dispatch (HTTP REST API call via SmsGateway interface)
 * 3. Per-message billing (consume from campaign reservation)
 * 4. MessageLog creation (canonical delivery record)
 * 5. CampaignRecipient status updates
 * 6. Campaign counter increments (atomic)
 *
 * Does NOT handle:
 * - Batch orchestration (that's ProcessCampaignBatch job)
 * - DLR processing (that's HandleDeliveryReceipt job)
 * - Scheduling (that's ScheduledCampaignDispatcher job)
 */
class DeliveryService
{
    /** @var array<string, SmsGateway> Gateway implementations keyed by gateway_code */
    private array $gateways = [];

    public function __construct(
        private PricingEngine $pricingEngine,
        private BalanceService $balanceService,
    ) {}

    /**
     * Register a gateway implementation.
     * Called during service provider boot or via config.
     */
    public function registerGateway(SmsGateway $gateway): void
    {
        $this->gateways[$gateway->getGatewayCode()] = $gateway;
    }

    /**
     * Send a single campaign recipient's message.
     *
     * Full pipeline for one recipient:
     * Route selection -> Price lookup -> Gateway dispatch -> Billing -> MessageLog -> Status update
     *
     * @return bool True if the message was successfully submitted to the gateway
     */
    public function sendRecipient(Campaign $campaign, CampaignRecipient $recipient): bool
    {
        try {
            // Step 1: Resolve the gateway to use via routing rules
            $selectedGateway = $this->selectGateway($campaign->type, $recipient->country_iso);

            if (!$selectedGateway) {
                $recipient->markFailed('No available gateway for destination', 'NO_ROUTE');
                $this->incrementCampaignCounter($campaign, 'failed_count');
                return false;
            }

            // Step 2: Calculate per-message cost
            $account = Account::find($campaign->account_id);
            $cost = $this->calculateRecipientCost($account, $campaign, $recipient);

            // Step 3: Build gateway message
            $gatewayMessage = $this->buildGatewayMessage($campaign, $recipient);

            // Step 4: Dispatch to gateway
            $response = $selectedGateway['implementation']->sendMessage($gatewayMessage);

            if (!$response->accepted) {
                $recipient->markFailed(
                    $response->errorMessage ?? 'Gateway rejected message',
                    $response->errorCode
                );
                $this->incrementCampaignCounter($campaign, 'failed_count');

                Log::warning('[DeliveryService] Gateway rejected message', [
                    'campaign_id' => $campaign->id,
                    'recipient_id' => $recipient->id,
                    'gateway' => $selectedGateway['code'],
                    'error' => $response->errorMessage,
                ]);

                return false;
            }

            // Step 5: Create MessageLog entry
            $messageLogId = $this->createMessageLog($campaign, $recipient, $selectedGateway, $cost);

            // Step 6: Bill the message (consume from reservation)
            if ($campaign->reservation_id && $cost > 0) {
                $this->billMessage($campaign, $recipient, $cost, $messageLogId);
            }

            // Step 7: Update recipient status
            $recipient->markSent($response->messageId, $selectedGateway['id']);
            $recipient->update([
                'message_log_id' => $messageLogId,
                'cost' => $cost,
                'currency' => $campaign->currency ?: 'GBP',
            ]);

            // Step 8: Increment campaign counter
            $this->incrementCampaignCounter($campaign, 'sent_count');

            return true;

        } catch (GatewayException $e) {
            Log::error('[DeliveryService] Gateway exception', [
                'campaign_id' => $campaign->id,
                'recipient_id' => $recipient->id,
                'gateway' => $e->gatewayCode,
                'error' => $e->getMessage(),
            ]);

            $recipient->markFailed('Gateway error: ' . $e->getMessage(), $e->errorCode);
            $this->incrementCampaignCounter($campaign, 'failed_count');

            return false;

        } catch (\Exception $e) {
            Log::error('[DeliveryService] Unexpected error sending message', [
                'campaign_id' => $campaign->id,
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);

            $recipient->markFailed('System error: ' . $e->getMessage(), 'SYSTEM_ERROR');
            $this->incrementCampaignCounter($campaign, 'failed_count');

            return false;
        }
    }

    /**
     * Process a delivery receipt (DLR) from the gateway.
     *
     * Updates the CampaignRecipient and Campaign counters based on
     * the gateway's delivery status report.
     */
    public function processDeliveryReceipt(
        string $gatewayMessageId,
        string $status,
        ?string $deliveredChannel = null,
        ?string $errorCode = null,
        ?string $errorMessage = null
    ): bool {
        $recipient = CampaignRecipient::where('gateway_message_id', $gatewayMessageId)->first();

        if (!$recipient) {
            Log::warning('[DeliveryService] DLR for unknown message ID', [
                'gateway_message_id' => $gatewayMessageId,
                'status' => $status,
            ]);
            return false;
        }

        $campaign = $recipient->campaign;

        switch (strtolower($status)) {
            case 'delivered':
            case 'DELIVRD':
                $recipient->markDelivered($deliveredChannel);
                $this->incrementCampaignCounter($campaign, 'delivered_count');
                $this->decrementCampaignCounter($campaign, 'sent_count');

                // Track RCS->SMS fallback
                if ($campaign->isRcs() && $deliveredChannel === 'sms') {
                    $this->incrementCampaignCounter($campaign, 'fallback_sms_count');
                }
                break;

            case 'undeliverable':
            case 'UNDELIV':
            case 'expired':
            case 'EXPIRED':
                $recipient->update([
                    'status' => CampaignRecipient::STATUS_UNDELIVERABLE,
                    'failure_reason' => $errorMessage ?? 'Undeliverable',
                    'failure_code' => $errorCode,
                    'failed_at' => now(),
                ]);
                $this->incrementCampaignCounter($campaign, 'failed_count');
                $this->decrementCampaignCounter($campaign, 'sent_count');
                break;

            case 'failed':
            case 'REJECTD':
            case 'rejected':
                // Failed — check if retryable
                if ($recipient->canRetry()) {
                    $recipient->scheduleRetry();
                } else {
                    $recipient->markFailed($errorMessage ?? 'Delivery failed', $errorCode);
                    $this->incrementCampaignCounter($campaign, 'failed_count');
                    $this->decrementCampaignCounter($campaign, 'sent_count');
                }
                break;

            default:
                Log::info('[DeliveryService] Unknown DLR status', [
                    'gateway_message_id' => $gatewayMessageId,
                    'status' => $status,
                ]);
        }

        // Update the message_log record too
        if ($recipient->message_log_id) {
            $this->updateMessageLog($recipient->message_log_id, $status);
        }

        return true;
    }

    // =====================================================
    // ROUTING
    // =====================================================

    /**
     * Select a gateway based on routing rules and weighted distribution.
     *
     * @return array|null ['id' => ..., 'code' => ..., 'implementation' => SmsGateway] or null
     */
    private function selectGateway(string $productType, ?string $countryIso): ?array
    {
        // Find the routing rule for this product/destination
        $query = RoutingRule::active()->forProduct($productType);

        if ($countryIso) {
            $query->where(function ($q) use ($countryIso) {
                $q->where('destination_code', $countryIso)
                  ->orWhereNull('destination_code'); // fallback rule
            });
        }

        $rule = $query->orderByRaw("CASE WHEN destination_code IS NOT NULL THEN 0 ELSE 1 END")
            ->first();

        if (!$rule) {
            Log::warning('[DeliveryService] No routing rule found', [
                'product_type' => $productType,
                'country_iso' => $countryIso,
            ]);
            return null;
        }

        // Get eligible gateways with weights
        $eligibleGateways = $rule->getEligibleGateways();

        if ($eligibleGateways->isEmpty()) {
            // Fallback to primary gateway
            $primaryGateway = $rule->primaryGateway;
            if ($primaryGateway && $primaryGateway->active) {
                return $this->resolveGatewayImplementation($primaryGateway);
            }
            return null;
        }

        // Weighted random selection
        $selected = $this->weightedRandomSelect($eligibleGateways);
        if (!$selected) {
            return null;
        }

        return $this->resolveGatewayImplementation($selected->gateway);
    }

    /**
     * Select a gateway using weighted random distribution.
     */
    private function weightedRandomSelect($gateways)
    {
        $totalWeight = $gateways->sum('weight');
        if ($totalWeight === 0) {
            return $gateways->first();
        }

        $random = mt_rand(1, $totalWeight);
        $cumulative = 0;

        foreach ($gateways as $gw) {
            $cumulative += $gw->weight;
            if ($random <= $cumulative) {
                return $gw;
            }
        }

        return $gateways->last();
    }

    /**
     * Resolve a Gateway model to its SmsGateway implementation.
     */
    private function resolveGatewayImplementation(Gateway $gateway): ?array
    {
        $implementation = $this->gateways[$gateway->gateway_code] ?? null;

        if (!$implementation) {
            Log::error('[DeliveryService] No implementation registered for gateway', [
                'gateway_code' => $gateway->gateway_code,
                'gateway_id' => $gateway->id,
            ]);
            return null;
        }

        return [
            'id' => $gateway->id,
            'code' => $gateway->gateway_code,
            'implementation' => $implementation,
        ];
    }

    // =====================================================
    // BILLING
    // =====================================================

    /**
     * Calculate the cost for a single recipient's message.
     */
    private function calculateRecipientCost(
        Account $account,
        Campaign $campaign,
        CampaignRecipient $recipient
    ): string {
        try {
            $calculation = $this->pricingEngine->calculateMessageCost(
                $account,
                $campaign->type,
                $recipient->country_iso,
                $recipient->segments ?: 1
            );

            return $calculation->totalCost;
        } catch (\Exception $e) {
            Log::warning('[DeliveryService] Failed to calculate cost, using 0', [
                'campaign_id' => $campaign->id,
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);
            return '0';
        }
    }

    /**
     * Consume from the campaign's fund reservation for a single message.
     */
    private function billMessage(
        Campaign $campaign,
        CampaignRecipient $recipient,
        string $cost,
        string $messageLogId
    ): void {
        try {
            $account = Account::find($campaign->account_id);
            $isPostpay = $account?->billing_type === 'postpay';

            $this->balanceService->consumeFromReservation(
                $campaign->reservation_id,
                $cost,
                $campaign->currency ?: 'GBP',
                $campaign->type,
                $messageLogId,
                $isPostpay
            );
        } catch (\Exception $e) {
            Log::error('[DeliveryService] Failed to bill message', [
                'campaign_id' => $campaign->id,
                'recipient_id' => $recipient->id,
                'cost' => $cost,
                'error' => $e->getMessage(),
            ]);
            // Don't fail the send — billing reconciliation will catch this
        }
    }

    // =====================================================
    // MESSAGE LOG
    // =====================================================

    /**
     * Create a MessageLog entry for a sent message.
     */
    private function createMessageLog(
        Campaign $campaign,
        CampaignRecipient $recipient,
        array $gateway,
        string $cost
    ): string {
        $id = (string) Str::uuid();

        MessageLog::create([
            'id' => $id,
            'mobile_number' => $recipient->mobile_number,
            'sender_id' => $campaign->getSenderDisplayName() ?? 'Unknown',
            'status' => MessageLog::STATUS_PENDING,
            'sent_time' => now(),
            'cost' => (float) $cost,
            'type' => $campaign->type,
            'sub_account' => $campaign->sub_account_id ?? '',
            'user' => $campaign->created_by ?? '',
            'origin' => MessageLog::ORIGIN_PORTAL,
            'country' => $recipient->country_iso ?? '',
            'fragments' => $recipient->segments ?: 1,
            'encoding' => $campaign->encoding ?? MessageLog::ENCODING_GSM7,
            'billable_flag' => true,
        ]);

        // Encrypt and store message content
        if ($recipient->resolved_content) {
            $log = MessageLog::find($id);
            if ($log) {
                $log->setContentAttribute($recipient->resolved_content);
                $log->save();
            }
        }

        return $id;
    }

    /**
     * Update a MessageLog record based on DLR status.
     */
    private function updateMessageLog(string $messageLogId, string $dlrStatus): void
    {
        $log = MessageLog::find($messageLogId);
        if (!$log) {
            return;
        }

        $mappedStatus = match (strtolower($dlrStatus)) {
            'delivered', 'delivrd' => MessageLog::STATUS_DELIVERED,
            'undeliverable', 'undeliv', 'expired' => MessageLog::STATUS_UNDELIVERABLE,
            'failed', 'rejectd', 'rejected' => MessageLog::STATUS_REJECTED,
            default => $log->status,
        };

        $updates = ['status' => $mappedStatus];

        if ($mappedStatus === MessageLog::STATUS_DELIVERED) {
            $updates['delivery_time'] = now();
            $updates['completed_time'] = now();
        } elseif (in_array($mappedStatus, [MessageLog::STATUS_UNDELIVERABLE, MessageLog::STATUS_REJECTED])) {
            $updates['completed_time'] = now();
        }

        $log->update($updates);
    }

    // =====================================================
    // GATEWAY MESSAGE BUILDER
    // =====================================================

    /**
     * Build a GatewayMessage from a campaign and recipient.
     */
    private function buildGatewayMessage(Campaign $campaign, CampaignRecipient $recipient): GatewayMessage
    {
        $from = $campaign->getSenderDisplayName() ?? '';

        return new GatewayMessage(
            to: $recipient->mobile_number,
            from: $from,
            body: $recipient->resolved_content ?? $campaign->message_content,
            type: $campaign->type,
            rcsContent: $campaign->type === Campaign::TYPE_RCS_SINGLE ? $campaign->rcs_content : null,
            recipientId: $recipient->id,
            campaignId: $campaign->id,
            accountId: $campaign->account_id,
            metadata: [
                'campaign_name' => $campaign->name,
                'batch_number' => $recipient->batch_number,
            ],
        );
    }

    // =====================================================
    // CAMPAIGN COUNTER HELPERS
    // =====================================================

    /**
     * Atomically increment a campaign counter.
     */
    private function incrementCampaignCounter(Campaign $campaign, string $column): void
    {
        DB::table('campaigns')
            ->where('id', $campaign->id)
            ->increment($column);
    }

    /**
     * Atomically decrement a campaign counter (min 0).
     */
    private function decrementCampaignCounter(Campaign $campaign, string $column): void
    {
        DB::table('campaigns')
            ->where('id', $campaign->id)
            ->where($column, '>', 0)
            ->decrement($column);
    }
}
