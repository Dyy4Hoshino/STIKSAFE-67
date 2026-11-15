<?php

namespace App\Console\Commands;

use App\Models\Scan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeExpiredFilesCommand extends Command
{
    protected $signature = 'scans:purge-expired-files';

    protected $description = 'Hapus file scan yang telah melewati retensi';

    public function handle(): int
    {
        Scan::query()
            ->whereNull('file_purged_at')
            ->whereNotNull('stored_path')
            ->where('file_expires_at', '<=', now())
            ->chunkById(100, function ($scans): void {
                foreach ($scans as $scan) {
                    Storage::disk($scan->storage_disk)->delete($scan->stored_path);
                    $scan->forceFill([
                        'stored_path' => null,
                        'file_purged_at' => now(),
                    ])->save();
                }
            });

        return self::SUCCESS;
    }
}
