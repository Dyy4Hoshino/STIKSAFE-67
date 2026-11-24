<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query()->with('actor:id,name,email')->orderByDesc('id');

        foreach (['action', 'outcome', 'severity', 'actor_user_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('occurred_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('occurred_at', '<=', $request->input('to'));
        }

        return view('admin.audit-logs.index', [
            'logs' => $query->paginate(30)->withQueryString(),
        ]);
    }
}
