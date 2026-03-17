<?php

namespace App\Events\Alerting;

class DlrLatencyIncreased extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected float $latencySeconds,
        protected ?string $network = null,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'warning', array_merge($metadata, [
            'latency_seconds' => $latencySeconds,
            'network' => $network,
        ]));
    }

    public function getTriggerKey(): string { return 'dlr_latency_seconds'; }
    public function getTriggerValue(): float { return $this->latencySeconds; }
    public function getCategory(): string { return 'messaging'; }
    public function getTitle(): string { return 'DLR latency increase detected'; }

    public function getBody(): string
    {
        $msg = sprintf('DLR latency has increased to %.0f seconds.', $this->latencySeconds);
        if ($this->network) {
            $msg .= sprintf(' Affected network: %s.', $this->network);
        }
        return $msg;
    }
}
