<?php

namespace App\Console\Commands;

use App\Models\Billing\AutoTopUpEvent;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireStaleAutoTopUpEvents extends Command
{
    protected $signature = 'billing:expire-stale-auto-topups {--hours=24 : Hours after which requires_action events expire}';
    protected $description = 'Expire auto top-up events stuck in requires_action or pending status';

    public function handle(): int
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

            // Notify customer
            try {
                Notification::create([
                    'tenant_id' => $event->account_id,
                    'type' => 'auto_topup_action_expired',
                    'severity' => 'warning',
                    'category' => 'billing',
                    'title' => 'Auto Top-Up Payment Expired',
                    'body' => "Your auto top-up payment of £{$event->topup_amount} required authentication but expired. A new top-up will be triggered when your balance next falls below your threshold.",
                    'deep_link' => '/payments/auto-topup',
                ]);
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
