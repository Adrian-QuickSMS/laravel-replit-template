<?php

namespace App\Events\Alerting;

class InvoiceGenerated extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $invoiceNumber,
        protected float $amount,
        protected string $currency,
        array $metadata = [],
    ) {
        parent::__construct($tenantId, 'info', array_merge($metadata, [
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'currency' => $currency,
        ]));
    }

    public function getTriggerKey(): string { return 'invoice_generated'; }
    public function getCategory(): string { return 'billing'; }
    public function getTitle(): string { return 'New invoice generated'; }

    public function getBody(): string
    {
        return sprintf('Invoice %s for %s %.2f has been generated.', $this->invoiceNumber, $this->currency, $this->amount);
    }
}
