<?php

namespace App\Listeners\Alerting;

use App\Contracts\AlertableEvent;
use App\Events\Alerting\AccountSecuritySettingChanged;
use App\Events\Alerting\AccountStatusOverridden;
use App\Events\Alerting\ApiConnectionStateChanged;
use App\Events\Alerting\ApiErrorsThresholdBreached;
use App\Events\Alerting\ApiKeyLifecycleEvent;
use App\Events\Alerting\AutoTopUpTriggered;
use App\Events\Alerting\BalanceThresholdBreached;
use App\Events\Alerting\CampaignCompleted;
use App\Events\Alerting\CampaignUnderperforming;
use App\Events\Alerting\CarrierDegradationDetected;
use App\Events\Alerting\ComplianceViolation;
use App\Events\Alerting\CountryRestrictionTriggered;
use App\Events\Alerting\CustomerTrafficAnomaly;
use App\Events\Alerting\DeliveryRateDropped;
use App\Events\Alerting\DlrLatencyIncreased;
use App\Events\Alerting\FailedMessagesThresholdBreached;
use App\Events\Alerting\FlowError;
use App\Events\Alerting\HighRiskAccountBehaviour;
use App\Events\Alerting\InvoiceGenerated;
use App\Events\Alerting\IpAllowlistChanged;
use App\Events\Alerting\MessageBlockedByRegulation;
use App\Events\Alerting\MfaStatusChanged;
use App\Events\Alerting\NegativeMarginRouteDetected;
use App\Events\Alerting\PasswordChanged;
use App\Events\Alerting\PaymentFailed;
use App\Events\Alerting\PendingMessagesSpiked;
use App\Events\Alerting\QueueBacklogBuilding;
use App\Events\Alerting\RateLimitHit;
use App\Events\Alerting\RcsAgentStatusChanged;
use App\Events\Alerting\RoutingFailure;
use App\Events\Alerting\SenderIdStatusChanged;
use App\Events\Alerting\SpamFilterModeChanged;
use App\Events\Alerting\SpamFilterTriggered;
use App\Events\Alerting\SpendAnomalyDetected;
use App\Events\Alerting\SubAccountDailyLimitApproaching;
use App\Events\Alerting\SubAccountDailyLimitBreached;
use App\Events\Alerting\SubAccountSpendCapApproaching;
use App\Events\Alerting\SubAccountSpendCapBreached;
use App\Events\Alerting\SubAccountVolumeCapApproaching;
use App\Events\Alerting\SubAccountVolumeCapBreached;
use App\Events\Alerting\SuspiciousLoginDetected;
use App\Events\Alerting\WebhookDeliveryFailed;
use App\Jobs\Alerting\EvaluateAlertEventJob;
use Illuminate\Events\Dispatcher;

/**
 * Subscribes to all AlertableEvent implementations and dispatches
 * them to the alerting engine for evaluation.
 */
class AlertEventSubscriber
{
    /**
     * Handle any alertable event by dispatching it to the evaluator queue.
     */
    public function handleAlertableEvent(AlertableEvent $event): void
    {
        EvaluateAlertEventJob::dispatch($event);
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        $eventClasses = [
            // Billing
            BalanceThresholdBreached::class,
            PaymentFailed::class,
            InvoiceGenerated::class,
            AutoTopUpTriggered::class,
            SpendAnomalyDetected::class,

            // Messaging
            DeliveryRateDropped::class,
            FailedMessagesThresholdBreached::class,
            PendingMessagesSpiked::class,
            DlrLatencyIncreased::class,
            CarrierDegradationDetected::class,

            // Compliance
            SenderIdStatusChanged::class,
            RcsAgentStatusChanged::class,
            CountryRestrictionTriggered::class,
            MessageBlockedByRegulation::class,

            // Security
            SuspiciousLoginDetected::class,
            ApiKeyLifecycleEvent::class,
            PasswordChanged::class,
            MfaStatusChanged::class,
            AccountSecuritySettingChanged::class,
            IpAllowlistChanged::class,
            ApiConnectionStateChanged::class,

            // System
            ApiErrorsThresholdBreached::class,
            WebhookDeliveryFailed::class,
            RateLimitHit::class,

            // Campaign
            CampaignCompleted::class,
            CampaignUnderperforming::class,
            FlowError::class,

            // Admin
            SpamFilterTriggered::class,
            HighRiskAccountBehaviour::class,
            QueueBacklogBuilding::class,
            RoutingFailure::class,
            NegativeMarginRouteDetected::class,
            ComplianceViolation::class,
            CustomerTrafficAnomaly::class,
            AccountStatusOverridden::class,
            SpamFilterModeChanged::class,

            // Sub-Account Caps & Limits
            SubAccountSpendCapBreached::class,
            SubAccountSpendCapApproaching::class,
            SubAccountVolumeCapBreached::class,
            SubAccountVolumeCapApproaching::class,
            SubAccountDailyLimitBreached::class,
            SubAccountDailyLimitApproaching::class,
        ];

        $subscriptions = [];
        foreach ($eventClasses as $eventClass) {
            $subscriptions[$eventClass] = 'handleAlertableEvent';
        }

        return $subscriptions;
    }
}
