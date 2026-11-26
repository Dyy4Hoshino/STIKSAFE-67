@extends('layouts.app')
@section('title', 'Kirim Notifikasi | STIKSAFE-67')
@section('content')
<div class="eyebrow">ADMIN / COMMUNICATION</div>
<h1>Kirim notifikasi.</h1>
<p class="muted">Pesan masuk ke pusat notifikasi akun penerima. Aksi ini mengirim segera.</p>
<section class="panel" style="max-width:760px">
    <form method="post" action="{{ route('admin.notifications.store') }}">
        @csrf
        <div class="field"><label for="target">PENERIMA</label><select id="target" name="target" required><option value="all_users">Semua user</option><option value="all_admins">Semua admin</option><option value="all">Seluruh akun</option><option value="custom">Pilih akun tertentu</option></select></div>
        <div class="field" id="custom-users" hidden style="margin-top:16px"><label for="user_ids">AKUN TERTENTU</label><select id="user_ids" name="user_ids[]" multiple size="8">@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }} — {{ $user->email }} ({{ $user->role }})</option>@endforeach</select><small>Pilih satu atau lebih akun. Gunakan Ctrl atau Cmd untuk memilih beberapa.</small></div>
        <div class="field" style="margin-top:16px"><label for="title">JUDUL</label><input id="title" name="title" maxlength="150" required value="{{ old('title') }}"></div>
        <div class="field" style="margin-top:16px"><label for="message">PESAN</label><textarea id="message" name="message" rows="5" maxlength="1000" required>{{ old('message') }}</textarea></div>
        <div class="actions"><button class="btn primary" type="submit">Kirim notifikasi</button><a class="btn" href="{{ route('admin.dashboard') }}">Batal</a></div>
    </form>
</section>
<script>
    const target = document.getElementById('target');
    const customUsers = document.getElementById('custom-users');
    const userIds = document.getElementById('user_ids');
    function toggleCustomUsers() {
        const enabled = target.value === 'custom';
        customUsers.hidden = !enabled;
        userIds.required = enabled;
    }
    target.addEventListener('change', toggleCustomUsers);
    toggleCustomUsers();
</script>
@endsection
