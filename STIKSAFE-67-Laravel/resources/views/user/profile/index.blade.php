@extends('layouts.app')
@section('title', 'Profil | STIKSAFE-67')

@section('content')
@php
    $hasEditError = $errors->hasAny(['avatar', 'name', 'current_password', 'password']);
@endphp

<div class="profile-workspace">
    @if (session('status'))
        <p class="alert alert-success" role="status" style="margin-bottom:16px">{{ session('status') }}</p>
    @endif

    <div class="profile-layout">
        {{-- 1. Header Title --}}
        <div class="area-top">
            <div class="eyebrow">PENGATURAN AKUN</div>
            <h1>Profil Anda.</h1>
            <p class="muted" style="margin:0">Kelola identitas, foto profil, tampilan, dan keamanan akun.</p>
        </div>

        {{-- 2. Theme Toggle (Desktop: top-right | Mobile: right below Info Akun) --}}
        <div class="area-theme">
            <div class="theme-mobile-meta">
                <span class="eyebrow">TAMPILAN WEBSITE</span>
                <p class="muted" style="margin:2px 0 0;font-size:12.5px">Ganti mode terang atau gelap.</p>
            </div>
            <button class="btn profile-theme-btn" type="button" id="theme-toggle" aria-pressed="false">
                <span class="material-symbols-outlined" id="theme-icon" aria-hidden="true">dark_mode</span>
                <span id="theme-label">Aktifkan dark mode</span>
            </button>
        </div>

        {{-- 3. Info Akun Card --}}
        <section class="panel identity-card area-identity">
            <div class="profile-heading">
                <div class="avatar-wrap">
                    @if ($user->avatar_path)
                        <img src="{{ asset('storage/' . $user->avatar_path) }}" alt="Foto profil" class="avatar-img">
                    @else
                        <div class="avatar" aria-hidden="true">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    @endif
                </div>
                <div class="identity-meta">
                    <h2>{{ $user->name }}</h2>
                    <p class="muted">{{ $user->email }}</p>
                    <span class="role-badge {{ $user->role === 'admin' ? 'role-admin' : 'role-user' }}">
                        ROLE: {{ strtoupper($user->role ?? 'USER') }}
                    </span>
                </div>
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

        {{-- 4. Edit Profil Forms (Desktop: always open on right | Mobile: collapsible dropdown) --}}
        <div class="area-edit {{ $hasEditError ? 'is-open' : '' }}" id="mobile-edit-dropdown">
            <button
                type="button"
                class="panel edit-dropdown-trigger"
                id="edit-profile-toggle"
                aria-expanded="{{ $hasEditError ? 'true' : 'false' }}"
                aria-controls="edit-profile-body"
            >
                <span class="edit-trigger-left">
                    <span class="edit-trigger-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                    </span>
                    <span>
                        <strong class="edit-trigger-title">Edit Profil</strong>
                        <small class="edit-trigger-sub">Foto profil, ubah nama &amp; ganti kata sandi</small>
                    </span>
                </span>
                <svg class="edit-trigger-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
            </button>

            <div class="edit-dropdown-body" id="edit-profile-body">
                <section class="panel">
                    <div class="card-head">
                        <span class="eyebrow">FOTO PROFIL</span>
                        <h2>Ganti Foto Profil</h2>
                        <p class="muted section-description">Pilih gambar (JPG, PNG, atau WebP, maks. 2 MB).</p>
                    </div>
                    <form method="post" action="{{ route('profile.avatar') }}" enctype="multipart/form-data" class="avatar-form">
                        @csrf
                        <input type="file" name="avatar" id="avatar" required accept="image/png,image/jpeg,image/webp" class="file-picker">
                        <button class="btn primary" type="submit" style="white-space:nowrap">Unggah Foto</button>
                    </form>
                    @error('avatar')<p class="error" style="margin-top:8px">{{ $message }}</p>@enderror
                </section>

                <section class="panel">
                    <div class="card-head">
                        <span class="eyebrow">IDENTITAS</span>
                        <h2>Ubah nama</h2>
                        <p class="muted section-description">Perbarui nama lengkap yang ditampilkan di akun Anda.</p>
                    </div>
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

                <section class="panel">
                    <div class="card-head">
                        <span class="eyebrow">KEAMANAN</span>
                        <h2>Ganti kata sandi</h2>
                        <p class="muted section-description">Gunakan kata sandi unik untuk menjaga keamanan akun Anda.</p>
                    </div>
                    <form method="post" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')
                        <div class="form-grid">
                            <div class="field full">
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
                        <p class="muted" style="margin:14px 0 0;font-size:12.5px">Mengganti kata sandi dapat mengakhiri sesi aktif di perangkat lain.</p>
                        <div class="actions">
                            <button class="btn primary" type="submit">Perbarui kata sandi</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>

        {{-- 5. Sesi & Akses (Desktop: left column below Info Akun | Mobile: very bottom) --}}
        <section class="panel session-card area-session">
            <span class="eyebrow">SESI &amp; AKSES</span>
            <p class="muted" style="margin:6px 0 0;font-size:13px">Selesai menggunakan akun ini? Keluar atau beralih ke akun lain.</p>
            <div class="actions session-actions">
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
</div>

<style>
.profile-workspace { width: 100%; }

.profile-layout {
    display: grid;
    grid-template-columns: 340px 1fr;
    grid-template-areas:
        "top theme"
        "identity edit"
        "session edit";
    gap: 18px 20px;
    align-items: start;
}
.area-top { grid-area: top; }
.area-top h1 { margin: 4px 0; }
.area-theme {
    grid-area: theme;
    justify-self: end;
    align-self: end;
}
.theme-mobile-meta { display: none; }
.profile-theme-btn { white-space: nowrap; }

