<?php

namespace App\Events\Alerting;

class SuspiciousLoginDetected extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $userId,
        protected string $ipAddress,
        protected ?string $userAgent = null,
        protected ?string $reason = null,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'critical', array_merge($metadata, [
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'reason' => $reason,
        ]));
    }

    public function getTriggerKey(): string { return 'suspicious_login'; }
    public function getCategory(): string { return 'security'; }
    public function getTitle(): string { return 'Suspicious login detected'; }

    public function getBody(): string
    {
        $msg = sprintf('A suspicious login was detected from IP %s.', $this->ipAddress);
        if ($this->reason) {
            $msg .= sprintf(' Reason: %s.', $this->reason);
        }
        return $msg;
    }
}
