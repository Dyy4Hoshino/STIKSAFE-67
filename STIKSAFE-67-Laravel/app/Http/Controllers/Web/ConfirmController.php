<?php

namespace App\Http\Controllers\Web;

use App\Enums\ScanStatus;
use App\Http\Controllers\Controller;
use App\Models\Scan;
use App\Services\ScanStateMachine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConfirmController extends Controller
{
    public function show(Request $request): View
    {
        abort_unless($request->query('action') === 'cancel-scan', 404);

        $scan = Scan::query()
            ->where('user_id', $request->user()->getKey())
            ->whereKey($request->query('target'))
            ->firstOrFail();

        abort_if($scan->isTerminal(), 409);

        return view('confirm', compact('scan'));
    }

    public function cancelScan(Request $request, string $id, ScanStateMachine $stateMachine): RedirectResponse
    {
        $scan = Scan::query()
            ->where('user_id', $request->user()->getKey())
            ->whereKey($id)
            ->firstOrFail();

        abort_if($scan->isTerminal(), 409);

        $stateMachine->transition($scan, ScanStatus::Cancelled, [
            'cancel_requested_at' => now(),
        ]);

        return redirect()->route('scans.show', $scan)->with('success', 'Scan dibatalkan.');
    }
}
