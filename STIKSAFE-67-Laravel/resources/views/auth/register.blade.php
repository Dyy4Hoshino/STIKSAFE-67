@extends('layouts.auth')
@section('title', 'Daftar | STIKSAFE-67')

@section('auth_topbar_right')
    <a class="auth-back" href="{{ route('login') }}" style="color:#a33900;font-weight:600">Sudah punya akun</a>
@endsection

@section('content')
<div class="auth-form-box wide">
    <span class="eyebrow">CREATE ACCOUNT / 02</span>
    <h1>Buat akun<br><span>STIKSAFE.</span></h1>
    <p class="muted" style="margin:0 0 22px">Simpan riwayat scan dan akses hasil pemeriksaan Anda.</p>

    @if($errors->any())
        <div class="alert" role="alert">
            <ul style="margin:0;padding-left:18px">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('register.submit') }}">
        @csrf
        <div class="form-grid">
            <div class="field full">
                <label for="name">NAMA LENGKAP</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="120" autocomplete="name">
                @error('name')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field full">
                <label for="email">EMAIL</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="254" autocomplete="email">
                @error('email')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="password">KATA SANDI</label>
                <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password">
                <small>Minimal 12 karakter.</small>
                @error('password')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="field">
                <label for="password_confirmation">KONFIRMASI KATA SANDI</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password">
            </div>
        </div>

        <label class="muted" style="display:flex;align-items:flex-start;gap:8px;margin-top:18px;font-size:12.5px;cursor:pointer">
            <input id="accept_terms" name="accept_terms" type="checkbox" value="1" required style="margin-top:3px;accent-color:#a33900">
            <span>Saya menyetujui syarat penggunaan layanan STIKSAFE-67</span>
        </label>
        @error('accept_terms')<span class="error">{{ $message }}</span>@enderror

        <div class="actions">
            <button class="btn primary" type="submit" style="flex:1">Buat akun →</button>
            <a class="btn" href="{{ route('login') }}">Sudah punya akun</a>
        </div>
    </form>
</div>
@endsection
