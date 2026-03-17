<?php

namespace App\Services\Alerting;

use App\Contracts\AlertableEvent;
use App\Models\Alerting\AlertHistory;
use App\Models\Alerting\AlertRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AlertEvaluatorService
{
    public function __construct(
        private AlertFrequencyService $frequencyService,
        private AlertDispatcherService $dispatcherService,
    ) {}

    /**
     * Evaluate an alertable event against all matching rules.
     */
    public function evaluate(AlertableEvent $event): void
    {
        $rules = $this->findMatchingRules($event);

        if ($rules->isEmpty()) {
            Log::debug('[AlertEvaluator] No matching rules', [
                'trigger_key' => $event->getTriggerKey(),
                'tenant_id' => $event->getTenantId(),
            ]);
            return;
        }

        foreach ($rules as $rule) {
            $this->evaluateRule($rule, $event);
        }
    }

    /**
     * Find all enabled rules matching the event.
     */
    private function findMatchingRules(AlertableEvent $event): Collection
    {
        $query = AlertRule::enabled()
            ->forTriggerKey($event->getTriggerKey());

        $tenantId = $event->getTenantId();

        if ($event->isAdminAlert()) {
            // Admin alerts: match system-level rules only (tenant_id is null)
            $query->whereNull('tenant_id');
        } else {
            // Customer alerts: match tenant-specific rules OR system defaults
            $query->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                    ->orWhere(function ($q2) {
                        $q2->whereNull('tenant_id')
                            ->where('is_system_default', true);
                    });
            });
        }

        return $query->get();
    }

    /**
     * Evaluate a single rule against the event.
     */
    private function evaluateRule(AlertRule $rule, AlertableEvent $event): void
    {
        $triggerValue = $event->getTriggerValue();

        // Check if condition is met
        if (!$rule->evaluateCondition($triggerValue)) {
            Log::debug('[AlertEvaluator] Condition not met', [
                'rule_id' => $rule->id,
                'trigger_key' => $rule->trigger_key,
                'trigger_value' => $triggerValue,
                'condition' => $rule->condition_operator . ' ' . $rule->condition_value,
            ]);
            return;
        }

        // Check frequency controls
        $decision = $this->frequencyService->shouldDispatch($rule, $triggerValue);

        if ($decision === 'suppress') {
            $this->recordHistory($rule, $event, [], 'suppressed_cooldown');
            return;
        }

        if ($decision === 'batch') {
            $this->handleBatchedAlert($rule, $event);
            $rule->markTriggered($triggerValue);
            return;
        }

        // Instant dispatch
        $dispatchedChannels = $this->dispatcherService->dispatch(
            $rule,
            $event,
            $rule->channels ?? ['in_app']
        );

        $rule->markTriggered($triggerValue);
        $this->recordHistory($rule, $event, $dispatchedChannels, 'dispatched');

        Log::info('[AlertEvaluator] Alert dispatched', [
            'rule_id' => $rule->id,
            'trigger_key' => $event->getTriggerKey(),
            'trigger_value' => $triggerValue,
            'channels' => $dispatchedChannels,
            'severity' => $event->getSeverity(),
        ]);
    }

    /**
     * Handle batched alert delivery.
     */
    private function handleBatchedAlert(AlertRule $rule, AlertableEvent $event): void
    {
        $payload = $this->dispatcherService->buildPayload($rule, $event);

        foreach ($rule->channels ?? ['in_app'] as $channel) {
            $this->frequencyService->addToBatch(
                $rule,
                $channel,
                $payload,
                $event->getTenantId(),
            );
        }

        $this->recordHistory($rule, $event, $rule->channels ?? [], 'batched');
    }

    /**
     * Record alert history entry.
     */
    private function recordHistory(
        AlertRule $rule,
        AlertableEvent $event,
        array $channelsDispatched,
        string $status,
    ): void {
        try {
            AlertHistory::create([
                'alert_rule_id' => $rule->id,
                'tenant_id' => $event->getTenantId(),
                'trigger_key' => $event->getTriggerKey(),
                'trigger_value' => $event->getTriggerValue(),
                'condition_value' => $rule->condition_value,
                'severity' => $event->getSeverity(),
                'category' => $event->getCategory(),
                'title' => $event->getTitle(),
                'body' => $event->getBody(),
                'channels_dispatched' => $channelsDispatched,
                'status' => $status,
                'metadata' => $event->getMetadata(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[AlertEvaluator] Failed to record history', [
                'rule_id' => $rule->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
