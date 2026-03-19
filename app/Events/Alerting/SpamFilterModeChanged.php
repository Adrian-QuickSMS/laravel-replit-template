<?php

namespace App\Events\Alerting;

class SpamFilterModeChanged extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $previousMode,
        protected string $newMode,
        protected ?string $adminName = null,
        string $severity = 'warning',
        array $metadata = [],
    ) {
        // Disabling spam filter entirely is critical
        if ($newMode === 'off') {
            $severity = 'critical';
        }

        parent::__construct($tenantId, $severity, array_merge($metadata, [
            'account_id' => $tenantId,
            'previous_mode' => $previousMode,
            'new_mode' => $newMode,
            'admin_name' => $adminName,
        ]));
    }

    public function getTriggerKey(): string
    {
        return 'spam_filter_mode_changed';
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
        return sprintf('Spam filter mode changed: %s → %s', $this->previousMode, $this->newMode);
    }

    public function getBody(): string
    {
        $body = sprintf(
            'Admin %s changed spam filter mode from "%s" to "%s".',
            $this->adminName ?? 'unknown',
            $this->previousMode,
            $this->newMode
        );

        if ($this->newMode === 'off') {
            $body .= ' WARNING: Spam filtering is now completely disabled for this account.';
        }

        return $body;
    }
}
