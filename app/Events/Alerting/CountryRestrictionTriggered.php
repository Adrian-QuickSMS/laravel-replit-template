<?php

namespace App\Events\Alerting;

class CountryRestrictionTriggered extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $country,
        protected string $reason,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'warning', array_merge($metadata, [
            'country' => $country,
            'reason' => $reason,
        ]));
    }

    public function getTriggerKey(): string { return 'country_restriction'; }
    public function getCategory(): string { return 'compliance'; }
    public function getTitle(): string { return sprintf('Country restriction: %s', $this->country); }
    public function getBody(): string { return sprintf('Message blocked due to country restriction for %s. Reason: %s', $this->country, $this->reason); }
}
