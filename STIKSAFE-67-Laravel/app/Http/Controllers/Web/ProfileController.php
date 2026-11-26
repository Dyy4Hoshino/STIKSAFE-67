<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        return view('user.profile.index', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $request->user()->update([
            'name' => trim($validated['name']),
        ]);

        return redirect()->route('profile')->with('success', 'Nama berhasil diperbarui.');
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'avatar.image' => 'Foto profil harus berupa gambar yang valid.',
            'avatar.mimes' => 'Format foto harus JPG, PNG, atau WebP.',
            'avatar.max' => 'Ukuran foto maksimum 2 MB.',
        ]);

        $user = $request->user();
        $oldPath = $user->avatar_path;
        $path = $validated['avatar']->store('avatars/'.$user->getKey(), 'public');
        $user->update(['avatar_path' => $path]);

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('profile')->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)->uncompromised()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile')->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
