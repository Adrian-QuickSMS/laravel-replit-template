<?php

namespace App\Jobs\Alerting;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendTeamsAlertJob implements ShouldQueue
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
        $url = $this->channelConfig['teams_webhook_url']
            ?? $this->channelConfig['webhook_url']
            ?? null;

        if (!$url) {
            Log::debug('[SendTeamsAlert] No Teams webhook URL');
            return;
        }

        $severity = $this->payload['severity'] ?? 'info';
        $color = match ($severity) {
            'critical' => 'Attention',
            'warning' => 'Warning',
            default => 'Default',
        };

        $facts = [
            ['title' => 'Category', 'value' => ucfirst($this->payload['category'] ?? 'General')],
            ['title' => 'Severity', 'value' => ucfirst($severity)],
        ];

        if (!empty($this->payload['trigger_value'])) {
            $facts[] = ['title' => 'Value', 'value' => (string) $this->payload['trigger_value']];
        }

        $card = [
            'type' => 'message',
            'attachments' => [[
                'contentType' => 'application/vnd.microsoft.card.adaptive',
                'content' => [
                    '$schema' => 'http://adaptivecards.io/schemas/adaptive-card.json',
                    'type' => 'AdaptiveCard',
                    'version' => '1.4',
                    'body' => [
                        [
                            'type' => 'TextBlock',
                            'text' => $this->payload['title'],
                            'weight' => 'Bolder',
                            'size' => 'Medium',
                            'color' => $color,
                        ],
                        [
                            'type' => 'TextBlock',
                            'text' => $this->payload['body'] ?? '',
                            'wrap' => true,
                        ],
                        [
                            'type' => 'FactSet',
                            'facts' => $facts,
                        ],
                        [
                            'type' => 'TextBlock',
                            'text' => $this->payload['timestamp'] ?? now()->toIso8601String(),
                            'size' => 'Small',
                            'isSubtle' => true,
                        ],
                    ],
                ],
            ]],
        ];

        $response = Http::timeout(5)->post($url, $card);

        if (!$response->successful()) {
            throw new \RuntimeException("Teams webhook returned HTTP {$response->status()}");
        }

        Log::debug('[SendTeamsAlert] Teams notification sent', [
            'trigger_key' => $this->payload['trigger_key'],
        ]);
    }

    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[SendTeamsAlert] Job failed', [
            'trigger_key' => $this->payload['trigger_key'] ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);
    }
}
