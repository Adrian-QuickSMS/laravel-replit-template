<?php

namespace App\Jobs\Alerting;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhookAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 15;

    public function __construct(
        public readonly array $payload,
        public readonly array $webhookConfig,
    ) {
        $this->onQueue(config('alerting.queue.dispatch', 'alerts'));
    }

    public function handle(): void
    {
        $url = $this->webhookConfig['webhook_url'] ?? null;

        if (!$url) {
            Log::debug('[SendWebhookAlert] No webhook URL configured');
            return;
        }

        $webhookPayload = [
            'event' => $this->payload['trigger_key'],
            'trigger_key' => $this->payload['trigger_key'],
            'value' => $this->payload['trigger_value'] ?? null,
            'severity' => $this->payload['severity'] ?? 'info',
            'category' => $this->payload['category'] ?? null,
            'title' => $this->payload['title'],
            'body' => $this->payload['body'] ?? null,
            'metadata' => $this->payload['metadata'] ?? [],
            'timestamp' => $this->payload['timestamp'] ?? now()->toIso8601String(),
        ];

        $jsonPayload = json_encode($webhookPayload);

        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => 'QuickSMS-Alerting/1.0',
        ];

        // HMAC-SHA256 signature
        $hmacSecret = $this->webhookConfig['hmac_secret'] ?? null;
        if ($hmacSecret) {
            $signature = hash_hmac(
                config('alerting.webhook.signature_algorithm', 'sha256'),
                $jsonPayload,
                $hmacSecret
            );
            $headers[config('alerting.webhook.signature_header', 'X-QuickSMS-Signature')] = $signature;
        }

        $timeout = config('alerting.webhook.timeout_seconds', 10);

        $response = Http::withHeaders($headers)
            ->timeout($timeout)
            ->withBody($jsonPayload, 'application/json')
            ->post($url);

        if (!$response->successful()) {
            Log::warning('[SendWebhookAlert] Webhook delivery failed', [
                'url' => $url,
                'status' => $response->status(),
                'trigger_key' => $this->payload['trigger_key'],
            ]);

            // Throw to trigger retry
            throw new \RuntimeException(
                sprintf('Webhook to %s returned HTTP %d', $url, $response->status())
            );
        }

        Log::debug('[SendWebhookAlert] Webhook delivered', [
            'url' => $url,
            'trigger_key' => $this->payload['trigger_key'],
            'status' => $response->status(),
        ]);
    }

    public function backoff(): array
    {
        return config('alerting.webhook.retry_backoff', [5, 30, 120]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[SendWebhookAlert] Job failed permanently', [
            'url' => $this->webhookConfig['webhook_url'] ?? 'unknown',
            'trigger_key' => $this->payload['trigger_key'] ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);
    }
}
