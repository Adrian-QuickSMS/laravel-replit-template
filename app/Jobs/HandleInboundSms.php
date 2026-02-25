<?php

namespace App\Jobs;

use App\Models\NumberAutoReplyRule;
use App\Models\PurchasedNumber;
use App\Services\Numbers\NumberService;
use App\Services\OptOutService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * HandleInboundSms — processes an inbound SMS received on a purchased number.
 *
 * Gateway webhook controllers dispatch this job when an inbound message arrives.
 *
 * Responsibilities:
 * 1. Update last_used_at on the purchased number
 * 2. Match auto-reply rules (keyword matching)
 * 3. Forward to configured webhook URL / email
 * 4. Log the inbound message
 *
 * Queue: 'inbound' (dedicated queue for inbound processing)
 */
class HandleInboundSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 30;

    public function __construct(
        /** E.164 number that received the message (our VMN/shortcode) */
        public readonly string $destinationNumber,
        /** E.164 number that sent the message (the end user) */
        public readonly string $senderNumber,
        /** Message body text */
        public readonly string $messageBody,
        /** Raw payload from gateway */
        public readonly array $rawPayload = [],
    ) {
        $this->onQueue('inbound');
    }

    public function handle(): void
    {
        Log::info('[HandleInboundSms] Processing inbound message', [
            'destination' => $this->destinationNumber,
            'sender' => $this->senderNumber,
            'body_length' => strlen($this->messageBody),
        ]);

        // Step 1: Check for campaign opt-out keyword match FIRST (highest priority)
        $optOutService = app(OptOutService::class);
        $wasOptOut = $optOutService->processInboundOptOut(
            $this->destinationNumber,
            $this->senderNumber,
            $this->messageBody
        );

        if ($wasOptOut) {
            Log::info('[HandleInboundSms] Opt-out processed, skipping further processing', [
                'destination' => $this->destinationNumber,
                'sender' => $this->senderNumber,
            ]);
            // Still update last_used_at
            NumberService::touchLastUsedByNumber($this->destinationNumber);
            return;
        }

        // Step 2: Update last_used_at on the purchased number
        NumberService::touchLastUsedByNumber($this->destinationNumber);

        // Step 3: Find the purchased number record
        $number = PurchasedNumber::withoutGlobalScopes()
            ->where('number', $this->destinationNumber)
            ->where('status', PurchasedNumber::STATUS_ACTIVE)
            ->first();

        if (!$number) {
            Log::warning('[HandleInboundSms] No active purchased number found', [
                'destination' => $this->destinationNumber,
            ]);
            return;
        }

        // Step 4: Check auto-reply rules
        $matchedRule = NumberAutoReplyRule::withoutGlobalScopes()
            ->where('purchased_number_id', $number->id)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get()
            ->first(function ($rule) {
                return $this->matchesRule($rule);
            });

        if ($matchedRule) {
            Log::info('[HandleInboundSms] Auto-reply rule matched', [
                'rule_id' => $matchedRule->id,
                'keyword' => $matchedRule->keyword,
            ]);
            // TODO: Dispatch auto-reply via DeliveryService
            // This would create a one-off outbound message using the number as sender
            // and bill it if charge_for_reply is true
        }

        // Step 5: Forward to webhook URL if configured
        if ($number->getForwardingUrl()) {
            $this->forwardToWebhook($number);
        }

        // Step 6: Forward to email if configured
        if ($number->getForwardingEmail()) {
            $this->forwardToEmail($number);
        }
    }

    /**
     * Check if the inbound message matches a rule.
     */
    private function matchesRule(NumberAutoReplyRule $rule): bool
    {
        $normalizedText = strtoupper(trim($this->messageBody));
        $keyword = strtoupper($rule->keyword);

        if ($keyword === '*') {
            return true; // Catch-all
        }

        return match ($rule->match_type) {
            'exact' => $normalizedText === $keyword,
            'starts_with' => str_starts_with($normalizedText, $keyword),
            'contains' => str_contains($normalizedText, $keyword),
            default => false,
        };
    }

    /**
     * Forward the inbound message to the configured webhook URL.
     */
    private function forwardToWebhook(PurchasedNumber $number): void
    {
        $url = $number->getForwardingUrl();
        $authHeaders = $number->getForwardingAuthHeaders();
        $retryPolicy = $number->getRetryPolicy();

        $payload = [
            'destination_number' => $this->destinationNumber,
            'sender_number' => $this->senderNumber,
            'message_body' => $this->messageBody,
            'received_at' => now()->toIso8601String(),
            'number_id' => $number->id,
        ];

        $headers = ['Content-Type' => 'application/json'];
        if ($authHeaders) {
            foreach ($authHeaders as $header) {
                $headers[$header['key']] = $header['value'];
            }
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
                ->timeout(10)
                ->retry(
                    $retryPolicy['max_retries'] ?? 3,
                    ($retryPolicy['retry_delay_seconds'] ?? 5) * 1000,
                )
                ->post($url, $payload);

            if ($response->failed()) {
                Log::warning('[HandleInboundSms] Webhook forwarding failed', [
                    'url' => $url,
                    'status' => $response->status(),
                    'number_id' => $number->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('[HandleInboundSms] Webhook forwarding error', [
                'url' => $url,
                'error' => $e->getMessage(),
                'number_id' => $number->id,
            ]);
        }
    }

    /**
     * Forward the inbound message to the configured email address.
     */
    private function forwardToEmail(PurchasedNumber $number): void
    {
        // TODO: Implement email forwarding via Mail facade
        // For now, log the intent
        Log::info('[HandleInboundSms] Email forwarding pending implementation', [
            'email' => $number->getForwardingEmail(),
            'number_id' => $number->id,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[HandleInboundSms] Job failed', [
            'destination' => $this->destinationNumber,
            'sender' => $this->senderNumber,
            'error' => $exception->getMessage(),
        ]);
    }

    public function backoff(): array
    {
        return [5, 15, 30, 60, 120];
    }
}
