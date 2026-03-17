<?php

namespace App\Jobs\Alerting;

use App\Contracts\AlertableEvent;
use App\Services\Alerting\AlertEvaluatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queued job that evaluates an AlertableEvent against all matching rules.
 *
 * This job is dispatched by the AlertEventSubscriber whenever a domain event
 * implementing AlertableEvent is fired. Processing happens asynchronously
 * to avoid blocking the originating service.
 */
class EvaluateAlertEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public readonly AlertableEvent $event,
    ) {
        $this->onQueue(config('alerting.queue.evaluation', 'alerts'));
    }

    public function handle(AlertEvaluatorService $evaluator): void
    {
        Log::debug('[EvaluateAlertEvent] Processing event', [
            'trigger_key' => $this->event->getTriggerKey(),
            'trigger_value' => $this->event->getTriggerValue(),
            'tenant_id' => $this->event->getTenantId(),
            'category' => $this->event->getCategory(),
        ]);

        $evaluator->evaluate($this->event);
    }

    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[EvaluateAlertEvent] Job failed', [
            'trigger_key' => $this->event->getTriggerKey(),
            'tenant_id' => $this->event->getTenantId(),
            'error' => $exception->getMessage(),
        ]);
    }
}
