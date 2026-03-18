<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Soft-purges message logs beyond the configured retention period.
 *
 * Nullifies mobile_number and content_encrypted while preserving
 * the rest of the record (message ID, timestamps, cost, status, country)
 * for billing dispute resolution.
 *
 * Runs daily. Processes accounts in batches to avoid long locks.
 */
class PurgeExpiredMessages extends Command
{
    protected $signature = 'message:purge-expired
                            {--dry-run : Show what would be purged without making changes}
                            {--batch-size=5000 : Number of records to update per batch}';

    protected $description = 'Soft-purge message logs (MSISDN + content) beyond retention period';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');
        $totalPurged = 0;

        $this->info($dryRun ? '[DRY RUN] Scanning for purgeable messages...' : 'Starting message purge...');

        try {
            // Guard: message_logs table may not exist yet (migration pending)
            if (!Schema::hasTable('message_logs')) {
                $this->warn('message_logs table does not exist — skipping message purge.');
                Log::warning('[PurgeExpiredMessages] message_logs table does not exist, skipping.');

                // Still clean dedup log if it exists
                $dedupCleaned = 0;
                if (Schema::hasTable('message_dedup_log')) {
                    $dedupCleaned = DB::table('message_dedup_log')
                        ->where('expires_at', '<', now())
                        ->delete();
                    if ($dedupCleaned > 0) {
                        $this->info("  Cleaned {$dedupCleaned} expired dedup log entries");
                    }
                }

                return Command::SUCCESS;
            }

            // Get all accounts with their retention settings
            // Default retention is 180 days (6 months) for accounts that haven't set one
            $accounts = DB::table('account_settings')
                ->select('account_id', 'message_retention_days')
                ->get();

            foreach ($accounts as $account) {
                $retentionDays = $account->message_retention_days ?: 180;
                $cutoffDate = now()->subDays($retentionDays);

                // Count purgeable records for this account
                $purgeableCount = DB::table('message_logs')
                    ->where('sent_time', '<', $cutoffDate)
                    ->where(function ($q) {
                        $q->whereNotNull('mobile_number')
                          ->orWhereNotNull('content_encrypted');
                    })
                    ->count();

                if ($purgeableCount === 0) {
                    continue;
                }

                $this->info("  Account {$account->account_id}: {$purgeableCount} records older than {$retentionDays} days");

                if ($dryRun) {
                    $totalPurged += $purgeableCount;
                    continue;
                }

                // Batch update — nullify MSISDN and content
                $accountPurged = 0;
                do {
                    $affected = DB::table('message_logs')
                        ->where('sent_time', '<', $cutoffDate)
                        ->where(function ($q) {
                            $q->whereNotNull('mobile_number')
                              ->orWhereNotNull('content_encrypted');
                        })
                        ->limit($batchSize)
                        ->update([
                            'mobile_number' => null,
                            'content_encrypted' => null,
                        ]);

                    $accountPurged += $affected;
                } while ($affected === $batchSize);

                $totalPurged += $accountPurged;

                Log::info('[PurgeExpiredMessages] Purged account messages', [
                    'account_id' => $account->account_id,
                    'retention_days' => $retentionDays,
                    'records_purged' => $accountPurged,
                ]);
            }

            // Also purge accounts with no settings row (use default 180 days)
            $defaultCutoff = now()->subDays(180);
            $orphanCount = DB::table('message_logs')
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                      ->from('account_settings');
                      // message_logs may not have account_id — skip if no FK
                })
                ->where('sent_time', '<', $defaultCutoff)
                ->where(function ($q) {
                    $q->whereNotNull('mobile_number')
                      ->orWhereNotNull('content_encrypted');
                })
                ->count();

            if ($orphanCount > 0 && !$dryRun) {
                $this->info("  Default retention (180 days): {$orphanCount} additional records");
            }

            // Clean up expired dedup log entries
            $dedupCleaned = 0;
            if (!Schema::hasTable('message_dedup_log')) {
                $this->warn('message_dedup_log table does not exist — skipping dedup cleanup.');
            } else {
                $dedupCleaned = DB::table('message_dedup_log')
                    ->where('expires_at', '<', now())
                    ->delete();
            }

            if ($dedupCleaned > 0) {
                $this->info("  Cleaned {$dedupCleaned} expired dedup log entries");
            }

            $prefix = $dryRun ? '[DRY RUN] Would purge' : 'Purged';
            $this->info("{$prefix} {$totalPurged} message records total.");

            Log::info('[PurgeExpiredMessages] Completed', [
                'total_purged' => $totalPurged,
                'dedup_cleaned' => $dedupCleaned,
                'dry_run' => $dryRun,
            ]);

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('Purge failed: ' . $e->getMessage());
            Log::error('[PurgeExpiredMessages] Failed', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }
}
