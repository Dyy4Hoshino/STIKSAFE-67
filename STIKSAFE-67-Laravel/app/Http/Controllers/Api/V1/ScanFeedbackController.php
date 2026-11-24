<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ScanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScanFeedbackRequest;
use App\Models\Scan;
use Illuminate\Http\JsonResponse;

class ScanFeedbackController extends Controller
{
    public function store(StoreScanFeedbackRequest $request, string $id): JsonResponse
    {
        $scan = Scan::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('user_deleted_at')
            ->whereKey($id)
            ->firstOrFail();

        if ($scan->status !== ScanStatus::Completed) {
            return response()->json([
                'success' => false,
                'message' => 'Umpan balik hanya tersedia setelah scan selesai.',
                'error' => ['code' => 'SCAN_NOT_COMPLETED'],
            ], 409);
        }

        if ($scan->feedback()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Umpan balik untuk scan ini sudah dikirim.',
                'error' => ['code' => 'FEEDBACK_EXISTS'],
            ], 409);
        }

        $feedback = $scan->feedback()->create([
            'user_id' => $request->user()->getKey(),
            'type' => $request->validated('type'),
            'comment' => $request->validated('comment'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Umpan balik berhasil dikirim.',
            'data' => [
                'id' => $feedback->id,
                'scan_id' => $scan->id,
                'type' => $feedback->type,
                'comment' => $feedback->comment,
                'status' => $feedback->status,
                'created_at' => $feedback->created_at?->toISOString(),
            ],
        ], 201);
    }
}
