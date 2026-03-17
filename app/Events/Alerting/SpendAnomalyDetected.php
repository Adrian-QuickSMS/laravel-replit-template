<?php

namespace App\Events\Alerting;

class SpendAnomalyDetected extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected float $currentSpendRate,
        protected float $normalSpendRate,
        protected float $percentageIncrease,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'warning', array_merge($metadata, [
            'current_spend_rate' => $currentSpendRate,
            'normal_spend_rate' => $normalSpendRate,
            'percentage_increase' => $percentageIncrease,
        ]));
    }

    public function getTriggerKey(): string { return 'spend_rate'; }
    public function getTriggerValue(): float { return $this->percentageIncrease; }
    public function getCategory(): string { return 'billing'; }
    public function getTitle(): string { return 'Unusual spend spike detected'; }

    public function getBody(): string
    {
        return sprintf(
            'Your spend rate has increased by %.1f%% compared to your usual pattern. Current: %.2f/hr, Normal: %.2f/hr.',
            $this->percentageIncrease,
            $this->currentSpendRate,
            $this->normalSpendRate
        );
    }
}
