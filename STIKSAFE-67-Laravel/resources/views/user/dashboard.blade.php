@extends('layouts.app')
@section('title', 'Beranda | STIKSAFE-67')

@section('content')
@php
    $firstName = explode(' ', trim($user->name))[0] ?: $user->name;
@endphp

<div class="dash-wrap">
    {{-- Welcome Card — exact original copy --}}
    <section class="panel welcome-card">
        <div class="welcome-copy">
            <span class="eyebrow">STIKSAFE / SECURITY CENTER</span>
            <h1>Halo, {{ $firstName }}.</h1>
            <p class="muted" style="margin:4px 0 0;max-width:580px">Periksa file atau URL mencurigakan, pantau hasil, lalu ambil tindakan dengan informasi yang jelas.</p>
            <div class="welcome-actions">
                <a class="btn primary" href="{{ route('scan.create') }}">+ Mulai Scan Baru</a>
                <a class="btn" href="{{ route('history') }}">Lihat riwayat</a>
            </div>
        </div>
        <div class="status-box" aria-label="Status layanan">
            <div class="shield-mark">SAFE</div>
            <div>
                <span class="eyebrow" style="color:#006948;font-size:10px">SYSTEM STATUS</span>
                <strong>Siap memindai</strong>
                <span>File &amp; URL analysis</span>
            </div>
        </div>
    </section>

    {{-- Section: Statistik scan — exact original copy --}}
    <section class="dash-section" aria-labelledby="overview-heading">
        <div class="section-bar">
            <div>
                <span class="eyebrow">RINGKASAN AKTIVITAS</span>
                <h2 id="overview-heading" style="margin:3px 0 0">Statistik scan</h2>
            </div>
            <span class="period-pill">Semua waktu</span>
        </div>

        <div class="kpi-grid">
            <article class="kpi-card">
                <div class="kpi-head">
                    <span class="kpi-label">TOTAL SCAN</span>
                    <span class="kpi-icon"><span class="material-symbols-outlined" style="font-size:18px">query_stats</span></span>
                </div>
                <strong class="kpi-num tabular-nums">{{ $totalScan }}</strong>
                <small class="kpi-sub">File yang pernah diperiksa</small>
            </article>

            <article class="kpi-card">
                <div class="kpi-head">
                    <span class="kpi-label"><span class="dot dot-safe"></span>AMAN</span>
                    <span class="kpi-icon kpi-icon-safe"><span class="material-symbols-outlined" style="font-size:18px">verified_user</span></span>
                </div>
                <strong class="kpi-num tabular-nums" style="color:#006948">{{ $safeCount }}</strong>
                <small class="kpi-sub">Tidak terdeteksi ancaman</small>
            </article>

            <article class="kpi-card">
                <div class="kpi-head">
                    <span class="kpi-label"><span class="dot dot-warn"></span>MENCURIGAKAN</span>
                    <span class="kpi-icon kpi-icon-warn"><span class="material-symbols-outlined" style="font-size:18px">warning</span></span>
                </div>
                <strong class="kpi-num tabular-nums" style="color:#b45309">{{ $suspiciousCount }}</strong>
                <small class="kpi-sub">Butuh perhatian</small>
            </article>

            <article class="kpi-card">
                <div class="kpi-head">
                    <span class="kpi-label"><span class="dot dot-danger"></span>BERBAHAYA</span>
                    <span class="kpi-icon kpi-icon-danger"><span class="material-symbols-outlined" style="font-size:18px">gpp_bad</span></span>
                </div>
                <strong class="kpi-num tabular-nums" style="color:#ba1a1a">{{ $maliciousCount }}</strong>
                <small class="kpi-sub">Ancaman terdeteksi</small>
            </article>
        </div>

        <div class="pipeline-bar">
            <div class="pipeline-info">
                <span class="dot dot-warn"></span><strong>{{ $runningCount }}</strong> dalam antrean / diproses
                <span style="margin:0 8px;color:#cbd5e1">·</span>
                <span class="dot dot-danger"></span><strong>{{ $incompleteCount }}</strong> gagal / dibatalkan / ditolak
            </div>
            <a class="pipeline-link" href="{{ route('history') }}">Detail riwayat →</a>
        </div>
    </section>

    {{-- Section: Scan terbaru — exact original copy --}}
    <section class="panel recent-card" aria-labelledby="recent-heading">
        <div class="recent-head">
            <div>
                <span class="eyebrow">ACTIVITY LOG</span>
                <h2 id="recent-heading" style="margin:3px 0 2px;font-size:19px">Scan terbaru</h2>
                <p class="muted" style="margin:0;font-size:13px">Aktivitas pemeriksaan file terbaru dalam akun Anda.</p>
            </div>
            <a class="btn" href="{{ route('history') }}">Semua riwayat</a>
        </div>

        @if($recentScans->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">SCAN</div>
                <h3>Belum ada scan</h3>
                <p class="muted">Mulai dengan memindai file atau URL pertama Anda.</p>
                <a class="btn primary" href="{{ route('scan.create') }}">+ Scan sekarang</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>FILE</th>
                            <th class="col-time">WAKTU</th>
                            <th>HASIL</th>
                            <th style="text-align:right"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($recentScans as $scan)
                        @php
                            $scanStatus = $scan->status?->value;
                            $display = match ($scanStatus) {
                                'queued' => ['Antrean', 'queue'],
                                'processing' => ['Diproses', 'queue'],
                                'completed' => match ($scan->result_label?->value) {
                                    'safe' => ['Aman', 'safe'],
                                    'suspicious' => ['Mencurigakan', 'warn'],
                                    'malicious' => ['Berbahaya', 'danger'],
                                    'unknown' => ['Tidak diketahui', 'neutral'],
                                    default => ['Selesai', 'neutral'],
                                },
                                'failed' => ['Gagal', 'danger'],
                                'cancelled' => ['Dibatalkan', 'neutral'],
                                'rejected' => ['Ditolak', 'danger'],
                                default => ['—', 'neutral'],
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="file-cell">
                                    <span class="mini-file">FILE</span>
                                    <span class="file-name-text" title="{{ $scan->original_filename }}">{{ $scan->original_filename }}</span>
                                </div>
                            </td>
                            <td class="col-time muted" style="font-size:12.5px;white-space:nowrap">
                                @if($scan->created_at)
                                    <time datetime="{{ $scan->created_at->toISOString() }}">{{ $scan->created_at->timezone(config('app.timezone'))->format('d M Y · H:i') }}</time>
                                @endif
                            </td>
                            <td>
                                <span class="verdict-pill pill-{{ $display[1] }}">
                                    <span class="pill-dot-sm"></span>{{ $display[0] }}
                                </span>
                            </td>
                            <td style="text-align:right">
                                <a class="detail-link" href="{{ route('scans.show', $scan->id) }}">Lihat →</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>

<style>
.dash-wrap { display: flex; flex-direction: column; gap: 24px; }
.welcome-card { display: flex; align-items: center; justify-content: space-between; gap: 24px; flex-wrap: wrap; padding: 28px; }
.welcome-copy h1 { margin: 6px 0 4px; font-size: clamp(26px, 3.6vw, 34px); }
.welcome-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 18px; }

.status-box {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 18px;
    min-width: 225px;
    border-radius: 10px;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
}
.shield-mark {
    width: 46px;
    height: 46px;
    border-radius: 999px;
    background: #ffffff;
    border: 1px solid #a7f3d0;
    color: #006948;
    font: 700 10px 'JetBrains Mono', monospace;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.status-box strong { display: block; font-size: 14px; color: #191c1e; margin: 2px 0; }
.status-box span { display: block; font-size: 11.5px; color: #565e74; }

.section-bar { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
.period-pill { padding: 4px 10px; border-radius: 999px; background: #f2f4f6; border: 1px solid #e6e8ea; font-size: 12px; color: #565e74; }

.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.kpi-card { background: #ffffff; border: 1px solid #e6e8ea; border-radius: 10px; padding: 18px 20px; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04); }
.kpi-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.kpi-label { display: inline-flex; align-items: center; gap: 6px; font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 600; color: #565e74; }
.dot { width: 7px; height: 7px; border-radius: 999px; display: inline-block; }
.dot-safe { background: #006948; }
.dot-warn { background: #b45309; }
.dot-danger { background: #ba1a1a; }

.kpi-icon { width: 32px; height: 32px; border-radius: 7px; background: #f2f4f6; color: #565e74; display: grid; place-items: center; }
.kpi-icon-safe { color: #006948; }
.kpi-icon-warn { color: #b45309; }
.kpi-icon-danger { color: #ba1a1a; }
.kpi-num { display: block; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 32px; font-weight: 700; line-height: 1.1; color: #191c1e; margin-bottom: 4px; }
.kpi-sub { font-size: 12px; color: #64748b; }

.pipeline-bar {
    margin-top: 12px;
    padding: 12px 16px;
    background: #ffffff;
    border: 1px solid #e6e8ea;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    font-size: 12.5px;
    color: #565e74;
}
.pipeline-info { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.pipeline-info strong { color: #191c1e; }
.pipeline-link { font-weight: 600; color: #a33900; }
.pipeline-link:hover { text-decoration: underline; }

.recent-card { padding: 24px; }
.recent-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 14px; flex-wrap: wrap; padding-bottom: 16px; margin-bottom: 16px; border-bottom: 1px solid #eceef0; }

.empty-state { padding: 44px 16px; text-align: center; border: 1.5px dashed #d8dadc; border-radius: 10px; background: #f7f9fb; }
.empty-icon { width: 52px; height: 52px; margin: 0 auto 12px; border-radius: 999px; background: #fff3ed; border: 1px solid #ffdbce; color: #a33900; font: 700 10px 'JetBrains Mono', monospace; display: grid; place-items: center; }
.empty-state h3 { margin: 0 0 4px; font-size: 16px; }
.empty-state p { margin: 0 0 18px; font-size: 13.5px; }

.file-cell { display: flex; align-items: center; gap: 10px; max-width: 340px; }
.mini-file { width: 32px; height: 28px; border-radius: 6px; background: #fff3ed; color: #a33900; font: 700 9px 'JetBrains Mono', monospace; display: grid; place-items: center; flex-shrink: 0; }
.file-name-text { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 500; }
.verdict-pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; border: 1px solid #e6e8ea; background: #f7f9fb; color: #191c1e; white-space: nowrap; }
.pill-dot-sm { width: 6px; height: 6px; border-radius: 999px; background: #565e74; }
.pill-safe { background: #ecfdf5; border-color: #a7f3d0; color: #006948; }
.pill-safe .pill-dot-sm { background: #006948; }
.pill-warn { background: #fffbeb; border-color: #fde68a; color: #b45309; }
.pill-warn .pill-dot-sm { background: #b45309; }
.pill-danger { background: #fef2f2; border-color: #fecaca; color: #ba1a1a; }
.pill-danger .pill-dot-sm { background: #ba1a1a; }
.pill-queue { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.pill-queue .pill-dot-sm { background: #1d4ed8; }
.detail-link { color: #a33900; font-size: 13px; font-weight: 600; white-space: nowrap; }
.detail-link:hover { text-decoration: underline; }

@media (max-width: 900px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px) {
    .dash-wrap { gap: 18px; }
    .welcome-card { padding: 18px; gap: 16px; }
    .welcome-actions { width: 100%; flex-direction: column; }
    .welcome-actions .btn { width: 100%; }
    .status-box { width: 100%; min-width: 0; }
    .kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .kpi-card { padding: 14px; }
    .kpi-label { font-size: 10px; }
    .kpi-num { font-size: 24px; }
    .kpi-sub { font-size: 11px; }
    .pipeline-bar { padding: 12px 14px; font-size: 12px; }
    .recent-card { padding: 16px; }
    .recent-head .btn { width: 100%; }
    .file-cell { max-width: 170px; }
    .col-time { display: none; }
}
</style>
@endsection
