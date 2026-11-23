@extends('layouts.app')
@section('title', 'Konfirmasi Aksi | STIKSAFE-67')
@section('content')
<div style="max-width:600px;margin:45px auto">
    <section class="panel">
        <span class="eyebrow" style="color:#fca5a5">CONFIRM ACTION</span>
        <h1>{{ $action === 'delete-scan' ? 'Hapus riwayat scan?' : 'Batalkan scan?' }}</h1>
        <p class="muted">{{ $action === 'delete-scan' ? 'Riwayat akan dihapus dari akun Anda dan file asli dipurge segera. Tindakan ini tidak dapat dibatalkan.' : 'Pemindaian berikut akan dihentikan dan tidak dapat dilanjutkan.' }}</p>
        <dl class="muted" style="padding:16px;border:1px solid rgba(239,68,68,.25);border-radius:9px">
            <dt>ID scan</dt>
            <dd style="margin:4px 0 13px;overflow-wrap:anywhere;color:#f8fafc">{{ $scan->id }}</dd>
            <dt>Nama file</dt>
            <dd style="margin:4px 0;color:#f8fafc">{{ $scan->original_filename }}</dd>
        </dl>
        <form method="post" action="{{ $action === 'delete-scan' ? route('history.destroy', $scan->id) : route('scans.cancel.confirm', $scan->id) }}">
            @csrf
            @if ($action === 'delete-scan')
                @method('DELETE')
            @endif
            <div class="actions">
                <button class="btn" style="border-color:#EF4444;color:#fca5a5" type="submit">{{ $action === 'delete-scan' ? 'Ya, hapus riwayat' : 'Ya, batalkan scan' }}</button>
                <a class="btn" href="{{ $action === 'delete-scan' ? route('history') : route('scans.show', $scan->id) }}">Kembali</a>
            </div>
        </form>
    </section>
</div>
@endsection
