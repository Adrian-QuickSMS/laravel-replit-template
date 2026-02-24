<?php

namespace App\Jobs;

use App\Services\Campaign\DeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * HandleDeliveryReceipt — processes a delivery receipt (DLR) from the gateway.
 *
 * Gateway webhook controllers dispatch this job when they receive a DLR callback.
 * Processes asynchronously to avoid blocking the webhook response.
 *
 * Updates:
 * - CampaignRecipient status (delivered / undeliverable / failed)
 * - Campaign counters (delivered_count, failed_count, etc.)
 * - MessageLog status and timestamps
 * - Triggers retry scheduling for retryable failures
 *
 * Queue: 'dlr' (dedicated queue for delivery receipts, separate from send queue)
 */
class HandleDeliveryReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 30;

    public function __construct(
        /** Gateway-assigned message ID for correlation */
        public readonly string $gatewayMessageId,
        /** Delivery status from gateway (delivered, failed, undeliverable, etc.) */
        public readonly string $status,
        /** The channel that actually delivered (sms, rcs_basic, rcs_single) - for fallback tracking */
        public readonly ?string $deliveredChannel = null,
        /** Gateway error code if failed */
        public readonly ?string $errorCode = null,
        /** Gateway error message if failed */
        public readonly ?string $errorMessage = null,
        /** Raw DLR payload from gateway for debugging */
        public readonly array $rawPayload = [],
    ) {
        $this->onQueue('dlr');
    }

    public function handle(DeliveryService $deliveryService): void
    {
        Log::info('[HandleDeliveryReceipt] Processing DLR', [
            'gateway_message_id' => $this->gatewayMessageId,
            'status' => $this->status,
            'channel' => $this->deliveredChannel,
        ]);

        $processed = $deliveryService->processDeliveryReceipt(
            $this->gatewayMessageId,
            $this->status,
            $this->deliveredChannel,
            $this->errorCode,
            $this->errorMessage
        );

        if (!$processed) {
            Log::warning('[HandleDeliveryReceipt] Could not match DLR to recipient', [
                'gateway_message_id' => $this->gatewayMessageId,
                'status' => $this->status,
            ]);
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('[HandleDeliveryReceipt] Job failed', [
            'gateway_message_id' => $this->gatewayMessageId,
            'status' => $this->status,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Calculate backoff in seconds between retries.
     */
    public function backoff(): array
    {
        return [5, 15, 30, 60, 120];
    }
}
