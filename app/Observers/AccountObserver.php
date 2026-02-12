<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\AccountCredit;
use Illuminate\Support\Facades\Log;

/**
 * Account Observer
 *
 * Handles account lifecycle events, particularly:
 * - Credit expiry when account transitions from trial to live
 * - Audit logging for account status changes
 */
class AccountObserver
{
    /**
     * Handle the Account "updating" event.
     *
     * This fires BEFORE the model is saved, allowing us to check original values
     */
    public function updating(Account $account): void
    {
        // Check if account_type is being changed
        if ($account->isDirty('account_type')) {
            $oldType = $account->getOriginal('account_type');
            $newType = $account->account_type;

            // If transitioning from trial to live (prepay/postpay)
            if ($oldType === 'trial' && in_array($newType, ['prepay', 'postpay'])) {
                // Schedule credit expiry (will execute after save)
                $account->_shouldExpireCredits = true;

                Log::info('Account transitioning from trial to live', [
                    'account_id' => $account->id,
                    'account_number' => $account->account_number,
                    'old_type' => $oldType,
                    'new_type' => $newType,
                ]);
            }
        }
    }

    /**
     * Handle the Account "updated" event.
     *
     * This fires AFTER the model is saved
     */
    public function updated(Account $account): void
    {
        // If credits should be expired (set in updating hook)
        if (isset($account->_shouldExpireCredits) && $account->_shouldExpireCredits) {
            $this->expirePromotionalCredits($account);
            unset($account->_shouldExpireCredits);
        }

        // Log status changes
        if ($account->wasChanged('status')) {
            Log::info('Account status changed', [
                'account_id' => $account->id,
                'account_number' => $account->account_number,
                'old_status' => $account->getOriginal('status'),
                'new_status' => $account->status,
            ]);
        }
    }

    /**
     * Expire all promotional credits for an account
     *
     * This runs when account transitions from trial to live
     * Promotional credits include: signup_promo, mobile_verification, referral
     */
    protected function expirePromotionalCredits(Account $account): void
    {
        // Get all valid promotional credits
        $promotionalCredits = AccountCredit::where('account_id', $account->id)
            ->promotional()
            ->valid()
            ->get();

        $totalExpired = 0;
        $creditsExpired = 0;

        foreach ($promotionalCredits as $credit) {
            $totalExpired += $credit->credits_remaining;
            $credit->markAsExpired();
            $creditsExpired++;
        }

        Log::info('Promotional credits expired on account type change', [
            'account_id' => $account->id,
            'account_number' => $account->account_number,
            'account_type' => $account->account_type,
            'credit_records_expired' => $creditsExpired,
            'total_credits_expired' => $totalExpired,
        ]);

        // Update account's total signup credits awarded (for historical tracking)
        // Note: signup_credits_awarded is immutable - tracks what was initially given
        // The expiry is tracked in account_credits.expired_at
    }

    /**
     * Handle the Account "created" event.
     */
    public function created(Account $account): void
    {
        Log::info('New account created', [
            'account_id' => $account->id,
            'account_number' => $account->account_number,
            'company_name' => $account->company_name,
            'account_type' => $account->account_type,
            'status' => $account->status,
        ]);
    }

    /**
     * Handle the Account "deleted" event.
     */
    public function deleted(Account $account): void
    {
        Log::warning('Account deleted', [
            'account_id' => $account->id,
            'account_number' => $account->account_number,
            'company_name' => $account->company_name,
        ]);
    }

    /**
     * Handle the Account "restored" event (soft delete restoration).
     */
    public function restored(Account $account): void
    {
        Log::info('Account restored', [
            'account_id' => $account->id,
            'account_number' => $account->account_number,
            'company_name' => $account->company_name,
        ]);
    }
}
