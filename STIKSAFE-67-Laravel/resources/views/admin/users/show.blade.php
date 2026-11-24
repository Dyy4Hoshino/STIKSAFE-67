@extends('layouts.app')
@section('title', 'Detail User | Admin STIKSAFE-67')
@section('content')
<div class="eyebrow">ADMIN / USER / {{ $managedUser->id }}</div>
<h1>{{ $managedUser->name }}</h1>
<p class="muted">{{ $managedUser->email }}</p>

<div class="grid" style="grid-template-columns:1fr 1fr;gap:20px;align-items:start">
    <section class="panel">
        <h3>Informasi & Perubahan Status</h3>
        <p class="muted">Status saat ini: <strong style="color:{{ $managedUser->status==='active'?'#10b981':'#ef4444' }}">{{ ucfirst($managedUser->status) }}</strong></p>
        @if($managedUser->status_reason)<p class="muted" style="font-size:13px">Alasan: {{ $managedUser->status_reason }}</p>@endif

        @if(auth()->id() !== $managedUser->id)
        <form method="POST" action="{{ route('admin.users.status', $managedUser->id) }}" style="margin-top:16px;display:flex;flex-direction:column;gap:12px">
            @csrf
            @method('PATCH')
            <div class="field">
                <label>Ubah Status</label>
                <select name="status" style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:8px 12px;border-radius:8px">
                    <option value="active" @selected($managedUser->status==='active')>Active</option>
                    <option value="suspended" @selected($managedUser->status==='suspended')>Suspended</option>
                    <option value="inactive" @selected($managedUser->status==='inactive')>Inactive</option>
                </select>
            </div>
            <div class="field">
                <label>Alasan (wajib jika Suspended)</label>
                <input type="text" name="reason" value="{{ old('reason') }}" placeholder="Contoh: Pelanggaran TOS..." style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:8px 12px;border-radius:8px">
            </div>
            <button type="submit" class="btn primary">Simpan Status</button>
        </form>

        <form method="POST" action="{{ route('admin.users.role', $managedUser->id) }}" style="margin-top:24px;padding-top:16px;border-top:1px solid rgba(148,163,184,.14);display:flex;flex-direction:column;gap:12px">
            @csrf
            @method('PATCH')
            <div class="field">
                <label>Role Pengguna</label>
                <select name="role" style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:8px 12px;border-radius:8px">
                    <option value="user" @selected($managedUser->role==='user')>User</option>
                    <option value="admin" @selected($managedUser->role==='admin')>Admin</option>
                </select>
            </div>
            <button type="submit" class="btn" style="border-color:#f59e0b;color:#fdf6b2">Ubah Role</button>
        </form>

        <form method="POST" action="{{ route('admin.users.force-logout', $managedUser->id) }}" style="margin-top:16px">
            @csrf
            <button type="submit" class="btn" style="border-color:#ef4444;color:#fca5a5">Cabut Semua Sesi API (Force Logout)</button>
        </form>
        @else
        <p class="muted" style="font-size:12px">Akun milik sendiri tidak dapat diubah status/rolenya dari halaman admin ini.</p>
        @endif
    </section>

    <section class="panel">
        <h3>Scan Terbaru (10)</h3>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>ID</th><th>File</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($recentScans as $sc)
                    <tr>
                        <td><a href="{{ route('admin.scans.show', $sc->id) }}">{{ substr($sc->id, 0, 10) }}...</a></td>
                        <td>{{ $sc->original_filename }}</td>
                        <td>{{ $sc->status?->value }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="muted">Belum ada scan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
<div style="margin-top:20px"><a class="btn" href="{{ route('admin.users') }}">← Kembali ke Daftar Pengguna</a></div>
@endsection
