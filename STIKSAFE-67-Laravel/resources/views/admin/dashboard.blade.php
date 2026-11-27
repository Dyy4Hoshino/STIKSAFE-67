@extends('layouts.app')
@section('title', 'Dashboard Admin | STIKSAFE-67')

@section('content')
@php
    $statusCards = [
        'queued' => ['Menunggu antrean', 'queue', 'hourglass_top'],
        'processing' => ['Sedang dianalisis', 'processing', 'sync'],
        'completed' => ['Selesai', 'completed', 'check_circle'],
        'failed' => ['Gagal', 'failed', 'error'],
        'cancelled' => ['Dibatalkan', 'cancelled', 'block'],
        'rejected' => ['Ditolak', 'rejected', 'gpp_bad'],
    ];
    $resultCards = [
        'safe' => ['Aman', '#006948'],
        'suspicious' => ['Mencurigakan', '#b45309'],
        'malicious' => ['Berbahaya', '#ba1a1a'],
        'unknown' => ['Indikasi netral', '#565e74'],
    ];
@endphp

<div class="admin-dashboard">
    <header class="admin-heading">
        <div>
            <span class="eyebrow">OPERATIONS / ADMIN CONSOLE</span>
            <h1>Dashboard admin</h1>
            <p class="muted">Pantau antrean, aktivitas scan, akun, dan kesehatan sistem dari satu tempat.</p>
        </div>
        <div class="admin-heading-actions">
            <a class="btn primary" href="{{ route('admin.notifications.create') }}"><span class="material-symbols-outlined">campaign</span>Kirim notifikasi</a>
            <a class="btn" href="{{ route('admin.audit-logs') }}"><span class="material-symbols-outlined">manage_search</span>Audit log</a>
        </div>
    </header>

    <section aria-labelledby="scan-status-heading">
        <div class="section-heading">
            <div>
                <span class="eyebrow">SYSTEM ACTIVITY</span>
                <h2 id="scan-status-heading">Status pemindaian</h2>
            </div>
            <a class="text-link" href="{{ route('admin.scans') }}">Buka scan log <span>→</span></a>
        </div>
        <div class="status-grid">
            @foreach($statusCards as $key => [$label, $class, $icon])
                <article class="status-card {{ $class }}">
                    <div class="status-card-top"><span>{{ $label }}</span><span class="material-symbols-outlined">{{ $icon }}</span></div>
                    <strong>{{ $scansByStatus[$key] ?? 0 }}</strong>
                    <small>Status scan</small>
                </article>
            @endforeach
        </div>
    </section>

    <div class="admin-content-grid">
        <section class="panel admin-card" aria-labelledby="results-heading">
            <div class="section-heading compact">
                <div><span class="eyebrow">DETECTION SUMMARY</span><h2 id="results-heading">Distribusi hasil</h2></div>
                <span class="material-symbols-outlined section-icon">donut_small</span>
            </div>
            <div class="result-list">
                @foreach($resultCards as $key => [$label, $color])
                    <div class="result-row">
                        <span class="result-dot" style="background:{{ $color }}"></span>
                        <span>{{ $label }}</span>
                        <strong>{{ $scansByResult[$key] ?? 0 }}</strong>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="panel admin-card" aria-labelledby="users-heading">
            <div class="section-heading compact">
                <div><span class="eyebrow">ACCOUNT OVERVIEW</span><h2 id="users-heading">Akun pengguna</h2></div>
                <a class="text-link" href="{{ route('admin.users') }}">Kelola →</a>
            </div>
            <div class="account-total">
                <strong>{{ $usersByStatus->sum() }}</strong>
                <span>Total akun terdaftar</span>
            </div>
            <div class="account-breakdown">
                @forelse($usersByStatus as $status => $count)
                    <div><span class="account-status {{ $status === 'active' ? 'active' : 'inactive' }}"></span>{{ ucfirst($status) }}<strong>{{ $count }}</strong></div>
                @empty
                    <div class="muted">Belum ada data akun.</div>
                @endforelse
            </div>
        </section>

        <section class="panel admin-card queue-card" aria-labelledby="queue-heading">
            <div class="section-heading compact">
                <div><span class="eyebrow">QUEUE MONITOR</span><h2 id="queue-heading">Antrean aktif</h2></div>
                <span class="material-symbols-outlined section-icon">stacked_line_chart</span>
            </div>
            <div class="queue-value"><strong>{{ $queueDepth }}</strong><span>job menunggu worker</span></div>
            <a class="btn" href="{{ route('admin.scans') }}">Pantau antrean</a>
        </section>

        <section class="panel admin-card trend-card" aria-labelledby="trend-heading">
            <div class="section-heading compact">
                <div><span class="eyebrow">ACTIVITY TREND</span><h2 id="trend-heading">Scan 30 hari terakhir</h2></div>
                <span class="updated-at">Diperbarui {{ $updatedAt->timezone(config('app.timezone'))->format('d M, H:i') }}</span>
            </div>
            @if($dailyScans->isEmpty())
                <p class="muted empty-state">Belum ada scan dalam 30 hari terakhir.</p>
            @else
                <div class="table-wrap">
                    <table class="data compact-table">
                        <thead><tr><th>Tanggal</th><th>Jumlah scan</th></tr></thead>
                        <tbody>
                            @foreach($dailyScans as $dailyScan)
                                <tr><td>{{ $dailyScan->date }}</td><td><strong>{{ $dailyScan->count }}</strong></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</div>

