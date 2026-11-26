<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ScanFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminFeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $query = ScanFeedback::query()->with(['scan:id,original_filename,result_label', 'user:id,name,email', 'reviewer:id,name']);

        if ($request->string('status')->toString() === 'reviewed') {
            $query->whereNotNull('reviewed_at');
        }

        if ($request->string('status')->toString() === 'new') {
            $query->whereNull('reviewed_at');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        return view('admin.feedback.index', [
            'feedback' => $query->latest()->paginate(25)->withQueryString(),
        ]);
    }

    public function review(Request $request, ScanFeedback $feedback): RedirectResponse
    {
        $feedback->update([
            'reviewed_by' => $request->user()->getKey(),
            'reviewed_at' => now(),
            'status' => 'reviewed',
        ]);

        return back()->with('success', 'Feedback ditandai telah ditinjau.');
    }
}
