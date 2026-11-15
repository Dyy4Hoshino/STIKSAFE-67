<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function systemDashboard(): JsonResponse
    {
        $usersByStatus = User::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $scansByStatus = Scan::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $scansByResult = Scan::query()
            ->whereNotNull('result_label')
            ->selectRaw('result_label, count(*) as count')
            ->groupBy('result_label')
            ->pluck('count', 'result_label')
            ->toArray();

        $dailyScans = Scan::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('date(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $queueDepth = DB::table('jobs')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'users' => [
                    'total' => User::query()->count(),
                    'by_status' => $usersByStatus,
                ],
                'scans' => [
                    'total' => Scan::query()->count(),
                    'by_status' => $scansByStatus,
                    'by_result' => $scansByResult,
                ],
                'queue' => [
                    'depth' => $queueDepth,
                ],
                'daily_scans_30_days' => $dailyScans,
            ],
        ]);
    }
}
