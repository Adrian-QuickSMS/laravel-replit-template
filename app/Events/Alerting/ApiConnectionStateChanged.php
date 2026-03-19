<?php

namespace App\Events\Alerting;

class ApiConnectionStateChanged extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $connectionName,
        protected string $oldState,
        protected string $newState,
        protected ?string $actor = null,
        string $severity = 'warning',
        array $metadata = [],
    ) {
        // Suspension or archival is critical
        if (in_array($newState, ['suspended', 'archived'])) {
            $severity = 'critical';
        }

        parent::__construct($tenantId, $severity, array_merge($metadata, [
            'connection_name' => $connectionName,
            'old_state' => $oldState,
            'new_state' => $newState,
            'actor' => $actor,
        ]));
    }

    public function getTriggerKey(): string
    {
        return 'api_connection_state_changed';
    }

    public function getCategory(): string
    {
        return 'security';
    }

    public function getTitle(): string
    {
        return sprintf('API connection "%s" %s', $this->connectionName, $this->newState);
    }

    public function getBody(): string
    {
        $body = sprintf(
            'API connection "%s" status changed from %s to %s.',
            $this->connectionName,
            $this->oldState,
            $this->newState
        );

        if ($this->actor) {
            $body .= sprintf(' Changed by: %s.', $this->actor);
        }

        return $body;
    }
}
