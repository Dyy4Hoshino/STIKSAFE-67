@extends('layouts.app')
@section('title', 'Beranda | STIKSAFE-67')

@section('content')
@php
    $firstName = explode(' ', trim($user->name))[0] ?: $user->name;
@endphp

<div class="home-page">
    <section class="home-hero panel">
        <div class="home-hero-copy">
            <span class="eyebrow">STIKSAFE / SECURITY CENTER</span>
            <h1>Halo, {{ $firstName }}.</h1>
            <p>Periksa file atau URL mencurigakan, pantau hasil, lalu ambil tindakan dengan informasi yang jelas.</p>
            <div class="home-hero-actions">
                <a class="btn primary home-main-cta" href="{{ route('scan.create') }}">+ Mulai Scan Baru</a>
                <a class="btn" href="{{ route('history') }}">Lihat riwayat</a>
            </div>
        </div>
        <div class="home-hero-status" aria-label="Status layanan">
            <div class="shield-mark">SAFE</div>
            <div>
                <span class="eyebrow" style="color:#6ee7b7">SYSTEM STATUS</span>
                <strong>Siap memindai</strong>
                <small>File & URL analysis</small>
            </div>
        </div>
    </section>

    <section class="overview-section" aria-labelledby="overview-heading">
        <div class="section-row">
            <div>
                <span class="eyebrow">RINGKASAN AKTIVITAS</span>
                <h2 id="overview-heading">Statistik scan</h2>
            </div>
            <span class="overview-period">Semua waktu</span>
        </div>
        <div class="overview-grid">
            <article class="overview-stat total"><span>TOTAL SCAN</span><strong>{{ $totalScan }}</strong><small>File yang pernah diperiksa</small></article>
            <article class="overview-stat safe"><span>AMAN</span><strong>{{ $safeCount }}</strong><small>Tidak terdeteksi ancaman</small></article>
            <article class="overview-stat suspicious"><span>MENCURIGAKAN</span><strong>{{ $suspiciousCount }}</strong><small>Butuh perhatian</small></article>
            <article class="overview-stat malicious"><span>BERBAHAYA</span><strong>{{ $maliciousCount }}</strong><small>Ancaman terdeteksi</small></article>
        </div>
        <div class="operation-strip">
            <div><span class="operation-dot running"></span><strong>{{ $runningCount }}</strong> dalam antrean / diproses</div>
            <div><span class="operation-dot incomplete"></span><strong>{{ $incompleteCount }}</strong> gagal / dibatalkan / ditolak</div>
            <a href="{{ route('history') }}">Detail riwayat →</a>
        </div>
    </section>

    <section class="panel recent-card" aria-labelledby="recent-heading">
        <div class="section-row recent-heading">
            <div>
                <span class="eyebrow">ACTIVITY LOG</span>
                <h2 id="recent-heading">Scan terbaru</h2>
                <p class="muted">Aktivitas pemeriksaan file terbaru dalam akun Anda.</p>
            </div>
            <a class="btn" href="{{ route('history') }}">Semua riwayat</a>
        </div>

        @if($recentScans->isEmpty())
            <div class="empty-scans">
                <div class="empty-icon">SCAN</div>
                <h3>Belum ada scan</h3>
                <p class="muted">Mulai dengan memindai file atau URL pertama Anda.</p>
                <a class="btn primary" href="{{ route('scan.create') }}">+ Scan sekarang</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="data home-data">
                    <thead><tr><th>FILE</th><th>WAKTU</th><th>HASIL</th><th></th></tr></thead>
                    <tbody>
                    @foreach($recentScans as $scan)
                        @php
                            $scanStatus = $scan->status?->value;
                            $display = match ($scanStatus) {
                                'queued' => ['Antrean', 'queue'],
                                'processing' => ['Diproses', 'processing'],
                                'completed' => match ($scan->result_label?->value) {
                                    'safe' => ['Aman', 'safe'],
                                    'suspicious' => ['Mencurigakan', 'suspicious'],
                                    'malicious' => ['Berbahaya', 'malicious'],
                                    'unknown' => ['Tidak diketahui', 'unknown'],
                                    default => ['Selesai', 'unknown'],
                                },
                                'failed' => ['Gagal', 'failed'],
                                'cancelled' => ['Dibatalkan', 'failed'],
                                'rejected' => ['Ditolak', 'failed'],
                                default => ['—', 'unknown'],
                            };
                        @endphp
                        <tr>
                            <td><div class="scan-file-name"><span class="mini-file">FILE</span><span title="{{ $scan->original_filename }}">{{ $scan->original_filename }}</span></div></td>
                            <td class="scan-time">@if($scan->created_at)<time datetime="{{ $scan->created_at->toISOString() }}">{{ $scan->created_at->timezone(config('app.timezone'))->format('d M Y · H:i') }}</time>@endif</td>
                            <td><span class="result-chip {{ $display[1] }}">{{ $display[0] }}</span></td>
                            <td><a class="detail-link" href="{{ route('scans.show', $scan->id) }}">Lihat →</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>

