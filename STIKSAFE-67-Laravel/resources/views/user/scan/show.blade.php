@extends('layouts.app')
@section('title', 'Detail Scan | STIKSAFE-67')
@section('head')
@if(in_array($scan->status?->value,['queued','processing'],true) && isset($scan->poll_after_ms) && $scan->poll_after_ms>=5000)<meta http-equiv="refresh" content="{{ (int) ($scan->poll_after_ms / 1000) }}">@endif
@endsection
@section('content')
<div class="eyebrow">SCAN REPORT / {{ $scan->id }}</div><h1>Hasil pemeriksaan</h1><p class="muted">Rincian status dan informasi file yang dianalisis.</p>
<div class="grid" style="grid-template-columns:1.2fr .8fr;align-items:start;margin-top:24px"><section class="panel"><h2 style="margin-top:0;overflow-wrap:anywhere">{{ $scan->original_filename }}</h2><dl class="muted" style="display:grid;grid-template-columns:150px 1fr;gap:12px;font-size:13px"><dt>ID scan</dt><dd style="margin:0;overflow-wrap:anywhere">{{ $scan->id }}</dd><dt>Ukuran</dt><dd style="margin:0">{{ number_format($scan->file_size / 1024, 2, ',', '.') }} KB</dd><dt>Jenis file</dt><dd style="margin:0">{{ $scan->mime_type }}</dd><dt>Status</dt><dd style="margin:0">{{ $scan->status?->value === 'queued' ? 'Menunggu antrean' : ($scan->status?->value === 'processing' ? 'Sedang dianalisis' : ucfirst($scan->status?->value ?? 'Tidak diketahui')) }}</dd>@if($scan->created_at)<dt>Waktu upload</dt><dd style="margin:0"><time datetime="{{ $scan->created_at->toISOString() }}">{{ $scan->created_at->timezone(config('app.timezone'))->format('d M Y H:i:s T') }}</time></dd>@endif</dl></section>
@if(in_array($scan->status?->value,['queued','processing'],true))<section class="panel"><span class="eyebrow">ANALYSIS RUNNING</span><h2>Pemindaian berlangsung</h2><p class="muted">Status diperbarui otomatis. Hindari mengirim file yang sama berulang.</p><div style="height:4px;background:#1e293b;border-radius:9px;overflow:hidden"><div style="height:100%;width:45%;background:#FF6700;animation:pulse 1.4s ease-in-out infinite alternate"></div></div><p><a style="color:#FF8A38" href="{{ route('scans.show',$scan->id) }}">Segarkan status</a></p><p><a style="color:#fb7185" href="{{ route('scans.cancel',$scan->id) }}">Batalkan scan</a></p></section>
@elseif($scan->status?->value==='completed')<section class="panel"><span class="eyebrow">ANALYSIS COMPLETE</span><h2>Hasil pemindaian</h2><p style="font-size:20px;color:{{ match($scan->result_label?->value){'safe'=>'#10B981','malicious'=>'#EF4444',default=>'#FF8A38'} }}">{{ match ($scan->result_label?->value) { 'safe' => 'Aman', 'suspicious' => 'Mencurigakan', 'malicious' => 'Berbahaya', 'unknown' => 'Tidak diketahui', default => '—' } }}</p>@if($scan->confidence!==null)<p class="muted">Skor model: {{ $scan->confidence }} / skala 0–1</p>@endif@if($scan->model_version)<p class="muted">Versi model: {{ $scan->model_version }}</p>@endif<p class="muted" style="font-size:12px">Prediksi model bukan jaminan keamanan mutlak. File asli: {{ $scan->file_purged_at ? 'sudah dihapus sesuai retensi' : 'tersedia' }}.</p>

@if($scan->feedback)
<div style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(148,163,184,.14)">
    <span class="eyebrow" style="color:#10b981">FEEDBACK TERKIRIM</span>
    <p class="muted" style="font-size:13px;margin:6px 0">Tipe: {{ match($scan->feedback->type){'false_positive'=>'False Positive (Harusnya Aman)','false_negative'=>'False Negative (Harusnya Berbahaya)','other'=>'Lainnya',default=>$scan->feedback->type} }}</p>
    @if($scan->feedback->comment)<p class="muted" style="font-size:13px;margin:0">Komentar: {{ $scan->feedback->comment }}</p>@endif
</div>
@else
<form method="POST" action="{{ route('scans.feedback.store', $scan->id) }}" style="margin-top:16px;padding-top:16px;border-top:1px solid rgba(148,163,184,.14);display:flex;flex-direction:column;gap:10px">
    @csrf
    <span class="eyebrow" style="color:#FF8A38">KIRIM UMPAN BALIK</span>
    <div style="display:flex;flex-direction:column;gap:4px">
        <label style="font-size:12px;color:#91a0b4">Jenis Masukan</label>
        <select name="type" required style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:8px;border-radius:6px;font-size:12px">
            <option value="false_positive">False Positive (File aman tapi terdeteksi berbahaya)</option>
            <option value="false_negative">False Negative (File berbahaya tapi terdeteksi aman)</option>
            <option value="other">Lainnya / Catatan Model</option>
        </select>
    </div>
    <div style="display:flex;flex-direction:column;gap:4px">
        <label style="font-size:12px;color:#91a0b4">Komentar Opsional</label>
        <textarea name="comment" rows="2" maxlength="1000" placeholder="Jelaskan alasan atau konteks tambahan..." style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:8px;border-radius:6px;font-size:12px;resize:vertical"></textarea>
    </div>
    <button type="submit" class="btn primary" style="align-self:flex-start">Kirim Umpan Balik</button>
</form>
@endif
</section>
@else<section class="panel"><span class="eyebrow">ANALYSIS INCOMPLETE</span><h2>Pemindaian tidak selesai</h2><p class="muted">Status: {{ ucfirst($scan->status?->value ?? 'Tidak diketahui') }}. Coba lagi dengan file lain.</p><a class="btn primary" href="{{ route('scan.create') }}">Pindai file lain</a></section>@endif</div>
<div class="actions"><a class="btn" href="{{ route('dashboard') }}">← Dashboard</a><a class="btn" href="{{ route('history') }}">Riwayat scan</a>@if($scan->status?->value !== 'queued' && $scan->status?->value !== 'processing')<a class="btn" style="border-color:#EF4444;color:#fca5a5" href="{{ route('confirm',['action'=>'delete-scan','target'=>$scan->id]) }}">Hapus dari riwayat</a>@endif</div><style>@keyframes pulse{from{transform:translateX(-100%)}to{transform:translateX(160%)}}@media(max-width:700px){.grid[style*="1.2fr"]{grid-template-columns:1fr!important}}</style>
@endsection
