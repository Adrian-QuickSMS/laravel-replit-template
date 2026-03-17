<?php

namespace App\Events\Alerting;

class FlowError extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $flowId,
        protected string $flowName,
        protected string $errorType,
        protected string $errorMessage,
        protected ?string $nodeId = null,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'critical', array_merge($metadata, [
            'flow_id' => $flowId,
            'flow_name' => $flowName,
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'node_id' => $nodeId,
        ]));
    }

    public function getTriggerKey(): string { return 'flow_error'; }
    public function getCategory(): string { return 'campaign'; }
    public function getTitle(): string { return sprintf('Flow error in "%s"', $this->flowName); }
    public function getBody(): string { return sprintf('Error in flow "%s": %s. %s', $this->flowName, $this->errorType, $this->errorMessage); }
}
