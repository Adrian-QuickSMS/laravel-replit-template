<?php

namespace App\Events\Alerting;

class CampaignCompleted extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $campaignId,
        protected string $campaignName,
        protected int $totalSent,
        protected int $totalDelivered,
        protected int $totalFailed,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'info', array_merge($metadata, [
            'campaign_id' => $campaignId,
            'campaign_name' => $campaignName,
            'total_sent' => $totalSent,
            'total_delivered' => $totalDelivered,
            'total_failed' => $totalFailed,
        ]));
    }

    public function getTriggerKey(): string { return 'campaign_completed'; }
    public function getCategory(): string { return 'campaign'; }
    public function getTitle(): string { return sprintf('Campaign "%s" completed', $this->campaignName); }

    public function getBody(): string
    {
        $rate = $this->totalSent > 0 ? ($this->totalDelivered / $this->totalSent) * 100 : 0;
        return sprintf(
            'Campaign "%s" completed. Sent: %d, Delivered: %d (%.1f%%), Failed: %d.',
            $this->campaignName, $this->totalSent, $this->totalDelivered, $rate, $this->totalFailed
        );
    }
}
