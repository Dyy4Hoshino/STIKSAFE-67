@extends('layouts.app')
@section('title', 'Detail User | Admin STIKSAFE-67')

@section('content')
<div class="eyebrow">ADMIN / USER / {{ $managedUser->id }}</div>
<h1>{{ $managedUser->name }}</h1>
<p class="muted" style="margin:0 0 22px">{{ $managedUser->email }}</p>

<div class="form-grid" style="align-items:start">
    <section class="panel">
        <h3 style="margin:0 0 14px">Informasi &amp; Perubahan Status</h3>
        <dl style="display:grid;gap:8px;margin:0 0 14px;font-size:13.5px">
            <div><dt class="muted" style="font-size:11.5px">User ID</dt><dd style="margin:2px 0 0;font-family:'JetBrains Mono',monospace">{{ $managedUser->id }}</dd></div>
            <div><dt class="muted" style="font-size:11.5px">Email</dt><dd style="margin:2px 0 0">{{ $managedUser->email }}</dd></div>
            <div><dt class="muted" style="font-size:11.5px">Verifikasi email</dt><dd style="margin:2px 0 0">{{ $managedUser->email_verified_at ? 'Terverifikasi' : 'Belum diverifikasi' }}</dd></div>
            <div><dt class="muted" style="font-size:11.5px">Bergabung</dt><dd style="margin:2px 0 0">{{ $managedUser->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') ?? '—' }}</dd></div>
        </dl>
        <p class="muted" style="margin:0">Status saat ini: <strong style="color:{{ $managedUser->status === 'active' ? '#006948' : '#ba1a1a' }}">{{ ucfirst($managedUser->status) }}</strong></p>
        @if($managedUser->status_reason)<p class="muted" style="font-size:13px;margin:4px 0 0">Alasan: {{ $managedUser->status_reason }}</p>@endif

        @if(auth()->id() !== $managedUser->id)
            <form method="POST" action="{{ route('admin.users.status', $managedUser->id) }}" style="margin-top:18px;display:flex;flex-direction:column;gap:12px">
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

            <form method="POST" action="{{ route('admin.users.role', $managedUser->id) }}" style="margin-top:20px;padding-top:16px;border-top:1px solid #eceef0;display:flex;flex-direction:column;gap:12px">
                @csrf
                @method('PATCH')
                <div class="field">
                    <label>Role Pengguna</label>
                    <select name="role">
                        <option value="user" @selected($managedUser->role === 'user')>User</option>
                        <option value="admin" @selected($managedUser->role === 'admin')>Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn" style="align-self:flex-start;border-color:#fde68a;background:#fffbeb;color:#b45309">Ubah Role</button>
            </form>

            <form method="POST" action="{{ route('admin.users.force-logout', $managedUser->id) }}" style="margin-top:16px">
                @csrf
                <button type="submit" class="btn danger">Cabut Semua Sesi API (Force Logout)</button>
            </form>
        @else
            <p class="muted" style="font-size:12.5px;margin-top:14px">Akun milik sendiri tidak dapat diubah status/rolenya dari halaman admin ini.</p>
        @endif
    </section>

    <section class="panel">
        <h3 style="margin:0 0 14px">Scan Terbaru (10)</h3>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>ID</th><th>File</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($recentScans as $sc)
                        <tr>
                            <td><a style="color:#a33900;font-weight:600;font-family:'JetBrains Mono',monospace;font-size:12px" href="{{ route('admin.scans.show', $sc->id) }}">{{ substr($sc->id, 0, 10) }}...</a></td>
                            <td>{{ $sc->original_filename }}</td>
                            <td>{{ $sc->status?->value }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted" style="text-align:center;padding:24px">Belum ada scan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
<div style="margin-top:20px"><a class="btn" href="{{ route('admin.users') }}">← Kembali ke Daftar Pengguna</a></div>
@endsection
