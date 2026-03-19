<?php

namespace App\Events\Alerting;

class IpAllowlistChanged extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $action,
        protected ?string $ipAddress = null,
        protected ?string $label = null,
        protected ?int $entriesRemaining = null,
        protected ?string $changedBy = null,
        string $severity = 'warning',
        array $metadata = [],
    ) {
        // Removing an IP while allowlist is enabled is critical
        if ($action === 'removed') {
            $severity = 'critical';
        }

        parent::__construct($tenantId, $severity, array_merge($metadata, [
            'action' => $action,
            'ip_address' => $ipAddress,
            'label' => $label,
            'entries_remaining' => $entriesRemaining,
            'changed_by' => $changedBy,
        ]));
    }

    public function getTriggerKey(): string
    {
        return 'ip_allowlist_changed';
    }

    public function getCategory(): string
    {
        return 'security';
    }

    public function getTitle(): string
    {
        return match ($this->action) {
            'enabled' => 'IP allowlist enabled',
            'disabled' => 'IP allowlist disabled',
            'added' => sprintf('IP address added to allowlist%s', $this->ipAddress ? ": {$this->ipAddress}" : ''),
            'removed' => sprintf('IP address removed from allowlist%s', $this->ipAddress ? ": {$this->ipAddress}" : ''),
            default => 'IP allowlist changed',
        };
    }

    public function getBody(): string
    {
        $body = match ($this->action) {
            'enabled' => 'IP allowlist has been enabled. Only allowlisted IP addresses can now access the portal and API.',
            'disabled' => 'IP allowlist has been disabled. All IP addresses can now access the portal and API.',
            'added' => sprintf('IP address %s has been added to the allowlist.%s', $this->ipAddress ?? 'unknown', $this->label ? " Label: {$this->label}" : ''),
            'removed' => sprintf('IP address %s has been removed from the allowlist.%s', $this->ipAddress ?? 'unknown', $this->entriesRemaining !== null ? " {$this->entriesRemaining} entries remaining." : ''),
            default => 'IP allowlist configuration has been changed.',
        };

        if ($this->changedBy) {
            $body .= sprintf(' Changed by: %s.', $this->changedBy);
        }

        return $body;
    }
}
