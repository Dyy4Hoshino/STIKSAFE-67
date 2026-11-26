<?php

namespace App\Jobs;

use App\Contracts\ScanEngineClient;
use App\Enums\ScanAttemptStatus;
use App\Enums\ScanStatus;
use App\Models\Scan;
use App\Models\ScanAttempt;
use App\Services\ScanStateMachine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Str;
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

    public function handle(ScanStateMachine $stateMachine, ScanEngineClient $engine): void
    {
        $scan = Scan::query()->find($this->scanId);

        if ($scan === null || $scan->isTerminal()) {
            return;
        }

        if ($scan->status !== ScanStatus::Queued) {
            return;
        }

        // Ambil attempt yang running untuk scan ini
        $attempt = $scan->attempts()
            ->where('status', ScanAttemptStatus::Running->value)
            ->latest('id')
            ->first();

        if ($attempt === null) {
            return;
        }

        // Buat ml_request_id unik per attempt
        $mlRequestId = (string) Str::uuid();
        $attempt->forceFill([
            'ml_request_id' => $mlRequestId,
            'worker_host' => gethostname(),
            'started_at' => now(),
        ])->save();

        // Transisi ke processing
        $stateMachine->transition($scan, ScanStatus::Processing, [
            'started_at' => now(),
            'ml_request_id' => $mlRequestId,
        ]);

        // Heartbeat
        $attempt->forceFill(['heartbeat_at' => now()])->save();

        // Panggil ML engine (Fake atau HTTP tergantung binding)
        $result = $engine->scan($scan->refresh(), $attempt);

        // Heartbeat setelah ML selesai
        $attempt->forceFill(['heartbeat_at' => now()])->save();

        // Respons ML tidak valid → tidak pernah menjadi safe (BR-08)
        if (! in_array($result->label, ['safe', 'suspicious', 'malicious', 'unknown', null], true)) {
            $this->markAttemptFailed($attempt, 'ML_INVALID_RESPONSE', 'Label ML tidak dikenali.');
            $stateMachine->transition($scan->refresh(), ScanStatus::Failed, [
                'error_code' => 'ML_INVALID_RESPONSE',
                'error_message' => 'Respons ML tidak valid.',
            ]);
            return;
        }

        if ($result->isCompleted() && $result->label !== null) {
            // Simpan hasil valid
            $this->markAttemptSucceeded($attempt, $result);

            $stateMachine->transition($scan->refresh(), ScanStatus::Completed, [
                'result_label' => $result->label,
                'confidence' => $result->confidence,
                'threat_name' => $result->threatName,
                'threat_category' => $result->threatCategory,
                'model_version' => $result->modelVersion,
                'reason_codes' => $result->reasonCodes,
                'analysis' => $result->analysis,
                'preprocessing_version' => $result->preprocessingVersion,
                'completed_at' => now(),
            ]);
        } else {
            // Gagal dari ML
            $this->markAttemptFailed($attempt, $result->errorCode, $result->errorMessage);

            if ($this->attempts() >= $this->tries) {
                $stateMachine->transition($scan->refresh(), ScanStatus::Failed, [
                    'error_code' => $result->errorCode ?? 'ML_FAILED',
                    'error_message' => $result->errorMessage ?? 'Pemindaian gagal setelah beberapa percobaan.',
                ]);
            } else {
                // Retry: kembalikan ke queued dan buat attempt baru
                $this->createNextAttempt($scan->refresh());
                $stateMachine->transition($scan->refresh(), ScanStatus::Queued, [
                    'started_at' => null,
                    'ml_request_id' => null,
                ]);
                $this->release($this->backoff()[$this->attempts() - 1] ?? 300);
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        $scan = Scan::query()->find($this->scanId);

        if ($scan === null || $scan->isTerminal()) {
            return;
        }

        $attempt = $scan->attempts()
            ->where('status', ScanAttemptStatus::Running->value)
            ->latest('id')
            ->first();

        if ($attempt) {
            $this->markAttemptFailed($attempt, 'SCAN_JOB_FAILED', 'Job gagal dieksekusi: '.$exception->getMessage());
        }

        app(ScanStateMachine::class)->transition($scan, ScanStatus::Failed, [
            'error_code' => 'SCAN_JOB_FAILED',
            'error_message' => 'Pemrosesan scan gagal.',
        ]);
    }

    private function markAttemptSucceeded(ScanAttempt $attempt, \App\Services\ScanEngine\ScanEngineResult $result): void
    {
        $attempt->forceFill([
            'status' => ScanAttemptStatus::Succeeded,
            'finished_at' => now(),
            'duration_ms' => (int) ($attempt->started_at ? max(0, $attempt->started_at->diffInMilliseconds(now())) : 0),
        ])->save();
    }

    private function markAttemptFailed(ScanAttempt $attempt, ?string $code, ?string $message): void
    {
        $attempt->forceFill([
            'status' => ScanAttemptStatus::Failed,
            'error_code' => $code,
            'error_message' => $message,
            'finished_at' => now(),
            'duration_ms' => (int) ($attempt->started_at ? max(0, $attempt->started_at->diffInMilliseconds(now())) : 0),
        ])->save();
    }

    private function createNextAttempt(Scan $scan): void
    {
        $nextNumber = $scan->attempts()->max('attempt_number') + 1;
        $scan->attempts()->create([
            'attempt_number' => $nextNumber,
            'trigger' => \App\Enums\ScanAttemptTrigger::AutoRetry->value,
            'status' => ScanAttemptStatus::Running->value,
            'started_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $scan->forceFill(['attempts_count' => $nextNumber])->save();
    }
}
