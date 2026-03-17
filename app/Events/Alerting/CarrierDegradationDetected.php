<?php

namespace App\Events\Alerting;

class CarrierDegradationDetected extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $carrier,
        protected string $degradationType,
        protected float $impactPercentage,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'warning', array_merge($metadata, [
            'carrier' => $carrier,
            'degradation_type' => $degradationType,
            'impact_percentage' => $impactPercentage,
        ]));
    }

    public function getTriggerKey(): string { return 'carrier_degradation'; }
    public function getTriggerValue(): float { return $this->impactPercentage; }
    public function getCategory(): string { return 'messaging'; }
    public function getTitle(): string { return sprintf('%s network degradation', $this->carrier); }

    public function getBody(): string
    {
        return sprintf(
            '%s network is experiencing %s. Estimated impact: %.1f%% of messages.',
            $this->carrier,
            $this->degradationType,
            $this->impactPercentage
        );
    }
}
