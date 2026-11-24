<?php

namespace App\Http\Controllers\Web;

use App\Enums\ScanAttemptStatus;
use App\Enums\ScanAttemptTrigger;
use App\Enums\ScanStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessScanJob;
use App\Models\Scan;
use App\Services\AuditLogService;
use App\Services\ScanStateMachine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminScanController extends Controller
{
    public function index(Request $request): View
    {
        $query = Scan::query()->with('user:id,name,email')->orderByDesc('created_at');

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(fn ($builder) => $builder->where('original_filename', 'like', "%{$q}%")->orWhere('id', 'like', "%{$q}%"));
        }

        foreach (['status' => 'status', 'result' => 'result_label', 'user' => 'user_id'] as $input => $column) {
            if ($request->filled("filter.{$input}")) {
                $query->where($column, $request->input("filter.{$input}"));
            }
        }

        if ($request->filled('filter.from')) {
            $query->whereDate('created_at', '>=', $request->input('filter.from'));
        }

        if ($request->filled('filter.to')) {
            $query->whereDate('created_at', '<=', $request->input('filter.to'));
        }

        return view('admin.scans.index', ['scans' => $query->paginate(20)->withQueryString()]);
    }

    public function show(string $id): View
    {
        $scan = Scan::query()->with(['user:id,name,email', 'attempts', 'feedback'])->findOrFail($id);

        return view('admin.scans.show', compact('scan'));
    }

    public function retry(string $id, ScanStateMachine $stateMachine, AuditLogService $audit): RedirectResponse
    {
        $scan = Scan::query()->findOrFail($id);
        if ($scan->status !== ScanStatus::Failed || $scan->stored_path === null
            || ! Storage::disk($scan->storage_disk)->exists($scan->stored_path)) {
            return back()->withErrors(['retry' => 'Scan tidak dapat dicoba ulang (bukan status failed atau file fisik sudah tidak tersedia).']);
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

        return back()->with('success', 'Scan dimasukkan kembali ke antrean untuk dicoba ulang.');
    }
}
