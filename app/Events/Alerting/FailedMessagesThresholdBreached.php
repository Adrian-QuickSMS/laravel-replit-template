<?php

namespace App\Events\Alerting;

class FailedMessagesThresholdBreached extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected int $failedCount,
        protected ?string $timeWindow = '1h',
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'warning', array_merge($metadata, [
            'failed_count' => $failedCount,
            'time_window' => $timeWindow,
        ]));
    }

    public function getTriggerKey(): string { return 'failed_messages'; }
    public function getTriggerValue(): float { return (float) $this->failedCount; }
    public function getCategory(): string { return 'messaging'; }
    public function getTitle(): string { return 'Failed messages above threshold'; }

    public function getBody(): string
    {
        return sprintf('%d messages failed in the last %s.', $this->failedCount, $this->timeWindow);
    }
}
