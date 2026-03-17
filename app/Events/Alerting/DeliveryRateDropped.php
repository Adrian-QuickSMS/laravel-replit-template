<?php

namespace App\Events\Alerting;

class DeliveryRateDropped extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected float $deliveryRate,
        protected ?string $campaignId = null,
        protected ?string $network = null,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, $deliveryRate < 50 ? 'critical' : 'warning', array_merge($metadata, [
            'delivery_rate' => $deliveryRate,
            'campaign_id' => $campaignId,
            'network' => $network,
        ]));
    }

    public function getTriggerKey(): string { return 'delivery_rate'; }
    public function getTriggerValue(): float { return $this->deliveryRate; }
    public function getCategory(): string { return 'messaging'; }
    public function getTitle(): string { return 'Delivery rate below threshold'; }

    public function getBody(): string
    {
        $msg = sprintf('Delivery rate has dropped to %.1f%%.', $this->deliveryRate);
        if ($this->network) {
            $msg .= sprintf(' Network: %s.', $this->network);
        }
        if ($this->campaignId) {
            $msg .= sprintf(' Campaign: %s.', $this->campaignId);
        }
        return $msg;
    }
}
