@extends('layouts.app')
@section('title', 'Tentang STIKSAFE')

@section('content')
<div style="max-width:780px;margin:0 auto">
    <span class="eyebrow">TENTANG STIKSAFE-67</span>
    <h1>Keamanan file <span style="color:#a33900">dimulai dari pemeriksaan.</span></h1>
    <p class="muted" style="margin-top:6px">Alat bantu pemeriksaan awal keamanan berkas dan tautan secara statis.</p>

    <section class="panel" style="margin-top:22px;display:flex;flex-direction:column;gap:14px">
        <p class="muted" style="margin:0">STIKSAFE-67 membantu pengguna mengenali indikasi awal risiko file dan URL melalui analisis statis. Berkas tidak pernah dijalankan dan tautan tidak pernah dikunjungi selama pemeriksaan.</p>
        <p class="muted" style="margin:0">Platform ini dikembangkan sebagai proyek keamanan digital di <strong>SMK Negeri 1 Mas Ubud</strong>, memadukan model machine learning EMBER2024 untuk program Windows (<code>.exe</code>, <code>.dll</code>), aturan statis untuk dokumen (PDF, Office, ZIP, gambar, skrip), serta pemeriksaan URL 3 lapis.</p>
        <div style="padding:14px 16px;border-radius:8px;background:#f2f4f6;border:1px solid #e6e8ea;font-size:13px;color:#565e74">
            <strong style="color:#191c1e">Catatan keterbatasan:</strong> Hasil klasifikasi merupakan panduan awal berbasis model dan aturan, bukan jaminan keamanan mutlak. Tetap berhati-hati sebelum membuka atau membagikan berkas dari sumber tidak dikenal.
        </div>
        <div class="actions" style="margin-top:6px">
            <a class="btn primary" href="{{ auth()->check() ? route('scan.create') : route('register') }}">Mulai gunakan STIKSAFE →</a>
            <a class="btn" href="{{ route('home') }}">Kembali ke Beranda</a>
        </div>
    </section>
</div>
@endsection
