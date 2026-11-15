<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScanRequest;
use App\Http\Resources\ScanResource;
use App\Models\Scan;
use App\Services\CreateScanService;
use App\Services\ScanStateMachine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ScanController extends Controller
{
    public function store(StoreScanRequest $request, CreateScanService $createScan): JsonResponse
    {
        $scan = $createScan->create(
            $request->user()->getKey(),
            $request->file('file'),
        );

        return (new ScanResource($scan))
            ->additional([
                'success' => true,
                'message' => 'File diterima dan masuk antrean pemindaian.',
                'meta' => [
                    'request_id' => $request->header('X-Request-ID'),
                ],
            ])
            ->response()
            ->setStatusCode(202);
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Scan::query()
            ->where('user_id', $user->getKey())
            ->whereNull('user_deleted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('result')) {
            $query->where('result_label', $request->string('result')->toString());
        }

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($sub) use ($q): void {
                $sub->where('original_filename', 'like', "%{$q}%")
                    ->orWhere('sha256', 'like', "{$q}%");
            });
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $scans = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ScanResource::collection($scans)
            ->additional([
                'success' => true,
                'meta' => [
                    'request_id' => $request->header('X-Request-ID'),
                ],
            ])
            ->response();
    }

    public function show(Request $request, string $id): ScanResource
    {
        $scan = Scan::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('user_deleted_at')
            ->whereKey($id)
            ->firstOrFail();

        return new ScanResource($scan);
    }

    public function status(Request $request, string $id): JsonResponse
    {
        $scan = Scan::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('user_deleted_at')
            ->whereKey($id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $scan->id,
                'status' => $scan->status?->value,
                'result_label' => $scan->result_label?->value,
                'is_terminal' => $scan->isTerminal(),
                'poll_after_ms' => $scan->isTerminal() ? 0 : 2000,
                'updated_at' => $scan->updated_at?->toISOString(),
            ],
            'meta' => [
                'request_id' => $request->header('X-Request-ID'),
            ],
        ]);
    }

    public function cancel(Request $request, string $id, ScanStateMachine $stateMachine): JsonResponse
    {
        $scan = Scan::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('user_deleted_at')
            ->whereKey($id)
            ->firstOrFail();

        if ($scan->isTerminal()) {
            return response()->json([
                'success' => false,
                'message' => 'Scan sudah berada di status terminal.',
                'error' => ['code' => 'SCAN_INVALID_STATE'],
            ], 409);
        }

        $stateMachine->transition($scan, \App\Enums\ScanStatus::Cancelled, [
            'cancel_requested_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Scan dibatalkan.',
            'data' => new ScanResource($scan->refresh()),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $scan = Scan::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('user_deleted_at')
            ->whereKey($id)
            ->firstOrFail();

        if ($scan->stored_path && $scan->file_purged_at === null) {
            Storage::disk($scan->storage_disk)->delete($scan->stored_path);
        }

        $scan->forceFill([
            'stored_path' => null,
            'file_purged_at' => now(),
            'user_deleted_at' => now(),
        ])->save();

        return response()->json(null, 204);
    }
}
