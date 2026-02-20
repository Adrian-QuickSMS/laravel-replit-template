<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Billing\StripeCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(
        private StripeCheckoutService $stripeService,
    ) {}

    public function handle(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        // Idempotency check
        if (DB::table('processed_stripe_events')->where('event_id', $event->id)->exists()) {
            return response('Already processed', 200);
        }

        try {
            match ($event->type) {
                'checkout.session.completed' => $this->stripeService->handleCheckoutCompleted($event->toArray()),
                'payment_intent.succeeded' => $this->stripeService->handlePaymentIntentSucceeded($event->toArray()),
                'payment_intent.payment_failed' => $this->handlePaymentFailed($event->toArray()),
                default => Log::info('Unhandled Stripe event', ['type' => $event->type]),
            };

            DB::table('processed_stripe_events')->insert([
                'event_id' => $event->id,
                'event_type' => $event->type,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'event_type' => $event->type,
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
            return response('Processing failed', 500);
        }

        return response('OK', 200);
    }

    private function handlePaymentFailed(array $event): void
    {
        $pi = $event['data']['object'];
        $accountId = $pi['metadata']['account_id'] ?? null;

        Log::warning('Stripe payment failed', [
            'payment_intent' => $pi['id'],
            'account_id' => $accountId,
        ]);

        // Notification dispatched here in production
    }
}
