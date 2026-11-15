<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email:rfc', 'max:254']]);

        $throttleKey = 'forgot:'.strtolower($request->string('email')->toString()).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return response()->json([
                'success' => true,
                'message' => 'Jika email terdaftar, tautan reset password telah dikirim.',
            ], 202);
        }

        RateLimiter::hit($throttleKey, 3600);

        $user = User::query()->where('email', strtolower(trim($request->string('email')->toString())))->first();

        if ($user) {
            $token = \Illuminate\Support\Str::random(64);

            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->upsert([
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ], uniqueBy: ['email']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Jika email terdaftar, tautan reset password telah dikirim.',
        ], 202);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email:rfc', 'lowercase'],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(12)->uncompromised()],
        ]);

        $email = strtolower(trim($request->string('email')->toString()));
        $record = \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->first();

        if (! $record || ! Hash::check($request->string('token')->toString(), $record->token)) {
            throw ValidationException::withMessages(['email' => ['Token reset tidak valid atau sudah kedaluwarsa.']]);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->delete();
            throw ValidationException::withMessages(['email' => ['Token reset sudah kedaluwarsa.']]);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return response()->json(['success' => true, 'message' => 'Password berhasil direset.']);
        }

        $user->forceFill(['password' => $request->string('password')->toString()])->save();
        $user->tokens()->delete();

        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json(['success' => true, 'message' => 'Password berhasil direset. Silakan login kembali.']);
    }
}
