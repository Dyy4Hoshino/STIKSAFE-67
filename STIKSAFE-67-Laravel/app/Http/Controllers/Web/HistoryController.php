<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function destroy(Request $request, string $id): RedirectResponse
    {
        $scan = Scan::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('user_deleted_at')
            ->whereKey($id)
            ->firstOrFail();

        if ($scan->stored_path && $scan->file_purged_at === null && ! Storage::disk($scan->storage_disk)->delete($scan->stored_path)) {
            return back()->withErrors(['delete' => 'File asli belum dapat dihapus. Riwayat tetap dipertahankan; coba lagi.']);
        }

        $scan->forceFill([
            'stored_path' => null,
            'file_purged_at' => now(),
            'user_deleted_at' => now(),
        ])->save();

        return redirect()->route('history')->with('success', 'Riwayat scan dihapus. File asli dipurge segera.');
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Scan::query()
            ->where('user_id', $user->getKey())
            ->whereNull('user_deleted_at');

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($sub) use ($q): void {
                $sub->where('original_filename', 'like', "%{$q}%")
                    ->orWhere('sha256', 'like', "{$q}%");
            });
        }

        if ($request->filled('filter.status')) {
            $query->where('status', $request->string('filter.status')->toString());
        }

        if ($request->filled('filter.result')) {
            $query->where('result_label', $request->string('filter.result')->toString());
        }

        if ($request->filled('filter.from')) {
            $query->whereDate('created_at', '>=', $request->string('filter.from')->toString());
        }

        if ($request->filled('filter.to')) {
            $query->whereDate('created_at', '<=', $request->string('filter.to')->toString());
        }

        $sort = $request->string('sort', '-created_at')->toString();
        if ($sort === 'created_at') {
            $query->orderBy('created_at', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $scans = $query->paginate(20)->withQueryString();

        return view('user.history.index', compact('scans'));
    }
}
