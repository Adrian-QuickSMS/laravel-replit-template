<?php

namespace App\Events\Alerting;

class PaymentFailed extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $paymentMethod,
        protected float $amount,
        protected string $currency,
        protected string $failureReason,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'critical', array_merge($metadata, [
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'currency' => $currency,
            'failure_reason' => $failureReason,
        ]));
    }

    public function getTriggerKey(): string { return 'payment_failed'; }
    public function getCategory(): string { return 'billing'; }
    public function getTitle(): string { return 'Payment failed'; }

    public function getBody(): string
    {
        return sprintf(
            'Payment of %s %.2f failed. Reason: %s',
            $this->currency,
            $this->amount,
            $this->failureReason
        );
    }
}
