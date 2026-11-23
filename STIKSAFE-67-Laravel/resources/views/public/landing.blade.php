<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0B1120">
    <meta name="description" content="STIKSAFE membantu pemeriksaan awal keamanan file dengan analisis statis.">
    <title>STIKSAFE | Pemeriksaan Keamanan File</title>
    @vite(['resources/css/app.css'])
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; background: #0B1120 url('{{ asset('img/cyber-grid.svg') }}') center/cover fixed; color: #f8fafc; }
        a { color: inherit; text-decoration: none; }
        .site-shell { position: relative; z-index: 1; width: min(1120px, calc(100% - 40px)); margin: auto; }
        .topbar { min-height: 86px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid rgba(148,163,184,.14); }
        .brand-group { display:flex; align-items:center; gap:12px; }
        .brand-logo { width:42px;height:42px;object-fit:contain; }
        .brand-name { font-size:17px; font-weight:800; letter-spacing:.04em; }
        .brand-caption { color:#94a3b8;font:10px 'JetBrains Mono',monospace; letter-spacing:.12em; }
        .school-group { display:flex;align-items:center;gap:10px;margin-left:18px;padding-left:18px;border-left:1px solid rgba(148,163,184,.2); }
        .school-logo { width:34px;height:38px;object-fit:contain; }
        nav { display:flex;align-items:center;gap:27px;color:#a8b4c7;font-size:13px; }
        nav a:hover,.inline-link:hover { color:#FF6700; }
        .button { display:inline-flex;align-items:center;justify-content:center;gap:9px;padding:12px 18px;border:1px solid rgba(70,130,180,.35);border-radius:9px;background:rgba(15,23,42,.78);font-weight:700;font-size:13px;transition:.2s; }
        .button:hover { transform:translateY(-2px);border-color:#FF6700;box-shadow:0 8px 28px rgba(255,103,0,.15); }
        .button.primary { background:#FF6700;border-color:#FF6700;color:#111827; }
        .button.primary:hover { background:#ff7a24; }
        .hero { min-height:590px;display:grid;grid-template-columns:1.1fr .9fr;gap:50px;align-items:center;padding:64px 0 76px; }
        .eyebrow { display:inline-flex;align-items:center;gap:9px;padding:8px 11px;border:1px solid rgba(16,185,129,.25);border-radius:99px;background:rgba(16,185,129,.07);color:#6ee7b7;font:11px 'JetBrains Mono',monospace;letter-spacing:.05em; }
        .live-dot { width:7px;height:7px;border-radius:50%;background:#10B981;box-shadow:0 0 12px #10B981; }
        h1 { max-width:650px;margin:22px 0 17px;font-size:clamp(42px,6vw,69px);line-height:1.06;letter-spacing:-.055em; }
        h1 span { color:#FF6700; }
        .intro { max-width:580px;color:#9aa9bd;font-size:16px;line-height:1.8; }
        .hero-actions { display:flex;gap:12px;flex-wrap:wrap;margin-top:27px; }
        .microcopy { margin-top:17px;color:#718096;font:11px 'JetBrains Mono',monospace; }
        .visual-card { position:relative;min-height:370px;display:grid;place-items:center;overflow:hidden;border:1px solid rgba(70,130,180,.25);border-radius:24px;background:linear-gradient(145deg,rgba(15,23,42,.88),rgba(15,23,42,.5));backdrop-filter:blur(12px); }
        .visual-card:before,.visual-card:after { content:"";position:absolute;border:1px solid rgba(70,130,180,.19);border-radius:50%;width:290px;height:290px;animation:orbit 24s linear infinite; }
        .visual-card:after { width:220px;height:220px;border-color:rgba(255,103,0,.21);animation-direction:reverse;animation-duration:18s; }
        .visual-card img { position:relative;z-index:1;width:138px;height:138px;filter:drop-shadow(0 0 32px rgba(70,130,180,.35)); }
        .visual-label { position:absolute;bottom:21px;left:22px;right:22px;display:flex;justify-content:space-between;color:#8291a6;font:10px 'JetBrains Mono',monospace; }
        @keyframes orbit { to { transform:rotate(360deg); } }
        .section { padding:32px 0 76px; }
        .section-heading { margin:0 0 10px;font-size:27px;letter-spacing:-.035em; }
        .section-copy { margin:0 0 25px;color:#91a0b4;font-size:14px;line-height:1.7; }
        .feature-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:15px; }
        .feature { min-height:170px;padding:22px;border:1px solid rgba(70,130,180,.2);border-radius:14px;background:rgba(15,23,42,.72);backdrop-filter:blur(12px); }
        .feature-mark { color:#FF6700;font:12px 'JetBrains Mono',monospace; }
        .feature h3 { margin:16px 0 7px;font-size:15px; }
        .feature p { margin:0;color:#91a0b4;font-size:13px;line-height:1.7; }
        .about-strip { display:flex;align-items:center;justify-content:space-between;gap:22px;padding:23px 25px;border:1px solid rgba(70,130,180,.2);border-radius:14px;background:rgba(15,23,42,.72); }
        .about-strip p { margin:5px 0 0;color:#91a0b4;font-size:13px;line-height:1.7; }
        footer { border-top:1px solid rgba(148,163,184,.14);padding:22px 0 28px;color:#718096;font-size:11px; }
        .footer-inner { display:flex;justify-content:space-between;gap:16px; }
        :focus-visible { outline:2px solid #FF6700;outline-offset:3px; }
        @media(max-width:760px) { .topbar { min-height:75px; } nav { gap:12px; } nav .nav-about { display:none; } .school-group { margin-left:10px;padding-left:10px; } .school-caption { display:none; } .hero { grid-template-columns:1fr;gap:32px;padding:55px 0; } .visual-card { min-height:280px; } .feature-grid { grid-template-columns:1fr; } .feature { min-height:0; } }
        @media(max-width:480px) { .site-shell { width:calc(100% - 28px); } .topbar { flex-wrap:wrap;padding:13px 0;gap:12px; } nav { width:100%;justify-content:space-between;font-size:12px; } .brand-caption { display:none; } h1 { font-size:44px; } .about-strip,.footer-inner { align-items:flex-start;flex-direction:column; } }
        @media(prefers-reduced-motion:reduce) { *,*::before,*::after { animation-duration:.01ms!important;transition-duration:.01ms!important;scroll-behavior:auto!important; } }
    </style>
</head>
<body>
    <header class="site-shell topbar">
        <div class="brand-group">
            <a class="brand-group" href="{{ route('home') }}" aria-label="STIKSAFE beranda">
                <img class="brand-logo" src="{{ asset('img/stiksafe-logo.svg') }}" alt="Logo STIKSAFE">
                <span><span class="brand-name">STIK<span style="color:#FF6700">SAFE</span></span><br><span class="brand-caption">FILE SECURITY LAB</span></span>
            </a>
            <div class="school-group" aria-label="SMK Negeri 1 Mas Ubud">
                <img class="school-logo" src="{{ asset('img/smkn1masubud-logo.svg') }}" alt="Logo SMK Negeri 1 Mas Ubud">
                <span class="brand-caption school-caption">SMK N 1<br>MAS UBUD</span>
            </div>
        </div>
        <nav aria-label="Navigasi utama">
            <a href="#fitur">Fitur</a>
            <a class="nav-about" href="{{ route('about') }}">Tentang</a>
            <a href="{{ route('login') }}">Masuk</a>
            <a class="button primary" href="{{ route('register') }}">Mulai scan <span aria-hidden="true">↗</span></a>
        </nav>
    </header>
    <main class="site-shell">
        <section class="hero" aria-labelledby="hero-title">
            <div>
                <div class="eyebrow"><span class="live-dot"></span> PEMERIKSAAN FILE · ANALISIS STATIS</div>
                <h1>Kenali risiko.<br><span>Lindungi data.</span></h1>
                <p class="intro">Periksa indikasi ancaman file melalui analisis keamanan berbasis machine learning. Tanpa menjalankan file. Hasil pemeriksaan membantu Anda mengambil langkah lebih aman.</p>
                <div class="hero-actions">
                    <a class="button primary" href="{{ route('register') }}">Mulai pemeriksaan <span aria-hidden="true">→</span></a>
                    <a class="button" href="{{ route('login') }}">Masuk ke akun</a>
                </div>
                <p class="microcopy">STATIC ANALYSIS &nbsp;·&nbsp; ACCOUNT-BASED SCAN HISTORY</p>
            </div>
            <div class="visual-card" aria-label="Ilustrasi keamanan STIKSAFE">
                <img src="{{ asset('img/stiksafe-logo.svg') }}" alt="">
                <div class="visual-label"><span>STIKSAFE / CORE</span><span>STATUS: READY</span></div>
            </div>
        </section>
        <section id="fitur" class="section" aria-labelledby="features-title">
            <h2 id="features-title" class="section-heading">Pemeriksaan berlapis</h2>
            <p class="section-copy">Indikasi awal untuk membantu mengenali file berisiko sebelum dibuka.</p>
            <div class="feature-grid">
                <article class="feature"><span class="feature-mark">01 / CONTENT</span><h3>Analisis statis</h3><p>Periksa karakteristik file tanpa mengeksekusinya di perangkat Anda.</p></article>
                <article class="feature"><span class="feature-mark">02 / CLASSIFICATION</span><h3>Klasifikasi risiko</h3><p>Hasil model memberi indikasi aman, mencurigakan, berbahaya, atau belum diketahui.</p></article>
                <article class="feature"><span class="feature-mark">03 / HISTORY</span><h3>Riwayat pemindaian</h3><p>Simpan dan tinjau hasil pemeriksaan melalui akun Anda.</p></article>
            </div>
        </section>
        <section id="tentang" class="section" aria-labelledby="about-title">
            <div class="about-strip">
                <div><h2 id="about-title" class="section-heading">Tentang STIKSAFE</h2><p>STIKSAFE-67 adalah alat bantu pemeriksaan awal keamanan file, dikembangkan untuk lingkungan SMK Negeri 1 Mas Ubud.</p><p>Hasil analisis merupakan prediksi model, bukan jaminan keamanan mutlak. Jangan membuka file yang tidak dikenal.</p></div>
                <a class="button" href="{{ route('register') }}">Coba STIKSAFE <span aria-hidden="true">↗</span></a>
            </div>
        </section>
    </main>
    <footer><div class="site-shell footer-inner"><span>STIKSAFE-67 · SMK N 1 MAS UBUD</span><span>Analisis statis · Gunakan hasil sebagai panduan awal</span></div></footer>
</body>
</html>
