@extends('layouts.app')
@section('title', 'Hasil Scan | STIKSAFE-67')

@section('content')
@php
    $status = $scan->status?->value;
    $label = $scan->result_label?->value;
    $labelText = match ($label) {
        'safe' => 'Aman',
        'suspicious' => 'Mencurigakan',
        'malicious' => 'Berbahaya',
        'unknown' => 'Tidak diketahui',
        default => '—',
    };
    $labelColor = match ($label) {
        'safe' => '#10B981',
        'malicious' => '#EF4444',
        default => '#FF8A38',
    };
    $reasonLabels = [
        'DOUBLE_EXTENSION' => 'Ekstensi ganda terdeteksi — indikasi penyamaran file.',
        'DISGUISED_EXECUTABLE' => 'File executable menyamar sebagai dokumen.',
        'ODD_EXTENSION_CHARS' => 'Karakter ekstensi tidak wajar atau disembunyikan.',
        'FORMAT_MISMATCH' => 'Isi file tidak sesuai dengan ekstensi namanya.',
        'NO_RISK_INDICATORS' => 'Pemeriksaan dasar selesai; tidak ada indikator berisiko.',
        'PDF_JAVASCRIPT' => 'PDF berisi JavaScript.',
        'PDF_AUTO_ACTION' => 'PDF menjalankan aksi otomatis saat dibuka.',
        'PDF_LAUNCH_ACTION' => 'PDF mencoba meluncurkan aplikasi eksternal.',
        'PDF_EMBEDDED_FILE' => 'PDF memuat file di dalamnya.',
        'ARCHIVE_NESTED_EXEC' => 'Arsip berisi file executable.',
        'OFFICE_MACRO' => 'Dokumen Office berisi macro aktif.',
    ];
@endphp

