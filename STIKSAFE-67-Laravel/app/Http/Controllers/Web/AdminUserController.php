<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->orderByDesc('created_at');

        if ($request->filled('q')) {
            $term = $request->string('q')->toString();
            $query->where(fn ($builder) => $builder->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
        }

        foreach (['role', 'status'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }

        return view('admin.users.index', ['users' => $query->paginate(25)->withQueryString()]);
    }

    public function show(User $user): View
    {
        return view('admin.users.show', [
            'managedUser' => $user,
            'recentScans' => $user->scans()->latest()->limit(10)->get(),
        ]);
    }

    public function updateStatus(Request $request, User $user, AuditLogService $audit)
    {
        abort_if($request->user()->is($user), 403, 'Tidak dapat mengubah status akun sendiri.');
        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'suspended', 'inactive'])],
            'reason' => ['required_if:status,suspended', 'nullable', 'string', 'max:255'],
        ]);

        return DB::transaction(function () use ($request, $user, $data, $audit) {
            $locked = User::query()->lockForUpdate()->findOrFail($user->id);
            if ($locked->role === 'admin' && $locked->status === 'active' && $data['status'] !== 'active'
                && User::query()->where('role', 'admin')->where('status', 'active')->count() <= 1) {
                return back()->withErrors(['status' => 'Admin aktif terakhir tidak dapat dinonaktifkan.']);
            }

            $before = $locked->status;
            $locked->forceFill([
                'status' => $data['status'],
                'status_reason' => $data['status'] === 'suspended' ? ($data['reason'] ?? null) : null,
            ])->save();
            if ($data['status'] !== 'active') {
                $locked->tokens()->delete();
            }

            $audit->record('account.status.changed', 'user', $locked->id, [
                'status_before' => $before,
                'status_after' => $locked->status,
                'reason' => $data['reason'] ?? null,
            ], severity: 'warn', request: $request);

            return back()->with('success', 'Status akun diperbarui.');
        });
    }

    public function updateRole(Request $request, User $user, AuditLogService $audit)
    {
        abort_if($request->user()->is($user), 403, 'Tidak dapat mengubah role akun sendiri.');
        $data = $request->validate(['role' => ['required', Rule::in(['user', 'admin'])]]);

        return DB::transaction(function () use ($request, $user, $data, $audit) {
            $locked = User::query()->lockForUpdate()->findOrFail($user->id);
            if ($locked->role === 'admin' && $locked->status === 'active' && $data['role'] !== 'admin'
                && User::query()->where('role', 'admin')->where('status', 'active')->count() <= 1) {
                return back()->withErrors(['role' => 'Admin aktif terakhir tidak dapat diturunkan.']);
            }

            $before = $locked->role;
            $locked->forceFill(['role' => $data['role']])->save();
            $locked->tokens()->delete();
            $audit->record('account.role.changed', 'user', $locked->id, [
                'role_before' => $before,
                'role_after' => $locked->role,
            ], severity: 'critical', request: $request);

            return back()->with('success', 'Role akun diperbarui.');
        });
    }

    public function forceLogout(Request $request, User $user, AuditLogService $audit)
    {
        abort_if($request->user()->is($user), 403, 'Tidak dapat mencabut sesi akun sendiri dari halaman ini.');
        $user->tokens()->delete();
        $audit->record('account.sessions.revoked', 'user', $user->id, severity: 'warn', request: $request);

        return back()->with('success', 'Token API pengguna telah dicabut.');
    }
}
