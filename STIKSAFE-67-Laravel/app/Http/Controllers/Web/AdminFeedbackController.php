<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ScanFeedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminFeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['new', 'reviewed'])],
            'type' => ['nullable', Rule::in(['false_positive', 'false_negative'])],
        ]);
        $query = ScanFeedback::query()->with(['scan:id,original_filename,result_label', 'user:id,name,email', 'reviewer:id,name']);

        if (($filters['status'] ?? null) === 'reviewed') {
            $query->whereNotNull('reviewed_at');
        }

        if (($filters['status'] ?? null) === 'new') {
            $query->whereNull('reviewed_at');
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return view('admin.feedback.index', [
            'feedback' => $query->latest()->paginate(25)->withQueryString(),
        ]);
    }

    public function review(Request $request, ScanFeedback $feedback): RedirectResponse
    {
        abort_if($feedback->reviewed_at !== null, 409, 'Feedback ini sudah ditinjau.');

        $feedback->forceFill([
            'reviewed_by' => $request->user()->getKey(),
            'reviewed_at' => now(),
            'status' => 'reviewed',
        ])->save();

        return back()->with('success', 'Feedback ditandai telah ditinjau.');
    }
}
