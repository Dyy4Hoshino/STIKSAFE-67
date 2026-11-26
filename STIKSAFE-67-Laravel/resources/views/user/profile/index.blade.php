@extends('layouts.app')
@section('title', 'Profil | STIKSAFE-67')

@section('content')
<div class="profile-page">
    <div class="eyebrow">ACCOUNT / SETTINGS</div>
    <h1>Profil Anda.</h1>
    <p class="muted">Kelola identitas, foto profil, dan keamanan akun.</p>

    @if (session('status'))
        <p class="alert" role="status" style="border-color:#10b98166;background:#10b98111;color:#6ee7b7">{{ session('status') }}</p>
    @endif

    {{-- KARTU INFO AKUN & FOTO PROFIL --}}
    <section class="panel profile-card">
        <div class="profile-heading">
            <div class="avatar-wrap">
                @if ($user->avatar_path)
                    <img src="{{ asset('storage/' . $user->avatar_path) }}" alt="Foto profil" class="avatar-img">
                @else
                    <div class="avatar" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                @endif
            </div>
            <div style="flex-grow:1">
                <h2 style="margin:0 0 4px">{{ $user->name }}</h2>
                <p class="muted" style="margin:0">{{ $user->email }}</p>
                <div style="margin-top:10px">
                    <span class="eyebrow" style="color:{{ $user->role === 'admin' ? '#FF8A38' : '#10B981' }}">
                        ROLE: {{ strtoupper($user->role ?? 'USER') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- FORM UPLOAD FOTO PROFIL --}}
        <div style="margin-top:20px;padding-top:18px;border-top:1px solid rgba(148,163,184,.14)">
            <h3 style="margin:0 0 6px;font-size:15px">Ganti Foto Profil</h3>
            <p class="muted" style="font-size:12px;margin:0 0 12px">Pilih file gambar (JPG, PNG, atau WebP, maks. 2 MB) untuk ditampilkan di pojok kanan atas.</p>
            <form method="post" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                @csrf
                <input type="file" name="avatar" id="avatar" required accept="image/png,image/jpeg,image/webp" style="background:#0b1120;border:1px solid rgba(70,130,180,.35);padding:8px 12px;border-radius:8px;font-size:12px;color:#cbd5e1">
                <button class="btn primary" type="submit">Unggah Foto</button>
            </form>
            @error('avatar')<p class="error" style="margin-top:6px">{{ $message }}</p>@enderror
        </div>

        <dl class="account-details">
            <div>
                <dt>Nama Lengkap</dt>
                <dd>{{ $user->name }}</dd>
            </div>
            <div>
                <dt>Alamat Email</dt>
                <dd>{{ $user->email }}</dd>
            </div>
            <div>
                <dt>Bergabung Sejak</dt>
                <dd>
                    @if ($user->created_at)
                        <time datetime="{{ $user->created_at->toISOString() }}">{{ $user->created_at->timezone(config('app.timezone'))->format('d M Y') }}</time>
                    @else
                        —
                    @endif
                </dd>
            </div>
        </dl>
    </section>

    {{-- UBAH NAMA --}}
    <section class="panel profile-card">
        <h2>Ubah nama</h2>
        <p class="muted section-description">Perbarui nama lengkap yang ditampilkan di akun Anda.</p>
        <form method="post" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="name">NAMA LENGKAP</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="120">
                @error('name')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="actions">
                <button class="btn primary" type="submit">Simpan nama</button>
            </div>
        </form>
    </section>

    {{-- GANTI KATA SANDI --}}
    <section class="panel profile-card">
        <h2>Ganti kata sandi</h2>
        <p class="muted section-description">Gunakan kata sandi unik untuk menjaga keamanan akun Anda.</p>
        <form method="post" action="{{ route('profile.password') }}">
            @csrf
            @method('PUT')
            <div class="field-stack">
                <div class="field">
                    <label for="current_password">KATA SANDI SAAT INI</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password">
                    @error('current_password')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password">KATA SANDI BARU</label>
                    <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password">
                    <small>Minimal 12 karakter.</small>
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">KONFIRMASI KATA SANDI</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password">
                </div>
            </div>
            <p class="muted security-note">Mengganti kata sandi dapat mengakhiri sesi aktif di perangkat lain.</p>
            <button class="btn primary" type="submit">Perbarui kata sandi</button>
        </form>
    </section>

    {{-- TOMBOL KELUAR & GANTI AKUN DI PALING BAWAH --}}
    <section class="profile-actions">
        <p class="eyebrow">SESI & AKSES</p>
        <p class="muted">Selesai menggunakan akun ini? Keluar atau beralih ke akun lain.</p>
        <div class="actions actions-bottom">
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="btn danger" type="submit">Log out</button>
            </form>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button class="btn" type="submit">Ganti akun</button>
            </form>
        </div>
    </section>
</div>

<style>
    .profile-page {
        width: min(100%, 760px);
        margin: 0 auto;
        padding: 10px 0 48px;
    }
    .profile-card {
        margin-top: 20px;
        padding: 24px;
    }
    .profile-heading {
        display: flex;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
    }
    .avatar-wrap {
        flex: 0 0 64px;
        width: 64px;
        height: 64px;
    }
    .avatar-img {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #FF6700;
        box-shadow: 0 0 16px rgba(255,103,0,.35);
    }
    .avatar {
        display: grid;
        place-items: center;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        color: #fff;
        background: linear-gradient(135deg, #FF6700, #a33b00);
        font-size: 24px;
        font-weight: 700;
        border: 2px solid rgba(255,103,0,.5);
    }
    .account-details {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
        margin: 22px 0 0;
        padding-top: 18px;
        border-top: 1px solid rgba(148,163,184,.14);
    }
    .account-details dt {
        color: #91a0b4;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .account-details dd {
        margin: 5px 0 0;
        color: #f8fafc;
        font-size: 14px;
        overflow-wrap: anywhere;
    }
    .section-description { margin: -8px 0 20px; font-size: 13px; }
    .field-stack { display: grid; gap: 16px; }
    .security-note { margin: 18px 0; font-size: 12px; }
    .profile-actions {
        margin-top: 28px;
        padding: 24px;
        text-align: center;
        border: 1px solid rgba(148,163,184,.18);
        border-radius: 12px;
        background: rgba(15,23,42,.45);
    }
    .profile-actions .eyebrow { margin-bottom: 6px; }
    .profile-actions p { margin: 0 0 12px; }
    .actions-bottom { justify-content: center; margin-top: 18px; }
    .actions-bottom form { margin: 0; }
    .btn.danger { border-color: rgba(239,68,68,.6); color: #fca5a5; }
    @media (max-width: 650px) {
        .profile-page { padding: 0 0 32px; }
        .profile-card, .profile-actions { padding: 18px; }
        .account-details { grid-template-columns: 1fr; gap: 12px; }
        .actions-bottom { flex-direction: column; align-items: stretch; }
        .actions-bottom form, .actions-bottom .btn { width: 100%; }
    }
</style>
@endsection
