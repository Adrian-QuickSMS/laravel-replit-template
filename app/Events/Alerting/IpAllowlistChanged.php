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
            'ip_address' => $ipAddress ? self::maskIp($ipAddress) : null,
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
        $masked = $this->ipAddress ? self::maskIp($this->ipAddress) : null;

        return match ($this->action) {
            'enabled' => 'IP allowlist enabled',
            'disabled' => 'IP allowlist disabled',
            'added' => sprintf('IP address added to allowlist%s', $masked ? ": {$masked}" : ''),
            'removed' => sprintf('IP address removed from allowlist%s', $masked ? ": {$masked}" : ''),
            default => 'IP allowlist changed',
        };
    }

    public function getBody(): string
    {
        $masked = $this->ipAddress ? self::maskIp($this->ipAddress) : 'unknown';

        $body = match ($this->action) {
            'enabled' => 'IP allowlist has been enabled. Only allowlisted IP addresses can now access the portal and API.',
            'disabled' => 'IP allowlist has been disabled. All IP addresses can now access the portal and API.',
            'added' => sprintf('IP address %s has been added to the allowlist.%s', $masked, $this->label ? " Label: {$this->label}" : ''),
            'removed' => sprintf('IP address %s has been removed from the allowlist.%s', $masked, $this->entriesRemaining !== null ? " {$this->entriesRemaining} entries remaining." : ''),
            default => 'IP allowlist configuration has been changed.',
        };

        if ($this->changedBy) {
            $body .= sprintf(' Changed by: %s.', $this->changedBy);
        }

        return $body;
    }

    /**
     * Mask an IP address for safe display in notifications.
     * e.g. 192.168.1.100 → 192.168.*.* , 2001:db8::1 → 2001:db8::***
     */
    private static function maskIp(string $ip): string
    {
        if (str_contains($ip, ':')) {
            // IPv6: mask last 4 groups
            $parts = explode(':', $ip);
            $count = count($parts);
            for ($i = max(0, $count - 4); $i < $count; $i++) {
                $parts[$i] = '***';
            }
            return implode(':', $parts);
        }

        // IPv4: mask last two octets
        $parts = explode('.', $ip);
        if (count($parts) === 4) {
            return "{$parts[0]}.{$parts[1]}.*.*";
        }

        return '***';
    }
}
