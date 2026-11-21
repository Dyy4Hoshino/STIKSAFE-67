<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $usersByStatus = User::query()->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');
        $scansByStatus = Scan::query()->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');
        $scansByResult = Scan::query()->whereNotNull('result_label')->selectRaw('result_label, count(*) as count')->groupBy('result_label')->pluck('count', 'result_label');
        $dailyScans = Scan::query()->where('created_at', '>=', now()->subDays(30))->selectRaw('date(created_at) as date, count(*) as count')->groupBy('date')->orderBy('date')->get();

        return view('admin.dashboard', [
            'usersByStatus' => $usersByStatus,
            'scansByStatus' => $scansByStatus,
            'scansByResult' => $scansByResult,
            'dailyScans' => $dailyScans,
            'queueDepth' => DB::table('jobs')->count(),
            'updatedAt' => now(),
        ]);
    }
}
