<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Webhook Controller
 * 
 * SECURITY NOTES:
 * - Handles payment webhooks from HubSpot/Stripe
 * - PCI compliance: This controller NEVER handles card data
 * - Payment status received via secure webhook only
 * - All webhook events are logged for audit trail
 * - Stripe webhook signature verification enabled when STRIPE_WEBHOOK_SECRET is set
 */
class WebhookController extends Controller
{
    private StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        if (!$this->stripeService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Stripe webhook signature verification failed', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = $this->stripeService->parseWebhookEvent($payload);

        if (!$event) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        Log::info('Stripe webhook received', [
            'event_type' => $event['type'] ?? 'unknown',
            'event_id' => $event['id'] ?? null,
        ]);

        $eventType = $event['type'] ?? null;

        switch ($eventType) {
            case 'checkout.session.completed':
                $this->handleCheckoutCompleted($event['data']['object'] ?? []);
                break;

            case 'payment_intent.succeeded':
                $this->handlePaymentSucceeded($event['data']['object'] ?? []);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentFailed($event['data']['object'] ?? []);
                break;

            default:
                Log::info('Unhandled Stripe event type', ['type' => $eventType]);
        }

        return response()->json(['received' => true]);
    }

    private function handleCheckoutCompleted(array $session): void
    {
        $metadata = $session['metadata'] ?? [];
        $type = $metadata['type'] ?? null;

        Log::info('Checkout session completed', [
            'session_id' => $session['id'] ?? null,
            'type' => $type,
            'metadata' => $metadata,
            'payment_status' => $session['payment_status'] ?? null,
        ]);

        if ($session['payment_status'] !== 'paid') {
            return;
        }

        if ($type === 'invoice_payment') {
            $invoiceId = $metadata['invoice_id'] ?? null;
            $invoiceNumber = $metadata['invoice_number'] ?? null;
            $amount = ($session['amount_total'] ?? 0) / 100;
            $currency = strtoupper($session['currency'] ?? 'gbp');

            $this->logAudit('payment_successful', [
                'payment_type' => 'invoice',
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoiceNumber,
                'amount' => $amount,
                'currency' => $currency,
                'session_id' => $session['id'] ?? null,
                'payment_intent' => $session['payment_intent'] ?? null,
                'customer_email' => $session['customer_details']['email'] ?? null,
            ]);

            Cache::put("invoice_paid_{$invoiceId}", true, now()->addHours(24));
        } elseif ($type === 'balance_topup') {
            $accountId = $metadata['account_id'] ?? 'demo_account';
            $creditAmount = (float) ($metadata['credit_amount'] ?? 0);
            $tier = $metadata['tier'] ?? 'starter';
            $totalPaid = ($session['amount_total'] ?? 0) / 100;
            $currency = strtoupper($session['currency'] ?? 'gbp');

            $this->logAudit('topup_successful', [
                'payment_type' => 'balance_topup',
                'account_id' => $accountId,
                'credit_amount' => $creditAmount,
                'tier' => $tier,
                'total_paid' => $totalPaid,
                'currency' => $currency,
                'session_id' => $session['id'] ?? null,
                'payment_intent' => $session['payment_intent'] ?? null,
                'customer_email' => $session['customer_details']['email'] ?? null,
            ]);

            $this->updateAccountBalance($accountId, $creditAmount);
            $this->notifyPaymentSuccess($accountId, null, $creditAmount);
        }
    }

    private function handlePaymentSucceeded(array $paymentIntent): void
    {
        Log::info('Payment intent succeeded', [
            'payment_intent_id' => $paymentIntent['id'] ?? null,
            'amount' => ($paymentIntent['amount'] ?? 0) / 100,
        ]);
    }

    private function handlePaymentFailed(array $paymentIntent): void
    {
        $this->logAudit('payment_failed', [
            'payment_intent_id' => $paymentIntent['id'] ?? null,
            'amount' => ($paymentIntent['amount'] ?? 0) / 100,
            'currency' => strtoupper($paymentIntent['currency'] ?? 'gbp'),
            'error' => $paymentIntent['last_payment_error']['message'] ?? 'Unknown error',
            'error_code' => $paymentIntent['last_payment_error']['code'] ?? null,
        ]);
    }

