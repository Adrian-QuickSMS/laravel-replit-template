<?php

namespace App\Services;

use App\Models\Account;
use App\Models\SenderId;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * SenderID Enforcement Service
 *
 * Validates SenderIDs at the message-sending layer.
 * Called by Send Message, Templates, Email-to-SMS, and Bulk API
 * to ensure only approved SenderIDs are used.
 *
 * SECURITY: This is the enforcement point that prevents unapproved
 * SenderIDs from being used to send messages. It runs at API level,
 * not just UI level, so it catches programmatic access too.
 */
class SenderIdEnforcementService
{
    /**
     * Validate that a SenderID is approved and usable by the given account
     *
     * @param string $senderIdValue The sender ID string (e.g., "QuickSMS", "447700900100")
     * @param string $accountId The account UUID
     * @param string|null $userId Optional user UUID for user-level assignment check
     * @return array{allowed: bool, reason: string|null, sender_id: SenderId|null}
     */
    public function validateForSending(string $senderIdValue, string $accountId, ?string $userId = null): array
    {
        // Look up the SenderID for this account
        $senderId = SenderId::withoutGlobalScope('tenant')
            ->where('account_id', $accountId)
            ->where('sender_id_value', $senderIdValue)
            ->where('workflow_status', SenderId::STATUS_APPROVED)
            ->first();

        if (!$senderId) {
            // Check if this account has freeform exception
            $account = Account::find($accountId);
            if ($account) {
                $flags = $account->flags;
                // Check for allow_freeform_sender_id flag
                // This would be a boolean on AccountFlags - future enhancement
                // For now, freeform is not supported
            }

            return [
                'allowed' => false,
                'reason' => "SenderID '{$senderIdValue}' is not approved for this account.",
                'sender_id' => null,
            ];
        }

        // If user is specified, check user-level assignment
        if ($userId) {
            $user = User::withoutGlobalScope('tenant')->find($userId);
            if ($user) {
                $isAssigned = $this->isAssignedToUser($senderId, $user);
                if (!$isAssigned) {
                    return [
                        'allowed' => false,
                        'reason' => "SenderID '{$senderIdValue}' is not assigned to your user or sub-account.",
                        'sender_id' => $senderId,
                    ];
                }
            }
        }

        return [
            'allowed' => true,
            'reason' => null,
            'sender_id' => $senderId,
        ];
    }

    /**
     * Check if a SenderID is assigned to a user (directly or via sub-account)
     *
     * If the SenderID has NO assignments, it's available to all users on the account.
     * If it HAS assignments, the user must be directly assigned OR their sub-account must be assigned.
     */
    protected function isAssignedToUser(SenderId $senderId, User $user): bool
    {
        // No assignments = available to everyone on the account
        if ($senderId->assignments()->count() === 0) {
            return true;
        }

        // Check direct user assignment
        $directAssignment = $senderId->assignments()
            ->where('assignable_type', User::class)
            ->where('assignable_id', $user->id)
            ->exists();

        if ($directAssignment) {
            return true;
        }

        // Check sub-account assignment
        if ($user->sub_account_id) {
            $subAccountAssignment = $senderId->assignments()
                ->where('assignable_type', \App\Models\SubAccount::class)
                ->where('assignable_id', $user->sub_account_id)
                ->exists();

            if ($subAccountAssignment) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the default SenderID for an account
     * Every account gets "QuickSMS" as default
     *
     * @return SenderId|null
     */
    public function getDefaultSenderId(string $accountId): ?SenderId
    {
        return SenderId::withoutGlobalScope('tenant')
            ->where('account_id', $accountId)
            ->where('is_default', true)
            ->where('workflow_status', SenderId::STATUS_APPROVED)
            ->first();
    }

    /**
     * Get all approved SenderIDs for a user (for dropdowns)
     * Used by Send Message, Templates, Email-to-SMS UI
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getApprovedForUser(User $user)
    {
        return SenderId::usableByUser($user)->get();
    }

    /**
     * Suspend all approved SenderIDs for an account
     * Called when an account is suspended
     *
     * @param string $accountId
     * @param string|null $reason
     * @return int Number of SenderIDs suspended
     */
    public function suspendAllForAccount(string $accountId, ?string $reason = null): int
    {
        $senderIds = SenderId::withoutGlobalScope('tenant')
            ->where('account_id', $accountId)
            ->where('workflow_status', SenderId::STATUS_APPROVED)
            ->get();

        $count = 0;
        foreach ($senderIds as $senderId) {
            try {
                $senderId->transitionTo(
                    SenderId::STATUS_SUSPENDED,
                    null,
                    $reason ?? 'Account suspended',
                    null,
                    null
                );
                $count++;
            } catch (\Exception $e) {
                Log::error('[SenderIdEnforcement] Failed to suspend SenderID', [
                    'sender_id' => $senderId->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("[SenderIdEnforcement] Suspended {$count} SenderIDs for account {$accountId}");
        return $count;
    }

    /**
     * Create the default "QuickSMS" SenderID for a new account
     * Called during account activation
     *
     * @param string $accountId
     * @return SenderId
     */
    public function createDefaultSenderId(string $accountId): SenderId
    {
        $existing = SenderId::withoutGlobalScope('tenant')
            ->where('account_id', $accountId)
            ->where('is_default', true)
            ->first();

        if ($existing) {
            return $existing;
        }

        $senderId = SenderId::create([
            'account_id' => $accountId,
            'sender_id_value' => 'QuickSMS',
            'sender_type' => SenderId::TYPE_ALPHA,
            'brand_name' => 'QuickSMS Default',
            'country_code' => 'GB',
            'use_case' => 'mixed',
            'use_case_description' => 'Default platform SenderID for all message types.',
            'permission_confirmed' => true,
            'permission_explanation' => 'System default - auto-provisioned on account activation.',
            'workflow_status' => SenderId::STATUS_APPROVED,
            'is_default' => true,
            'is_locked' => true,
            'reviewed_at' => now(),
            'submitted_at' => now(),
        ]);

        // Record the auto-approval in history
        $senderId->recordStatusHistory(
            null,
            SenderId::STATUS_APPROVED,
            'system_auto_approved',
            null,
            'Default SenderID auto-provisioned on account activation.',
            null,
            null
        );

        Log::info("[SenderIdEnforcement] Created default QuickSMS SenderID for account {$accountId}");

        return $senderId;
    }
}
