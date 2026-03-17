<?php

namespace App\Contracts;

/**
 * Interface for events that can trigger alert rules.
 *
 * Any event implementing this interface will be picked up by the
 * AlertEventSubscriber and evaluated against matching AlertRule records.
 */
interface AlertableEvent
{
    /**
     * The trigger key that matches against AlertRule.trigger_key.
     * e.g. 'credit_balance_percentage', 'delivery_rate', 'payment_failed'
     */
    public function getTriggerKey(): string;

    /**
     * The current metric value that triggered the event.
     * For event-based triggers, return 1.0 as a sentinel.
     */
    public function getTriggerValue(): float;

    /**
     * The tenant (account) ID this event belongs to.
     * Return null for admin/system-level events.
     */
    public function getTenantId(): ?string;

    /**
     * Severity level: 'critical', 'warning', or 'info'.
     */
    public function getSeverity(): string;

    /**
     * Alert category: 'billing', 'messaging', 'compliance', 'security', 'system', 'campaign',
     * or admin categories: 'fraud', 'platform_health', 'customer_risk', 'commercial', 'compliance_legal'.
     */
    public function getCategory(): string;

    /**
     * Human-readable title for the alert notification.
     */
    public function getTitle(): string;

    /**
     * Human-readable body/description for the alert notification.
     */
    public function getBody(): string;

    /**
     * Additional metadata to include in the alert (stored as JSON).
     */
    public function getMetadata(): array;

    /**
     * Whether this is an admin-facing alert (vs customer-facing).
     */
    public function isAdminAlert(): bool;
}
