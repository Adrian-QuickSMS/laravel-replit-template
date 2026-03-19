<?php

namespace App\Services\Alerting;

use App\Contracts\AlertableEvent;
use App\Jobs\Alerting\CreateInAppNotificationJob;
use App\Jobs\Alerting\SendEmailAlertJob;
use App\Jobs\Alerting\SendSlackAlertJob;
use App\Jobs\Alerting\SendSmsAlertJob;
use App\Jobs\Alerting\SendTeamsAlertJob;
use App\Jobs\Alerting\SendWebhookAlertJob;
use App\Models\Alerting\AlertChannelConfig;
use App\Models\Alerting\AlertPreference;
use App\Models\Alerting\AlertRule;
use Illuminate\Support\Facades\Log;

class AlertDispatcherService
{
    /**
     * Dispatch an alert through all configured channels.
     *
     * @param AlertRule $rule The matched alert rule
     * @param AlertableEvent $event The triggering event
     * @param array $channels The channels to dispatch to
     * @return array List of channels that were dispatched
     */
    public function dispatch(AlertRule $rule, AlertableEvent $event, array $channels): array
    {
        $dispatched = [];
        $queue = config('alerting.queue.dispatch', 'alerts');

        // Resolve effective channels based on user preferences
        $effectiveChannels = $this->resolveEffectiveChannels($channels, $event);

        $payload = $this->buildPayload($rule, $event);

        foreach ($effectiveChannels as $channel) {
            try {
                $this->dispatchToChannel($channel, $payload, $event, $queue);
                $dispatched[] = $channel;
            } catch (\Throwable $e) {
                Log::error('[AlertDispatcher] Failed to dispatch to channel', [
                    'channel' => $channel,
                    'rule_id' => $rule->id,
                    'trigger_key' => $event->getTriggerKey(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Check escalation rules
        $escalationChannels = $rule->getEscalationChannels($event->getTriggerValue());
        foreach ($escalationChannels as $channel) {
            if (!in_array($channel, $dispatched)) {
                try {
                    $this->dispatchToChannel($channel, $payload, $event, $queue);
                    $dispatched[] = $channel;
                } catch (\Throwable $e) {
                    Log::error('[AlertDispatcher] Failed to dispatch escalation channel', [
                        'channel' => $channel,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return $dispatched;
    }

    /**
     * Build the notification payload from the event.
     */
    public function buildPayload(AlertRule $rule, AlertableEvent $event): array
    {
        return [
            'rule_id' => $rule->id,
            'trigger_key' => $event->getTriggerKey(),
            'trigger_value' => $event->getTriggerValue(),
            'tenant_id' => $event->getTenantId(),
            'category' => $event->getCategory(),
            'severity' => $event->getSeverity(),
            'title' => $event->getTitle(),
            'body' => $event->getBody(),
            'metadata' => $event->getMetadata(),
            'is_admin' => $event->isAdminAlert(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Resolve effective channels, considering user preferences.
     */
    private function resolveEffectiveChannels(array $ruleChannels, AlertableEvent $event): array
    {
        // For admin alerts, use rule channels directly (no user preferences)
        if ($event->isAdminAlert()) {
            return $ruleChannels;
        }

        $tenantId = $event->getTenantId();
        if (!$tenantId) {
            return $ruleChannels;
        }

        // Check if there's a preference override for this category
        $preference = AlertPreference::forTenant($tenantId)
            ->forCategory($event->getCategory())
            ->first();

        if ($preference && $preference->isCurrentlyMuted()) {
            return []; // Category is muted
        }

        if ($preference) {
            // Intersect rule channels with user preferences
            $preferredChannels = $preference->getEffectiveChannels();
            return array_values(array_intersect($ruleChannels, $preferredChannels));
        }

        return $ruleChannels;
    }

    /**
     * Dispatch to a specific channel.
     */
    private function dispatchToChannel(string $channel, array $payload, AlertableEvent $event, string $queue): void
    {
        $tenantId = $event->getTenantId();

        match ($channel) {
            'in_app' => CreateInAppNotificationJob::dispatch($payload)
                ->onQueue($queue),

            'email' => SendEmailAlertJob::dispatch($payload, $this->resolveEmailRecipients($payload, $tenantId))
                ->onQueue($queue),

            'webhook' => SendWebhookAlertJob::dispatch($payload, $this->resolveWebhookConfig($tenantId))
                ->onQueue($queue),

            'sms' => SendSmsAlertJob::dispatch($payload, $this->resolveSmsRecipients($payload, $tenantId))
                ->onQueue($queue),

            'slack' => SendSlackAlertJob::dispatch($payload, $this->resolveChannelConfig('slack', $tenantId))
                ->onQueue($queue),

            'teams' => SendTeamsAlertJob::dispatch($payload, $this->resolveChannelConfig('teams', $tenantId))
                ->onQueue($queue),

            default => Log::warning('[AlertDispatcher] Unknown channel', ['channel' => $channel]),
        };
    }

    /**
     * Resolve email recipients for a tenant.
     */
    private function resolveEmailRecipients(array $payload, ?string $tenantId): array
    {
        $recipients = [];

        // Only use configured channel recipients — never trust metadata recipients
        // to prevent potential email injection via crafted event payloads
        if ($tenantId) {
            $config = AlertChannelConfig::forTenant($tenantId)
                ->forChannel('email')
                ->enabled()
                ->first();

            if ($config && !empty($config->config['email'])) {
                $recipients[] = $config->config['email'];
            }
        }

        return array_unique($recipients);
    }

    /**
     * Resolve webhook configuration for a tenant.
     */
    private function resolveWebhookConfig(?string $tenantId): array
    {
        if (!$tenantId) {
            return [];
        }

        $config = AlertChannelConfig::forTenant($tenantId)
            ->forChannel('webhook')
            ->enabled()
            ->first();

        if (!$config) {
            return [];
        }

        return $config->config ?? [];
    }

    /**
     * Resolve SMS recipients.
     */
    private function resolveSmsRecipients(array $payload, ?string $tenantId): array
    {
        $recipients = [];

        if ($tenantId) {
            $config = AlertChannelConfig::forTenant($tenantId)
                ->forChannel('sms')
                ->enabled()
                ->first();

            if ($config && !empty($config->config['phone'])) {
                $recipients[] = $config->config['phone'];
            }
        }

        return array_unique($recipients);
    }

    /**
     * Resolve channel config (Slack/Teams).
     */
    private function resolveChannelConfig(string $channel, ?string $tenantId): array
    {
        if (!$tenantId) {
            // For admin alerts, use global config
            return [
                'webhook_url' => config("alerting.admin_{$channel}_webhook_url"),
            ];
        }

        $config = AlertChannelConfig::forTenant($tenantId)
            ->forChannel($channel)
            ->enabled()
            ->first();

        return $config ? ($config->config ?? []) : [];
    }
}
