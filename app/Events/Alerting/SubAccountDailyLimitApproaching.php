<?php

namespace App\Events\Alerting;

class SubAccountDailyLimitApproaching extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $subAccountId,
        protected string $subAccountName,
        protected int $dailyUsed,
        protected int $dailyLimit,
    ) {
        parent::__construct($tenantId, 'warning', [
            'sub_account_id' => $subAccountId,
            'sub_account_name' => $subAccountName,
            'daily_used' => $dailyUsed,
            'daily_limit' => $dailyLimit,
            'usage_percentage' => $dailyLimit > 0 ? round(($dailyUsed / $dailyLimit) * 100, 1) : 0,
        ]);
    }

    public function getTriggerKey(): string
    {
        return 'sub_account_daily_limit_approaching';
    }

    public function getTriggerValue(): float
    {
        return $this->dailyLimit > 0 ? ($this->dailyUsed / $this->dailyLimit) * 100 : 0;
    }

    public function getCategory(): string
    {
        return 'sub_account';
    }

    public function getTitle(): string
    {
        return "Sub-account \"{$this->subAccountName}\" is approaching its daily send limit";
    }

    public function getBody(): string
    {
        $percentage = $this->dailyLimit > 0 ? round(($this->dailyUsed / $this->dailyLimit) * 100, 1) : 0;

        return sprintf(
            'Sub-account "%s" has sent %s of its %s daily send limit (%.1f%%).',
            $this->subAccountName,
            number_format($this->dailyUsed),
            number_format($this->dailyLimit),
            $percentage
        );
    }
}
