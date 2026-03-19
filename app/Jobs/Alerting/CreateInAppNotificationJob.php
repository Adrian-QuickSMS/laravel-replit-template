<?php

namespace App\Jobs\Alerting;

use App\Models\AdminNotification;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateInAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 15;

    public function __construct(
        public readonly array $payload,
    ) {
        $this->onQueue(config('alerting.queue.dispatch', 'alerts'));
    }

    public function handle(): void
    {
        $isAdmin = $this->payload['is_admin'] ?? false;

        if ($isAdmin) {
            $this->createAdminNotification();
        } else {
            $this->createCustomerNotification();
        }
    }

    private function createCustomerNotification(): void
    {
        $tenantId = $this->payload['tenant_id'] ?? null;
        if (!$tenantId) {
            Log::warning('[CreateInAppNotification] No tenant_id for customer notification');
            return;
        }

        Notification::create([
            'tenant_id' => $tenantId,
            'user_id' => $this->payload['user_id'] ?? null,
            'type' => $this->payload['trigger_key'],
            'severity' => $this->payload['severity'] ?? 'info',
            'category' => $this->payload['category'] ?? null,
            'title' => $this->payload['title'],
            'body' => $this->payload['body'] ?? null,
            'deep_link' => $this->payload['metadata']['deep_link'] ?? null,
            'action_url' => $this->payload['metadata']['action_url'] ?? null,
            'action_label' => $this->payload['metadata']['action_label'] ?? null,
            'meta' => $this->payload['metadata'] ?? [],
        ]);

        Log::debug('[CreateInAppNotification] Customer notification created', [
            'tenant_id' => $tenantId,
            'trigger_key' => $this->payload['trigger_key'],
        ]);
    }

    private function createAdminNotification(): void
    {
        AdminNotification::create([
            'type' => $this->payload['trigger_key'],
            'severity' => $this->payload['severity'] ?? 'info',
            'category' => $this->payload['category'] ?? null,
            'title' => $this->payload['title'],
            'body' => $this->payload['body'] ?? null,
            'deep_link' => $this->payload['metadata']['deep_link'] ?? null,
            'action_url' => $this->payload['metadata']['action_url'] ?? null,
            'action_label' => $this->payload['metadata']['action_label'] ?? null,
            'meta' => $this->payload['metadata'] ?? [],
        ]);

        Log::debug('[CreateInAppNotification] Admin notification created', [
            'trigger_key' => $this->payload['trigger_key'],
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[CreateInAppNotification] Job failed', [
            'trigger_key' => $this->payload['trigger_key'] ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);
    }
}
