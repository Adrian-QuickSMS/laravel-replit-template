<?php

namespace App\Events\Alerting;

class SubAccountDailyLimitBreached extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $subAccountId,
        protected string $subAccountName,
        protected int $dailyUsed,
        protected int $dailyLimit,
        protected bool $hardStopped,
    ) {
        $severity = $hardStopped ? 'critical' : 'warning';

        parent::__construct($tenantId, $severity, [
            'sub_account_id' => $subAccountId,
            'sub_account_name' => $subAccountName,
            'daily_used' => $dailyUsed,
            'daily_limit' => $dailyLimit,
            'hard_stopped' => $hardStopped,
            'usage_percentage' => $dailyLimit > 0 ? round(($dailyUsed / $dailyLimit) * 100, 1) : 0,
        ]);
    }

    public function getTriggerKey(): string
    {
        return 'sub_account_daily_limit';
    }

    public function getTriggerValue(): float
    {
        return $this->dailyLimit > 0 ? ($this->dailyUsed / $this->dailyLimit) * 100 : 0;
    }

    public function getCategory(): string
    {
        return 'billing';
    }

    public function getTitle(): string
    {
        return "Sub-account \"{$this->subAccountName}\" has exceeded its daily send limit";
    }

    public function getBody(): string
    {
        $action = $this->hardStopped ? 'Sending has been automatically stopped.' : 'Sending will continue until manually stopped.';

        return sprintf(
            'Sub-account "%s" has sent %s messages today, exceeding its daily limit of %s. %s',
            $this->subAccountName,
            number_format($this->dailyUsed),
            number_format($this->dailyLimit),
            $action
        );
    }
}
