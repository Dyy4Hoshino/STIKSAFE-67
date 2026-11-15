<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AuditLog::query()->orderByDesc('id');

        foreach (['actor_user_id', 'action', 'target_type', 'target_id', 'outcome', 'severity'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        if ($request->filled('from')) {
            $query->where('occurred_at', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->where('occurred_at', '<=', $request->date('to')?->endOfDay());
        }

        $logs = $query->cursorPaginate(min(max((int) $request->input('per_page', 50), 1), 100));

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'meta' => [
                'next_cursor' => $logs->nextCursor()?->encode(),
                'prev_cursor' => $logs->previousCursor()?->encode(),
                'per_page' => $logs->perPage(),
            ],
        ]);
    }
}
