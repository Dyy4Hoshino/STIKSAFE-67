@extends('layouts.app')
@section('title', 'Hasil URL | STIKSAFE-67')

@section('content')
@php
    $urlLabel = $result['result']['label'] ?? 'unknown';
    $urlLabelText = match ($urlLabel) { 'malicious' => 'Berbahaya', 'suspicious' => 'Mencurigakan', default => 'Tidak diketahui' };
    $urlColor = match ($urlLabel) { 'malicious' => '#EF4444', 'suspicious' => '#FF8A38', default => '#94a3b8' };
    $urlReasons = [
        'URL_BLOCKLIST_MATCH' => 'URL cocok dengan daftar hitam.', 'URL_IP_HOST' => 'URL menggunakan alamat IP sebagai host.',
        'URL_NONSTANDARD_PORT' => 'URL menggunakan port jaringan tidak standar.', 'URL_USERINFO' => 'URL mengandung informasi pengguna.',
        'URL_EXEC_DOWNLOAD' => 'URL mengarah pada unduhan executable.', 'URL_PUNYCODE' => 'Host menggunakan Punycode.',
        'URL_MANY_SUBDOMAINS' => 'URL memiliki terlalu banyak subdomain.', 'URL_SHORTENER' => 'URL menggunakan layanan pemendek link.',
        'URL_SUSPICIOUS_KEYWORDS' => 'URL mengandung kata kunci mencurigakan.', 'URL_NO_INDICATION' => 'Tidak ada indikasi risiko yang dikenali.',
    ];
@endphp

<div class="url-result-page">
    <div class="result-topbar"><div><span class="eyebrow">URL ANALYSIS / REPORT</span><h1>Hasil pemeriksaan URL</h1><p class="muted">URL tidak dibuka; hanya struktur teks yang dianalisis.</p></div><a class="btn primary" href="{{ route('scan.create') }}">+ Scan Baru</a></div>
    <section class="panel url-target"><span class="target-icon">LINK</span><div><span class="eyebrow">TARGET URL</span><h2>{{ $url }}</h2></div></section>
    <section class="panel url-verdict" style="--verdict-color:{{ $urlColor }}"><span class="eyebrow">ANALYSIS COMPLETE</span><div class="url-verdict-title"><span class="verdict-dot"></span>{{ $urlLabelText }}</div><p class="muted">{{ $urlLabel === 'malicious' ? 'Ancaman terdeteksi. Jangan kunjungi URL ini.' : ($urlLabel === 'suspicious' ? 'Ditemukan ciri yang memerlukan perhatian.' : 'Tidak ada indikasi ancaman yang dikenali; tetap berhati-hati.') }}</p>
        <div class="url-stats"><div><span>RULE POINTS</span><strong>{{ $result['result']['rule_points'] ?? 0 }}</strong></div><div><span>MODEL POINTS</span><strong>{{ $result['result']['model_points'] ?? 0 }}</strong></div><div><span>HOST IP</span><strong>{{ ($result['target']['host_is_ip'] ?? false) ? 'YA' : 'TIDAK' }}</strong></div><div><span>MODEL</span><strong>{{ $result['model_version'] ?? '—' }}</strong></div></div>
        <div class="url-reasons"><span class="eyebrow">REASON / ANALYSIS</span>@if (!empty($result['result']['reason_codes']))<ul>@foreach ($result['result']['reason_codes'] as $code)<li><b>✓</b><span>{{ $urlReasons[$code] ?? $code }} <code>{{ $code }}</code></span></li>@endforeach</ul>@else<p class="muted">Tidak ada kode alasan khusus.</p>@endif</div>
    </section>
    <div class="result-actions"><a class="btn primary" href="{{ route('scan.create') }}">Scan File / URL Lain</a><a class="btn" href="{{ route('home') }}">Beranda</a></div>
</div>
<style>
.url-result-page{width:min(100%,820px);margin:auto;padding:4px 0 40px}.result-topbar{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:22px}.result-topbar h1{margin:8px 0 4px}.result-topbar p{margin:0}.url-target{display:flex;align-items:center;gap:15px;margin-bottom:16px;padding:18px}.target-icon{display:grid;place-items:center;width:48px;height:48px;border-radius:11px;background:rgba(16,185,129,.1);color:#6ee7b7;font:10px monospace}.url-target h2{margin:6px 0 0;font-size:15px;color:#f8fafc;overflow-wrap:anywhere}.url-verdict{--verdict-color:#94a3b8;padding:30px}.url-verdict-title{display:flex;align-items:center;gap:10px;margin:18px 0 6px;color:var(--verdict-color);font-size:30px;font-weight:800}.verdict-dot{width:12px;height:12px;border-radius:50%;background:var(--verdict-color);box-shadow:0 0 14px var(--verdict-color)}.url-verdict>p{margin:0 0 22px}.url-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}.url-stats>div{padding:13px;border:1px solid rgba(148,163,184,.14);border-radius:9px;background:rgba(11,17,32,.55)}.url-stats span{display:block;color:#91a0b4;font:10px monospace}.url-stats strong{display:block;color:#f8fafc;font-size:13px;margin-top:6px;overflow:hidden;text-overflow:ellipsis}.url-reasons{margin-top:18px;padding:15px;border:1px solid rgba(255,103,0,.2);border-radius:10px;background:rgba(255,103,0,.04)}.url-reasons ul{list-style:none;padding:0;margin:12px 0 0;display:grid;gap:9px}.url-reasons li{display:flex;gap:9px;color:#e2e8f0;font-size:13px}.url-reasons li b{color:#10B981}.url-reasons code{display:block;color:#64748b;font-size:10px;margin-top:2px}.result-actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:22px}@media(max-width:620px){.result-topbar{align-items:flex-start;flex-direction:column}.url-stats{grid-template-columns:repeat(2,1fr)}.url-verdict{padding:22px}.url-verdict-title{font-size:25px}}
</style>
@endsection