<style>
.home-page{width:min(100%,960px);margin:0 auto;padding:6px 0 42px}.home-hero{display:flex;justify-content:space-between;align-items:center;gap:30px;padding:34px;margin-bottom:28px;overflow:hidden;background:linear-gradient(115deg,rgba(15,23,42,.92),rgba(32,40,57,.7))}.home-hero-copy{max-width:600px}.home-hero h1{margin:8px 0;font-size:clamp(32px,5vw,47px)}.home-hero p{margin:0;color:#a8b4c7;line-height:1.7;max-width:580px}.home-hero-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:22px}.home-main-cta{box-shadow:0 0 20px rgba(255,103,0,.25)}.home-hero-status{display:flex;align-items:center;gap:14px;min-width:205px;padding:16px;border:1px solid rgba(16,185,129,.22);border-radius:13px;background:rgba(16,185,129,.06)}.shield-mark{display:grid;place-items:center;width:52px;height:52px;border-radius:50%;background:rgba(16,185,129,.14);border:1px solid rgba(16,185,129,.35);color:#6ee7b7;font:10px monospace;font-weight:800}.home-hero-status strong,.home-hero-status small{display:block}.home-hero-status strong{font-size:14px;margin:3px 0}.home-hero-status small{color:#91a0b4;font-size:11px}.section-row{display:flex;justify-content:space-between;align-items:flex-end;gap:14px;margin-bottom:14px}.section-row h2{margin:5px 0 0;font-size:22px}.overview-period{font-size:12px;color:#64748b}.overview-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}.overview-stat{padding:17px;border:1px solid rgba(70,130,180,.2);border-radius:12px;background:rgba(15,23,42,.72)}.overview-stat span{display:block;font:10px monospace;color:#91a0b4}.overview-stat strong{display:block;font-size:28px;margin:7px 0 3px;color:#f8fafc}.overview-stat small{font-size:11px;color:#718096}.overview-stat.safe strong{color:#10B981}.overview-stat.suspicious strong{color:#FF8A38}.overview-stat.malicious strong{color:#EF4444}.operation-strip{display:flex;align-items:center;gap:18px;flex-wrap:wrap;margin:12px 0 28px;padding:12px 15px;border:1px solid rgba(148,163,184,.12);border-radius:10px;background:rgba(11,17,32,.42);font-size:12px;color:#94a3b8}.operation-strip strong{color:#f8fafc}.operation-strip a{margin-left:auto;color:#FF8A38;text-decoration:none}.operation-dot{display:inline-block;width:7px;height:7px;margin-right:6px;border-radius:50%}.operation-dot.running{background:#fbbf24;box-shadow:0 0 8px #fbbf24}.operation-dot.incomplete{background:#ef4444}.recent-card{padding:25px}.recent-heading{margin-bottom:17px}.recent-heading p{margin:4px 0 0;font-size:13px}.home-data td{vertical-align:middle}.scan-file-name{display:flex;align-items:center;gap:9px;max-width:300px;overflow:hidden}.scan-file-name>span:last-child{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.mini-file{display:grid;place-items:center;flex:none;width:30px;height:30px;border-radius:6px;background:rgba(255,103,0,.12);color:#FF8A38;font:8px monospace}.scan-time{color:#91a0b4;font-size:12px!important;white-space:nowrap}.result-chip{display:inline-block;padding:5px 9px;border-radius:99px;font-size:11px;font-weight:700}.result-chip.safe{background:rgba(16,185,129,.1);color:#6ee7b7}.result-chip.suspicious{background:rgba(255,103,0,.11);color:#FF8A38}.result-chip.malicious,.result-chip.failed{background:rgba(239,68,68,.1);color:#fca5a5}.result-chip.queue,.result-chip.processing{background:rgba(251,191,36,.1);color:#fbbf24}.result-chip.unknown{background:rgba(148,163,184,.12);color:#cbd5e1}.detail-link{color:#FF8A38;text-decoration:none;font-size:12px;font-weight:600}.empty-scans{text-align:center;padding:42px 20px;border:1px dashed rgba(70,130,180,.25);border-radius:12px}.empty-scans h3{margin:12px 0 4px}.empty-scans p{margin:0 0 16px}.empty-icon{display:grid;place-items:center;width:52px;height:52px;margin:auto;border-radius:50%;background:rgba(255,103,0,.1);color:#FF8A38;font:10px monospace}@media(max-width:720px){.home-hero{align-items:flex-start;flex-direction:column;padding:25px}.overview-grid{grid-template-columns:repeat(2,1fr)}.home-hero-status{width:100%;box-sizing:border-box}.operation-strip a{margin-left:0}.recent-card{padding:18px}.scan-time{display:none}.home-data th:nth-child(2){display:none}}@media(max-width:430px){.overview-grid{gap:8px}.overview-stat{padding:13px}.overview-stat strong{font-size:23px}.section-row{align-items:flex-start;flex-direction:column}.recent-heading .btn{width:100%;text-align:center}.home-hero-actions .btn{width:100%;text-align:center}}
</style>
@endsection
