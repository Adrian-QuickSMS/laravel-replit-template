<?php

namespace App\Jobs\Alerting;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Validation\WebhookUrlValidator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSlackAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 10;

    public function __construct(
        public readonly array $payload,
        public readonly array $channelConfig,
    ) {
        $this->onQueue(config('alerting.queue.dispatch', 'alerts'));
    }

    public function handle(): void
    {
        $url = $this->channelConfig['slack_webhook_url']
            ?? $this->channelConfig['webhook_url']
            ?? null;

        if (!$url) {
            Log::debug('[SendSlackAlert] No Slack webhook URL');
            return;
        }

        // SSRF protection
        $validation = WebhookUrlValidator::validate($url);
        if (!$validation['valid']) {
            Log::error('[SendSlackAlert] URL blocked by SSRF protection', [
                'error' => $validation['error'],
                'trigger_key' => $this->payload['trigger_key'],
            ]);
            return;
        }

        $severity = $this->payload['severity'] ?? 'info';
        $emoji = match ($severity) {
            'critical' => ':rotating_light:',
            'warning' => ':warning:',
            default => ':information_source:',
        };

        $color = match ($severity) {
            'critical' => '#dc3545',
            'warning' => '#ffc107',
            default => '#0078D7',
        };

        $blocks = [
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "{$emoji} *{$this->payload['title']}*",
                ],
            ],
        ];

        if (!empty($this->payload['body'])) {
            $blocks[] = [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => $this->payload['body'],
                ],
            ];
        }

        $fields = [];
        $fields[] = ['type' => 'mrkdwn', 'text' => "*Category:*\n" . ucfirst($this->payload['category'] ?? 'General')];
        $fields[] = ['type' => 'mrkdwn', 'text' => "*Severity:*\n" . ucfirst($severity)];

        if (!empty($this->payload['trigger_value'])) {
            $fields[] = ['type' => 'mrkdwn', 'text' => "*Value:*\n" . $this->payload['trigger_value']];
        }

        $blocks[] = [
            'type' => 'section',
            'fields' => $fields,
        ];

        $blocks[] = [
            'type' => 'context',
            'elements' => [
                ['type' => 'mrkdwn', 'text' => $this->payload['timestamp'] ?? now()->toIso8601String()],
            ],
        ];

        $response = Http::timeout(5)->post($url, [
            'blocks' => $blocks,
            'attachments' => [['color' => $color, 'blocks' => []]],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException("Slack webhook returned HTTP {$response->status()}");
        }

        Log::debug('[SendSlackAlert] Slack notification sent', [
            'trigger_key' => $this->payload['trigger_key'],
        ]);
    }

    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[SendSlackAlert] Job failed', [
            'trigger_key' => $this->payload['trigger_key'] ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);
    }
}
