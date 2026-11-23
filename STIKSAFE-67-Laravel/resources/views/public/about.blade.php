@extends('layouts.app')
@section('title', 'Tentang STIKSAFE')
@section('content')
<div class="eyebrow">ABOUT / STIKSAFE-67</div><h1>Keamanan file<br><span style="color:#FF6700">dimulai dari pemeriksaan.</span></h1><section class="panel" style="max-width:800px;margin-top:22px"><p class="muted">STIKSAFE membantu pengguna mengenali indikasi awal risiko file melalui analisis statis. File tidak dijalankan selama proses pemeriksaan.</p><p class="muted">Platform ini dikembangkan sebagai proyek keamanan digital di SMK Negeri 1 Mas Ubud.</p><p class="muted">Hasil klasifikasi adalah panduan awal, bukan jaminan keamanan mutlak. Tetap waspada sebelum membuka atau membagikan file.</p><a class="btn primary" href="{{ route('register') }}">Mulai gunakan STIKSAFE →</a></section>
@endsection
