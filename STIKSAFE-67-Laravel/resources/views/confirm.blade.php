@extends('layouts.app')
@section('title', 'Konfirmasi Aksi | STIKSAFE-67')

@section('content')
<div style="max-width:560px;margin:32px auto">
    <section class="panel" style="padding:28px">
        <span class="eyebrow" style="color:#ba1a1a">KONFIRMASI AKSI</span>
        <h1 style="margin:6px 0 8px">{{ $action === 'delete-scan' ? 'Hapus riwayat scan?' : 'Batalkan scan?' }}</h1>
        <p class="muted" style="margin:0 0 18px">{{ $action === 'delete-scan' ? 'Riwayat akan dihapus dari akun Anda dan file asli segera dibersihkan. Tindakan ini tidak dapat dibatalkan.' : 'Pemindaian berikut akan dihentikan dan tidak dapat dilanjutkan.' }}</p>

        <dl style="padding:14px 16px;border:1px solid #fecaca;background:#fef2f2;border-radius:8px;margin:0 0 18px">
            <dt style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#93000a">ID SCAN</dt>
            <dd style="margin:4px 0 12px;overflow-wrap:anywhere;color:#191c1e;font-family:'JetBrains Mono',monospace;font-size:13px">{{ $scan->id }}</dd>
            <dt style="font-family:'JetBrains Mono',monospace;font-size:11px;color:#93000a">NAMA FILE</dt>
            <dd style="margin:4px 0 0;color:#191c1e;font-weight:600">{{ $scan->original_filename }}</dd>
        </dl>

        <form method="post" action="{{ $action === 'delete-scan' ? route('history.destroy', $scan->id) : route('scans.cancel.confirm', $scan->id) }}">
            @csrf
            @if ($action === 'delete-scan')
                @method('DELETE')
            @endif
            <div class="actions">
                <button class="btn danger" type="submit">{{ $action === 'delete-scan' ? 'Ya, hapus riwayat' : 'Ya, batalkan scan' }}</button>
                <a class="btn" href="{{ $action === 'delete-scan' ? route('history') : route('scans.show', $scan->id) }}">Kembali</a>
            </div>
        </form>
    </section>
</div>
@endsection
