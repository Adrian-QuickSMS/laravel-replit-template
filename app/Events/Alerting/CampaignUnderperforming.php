<?php

namespace App\Events\Alerting;

class CampaignUnderperforming extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $campaignId,
        protected string $campaignName,
        protected float $deliveryRate,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'warning', array_merge($metadata, [
            'campaign_id' => $campaignId,
            'campaign_name' => $campaignName,
            'delivery_rate' => $deliveryRate,
        ]));
    }

    public function getTriggerKey(): string { return 'campaign_delivery_rate'; }
    public function getTriggerValue(): float { return $this->deliveryRate; }
    public function getCategory(): string { return 'campaign'; }
    public function getTitle(): string { return sprintf('Campaign "%s" underperforming', $this->campaignName); }
    public function getBody(): string { return sprintf('Campaign "%s" delivery rate is %.1f%%.', $this->campaignName, $this->deliveryRate); }
}
