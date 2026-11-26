<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminNotificationBroadcastController extends Controller
{
    public function create(): View
    {
        return view('admin.notifications.create', [
            'users' => User::query()->select('id', 'name', 'email', 'role')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'target' => ['required', 'in:all_users,all_admins,all,custom'],
            'user_ids' => ['required_if:target,custom', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $query = User::query();

        if ($validated['target'] === 'all_users') {
            $query->where('role', 'user');
        } elseif ($validated['target'] === 'all_admins') {
            $query->where('role', 'admin');
        } elseif ($validated['target'] === 'custom') {
            $query->whereIn('id', $validated['user_ids']);
        }

        $users = $query->get();

        foreach ($users as $user) {
            $user->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\AdminAnnouncementNotification',
                'data' => [
                    'title' => $validated['title'],
                    'message' => $validated['message'],
                    'sent_by' => $request->user()->name,
                ],
            ]);
        }

        return redirect()->route('admin.dashboard')->with('success', 'Notifikasi berhasil dikirim ke '.$users->count().' pengguna.');
    }
}
