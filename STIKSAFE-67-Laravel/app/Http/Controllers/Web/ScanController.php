<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use App\Rules\ValidScanFile;
use App\Services\CreateScanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class ScanController extends Controller
{
    public function create(): View
    {
        return view('user.scan.create', [
            'idempotencyKey' => (string) Str::uuid(),
        ]);
    }

    public function store(Request $request, CreateScanService $createScan): RedirectResponse
    {
        $request->validate([
            'file' => ['bail', 'required', 'file', 'max:25600', new ValidScanFile()],
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.file' => 'Upload file tidak valid.',
            'file.max' => 'Ukuran file maksimum 25 MB.',
        ]);

        $scan = $createScan->create($request->user()->getKey(), $request->file('file'));

        return redirect()->route('scans.show', $scan)->with('success', 'File diterima dan masuk antrean.');
    }

    public function show(Request $request, string $id): View
    {
        $scan = Scan::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('user_deleted_at')
            ->whereKey($id)
            ->firstOrFail();

        return view('user.scan.show', compact('scan'));
    }

    public function cancel(Request $request, string $id): RedirectResponse
    {
        $scan = Scan::query()
            ->where('user_id', $request->user()->getKey())
            ->whereKey($id)
            ->firstOrFail();

        return redirect()->route('confirm', [
            'action' => 'cancel-scan',
            'target' => $scan->id,
        ]);
    }
}
