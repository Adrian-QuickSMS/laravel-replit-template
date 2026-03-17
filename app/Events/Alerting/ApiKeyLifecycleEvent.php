<?php

namespace App\Events\Alerting;

class ApiKeyLifecycleEvent extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $action, // created, deleted, suspended, activated
        protected string $connectionName,
        protected ?string $actorId = null,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, in_array($action, ['deleted', 'suspended']) ? 'warning' : 'info', array_merge($metadata, [
            'action' => $action,
            'connection_name' => $connectionName,
            'actor_id' => $actorId,
        ]));
    }

    public function getTriggerKey(): string { return 'api_key_lifecycle'; }
    public function getCategory(): string { return 'security'; }
    public function getTitle(): string { return sprintf('API key %s: %s', $this->action, $this->connectionName); }
    public function getBody(): string { return sprintf('API connection "%s" was %s.', $this->connectionName, $this->action); }
}
