<?php

namespace App\Services\Alerting;

use App\Models\Alerting\AlertRule;
use App\Models\Alerting\NotificationBatch;
use Illuminate\Support\Facades\Log;

class AlertFrequencyService
{
    /**
     * Determine if an alert should be dispatched based on frequency controls.
     *
     * @return string 'dispatch', 'batch', or 'suppress'
     */
    public function shouldDispatch(AlertRule $rule, float $triggerValue): string
    {
        // Check cooldown first
        if ($rule->isOnCooldown()) {
            Log::debug('[AlertFrequency] Suppressed by cooldown', [
                'rule_id' => $rule->id,
                'trigger_key' => $rule->trigger_key,
                'last_triggered' => $rule->last_triggered_at?->toIso8601String(),
                'cooldown_minutes' => $rule->cooldown_minutes,
            ]);
            return 'suppress';
        }

        // Check once_per_breach: only trigger once until value recovers
        if ($rule->frequency === 'once_per_breach') {
            if ($rule->last_triggered_at && $rule->last_value_snapshot !== null) {
                // If the condition is still breached (value hasn't recovered), suppress
                if ($rule->evaluateCondition($triggerValue)) {
                    // Still breached — check if this is a new breach or continuation
                    $wasAlreadyBreached = $rule->evaluateCondition((float) $rule->last_value_snapshot);
                    if ($wasAlreadyBreached) {
                        return 'suppress';
                    }
                }
            }
            return 'dispatch';
        }

        return match ($rule->frequency) {
            'instant' => 'dispatch',
            'batched_15m', 'batched_1h', 'daily_digest' => 'batch',
            default => 'dispatch',
        };
    }

    /**
     * Add a notification to the appropriate batch.
     */
    public function addToBatch(
        AlertRule $rule,
        string $channel,
        array $notificationPayload,
        ?string $tenantId = null,
        ?string $userId = null,
    ): void {
        $batchType = $rule->frequency;
        $scheduledFor = $this->calculateNextBatchTime($batchType);

        // Atomic find-or-create to prevent race condition (unique constraint enforced at DB level)
        try {
            $batch = NotificationBatch::firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'batch_type' => $batchType,
                    'channel' => $channel,
                    'scheduled_for' => $scheduledFor,
                ],
                ['items' => [$notificationPayload]]
            );

            if (!$batch->wasRecentlyCreated) {
                $batch->addItem($notificationPayload);
            }
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Race condition hit — retry by finding the existing batch
            $batch = NotificationBatch::where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('batch_type', $batchType)
                ->where('channel', $channel)
                ->where('scheduled_for', $scheduledFor)
                ->first();

            $batch?->addItem($notificationPayload);
        }

        Log::debug('[AlertFrequency] Added to batch', [
            'batch_type' => $batchType,
            'channel' => $channel,
            'scheduled_for' => $scheduledFor->toIso8601String(),
        ]);
    }

    /**
     * Calculate the next batch dispatch time.
     */
    private function calculateNextBatchTime(string $batchType): \DateTime
    {
        $now = now();

        return match ($batchType) {
            'batched_15m' => $now->copy()->ceilMinute(15),
            'batched_1h' => $now->copy()->ceilHour(),
            'daily_digest' => $now->copy()->addDay()->setHour(
                config('alerting.batch.daily_digest_hour', 8)
            )->setMinute(0)->setSecond(0),
            default => $now->copy()->addMinutes(15),
        };
    }
}
