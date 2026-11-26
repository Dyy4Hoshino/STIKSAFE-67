<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function landingOrDashboard(Request $request)
    {
        if ($request->user()) {
            return $this->index($request);
        }

        return view('public.landing');
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $baseQuery = Scan::query()
            ->where('user_id', $user->getKey())
            ->whereNull('user_deleted_at');

        $totalScan = (clone $baseQuery)->count();

        $counts = (clone $baseQuery)
            ->selectRaw("
                COUNT(CASE WHEN status = 'completed' AND result_label = 'safe' THEN 1 END) as safe_count,
                COUNT(CASE WHEN status = 'completed' AND result_label = 'suspicious' THEN 1 END) as suspicious_count,
                COUNT(CASE WHEN status = 'completed' AND result_label = 'malicious' THEN 1 END) as malicious_count,
                COUNT(CASE WHEN status = 'completed' AND result_label = 'unknown' THEN 1 END) as unknown_count,
                COUNT(CASE WHEN status IN ('queued', 'processing') THEN 1 END) as running_count,
                COUNT(CASE WHEN status IN ('failed', 'cancelled', 'rejected') THEN 1 END) as incomplete_count
            ")
            ->first();

        $recentScans = (clone $baseQuery)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('user.dashboard', [
            'user' => $user,
            'totalScan' => $totalScan,
            'safeCount' => $counts->safe_count ?? 0,
            'suspiciousCount' => $counts->suspicious_count ?? 0,
            'maliciousCount' => $counts->malicious_count ?? 0,
            'unknownCount' => $counts->unknown_count ?? 0,
            'runningCount' => $counts->running_count ?? 0,
            'incompleteCount' => $counts->incomplete_count ?? 0,
            'recentScans' => $recentScans,
        ]);
    }
}
