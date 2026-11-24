@extends('layouts.app')
@section('title', 'Admin Users | STIKSAFE-67')
@section('content')
<div class="eyebrow">ADMIN / USERS</div>
<h1>Manajemen Pengguna</h1>
<p class="muted">Kelola akun, status, dan hak akses pengguna.</p>

<form method="GET" style="margin:24px 0;display:flex;gap:12px;flex-wrap:wrap">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau email..." style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:10px 14px;border-radius:8px;width:280px">
    <select name="role" style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:10px 14px;border-radius:8px">
        <option value="">Semua Role</option>
        <option value="user" @selected(request('role')==='user')>User</option>
        <option value="admin" @selected(request('role')==='admin')>Admin</option>
    </select>
    <select name="status" style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:10px 14px;border-radius:8px">
        <option value="">Semua Status</option>
        <option value="active" @selected(request('status')==='active')>Active</option>
        <option value="suspended" @selected(request('status')==='suspended')>Suspended</option>
        <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
    </select>
    <button type="submit" class="btn primary">Filter</button>
</form>

<div class="panel table-wrap">
    <table class="data">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Terdaftar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $usr)
            <tr>
                <td><strong>{{ $usr->name }}</strong></td>
                <td>{{ $usr->email }}</td>
                <td><span style="color:{{ $usr->role==='admin'?'#FF6700':'#38bdf8' }}">{{ ucfirst($usr->role) }}</span></td>
                <td><span style="color:{{ $usr->status==='active'?'#10b981':($usr->status==='suspended'?'#ef4444':'#94a3b8') }}">{{ ucfirst($usr->status) }}</span></td>
                <td>{{ $usr->created_at?->format('d M Y') }}</td>
                <td><a class="btn" href="{{ route('admin.users.show', $usr->id) }}">Detail</a></td>
            </tr>
            @empty
            <tr><td colspan="6" class="muted" style="text-align:center;padding:32px">Tidak ada pengguna ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $users->links() }}
</div>
@endsection
