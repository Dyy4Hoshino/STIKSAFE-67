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
    $labelTone = match ($label) {
        'safe' => ['color' => '#006948', 'bg' => '#ecfdf5', 'border' => '#a7f3d0'],
        'malicious' => ['color' => '#ba1a1a', 'bg' => '#fef2f2', 'border' => '#fecaca'],
        'suspicious' => ['color' => '#b45309', 'bg' => '#fffbeb', 'border' => '#fde68a'],
        default => ['color' => '#565e74', 'bg' => '#f2f4f6', 'border' => '#e6e8ea'],
    };
    $reasonLabels = [
        'DOUBLE_EXTENSION' => 'Ekstensi ganda terdeteksi — indikasi penyamaran file.',
        'DISGUISED_EXECUTABLE' => 'File executable menyamar sebagai dokumen atau gambar.',
        'ODD_EXTENSION_CHARS' => 'Karakter ekstensi tidak wajar atau disembunyikan.',
        'FORMAT_MISMATCH' => 'Isi file tidak sesuai dengan ekstensi namanya.',
        'NO_RISK_INDICATORS' => 'Pemeriksaan dasar selesai; tidak ditemukan ciri berisiko.',
        'PDF_JAVASCRIPT' => 'PDF berisi JavaScript tertanam.',
        'PDF_AUTO_ACTION' => 'PDF menjalankan aksi otomatis saat dibuka.',
        'PDF_LAUNCH_ACTION' => 'PDF mencoba meluncurkan aplikasi eksternal.',
        'PDF_EMBEDDED_FILE' => 'PDF memuat berkas tertanam di dalamnya.',
        'ARCHIVE_CONTAINS_EXECUTABLE' => 'Arsip berisi berkas executable.',
        'ARCHIVE_DOUBLE_EXTENSION' => 'Arsip berisi berkas berekstensi ganda.',
        'ARCHIVE_NESTED_EXEC' => 'Arsip berisi file executable.',
        'OFFICE_MACRO' => 'Dokumen Office berisi makro aktif.',
        'IMAGE_EMBEDDED_PAYLOAD' => 'Gambar memiliki muatan tersembunyi di bagian akhir file.',
        'LNK_FILE' => 'Berkas shortcut Windows (.lnk) berisiko tinggi pada unggahan web.',
        'SCRIPT_RISKY_PATTERN' => 'Ditemukan pola skrip berisiko.',
    ];
@endphp

