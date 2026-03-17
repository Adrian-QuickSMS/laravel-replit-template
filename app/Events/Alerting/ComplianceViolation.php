<?php

namespace App\Events\Alerting;

class ComplianceViolation extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $violationType,
        protected string $description,
        protected ?string $accountNumber = null,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'critical', array_merge($metadata, [
            'violation_type' => $violationType,
            'description' => $description,
            'account_number' => $accountNumber,
        ]));
    }

    public function getTriggerKey(): string { return 'compliance_violation'; }
    public function getCategory(): string { return 'compliance_legal'; }
    public function getTitle(): string { return sprintf('Compliance violation: %s', $this->violationType); }
    public function getBody(): string { return sprintf('%s violation detected. %s', $this->violationType, $this->description); }
    public function isAdminAlert(): bool { return true; }
}
