<?php

namespace App\Services\Billing;

use App\Models\Account;
use App\Models\Billing\Payment;
use App\Models\Billing\AutoTopUpConfig;
use App\Models\Billing\FinancialAuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StripeCheckoutService
{
    public function __construct(
        private BalanceService $balanceService,
        private InvoiceService $invoiceService,
    ) {}

    /**
     * Create a Stripe Checkout Session for a prepay top-up.
     */
    public function createCheckoutSession(Account $account, string $amount, string $currency): array
    {
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->create([
            'mode' => 'payment',
            'currency' => strtolower($currency),
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($currency),
                    'product_data' => [
                        'name' => 'QuickSMS Account Top-Up',
                        'description' => "Top up your QuickSMS account with {$currency} {$amount}",
                    ],
                    'unit_amount' => (int)bcmul($amount, '100', 0), // Convert to pence/cents
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'account_id' => $account->id,
                'amount' => $amount,
                'currency' => $currency,
                'type' => 'top_up',
            ],
            'success_url' => config('app.url') . '/billing/top-up/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.url') . '/billing/top-up/cancel',
        ]);

        return [
            'session_id' => $session->id,
            'url' => $session->url,
        ];
    }

    /**
     * Handle Stripe checkout.session.completed webhook.
     */
    public function handleCheckoutCompleted(array $event): void
    {
        $session = $event['data']['object'];
        $metadata = $session['metadata'] ?? [];

        $accountId = $metadata['account_id'] ?? null;
        $amount = $metadata['amount'] ?? null;
        $currency = strtoupper($metadata['currency'] ?? 'GBP');
        $type = $metadata['type'] ?? 'top_up';

        if (!$accountId || !$amount) {
            Log::error('Stripe checkout webhook: missing metadata', ['session' => $session['id']]);
            return;
        }

        $idempotencyKey = "stripe-checkout-{$session['id']}";

        DB::transaction(function () use ($accountId, $amount, $currency, $idempotencyKey, $session, $type) {
            $account = Account::findOrFail($accountId);

            if ($type === 'top_up' && $account->billing_type === 'postpay') {
                // Postpay mid-month advance
                $this->balanceService->processPostpayAdvance(
                    $accountId, $amount, $currency, $idempotencyKey,
                    ['stripe_session' => $session['id']]
                );
            } else {
                // Standard prepay top-up
                $this->balanceService->processTopUp(
                    $accountId, $amount, $currency, $idempotencyKey,
                    'stripe_payment', $session['payment_intent'] ?? null,
                    ['stripe_session' => $session['id']]
                );
            }

            // Create payment record
            Payment::create([
                'account_id' => $accountId,
                'payment_method' => 'stripe_checkout',
                'stripe_checkout_session_id' => $session['id'],
                'stripe_payment_intent_id' => $session['payment_intent'] ?? null,
                'currency' => $currency,
                'amount' => $amount,
                'status' => 'succeeded',
                'paid_at' => now(),
            ]);

            // Create top-up invoice (paid immediately)
            $this->invoiceService->createTopUpInvoice($account, $amount, $currency, $session['id']);

            FinancialAuditLog::record(
                'stripe_top_up', 'account', $accountId,
                null, ['amount' => $amount, 'session' => $session['id']],
                null, 'webhook'
            );
        });
    }

    /**
     * Handle payment_intent.succeeded (for auto top-ups and DD).
     */
    public function handlePaymentIntentSucceeded(array $event): void
    {
        $paymentIntent = $event['data']['object'];
        $metadata = $paymentIntent['metadata'] ?? [];

        $accountId = $metadata['account_id'] ?? null;
        $type = $metadata['type'] ?? null;

        if (!$accountId) return;

        $amount = bcdiv((string)$paymentIntent['amount'], '100', 4);
        $currency = strtoupper($paymentIntent['currency']);
        $idempotencyKey = "stripe-pi-{$paymentIntent['id']}";

        if ($type === 'auto_topup') {
            DB::transaction(function () use ($accountId, $amount, $currency, $idempotencyKey, $paymentIntent) {
                $account = Account::findOrFail($accountId);

                $this->balanceService->processTopUp(
                    $accountId, $amount, $currency, $idempotencyKey,
                    'stripe_payment', $paymentIntent['id'],
                    ['auto_topup' => true]
                );

                Payment::create([
                    'account_id' => $accountId,
                    'payment_method' => 'stripe_auto_topup',
                    'stripe_payment_intent_id' => $paymentIntent['id'],
                    'currency' => $currency,
                    'amount' => $amount,
                    'status' => 'succeeded',
                    'paid_at' => now(),
                ]);

                $this->invoiceService->createTopUpInvoice($account, $amount, $currency);

                AutoTopUpConfig::where('account_id', $accountId)
                    ->update(['last_triggered_at' => now()]);
            });
        } elseif ($type === 'dd_collection') {
            // Direct Debit collection for postpay
            $invoiceId = $metadata['invoice_id'] ?? null;

            DB::transaction(function () use ($accountId, $amount, $currency, $idempotencyKey, $paymentIntent, $invoiceId) {
                $this->balanceService->processPostpayAdvance(
                    $accountId, $amount, $currency, $idempotencyKey,
                    ['stripe_pi' => $paymentIntent['id'], 'dd' => true]
                );

                Payment::create([
                    'account_id' => $accountId,
                    'invoice_id' => $invoiceId,
                    'payment_method' => 'stripe_dd',
                    'stripe_payment_intent_id' => $paymentIntent['id'],
                    'currency' => $currency,
                    'amount' => $amount,
                    'status' => 'succeeded',
                    'paid_at' => now(),
                ]);

                if ($invoiceId) {
                    $invoice = \App\Models\Billing\Invoice::find($invoiceId);
                    if ($invoice) {
                        $invoice->update([
                            'amount_paid' => $amount,
                            'amount_due' => '0',
                            'status' => 'paid',
                            'paid_date' => now()->toDateString(),
                        ]);
                    }
                }
            });
        }
    }

    /**
     * Trigger auto top-up if conditions are met.
     */
    public function checkAutoTopUp(string $accountId): void
    {
        $config = AutoTopUpConfig::where('account_id', $accountId)
            ->where('enabled', true)
            ->first();

        if (!$config) return;

        $balance = $this->balanceService->getBalance($accountId);

        if (bccomp($balance->effective_available, $config->threshold_amount, 4) >= 0) {
            return; // Balance above threshold
        }

        // Check daily limit
        $todayCount = Payment::where('account_id', $accountId)
            ->where('payment_method', 'stripe_auto_topup')
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= $config->max_topups_per_day) {
            Log::warning('Auto top-up daily limit reached', ['account' => $accountId]);
            return;
        }

        // Create off-session PaymentIntent
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            $stripe->paymentIntents->create([
                'amount' => (int)bcmul($config->topup_amount, '100', 0),
                'currency' => strtolower($balance->currency),
                'customer' => $config->stripe_customer_id,
                'payment_method' => $config->stripe_payment_method_id,
                'off_session' => true,
                'confirm' => true,
                'metadata' => [
                    'account_id' => $accountId,
                    'type' => 'auto_topup',
                    'amount' => $config->topup_amount,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Auto top-up failed', [
                'account' => $accountId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get or update auto top-up configuration.
     */
    public function getAutoTopUpConfig(string $accountId): ?AutoTopUpConfig
    {
        return AutoTopUpConfig::where('account_id', $accountId)->first();
    }

    public function updateAutoTopUpConfig(string $accountId, array $data): AutoTopUpConfig
    {
        return AutoTopUpConfig::updateOrCreate(
            ['account_id' => $accountId],
            $data
        );
    }
}
