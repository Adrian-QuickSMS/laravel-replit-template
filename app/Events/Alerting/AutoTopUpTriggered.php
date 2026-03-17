<?php

namespace App\Events\Alerting;

class AutoTopUpTriggered extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected float $amount,
        protected string $currency,
        protected bool $success,
        protected ?string $failureReason = null,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, $success ? 'info' : 'critical', array_merge($metadata, [
            'amount' => $amount,
            'currency' => $currency,
            'success' => $success,
            'failure_reason' => $failureReason,
        ]));
    }

    public function getTriggerKey(): string { return 'auto_top_up'; }
    public function getCategory(): string { return 'billing'; }
    public function getTitle(): string { return $this->success ? 'Auto top-up successful' : 'Auto top-up failed'; }

    public function getBody(): string
    {
        if ($this->success) {
            return sprintf('Auto top-up of %s %.2f was successful.', $this->currency, $this->amount);
        }
        return sprintf('Auto top-up of %s %.2f failed. Reason: %s', $this->currency, $this->amount, $this->failureReason ?? 'Unknown');
    }
}
