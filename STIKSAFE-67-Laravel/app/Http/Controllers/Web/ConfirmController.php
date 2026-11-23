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
        $action = $request->query('action');
        abort_unless(in_array($action, ['cancel-scan', 'delete-scan'], true), 404);

        $scan = Scan::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('user_deleted_at')
            ->whereKey($request->query('target'))
            ->firstOrFail();

        if ($action === 'cancel-scan') {
            abort_if($scan->isTerminal(), 409);
        }

        return view('confirm', compact('action', 'scan'));
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
