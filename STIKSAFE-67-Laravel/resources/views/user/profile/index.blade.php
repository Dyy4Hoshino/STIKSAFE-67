@extends('layouts.app')
@section('title', 'Profil | STIKSAFE-67')

@section('content')
<div class="profile-page">
    <div class="eyebrow">PENGATURAN AKUN</div>
    <h1>Profil Anda.</h1>
    <p class="muted" style="margin:0 0 18px">Kelola identitas, foto profil, tampilan, dan keamanan akun.</p>

    <section class="panel theme-preference" aria-labelledby="theme-heading">
        <div>
            <h2 id="theme-heading" style="margin:0 0 3px">Tampilan website</h2>
            <p class="muted" style="margin:0;font-size:13px">Pilih tema yang nyaman. Pengaturan tersimpan di browser ini.</p>
        </div>
        <button class="btn" type="button" id="theme-toggle" aria-pressed="false">
            <span class="material-symbols-outlined" id="theme-icon" aria-hidden="true">dark_mode</span>
            <span id="theme-label">Aktifkan dark mode</span>
        </button>
    </section>

    @if (session('status'))
        <p class="alert alert-success" role="status">{{ session('status') }}</p>
    @endif

    <section class="panel profile-card">
        <div class="profile-heading">
            <div class="avatar-wrap">
                @if ($user->avatar_path)
                    <img src="{{ asset('storage/' . $user->avatar_path) }}" alt="Foto profil" class="avatar-img">
                @else
                    <div class="avatar" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                @endif
            </div>
            <div style="flex:1;min-width:0">
                <h2 style="margin:0 0 2px">{{ $user->name }}</h2>
                <p class="muted" style="margin:0">{{ $user->email }}</p>
                <div style="margin-top:8px">
                    <span class="role-badge {{ $user->role === 'admin' ? 'role-admin' : 'role-user' }}">
                        ROLE: {{ strtoupper($user->role ?? 'USER') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="avatar-upload-box">
            <h3 style="margin:0 0 4px;font-size:14.5px">Ganti Foto Profil</h3>
            <p class="muted" style="font-size:12.5px;margin:0 0 12px">Pilih gambar (JPG, PNG, atau WebP, maks. 2 MB).</p>
            <form method="post" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                @csrf
                <input type="file" name="avatar" id="avatar" required accept="image/png,image/jpeg,image/webp" class="file-picker">
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

    <section class="panel profile-card">
        <h2 style="margin:0 0 4px">Ubah nama</h2>
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

    <section class="panel profile-card">
        <h2 style="margin:0 0 4px">Ganti kata sandi</h2>
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
            <p class="muted" style="margin:14px 0;font-size:12.5px">Mengganti kata sandi dapat mengakhiri sesi aktif di perangkat lain.</p>
            <button class="btn primary" type="submit">Perbarui kata sandi</button>
        </form>
    </section>

    <section class="panel profile-actions">
        <span class="eyebrow">SESI &amp; AKSES</span>
        <p class="muted" style="margin:6px 0 0">Selesai menggunakan akun ini? Keluar atau beralih ke akun lain.</p>
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
.theme-preference { display:flex; justify-content:space-between; align-items:center; gap:16px; margin:18px 0; }
@media (max-width:520px) { .theme-preference { align-items:stretch; flex-direction:column; } }
.profile-page { width: min(100%, 740px); margin: 0 auto; }
.profile-card { margin-top: 18px; }
.profile-heading { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
.avatar-wrap { flex: 0 0 60px; width: 60px; height: 60px; }
.avatar-img { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #e6e8ea; }
.avatar { display: grid; place-items: center; width: 60px; height: 60px; border-radius: 50%; color: #ffffff; background: #a33900; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 22px; font-weight: 700; }
.role-badge { display: inline-block; padding: 3px 9px; border-radius: 999px; font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 600; }
.role-admin { background: #fff3ed; color: #a33900; border: 1px solid #ffdbce; }
.role-user { background: #ecfdf5; color: #006948; border: 1px solid #a7f3d0; }
.avatar-upload-box { margin-top: 20px; padding-top: 18px; border-top: 1px solid #eceef0; }
.file-picker { background: #f7f9fb; border: 1px solid #d8dadc; padding: 7px 10px; border-radius: 6px; font-size: 12.5px; color: #191c1e; }
.account-details { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin: 20px 0 0; padding-top: 18px; border-top: 1px solid #eceef0; }
.account-details dt { color: #565e74; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }
.account-details dd { margin: 4px 0 0; color: #191c1e; font-size: 13.5px; font-weight: 500; overflow-wrap: anywhere; }
.section-description { margin: 0 0 18px; font-size: 13.5px; }
.field-stack { display: grid; gap: 14px; }
.profile-actions { margin-top: 18px; text-align: center; }
.actions-bottom { justify-content: center; margin-top: 16px; }
.actions-bottom form { margin: 0; }
@media (max-width: 640px) {
    .account-details { grid-template-columns: 1fr; gap: 12px; }
    .actions-bottom { flex-direction: column; align-items: stretch; }
    .actions-bottom form, .actions-bottom .btn { width: 100%; }
}
</style>
@endsection
