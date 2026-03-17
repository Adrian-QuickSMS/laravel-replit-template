<?php

namespace App\Events\Alerting;

class NegativeMarginRouteDetected extends BaseAlertEvent
{
    public function __construct(
        protected string $routeName,
        protected string $country,
        protected float $costPerMessage,
        protected float $revenuePerMessage,
        protected float $marginPercentage,
        array $metadata = [],
    ) {
        parent::__construct(null, 'warning', array_merge($metadata, [
            'route_name' => $routeName,
            'country' => $country,
            'cost_per_message' => $costPerMessage,
            'revenue_per_message' => $revenuePerMessage,
            'margin_percentage' => $marginPercentage,
        ]));
    }

    public function getTriggerKey(): string { return 'negative_margin_route'; }
    public function getTriggerValue(): float { return $this->marginPercentage; }
    public function getCategory(): string { return 'commercial'; }
    public function getTitle(): string { return sprintf('Negative margin: %s (%s)', $this->routeName, $this->country); }
    public function getBody(): string { return sprintf('Route "%s" to %s has negative margin (%.1f%%). Cost: %.4f, Revenue: %.4f.', $this->routeName, $this->country, $this->marginPercentage, $this->costPerMessage, $this->revenuePerMessage); }
    public function isAdminAlert(): bool { return true; }
}
