<?php

namespace App\Events\Alerting;

class MessageBlockedByRegulation extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $regulation,
        protected string $reason,
        protected ?string $destination = null,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'warning', array_merge($metadata, [
            'regulation' => $regulation,
            'reason' => $reason,
            'destination' => $destination,
        ]));
    }

    public function getTriggerKey(): string { return 'message_blocked_regulation'; }
    public function getCategory(): string { return 'compliance'; }
    public function getTitle(): string { return 'Message blocked by regulation'; }
    public function getBody(): string { return sprintf('Message blocked due to %s regulation. %s', $this->regulation, $this->reason); }
}
