<?php

namespace App\Console\Commands;

use App\Models\RcsAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupDraftRcsAssets extends Command
{
    protected $signature = 'rcs:cleanup-drafts
        {--hours=24 : Delete draft assets older than this many hours}
        {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Remove stale draft RCS assets and their stored files';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $dryRun = $this->option('dry-run');

        $query = RcsAsset::withoutGlobalScope('tenant')
            ->where('is_draft', true)
            ->where('created_at', '<', now()->subHours($hours));

        $count = $query->count();

        if ($count === 0) {
            $this->info('No stale draft assets found.');
            return self::SUCCESS;
        }

        $this->info(($dryRun ? '[DRY RUN] Would delete' : 'Deleting') . " {$count} draft assets older than {$hours} hours.");

        $deleted = 0;
        $filesDeleted = 0;

        $query->chunk(200, function ($assets) use ($dryRun, &$deleted, &$filesDeleted) {
            foreach ($assets as $asset) {
                if ($dryRun) {
                    $this->line("  Would delete: {$asset->uuid} (created {$asset->created_at})");
                    $deleted++;
                    continue;
                }

                if ($asset->storage_path && Storage::disk('rcs-assets')->exists($asset->storage_path)) {
                    Storage::disk('rcs-assets')->delete($asset->storage_path);
                    $filesDeleted++;
                }
                if ($asset->original_storage_path && Storage::disk('rcs-assets')->exists($asset->original_storage_path)) {
                    Storage::disk('rcs-assets')->delete($asset->original_storage_path);
                    $filesDeleted++;
                }

                $asset->delete();
                $deleted++;
            }
        });

        $message = $dryRun
            ? "[DRY RUN] Would have deleted {$deleted} draft assets."
            : "Deleted {$deleted} draft assets and {$filesDeleted} files.";

        $this->info($message);
        Log::info('[RCS Cleanup] ' . $message);

        return self::SUCCESS;
    }
}
