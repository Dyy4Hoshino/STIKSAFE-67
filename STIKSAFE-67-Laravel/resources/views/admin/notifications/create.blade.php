@extends('layouts.app')
@section('title', 'Kirim Notifikasi | STIKSAFE-67')
@section('content')
<div class="eyebrow">ADMIN / COMMUNICATION</div>
<h1>Kirim notifikasi.</h1>
<p class="muted">Pesan masuk ke pusat notifikasi akun penerima. Aksi ini mengirim segera.</p>
<section class="panel" style="max-width:760px">
    <form method="post" action="{{ route('admin.notifications.store') }}">
        @csrf
        <div class="field">
            <label for="target">PENERIMA</label>
            <select id="target" name="target" required>
                <option value="all_users" @selected(old('target', 'all_users') === 'all_users')>Semua user</option>
                <option value="all_admins" @selected(old('target') === 'all_admins')>Semua admin</option>
                <option value="all" @selected(old('target') === 'all')>Seluruh akun (User & Admin)</option>
            </select>
        </div>
        <div class="field" style="margin-top:16px">
            <label for="title">JUDUL</label>
            <input id="title" name="title" maxlength="150" required value="{{ old('title') }}" placeholder="Judul notifikasi...">
        </div>
        <div class="field" style="margin-top:16px">
            <label for="message">PESAN</label>
            <textarea id="message" name="message" rows="5" maxlength="1000" required placeholder="Tulis isi pesan notifikasi di sini...">{{ old('message') }}</textarea>
        </div>
        <div class="actions">
            <button class="btn primary" type="submit">Kirim notifikasi</button>
            <a class="btn" href="{{ route('admin.dashboard') }}">Batal</a>
        </div>
    </form>
</section>
@endsection
