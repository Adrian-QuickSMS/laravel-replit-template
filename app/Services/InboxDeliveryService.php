<?php

namespace App\Services;

use App\Models\Account;
use App\Models\InboxConversation;
use App\Models\InboxMessage;
use App\Models\PurchasedNumber;
use App\Models\SenderId;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handles sending outbound messages from the Inbox.
 *
 * Responsibilities:
 * - Balance/credit check before sending
 * - Route through gateway (reuses existing SmsGateway interface)
 * - Create inbox_message record
 * - Update conversation metadata
 * - RCS-to-SMS fallback on failure
 */
class InboxDeliveryService
{
    private InboxService $inboxService;

    public function __construct(InboxService $inboxService)
    {
        $this->inboxService = $inboxService;
    }

    /**
     * Send a reply within an existing conversation.
     *
     * @return array{success: bool, message: ?InboxMessage, error: ?string}
     */
    public function sendReply(
        InboxConversation $conversation,
        string $content,
        string $channel = 'sms',
        ?array $rcsPayload = null,
        ?string $purchasedNumberId = null,
        ?string $rcsAgentId = null,
        ?string $smsFallbackId = null
    ): array {
        $accountId = $conversation->account_id;

        // 1. Balance check
        $balanceCheck = $this->checkBalance($accountId, $channel);
        if (!$balanceCheck['ok']) {
            return ['success' => false, 'message' => null, 'error' => $balanceCheck['error']];
        }

        // 2. Resolve from number from purchased_number_id
        $fromNumber = $this->resolveFromNumber(
            $purchasedNumberId ?? $conversation->purchased_number_id,
            $channel === 'rcs' ? ($smsFallbackId ?? $purchasedNumberId ?? $conversation->purchased_number_id) : null
        );
        $toNumber = $conversation->phone_number;

        // 3. Calculate cost and fragments
        $fragments = $this->calculateFragments($content, $channel);
        $cost = $this->calculateCost($accountId, $channel, $conversation->phone_number);

        // 4. Attempt send via gateway
        try {
            $gatewayResult = $this->sendViaGateway(
                $accountId,
                $fromNumber,
                $toNumber,
                $content,
                $channel,
                $rcsPayload
            );
        } catch (\Exception $e) {
            // RCS fallback to SMS
            if ($channel === 'rcs') {
                Log::info("Inbox: RCS send failed for conversation {$conversation->id}, falling back to SMS", [
                    'error' => $e->getMessage(),
                ]);
                return $this->sendReply($conversation, $content, 'sms', null, $senderId);
            }

            Log::error("Inbox: Send failed for conversation {$conversation->id}", [
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'message' => null, 'error' => 'Message delivery failed. Please try again.'];
        }

        // 5. Create inbox message record
        $message = $this->inboxService->addMessage(
            $conversation,
            'outbound',
            $content,
            $fromNumber,
            $toNumber,
            [
                'channel' => $channel,
                'rcs_payload' => $rcsPayload,
                'cost' => $cost,
                'fragments' => $fragments,
                'encoding' => $this->detectEncoding($content),
                'gateway_message_id' => $gatewayResult['message_id'] ?? null,
                'message_log_id' => $gatewayResult['log_id'] ?? null,
            ]
        );

        // 6. Deduct balance
        $this->deductBalance($accountId, $cost);

        return ['success' => true, 'message' => $message, 'error' => null];
    }

    private function resolveFromNumber(?string $purchasedNumberId, ?string $fallbackId = null): string
    {
        if ($purchasedNumberId) {
            $number = PurchasedNumber::withoutGlobalScope('tenant')->find($purchasedNumberId);
            if ($number) {
                return '+' . $number->number;
            }

            $sender = SenderId::withoutGlobalScope('tenant')
                ->where('uuid', $purchasedNumberId)
                ->where('workflow_status', 'approved')
                ->first();
            if ($sender) {
                return $sender->sender_id_value;
            }
        }

        if ($fallbackId && $fallbackId !== $purchasedNumberId) {
            $fallback = PurchasedNumber::withoutGlobalScope('tenant')->find($fallbackId);
            if ($fallback) {
                return '+' . $fallback->number;
            }
        }

        return 'QuickSMS';
    }

    /**
     * Check if the account has sufficient balance to send.
     */
    private function checkBalance(string $accountId, string $channel): array
    {
        $account = Account::withoutGlobalScope('tenant')->find($accountId);
        if (!$account) {
            return ['ok' => false, 'error' => 'Account not found'];
        }

        // Test mode accounts bypass balance checks
        if ($account->isTestMode()) {
            return ['ok' => true, 'error' => null];
        }

        // Check if account is active
        if (!in_array($account->status, ['active_standard', 'active_dynamic'])) {
            return ['ok' => false, 'error' => 'Account is not active for sending'];
        }

        // TODO: Integrate with billing engine for real balance check
        // For now, allow sending if account is active
        return ['ok' => true, 'error' => null];
    }

    /**
     * Send message through gateway infrastructure.
     *
     * @return array{message_id: ?string, log_id: ?string}
     */
    private function sendViaGateway(
        string $accountId,
        string $from,
        string $to,
        string $content,
        string $channel,
        ?array $rcsPayload
    ): array {
        // TODO: Wire to actual gateway delivery service
        // This should use the existing SmsGateway interface and RoutingRule system.
        //
        // For now, return a placeholder gateway response.
        // The gateway integration will be built when the gateway module is ready.
        Log::info("Inbox: Gateway send placeholder", [
            'account_id' => $accountId,
            'from' => $from,
            'to' => $to,
            'channel' => $channel,
            'content_length' => strlen($content),
        ]);

        return [
            'message_id' => 'gw_' . bin2hex(random_bytes(8)),
            'log_id' => null,
        ];
    }

    private function calculateFragments(string $content, string $channel): int
    {
        if ($channel === 'rcs') {
            return 1;
        }

        $encoding = $this->detectEncoding($content);
        $len = mb_strlen($content);

        if ($encoding === 'gsm7') {
            return $len <= 160 ? 1 : (int) ceil($len / 153);
        }

        return $len <= 70 ? 1 : (int) ceil($len / 67);
    }

    private function calculateCost(string $accountId, string $channel, string $toNumber): float
    {
        // TODO: Integrate with PricingEngine for real cost calculation
        // This will look up the customer's rate for the destination country + channel
        return 0.0;
    }

    private function deductBalance(string $accountId, float $cost): void
    {
        if ($cost <= 0) {
            return;
        }

        // TODO: Integrate with LedgerEngine for real balance deduction
        // This will create a debit entry in the double-entry ledger
        Log::info("Inbox: Balance deduction placeholder", [
            'account_id' => $accountId,
            'cost' => $cost,
        ]);
    }

    private function detectEncoding(string $content): string
    {
        // GSM 7-bit basic character set detection
        $gsm7 = '@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ ÆæßÉ !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZ'
               . 'ÄÖÑÜabcdefghijklmnopqrstuvwxyzäöñüà';
        $extended = '|^€{}[~]\\';

        for ($i = 0; $i < mb_strlen($content); $i++) {
            $char = mb_substr($content, $i, 1);
            if (mb_strpos($gsm7, $char) === false && mb_strpos($extended, $char) === false) {
                return 'unicode';
            }
        }

        return 'gsm7';
    }
}