<style>
.admin-dashboard{width:min(100%,1120px);margin:0 auto}.admin-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;margin-bottom:32px}.admin-heading h1{margin:7px 0 5px}.admin-heading p{margin:0}.admin-heading-actions{display:flex;gap:10px;flex-wrap:wrap}.admin-heading-actions .btn{display:inline-flex;align-items:center;gap:7px}.admin-heading-actions .material-symbols-outlined{font-size:17px}.section-heading{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:14px}.section-heading h2{margin:4px 0 0;font-size:19px;letter-spacing:-.02em}.section-heading.compact{align-items:center}.section-icon{color:#a33900;font-size:21px}.text-link{color:#a33900;font-size:12px;font-weight:700}.text-link span{font-size:16px}.status-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:28px}.status-card{min-width:0;padding:16px;border:1px solid #e2e5e8;border-radius:12px;background:#fff;box-shadow:0 2px 8px rgba(25,28,30,.04)}.status-card-top{display:flex;justify-content:space-between;align-items:center;gap:8px;color:#565e74;font-size:11px;font-weight:600;line-height:1.3}.status-card-top .material-symbols-outlined{font-size:18px}.status-card strong{display:block;margin-top:13px;font-size:28px;line-height:1;color:#191c1e}.status-card small{display:block;margin-top:7px;color:#7a8194;font-size:10px}.status-card.queue .material-symbols-outlined,.status-card.queue strong{color:#b45309}.status-card.processing .material-symbols-outlined,.status-card.processing strong{color:#1769aa}.status-card.completed .material-symbols-outlined,.status-card.completed strong{color:#006948}.status-card.failed .material-symbols-outlined,.status-card.failed strong{color:#ba1a1a}.status-card.cancelled .material-symbols-outlined,.status-card.rejected .material-symbols-outlined{color:#7a8194}.admin-content-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.admin-card{padding:22px;background:rgba(255,255,255,.88);border-color:#e2e5e8;box-shadow:0 3px 12px rgba(25,28,30,.04)}.result-list{display:grid;gap:3px}.result-row{display:grid;grid-template-columns:10px 1fr auto;align-items:center;gap:10px;padding:12px 0;border-bottom:1px solid #edf0f2;color:#565e74;font-size:13px}.result-row:last-child{border-bottom:0}.result-row strong{color:#191c1e;font-size:16px}.result-dot{width:8px;height:8px;border-radius:50%}.account-total{display:flex;align-items:baseline;gap:10px;padding:8px 0 14px}.account-total strong{font-size:34px;color:#191c1e}.account-total span,.updated-at{color:#7a8194;font-size:11px}.account-breakdown{display:grid;grid-template-columns:1fr 1fr;gap:8px}.account-breakdown>div{display:flex;align-items:center;gap:7px;padding:10px;background:#f5f7fa;border-radius:8px;color:#565e74;font-size:12px}.account-breakdown strong{margin-left:auto;color:#191c1e}.account-status{width:7px;height:7px;border-radius:50%;background:#9ca3af}.account-status.active{background:#00875a}.queue-value{display:flex;align-items:baseline;gap:10px;margin:8px 0 20px}.queue-value strong{font-size:42px;color:#a33900}.queue-value span{font-size:12px;color:#7a8194}.queue-card .btn{margin-top:auto}.trend-card{grid-column:1/-1}.compact-table th,.compact-table td{padding:10px 8px}.empty-state{text-align:center;padding:22px 0}@media(max-width:950px){.status-grid{grid-template-columns:repeat(3,1fr)}}@media(max-width:700px){.admin-heading{align-items:flex-start;flex-direction:column;margin-bottom:24px}.admin-heading-actions{width:100%}.admin-heading-actions .btn{flex:1;text-align:center;justify-content:center}.status-grid{grid-template-columns:repeat(2,1fr);gap:9px}.admin-content-grid{grid-template-columns:1fr}.trend-card{grid-column:auto}.admin-card{padding:18px}}@media(max-width:420px){.status-card{padding:12px}.status-card strong{font-size:24px}.status-card-top{font-size:10px}}
</style>
@endsection
