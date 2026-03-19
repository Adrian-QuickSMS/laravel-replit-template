<?php

namespace App\Events\Alerting;

class AccountStatusOverridden extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $previousStatus,
        protected string $newStatus,
        protected ?string $reason = null,
        protected ?string $adminName = null,
        string $severity = 'critical',
        array $metadata = [],
    ) {
        parent::__construct($tenantId, $severity, array_merge($metadata, [
            'account_id' => $tenantId,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'admin_name' => $adminName,
        ]));
    }

    public function getTriggerKey(): string
    {
        return 'account_status_override';
    }

    public function getCategory(): string
    {
        return 'customer_risk';
    }

    public function isAdminAlert(): bool
    {
        return true;
    }

    public function getTitle(): string
    {
        return sprintf('Account status overridden: %s → %s', $this->previousStatus, $this->newStatus);
    }

    public function getBody(): string
    {
        $body = sprintf(
            'Admin %s overrode account status from "%s" to "%s".',
            $this->adminName ?? 'unknown',
            $this->previousStatus,
            $this->newStatus
        );

        if ($this->reason) {
            $body .= sprintf(' Reason: %s', $this->reason);
        }

        return $body;
    }
}