<div class="result-page">
    <div class="result-topbar">
        <div>
            <span class="eyebrow">FILE ANALYSIS / REPORT</span>
            <h1>Hasil pemeriksaan</h1>
            <p class="muted">Analisis statis selesai tanpa menjalankan file Anda.</p>
        </div>
        <a class="btn primary" href="{{ route('scan.create') }}">+ Scan Baru</a>
    </div>

    <section class="result-file-card panel">
        <div class="file-icon">FILE</div>
        <div class="file-info">
            <h2 title="{{ $scan->original_filename }}">{{ $scan->original_filename }}</h2>
            <p>{{ number_format($scan->file_size / 1024, 2, ',', '.') }} KB <span>·</span> {{ $scan->mime_type ?: 'Tipe tidak diketahui' }}</p>
        </div>
        <div class="status-pill status-{{ $status }}" id="scan-status-text">
            @if ($status === 'queued') Antrean
            @elseif ($status === 'processing') Diproses
            @elseif ($status === 'completed') Selesai
            @else {{ ucfirst($status ?? 'Tidak diketahui') }}
            @endif
        </div>
    </section>

    @if (in_array($status, ['queued', 'processing'], true))
        <section class="panel progress-card">
            <div class="loader-ring"></div>
            <span class="eyebrow">ANALYSIS RUNNING</span>
            <h2>Pemindaian sedang berlangsung</h2>
            <p class="muted">Hasil akan muncul otomatis. Tidak perlu me-refresh halaman.</p>
            <div class="progress-track"><div class="progress-bar"></div></div>
            <div class="progress-meta"><span>File dikarantina</span><span>ML Service aktif</span></div>
            <div class="actions centered-actions">
                <a class="btn" href="{{ route('scans.cancel', $scan->id) }}">Batalkan scan</a>
            </div>
        </section>
    @elseif ($status === 'completed')
        <section class="panel verdict-card">
            <span class="eyebrow">ANALYSIS COMPLETE</span>
            <div class="verdict-badge" style="--verdict-color:{{ $labelColor }}">
                <span class="verdict-dot"></span>{{ $labelText }}
            </div>
            <p class="verdict-caption">
                @if ($label === 'safe') Tidak terdeteksi ancaman oleh pemindai kami.
                @elseif ($label === 'suspicious') Ditemukan ciri yang memerlukan perhatian.
                @elseif ($label === 'malicious') Ancaman terdeteksi. Jangan buka atau bagikan file ini.
                @else Pemeriksaan belum dapat memastikan risiko file ini.
                @endif
            </p>

            <div class="result-grid">
                @if ($scan->confidence !== null)
                    <div class="result-stat"><span>CONFIDENCE</span><strong>{{ $scan->confidence }}</strong><small>Skala 0–1</small></div>
                @endif
                <div class="result-stat"><span>MODEL / RULESET</span><strong>{{ $scan->model_version ?: '—' }}</strong><small>Engine pemrosesan</small></div>
                <div class="result-stat"><span>SCAN ID</span><strong>{{ substr($scan->id, 0, 12) }}…</strong><small>Identitas pemeriksaan</small></div>
            </div>

            <div class="reason-box">
                <div class="reason-heading"><span class="eyebrow">REASON / ANALYSIS</span><span class="reason-count">{{ count($scan->reason_codes ?? []) }} kode</span></div>
                @if (!empty($scan->reason_codes))
                    <ul class="reason-list">
                        @foreach ($scan->reason_codes as $code)
                            <li><span class="reason-mark">✓</span><span>{{ $reasonLabels[$code] ?? $code }} <code>{{ $code }}</code></span></li>
                        @endforeach
                    </ul>
                @else
                    <p class="muted empty-reason">Analisis selesai tanpa kode peringatan khusus.</p>
                @endif
            </div>
            <p class="disclaimer">Hasil merupakan prediksi model/pemeriksaan aturan, bukan jaminan keamanan mutlak. Versi preprocessing: {{ $scan->preprocessing_version ?: '—' }}.</p>
        </section>
    @else
        <section class="panel incomplete-card">
            <div class="warning-icon">!</div>
            <span class="eyebrow">ANALYSIS INCOMPLETE</span>
            <h2>Pemindaian tidak selesai</h2>
            <p class="muted">Status: {{ ucfirst($status ?? 'Tidak diketahui') }}.</p>
            @if ($scan->error_message)<p class="error-box">{{ $scan->error_message }} <code>{{ $scan->error_code }}</code></p>@endif
        </section>
    @endif

    @if ($status === 'completed')
        <section class="panel feedback-card">
            @if ($scan->feedback)
                <span class="eyebrow">FEEDBACK TERKIRIM</span>
                <p class="muted">{{ $scan->feedback->comment ?: 'Terima kasih atas masukan Anda.' }}</p>
            @else
                <span class="eyebrow">BANTU KAMI MENINGKATKAN MODEL</span>
                <form method="POST" action="{{ route('scans.feedback.store', $scan->id) }}" class="feedback-form">
                    @csrf
                    <select name="type" required><option value="false_positive">Seharusnya aman</option><option value="false_negative">Seharusnya berbahaya</option><option value="other">Catatan lainnya</option></select>
                    <input name="comment" maxlength="1000" placeholder="Komentar opsional">
                    <button type="submit" class="btn">Kirim feedback</button>
                </form>
            @endif
        </section>
    @endif

    <div class="result-actions">
        <a class="btn primary" href="{{ route('scan.create') }}">Scan File / URL Lain</a>
        <a class="btn" href="{{ route('history') }}">Riwayat</a>
        <a class="btn" href="{{ route('home') }}">Beranda</a>
    </div>
</div>

