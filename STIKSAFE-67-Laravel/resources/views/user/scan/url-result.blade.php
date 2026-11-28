@extends('layouts.app')
@section('title', 'Hasil URL | STIKSAFE-67')

@section('content')
@php
    $urlLabel = $result['result']['label'] ?? 'unknown';
    $urlLabelText = match ($urlLabel) {
        'malicious' => 'Berbahaya',
        'suspicious' => 'Mencurigakan',
        default => 'Indikasi netral',
    };
    $urlToneClass = match ($urlLabel) {
        'malicious' => 'tone-malicious',
        'suspicious' => 'tone-suspicious',
        default => 'tone-neutral',
    };
    $urlReasons = [
        'URL_BLOCKLIST_MATCH' => 'URL cocok dengan daftar hitam ancaman.',
        'URL_IP_HOST' => 'URL menggunakan alamat IP langsung sebagai host.',
        'URL_NONSTANDARD_PORT' => 'URL menggunakan port jaringan tidak standar.',
        'URL_USERINFO' => 'URL mengandung informasi pengguna (@) yang sering dipakai penyamaran.',
        'URL_EXEC_DOWNLOAD' => 'URL mengarah pada unduhan file executable.',
        'URL_PUNYCODE' => 'Host menggunakan karakter Punycode (xn--).',
        'URL_MANY_SUBDOMAINS' => 'URL memiliki terlalu banyak subdomain.',
        'URL_SHORTENER' => 'URL menggunakan layanan pemendek tautan.',
        'URL_SUSPICIOUS_KEYWORDS' => 'URL mengandung kata kunci mencurigakan.',
        'URL_NO_INDICATION' => 'Tidak ditemukan indikasi ancaman yang dikenali.',
    ];
@endphp

<div class="url-result-page">
    <div class="result-topbar">
        <div>
            <span class="eyebrow">LAPORAN ANALISIS URL</span>
            <h1>Hasil Pemeriksaan URL</h1>
            <p class="muted" style="margin:0">URL tidak pernah dikunjungi; hanya struktur teks dan daftar hitam yang dianalisis.</p>
        </div>
        <a class="btn primary" href="{{ route('scan.create', ['tab' => 'url']) }}">+ Periksa URL Lain</a>
    </div>

    <section class="panel url-target">
        <span class="target-icon"><span class="material-symbols-outlined" style="font-size:20px">link</span></span>
        <div style="min-width:0;flex:1">
            <span class="eyebrow">TARGET URL</span>
            <h2>{{ $url }}</h2>
        </div>
    </section>

    <section class="panel url-verdict {{ $urlToneClass }}">
        <span class="eyebrow">HASIL PEMERIKSAAN</span>
        <div style="margin:14px 0 8px">
            <span class="verdict-badge {{ $urlToneClass }}">
                <span class="verdict-dot"></span>
                {{ $urlLabelText }}
            </span>
        </div>
        <p class="muted" style="margin:6px 0 22px">
            {{ $urlLabel === 'malicious' ? 'Ancaman terdeteksi. Jangan kunjungi URL ini.' : ($urlLabel === 'suspicious' ? 'Ditemukan ciri yang memerlukan perhatian sebelum membuka tautan ini.' : 'Tidak ada indikasi ancaman yang dikenali; tetap berhati-hati.') }}
        </p>

        <div class="url-stats">
            <div><span>RULE POINTS</span><strong>{{ $result['result']['rule_points'] ?? 0 }}</strong></div>
            <div><span>MODEL POINTS</span><strong>{{ $result['result']['model_points'] ?? 0 }}</strong></div>
            <div><span>HOST IP</span><strong>{{ ($result['target']['host_is_ip'] ?? false) ? 'YA' : 'TIDAK' }}</strong></div>
            <div><span>MODEL</span><strong>{{ $result['model_version'] ?? '—' }}</strong></div>
        </div>

        <div class="url-reasons">
            <span class="eyebrow">INDIKATOR &amp; ALASAN</span>
            @if (!empty($result['result']['reason_codes']))
                <ul>
                    @foreach ($result['result']['reason_codes'] as $code)
                        <li>
                            <b>●</b>
                            <div>
                                <span>{{ $urlReasons[$code] ?? $code }}</span>
                                <code>{{ $code }}</code>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="muted" style="margin:10px 0 0">Tidak ada kode alasan khusus.</p>
            @endif
        </div>
    </section>

    <div class="result-actions">
        <a class="btn primary" href="{{ route('scan.create') }}">Scan File / URL Lain</a>
        <a class="btn" href="{{ route('home') }}">Beranda</a>
    </div>
