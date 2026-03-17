<?php

namespace App\Events\Alerting;

class PendingMessagesSpiked extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected int $pendingCount,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'warning', array_merge($metadata, [
            'pending_count' => $pendingCount,
        ]));
    }

    public function getTriggerKey(): string { return 'pending_messages'; }
    public function getTriggerValue(): float { return (float) $this->pendingCount; }
    public function getCategory(): string { return 'messaging'; }
    public function getTitle(): string { return 'Pending messages spike'; }

    public function getBody(): string
    {
        return sprintf('%d messages are currently pending delivery.', $this->pendingCount);
    }
}
