<?php

namespace App\Events\Alerting;

class ApiErrorsThresholdBreached extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected float $errorRate,
        protected int $errorCount,
        protected string $timeWindow,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'warning', array_merge($metadata, [
            'error_rate' => $errorRate,
            'error_count' => $errorCount,
            'time_window' => $timeWindow,
        ]));
    }

    public function getTriggerKey(): string { return 'api_error_rate'; }
    public function getTriggerValue(): float { return $this->errorRate; }
    public function getCategory(): string { return 'system'; }
    public function getTitle(): string { return 'API error rate above threshold'; }
    public function getBody(): string { return sprintf('API error rate is %.1f%% (%d errors in %s).', $this->errorRate, $this->errorCount, $this->timeWindow); }
}
