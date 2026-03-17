<?php

namespace App\Events\Alerting;

class SpamFilterTriggered extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $accountNumber,
        protected string $filterEngine,
        protected string $ruleName,
        protected ?string $messageContent = null,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'warning', array_merge($metadata, [
            'account_number' => $accountNumber,
            'filter_engine' => $filterEngine,
            'rule_name' => $ruleName,
        ]));
    }

    public function getTriggerKey(): string { return 'spam_filter_triggered'; }
    public function getCategory(): string { return 'fraud'; }
    public function getTitle(): string { return sprintf('Spam filter: %s (%s)', $this->ruleName, $this->accountNumber); }
    public function getBody(): string { return sprintf('Spam filter triggered for account %s. Engine: %s, Rule: %s.', $this->accountNumber, $this->filterEngine, $this->ruleName); }
    public function isAdminAlert(): bool { return true; }
}
