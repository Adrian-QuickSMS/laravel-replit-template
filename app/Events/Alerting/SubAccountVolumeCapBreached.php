<?php

namespace App\Events\Alerting;

class SubAccountVolumeCapBreached extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $subAccountId,
        protected string $subAccountName,
        protected int $messagesUsed,
        protected int $messageCap,
        protected bool $hardStopped,
        string $severity = 'warning',
    ) {
        parent::__construct($tenantId, $severity, [
            'sub_account_id' => $subAccountId,
            'sub_account_name' => $subAccountName,
            'messages_used' => $messagesUsed,
            'message_cap' => $messageCap,
            'hard_stopped' => $hardStopped,
            'usage_percentage' => $messageCap > 0 ? round(($messagesUsed / $messageCap) * 100, 1) : 0,
        ]);
    }

    public function getTriggerKey(): string
    {
        return 'sub_account_volume_cap';
    }

    public function getTriggerValue(): float
    {
        return $this->messageCap > 0 ? ($this->messagesUsed / $this->messageCap) * 100 : 0;
    }

    public function getCategory(): string
    {
        return 'billing';
    }

    public function getTitle(): string
    {
        return "Sub-account \"{$this->subAccountName}\" has exceeded its message cap";
    }

    public function getBody(): string
    {
        $action = $this->hardStopped ? 'Sending has been automatically stopped.' : 'Sending will continue until manually stopped.';

        return sprintf(
            'Sub-account "%s" has sent %s of its %s monthly message cap. %s',
            $this->subAccountName,
            number_format($this->messagesUsed),
            number_format($this->messageCap),
            $action
        );
    }
}
