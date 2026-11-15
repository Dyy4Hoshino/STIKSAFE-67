<?php

namespace App\Console\Commands;

use App\Enums\ScanAttemptStatus;
use App\Enums\ScanStatus;
use App\Models\Scan;
use App\Services\ScanStateMachine;
use Illuminate\Console\Command;

class ScanWatchdogCommand extends Command
{
    protected $signature = 'scans:watchdog';

    protected $description = 'Periksa scan processing tanpa heartbeat atau melewati batas waktu dan pulihkan';

    public function handle(ScanStateMachine $stateMachine): int
    {
        $timeoutMinutes = (int) config('stiksafe.scan.max_total_duration_min', 15);
        $heartbeatTimeoutMinutes = 5;

        // 1. Scan processing tanpa heartbeat dalam 5 menit terakhir
        $staleScans = Scan::query()
            ->where('status', ScanStatus::Processing->value)
            ->whereHas('attempts', function ($query) use ($heartbeatTimeoutMinutes): void {
                $query->where('status', ScanAttemptStatus::Running->value)
                    ->where('heartbeat_at', '<', now()->subMinutes($heartbeatTimeoutMinutes));
            })
            ->get();

        foreach ($staleScans as $scan) {
            $runningAttempt = $scan->attempts()
                ->where('status', ScanAttemptStatus::Running->value)
                ->latest('id')
                ->first();

            if ($runningAttempt) {
                $runningAttempt->forceFill([
                    'status' => ScanAttemptStatus::TimedOut,
                    'error_code' => 'SCAN_HEARTBEAT_TIMEOUT',
                    'error_message' => 'Worker tidak mengirim heartbeat dalam batas waktu.',
                    'finished_at' => now(),
                ])->save();
            }

            $stateMachine->transition($scan, ScanStatus::Failed, [
                'error_code' => 'SCAN_TIMEOUT',
                'error_message' => 'Proses pemindaian terhenti karena waktu habis.',
            ]);

            $this->warn("Scan {$scan->id} ditandai TIMEOUT oleh Watchdog.");
        }

        // 2. Scan melewati batas total waktu scan (15 menit dari queued_at)
        $expiredScans = Scan::query()
            ->whereIn('status', [ScanStatus::Queued->value, ScanStatus::Processing->value])
            ->where('queued_at', '<', now()->subMinutes($timeoutMinutes))
            ->get();

        foreach ($expiredScans as $scan) {
            $stateMachine->transition($scan, ScanStatus::Failed, [
                'error_code' => 'SCAN_MAX_DURATION_EXCEEDED',
                'error_message' => 'Scan melebihi batas waktu maksimal pemrosesan.',
            ]);

            $this->warn("Scan {$scan->id} ditandai EXCEEDED MAX DURATION.");
        }

        return self::SUCCESS;
    }
}
