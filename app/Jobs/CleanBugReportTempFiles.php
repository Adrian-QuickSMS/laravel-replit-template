<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Clean up stale temporary files from bug report screenshot uploads.
 *
 * Schedule this hourly via kernel:
 *   $schedule->job(new CleanBugReportTempFiles)->hourly();
 */
class CleanBugReportTempFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $disk = Storage::disk('local');
        $directory = 'temp/bug-reports';

        if (!$disk->exists($directory)) {
            return;
        }

        $files = $disk->files($directory);
        $threshold = now()->subHour()->getTimestamp();
        $deleted = 0;

        foreach ($files as $file) {
            if ($disk->lastModified($file) < $threshold) {
                $disk->delete($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            Log::info('CleanBugReportTempFiles: removed stale files', ['count' => $deleted]);
        }
    }
}
