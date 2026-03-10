<?php

namespace App\Services;

use App\Models\InboxConversation;
use App\Models\Notification;
use App\Models\NumberAutoReplyRule;
use App\Models\PurchasedNumber;
use App\Models\ApiConnection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Processes inbound messages from gateways and routes them to:
 * 1. Number-level config (inbox_enabled / forwarding_url)
 * 2. API connection webhook (webhook_inbound_url)
 * 3. Auto-reply rules (keyword matching)
 *
 * Opt-out keywords skip conversation creation.
 * Inbox and webhook forwarding can run in parallel.
 */
class InboundRoutingService
{
    private InboxService $inboxService;
    private InboxDeliveryService $deliveryService;

    public function __construct(InboxService $inboxService, InboxDeliveryService $deliveryService)
    {
        $this->inboxService = $inboxService;
        $this->deliveryService = $deliveryService;
    }

    /**
     * Process an inbound message from a gateway.
     *
     * @param array $payload Gateway-normalised payload:
     *   - to: string (E.164 — our number)
     *   - from: string (E.164 — remote party)
     *   - content: string (message body)
     *   - channel: string (sms|rcs)
     *   - gateway_message_id: string
     *   - rcs_payload: ?array (rich card data if RCS)
     *   - keyword: ?string (extracted keyword for shortcode messages)
     */
    public function processInbound(array $payload): array
    {
        $toNumber = $payload['to'];
        $fromNumber = $payload['from'];
        $content = $payload['content'] ?? '';
        $channel = $payload['channel'] ?? 'sms';
        $gatewayMessageId = $payload['gateway_message_id'] ?? null;

        // 1. Identify the purchased number
        $purchasedNumber = PurchasedNumber::withoutGlobalScope('tenant')
            ->where('number', $toNumber)
            ->where('status', 'active')
            ->first();

        if (!$purchasedNumber) {
            Log::warning('Inbound: No active purchased number found', ['to' => $toNumber]);
            return ['routed' => false, 'reason' => 'number_not_found'];
        }

        $accountId = $purchasedNumber->account_id;
        $config = $purchasedNumber->configuration ?? [];
        $results = [];

        // 2. Check keyword against auto-reply rules
        $keyword = $payload['keyword'] ?? $this->extractKeyword($content);
        $autoReplyResult = $this->processAutoReply($purchasedNumber, $keyword, $fromNumber, $accountId);
        $results['auto_reply'] = $autoReplyResult;

        // If keyword is an opt-out keyword, skip inbox (opt-out takes precedence)
        if ($autoReplyResult['is_opt_out'] ?? false) {
            Log::info('Inbound: Opt-out keyword detected, skipping inbox', [
                'keyword' => $keyword, 'from' => $fromNumber,
            ]);
            // Still forward to webhook if configured
            $this->forwardToWebhooks($purchasedNumber, $payload, $accountId);
            return ['routed' => true, 'results' => $results, 'opt_out' => true];
        }

        // 3. Route to inbox if enabled
        $inboxEnabled = $config['inbox_enabled'] ?? true; // Default on
        if ($inboxEnabled) {
            $inboxResult = $this->routeToInbox(
                $accountId, $purchasedNumber, $fromNumber, $content, $channel,
                $gatewayMessageId, $payload['rcs_payload'] ?? null
            );
            $results['inbox'] = $inboxResult;
        }

        // 4. Forward to webhooks (runs in parallel with inbox)
        $this->forwardToWebhooks($purchasedNumber, $payload, $accountId);

        return ['routed' => true, 'results' => $results];
    }

    /**
     * Route inbound message to the inbox.
     */
    private function routeToInbox(
        string $accountId,
        PurchasedNumber $number,
        string $fromNumber,
        string $content,
        string $channel,
        ?string $gatewayMessageId,
        ?array $rcsPayload
    ): array {
        $sourceType = match ($number->number_type) {
            'vmn' => 'vmn',
            'shared_shortcode', 'dedicated_shortcode' => 'shortcode',
            default => 'vmn',
        };

        // Find or create conversation
        $conversation = $this->inboxService->findOrCreateConversation(
            $accountId,
            $fromNumber,
            $channel,
            $number->number,
            $sourceType,
            $number->id,
            null, // rcs_agent_id
            null  // sender_id
        );

        // Add message
        $message = $this->inboxService->addMessage(
            $conversation,
            'inbound',
            $content,
            $fromNumber,
            $number->number,
            [
                'gateway_message_id' => $gatewayMessageId,
                'rcs_payload' => $rcsPayload,
            ]
        );

        // Create notification
        $this->createInboundNotification($accountId, $conversation, $content);

        return ['conversation_id' => $conversation->id, 'message_id' => $message->id];
    }

