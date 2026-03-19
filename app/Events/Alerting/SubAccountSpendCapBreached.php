<?php

namespace App\Events\Alerting;

class SubAccountSpendCapBreached extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $subAccountId,
        protected string $subAccountName,
        protected float $spendUsed,
        protected float $spendCap,
        protected bool $hardStopped,
        string $severity = 'warning',
    ) {
        parent::__construct($tenantId, $severity, [
            'sub_account_id' => $subAccountId,
            'sub_account_name' => $subAccountName,
            'spend_used' => $spendUsed,
            'spend_cap' => $spendCap,
            'hard_stopped' => $hardStopped,
            'usage_percentage' => $spendCap > 0 ? round(($spendUsed / $spendCap) * 100, 1) : 0,
        ]);
    }

    public function getTriggerKey(): string
    {
        return 'sub_account_spend_cap';
    }

    public function getTriggerValue(): float
    {
        return $this->spendCap > 0 ? ($this->spendUsed / $this->spendCap) * 100 : 0;
    }

    public function getCategory(): string
    {
        return 'billing';
    }

    public function getTitle(): string
    {
        return "Sub-account \"{$this->subAccountName}\" has exceeded its spending cap";
    }

    public function getBody(): string
    {
        $action = $this->hardStopped ? 'Sending has been automatically stopped.' : 'Sending will continue until manually stopped.';

        return sprintf(
            'Sub-account "%s" has used £%.2f of its £%.2f monthly spending cap. %s',
            $this->subAccountName,
            $this->spendUsed,
            $this->spendCap,
            $action
        );
    }
}
