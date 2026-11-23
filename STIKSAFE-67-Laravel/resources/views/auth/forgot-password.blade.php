@extends('layouts.app')
@section('title', 'Lupa Kata Sandi | STIKSAFE-67')
@section('content')
<div style="max-width:520px;margin:35px auto">
    <section class="panel">
        <span class="eyebrow">ACCOUNT RECOVERY / 01</span>
        <h1>Atur ulang<br><span style="color:#FF6700">kata sandi.</span></h1>
        <p class="muted">Masukkan email akun. Jika terdaftar, kami kirim tautan untuk membuat kata sandi baru.</p>

        @if (session('status'))
            <p class="alert" role="status" style="border-color:#10b98166;background:#10b98111;color:#6ee7b7">{{ session('status') }}</p>
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
    </section>
</div>
@endsection
