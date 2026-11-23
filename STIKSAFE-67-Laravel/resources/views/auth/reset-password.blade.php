@extends('layouts.app')
@section('title', 'Reset Kata Sandi | STIKSAFE-67')
@section('content')
<div style="max-width:560px;margin:35px auto">
    <section class="panel">
        <span class="eyebrow">ACCOUNT RECOVERY / 02</span>
        <h1>Buat kata sandi<br><span style="color:#FF6700">baru.</span></h1>
        <p class="muted">Gunakan kata sandi baru minimal 12 karakter. Tautan reset hanya dapat digunakan sekali.</p>
        <form method="post" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="field">
                <label for="email">EMAIL AKUN</label>
                <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required maxlength="254" autocomplete="email" aria-describedby="email-error">
                @error('email')<span class="error" id="email-error">{{ $message }}</span>@enderror
            </div>
            <div class="field" style="margin-top:16px">
                <label for="password">KATA SANDI BARU</label>
                <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password" aria-describedby="password-error password-hint">
                <small id="password-hint">Minimal 12 karakter.</small>
                @error('password')<span class="error" id="password-error">{{ $message }}</span>@enderror
            </div>
            <div class="field" style="margin-top:16px">
                <label for="password_confirmation">KONFIRMASI KATA SANDI</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password">
            </div>
            <div class="actions">
                <button class="btn primary" type="submit">Simpan kata sandi →</button>
                <a class="btn" href="{{ route('login') }}">Kembali masuk</a>
            </div>
        </form>
    </section>
</div>
@endsection