.area-identity { grid-area: identity; }
.area-edit { grid-area: edit; }
.area-session { grid-area: session; }

.edit-dropdown-trigger { display: none; }
.edit-dropdown-body {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.profile-heading {
    display: flex;
    align-items: center;
    gap: 14px;
}
.avatar-wrap { flex: 0 0 68px; width: 68px; height: 68px; }
.avatar-img { width: 68px; height: 68px; border-radius: 50%; object-fit: cover; border: 2px solid #e6e8ea; }
.avatar {
    display: grid;
    place-items: center;
    width: 68px;
    height: 68px;
    border-radius: 50%;
    color: #ffffff;
    background: #f97316;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 24px;
    font-weight: 700;
}
.identity-meta { flex: 1; min-width: 0; }
.identity-meta h2 { margin: 0 0 2px; font-size: 18px; overflow-wrap: anywhere; }
.identity-meta p { margin: 0 0 8px; font-size: 13px; overflow-wrap: anywhere; }

.role-badge {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 999px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10.5px;
    font-weight: 600;
}
.role-admin { background: #fff3ed; color: #c2410c; border: 1px solid #fed7aa; }
.role-user { background: #ecfdf5; color: #006948; border: 1px solid #a7f3d0; }

.account-details {
    display: grid;
    gap: 12px;
    margin: 18px 0 0;
    padding-top: 16px;
    border-top: 1px solid #eceef0;
}
.account-details dt {
    color: #565e74;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.account-details dd {
    margin: 2px 0 0;
    color: #191c1e;
    font-size: 13.5px;
    font-weight: 500;
    overflow-wrap: anywhere;
}

.avatar-form { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.file-picker {
    flex: 1;
    min-width: 200px;
    background: #f7f9fb;
    border: 1px solid #d8dadc;
    padding: 8px 10px;
    border-radius: 6px;
    font-size: 12.5px;
    color: #191c1e;
}

.card-head { margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid #eceef0; }
.card-head h2 { margin: 4px 0 4px; }
.section-description { margin: 0; font-size: 13px; }

.session-actions { margin-top: 14px; }
.session-actions form { margin: 0; }

body.dark-mode .account-details,
body.dark-mode .card-head { border-color: #212c3d; }
body.dark-mode .account-details dt { color: #94a3b8; }
body.dark-mode .account-details dd { color: #f1f5f9; }
body.dark-mode .file-picker { background: #0e141f; border-color: #2a384e; color: #e2e8f0; }
body.dark-mode .avatar-img { border-color: #2a384e; }
body.dark-mode .role-admin { background: #2a1b12; color: #ff9a3d; border-color: #5c3317; }
body.dark-mode .role-user { background: #0c261d; color: #34d399; border-color: #164e3a; }

/* Mobile-only compact layout: Info Akun -> Mode Terang/Gelap -> Dropdown Edit Profil -> Sesi & Akses */
@media (max-width: 900px) {
    .profile-layout {
        grid-template-columns: 1fr;
        grid-template-areas:
            "top"
            "identity"
            "theme"
            "edit"
            "session";
        gap: 14px;
    }
    .area-theme {
        justify-self: stretch;
        align-self: stretch;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        background: #ffffff;
        border: 1px solid #e6e8ea;
        border-radius: 10px;
        padding: 16px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    }
    body.dark-mode .area-theme {
        background: #131924;
        border-color: #212c3d;
    }
    .theme-mobile-meta { display: block; }

    .edit-dropdown-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
        padding: 16px;
        text-align: left;
        cursor: pointer;
        font-family: inherit;
        transition: border-color 0.15s, background-color 0.15s;
    }
    .edit-dropdown-trigger:hover { border-color: #cbd5e1; }
    .edit-trigger-left { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .edit-trigger-icon {
        width: 38px;
        height: 38px;
        border-radius: 9px;
        background: #fff3ed;
        border: 1px solid #fed7aa;
        color: #f97316;
        display: grid;
        place-items: center;
        flex-shrink: 0;
    }
    .edit-trigger-icon svg { width: 18px; height: 18px; stroke-width: 2; }
    .edit-trigger-title { display: block; font-size: 14.5px; font-weight: 700; color: #191c1e; }
    .edit-trigger-sub { display: block; font-size: 12px; color: #565e74; margin-top: 2px; }
    .edit-trigger-chevron {
        width: 18px;
        height: 18px;
        stroke-width: 2;
        color: #565e74;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }
    .area-edit.is-open .edit-trigger-chevron { transform: rotate(180deg); color: #f97316; }

    .edit-dropdown-body {
        display: none;
        margin-top: 12px;
        gap: 14px;
    }
    .area-edit.is-open .edit-dropdown-body { display: flex; }

    body.dark-mode .edit-trigger-icon { background: #2a1b12; border-color: #5c3317; color: #ff9a3d; }
    body.dark-mode .edit-trigger-title { color: #f8fafc; }
    body.dark-mode .edit-trigger-sub,
    body.dark-mode .edit-trigger-chevron { color: #94a3b8; }
}

@media (max-width: 520px) {
    .area-theme { flex-direction: column; align-items: stretch; }
    .profile-theme-btn { width: 100%; }
    .avatar-form { flex-direction: column; align-items: stretch; }
    .avatar-form .btn,
    .file-picker { width: 100%; min-width: 0; }
    .session-actions { flex-direction: column; align-items: stretch; }
    .session-actions form,
    .session-actions .btn { width: 100%; }
}
</style>

<script>
(() => {
    const wrapper = document.getElementById('mobile-edit-dropdown');
    const toggle = document.getElementById('edit-profile-toggle');
    if (!wrapper || !toggle) return;

    toggle.addEventListener('click', () => {
        const isOpen = wrapper.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
})();
</script>
@endsection
