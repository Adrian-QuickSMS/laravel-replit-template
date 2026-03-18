<?php

namespace App\Services;

use App\Models\AccountSettings;
use App\Models\HeldMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * OutOfHoursService — blocks/holds outbound messages during anti-social hours.
 *
 * Pure in-memory computation for the check (sub-1ms):
 *   1. Get current time in account's timezone
 *   2. Compare against configured window (default 21:00–08:00)
 *   3. Return allow/reject/hold
 *
 * No Redis, no DB queries on the hot path. Account settings are cached
 * in-process for the duration of the request.
 *
 * Actions:
 *   reject — return error to caller (they must retry later)
 *   hold   — store message in held_messages table for automatic release
 */
class OutOfHoursService
{
    /** @var array<string, AccountSettings|null> Request-scoped settings cache */
    private array $settingsCache = [];

    /**
     * Check if sending is currently allowed for this account.
     *
     * @return OutOfHoursResult
     */
    public function check(string $accountId): OutOfHoursResult
    {
        try {
            $settings = $this->getSettings($accountId);

            if (!$settings || !$settings->out_of_hours_enabled) {
                return OutOfHoursResult::allowed();
            }

            $timezone = $settings->timezone ?: 'Europe/London';
            $now = Carbon::now($timezone);

            $startTime = $settings->out_of_hours_start ?: '21:00';
            $endTime = $settings->out_of_hours_end ?: '08:00';

            // Defence-in-depth: reject invalid config
            if ($startTime === $endTime) {
                return OutOfHoursResult::allowed();
            }

            if (!$this->isWithinRestrictedWindow($now, $startTime, $endTime)) {
                return OutOfHoursResult::allowed();
            }

            // Calculate when the window ends (next release time)
            $releaseAfter = $this->calculateReleaseTime($now, $endTime, $timezone);

            $action = $settings->out_of_hours_action ?: 'reject';

            return OutOfHoursResult::restricted(
                action: $action,
                releaseAfter: $releaseAfter,
                reason: "Sending is restricted between {$startTime} and {$endTime} ({$timezone}). " .
                    ($action === 'hold' ? "Message will be sent at {$endTime}." : "Please retry after {$endTime}.")
            );

        } catch (\Throwable $e) {
            Log::error('[OutOfHoursService] Check failed, allowing message', [
                'error' => $e->getMessage(),
                'account_id' => $accountId,
            ]);
            // Fail-open: never block sends due to service errors
            return OutOfHoursResult::allowed();
        }
    }

    /**
     * Hold a message for later release.
     * Used for non-campaign messages (API, email-to-SMS) when action=hold.
     */
    public function holdMessage(array $messageData): HeldMessage
    {
        return HeldMessage::create($messageData);
    }

