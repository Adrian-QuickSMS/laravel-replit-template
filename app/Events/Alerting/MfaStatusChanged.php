<?php

namespace App\Events\Alerting;

class MfaStatusChanged extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $userId,
        protected bool $enabled,
        protected string $method, // totp, sms
        array $metadata = [],
    ) {
        parent::__construct($tenantId, $enabled ? 'info' : 'warning', array_merge($metadata, [
            'user_id' => $userId,
            'enabled' => $enabled,
            'method' => $method,
        ]));
    }

    public function getTriggerKey(): string { return 'mfa_status_changed'; }
    public function getCategory(): string { return 'security'; }
    public function getTitle(): string { return $this->enabled ? 'Two-factor authentication enabled' : 'Two-factor authentication disabled'; }
    public function getBody(): string { return sprintf('Two-factor authentication (%s) was %s.', $this->method, $this->enabled ? 'enabled' : 'disabled'); }
}