</div>

<style>
.url-result-page { width: min(100%, 840px); margin: 0 auto; }
.result-topbar { display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; margin-bottom: 20px; flex-wrap: wrap; }
.result-topbar h1 { margin: 4px 0; }
.url-target { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; padding: 16px 20px; }
.target-icon { width: 44px; height: 44px; border-radius: 9px; background: #ecfdf5; color: #006948; display: grid; place-items: center; flex-shrink: 0; }
.url-target h2 { margin: 4px 0 0; font-family: 'JetBrains Mono', monospace; font-size: 14px; color: #191c1e; overflow-wrap: anywhere; }
.url-verdict { padding: 28px; border-left: 4px solid #565e74; }
.url-verdict.tone-malicious { border-left-color: #ba1a1a; }
.url-verdict.tone-suspicious { border-left-color: #b45309; }
.url-verdict.tone-neutral { border-left-color: #565e74; }

.verdict-badge { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 999px; border: 1px solid #e6e8ea; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 20px; font-weight: 700; }
.verdict-dot { width: 9px; height: 9px; border-radius: 999px; background: currentColor; }
.verdict-badge.tone-malicious { color: #ba1a1a; background: #fef2f2; border-color: #fecaca; }
.verdict-badge.tone-suspicious { color: #b45309; background: #fffbeb; border-color: #fde68a; }
.verdict-badge.tone-neutral { color: #565e74; background: #f2f4f6; border-color: #e6e8ea; }

.url-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
.url-stats > div { padding: 12px 14px; border: 1px solid #e6e8ea; border-radius: 8px; background: #f7f9fb; }
.url-stats span { display: block; color: #565e74; font-family: 'JetBrains Mono', monospace; font-size: 10px; font-weight: 600; }
.url-stats strong { display: block; color: #191c1e; font-family: 'JetBrains Mono', monospace; font-size: 13px; margin-top: 5px; overflow: hidden; text-overflow: ellipsis; }
.url-reasons { margin-top: 18px; padding: 16px 18px; border: 1px solid #e6e8ea; border-radius: 8px; background: #f7f9fb; }
.url-reasons ul { list-style: none; padding: 0; margin: 12px 0 0; display: grid; gap: 10px; }
.url-reasons li { display: flex; gap: 10px; color: #191c1e; font-size: 13.5px; align-items: flex-start; }
.url-reasons li b { color: #f97316; font-size: 10px; margin-top: 4px; }
.url-reasons code { display: inline-block; margin-top: 3px; padding: 2px 6px; border-radius: 4px; background: #ffffff; border: 1px solid #e6e8ea; color: #565e74; font-size: 11px; }
.result-actions { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-top: 22px; }

/* Dark Mode for URL Scan Result */
body.dark-mode .target-icon { background: #0c261d; color: #34d399; border: 1px solid #164e3a; }
body.dark-mode .url-target h2 { color: #f8fafc; }
body.dark-mode .url-verdict.tone-malicious { border-left-color: #f87171; }
body.dark-mode .url-verdict.tone-suspicious { border-left-color: #fbbf24; }
body.dark-mode .url-verdict.tone-neutral { border-left-color: #64748b; }
body.dark-mode .verdict-badge.tone-malicious { color: #fca5a5; background: #2d1215; border-color: #5c2229; }
body.dark-mode .verdict-badge.tone-suspicious { color: #fbbf24; background: #2b2110; border-color: #5c4317; }
body.dark-mode .verdict-badge.tone-neutral { color: #e2e8f0; background: #1e293b; border-color: #334155; }
body.dark-mode .url-stats > div { background: #0e141f; border-color: #212c3d; }
body.dark-mode .url-stats span { color: #94a3b8; }
body.dark-mode .url-stats strong { color: #f8fafc; }
body.dark-mode .url-reasons { background: #0e141f; border-color: #212c3d; }
body.dark-mode .url-reasons li { color: #e2e8f0; }
body.dark-mode .url-reasons li b { color: #ff7b00; }
body.dark-mode .url-reasons code { background: #131924; border-color: #2a384e; color: #cbd5e1; }

@media (max-width: 640px) {
    .url-stats { grid-template-columns: repeat(2, 1fr); }
    .url-verdict { padding: 20px; }
    .verdict-badge { font-size: 17px; padding: 7px 14px; }
}
</style>
@endsection
