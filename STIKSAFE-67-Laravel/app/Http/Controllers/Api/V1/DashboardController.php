<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function userDashboard(Request $request): JsonResponse
    {
        $userId = $request->user()->getKey();

        $totalScans = Scan::query()->where('user_id', $userId)->whereNull('user_deleted_at')->count();

        $byLabel = Scan::query()
            ->where('user_id', $userId)
            ->whereNull('user_deleted_at')
            ->selectRaw('result_label, count(*) as count')
            ->groupBy('result_label')
            ->pluck('count', 'result_label')
            ->toArray();

        $queuedOrProcessing = Scan::query()
            ->where('user_id', $userId)
            ->whereNull('user_deleted_at')
            ->whereIn('status', ['queued', 'processing'])
            ->count();

        $failed = Scan::query()
            ->where('user_id', $userId)
            ->whereNull('user_deleted_at')
            ->where('status', 'failed')
            ->count();

        $latest = Scan::query()
            ->where('user_id', $userId)
            ->whereNull('user_deleted_at')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($scan) => [
                'id' => $scan->id,
                'filename' => $scan->original_filename,
                'status' => $scan->status?->value,
                'result_label' => $scan->result_label?->value,
                'created_at' => $scan->created_at?->toISOString(),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'total_scans' => $totalScans,
                'counts_by_result' => [
                    'safe' => $byLabel['safe'] ?? 0,
                    'suspicious' => $byLabel['suspicious'] ?? 0,
                    'malicious' => $byLabel['malicious'] ?? 0,
                    'unknown' => $byLabel['unknown'] ?? 0,
                ],
                'active_scans' => $queuedOrProcessing,
                'failed_scans' => $failed,
                'recent_activity' => $latest,
            ],
        ]);
    }
}
