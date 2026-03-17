<?php

namespace App\Events\Alerting;

class RcsAgentStatusChanged extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $agentName,
        protected string $oldStatus,
        protected string $newStatus,
        array $metadata = [],
    ) {
        $severity = in_array($newStatus, ['rejected', 'suspended']) ? 'warning' : 'info';
        parent::__construct($tenantId, $severity, array_merge($metadata, [
            'agent_name' => $agentName,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]));
    }

    public function getTriggerKey(): string { return 'rcs_agent_status_changed'; }
    public function getCategory(): string { return 'compliance'; }
    public function getTitle(): string { return sprintf('RCS agent "%s": %s', $this->agentName, $this->newStatus); }

    public function getBody(): string
    {
        return sprintf('RCS agent "%s" status changed from %s to %s.', $this->agentName, $this->oldStatus, $this->newStatus);
    }
}
