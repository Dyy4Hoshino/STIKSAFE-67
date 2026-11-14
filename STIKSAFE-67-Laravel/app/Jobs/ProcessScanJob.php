<?php

namespace App\Jobs;

use App\Enums\ScanStatus;
use App\Models\Scan;
use App\Services\ScanStateMachine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

class ProcessScanJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 150;

    public function __construct(public readonly string $scanId)
    {
        $this->onQueue('scans');
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("scan:{$this->scanId}"))->expireAfter(180),
        ];
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(ScanStateMachine $stateMachine): void
    {
        $scan = Scan::query()->find($this->scanId);

        if ($scan === null || $scan->isTerminal()) {
            return;
        }

        if ($scan->status !== ScanStatus::Queued) {
            return;
        }

        $stateMachine->transition($scan, ScanStatus::Processing, [
            'started_at' => now(),
        ]);

        // ML integration enters here through ScanEngineClient.
        // Laravel must not implement ML classification.
    }

    public function failed(Throwable $exception): void
    {
        $scan = Scan::query()->find($this->scanId);

        if ($scan === null || $scan->isTerminal()) {
            return;
        }

        if (! in_array($scan->status, [ScanStatus::Queued, ScanStatus::Processing], true)) {
            return;
        }

        app(ScanStateMachine::class)->transition($scan, ScanStatus::Failed, [
            'error_code' => 'SCAN_JOB_FAILED',
            'error_message' => 'Pemrosesan scan gagal.',
        ]);
    }
}
