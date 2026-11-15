<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ScanAttemptStatus;
use App\Enums\ScanAttemptTrigger;
use App\Enums\ScanStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ScanResource;
use App\Jobs\ProcessScanJob;
use App\Models\Scan;
use App\Services\AuditLogService;
use App\Services\ScanStateMachine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ScanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Scan::query()->with('user:id,name,email')->orderByDesc('created_at');

        foreach (['status', 'result_label', 'user_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        $scans = $query->paginate(min(max((int) $request->input('per_page', 20), 1), 100));

        return ScanResource::collection($scans)
            ->additional(['success' => true])
            ->response();
    }

    public function show(Scan $scan, AuditLogService $audit): JsonResponse
    {
        $audit->record('scan.viewed_by_admin', 'scan', $scan->id, severity: 'info');

        return response()->json([
            'success' => true,
            'data' => [
                'scan' => (new ScanResource($scan))->resolve(),
                'attempts' => $scan->attempts()->orderBy('attempt_number')->get()->map(fn ($attempt) => [
                    'attempt_number' => $attempt->attempt_number,
                    'trigger' => $attempt->trigger?->value,
                    'status' => $attempt->status?->value,
                    'ml_request_id' => $attempt->ml_request_id,
                    'http_status' => $attempt->http_status,
                    'error_code' => $attempt->error_code,
                    'started_at' => $attempt->started_at?->toISOString(),
                    'finished_at' => $attempt->finished_at?->toISOString(),
                    'duration_ms' => $attempt->duration_ms,
                ]),
            ],
        ]);
    }

    public function retry(Scan $scan, ScanStateMachine $stateMachine, AuditLogService $audit): JsonResponse
    {
        if ($scan->status !== ScanStatus::Failed || $scan->stored_path === null
            || ! Storage::disk($scan->storage_disk)->exists($scan->stored_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Scan tidak dapat dicoba ulang.',
                'error' => ['code' => 'SCAN_NOT_RETRYABLE'],
            ], 409);
        }

        $scan = DB::transaction(function () use ($scan, $stateMachine): Scan {
            $locked = Scan::query()->whereKey($scan->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== ScanStatus::Failed) {
                abort(409, 'Scan tidak lagi berstatus failed.');
            }

            $attemptNumber = (int) $locked->attempts()->max('attempt_number') + 1;
            $locked->attempts()->create([
                'attempt_number' => $attemptNumber,
                'trigger' => ScanAttemptTrigger::ManualRetry,
                'status' => ScanAttemptStatus::Running,
                'triggered_by' => auth()->id(),
            ]);

            $locked->forceFill(['attempts_count' => $attemptNumber])->save();

            return $stateMachine->transition($locked, ScanStatus::Queued, [
                'error_code' => null,
                'error_message' => null,
                'result_label' => null,
                'confidence' => null,
                'queued_at' => now(),
                'started_at' => null,
                'completed_at' => null,
            ]);
        });

        ProcessScanJob::dispatch($scan->id)->afterCommit();
        $audit->record('scan.retried', 'scan', $scan->id, ['attempt_number' => $scan->attempts_count], severity: 'warn');

        return response()->json(['success' => true, 'data' => new ScanResource($scan)]);
    }
}
