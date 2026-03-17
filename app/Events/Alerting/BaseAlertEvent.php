<?php

namespace App\Events\Alerting;

use App\Contracts\AlertableEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class BaseAlertEvent implements AlertableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        protected ?string $tenantId = null,
        protected string $severity = 'info',
        protected array $metadata = [],
    ) {}

    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function isAdminAlert(): bool
    {
        return false;
    }

    public function getTriggerValue(): float
    {
        return 1.0; // Default for event-based triggers
    }
}
