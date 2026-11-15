<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->orderByDesc('created_at');

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }

        foreach (['role', 'status'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->string($field)->toString());
            }
        }

        return UserResource::collection($query->paginate(min((int) $request->input('per_page', 20), 100)))
            ->additional(['success' => true])
            ->response();
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function updateStatus(Request $request, User $user, AuditLogService $audit): JsonResponse
    {
        abort_if($request->user()->is($user), 403, 'Admin tidak dapat mengubah status dirinya sendiri.');

        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'suspended', 'inactive'])],
            'reason' => ['required_if:status,suspended', 'nullable', 'string', 'max:255'],
        ]);

        if ($user->role === 'admin' && $user->status === 'active' && $data['status'] !== 'active'
            && User::query()->where('role', 'admin')->where('status', 'active')->count() <= 1) {
            return response()->json(['success' => false, 'message' => 'Admin aktif terakhir tidak dapat dinonaktifkan.', 'error' => ['code' => 'AUTH_FORBIDDEN']], 409);
        }

        $before = $user->status;
        $user->forceFill([
            'status' => $data['status'],
            'status_reason' => $data['status'] === 'suspended' ? $data['reason'] : null,
        ])->save();

        if ($data['status'] !== 'active') {
            $user->tokens()->delete();
        }

        $audit->record('account.'.($data['status'] === 'suspended' ? 'suspended' : 'reactivated'), 'user', $user->id, [
            'status_before' => $before,
            'status_after' => $data['status'],
            'reason' => $data['reason'] ?? null,
        ], severity: 'warn');

        return response()->json(['success' => true, 'data' => new UserResource($user->refresh())]);
    }

    public function updateRole(Request $request, User $user, AuditLogService $audit): JsonResponse
    {
        abort_if($request->user()->is($user), 403, 'Admin tidak dapat mengubah role dirinya sendiri.');

        $data = $request->validate(['role' => ['required', Rule::in(['user', 'admin'])]]);

        if ($user->role === 'admin' && $user->status === 'active' && $data['role'] !== 'admin'
            && User::query()->where('role', 'admin')->where('status', 'active')->count() <= 1) {
            return response()->json(['success' => false, 'message' => 'Admin aktif terakhir tidak dapat diturunkan.', 'error' => ['code' => 'AUTH_FORBIDDEN']], 409);
        }

        $before = $user->role;
        $user->forceFill(['role' => $data['role']])->save();
        $user->tokens()->delete();

        $audit->record('account.role.changed', 'user', $user->id, [
            'role_before' => $before,
            'role_after' => $data['role'],
        ], severity: 'critical');

        return response()->json(['success' => true, 'data' => new UserResource($user->refresh())]);
    }
}
