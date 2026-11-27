@extends('layouts.app')
@section('title', 'Detail User | Admin STIKSAFE-67')

@section('content')
<div class="admin-user-workspace">
    <div class="admin-user-top">
        <div class="admin-user-hero">
            <div class="admin-user-avatar">
                @if ($managedUser->avatar_path)
                    <img src="{{ asset('storage/' . $managedUser->avatar_path) }}" alt="Foto profil {{ $managedUser->name }}">
                @else
                    <span aria-hidden="true">{{ strtoupper(substr($managedUser->name, 0, 1)) }}</span>
                @endif
            </div>
            <div style="min-width:0">
                <div class="eyebrow">ADMIN / USER / {{ $managedUser->id }}</div>
                <h1>{{ $managedUser->name }}</h1>
                <div class="admin-user-sub">
                    <span class="muted">{{ $managedUser->email }}</span>
                    <span class="admin-pill {{ $managedUser->role === 'admin' ? 'pill-role-admin' : 'pill-role-user' }}">
                        {{ strtoupper($managedUser->role ?? 'USER') }}
                    </span>
                    <span class="admin-pill {{ $managedUser->status === 'active' ? 'pill-status-active' : 'pill-status-warn' }}">
                        {{ ucfirst($managedUser->status) }}
                    </span>
                </div>
            </div>
        </div>
        <a class="btn" href="{{ route('admin.users') }}">← Kembali ke Daftar Pengguna</a>
    </div>

    <div class="admin-user-grid">
        {{-- Left Column: Account Info & Controls --}}
        <div class="admin-user-rail">
            <section class="panel">
                <div class="admin-card-head">
                    <span class="eyebrow">PROFIL PENGGUNA</span>
                    <h3>Informasi Akun</h3>
                </div>
                <dl class="admin-dl">
                    <div>
                        <dt>User ID</dt>
                        <dd class="font-mono">{{ $managedUser->id }}</dd>
                    </div>
                    <div>
                        <dt>Email</dt>
                        <dd>{{ $managedUser->email }}</dd>
                    </div>
                    <div>
                        <dt>Verifikasi email</dt>
                        <dd>{{ $managedUser->email_verified_at ? 'Terverifikasi' : 'Belum diverifikasi' }}</dd>
                    </div>
                    <div>
                        <dt>Bergabung</dt>
                        <dd>{{ $managedUser->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt>Status saat ini</dt>
                        <dd>
                            <strong style="color:{{ $managedUser->status === 'active' ? '#006948' : '#ba1a1a' }}">{{ ucfirst($managedUser->status) }}</strong>
                            @if($managedUser->status_reason)
                                <div class="muted" style="font-size:12.5px;margin-top:3px">Alasan: {{ $managedUser->status_reason }}</div>
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="panel">
                <div class="admin-card-head">
                    <span class="eyebrow">KONTROL AKSES</span>
                    <h3>Status, Role &amp; Sesi</h3>
                </div>

                @if(auth()->id() !== $managedUser->id)
                    <form method="POST" action="{{ route('admin.users.status', $managedUser->id) }}" class="admin-form-block">
                        @csrf
                        @method('PATCH')
                        <div class="field">
                            <label for="status">Ubah Status</label>
                            <select id="status" name="status">
                                <option value="active" @selected($managedUser->status === 'active')>Active</option>
                                <option value="suspended" @selected($managedUser->status === 'suspended')>Suspended</option>
                                <option value="inactive" @selected($managedUser->status === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="reason">Alasan (wajib jika Suspended)</label>
                            <input id="reason" type="text" name="reason" value="{{ old('reason') }}" placeholder="Contoh: Pelanggaran TOS..." maxlength="255">
                        </div>
                        <button type="submit" class="btn primary" style="align-self:flex-start">Simpan Status</button>
                    </form>

                    <form method="POST" action="{{ route('admin.users.role', $managedUser->id) }}" class="admin-form-block admin-form-divider">
                        @csrf
                        @method('PATCH')
                        <div class="field">
                            <label for="role">Role Pengguna</label>
                            <select id="role" name="role">
                                <option value="user" @selected($managedUser->role === 'user')>User</option>
                                <option value="admin" @selected($managedUser->role === 'admin')>Admin</option>
                            </select>
                        </div>
                        <button type="submit" class="btn admin-role-btn" style="align-self:flex-start">Ubah Role</button>
                    </form>

                    <form method="POST" action="{{ route('admin.users.force-logout', $managedUser->id) }}" class="admin-form-divider">
                        @csrf
                        <button type="submit" class="btn danger" style="width:100%">Cabut Semua Sesi API (Force Logout)</button>
                    </form>
                @else
                    <p class="muted" style="font-size:13px;margin:0">Akun milik sendiri tidak dapat diubah status/rolenya dari halaman admin ini.</p>
                @endif
            </section>
        </div>

        {{-- Right Column: Recent Scans --}}
        <section class="panel">
            <div class="admin-card-head">
                <span class="eyebrow">AKTIVITAS PEMINDAIAN</span>
                <h3>Scan Terbaru (10)</h3>
            </div>
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>File</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentScans as $sc)
                            <tr>
                                <td>
                                    <a class="admin-scan-link" href="{{ route('admin.scans.show', $sc->id) }}">{{ substr($sc->id, 0, 10) }}...</a>
                                </td>
                                <td style="font-weight:500;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $sc->original_filename }}</td>
                                <td>{{ $sc->status?->value }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="muted" style="text-align:center;padding:32px">Belum ada scan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<style>
.admin-user-workspace { width: 100%; }
.admin-user-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 22px;
}
.admin-user-hero {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}
.admin-user-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: #f97316;
    color: #ffffff;
    display: grid;
    place-items: center;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 20px;
    font-weight: 700;
    border: 2px solid #e6e8ea;
}
.admin-user-avatar img { width: 100%; height: 100%; object-fit: cover; }
.admin-user-hero h1 { margin: 2px 0 4px; font-size: clamp(22px, 3vw, 28px); }
.admin-user-sub {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    font-size: 13px;
}
.admin-pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 999px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10.5px;
    font-weight: 600;
}
.pill-role-admin { background: #fff3ed; color: #c2410c; border: 1px solid #fed7aa; }
.pill-role-user { background: #f2f4f6; color: #565e74; border: 1px solid #e6e8ea; }
.pill-status-active { background: #ecfdf5; color: #006948; border: 1px solid #a7f3d0; }
.pill-status-warn { background: #fef2f2; color: #ba1a1a; border: 1px solid #fecaca; }

.admin-user-grid {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 20px;
    align-items: start;
}
.admin-user-rail { display: flex; flex-direction: column; gap: 18px; }
.admin-card-head { margin-bottom: 14px; padding-bottom: 12px; border-bottom: 1px solid #eceef0; }
.admin-card-head h3 { margin: 3px 0 0; }

.admin-dl { display: grid; gap: 11px; margin: 0; font-size: 13.5px; }
.admin-dl dt {
    color: #565e74;
    font-family: 'JetBrains Mono', monospace;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.admin-dl dd { margin: 2px 0 0; color: #191c1e; overflow-wrap: anywhere; }

.admin-form-block { display: flex; flex-direction: column; gap: 12px; }
.admin-form-divider { margin-top: 18px; padding-top: 16px; border-top: 1px solid #eceef0; }
.admin-role-btn { border-color: #fde68a; background: #fffbeb; color: #b45309; }
.admin-scan-link { color: #f97316; font-weight: 600; font-family: 'JetBrains Mono', monospace; font-size: 12px; }
.admin-scan-link:hover { text-decoration: underline; }

body.dark-mode .admin-card-head,
body.dark-mode .admin-form-divider { border-color: #212c3d; }
body.dark-mode .admin-user-avatar { border-color: #2a384e; }
body.dark-mode .admin-dl dt { color: #94a3b8; }
body.dark-mode .admin-dl dd { color: #f1f5f9; }
body.dark-mode .pill-role-admin { background: #2a1b12; color: #ff9a3d; border-color: #5c3317; }
body.dark-mode .pill-role-user { background: #1e293b; color: #cbd5e1; border-color: #334155; }
body.dark-mode .pill-status-active { background: #0c261d; color: #34d399; border-color: #164e3a; }
body.dark-mode .pill-status-warn { background: #2d1215; color: #fca5a5; border-color: #5c2229; }
body.dark-mode .admin-role-btn { background: #2b2110 !important; border-color: #5c4317 !important; color: #fbbf24 !important; }

@media (max-width: 960px) {
    .admin-user-grid { grid-template-columns: 1fr; }
}
@media (max-width: 520px) {
    .admin-user-top .btn { width: 100%; }
    .admin-form-block .btn { width: 100%; align-self: stretch !important; }
}
</style>
@endsection