    /**
     * Check for auto-reply rules and send reply if matched.
     */
    private function processAutoReply(
        PurchasedNumber $number,
        ?string $keyword,
        string $fromNumber,
        string $accountId
    ): array {
        if (!$keyword) {
            return ['matched' => false, 'is_opt_out' => false];
        }

        $keywordUpper = strtoupper(trim($keyword));

        // Check if this is an opt-out keyword (STOP, UNSUBSCRIBE, etc.)
        $optOutKeywords = ['STOP', 'UNSUBSCRIBE', 'OPTOUT', 'OPT-OUT', 'QUIT', 'CANCEL', 'END'];
        $isOptOut = in_array($keywordUpper, $optOutKeywords);

        // Find matching auto-reply rule
        $rule = NumberAutoReplyRule::withoutGlobalScope('tenant')
            ->where('purchased_number_id', $number->id)
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->get()
            ->first(function ($rule) use ($keywordUpper) {
                $ruleKeyword = strtoupper(trim($rule->keyword));
                return match ($rule->match_type) {
                    'exact' => $ruleKeyword === $keywordUpper,
                    'starts_with' => str_starts_with($keywordUpper, $ruleKeyword),
                    'contains' => str_contains($keywordUpper, $ruleKeyword),
                    default => $ruleKeyword === $keywordUpper,
                };
            });

        if (!$rule && !$isOptOut) {
            return ['matched' => false, 'is_opt_out' => false];
        }

        // If a rule is configured as opt-out type, override our detection
        if ($rule && $isOptOut) {
            return ['matched' => true, 'is_opt_out' => true, 'rule_id' => $rule->id];
        }

        if ($rule) {
            // Send auto-reply (fire-and-forget, billing handled by delivery service)
            Log::info('Inbound: Auto-reply triggered', [
                'rule_id' => $rule->id, 'keyword' => $keyword,
            ]);

            // TODO: Send auto-reply via InboxDeliveryService when gateway is ready
        }

        return [
            'matched' => (bool) $rule,
            'is_opt_out' => $isOptOut,
            'rule_id' => $rule?->id,
        ];
    }

    /**
     * Forward inbound message to configured webhooks.
     */
    private function forwardToWebhooks(PurchasedNumber $number, array $payload, string $accountId): void
    {
        $config = $number->configuration ?? [];

        // Number-level forwarding
        $forwardingUrl = $config['forwarding_url'] ?? null;
        if ($forwardingUrl) {
            $this->postToWebhook($forwardingUrl, $payload, 'number');
        }

        // API connection-level forwarding
        $connections = ApiConnection::withoutGlobalScope('tenant')
            ->where('account_id', $accountId)
            ->where('status', 'active')
            ->whereNotNull('webhook_inbound_url')
            ->get();

        foreach ($connections as $conn) {
            $this->postToWebhook($conn->webhook_inbound_url, $payload, 'api_connection');
        }
    }

    /**
     * POST payload to a webhook URL (non-blocking).
     * Validates URL is not internal/private to prevent SSRF.
     */
    private function postToWebhook(string $url, array $payload, string $source): void
    {
        // Basic SSRF protection: reject non-HTTPS and private/internal URLs
        $parsed = parse_url($url);
        if (!$parsed || !in_array($parsed['scheme'] ?? '', ['https', 'http'])) {
            Log::warning("Inbound: Webhook URL rejected (invalid scheme)", ['url' => $url]);
            return;
        }
        $host = $parsed['host'] ?? '';
        if (preg_match('/^(localhost|127\.|10\.|192\.168\.|172\.(1[6-9]|2\d|3[01])\.|0\.0\.0\.0|::1|\[::1\])/i', $host)) {
            Log::warning("Inbound: Webhook URL rejected (private/internal)", ['url' => $url]);
            return;
        }

        try {
            Http::timeout(5)->withOptions(['allow_redirects' => false])->post($url, [
                'event' => 'inbound_message',
                'source' => $source,
                'data' => [
                    'from' => $payload['from'],
                    'to' => $payload['to'],
                    'content' => $payload['content'] ?? '',
                    'channel' => $payload['channel'] ?? 'sms',
                    'message_id' => $payload['gateway_message_id'] ?? null,
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning("Inbound: Webhook delivery failed to {$url}", [
                'error' => $e->getMessage(),
                'source' => $source,
            ]);
        }
    }

    /**
     * Extract keyword from message content (first word).
     */
    private function extractKeyword(string $content): ?string
    {
        $words = preg_split('/\s+/', trim($content));
        return !empty($words[0]) ? $words[0] : null;
    }

    /**
     * Create a notification for an inbound message.
     */
    private function createInboundNotification(
        string $accountId,
        InboxConversation $conversation,
        string $content
    ): void {
        if (!class_exists(Notification::class)) {
            return;
        }

        try {
            Notification::create([
                'tenant_id' => $accountId,
                'type' => 'inbox_message',
                'severity' => 'info',
                'title' => 'New inbound message',
                'body' => 'Message from ' . $conversation->phone_number . ': '
                    . mb_substr($content, 0, 80),
                'deep_link' => '/messages/inbox?conversation=' . $conversation->id,
                'meta' => [
                    'conversation_id' => $conversation->id,
                    'phone_number' => $conversation->phone_number,
                    'channel' => $conversation->channel,
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('Inbound: Failed to create notification', ['error' => $e->getMessage()]);
        }
    }
}