<div class="result-page">
    <div class="result-topbar">
        <div>
            <span class="eyebrow">LAPORAN ANALISIS FILE</span>
            <h1>Hasil Pemeriksaan</h1>
            <p class="muted" style="margin:0">Analisis statis dilakukan tanpa menjalankan berkas Anda.</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a class="btn" href="{{ route('history') }}">Riwayat</a>
            <a class="btn primary" href="{{ route('scan.create') }}">+ Scan Baru</a>
        </div>
    </div>

    <section class="panel result-file-card">
        <div class="file-icon"><span class="material-symbols-outlined" style="font-size:22px">description</span></div>
        <div class="file-info">
            <h2 title="{{ $scan->original_filename }}">{{ $scan->original_filename }}</h2>
            <p>
                {{ number_format($scan->file_size / 1024, 2, ',', '.') }} KB
                <span>·</span>
                {{ $scan->mime_type ?: 'Tipe tidak diketahui' }}
            </p>
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
            <span class="eyebrow">SEDANG DIPROSES</span>
            <h2>Pemindaian sedang berlangsung</h2>
            <p class="muted" style="margin:0">Hasil akan muncul otomatis tanpa perlu me-refresh halaman.</p>
            <div class="progress-track"><div class="progress-bar"></div></div>
            <div class="progress-meta"><span>File dikarantina</span><span>ML Service aktif</span></div>
            <div class="actions centered-actions">
                <a class="btn" href="{{ route('scans.cancel', $scan->id) }}">Batalkan scan</a>
            </div>
        </section>
    @elseif ($status === 'completed')
        <section class="panel verdict-card" style="border-left:4px solid {{ $labelTone['color'] }}">
            <span class="eyebrow">HASIL PEMERIKSAAN</span>
            <div style="margin:14px 0 8px">
                <span class="verdict-badge" style="color:{{ $labelTone['color'] }};background:{{ $labelTone['bg'] }};border-color:{{ $labelTone['border'] }}">
                    <span class="verdict-dot" style="background:{{ $labelTone['color'] }}"></span>
                    {{ $labelText }}
                </span>
            </div>
            <p class="verdict-caption">
                @if ($label === 'safe') Tidak terdeteksi ancaman oleh pemindai kami.
                @elseif ($label === 'suspicious') Ditemukan ciri yang memerlukan perhatian. Jangan langsung membuka file ini.
                @elseif ($label === 'malicious') Ancaman terdeteksi. Jangan buka atau bagikan file ini.
                @else Pemeriksaan dasar selesai; belum dapat memastikan risiko file ini.
                @endif
            </p>

            <div class="result-grid">
                @if ($scan->confidence !== null)
                    <div class="result-stat">
                        <span>CONFIDENCE</span>
                        <strong class="tabular-nums">{{ number_format((float) $scan->confidence, 4) }}</strong>
                        <small>Skala 0–1</small>
                    </div>
                @endif
                <div class="result-stat">
                    <span>MODEL / RULESET</span>
                    <strong>{{ $scan->model_version ?: '—' }}</strong>
                    <small>Engine pemrosesan</small>
                </div>
                <div class="result-stat">
                    <span>SCAN ID</span>
                    <strong>{{ substr($scan->id, 0, 12) }}…</strong>
                    <small>Identitas pemeriksaan</small>
                </div>
            </div>

            <div class="reason-box">
                <div class="reason-heading">
                    <span class="eyebrow">INDIKATOR &amp; ALASAN</span>
                    <span class="reason-count">{{ count($scan->reason_codes ?? []) }} kode</span>
                </div>
                @if (!empty($scan->reason_codes))
                    <ul class="reason-list">
                        @foreach ($scan->reason_codes as $code)
                            <li>
                                <span class="reason-mark">●</span>
                                <div>
                                    <span>{{ $reasonLabels[$code] ?? $code }}</span>
                                    <code>{{ $code }}</code>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="muted" style="font-size:13px;margin:10px 0 0">Analisis selesai tanpa kode peringatan khusus.</p>
                @endif
            </div>

            <p class="disclaimer">Hasil merupakan prediksi model dan pemeriksaan aturan awal, bukan jaminan keamanan mutlak. Versi preprocessing: <code>{{ $scan->preprocessing_version ?: '—' }}</code>.</p>
        </section>
    @else
        <section class="panel incomplete-card">
            <div class="warning-icon">!</div>
            <span class="eyebrow" style="color:#ba1a1a">TIDAK SELESAI</span>
            <h2>Pemindaian tidak selesai</h2>
            <p class="muted" style="margin:0 0 12px">Status: {{ ucfirst($status ?? 'Tidak diketahui') }}.</p>
            @if ($scan->error_message)
                <p class="error-box">{{ $scan->error_message }} <code>{{ $scan->error_code }}</code></p>
            @endif
        </section>
    @endif

    @if ($status === 'completed')
        <section class="panel feedback-card">
            @if ($scan->feedback)
                <span class="eyebrow" style="color:#006948">FEEDBACK TERKIRIM</span>
                <p class="muted" style="margin:6px 0 0">{{ $scan->feedback->comment ?: 'Terima kasih atas masukan Anda.' }}</p>
            @else
                <span class="eyebrow">BANTU TINGKATKAN MODEL</span>
                <form method="POST" action="{{ route('scans.feedback.store', $scan->id) }}" class="feedback-form">
                    @csrf
                    <select name="type" required>
                        <option value="false_positive">Seharusnya aman</option>
                        <option value="false_negative">Seharusnya berbahaya</option>
                        <option value="other">Catatan lainnya</option>
                    </select>
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
(() => {
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
.result-page { width: min(100%, 840px); margin: 0 auto; }
.result-topbar { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
.result-topbar h1 { margin: 4px 0; }
.result-file-card { display: flex; align-items: center; gap: 14px; padding: 16px 20px; margin-bottom: 16px; }
.file-icon {
    width: 44px; height: 44px; border-radius: 9px;
    background: #f2f4f6; color: #a33900;
    display: grid; place-items: center; flex-shrink: 0;
}
.file-info { min-width: 0; flex: 1; }
.file-info h2 { font-size: 16px; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.file-info p { font-size: 12.5px; color: #565e74; margin: 4px 0 0; }
.file-info p span { margin: 0 5px; color: #cbd5e1; }
.status-pill { padding: 5px 12px; border-radius: 999px; font-size: 12px; font-weight: 600; white-space: nowrap; }
.status-queued, .status-processing { color: #1d4ed8; background: #eff6ff; border: 1px solid #bfdbfe; }
.status-completed { color: #006948; background: #ecfdf5; border: 1px solid #a7f3d0; }
.status-failed, .status-cancelled, .status-rejected { color: #ba1a1a; background: #fef2f2; border: 1px solid #fecaca; }

.progress-card { text-align: center; padding: 40px 24px; }
.loader-ring { width: 46px; height: 46px; margin: 0 auto 18px; border: 3px solid #eceef0; border-top-color: #a33900; border-radius: 50%; animation: spin 0.9s linear infinite; }
.progress-card h2 { margin: 6px 0; }
.progress-track { height: 6px; max-width: 420px; margin: 22px auto 8px; background: #eceef0; border-radius: 999px; overflow: hidden; }
.progress-bar { height: 100%; width: 40%; background: #a33900; border-radius: 999px; animation: progress 2s ease-in-out infinite; }
.progress-meta { display: flex; justify-content: space-between; max-width: 420px; margin: 0 auto; color: #565e74; font-size: 11.5px; }
.centered-actions { justify-content: center; }

.verdict-card { padding: 28px; }
.verdict-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 16px; border-radius: 999px; border: 1px solid;
    font-family: 'Plus Jakarta Sans', sans-serif; font-size: 20px; font-weight: 700;
}
.verdict-dot { width: 9px; height: 9px; border-radius: 999px; }
.verdict-caption { color: #565e74; margin: 6px 0 22px; font-size: 14.5px; }

.result-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 18px; }
.result-stat { padding: 14px 16px; border: 1px solid #e6e8ea; border-radius: 8px; background: #f7f9fb; }
.result-stat span { display: block; color: #565e74; font-family: 'JetBrains Mono', monospace; font-size: 10.5px; font-weight: 600; }
.result-stat strong { display: block; color: #191c1e; font-family: 'JetBrains Mono', monospace; font-size: 13px; margin-top: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.result-stat small { color: #64748b; font-size: 11px; }

.reason-box { padding: 16px 18px; border: 1px solid #e6e8ea; border-radius: 8px; background: #f7f9fb; }
.reason-heading { display: flex; justify-content: space-between; align-items: center; }
.reason-count { color: #565e74; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; }
.reason-list { list-style: none; padding: 0; margin: 12px 0 0; display: grid; gap: 10px; }
.reason-list li { display: flex; gap: 10px; align-items: flex-start; font-size: 13.5px; color: #191c1e; }
.reason-mark { color: #a33900; font-size: 10px; margin-top: 4px; }
.reason-list code { display: inline-block; margin-top: 3px; padding: 2px 6px; border-radius: 4px; background: #ffffff; border: 1px solid #e6e8ea; color: #565e74; font-size: 11px; }
.disclaimer { font-size: 12px; color: #64748b; line-height: 1.6; margin: 18px 0 0; }

.incomplete-card { text-align: center; padding: 36px 24px; }
.warning-icon { display: grid; place-items: center; width: 44px; height: 44px; margin: 0 auto 12px; border-radius: 999px; background: #fef2f2; border: 1px solid #fecaca; color: #ba1a1a; font-size: 22px; font-weight: 700; }
.error-box { display: inline-block; padding: 10px 14px; border-radius: 8px; background: #fef2f2; border: 1px solid #fecaca; color: #93000a; font-size: 13px; }

.feedback-card { margin-top: 16px; padding: 20px; }
.feedback-form { display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap; }
.feedback-form select, .feedback-form input {
    min-width: 180px; flex: 1;
    background: #ffffff; border: 1px solid #d8dadc; border-radius: 6px;
    color: #191c1e; padding: 9px 12px; font: inherit; font-size: 13.5px;
}
.result-actions { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-top: 22px; }

@keyframes spin { to { transform: rotate(360deg); } }
@keyframes progress { 0% { transform: translateX(-120%); } 100% { transform: translateX(340%); } }
@media (max-width: 640px) {
    .result-grid { grid-template-columns: 1fr; }
    .feedback-form { flex-direction: column; }
    .verdict-card { padding: 20px; }
}
</style>
@endsection
