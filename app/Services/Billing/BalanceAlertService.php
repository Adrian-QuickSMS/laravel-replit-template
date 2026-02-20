<?php

namespace App\Services\Billing;

use App\Models\Account;
use App\Models\Billing\AccountBalance;
use App\Models\Billing\BalanceAlertConfig;
use App\Models\Billing\LedgerEntry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BalanceAlertService
{
    /**
     * Check balance alerts after a deduction.
     */
    public function checkAlerts(string $accountId, AccountBalance $balance): void
    {
        $alerts = BalanceAlertConfig::where('account_id', $accountId)->get();

        if ($alerts->isEmpty()) return;

        $account = Account::find($accountId);
        if (!$account) return;

        $usagePercentage = $this->calculateUsagePercentage($account, $balance);

        foreach ($alerts as $alert) {
            if ($usagePercentage >= $alert->threshold_percentage && !$alert->isOnCooldown()) {
                $this->triggerAlert($account, $alert, $balance, $usagePercentage);
            }
        }
    }

    private function calculateUsagePercentage(Account $account, AccountBalance $balance): float
    {
        if ($account->billing_type === 'postpay') {
            $creditLimit = (float)$balance->credit_limit;
            if ($creditLimit <= 0) return 100;
            return ((float)$balance->total_outstanding / $creditLimit) * 100;
        }

        // Prepay: find total loaded since last top-up
        $lastTopUp = LedgerEntry::where('account_id', $account->id)
            ->where('entry_type', 'top_up')
            ->latest('created_at')
            ->first();

        $startBalance = $lastTopUp ? (float)$lastTopUp->amount : (float)$balance->balance;
        if ($startBalance <= 0) return 100;

        $remaining = (float)$balance->effective_available;
        return (1 - ($remaining / $startBalance)) * 100;
    }

    private function triggerAlert(Account $account, BalanceAlertConfig $alert, AccountBalance $balance, float $usagePercentage): void
    {
        $alert->update(['last_triggered_at' => now()]);

        $remaining = $balance->effective_available;
        $currency = $balance->currency;

        Log::info('Balance alert triggered', [
            'account_id' => $account->id,
            'threshold' => $alert->threshold_percentage,
            'usage' => round($usagePercentage, 1),
            'remaining' => $remaining,
        ]);

        // In production, dispatch notification jobs here:
        // if ($alert->notify_customer) dispatch(new SendCustomerBalanceAlert(...));
        // if ($alert->notify_admin) dispatch(new SendAdminBalanceAlert(...));
    }
}
