<?php

namespace App\Events\Alerting;

class WebhookDeliveryFailed extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $webhookUrl,
        protected int $statusCode,
        protected int $failureCount,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'warning', array_merge($metadata, [
            'webhook_url' => $webhookUrl,
            'status_code' => $statusCode,
            'failure_count' => $failureCount,
        ]));
    }

    public function getTriggerKey(): string { return 'webhook_delivery_failed'; }
    public function getTriggerValue(): float { return (float) $this->failureCount; }
    public function getCategory(): string { return 'system'; }
    public function getTitle(): string { return 'Webhook delivery failures'; }
    public function getBody(): string { return sprintf('Webhook delivery to %s failed %d times. Last status code: %d.', $this->webhookUrl, $this->failureCount, $this->statusCode); }
}