@if (in_array($status, ['queued', 'processing'], true))
<script>
(function () {
    const pollUrl = @json(route('scans.status', $scan->id));
    const interval = setInterval(async () => {
        try {
            const response = await fetch(pollUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
            if (response.ok && (await response.json()).is_terminal) {
                clearInterval(interval);
                window.location.reload();
            }
        } catch (_) {}
    }, 2000);
})();
</script>
@endif

<style>
.result-page{width:min(100%,820px);margin:0 auto;padding:4px 0 40px}.result-topbar{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:22px}.result-topbar h1{margin:8px 0 4px}.result-topbar p{margin:0}.result-file-card{display:flex;align-items:center;gap:15px;padding:16px 20px;margin-bottom:16px}.file-icon{display:grid;place-items:center;width:46px;height:46px;border-radius:10px;background:rgba(255,103,0,.14);color:#FF8A38;font:10px 'JetBrains Mono',monospace;font-weight:700}.file-info{min-width:0;flex:1}.file-info h2{font-size:16px;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.file-info p{font-size:12px;color:#91a0b4;margin:5px 0 0}.file-info p span{margin:0 5px;color:#475569}.status-pill{padding:6px 11px;border-radius:99px;font-size:11px;font-weight:700;white-space:nowrap}.status-queued,.status-processing{color:#fbbf24;background:rgba(251,191,36,.1)}.status-completed{color:#6ee7b7;background:rgba(16,185,129,.1)}.status-failed,.status-cancelled,.status-rejected{color:#fca5a5;background:rgba(239,68,68,.1)}.progress-card{text-align:center;padding:42px 28px}.loader-ring{width:54px;height:54px;margin:0 auto 20px;border:3px solid rgba(255,103,0,.18);border-top-color:#FF6700;border-radius:50%;animation:spin 1s linear infinite}.progress-card h2{margin:8px 0}.progress-card p{margin:0}.progress-track{height:6px;max-width:450px;margin:26px auto 8px;background:#1e293b;border-radius:99px;overflow:hidden}.progress-bar{height:100%;width:40%;background:linear-gradient(90deg,#FF6700,#fbbf24);border-radius:99px;animation:progress 2.2s ease-in-out infinite}.progress-meta{display:flex;justify-content:space-between;max-width:450px;margin:auto;color:#64748b;font-size:11px}.centered-actions{justify-content:center}.verdict-card{padding:30px}.verdict-badge{display:inline-flex;align-items:center;gap:9px;margin:20px 0 8px;color:var(--verdict-color);font-size:30px;font-weight:800}.verdict-dot{width:12px;height:12px;border-radius:50%;background:var(--verdict-color);box-shadow:0 0 14px var(--verdict-color)}.verdict-caption{color:#cbd5e1;margin:0 0 24px}.result-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:18px}.result-stat{padding:14px;border:1px solid rgba(148,163,184,.14);border-radius:10px;background:rgba(11,17,32,.55)}.result-stat span{display:block;color:#91a0b4;font:10px 'JetBrains Mono',monospace}.result-stat strong{display:block;color:#f8fafc;font-size:13px;margin-top:7px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.result-stat small{color:#64748b;font-size:10px}.reason-box{padding:16px;border:1px solid rgba(255,103,0,.2);border-radius:11px;background:rgba(255,103,0,.04)}.reason-heading{display:flex;justify-content:space-between;align-items:center}.reason-count{color:#91a0b4;font-size:11px}.reason-list{list-style:none;padding:0;margin:12px 0 0;display:grid;gap:9px}.reason-list li{display:flex;gap:9px;align-items:flex-start;color:#e2e8f0;font-size:13px}.reason-mark{color:#10B981;font-weight:700}.reason-list code{display:block;color:#64748b;font-size:10px;margin-top:2px}.empty-reason{font-size:13px;margin:12px 0 0}.disclaimer{font-size:11px;color:#64748b;line-height:1.6;margin:18px 0 0}.incomplete-card{text-align:center;padding:38px}.warning-icon{display:grid;place-items:center;width:44px;height:44px;margin:0 auto 14px;border-radius:50%;background:rgba(239,68,68,.12);color:#fca5a5;font-size:24px;font-weight:700}.error-box{padding:12px;border-radius:8px;background:rgba(239,68,68,.08);color:#fca5a5;font-size:13px}.feedback-card{margin-top:16px;padding:18px}.feedback-form{display:flex;gap:8px;margin-top:12px}.feedback-form select,.feedback-form input{min-width:0;flex:1;background:#0b1120;border:1px solid rgba(70,130,180,.35);border-radius:8px;color:#f8fafc;padding:10px;font:inherit;font-size:12px}.result-actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:22px}@keyframes spin{to{transform:rotate(360deg)}}@keyframes progress{0%{transform:translateX(-120%)}100%{transform:translateX(340%)}}@media(max-width:620px){.result-topbar{align-items:flex-start;flex-direction:column}.result-file-card{align-items:flex-start;flex-wrap:wrap}.status-pill{margin-left:61px}.result-grid{grid-template-columns:1fr}.feedback-form{flex-direction:column}.verdict-card{padding:22px}.verdict-badge{font-size:24px}}
</style>
@endsection
