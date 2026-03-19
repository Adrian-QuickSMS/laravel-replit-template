<?php

namespace App\Jobs\Alerting;

use App\Models\Alerting\NotificationBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Dispatches batched notifications that are ready to send.
 *
 * Runs on a schedule (every 5 minutes) and processes any batches
 * whose scheduled_for time has passed.
 */
class DispatchBatchedAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct()
    {
        $this->onQueue(config('alerting.queue.batch', 'alerts-batch'));
    }

    public function handle(): void
    {
        // Use lockForUpdate to prevent concurrent workers from processing same batches
        $batches = NotificationBatch::ready()
            ->orderBy('scheduled_for')
            ->limit(100)
            ->lockForUpdate()
            ->get();

        if ($batches->isEmpty()) {
            return;
        }

        Log::info('[DispatchBatchedAlerts] Processing batches', [
            'count' => $batches->count(),
        ]);

        foreach ($batches as $batch) {
            try {
                $this->processBatch($batch);
                $batch->markDispatched();
            } catch (\Throwable $e) {
                Log::error('[DispatchBatchedAlerts] Failed to process batch', [
                    'batch_id' => $batch->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function processBatch(NotificationBatch $batch): void
    {
        $items = $batch->items ?? [];
        if (empty($items)) {
            return;
        }

        $channel = $batch->channel;
        $itemCount = count($items);

        // Build a summary notification from batch items
        $summaryPayload = [
            'trigger_key' => 'batch_summary',
            'title' => sprintf('%d alerts in the last %s', $itemCount, $this->batchTypeLabel($batch->batch_type)),
            'body' => $this->buildBatchSummaryBody($items),
            'severity' => $this->getHighestSeverity($items),
            'category' => 'batch',
            'tenant_id' => $batch->tenant_id,
            'is_admin' => false,
            'metadata' => [
                'batch_id' => $batch->id,
                'batch_type' => $batch->batch_type,
                'item_count' => $itemCount,
                'items' => array_map(fn ($item) => [
                    'title' => $item['title'] ?? 'Alert',
                    'severity' => $item['severity'] ?? 'info',
                    'trigger_key' => $item['trigger_key'] ?? null,
                ], $items),
            ],
            'timestamp' => now()->toIso8601String(),
        ];

        // Dispatch through the appropriate channel
        match ($channel) {
            'in_app' => CreateInAppNotificationJob::dispatch($summaryPayload),
            'email' => SendEmailAlertJob::dispatch($summaryPayload, $this->resolveRecipients($batch)),
            'webhook' => SendWebhookAlertJob::dispatch($summaryPayload, $this->resolveWebhookConfig($batch)),
            'slack' => SendSlackAlertJob::dispatch($summaryPayload, $this->resolveChannelConfig($batch, 'slack')),
            'teams' => SendTeamsAlertJob::dispatch($summaryPayload, $this->resolveChannelConfig($batch, 'teams')),
            default => Log::warning('[DispatchBatchedAlerts] Unknown channel', ['channel' => $channel]),
        };

        Log::debug('[DispatchBatchedAlerts] Batch dispatched', [
            'batch_id' => $batch->id,
            'channel' => $channel,
            'items' => $itemCount,
        ]);
    }

    private function buildBatchSummaryBody(array $items): string
    {
        $lines = [];
        foreach (array_slice($items, 0, 5) as $item) {
            $severity = strtoupper($item['severity'] ?? 'INFO');
            $lines[] = "[{$severity}] " . ($item['title'] ?? 'Alert');
        }

        if (count($items) > 5) {
            $lines[] = sprintf('... and %d more', count($items) - 5);
        }

        return implode("\n", $lines);
    }

    private function getHighestSeverity(array $items): string
    {
        $severities = array_column($items, 'severity');
        if (in_array('critical', $severities)) return 'critical';
        if (in_array('warning', $severities)) return 'warning';
        return 'info';
    }

    private function batchTypeLabel(string $batchType): string
    {
        return match ($batchType) {
            'batched_15m' => '15 minutes',
            'batched_1h' => 'hour',
            'daily_digest' => 'day',
            default => 'period',
        };
    }

    private function resolveRecipients(NotificationBatch $batch): array
    {
        // Resolve from AlertChannelConfig for the tenant
        if ($batch->tenant_id) {
            $config = \App\Models\Alerting\AlertChannelConfig::forTenant($batch->tenant_id)
                ->forChannel('email')
                ->enabled()
                ->first();

            if ($config && !empty($config->config['email'])) {
                return [$config->config['email']];
            }
        }
        return [];
    }

    private function resolveWebhookConfig(NotificationBatch $batch): array
    {
        if ($batch->tenant_id) {
            $config = \App\Models\Alerting\AlertChannelConfig::forTenant($batch->tenant_id)
                ->forChannel('webhook')
                ->enabled()
                ->first();
            return $config ? ($config->config ?? []) : [];
        }
        return [];
    }

    private function resolveChannelConfig(NotificationBatch $batch, string $channel): array
    {
        if ($batch->tenant_id) {
            $config = \App\Models\Alerting\AlertChannelConfig::forTenant($batch->tenant_id)
                ->forChannel($channel)
                ->enabled()
                ->first();
            return $config ? ($config->config ?? []) : [];
        }
        return [];
    }
}