    private function logAudit(string $action, array $data): void
    {
        Log::channel('single')->info('[AUDIT] ' . strtoupper($action), array_merge([
            'action' => $action,
            'source' => 'stripe_webhook',
            'timestamp' => now()->toIso8601String(),
        ], $data));
    }

    public function hubspotPayment(Request $request): JsonResponse
    {
        $payload = $request->all();
        
        // Comprehensive audit log for all webhook events
        Log::info('HubSpot payment webhook received', [
            'action' => 'webhook_received',
            'source' => 'hubspot',
            'payload' => $payload,
            'ip' => $request->ip(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // TODO: Verify webhook signature from HubSpot
        // $signature = $request->header('X-HubSpot-Signature');
        
        $eventType = $payload['eventType'] ?? $payload['subscriptionType'] ?? null;
        
        if ($eventType === 'invoice.paid' || $eventType === 'deal.propertyChange') {
            $invoiceId = $payload['objectId'] ?? $payload['invoiceId'] ?? null;
            $accountId = $payload['properties']['hs_external_account_id'] ?? null;
            $amount = $payload['properties']['amount'] ?? 0;
            
            if ($accountId) {
                $this->updateAccountBalance($accountId, $amount);
                $this->notifyPaymentSuccess($accountId, $invoiceId, $amount);
                
                // Audit log: Successful payment
                Log::info('Payment processed successfully', [
                    'action' => 'payment_success',
                    'account_id' => $accountId,
                    'invoice_id' => $invoiceId,
                    'amount' => $amount,
                    'currency' => $payload['properties']['currency'] ?? 'GBP',
                    'timestamp' => now()->toIso8601String(),
                ]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Webhook processed']);
    }

    public function checkPaymentStatus(Request $request): JsonResponse
    {
        $accountId = $request->get('account_id', 'ACC-001');
        
        $paymentStatus = Cache::get("payment_status_{$accountId}");
        
        if ($paymentStatus) {
            Cache::forget("payment_status_{$accountId}");
            
            return response()->json([
                'success' => true,
                'payment_completed' => true,
                'message' => $paymentStatus['message'] ?? 'Payment successful',
                'amount' => $paymentStatus['amount'] ?? 0,
                'new_balance' => $paymentStatus['new_balance'] ?? 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'payment_completed' => false,
        ]);
    }

    public function getAccountBalance(Request $request): JsonResponse
    {
        $accountId = $request->get('account_id', 'ACC-001');
        
        // TODO: Replace with actual database query
        $balance = Cache::get("account_balance_{$accountId}", 0);
        
        return response()->json([
            'success' => true,
            'account_id' => $accountId,
            'balance' => $balance,
            'currency' => 'GBP',
        ]);
    }

    private function updateAccountBalance(string $accountId, float $amount): void
    {
        // TODO: Replace with actual database update for production
        // Using Cache as placeholder - must be replaced with persistent storage
        $currentBalance = Cache::get("account_balance_{$accountId}", 0);
        $newBalance = $currentBalance + $amount;
        Cache::put("account_balance_{$accountId}", $newBalance, now()->addDays(30));
        
        // Audit log: Balance update
        Log::info('Account balance updated', [
            'action' => 'balance_update',
            'account_id' => $accountId,
            'previous_balance' => $currentBalance,
            'amount_added' => $amount,
            'new_balance' => $newBalance,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    private function notifyPaymentSuccess(string $accountId, ?string $invoiceId, float $amount): void
    {
        $newBalance = Cache::get("account_balance_{$accountId}", 0);
        
        Cache::put("payment_status_{$accountId}", [
            'status' => 'success',
            'message' => 'Payment successful. Your balance has been updated.',
            'invoice_id' => $invoiceId,
            'amount' => $amount,
            'new_balance' => $newBalance,
            'timestamp' => now()->toIso8601String(),
        ], now()->addMinutes(10));
    }
}
