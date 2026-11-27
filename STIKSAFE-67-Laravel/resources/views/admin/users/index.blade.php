@extends('layouts.app')
@section('title', 'Admin Users | STIKSAFE-67')
@section('content')
<div class="eyebrow">ADMIN / USERS</div>
<h1>Manajemen Pengguna</h1>
<p class="muted">Kelola akun, status, dan hak akses pengguna.</p>

<form method="GET" class="panel" style="margin:24px 0;display:flex;gap:12px;align-items:end;flex-wrap:wrap">
    <div class="field"><label for="q">CARI AKUN</label><input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="Nama atau email" maxlength="120"></div>
    <div class="field"><label for="role">ROLE</label><select id="role" name="role">
        <option value="">Semua Role</option>
        <option value="user" @selected(request('role')==='user')>User</option>
        <option value="admin" @selected(request('role')==='admin')>Admin</option>
    </select></div>
    <div class="field"><label for="status">STATUS</label><select id="status" name="status">
        <option value="">Semua Status</option>
        <option value="active" @selected(request('status')==='active')>Active</option>
        <option value="suspended" @selected(request('status')==='suspended')>Suspended</option>
        <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
    </select></div>
    <button type="submit" class="btn primary">Filter</button>
    <a class="btn" href="{{ route('admin.users') }}">Reset</a>
</form>

<div class="panel table-wrap">
    <table class="data">
        <thead>
            <tr>
                <th scope="col">Nama</th>
                <th scope="col">Email</th>
                <th scope="col">Role</th>
                <th scope="col">Status</th>
                <th scope="col">Email diverifikasi</th>
                <th scope="col">Terdaftar</th>
                <th scope="col">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $usr)
            <tr>
                <td><strong>{{ $usr->name }}</strong></td>
                <td>{{ $usr->email }}</td>
                <td><span style="color:{{ $usr->role==='admin'?'#FF6700':'#38bdf8' }}">{{ ucfirst($usr->role) }}</span></td>
                <td><span style="color:{{ $usr->status==='active'?'#10b981':($usr->status==='suspended'?'#ef4444':'#94a3b8') }}">{{ ucfirst($usr->status) }}</span></td>
                <td>{{ $usr->email_verified_at ? 'Sudah (' . $usr->email_verified_at->timezone(config('app.timezone'))->format('d M Y') . ')' : 'Belum' }}</td>
                <td>{{ $usr->created_at?->format('d M Y') }}</td>
                <td><a class="btn" href="{{ route('admin.users.show', $usr->id) }}">Detail</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="muted" style="text-align:center;padding:32px">Tidak ada pengguna ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $users->links() }}
</div>
@endsection
