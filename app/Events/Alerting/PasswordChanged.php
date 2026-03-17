<?php

namespace App\Events\Alerting;

class PasswordChanged extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $userId,
        protected string $ipAddress,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'info', array_merge($metadata, [
            'user_id' => $userId,
            'ip_address' => $ipAddress,
        ]));
    }

    public function getTriggerKey(): string { return 'password_changed'; }
    public function getCategory(): string { return 'security'; }
    public function getTitle(): string { return 'Password changed'; }
    public function getBody(): string { return sprintf('Your password was changed from IP %s.', $this->ipAddress); }
}
