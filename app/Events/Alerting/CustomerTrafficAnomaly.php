<?php

namespace App\Events\Alerting;

class CustomerTrafficAnomaly extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $accountNumber,
        protected string $anomalyType, // sudden_spike, sudden_drop, unusual_destination
        protected string $description,
        protected float $currentValue,
        protected float $normalValue,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'warning', array_merge($metadata, [
            'account_number' => $accountNumber,
            'anomaly_type' => $anomalyType,
            'current_value' => $currentValue,
            'normal_value' => $normalValue,
        ]));
    }

    public function getTriggerKey(): string { return 'customer_traffic_anomaly'; }
    public function getTriggerValue(): float { return $this->currentValue; }
    public function getCategory(): string { return 'customer_risk'; }
    public function getTitle(): string { return sprintf('Traffic anomaly: %s (%s)', $this->accountNumber, $this->anomalyType); }
    public function getBody(): string { return sprintf('Account %s: %s. Current: %.0f, Normal: %.0f.', $this->accountNumber, $this->description, $this->currentValue, $this->normalValue); }
    public function isAdminAlert(): bool { return true; }
}
