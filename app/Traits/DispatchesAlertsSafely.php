<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Wraps alert event dispatch in try/catch so alerting failures
 * never block business logic or return errors to the user.
 */
trait DispatchesAlertsSafely
{
    /**
     * Dispatch an alert event safely — failures are logged, never thrown.
     *
     * @param string $triggerKey  Identifier for log messages (e.g. 'security_setting_changed')
     * @param string $eventClass  Fully-qualified event class name
     * @param array  $args        Constructor arguments for the event
     * @param array  $context     Extra key-value pairs for the log entry
     */
    private function safeDispatch(string $triggerKey, string $eventClass, array $args, array $context = []): void
    {
        try {
            $eventClass::dispatch(...$args);
            Log::info("Alert dispatched: {$triggerKey}", $context);
        } catch (\Throwable $e) {
            Log::warning("Alert dispatch failed: {$triggerKey}", array_merge($context, [
                'error' => $e->getMessage(),
            ]));
        }
    }
}
