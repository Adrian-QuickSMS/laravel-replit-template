<?php

namespace App\Events\Alerting;

class BalanceThresholdBreached extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected float $usagePercentage,
        protected float $remainingBalance,
        protected string $currency,
        string $severity = 'warning',
        array $metadata = [],
    ) {
        parent::__construct($tenantId, $severity, array_merge($metadata, [
            'remaining_balance' => $remainingBalance,
            'currency' => $currency,
            'usage_percentage' => $usagePercentage,
        ]));
    }

    public function getTriggerKey(): string
    {
        return 'credit_balance_percentage';
    }

    public function getTriggerValue(): float
    {
        return $this->usagePercentage;
    }

    public function getCategory(): string
    {
        return 'billing';
    }

    public function getTitle(): string
    {
        return 'Credit balance running low';
    }

    public function getBody(): string
    {
        return sprintf(
            'Your credit balance is running low. %.1f%% used, %s %.2f remaining.',
            $this->usagePercentage,
            $this->currency,
            $this->remainingBalance
        );
    }
}
