<?php

namespace App\Events\Alerting;

class HighRiskAccountBehaviour extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $accountNumber,
        protected string $behaviourType,
        protected string $description,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'critical', array_merge($metadata, [
            'account_number' => $accountNumber,
            'behaviour_type' => $behaviourType,
            'description' => $description,
        ]));
    }

    public function getTriggerKey(): string { return 'high_risk_account'; }
    public function getCategory(): string { return 'fraud'; }
    public function getTitle(): string { return sprintf('High-risk behaviour: %s', $this->accountNumber); }
    public function getBody(): string { return sprintf('Account %s flagged for %s. %s', $this->accountNumber, $this->behaviourType, $this->description); }
    public function isAdminAlert(): bool { return true; }
}
