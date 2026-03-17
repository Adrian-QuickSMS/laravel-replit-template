<?php

namespace App\Events\Alerting;

class RateLimitHit extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $endpoint,
        protected int $limitPerMinute,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'warning', array_merge($metadata, [
            'endpoint' => $endpoint,
            'limit_per_minute' => $limitPerMinute,
        ]));
    }

    public function getTriggerKey(): string { return 'rate_limit_hit'; }
    public function getCategory(): string { return 'system'; }
    public function getTitle(): string { return 'Rate limit reached'; }
    public function getBody(): string { return sprintf('Rate limit of %d/min hit on %s.', $this->limitPerMinute, $this->endpoint); }
}
