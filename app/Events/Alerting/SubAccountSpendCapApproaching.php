<?php

namespace App\Events\Alerting;

class SubAccountSpendCapApproaching extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $subAccountId,
        protected string $subAccountName,
        protected float $spendUsed,
        protected float $spendCap,
    ) {
        parent::__construct($tenantId, 'warning', [
            'sub_account_id' => $subAccountId,
            'sub_account_name' => $subAccountName,
            'spend_used' => $spendUsed,
            'spend_cap' => $spendCap,
            'usage_percentage' => $spendCap > 0 ? round(($spendUsed / $spendCap) * 100, 1) : 0,
        ]);
    }

    public function getTriggerKey(): string
    {
        return 'sub_account_spend_cap_approaching';
    }

    public function getTriggerValue(): float
    {
        return $this->spendCap > 0 ? ($this->spendUsed / $this->spendCap) * 100 : 0;
    }

    public function getCategory(): string
    {
        return 'sub_account';
    }

    public function getTitle(): string
    {
        return "Sub-account \"{$this->subAccountName}\" is approaching its spending cap";
    }

    public function getBody(): string
    {
        $percentage = $this->spendCap > 0 ? round(($this->spendUsed / $this->spendCap) * 100, 1) : 0;

        return sprintf(
            'Sub-account "%s" has used £%.2f of its £%.2f monthly spending cap (%.1f%%).',
            $this->subAccountName,
            $this->spendUsed,
            $this->spendCap,
            $percentage
        );
    }
}
