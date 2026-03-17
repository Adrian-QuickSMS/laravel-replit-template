<?php

namespace App\Jobs\Alerting;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public readonly array $payload,
        public readonly array $recipients,
    ) {
        $this->onQueue(config('alerting.queue.dispatch', 'alerts'));
    }

    public function handle(): void
    {
        if (empty($this->recipients)) {
            Log::debug('[SendSmsAlert] No recipients, skipping');
            return;
        }

        $severity = $this->payload['severity'] ?? 'info';
        $prefix = match ($severity) {
            'critical' => 'CRITICAL: ',
            'warning' => 'WARNING: ',
            default => '',
        };

        $message = $prefix . ($this->payload['title'] ?? 'QuickSMS Alert');

        // Truncate to SMS-friendly length
        if (strlen($message) > 160) {
            $message = substr($message, 0, 157) . '...';
        }

        // Use the SmsGateway contract if available, otherwise log intent
        $gateway = app()->bound(\App\Contracts\SmsGateway::class)
            ? app(\App\Contracts\SmsGateway::class)
            : null;

        foreach ($this->recipients as $recipient) {
            try {
                if ($gateway) {
                    $gateway->send($recipient, 'QuickSMS', $message);
                } else {
                    Log::info('[SendSmsAlert] SMS gateway not configured, would send', [
                        'to' => $recipient,
                        'message' => $message,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('[SendSmsAlert] Failed to send SMS', [
                    'to' => $recipient,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[SendSmsAlert] Job failed', [
            'trigger_key' => $this->payload['trigger_key'] ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);
    }
}
