<?php

namespace App\Events\Alerting;

class SubAccountVolumeCapApproaching extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $subAccountId,
        protected string $subAccountName,
        protected int $messagesUsed,
        protected int $messageCap,
    ) {
        parent::__construct($tenantId, 'warning', [
            'sub_account_id' => $subAccountId,
            'sub_account_name' => $subAccountName,
            'messages_used' => $messagesUsed,
            'message_cap' => $messageCap,
            'usage_percentage' => $messageCap > 0 ? round(($messagesUsed / $messageCap) * 100, 1) : 0,
        ]);
    }

    public function getTriggerKey(): string
    {
        return 'sub_account_volume_cap_approaching';
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
        return "Sub-account \"{$this->subAccountName}\" is approaching its message cap";
    }

    public function getBody(): string
    {
        $percentage = $this->messageCap > 0 ? round(($this->messagesUsed / $this->messageCap) * 100, 1) : 0;

        return sprintf(
            'Sub-account "%s" has sent %s of its %s monthly message cap (%.1f%%).',
            $this->subAccountName,
            number_format($this->messagesUsed),
            number_format($this->messageCap),
            $percentage
        );
    }
}