    /**
     * Release held messages whose window has opened.
     * Called by the scheduled command.
     *
     * For campaign messages: resets the campaign_recipient to 'pending' and
     * dispatches a new ProcessCampaignBatch job to re-process them.
     * For non-campaign messages: dispatches a SendHeldMessage job.
     *
     * @return int Number of messages released
     */
    public function releaseEligibleMessages(): int
    {
        $released = 0;

        // Use withoutGlobalScopes — this runs from a scheduled command with no tenant context
        $messages = HeldMessage::withoutGlobalScopes()
            ->where('status', 'held')
            ->where('release_after', '<=', now())
            ->orderBy('release_after')
            ->limit(1000)
            ->get();

        foreach ($messages as $message) {
            try {
                // Re-check that the account's out-of-hours window has actually passed
                $settings = $this->getSettings($message->tenant_id);
                if ($settings && $settings->out_of_hours_enabled) {
                    $timezone = $settings->timezone ?: 'Europe/London';
                    $now = Carbon::now($timezone);
                    $startTime = $settings->out_of_hours_start ?: '21:00';
                    $endTime = $settings->out_of_hours_end ?: '08:00';

                    if ($this->isWithinRestrictedWindow($now, $startTime, $endTime)) {
                        // Still in restricted window — recalculate release time
                        $newRelease = $this->calculateReleaseTime($now, $endTime, $timezone);
                        $message->update(['release_after' => $newRelease]);
                        continue;
                    }
                }

                $message->update([
                    'status' => 'released',
                    'released_at' => now(),
                ]);

                // Dispatch the message based on origin
                $this->dispatchReleasedMessage($message);
                $released++;

            } catch (\Throwable $e) {
                Log::error('[OutOfHoursService] Failed to release held message', [
                    'message_id' => $message->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $released;
    }

    /**
     * Check if a time falls within the restricted window.
     * Handles overnight windows (e.g. 21:00–08:00 crosses midnight).
     */
    private function isWithinRestrictedWindow(Carbon $now, string $startTime, string $endTime): bool
    {
        $currentMinutes = $now->hour * 60 + $now->minute;

        [$startHour, $startMinute] = explode(':', $startTime);
        [$endHour, $endMinute] = explode(':', $endTime);

        $startMinutes = (int)$startHour * 60 + (int)$startMinute;
        $endMinutes = (int)$endHour * 60 + (int)$endMinute;

        if ($startMinutes > $endMinutes) {
            // Overnight window (e.g. 21:00 to 08:00)
            return $currentMinutes >= $startMinutes || $currentMinutes < $endMinutes;
        }

        // Same-day window (e.g. 13:00 to 15:00)
        return $currentMinutes >= $startMinutes && $currentMinutes < $endMinutes;
    }

    /**
     * Calculate when the restricted window ends.
     */
    private function calculateReleaseTime(Carbon $now, string $endTime, string $timezone): Carbon
    {
        [$endHour, $endMinute] = explode(':', $endTime);

        $release = $now->copy()->setTime((int)$endHour, (int)$endMinute, 0);

        // If end time is already past today, it's tomorrow's end time
        if ($release->lte($now)) {
            $release->addDay();
        }

        return $release;
    }

    /**
     * Dispatch a released held message back into the sending pipeline.
     *
     * Campaign messages: reset campaign_recipient to 'pending' so the existing
     * ProcessCampaignBatch job picks them up. We dispatch a new batch job to
     * ensure they're processed promptly.
     *
     * Non-campaign messages: dispatch via SendHeldMessage queue job.
     */
    private function dispatchReleasedMessage(HeldMessage $message): void
    {
        if ($message->campaign_id && $message->campaign_recipient_id) {
            // Campaign message — reset recipient to pending for re-processing
            $recipient = \App\Models\CampaignRecipient::withoutGlobalScopes()
                ->find($message->campaign_recipient_id);

            if ($recipient) {
                // Reset to pending — this is a valid status in the CHECK constraint
                DB::table('campaign_recipients')
                    ->where('id', $recipient->id)
                    ->update([
                        'status' => 'pending',
                        'failure_reason' => null,
                        'failure_code' => null,
                    ]);

                // Dispatch a new batch job to process the released recipients
                $campaign = \App\Models\Campaign::withoutGlobalScopes()->find($message->campaign_id);
                if ($campaign && in_array($campaign->status, ['sending', 'queued'])) {
                    \App\Jobs\ProcessCampaignBatch::dispatch(
                        $campaign->id,
                        $recipient->batch_number ?? 1
                    );
                }

                Log::info('[OutOfHoursService] Released campaign recipient', [
                    'recipient_id' => $recipient->id,
                    'campaign_id' => $message->campaign_id,
                ]);
            }
            return;
        }

        // Non-campaign message — dispatch via queue job
        \App\Jobs\SendHeldMessage::dispatch($message->id);
    }

    /**
     * Get account settings with request-scoped caching.
     */
    private function getSettings(string $accountId): ?AccountSettings
    {
        if (!isset($this->settingsCache[$accountId])) {
            $this->settingsCache[$accountId] = AccountSettings::withoutGlobalScopes()->find($accountId);
        }

        return $this->settingsCache[$accountId];
    }

    /**
     * Clear the in-process settings cache.
     * Called after settings are updated to prevent stale reads.
     */
    public function clearSettingsCache(): void
    {
        $this->settingsCache = [];
    }
}
