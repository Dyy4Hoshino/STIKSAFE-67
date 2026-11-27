@extends('layouts.auth')
@section('title', 'Lupa Kata Sandi | STIKSAFE-67')

@section('content')
<div class="auth-form-box">
    <span class="eyebrow">ACCOUNT RECOVERY / 01</span>
    <h1>Atur ulang<br><span>kata sandi.</span></h1>
    <p class="muted" style="margin:0 0 22px">Masukkan email akun. Jika terdaftar, kami kirim tautan untuk membuat kata sandi baru.</p>

    @if (session('status'))
        <p class="alert alert-success" role="status">{{ session('status') }}</p>
    @endif

    <form method="post" action="{{ route('password.email') }}">
        @csrf
        <div class="field">
            <label for="email">EMAIL AKUN</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="254" autocomplete="email" aria-describedby="email-error">
            @error('email')
                <span class="error" id="email-error">{{ $message }}</span>
            @enderror
        </div>
        <div class="actions">
            <button class="btn primary" type="submit">Kirim tautan →</button>
            <a class="btn" href="{{ route('login') }}">Kembali masuk</a>
        </div>
    </form>
</div>
@endsection
