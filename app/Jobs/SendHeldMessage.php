<?php

namespace App\Jobs;

use App\Models\HeldMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendHeldMessage — dispatches a single non-campaign held message
 * back through the gateway after its out-of-hours hold expires.
 *
 * This handles API sends, email-to-SMS, and other non-campaign messages
 * that were held during the restricted window.
 */
class SendHeldMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        public readonly string $heldMessageId,
    ) {
        $this->onQueue('messages');
    }

    public function handle(): void
    {
        $message = HeldMessage::withoutGlobalScopes()->find($this->heldMessageId);

        if (!$message) {
            Log::warning('[SendHeldMessage] Held message not found', [
                'id' => $this->heldMessageId,
            ]);
            return;
        }

        if ($message->status !== 'released') {
            Log::info('[SendHeldMessage] Message not in released state, skipping', [
                'id' => $message->id,
                'status' => $message->status,
            ]);
            return;
        }

        try {
            // Re-check out-of-hours before sending
            $outOfHoursService = app(\App\Services\OutOfHoursService::class);
            $check = $outOfHoursService->check($message->tenant_id);

            if (!$check->allowed) {
                // Still in restricted hours — re-hold
                $message->update([
                    'status' => 'held',
                    'released_at' => null,
                    'release_after' => $check->releaseAfter,
                ]);
                Log::info('[SendHeldMessage] Re-held message, still in restricted hours', [
                    'id' => $message->id,
                ]);
                return;
            }

            // Gateway dispatch not yet implemented — mark as failed so the message
            // is not silently lost. Once a single-message send service exists,
            // wire it here based on $message->origin (api, email_to_sms, portal).
            $message->update(['status' => 'failed']);

            Log::error('[SendHeldMessage] Gateway dispatch not implemented — message marked as failed', [
                'id' => $message->id,
                'tenant_id' => $message->tenant_id,
                'recipient' => substr($message->recipient_number, 0, 6) . '***',
                'origin' => $message->origin,
            ]);

        } catch (\Throwable $e) {
            Log::error('[SendHeldMessage] Failed to send held message', [
                'id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            $message->update(['status' => 'expired']);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[SendHeldMessage] Job failed permanently', [
            'id' => $this->heldMessageId,
            'error' => $exception->getMessage(),
        ]);

        // Mark as expired to avoid infinite retries
        HeldMessage::withoutGlobalScopes()
            ->where('id', $this->heldMessageId)
            ->update(['status' => 'expired']);
    }
}
