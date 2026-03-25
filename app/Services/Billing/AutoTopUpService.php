<?php

namespace App\Services\Billing;

use App\Models\Account;
use App\Models\Billing\AutoTopUpConfig;
use App\Models\Billing\AutoTopUpEvent;
use App\Models\Billing\FinancialAuditLog;
use App\Models\Billing\Payment;
use App\Jobs\ProcessAutoTopUpJob;
use App\Jobs\RetryAutoTopUpJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutoTopUpService
{
    public function __construct(
        private BalanceService $balanceService,
        private InvoiceService $invoiceService,
        private AutoTopUpNotificationService $notificationService,
    ) {}

    /**
     * Evaluate whether an auto top-up should be triggered.
     * Called from BalanceService::deductForMessage() after balance update.
     * Must NOT throw — failures here must never block message sending.
     */
    public function evaluateAutoTopUp(string $accountId, string $previousBalance, string $currentBalance): void
    {
        try {
            $config = AutoTopUpConfig::where('account_id', $accountId)->first();

            if (!$config || !$config->canTrigger()) {
                return;
            }

            // Check the account is prepay
            $account = Account::find($accountId);
            if (!$account || $account->billing_type !== 'prepay') {
                return;
            }

            $threshold = $config->threshold_amount;

            // Threshold-crossing: was above (or at), now below
            if (bccomp($previousBalance, $threshold, 4) >= 0 && bccomp($currentBalance, $threshold, 4) < 0) {
                $this->triggerAutoTopUp($accountId, $account, $config, $currentBalance);
            }
        } catch (\Throwable $e) {
            Log::error('Auto top-up evaluation failed', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Determine VAT rate for an account using the same logic as InvoiceService.
     * Rule: VAT-registered AND NOT reverse-charge eligible → 20%. Otherwise → 0%.
     */
    private function getVatRateForAccount(Account $account): string
    {
        if ($account->vat_registered && !$account->vat_reverse_charges) {
            return config('billing.vat.uk_rate', '20.00');
        }
        return config('billing.vat.default_rate', '0.00');
    }

    /**
     * Trigger an auto top-up: validate conditions, create event, dispatch job.
     */
    private function triggerAutoTopUp(string $accountId, Account $account, AutoTopUpConfig $config, string $currentBalance): void
    {
        // Check daily limits
        $dailyStats = $this->getDailyStats($accountId);

        if ($dailyStats['count'] >= $config->max_topups_per_day) {
            Log::info('Auto top-up daily count limit reached', ['account' => $accountId]);
            return;
        }

        if ($config->daily_topup_cap !== null
            && bccomp(bcadd($dailyStats['value'], $config->topup_amount, 4), $config->daily_topup_cap, 4) > 0) {
            Log::info('Auto top-up daily value cap would be exceeded', ['account' => $accountId]);
            return;
        }

        // Check cooldown
        if ($config->min_minutes_between_topups > 0 && $config->last_triggered_at) {
            $cooldownEnd = $config->last_triggered_at->addMinutes($config->min_minutes_between_topups);
            if (now()->lt($cooldownEnd)) {
                Log::info('Auto top-up cooldown active', ['account' => $accountId, 'cooldown_until' => $cooldownEnd]);
                return;
            }
        }

        // Check no pending/processing event exists
        $hasPending = AutoTopUpEvent::forAccount($accountId)
            ->pending()
            ->exists();

        if ($hasPending) {
            Log::info('Auto top-up already pending for account', ['account' => $accountId]);
            return;
        }

        // Acquire PG transaction-level advisory lock (auto-releases on transaction end)
        $lockKey = crc32("auto_topup_{$accountId}");

        DB::transaction(function () use ($accountId, $account, $config, $currentBalance, $dailyStats, $lockKey) {
            $lockAcquired = DB::selectOne("SELECT pg_try_advisory_xact_lock(?) as locked", [$lockKey]);

            if (!$lockAcquired || !$lockAcquired->locked) {
                Log::info('Auto top-up lock not acquired, another process handling', ['account' => $accountId]);
                return;
            }

            // Re-check pending inside lock to prevent TOCTOU
            $hasPending = AutoTopUpEvent::forAccount($accountId)->pending()->exists();
            if ($hasPending) {
                return;
            }

            $vatRate = $this->getVatRateForAccount($account);
            $vatAmount = bcmul($config->topup_amount, bcdiv($vatRate, '100', 6), 4);
            $totalCharge = bcadd($config->topup_amount, $vatAmount, 4);

            $idempotencyKey = 'auto-topup-' . $accountId . '-' . now()->utc()->format('Y-m-d') . '-' . Str::random(8);

            $event = AutoTopUpEvent::create([
                'account_id' => $accountId,
                'config_id' => $config->id,
                'event_type' => AutoTopUpEvent::TYPE_TRIGGERED,
                'status' => AutoTopUpEvent::STATUS_PENDING,
                'trigger_balance' => $currentBalance,
                'trigger_threshold' => $config->threshold_amount,
                'topup_amount' => $config->topup_amount,
                'vat_amount' => $vatAmount,
                'total_charge_amount' => $totalCharge,
                'daily_count_before' => $dailyStats['count'],
                'daily_value_before' => $dailyStats['value'],
                'stripe_customer_id' => $config->stripe_customer_id,
                'stripe_payment_method_id' => $config->stripe_payment_method_id,
                'idempotency_key' => $idempotencyKey,
            ]);

            $config->update(['last_triggered_at' => now()]);

            ProcessAutoTopUpJob::dispatch($event->id);
        });
    }

    /**
     * Process a triggered auto top-up event. Called by ProcessAutoTopUpJob.
     */
    public function processAutoTopUp(string $eventId): void
    {
        $event = AutoTopUpEvent::findOrFail($eventId);

        if ($event->status !== AutoTopUpEvent::STATUS_PENDING) {
            Log::info('Auto top-up event already processed', ['event_id' => $eventId, 'status' => $event->status]);
            return;
        }

        $config = AutoTopUpConfig::find($event->config_id);
        if (!$config || !$config->canTrigger()) {
            $event->update([
                'status' => AutoTopUpEvent::STATUS_CANCELLED,
                'event_type' => AutoTopUpEvent::TYPE_PAYMENT_FAILED,
                'failure_message' => 'Auto top-up config no longer valid',
                'completed_at' => now(),
            ]);
            return;
        }

        // Re-validate daily limits (guard against race conditions)
        $dailyStats = $this->getDailyStats($event->account_id);
        if ($dailyStats['count'] >= $config->max_topups_per_day) {
            $event->update([
                'status' => AutoTopUpEvent::STATUS_CANCELLED,
                'failure_message' => 'Daily top-up count limit reached',
                'completed_at' => now(),
            ]);
            return;
        }

        $event->update([
            'status' => AutoTopUpEvent::STATUS_PROCESSING,
            'event_type' => AutoTopUpEvent::TYPE_PAYMENT_INITIATED,
            'processed_at' => now(),
        ]);

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => (int) bcmul($event->total_charge_amount, '100', 0),
                'currency' => 'gbp',
                'customer' => $config->stripe_customer_id,
                'payment_method' => $config->stripe_payment_method_id,
                'off_session' => true,
                'confirm' => true,
                'metadata' => [
                    'account_id' => $event->account_id,
                    'type' => 'auto_topup',
                    'event_id' => $event->id,
                    'net_amount' => $event->topup_amount,
                    'vat_amount' => $event->vat_amount,
                ],
            ], [
                'idempotency_key' => $event->idempotency_key,
            ]);

            $event->update([
                'stripe_payment_intent_id' => $paymentIntent->id,
            ]);

            if ($paymentIntent->status === 'succeeded') {
                // Credit will happen via webhook (payment_intent.succeeded)
                // Event stays in 'processing' until webhook arrives
                Log::info('Auto top-up PaymentIntent succeeded synchronously, awaiting webhook', [
                    'event_id' => $event->id,
                    'pi_id' => $paymentIntent->id,
                ]);
            } elseif ($paymentIntent->status === 'requires_action') {
                $this->handleRequiresAction($event, $config, $paymentIntent);
            }
        } catch (\Stripe\Exception\CardException $e) {
            $this->handlePaymentError($event, $config, $e->getStripeCode() ?? 'card_error', $e->getMessage());
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $this->handlePaymentError($event, $config, 'invalid_request', $e->getMessage());
        } catch (\Exception $e) {
            $this->handlePaymentError($event, $config, 'system_error', $e->getMessage());
        }
    }

    /**
     * Handle successful payment (called from webhook).
     */
    public function handlePaymentSuccess(string $paymentIntentId, array $eventData): void
    {
        $event = AutoTopUpEvent::where('stripe_payment_intent_id', $paymentIntentId)->first();

        // Fallback: if webhook arrives before job has written the PI ID to the event,
        // look up by event_id stored in PaymentIntent metadata
        if (!$event) {
            $metadataEventId = $eventData['data']['object']['metadata']['event_id'] ?? null;
            if ($metadataEventId) {
                $event = AutoTopUpEvent::find($metadataEventId);
                if ($event) {
                    // Backfill the PI ID that the job hasn't written yet
                    $event->update(['stripe_payment_intent_id' => $paymentIntentId]);
                }
            }
        }

        if (!$event) {
            Log::warning('Auto top-up event not found for PI', ['pi_id' => $paymentIntentId]);
            return;
        }

        if ($event->status === AutoTopUpEvent::STATUS_SUCCEEDED) {
            return; // Already processed (idempotent)
        }

        $idempotencyKey = "stripe-pi-{$paymentIntentId}";

        DB::transaction(function () use ($event, $idempotencyKey, $paymentIntentId) {
            $account = Account::findOrFail($event->account_id);

            // Credit balance with net amount (not VAT)
            $this->balanceService->processTopUp(
                $event->account_id,
                $event->topup_amount,
                'GBP',
                $idempotencyKey,
                'stripe_payment',
                $paymentIntentId,
                ['auto_topup' => true, 'event_id' => $event->id]
            );

            // Create payment record
            Payment::create([
                'account_id' => $event->account_id,
                'payment_method' => 'stripe_auto_topup',
                'stripe_payment_intent_id' => $paymentIntentId,
                'currency' => 'GBP',
                'amount' => $event->total_charge_amount,
                'status' => 'succeeded',
                'paid_at' => now(),
                'metadata' => [
                    'net_amount' => $event->topup_amount,
                    'vat_amount' => $event->vat_amount,
                    'auto_topup_event_id' => $event->id,
                ],
            ]);

            // Create top-up invoice
            $this->invoiceService->createTopUpInvoice($account, $event->topup_amount, 'GBP');

            // Update event
            $event->update([
                'event_type' => AutoTopUpEvent::TYPE_PAYMENT_SUCCEEDED,
                'status' => AutoTopUpEvent::STATUS_SUCCEEDED,
                'completed_at' => now(),
            ]);

            // Reset failure count on config
            $config = AutoTopUpConfig::find($event->config_id);
            if ($config) {
                $config->resetFailureCount();
            }
        });

        // Notifications (outside transaction)
        try {
            $config = AutoTopUpConfig::find($event->config_id);
            if ($config) {
                $this->notificationService->notifySuccess($config, $event);
            }
        } catch (\Throwable $e) {
            Log::error('Auto top-up success notification failed', ['event_id' => $event->id, 'error' => $e->getMessage()]);
        }

        try {
            FinancialAuditLog::record(
                'auto_topup_succeeded', 'auto_topup_event', $event->id,
                null,
                ['amount' => $event->topup_amount, 'total_charge' => $event->total_charge_amount, 'pi_id' => $paymentIntentId],
                null, 'webhook'
            );
        } catch (\Throwable $e) {
            Log::error('Auto top-up audit log failed', ['event_id' => $event->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Handle failed payment (called from webhook).
     */
    public function handlePaymentFailure(string $paymentIntentId, array $eventData): void
    {
        $event = AutoTopUpEvent::where('stripe_payment_intent_id', $paymentIntentId)->first();

        // Fallback lookup by event_id in PI metadata (webhook ordering race)
        if (!$event) {
            $metadataEventId = $eventData['data']['object']['metadata']['event_id'] ?? null;
            if ($metadataEventId) {
                $event = AutoTopUpEvent::find($metadataEventId);
                if ($event) {
                    $event->update(['stripe_payment_intent_id' => $paymentIntentId]);
                }
            }
        }

        if (!$event || in_array($event->status, [AutoTopUpEvent::STATUS_FAILED, AutoTopUpEvent::STATUS_SUCCEEDED])) {
            return;
        }

        $failureCode = $eventData['data']['object']['last_payment_error']['code'] ?? 'unknown';
        $failureMessage = $eventData['data']['object']['last_payment_error']['message'] ?? 'Payment failed';

        $event->update([
            'event_type' => AutoTopUpEvent::TYPE_PAYMENT_FAILED,
            'status' => AutoTopUpEvent::STATUS_FAILED,
            'failure_code' => $failureCode,
            'failure_message' => $failureMessage,
            'completed_at' => now(),
        ]);

        $config = AutoTopUpConfig::find($event->config_id);
        if ($config) {
            $this->processFailureAndMaybeDisable($event, $config);
        }

        try {
            FinancialAuditLog::record(
                'auto_topup_failed', 'auto_topup_event', $event->id,
                null,
                ['failure_code' => $failureCode, 'failure_message' => $failureMessage, 'pi_id' => $paymentIntentId],
                null, 'webhook'
            );
        } catch (\Throwable $e) {
            Log::error('Auto top-up failure audit log failed', ['event_id' => $event->id]);
        }
    }

    /**
     * Handle requires_action from synchronous PaymentIntent response.
     * Does NOT store client_secret in DB — it is retrieved from Stripe when needed.
     */
    private function handleRequiresAction(AutoTopUpEvent $event, AutoTopUpConfig $config, $paymentIntent): void
    {
        $actionUrl = config('app.url') . '/payments/auto-topup/complete-action/' . $event->id;

        $event->update([
            'event_type' => AutoTopUpEvent::TYPE_REQUIRES_ACTION,
            'status' => AutoTopUpEvent::STATUS_REQUIRES_ACTION,
            'requires_action_url' => $actionUrl,
        ]);

        try {
            $this->notificationService->notifyRequiresAction($config, $event);
        } catch (\Throwable $e) {
            Log::error('Auto top-up requires_action notification failed', ['event_id' => $event->id]);
        }
    }

    /**
     * Handle payment error from synchronous Stripe call.
     */
    private function handlePaymentError(AutoTopUpEvent $event, AutoTopUpConfig $config, string $code, string $message): void
    {
        // Guard: if already processed (e.g., webhook arrived first), skip
        $event->refresh();
        if (in_array($event->status, [AutoTopUpEvent::STATUS_FAILED, AutoTopUpEvent::STATUS_SUCCEEDED])) {
            return;
        }

        Log::error('Auto top-up payment error', [
            'event_id' => $event->id,
            'account_id' => $event->account_id,
            'code' => $code,
            'message' => $message,
        ]);

        $event->update([
            'event_type' => AutoTopUpEvent::TYPE_PAYMENT_FAILED,
            'status' => AutoTopUpEvent::STATUS_FAILED,
            'failure_code' => $code,
            'failure_message' => $message,
            'completed_at' => now(),
        ]);

        $this->processFailureAndMaybeDisable($event, $config);

        try {
            FinancialAuditLog::record(
                'auto_topup_failed', 'auto_topup_event', $event->id,
                null,
                ['failure_code' => $code, 'failure_message' => $message],
                null, 'system'
            );
        } catch (\Throwable $e) {
            Log::error('Auto top-up failure audit log failed', ['event_id' => $event->id]);
        }
    }

    /**
     * Shared logic: increment failure count, auto-disable if threshold met,
     * schedule retry if applicable, and send appropriate notification.
     * Sends EITHER failure notification OR auto-disabled notification, never both.
     */
    private function processFailureAndMaybeDisable(AutoTopUpEvent $event, AutoTopUpConfig $config): void
    {
        $config->incrementFailureCount();

        if ($config->shouldAutoDisable()) {
            $config->update(['enabled' => false]);

            AutoTopUpEvent::create([
                'account_id' => $event->account_id,
                'config_id' => $config->id,
                'event_type' => AutoTopUpEvent::TYPE_AUTO_DISABLED,
                'status' => AutoTopUpEvent::STATUS_SUCCEEDED,
                'idempotency_key' => 'auto-disable-' . $event->account_id . '-' . Str::random(12),
                'metadata' => ['reason' => 'consecutive_failures', 'failure_count' => $config->consecutive_failure_count],
                'completed_at' => now(),
            ]);

            // Only send auto-disabled notification (not failure notification)
            $this->notificationService->notifyAutoDisabled($config);
        } else {
            $this->scheduleRetryIfApplicable($event, $config);
            // Only send failure notification when NOT auto-disabling
            $this->notificationService->notifyFailure($config, $event);
        }
    }

    /**
     * Schedule a retry if retry policy permits.
     */
    private function scheduleRetryIfApplicable(AutoTopUpEvent $event, AutoTopUpConfig $config): void
    {
        // Don't retry hard declines
        $hardDeclines = ['card_not_supported', 'expired_card', 'fraudulent', 'lost_card', 'stolen_card'];
        if (in_array($event->failure_code, $hardDeclines)) {
            return;
        }

        if ($event->retry_count >= $config->retry_attempts) {
            return;
        }

        $retryEvent = AutoTopUpEvent::create([
            'account_id' => $event->account_id,
            'config_id' => $config->id,
            'event_type' => AutoTopUpEvent::TYPE_RETRY_SCHEDULED,
            'status' => AutoTopUpEvent::STATUS_PENDING,
            'trigger_balance' => $event->trigger_balance,
            'trigger_threshold' => $event->trigger_threshold,
            'topup_amount' => $event->topup_amount,
            'vat_amount' => $event->vat_amount,
            'total_charge_amount' => $event->total_charge_amount,
            'stripe_customer_id' => $config->stripe_customer_id,
            'stripe_payment_method_id' => $config->stripe_payment_method_id,
            'retry_of_event_id' => $event->id,
            'retry_count' => $event->retry_count + 1,
            'idempotency_key' => 'auto-topup-retry-' . $event->id . '-' . ($event->retry_count + 1),
        ]);

        RetryAutoTopUpJob::dispatch($retryEvent->id)
            ->delay(now()->addMinutes($config->retry_delay_minutes));
    }

    /**
     * Get daily stats for auto top-ups (UTC day boundary).
     */
    public function getDailyStats(string $accountId): array
    {
        $today = now()->utc()->startOfDay();

        $stats = AutoTopUpEvent::forAccount($accountId)
            ->where('created_at', '>=', $today)
            ->where('status', AutoTopUpEvent::STATUS_SUCCEEDED)
            ->whereIn('event_type', [AutoTopUpEvent::TYPE_PAYMENT_SUCCEEDED])
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(topup_amount), 0) as value')
            ->first();

        return [
            'count' => (int) ($stats->count ?? 0),
            'value' => $stats->value ?? '0.0000',
        ];
    }

    /**
     * Create a Stripe Checkout Session in setup mode for saving a payment method.
     */
    public function setupPaymentMethod(string $accountId, string $userId): array
    {
        $account = Account::findOrFail($accountId);
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        // Create or retrieve Stripe Customer
        if (empty($account->stripe_customer_id)) {
            $customer = $stripe->customers->create([
                'metadata' => [
                    'quicksms_account_id' => $accountId,
                    'company_name' => $account->company_name,
                ],
            ]);
            $account->update(['stripe_customer_id' => $customer->id]);
        }

        $session = $stripe->checkout->sessions->create([
            'mode' => 'setup',
            'customer' => $account->stripe_customer_id,
            'currency' => 'gbp',
            'metadata' => [
                'account_id' => $accountId,
                'user_id' => $userId,
                'type' => 'auto_topup_setup',
            ],
            'success_url' => config('app.url') . '/payments/auto-topup?setup=success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.url') . '/payments/auto-topup?setup=cancelled',
        ]);

        return [
            'session_id' => $session->id,
            'url' => $session->url,
        ];
    }

    /**
     * Handle completed Stripe Checkout setup session — extract and store payment method.
     */
    public function handleSetupComplete(string $sessionId): void
    {
        $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

        $session = $stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['setup_intent.payment_method'],
        ]);

        $accountId = $session->metadata->account_id ?? null;
        if (!$accountId) {
            Log::error('Setup session missing account_id metadata', ['session_id' => $sessionId]);
            return;
        }

        $setupIntent = $session->setup_intent;
        $paymentMethod = $setupIntent->payment_method;

        $config = AutoTopUpConfig::updateOrCreate(
            ['account_id' => $accountId],
            [
                'stripe_customer_id' => $session->customer,
                'stripe_payment_method_id' => $paymentMethod->id,
                'card_brand' => $paymentMethod->card->brand ?? null,
                'card_last4' => $paymentMethod->card->last4 ?? null,
                'card_exp_month' => $paymentMethod->card->exp_month ?? null,
                'card_exp_year' => $paymentMethod->card->exp_year ?? null,
            ]
        );

        AutoTopUpEvent::create([
            'account_id' => $accountId,
            'config_id' => $config->id,
            'event_type' => AutoTopUpEvent::TYPE_PAYMENT_METHOD_ADDED,
            'status' => AutoTopUpEvent::STATUS_SUCCEEDED,
            'stripe_customer_id' => $session->customer,
            'stripe_payment_method_id' => $paymentMethod->id,
            'idempotency_key' => 'pm-added-' . $sessionId,
            'metadata' => $config->admin_locked ? ['admin_locked_at_time_of_setup' => true] : null,
            'completed_at' => now(),
        ]);

        try {
            FinancialAuditLog::record(
                'auto_topup_payment_method_added', 'account', $accountId,
                null,
                ['card_brand' => $paymentMethod->card->brand ?? null, 'card_last4' => $paymentMethod->card->last4 ?? null],
                $session->metadata->user_id ?? null, 'user'
            );
        } catch (\Throwable $e) {
            Log::error('Payment method added audit log failed', ['account_id' => $accountId]);
        }
    }

    /**
     * Retrieve the Stripe client_secret for a requires_action event.
     * Fetched from Stripe API on demand rather than stored in DB.
     */
    public function getClientSecretForEvent(AutoTopUpEvent $event): ?string
    {
        if (!$event->stripe_payment_intent_id) {
            return null;
        }

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $pi = $stripe->paymentIntents->retrieve($event->stripe_payment_intent_id);
            return $pi->client_secret;
        } catch (\Exception $e) {
            Log::error('Failed to retrieve PI client_secret', [
                'event_id' => $event->id,
                'pi_id' => $event->stripe_payment_intent_id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Remove the stored payment method and disable auto top-up.
     */
    public function removePaymentMethod(string $accountId, string $userId): void
    {
        $config = AutoTopUpConfig::where('account_id', $accountId)->first();
        if (!$config || empty($config->stripe_payment_method_id)) {
            return;
        }

        // Detach from Stripe
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $stripe->paymentMethods->detach($config->stripe_payment_method_id);
        } catch (\Exception $e) {
            Log::warning('Failed to detach payment method from Stripe', [
                'account_id' => $accountId,
                'pm_id' => $config->stripe_payment_method_id,
                'error' => $e->getMessage(),
            ]);
        }

        $oldPm = $config->stripe_payment_method_id;

        $config->update([
            'enabled' => false,
            'stripe_payment_method_id' => null,
            'card_brand' => null,
            'card_last4' => null,
            'card_exp_month' => null,
            'card_exp_year' => null,
            'updated_by_user_id' => $userId,
        ]);

        AutoTopUpEvent::create([
            'account_id' => $accountId,
            'config_id' => $config->id,
            'event_type' => AutoTopUpEvent::TYPE_PAYMENT_METHOD_REMOVED,
            'status' => AutoTopUpEvent::STATUS_SUCCEEDED,
            'stripe_payment_method_id' => $oldPm,
            'idempotency_key' => 'pm-removed-' . $accountId . '-' . Str::random(12),
            'completed_at' => now(),
        ]);

        try {
            FinancialAuditLog::record(
                'auto_topup_payment_method_removed', 'account', $accountId,
                null, null, $userId, 'user'
            );
        } catch (\Throwable $e) {
            Log::error('Payment method removed audit log failed', ['account_id' => $accountId]);
        }
    }

    /**
     * Handle payment_method.detached webhook — disable auto top-up if method matches.
     */
    public function handlePaymentMethodDetached(string $paymentMethodId): void
    {
        $config = AutoTopUpConfig::where('stripe_payment_method_id', $paymentMethodId)->first();
        if (!$config) {
            return;
        }

        $config->update([
            'enabled' => false,
            'stripe_payment_method_id' => null,
            'card_brand' => null,
            'card_last4' => null,
            'card_exp_month' => null,
            'card_exp_year' => null,
        ]);

        AutoTopUpEvent::create([
            'account_id' => $config->account_id,
            'config_id' => $config->id,
            'event_type' => AutoTopUpEvent::TYPE_PAYMENT_METHOD_REMOVED,
            'status' => AutoTopUpEvent::STATUS_SUCCEEDED,
            'stripe_payment_method_id' => $paymentMethodId,
            'idempotency_key' => 'pm-detached-' . $paymentMethodId . '-' . Str::random(12),
            'metadata' => ['source' => 'stripe_webhook'],
            'completed_at' => now(),
        ]);

        $this->notificationService->notifyPaymentMethodRemoved($config->account_id);
    }

    /**
     * Admin disables auto top-up with hard lock.
     */
    public function adminDisable(string $accountId, string $adminUserId, string $reason): void
    {
        $config = AutoTopUpConfig::where('account_id', $accountId)->first();
        if (!$config) {
            return;
        }

        $config->forceFill([
            'enabled' => false,
            'admin_locked' => true,
            'admin_locked_reason' => $reason,
            'admin_locked_at' => now(),
            'admin_locked_by' => $adminUserId,
        ])->save();

        AutoTopUpEvent::create([
            'account_id' => $accountId,
            'config_id' => $config->id,
            'event_type' => AutoTopUpEvent::TYPE_ADMIN_DISABLED,
            'status' => AutoTopUpEvent::STATUS_SUCCEEDED,
            'idempotency_key' => 'admin-disable-' . $accountId . '-' . Str::random(12),
            'metadata' => ['reason' => $reason, 'admin_user_id' => $adminUserId],
            'completed_at' => now(),
        ]);

        $this->notificationService->notifyAdminDisabled($config);

        try {
            FinancialAuditLog::record(
                'auto_topup_admin_disabled', 'account', $accountId,
                null,
                ['reason' => $reason],
                $adminUserId, 'admin'
            );
        } catch (\Throwable $e) {
            Log::error('Admin disable audit log failed', ['account_id' => $accountId]);
        }
    }

    /**
     * Admin unlocks auto top-up (does not re-enable — customer must do that).
     */
    public function adminUnlock(string $accountId, string $adminUserId): void
    {
        $config = AutoTopUpConfig::where('account_id', $accountId)->first();
        if (!$config) {
            return;
        }

        $config->forceFill([
            'admin_locked' => false,
            'admin_locked_reason' => null,
            'admin_locked_at' => null,
            'admin_locked_by' => null,
        ])->save();

        AutoTopUpEvent::create([
            'account_id' => $accountId,
            'config_id' => $config->id,
            'event_type' => AutoTopUpEvent::TYPE_ADMIN_UNLOCKED,
            'status' => AutoTopUpEvent::STATUS_SUCCEEDED,
            'idempotency_key' => 'admin-unlock-' . $accountId . '-' . Str::random(12),
            'metadata' => ['admin_user_id' => $adminUserId],
            'completed_at' => now(),
        ]);

        try {
            FinancialAuditLog::record(
                'auto_topup_admin_unlocked', 'account', $accountId,
                null, null, $adminUserId, 'admin'
            );
        } catch (\Throwable $e) {
            Log::error('Admin unlock audit log failed', ['account_id' => $accountId]);
        }
    }

    /**
     * Get auto top-up configuration for an account.
     */
    public function getConfig(string $accountId): ?AutoTopUpConfig
    {
        return AutoTopUpConfig::where('account_id', $accountId)->first();
    }

    /**
     * Update auto top-up configuration.
     * The service owns the invariant: cannot enable without a valid payment method.
     */
    public function updateConfig(string $accountId, array $data, string $userId): AutoTopUpConfig
    {
        // Service-level guard: cannot enable without a valid payment method
        if (!empty($data['enabled']) && $data['enabled']) {
            $existing = AutoTopUpConfig::where('account_id', $accountId)->first();
            if (!$existing || !$existing->hasValidPaymentMethod()) {
                throw new \InvalidArgumentException('Cannot enable auto top-up without a valid payment method.');
            }
        }

        $config = AutoTopUpConfig::updateOrCreate(
            ['account_id' => $accountId],
            array_merge($data, ['updated_by_user_id' => $userId])
        );

        AutoTopUpEvent::create([
            'account_id' => $accountId,
            'config_id' => $config->id,
            'event_type' => AutoTopUpEvent::TYPE_CONFIG_UPDATED,
            'status' => AutoTopUpEvent::STATUS_SUCCEEDED,
            'idempotency_key' => 'config-update-' . $accountId . '-' . Str::random(12),
            'metadata' => ['changes' => $data],
            'completed_at' => now(),
        ]);

        try {
            FinancialAuditLog::record(
                'auto_topup_config_updated', 'account', $accountId,
                null, $data, $userId, 'user'
            );
        } catch (\Throwable $e) {
            Log::error('Config update audit log failed', ['account_id' => $accountId]);
        }

        return $config;
    }

    /**
     * Get VAT info for an account (for customer portal display).
     */
    public function getVatInfo(string $accountId): array
    {
        $account = Account::find($accountId);
        if (!$account) {
            return ['vat_applicable' => false, 'vat_rate' => '0.00'];
        }
        $rate = $this->getVatRateForAccount($account);
        return [
            'vat_applicable' => bccomp($rate, '0', 2) > 0,
            'vat_rate' => $rate,
        ];
    }
}
