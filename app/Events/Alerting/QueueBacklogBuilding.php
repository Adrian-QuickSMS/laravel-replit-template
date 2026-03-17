<?php

namespace App\Events\Alerting;

class QueueBacklogBuilding extends BaseAlertEvent
{
    public function __construct(
        protected string $queueName,
        protected int $backlogSize,
        protected ?float $processingRate = null,
        array $metadata = [],
    ) {
        parent::__construct(null, $backlogSize > 50000 ? 'critical' : 'warning', array_merge($metadata, [
            'queue_name' => $queueName,
            'backlog_size' => $backlogSize,
            'processing_rate' => $processingRate,
        ]));
    }

    public function getTriggerKey(): string { return 'queue_backlog'; }
    public function getTriggerValue(): float { return (float) $this->backlogSize; }
    public function getCategory(): string { return 'platform_health'; }
    public function getTitle(): string { return sprintf('Queue backlog: %s (%d jobs)', $this->queueName, $this->backlogSize); }
    public function getBody(): string { return sprintf('Queue "%s" has %d pending jobs.', $this->queueName, $this->backlogSize); }
    public function isAdminAlert(): bool { return true; }
}
