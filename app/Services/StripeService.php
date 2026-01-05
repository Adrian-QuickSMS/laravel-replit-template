<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class StripeService
{
    private ?string $secretKey;
    private ?string $webhookSecret;
    private bool $isConfigured;

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret') ?: env('STRIPE_SECRET_KEY');
        $this->webhookSecret = env('STRIPE_WEBHOOK_SECRET');
        $this->isConfigured = !empty($this->secretKey);
    }

    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    public function createInvoicePaymentSession(array $invoiceData): array
    {
        $invoiceId = $invoiceData['id'];
        $invoiceNumber = $invoiceData['invoiceNumber'];
        $amount = $invoiceData['balanceDue'];
        $currency = strtolower($invoiceData['currency'] ?? 'gbp');

        $successUrl = route('reporting.invoices') . '?payment=success&invoice=' . $invoiceId . '&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('reporting.invoices') . '?payment=cancelled&invoice=' . $invoiceId;

        if (!$this->isConfigured) {
            Log::info('Stripe not configured - returning mock checkout URL', [
                'invoice_id' => $invoiceId,
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'checkoutUrl' => $successUrl,
                'sessionId' => 'mock_session_' . uniqid(),
                'isMock' => true,
            ];
        }

        try {
            \Stripe\Stripe::setApiKey($this->secretKey);

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Invoice ' . $invoiceNumber,
                            'description' => 'Payment for invoice ' . $invoiceNumber,
                        ],
                        'unit_amount' => (int) round($amount * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'type' => 'invoice_payment',
                    'invoice_id' => $invoiceId,
                    'invoice_number' => $invoiceNumber,
                ],
            ]);

            Log::info('Stripe Checkout session created for invoice payment', [
                'session_id' => $session->id,
                'invoice_id' => $invoiceId,
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'checkoutUrl' => $session->url,
                'sessionId' => $session->id,
                'isMock' => false,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create Stripe session for invoice payment', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create payment session: ' . $e->getMessage(),
            ];
        }
    }

    public function createTopUpSession(array $topUpData): array
    {
        $tier = $topUpData['tier'];
        $creditAmount = $topUpData['amount'];
        $vatRate = $topUpData['vatRate'] ?? 0.20;
        $currency = strtolower($topUpData['currency'] ?? 'gbp');
        $accountId = $topUpData['accountId'] ?? 'demo_account';

        $vatAmount = $creditAmount * $vatRate;
        $totalAmount = $creditAmount + $vatAmount;

        $tierLabel = $tier === 'bespoke' ? 'Custom Contract' : ucfirst($tier);

        $successUrl = route('reporting.invoices') . '?topup=success&amount=' . $creditAmount . '&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = route('reporting.invoices') . '?topup=cancelled';

        if (!$this->isConfigured) {
            Log::info('Stripe not configured - returning mock checkout URL for top-up', [
                'tier' => $tier,
                'amount' => $creditAmount,
                'total' => $totalAmount,
            ]);

            return [
                'success' => true,
                'checkoutUrl' => $successUrl,
                'sessionId' => 'mock_topup_' . uniqid(),
                'isMock' => true,
            ];
        }

        try {
            \Stripe\Stripe::setApiKey($this->secretKey);

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $currency,
                            'product_data' => [
                                'name' => 'Account Credit Top-Up (' . $tierLabel . ')',
                                'description' => 'Add £' . number_format($creditAmount, 2) . ' credit to your account',
                            ],
                            'unit_amount' => (int) round($creditAmount * 100),
                        ],
                        'quantity' => 1,
                    ],
                    [
                        'price_data' => [
                            'currency' => $currency,
                            'product_data' => [
                                'name' => 'VAT (20%)',
                            ],
                            'unit_amount' => (int) round($vatAmount * 100),
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'type' => 'balance_topup',
                    'tier' => $tier,
                    'credit_amount' => $creditAmount,
                    'vat_amount' => $vatAmount,
                    'account_id' => $accountId,
                ],
            ]);

            Log::info('Stripe Checkout session created for top-up', [
                'session_id' => $session->id,
                'tier' => $tier,
                'amount' => $creditAmount,
                'total' => $totalAmount,
            ]);

            return [
                'success' => true,
                'checkoutUrl' => $session->url,
                'sessionId' => $session->id,
                'isMock' => false,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create Stripe session for top-up', [
                'tier' => $tier,
                'amount' => $creditAmount,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create payment session: ' . $e->getMessage(),
            ];
        }
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (!$this->webhookSecret) {
            Log::warning('Stripe webhook secret not configured - skipping signature verification');
            return true;
        }

        try {
            \Stripe\Stripe::setApiKey($this->secretKey);
            \Stripe\Webhook::constructEvent($payload, $signature, $this->webhookSecret);
            return true;
        } catch (\Exception $e) {
            Log::error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function parseWebhookEvent(string $payload): ?array
    {
        try {
            $data = json_decode($payload, true);
            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }
}
