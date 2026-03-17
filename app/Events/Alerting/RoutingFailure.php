<?php

namespace App\Events\Alerting;

class RoutingFailure extends BaseAlertEvent
{
    public function __construct(
        protected string $routeName,
        protected string $supplierName,
        protected string $failureReason,
        protected int $affectedMessages,
        array $metadata = [],
    ) {
        parent::__construct(null, 'critical', array_merge($metadata, [
            'route_name' => $routeName,
            'supplier_name' => $supplierName,
            'failure_reason' => $failureReason,
            'affected_messages' => $affectedMessages,
        ]));
    }

    public function getTriggerKey(): string { return 'routing_failure'; }
    public function getTriggerValue(): float { return (float) $this->affectedMessages; }
    public function getCategory(): string { return 'platform_health'; }
    public function getTitle(): string { return sprintf('Route failure: %s via %s', $this->routeName, $this->supplierName); }
    public function getBody(): string { return sprintf('Route "%s" (supplier: %s) failed. %s. %d messages affected.', $this->routeName, $this->supplierName, $this->failureReason, $this->affectedMessages); }
    public function isAdminAlert(): bool { return true; }
}
