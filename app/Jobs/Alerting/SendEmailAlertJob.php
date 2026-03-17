<?php

namespace App\Jobs\Alerting;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailAlertJob implements ShouldQueue
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
            Log::debug('[SendEmailAlert] No recipients, skipping');
            return;
        }

        $severity = $this->payload['severity'] ?? 'info';
        $template = "emails.alerts.{$severity}";

        // Fallback to generic template if severity-specific doesn't exist
        if (!view()->exists($template)) {
            $template = 'emails.alerts.generic';
        }

        foreach ($this->recipients as $recipient) {
            try {
                Mail::send($template, [
                    'title' => $this->payload['title'],
                    'body' => $this->payload['body'],
                    'severity' => $severity,
                    'category' => $this->payload['category'] ?? 'General',
                    'triggerKey' => $this->payload['trigger_key'],
                    'triggerValue' => $this->payload['trigger_value'] ?? null,
                    'metadata' => $this->payload['metadata'] ?? [],
                    'timestamp' => $this->payload['timestamp'] ?? now()->toIso8601String(),
                ], function ($message) use ($recipient, $severity) {
                    $subject = $this->buildSubject($severity);
                    $message->to($recipient)
                        ->subject($subject);
                });

                Log::debug('[SendEmailAlert] Email sent', [
                    'recipient' => $recipient,
                    'trigger_key' => $this->payload['trigger_key'],
                ]);
            } catch (\Throwable $e) {
                Log::error('[SendEmailAlert] Failed to send email', [
                    'recipient' => $recipient,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function buildSubject(string $severity): string
    {
        $prefix = match ($severity) {
            'critical' => '[CRITICAL]',
            'warning' => '[WARNING]',
            default => '[INFO]',
        };

        return sprintf('%s %s — QuickSMS', $prefix, $this->payload['title']);
    }

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[SendEmailAlert] Job failed', [
            'trigger_key' => $this->payload['trigger_key'] ?? 'unknown',
            'recipients' => $this->recipients,
            'error' => $exception->getMessage(),
        ]);
    }
}
