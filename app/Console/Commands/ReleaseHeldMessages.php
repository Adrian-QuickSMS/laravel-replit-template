<?php

namespace App\Console\Commands;

use App\Services\OutOfHoursService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Releases held messages whose out-of-hours window has opened.
 * Runs every minute via the scheduler.
 *
 * Does NOT interfere with messages that don't have out-of-hours assigned —
 * only processes records in the held_messages table with status='held'.
 */
class ReleaseHeldMessages extends Command
{
    protected $signature = 'message:release-held';
    protected $description = 'Release messages held by out-of-hours restrictions';

    public function handle(OutOfHoursService $outOfHoursService): int
    {
        try {
            $released = $outOfHoursService->releaseEligibleMessages();

            if ($released > 0) {
                $this->info("Released {$released} held messages.");
                Log::info('[ReleaseHeldMessages] Released messages', ['count' => $released]);
            }

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('Failed: ' . $e->getMessage());
            Log::error('[ReleaseHeldMessages] Failed', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }
}
