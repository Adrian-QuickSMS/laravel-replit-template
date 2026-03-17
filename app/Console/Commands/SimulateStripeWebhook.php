<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Billing\AccountBalance;
use App\Models\Billing\AutoTopUpConfig;
use App\Models\Billing\FinancialAuditLog;
use App\Models\Billing\Invoice;
use App\Models\Billing\Payment;
use App\Services\Billing\BalanceService;
use App\Services\Billing\InvoiceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SimulateStripeWebhook extends Command
{
    protected $signature = 'billing:simulate-stripe
        {account : Account UUID}
        {amount : Amount in pounds/currency units (e.g. 50.00)}
        {--type=top_up : Event type: top_up, auto_topup, dd_collection}
        {--currency=GBP : Currency code}
        {--invoice= : Invoice ID (required for dd_collection)}
        {--dry-run : Show what would happen without making changes}';

    protected $description = 'Simulate a Stripe webhook event to test the payment → balance credit flow without a Stripe connection';

    public function handle(
        BalanceService $balanceService,
        InvoiceService $invoiceService
    ): int {
        $accountId = $this->argument('account');
        $amount = $this->argument('amount');
        $type = $this->option('type');
        $currency = strtoupper($this->option('currency'));
        $invoiceId = $this->option('invoice');
        $dryRun = $this->option('dry-run');

        if (!in_array($type, ['top_up', 'auto_topup', 'dd_collection'])) {
            $this->error("Invalid type '{$type}'. Must be one of: top_up, auto_topup, dd_collection");
            return self::FAILURE;
        }

        if (!is_numeric($amount) || bccomp($amount, '0', 4) <= 0) {
            $this->error("Amount must be a positive number. Got: {$amount}");
            return self::FAILURE;
        }

        if ($type === 'dd_collection' && !$invoiceId) {
            $this->error('The --invoice option is required for dd_collection type.');
            return self::FAILURE;
        }

        $account = Account::find($accountId);
        if (!$account) {
            $this->error("Account not found: {$accountId}");
            return self::FAILURE;
        }

        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if (!$invoice) {
                $this->error("Invoice not found: {$invoiceId}");
                return self::FAILURE;
            }
            if ($invoice->account_id !== $accountId) {
                $this->error("Invoice {$invoiceId} does not belong to account {$accountId}");
                return self::FAILURE;
            }
        }

        $balance = AccountBalance::where('account_id', $accountId)->first();
        $beforeBalance = $balance ? $balance->balance : 'N/A';
        $beforeAvailable = $balance ? $balance->effective_available : 'N/A';
        $beforeOutstanding = $balance ? $balance->total_outstanding : 'N/A';

        $simPiId = 'pi_sim_' . Str::random(16);
        $simEventId = 'evt_sim_' . Str::random(16);
        $simRefUuid = (string) Str::uuid();

        $typeLabels = [
            'top_up' => 'Checkout Top-Up',
            'auto_topup' => 'Auto Top-Up (payment_intent.succeeded)',
            'dd_collection' => 'Direct Debit Collection (payment_intent.succeeded)',
        ];

        $this->newLine();
        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║        STRIPE WEBHOOK SIMULATOR                     ║');
        $this->info('╚══════════════════════════════════════════════════════╝');
        $this->newLine();

        $this->table(
            ['Field', 'Value'],
            [
                ['Account', $account->company_name ?? $account->id],
                ['Account ID', $accountId],
                ['Billing Type', $account->billing_type ?? 'prepay'],
                ['Event Type', $typeLabels[$type]],
                ['Amount', "{$currency} " . number_format((float)$amount, 2)],
                ['Simulated PI', $simPiId],
                ['Simulated Event', $simEventId],
                $invoiceId ? ['Invoice', $invoiceId] : ['Invoice', '—'],
            ]
        );

        $this->newLine();
        $this->info('── Before ──');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Balance', $beforeBalance !== 'N/A' ? "{$currency} " . number_format((float)$beforeBalance, 2) : 'No balance record'],
                ['Effective Available', $beforeAvailable !== 'N/A' ? "{$currency} " . number_format((float)$beforeAvailable, 2) : 'No balance record'],
                ['Total Outstanding', $beforeOutstanding !== 'N/A' ? "{$currency} " . number_format((float)$beforeOutstanding, 2) : 'No balance record'],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('[DRY RUN] No changes made. The above shows what would be processed.');
            $this->line("  → Would credit account via BalanceService");
            $this->line("  → Would create Payment record (method: " . $this->paymentMethod($type) . ", PI: {$simPiId})");
            if ($type === 'top_up') {
                $this->line("  → Would create top-up invoice");
            }
            if ($type === 'dd_collection') {
                $this->line("  → Would mark invoice {$invoiceId} as paid");
            }
            if ($type === 'auto_topup') {
                $this->line("  → Would update auto top-up config last_triggered_at");
            }
            $this->line("  → Would log to financial_audit_log (source: cli_simulation)");
            return self::SUCCESS;
        }

        if (!$balance) {
            $this->warn("No balance record exists for this account. Creating one...");
            $balanceService->initializeBalance($accountId, $currency);
        }

        try {
            DB::transaction(function () use ($balanceService, $invoiceService, $account, $accountId, $amount, $currency, $type, $invoiceId, $simPiId, $simEventId, $simRefUuid) {
                $idempotencyKey = "sim-{$type}-{$simRefUuid}";
                $simMeta = ['simulated' => true, 'source' => 'billing:simulate-stripe', 'simulated_pi' => $simPiId, 'simulated_event' => $simEventId];

                if ($type === 'top_up') {
                    if ($account->billing_type === 'postpay') {
                        $balanceService->processPostpayAdvance($accountId, $amount, $currency, $idempotencyKey, $simMeta);
                    } else {
                        $balanceService->processTopUp($accountId, $amount, $currency, $idempotencyKey, 'cli_simulation', $simRefUuid, $simMeta);
                    }

                    Payment::create([
                        'account_id' => $accountId,
                        'payment_method' => 'stripe_checkout',
                        'stripe_payment_intent_id' => $simPiId,
                        'currency' => $currency,
                        'amount' => $amount,
                        'status' => 'succeeded',
                        'paid_at' => now(),
                        'metadata' => $simMeta,
                    ]);

                    $invoiceService->createTopUpInvoice($account, $amount, $currency, 'cs_sim_' . Str::random(16));

                } elseif ($type === 'auto_topup') {
                    $balanceService->processTopUp($accountId, $amount, $currency, $idempotencyKey, 'cli_simulation', $simRefUuid, array_merge($simMeta, ['auto_topup' => true]));

                    Payment::create([
                        'account_id' => $accountId,
                        'payment_method' => 'stripe_auto_topup',
                        'stripe_payment_intent_id' => $simPiId,
                        'currency' => $currency,
                        'amount' => $amount,
                        'status' => 'succeeded',
                        'paid_at' => now(),
                        'metadata' => $simMeta,
                    ]);

                    $invoiceService->createTopUpInvoice($account, $amount, $currency);

                    AutoTopUpConfig::where('account_id', $accountId)->update(['last_triggered_at' => now()]);

                } elseif ($type === 'dd_collection') {
                    $balanceService->processPostpayAdvance($accountId, $amount, $currency, $idempotencyKey, array_merge($simMeta, ['dd' => true]));

                    Payment::create([
                        'account_id' => $accountId,
                        'invoice_id' => $invoiceId,
                        'payment_method' => 'stripe_dd',
                        'stripe_payment_intent_id' => $simPiId,
                        'currency' => $currency,
                        'amount' => $amount,
                        'status' => 'succeeded',
                        'paid_at' => now(),
                        'metadata' => $simMeta,
                    ]);

                    $invoice = Invoice::find($invoiceId);
                    if ($invoice) {
                        $invoice->update([
                            'amount_paid' => $amount,
                            'amount_due' => '0',
                            'status' => 'paid',
                            'paid_date' => now()->toDateString(),
                        ]);
                    }
                }

                FinancialAuditLog::record(
                    'simulated_stripe_' . $type, 'account', $accountId,
                    null,
                    [
                        'amount' => $amount,
                        'currency' => $currency,
                        'simulated_pi' => $simPiId,
                        'simulated_event' => $simEventId,
                        'type' => $type,
                        'invoice_id' => $invoiceId,
                        'source' => 'cli_simulation',
                    ],
                    null, 'cli_simulation'
                );
            });
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error("Simulation failed: {$e->getMessage()}");
            $this->line($e->getTraceAsString());
            return self::FAILURE;
        }

        $balance = AccountBalance::where('account_id', $accountId)->first();
        $afterBalance = $balance ? $balance->balance : 'N/A';
        $afterAvailable = $balance ? $balance->effective_available : 'N/A';
        $afterOutstanding = $balance ? $balance->total_outstanding : 'N/A';

        $this->newLine();
        $this->info('── After ──');
        $this->table(
            ['Metric', 'Before', 'After', 'Change'],
            [
                [
                    'Balance',
                    $beforeBalance !== 'N/A' ? "{$currency} " . number_format((float)$beforeBalance, 2) : '—',
                    "{$currency} " . number_format((float)$afterBalance, 2),
                    $beforeBalance !== 'N/A' ? "{$currency} +" . number_format((float)bcsub($afterBalance, $beforeBalance, 4), 2) : '—',
                ],
                [
                    'Effective Available',
                    $beforeAvailable !== 'N/A' ? "{$currency} " . number_format((float)$beforeAvailable, 2) : '—',
                    "{$currency} " . number_format((float)$afterAvailable, 2),
                    $beforeAvailable !== 'N/A' ? "{$currency} +" . number_format((float)bcsub($afterAvailable, $beforeAvailable, 4), 2) : '—',
                ],
                [
                    'Total Outstanding',
                    $beforeOutstanding !== 'N/A' ? "{$currency} " . number_format((float)$beforeOutstanding, 2) : '—',
                    "{$currency} " . number_format((float)$afterOutstanding, 2),
                    $beforeOutstanding !== 'N/A' ? "{$currency} " . number_format((float)bcsub($afterOutstanding, $beforeOutstanding, 4), 2) : '—',
                ],
            ]
        );

        $payment = Payment::where('stripe_payment_intent_id', $simPiId)->first();
        if ($payment) {
            $this->newLine();
            $this->info('── Payment Record Created ──');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Payment ID', $payment->id],
                    ['Method', $payment->payment_method],
                    ['Status', $payment->status],
                    ['Amount', "{$currency} " . number_format((float)$payment->amount, 2)],
                    ['Stripe PI (simulated)', $payment->stripe_payment_intent_id],
                ]
            );
        }

        $this->newLine();
        $this->info('Simulation complete. Balance credited successfully.');

        return self::SUCCESS;
    }

    private function paymentMethod(string $type): string
    {
        return match ($type) {
            'top_up' => 'stripe_checkout',
            'auto_topup' => 'stripe_auto_topup',
            'dd_collection' => 'stripe_dd',
            default => 'stripe_checkout',
        };
    }
}
