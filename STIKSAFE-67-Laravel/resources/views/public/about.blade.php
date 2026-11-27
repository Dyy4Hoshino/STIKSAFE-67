@extends('layouts.app')
@section('title', 'Tentang STIKSAFE')

@section('content')
<div class="about-page">
    <span class="eyebrow">TENTANG STIKSAFE-67</span>
    <h1>Keamanan file <span class="about-accent">dimulai dari pemeriksaan.</span></h1>
    <p class="muted" style="margin-top:6px">Alat bantu pemeriksaan awal keamanan berkas dan tautan secara statis.</p>

    <section class="panel about-panel">
        <p class="muted" style="margin:0">STIKSAFE-67 membantu pengguna mengenali indikasi awal risiko file dan URL melalui analisis statis. Berkas tidak pernah dijalankan dan tautan tidak pernah dikunjungi selama pemeriksaan.</p>
        <p class="muted" style="margin:0">Platform ini dikembangkan sebagai proyek keamanan digital di <strong>SMK Negeri 1 Mas Ubud</strong>, memadukan model machine learning EMBER2024 untuk program Windows (<code>.exe</code>, <code>.dll</code>), aturan statis untuk dokumen (PDF, Office, ZIP, gambar, skrip), serta pemeriksaan URL 3 lapis.</p>
        <div class="about-disclaimer">
            <strong>Catatan keterbatasan:</strong> Hasil klasifikasi merupakan panduan awal berbasis model dan aturan, bukan jaminan keamanan mutlak. Tetap berhati-hati sebelum membuka atau membagikan berkas dari sumber tidak dikenal.
        </div>
        <div class="actions" style="margin-top:6px">
            <a class="btn primary" href="{{ auth()->check() ? route('scan.create') : route('register') }}">Mulai gunakan STIKSAFE →</a>
            <a class="btn" href="{{ route('home') }}">Kembali ke Beranda</a>
        </div>
    </section>
</div>

<style>
.about-page { max-width: 780px; margin: 0 auto; }
.about-accent { color: #f97316; }
.about-panel { margin-top: 22px; display: flex; flex-direction: column; gap: 14px; }
.about-disclaimer {
    padding: 14px 16px;
    border-radius: 8px;
    background: #f2f4f6;
    border: 1px solid #e6e8ea;
    border-left: 3px solid #f97316;
    font-size: 13px;
    line-height: 1.6;
    color: #565e74;
}
.about-disclaimer strong { color: #191c1e; }

body.dark-mode .about-accent { color: #ff7b00; }
body.dark-mode .about-disclaimer {
    background: #0e141f;
    border-color: #212c3d;
    border-left-color: #ff7b00;
    color: #cbd5e1;
}
body.dark-mode .about-disclaimer strong { color: #f8fafc; }
</style>
@endsection
