@extends('layouts.app')
@section('title', 'Konfirmasi Batalkan Scan | STIKSAFE-67')
@section('content')
<div style="max-width:600px;margin:45px auto"><section class="panel"><span class="eyebrow" style="color:#fca5a5">CONFIRM ACTION / CANCELLATION</span><h1>Batalkan scan?</h1><p class="muted">Pemindaian berikut akan dihentikan dan tidak dapat dilanjutkan.</p><dl class="muted" style="padding:16px;border:1px solid rgba(239,68,68,.25);border-radius:9px"><dt>ID scan</dt><dd style="margin:4px 0 13px;overflow-wrap:anywhere;color:#f8fafc">{{ $scan->id }}</dd><dt>Nama file</dt><dd style="margin:4px 0;color:#f8fafc">{{ $scan->original_filename }}</dd></dl><form method="post" action="{{ route('scans.cancel.confirm',$scan->id) }}">@csrf<div class="actions"><button class="btn" style="border-color:#EF4444;color:#fca5a5" type="submit">Ya, batalkan scan</button><a class="btn" href="{{ route('scans.show',$scan->id) }}">Kembali</a></div></form></section></div>
@endsection
