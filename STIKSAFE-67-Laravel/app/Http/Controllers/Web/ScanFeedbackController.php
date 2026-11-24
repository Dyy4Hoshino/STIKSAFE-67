<?php

namespace App\Http\Controllers\Web;

use App\Enums\ScanStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScanFeedbackRequest;
use App\Models\Scan;
use Illuminate\Http\RedirectResponse;

class ScanFeedbackController extends Controller
{
    public function store(StoreScanFeedbackRequest $request, string $id): RedirectResponse
    {
        $scan = Scan::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('user_deleted_at')
            ->whereKey($id)
            ->firstOrFail();

        abort_unless($scan->status === ScanStatus::Completed, 409, 'Umpan balik hanya tersedia setelah scan selesai.');
        abort_if($scan->feedback()->exists(), 409, 'Umpan balik untuk scan ini sudah dikirim.');

        $scan->feedback()->create([
            'user_id' => $request->user()->getKey(),
            'type' => $request->validated('type'),
            'comment' => $request->validated('comment'),
        ]);

        return redirect()->route('scans.show', $scan)->with('success', 'Umpan balik berhasil dikirim. Terima kasih.');
    }
}
