<?php

namespace App\Events\Alerting;

class SenderIdStatusChanged extends BaseAlertEvent
{
    public function __construct(
        ?string $tenantId,
        protected string $senderId,
        protected string $oldStatus,
        protected string $newStatus,
        protected ?string $country = null,
        array $metadata = [],
    ) {
        $severity = in_array($newStatus, ['rejected', 'suspended']) ? 'warning' : 'info';
        parent::__construct($tenantId, $severity, array_merge($metadata, [
            'sender_id' => $senderId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'country' => $country,
        ]));
    }

    public function getTriggerKey(): string { return 'sender_id_status_changed'; }
    public function getCategory(): string { return 'compliance'; }
    public function getTitle(): string { return sprintf('Sender ID %s: %s', $this->senderId, $this->newStatus); }

    public function getBody(): string
    {
        $msg = sprintf('Sender ID "%s" status changed from %s to %s.', $this->senderId, $this->oldStatus, $this->newStatus);
        if ($this->country) {
            $msg .= sprintf(' Country: %s.', $this->country);
        }
        return $msg;
    }
}
