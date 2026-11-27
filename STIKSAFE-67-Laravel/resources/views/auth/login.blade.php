@extends('layouts.auth')
@section('title', 'Masuk | STIKSAFE-67')

@section('auth_topbar_right')
    <a class="auth-back" href="{{ route('register') }}" style="color:#a33900;font-weight:600">Buat akun baru</a>
@endsection

@section('content')
<div class="auth-form-box">
    <span class="eyebrow">SECURE ACCESS / 01</span>
    <h1>Masuk ke<br><span>STIKSAFE.</span></h1>
    <p class="muted" style="margin:0 0 24px">Lanjutkan pemeriksaan file dan pantau riwayat keamanan Anda.</p>

    @if(session('status'))
        <p class="alert alert-success" role="status">{{ session('status') }}</p>
    @endif
    @if($errors->any())
        <div class="alert" role="alert">
            <ul style="margin:0;padding-left:18px">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('login.submit') }}">
        @csrf
        <div class="field">
            <label for="email">EMAIL</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username">
            @error('email')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="field" style="margin-top:16px">
            <label for="password">KATA SANDI</label>
            <input id="password" name="password" type="password" required autocomplete="current-password">
            @error('password')<span class="error">{{ $message }}</span>@enderror
        </div>
        <div class="actions">
            <button class="btn primary" type="submit" style="flex:1">Masuk ke dashboard →</button>
            <a class="btn" href="{{ route('home') }}">Beranda</a>
        </div>
    </form>

    <p class="muted" style="font-size:13px;margin:18px 0 0">
        <a style="color:#a33900;font-weight:500" href="{{ route('password.request') }}">Lupa kata sandi?</a>
    </p>
    <p class="muted" style="font-size:13px;margin:14px 0 0">
        Belum punya akun? <a style="color:#a33900;font-weight:600" href="{{ route('register') }}">Buat akun baru</a>
    </p>
</div>
@endsection
