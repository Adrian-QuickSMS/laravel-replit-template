<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AdminAuditLog;
use App\Models\Billing\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnforceOverdueInvoices extends Command
{
    protected $signature = 'billing:enforce-overdue
        {--dry-run : Show what would happen without making changes}';

    protected $description = 'Enforce overdue invoice policies: suspend accounts and send reminder emails';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $today = now()->startOfDay();
        $suspended = 0;
        $reminders = 0;
        $skipped = 0;

        $this->info($dryRun ? '[DRY RUN] Scanning overdue invoices...' : 'Scanning overdue invoices...');

        $overdueInvoices = Invoice::whereIn('status', ['sent', 'issued', 'overdue', 'partially_paid'])
            ->where('due_date', '<', $today->toDateString())
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('No overdue invoices found.');
            return self::SUCCESS;
        }

        $this->info("Found {$overdueInvoices->count()} overdue invoice(s).");

        $accountIds = $overdueInvoices->pluck('account_id')->unique();
        $accounts = Account::whereIn('id', $accountIds)->get()->keyBy('id');

        foreach ($accountIds as $accountId) {
            $account = $accounts->get($accountId);
            if (!$account) {
                continue;
            }

            if ($account->isSuspended() || $account->isClosed()) {
                $skipped++;
                continue;
            }

            $mode = $account->overdue_enforcement_mode ?? 'hard';
            if ($mode === 'none') {
                $this->sendReminderIfDue($account, $dryRun, $reminders);
                continue;
            }

            $oldestOverdueInvoice = $overdueInvoices
                ->where('account_id', $accountId)
                ->sortBy('due_date')
                ->first();

            $dueDate = \Carbon\Carbon::parse($oldestOverdueInvoice->due_date)->startOfDay();
            $graceDays = $mode === 'soft' ? ($account->overdue_grace_days ?? 0) : 0;
            $suspensionDate = $dueDate->copy()->addDays($graceDays + 1);

            if ($today->gte($suspensionDate)) {
                $this->line("  Account {$account->company_name} ({$account->id}): overdue since {$dueDate->toDateString()}, grace={$graceDays}d => SUSPEND");

                if (!$dryRun) {
                    try {
                        DB::transaction(function () use ($account, $oldestOverdueInvoice, $mode, $graceDays) {
                            $account->update([
                                'status' => Account::STATUS_SUSPENDED,
                                'suspended_at' => now(),
                            ]);

                            AdminAuditLog::record(
                                'account_suspended_overdue', 'billing', 'high',
                                null, 'System (billing:enforce-overdue)',
                                'account', $account->id, $account->id,
                                "Account auto-suspended: overdue invoice {$oldestOverdueInvoice->invoice_number}, enforcement={$mode}, grace={$graceDays}d",
                                [
                                    'invoice_id' => $oldestOverdueInvoice->id,
                                    'invoice_number' => $oldestOverdueInvoice->invoice_number,
                                    'due_date' => $oldestOverdueInvoice->due_date,
                                    'enforcement_mode' => $mode,
                                    'grace_days' => $graceDays,
                                ]
                            );
                        });
                        $suspended++;
                    } catch (\Throwable $e) {
                        $this->error("  Failed to suspend {$account->company_name}: {$e->getMessage()}");
                        Log::error('billing:enforce-overdue suspend failed', [
                            'account_id' => $account->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } else {
                    $suspended++;
                }
            } else {
                $daysUntil = $today->diffInDays($suspensionDate);
                $this->line("  Account {$account->company_name}: {$daysUntil} day(s) until suspension");
            }

            $this->sendReminderIfDue($account, $dryRun, $reminders);
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Complete: {$suspended} suspended, {$reminders} reminders queued, {$skipped} skipped (already suspended/closed).");

        return self::SUCCESS;
    }

    private function sendReminderIfDue(Account $account, bool $dryRun, int &$reminders): void
    {
        $frequency = $account->overdue_email_frequency ?? 'weekly';
        if ($frequency === 'none') {
            return;
        }

        $lastSent = $account->last_overdue_email_sent_at;
        $intervalDays = match ($frequency) {
            'daily' => 1,
            'every_3_days' => 3,
            'weekly' => 7,
            'fortnightly' => 14,
            default => 7,
        };

        if ($lastSent && $lastSent->diffInDays(now()) < $intervalDays) {
            return;
        }

        $this->line("  Reminder email due for {$account->company_name} (frequency: {$frequency})");

        if (!$dryRun) {
            Log::info('[OverdueReminder] Placeholder: would send overdue reminder email', [
                'account_id' => $account->id,
                'company_name' => $account->company_name,
                'email' => $account->billing_email ?? $account->email,
                'frequency' => $frequency,
            ]);

            $account->update(['last_overdue_email_sent_at' => now()]);
            $reminders++;
        } else {
            $reminders++;
        }
    }
}
