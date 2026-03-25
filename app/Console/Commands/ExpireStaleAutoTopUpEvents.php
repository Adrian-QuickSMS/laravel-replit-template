<?php

namespace App\Console\Commands;

use App\Models\Billing\AutoTopUpConfig;
use App\Models\Billing\AutoTopUpEvent;
use App\Services\Billing\AutoTopUpNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireStaleAutoTopUpEvents extends Command
{
    protected $signature = 'billing:expire-stale-auto-topups {--hours=24 : Hours after which requires_action events expire}';
    protected $description = 'Expire auto top-up events stuck in requires_action or pending status';

    public function handle(AutoTopUpNotificationService $notificationService): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        // Expire requires_action events (Stripe PaymentIntents expire after ~24h)
        $requiresAction = AutoTopUpEvent::where('status', AutoTopUpEvent::STATUS_REQUIRES_ACTION)
            ->where('created_at', '<', $cutoff)
            ->get();

        $expiredCount = 0;
        foreach ($requiresAction as $event) {
            $event->update([
                'event_type' => AutoTopUpEvent::TYPE_ACTION_EXPIRED,
                'status' => AutoTopUpEvent::STATUS_EXPIRED,
                'completed_at' => now(),
                'failure_message' => "Payment authentication expired after {$hours} hours.",
            ]);

            // Notify via notification service — respects lock status in message wording
            try {
                $config = AutoTopUpConfig::where('account_id', $event->account_id)->first();
                $isLocked = $config && $config->admin_locked;
                $notificationService->notifyActionExpired($event, $isLocked);
            } catch (\Throwable $e) {
                Log::error('Failed to notify expired auto top-up', ['event_id' => $event->id]);
            }

            $expiredCount++;
        }

        // Expire stuck pending/processing events (safety net for orphaned jobs)
        $stuck = AutoTopUpEvent::whereIn('status', [AutoTopUpEvent::STATUS_PENDING, AutoTopUpEvent::STATUS_PROCESSING])
            ->where('created_at', '<', $cutoff)
            ->get();

        $stuckCount = 0;
        foreach ($stuck as $event) {
            $event->update([
                'status' => AutoTopUpEvent::STATUS_EXPIRED,
                'completed_at' => now(),
                'failure_message' => "Event expired after {$hours} hours without completion.",
            ]);
            $stuckCount++;
        }

        if ($expiredCount > 0 || $stuckCount > 0) {
            Log::info('Expired stale auto top-up events', [
                'requires_action_expired' => $expiredCount,
                'stuck_expired' => $stuckCount,
            ]);
        }

        $this->info("Expired {$expiredCount} requires_action and {$stuckCount} stuck events.");

        return self::SUCCESS;
    }
}
