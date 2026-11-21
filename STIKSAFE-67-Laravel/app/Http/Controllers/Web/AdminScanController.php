<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminScanController extends Controller
{
    public function index(Request $request): View
    {
        $query = Scan::query()->with('user:id,name,email')->orderByDesc('created_at');

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(fn ($builder) => $builder->where('original_filename', 'like', "%{$q}%")->orWhere('id', 'like', "%{$q}%"));
        }

        foreach (['status' => 'status', 'result' => 'result_label', 'user' => 'user_id'] as $input => $column) {
            if ($request->filled("filter.{$input}")) {
                $query->where($column, $request->input("filter.{$input}"));
            }
        }

        if ($request->filled('filter.from')) {
            $query->whereDate('created_at', '>=', $request->input('filter.from'));
        }

        if ($request->filled('filter.to')) {
            $query->whereDate('created_at', '<=', $request->input('filter.to'));
        }

        return view('admin.scans.index', ['scans' => $query->paginate(20)->withQueryString()]);
    }

    public function show(string $id): View
    {
        $scan = Scan::query()->with(['user:id,name,email', 'attempts'])->findOrFail($id);

        return view('admin.scans.show', compact('scan'));
    }
}
