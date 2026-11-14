<?php

namespace App\Services;

use App\Enums\ScanAttemptStatus;
use App\Enums\ScanAttemptTrigger;
use App\Enums\ScanStatus;
use App\Jobs\ProcessScanJob;
use App\Models\Scan;
use App\Models\ScanAttempt;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CreateScanService
{
    public function create(int|string $userId, UploadedFile $file): Scan
    {
        $disk = (string) config('stiksafe.storage.disk', 'private');
        $originalFilename = $this->sanitizeFilename($file->getClientOriginalName());
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = (string) $file->getMimeType();
        $sha256 = hash_file('sha256', $file->getRealPath());

        if ($sha256 === false) {
            throw new RuntimeException('File hash gagal dibuat.');
        }

        $storedPath = 'quarantine/'.now()->format('Y/m').'/'.Str::uuid()->toString();
        $stored = Storage::disk($disk)->putFileAs(
            dirname($storedPath),
            $file,
            basename($storedPath),
        );

        if ($stored === false) {
            throw new RuntimeException('File gagal disimpan.');
        }

        try {
            $scan = DB::transaction(function () use (
                $userId,
                $originalFilename,
                $extension,
                $mimeType,
                $file,
                $sha256,
                $disk,
                $storedPath,
            ): Scan {
                $scan = Scan::query()->create([
                    'user_id' => $userId,
                    'original_filename' => $originalFilename,
                    'extension' => $extension,
                    'mime_type' => $mimeType,
                    'file_size' => $file->getSize(),
                    'sha256' => $sha256,
                    'storage_disk' => $disk,
                    'stored_path' => $storedPath,
                    'status' => ScanStatus::Queued,
                    'attempts_count' => 1,
                    'queued_at' => now(),
                    'file_expires_at' => now()->addDays((int) config('stiksafe.retention.file_days', 7)),
                ]);

                $scan->attempts()->create([
                    'attempt_number' => 1,
                    'trigger' => ScanAttemptTrigger::Initial,
                    'status' => ScanAttemptStatus::Running,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return $scan;
            });
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($storedPath);
            throw $exception;
        }

        ProcessScanJob::dispatch($scan->id)->afterCommit();

        return $scan;
    }

    private function sanitizeFilename(string $filename): string
    {
        $filename = basename(str_replace('\\', '/', $filename));
        $filename = preg_replace('/[\x00-\x1F\x7F]/u', '', $filename) ?? '';
        $filename = preg_replace('/[\x{202A}-\x{202E}\x{2066}-\x{2069}]/u', '', $filename) ?? '';
        $filename = trim($filename);

        return Str::limit($filename !== '' ? $filename : 'unnamed-file', 255, '');
    }
}
