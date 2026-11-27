<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f5f7fa">
    <link rel="icon" type="image/jpeg" href="{{ asset('img/stiksafe-shield-logo.jpg') }}">
    <meta name="description" content="STIKSAFE membantu pemeriksaan awal keamanan file dengan analisis statis.">
    <title>STIKSAFE | Pemeriksaan Keamanan File</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400..600,0..1,0&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        html { scroll-behavior: smooth; }
        #fitur { scroll-margin-top: 84px; }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f5f7fa;
            background-image:
                radial-gradient(circle at 10% 0%, rgba(163, 57, 0, 0.04) 0%, transparent 42%),
                radial-gradient(circle at 90% 6%, rgba(86, 94, 116, 0.05) 0%, transparent 40%),
                radial-gradient(rgba(86, 94, 116, 0.18) 1px, transparent 1px),
                linear-gradient(to right, rgba(25, 28, 30, 0.028) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(25, 28, 30, 0.028) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 24px 24px, 96px 96px, 96px 96px;
            background-attachment: fixed;
            color: #191c1e;
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }
        a { color: inherit; text-decoration: none; }
        .site-shell { width: min(1152px, calc(100% - 48px)); margin: 0 auto; }

        /* Topbar — exact original navigation text */
        .landing-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #e6e8ea;
        }
        .topbar {
            min-height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .brand-group { display: flex; align-items: center; gap: 11px; }
        .brand-logo {
            width: 38px;
            height: 38px;
            object-fit: contain;
            background: #ffffff;
            border: 1px solid #e6e8ea;
            border-radius: 9px;
            padding: 3px;
        }
        .brand-name { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 17px; font-weight: 800; letter-spacing: 0.04em; color: #191c1e; }
        .brand-name span { color: #f97316; }
        .brand-caption { display: block; color: #565e74; font: 500 10px 'JetBrains Mono', monospace; letter-spacing: 0.1em; line-height: 1.35; }
        .school-group { display: flex; align-items: center; gap: 10px; margin-left: 14px; padding-left: 16px; border-left: 1px solid #e6e8ea; }
        .school-logo { width: 30px; height: 34px; object-fit: contain; }

        nav { display: flex; align-items: center; gap: 20px; color: #565e74; font-size: 13.5px; font-weight: 500; }
        nav a { display: inline-flex; align-items: center; gap: 6px; }
        nav a:hover { color: #f97316; }
        .nav-icon { width: 15px; height: 15px; flex-shrink: 0; stroke-width: 1.9; }

        /* Mobile Bottom Navbar for Landing Page */
        .landing-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 999;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid #e6e8ea;
            box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.06);
            padding-bottom: env(safe-area-inset-bottom, 0px);
        }
        .landing-bottom-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            height: 64px;
            max-width: 640px;
            margin: 0 auto;
        }
        .mobile-login-btn {
            display: none;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border: 1px solid #d8dadc;
            border-radius: 7px;
            background: #ffffff;
            color: #191c1e;
            font-size: 12.5px;
            font-weight: 600;
            white-space: nowrap;
        }
        .mobile-login-btn:hover { background: #f2f4f6; border-color: #cbd5e1; }
        .landing-bottom-tab {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            font-size: 10.5px;
            font-weight: 500;
            color: #565e74;
        }
        .landing-bottom-tab svg { width: 19px; height: 19px; stroke-width: 1.9; }
        .landing-bottom-tab.active,
        .landing-bottom-tab.cta { color: #f97316; font-weight: 700; }
        .landing-bottom-tab.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 22%;
            right: 22%;
            height: 2.5px;
            border-radius: 0 0 4px 4px;
            background: #f97316;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 18px;
            border: 1px solid #d8dadc;
            border-radius: 7px;
            background: #ffffff;
            color: #191c1e;
            font-weight: 600;
            font-size: 13.5px;
            transition: background-color 0.15s, border-color 0.15s;
        }
        .button:hover { background: #f2f4f6; border-color: #cbd5e1; color: #191c1e; }
        .button.primary { background: #f97316; border-color: #f97316; color: #ffffff; }
        .button.primary:hover { background: #ea580c; border-color: #ea580c; color: #ffffff; }

        /* Asymmetric 2-Column Editorial Hero (LEFT-ALIGNED, NOT CENTERED) */
        .hero-band {
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid #eceef0;
            background: #ffffff;
        }
        .hero-dots {
            position: absolute;
            inset: 0;
            opacity: 0.28;
            pointer-events: none;
        }
        .hero {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 48px;
            align-items: center;
            padding: 44px 0 56px;
        }
        h1 {
            max-width: 640px;
            margin: 0 0 16px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(38px, 5.2vw, 60px);
            font-weight: 800;
            line-height: 1.08;
            letter-spacing: -0.035em;
            color: #191c1e;
        }
        h1 span { color: #f97316; }
        .intro {
            max-width: 560px;
            margin: 0;
            color: #565e74;
            font-size: 16px;
            line-height: 1.7;
        }
        .hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-top: 26px; }
        .microcopy {
            margin: 20px 0 0;
            color: #64748b;
            font: 500 11px 'JetBrains Mono', monospace;
            letter-spacing: 0.06em;
        }

        /* Right Hero Visual Card — Interactive Security Radar + Centered Logo */
        .visual-card {
            position: relative;
            min-height: 370px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 1px solid #e6e8ea;
            border-radius: 16px;
            background: #f7f9fb;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
            padding: 0 0 46px;
            perspective: 900px;
        }
        .visual-stage {
            position: relative;
            width: 100%;
            height: 324px;
            display: grid;
            place-items: center;
            background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.96) 0%, rgba(247, 249, 251, 0.45) 68%, transparent 100%);
            transform-style: preserve-3d;
            transition: transform 0.16s ease-out;
        }
        #stiksafe-radar-canvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: block;
        }
        .radar-logo-core {
            position: relative;
            z-index: 2;
            width: 82px;
            height: 82px;
            border-radius: 999px;
            background: #ffffff;
            border: 2px solid rgba(186, 26, 26, 0.32);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.09), 0 0 0 6px rgba(186, 26, 26, 0.08);
            display: grid;
            place-items: center;
            padding: 13px;
            transform: translateZ(24px);
            transition: border-color 0.45s ease, box-shadow 0.45s ease;
            pointer-events: none;
        }
        .visual-card.is-ml-online .radar-logo-core {
            border-color: rgba(0, 105, 72, 0.36);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.09), 0 0 0 6px rgba(0, 105, 72, 0.09);
        }
        .radar-logo-core img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }
        .visual-label {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 13px 20px;
            background: #ffffff;
            border-top: 1px solid #e6e8ea;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #565e74;
            font: 600 10.5px 'JetBrains Mono', monospace;
            letter-spacing: 0.06em;
        }
        .visual-status {
            color: #ba1a1a;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            transition: color 0.35s ease;
        }
        .visual-status-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #ba1a1a;
            box-shadow: 0 0 0 3px rgba(186, 26, 26, 0.16);
            transition: background-color 0.35s ease, box-shadow 0.35s ease;
        }
        .visual-card.is-ml-online .visual-status { color: #006948; }
        .visual-card.is-ml-online .visual-status-dot {
            background: #006948;
            box-shadow: 0 0 0 3px rgba(0, 105, 72, 0.18);
        }

        /* Left-Aligned Sections */
        .section { padding: 52px 0; }
        .section + .section { padding-top: 8px; padding-bottom: 68px; }
        .section-heading {
            margin: 0 0 8px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.025em;
            color: #191c1e;
        }
        .section-copy { margin: 0 0 24px; color: #565e74; font-size: 14.5px; line-height: 1.65; }

        .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .feature {
            padding: 24px;
            border: 1px solid #e6e8ea;
            border-radius: 10px;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            transition: border-color 0.15s;
        }
        .feature:hover { border-color: #cbd5e1; }
        .feature-mark {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 5px;
            background: #fff3ed;
            color: #f97316;
            font: 600 11px 'JetBrains Mono', monospace;
            letter-spacing: 0.04em;
        }
        .feature h3 { margin: 16px 0 8px; font-size: 16px; font-weight: 700; color: #191c1e; }
        .feature p { margin: 0; color: #565e74; font-size: 13.5px; line-height: 1.65; }

        .about-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 28px 30px;
            border: 1px solid #e6e8ea;
            border-radius: 12px;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .about-strip p { margin: 6px 0 0; color: #565e74; font-size: 13.5px; line-height: 1.65; max-width: 700px; }

        footer {
            margin-top: auto;
            background: #ffffff;
            border-top: 1px solid #e6e8ea;
            padding: 20px 0 24px;
            color: #565e74;
            font-size: 12px;
        }
        .footer-inner { display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; }

        @media (max-width: 860px) {
            body { background-attachment: scroll; }
            .hero {
                grid-template-columns: 1.12fr 0.88fr;
                gap: 16px;
                align-items: start;
                padding: 24px 0 34px;
            }
            h1 { font-size: clamp(24px, 5.8vw, 34px); margin: 0 0 10px; }
            .intro { font-size: 13px; line-height: 1.55; }
            .hero-actions { margin-top: 16px; gap: 8px; }
            .hero-actions .button { padding: 9px 12px; font-size: 12px; }
            .microcopy { margin-top: 14px; font-size: 9.5px; }
            .visual-card { min-height: 224px; padding-bottom: 42px; border-radius: 13px; }
            .visual-stage { height: 182px; }
            .radar-logo-core { width: 52px; height: 52px; padding: 8px; }
            .visual-label {
                flex-direction: column;
                align-items: center;
                gap: 3px;
                padding: 8px 10px;
                font-size: 9px;
                text-align: center;
            }
            .feature-grid { grid-template-columns: 1fr; }
            .about-strip { flex-direction: column; align-items: flex-start; padding: 22px 20px; }
        }
        @media (max-width: 768px) {
            .school-group { display: none; }
            .landing-header nav { display: none; }
            .mobile-login-btn { display: inline-flex; }
            .landing-bottom-nav { display: block !important; }
            footer { margin-bottom: 66px; }
        }
        /* Public landing dark mode */
        body.dark-mode {
            background-color: #0b0f17 !important;
            background-image: radial-gradient(circle at 10% 0%, rgba(30,144,255,.12), transparent 42%), radial-gradient(circle at 90% 8%, rgba(255,106,0,.1), transparent 40%), radial-gradient(rgba(30,144,255,.08) 1px, transparent 1px), linear-gradient(to right, rgba(255,255,255,.02) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,.02) 1px, transparent 1px) !important;
            color: #f1f5f9;
        }
        body.dark-mode .landing-header,
        body.dark-mode footer { background: rgba(15,23,42,.94) !important; border-color: #243247 !important; color: #cbd5e1; }
        body.dark-mode .brand-name, body.dark-mode h1, body.dark-mode .section-heading, body.dark-mode .feature h3 { color: #f8fafc !important; }
        body.dark-mode .brand-caption, body.dark-mode nav, body.dark-mode .intro, body.dark-mode .section-copy, body.dark-mode .feature p, body.dark-mode .about-strip p, body.dark-mode footer { color: #94a3b8 !important; }
        body.dark-mode .brand-name span, body.dark-mode h1 span, body.dark-mode nav a:hover { color: #ff7b00 !important; }
        body.dark-mode .hero-band { background: transparent !important; border-color: #243247 !important; }
        body.dark-mode .button:not(.primary), body.dark-mode .landing-theme-toggle { background: #1e293b !important; border-color: #334155 !important; color: #f1f5f9 !important; }
        body.dark-mode .button.primary { background: #ff6a00 !important; border-color: #ff6a00 !important; }
        body.dark-mode .feature, body.dark-mode .about-strip { background: #131924 !important; border-color: #243247 !important; box-shadow: 0 5px 18px rgba(0,0,0,.25); }
        body.dark-mode .feature-mark { background: #2a1b12 !important; color: #ff9a3d !important; }
        body.dark-mode .visual-card { background: #131924 !important; border-color: #2a384e !important; box-shadow: 0 8px 24px rgba(0,0,0,.35); }
        body.dark-mode .visual-stage { background: radial-gradient(circle at 50% 50%, rgba(30,41,59,.95), rgba(15,23,42,.5) 68%, transparent 100%); }
        body.dark-mode .visual-label { background: #111722 !important; border-color: #2a384e !important; color: #94a3b8 !important; }
        body.dark-mode .radar-logo-core { background: #f8fafc; }
        body.dark-mode .landing-bottom-nav { background: rgba(15,23,42,.97); border-color: #243247; }
        body.dark-mode .landing-bottom-tab { color: #94a3b8; }
        body.dark-mode .landing-bottom-tab.active, body.dark-mode .landing-bottom-tab.cta { color: #ff7b00; }
        .landing-theme-toggle { display:inline-flex; align-items:center; gap:6px; padding:7px 10px; border:1px solid #d8dadc; border-radius:7px; background:#fff; color:#191c1e; cursor:pointer; font:600 12px inherit; }
        .landing-theme-toggle .material-symbols-outlined { font-size:16px; }

        @media (max-width: 520px) {
            .site-shell { width: calc(100% - 24px); }
            .topbar { min-height: 58px; }
            .brand-caption { display: none; }
            .hero {
                grid-template-columns: 1.12fr 0.88fr;
                gap: 12px;
                padding: 22px 0 30px;
            }
            h1 { font-size: 22px; margin: 0 0 8px; }
            .intro { font-size: 12px; line-height: 1.5; }
            .hero-actions { flex-direction: column; gap: 7px; margin-top: 14px; }
            .hero-actions .button { width: 100%; padding: 8px 10px; font-size: 11.5px; }
            .microcopy { font-size: 8.5px; letter-spacing: 0.03em; }
            .visual-card { min-height: 196px; padding-bottom: 40px; }
            .visual-stage { height: 156px; }
            .radar-logo-core { width: 44px; height: 44px; padding: 7px; }
            .visual-label { padding: 7px 6px; font-size: 8px; }
            .footer-inner { flex-direction: column; align-items: flex-start; gap: 4px; }
        }
        @media (max-width: 359px) {
            .hero { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="landing-header">
        <div class="site-shell topbar">
            <div class="brand-group">
                <a class="brand-group" href="{{ route('home') }}" aria-label="STIKSAFE beranda">
                    <img class="brand-logo" src="{{ asset('img/stiksafe-shield-logo.jpg') }}" alt="Logo STIKSAFE">
                    <span>
                        <span class="brand-name">STIK<span>SAFE</span></span>
                        <span class="brand-caption">FILE SECURITY LAB</span>
                    </span>
                </a>
                <div class="school-group" aria-label="SMK Negeri 1 Mas Ubud">
                    <img class="school-logo" src="{{ asset('img/smkn1masubud-logo.svg') }}" alt="Logo SMK Negeri 1 Mas Ubud">
                    <span class="brand-caption school-caption">SMK N 1<br>MAS UBUD</span>
                </div>
            </div>
            <nav aria-label="Navigasi utama">
                <a href="#fitur">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                    <span>Fitur</span>
                </a>
                <a class="nav-about" href="{{ route('about') }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    <span>Tentang</span>
                </a>
                <a href="{{ route('login') }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                    <span>Masuk</span>
                </a>
                <button class="landing-theme-toggle" id="landing-theme-toggle" type="button" aria-label="Ganti tema"><span class="material-symbols-outlined" id="landing-theme-icon">dark_mode</span></button>
                <a class="button primary" href="{{ route('register') }}" style="padding:8px 14px;font-size:13px">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>Mulai scan <span aria-hidden="true">↗</span></span>
                </a>
            </nav>
            <a class="mobile-login-btn" href="{{ route('login') }}">
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                <span>Masuk</span>
            </a>
        </div>
    </header>

    <main>
        <div class="hero-band">
            <div class="hero-dots bg-hero-dots" aria-hidden="true"></div>
            <section class="site-shell hero" aria-labelledby="hero-title">
                <div>
                    <h1 id="hero-title">Kenali risiko.<br><span>Lindungi data.</span></h1>
                    <p class="intro">Periksa indikasi ancaman file melalui analisis keamanan berbasis machine learning. Tanpa menjalankan file. Hasil pemeriksaan membantu Anda mengambil langkah lebih aman.</p>
                    <div class="hero-actions">
                        <a class="button primary" href="{{ route('register') }}">Mulai pemeriksaan <span aria-hidden="true">→</span></a>
                        <a class="button" href="{{ route('login') }}">Masuk ke akun</a>
                    </div>
                    <p class="microcopy">STATIC ANALYSIS &nbsp;·&nbsp; ACCOUNT-BASED SCAN HISTORY</p>
                </div>

                <div class="visual-card" id="visual-radar-card" data-ml-status-url="{{ route('ml.status') }}" aria-label="Radar pemantauan status layanan ML STIKSAFE">
                    <div class="visual-stage" id="visual-radar-stage">
                        <canvas id="stiksafe-radar-canvas" aria-hidden="true"></canvas>
                        <div class="radar-logo-core">
                            <img src="{{ asset('img/stiksafe-shield-logo.jpg') }}" alt="Logo Perisai STIKSAFE">
                        </div>
                    </div>
                    <div class="visual-label">
                        <span>STIKSAFE / CORE</span>
                        <span class="visual-status" id="ml-status-indicator">
                            <span class="visual-status-dot"></span>
                            <span id="ml-status-text">STATUS: ML OFFLINE</span>
                        </span>
                    </div>
                </div>
            </section>
        </div>

        <section id="fitur" class="site-shell section" aria-labelledby="features-title">
            <h2 id="features-title" class="section-heading">Pemeriksaan berlapis</h2>
            <p class="section-copy">Indikasi awal untuk membantu mengenali file berisiko sebelum dibuka.</p>
            <div class="feature-grid">
                <article class="feature">
                    <span class="feature-mark">01 / CONTENT</span>
                    <h3>Analisis statis</h3>
                    <p>Periksa karakteristik file tanpa mengeksekusinya di perangkat Anda.</p>
                </article>
                <article class="feature">
                    <span class="feature-mark">02 / CLASSIFICATION</span>
                    <h3>Klasifikasi risiko</h3>
                    <p>Hasil model memberi indikasi aman, mencurigakan, berbahaya, atau belum diketahui.</p>
                </article>
                <article class="feature">
                    <span class="feature-mark">03 / HISTORY</span>
                    <h3>Riwayat pemindaian</h3>
                    <p>Simpan dan tinjau hasil pemeriksaan melalui akun Anda.</p>
                </article>
            </div>
        </section>

        <section id="tentang" class="site-shell section" aria-labelledby="about-title">
            <div class="about-strip">
                <div>
                    <h2 id="about-title" class="section-heading">Tentang STIKSAFE</h2>
                    <p>STIKSAFE-67 adalah alat bantu pemeriksaan awal keamanan file, dikembangkan untuk lingkungan SMK Negeri 1 Mas Ubud.</p>
                    <p>Hasil analisis merupakan prediksi model, bukan jaminan keamanan mutlak. Jangan membuka file yang tidak dikenal.</p>
                </div>
                <a class="button" href="{{ route('register') }}" style="white-space:nowrap">Coba STIKSAFE <span aria-hidden="true">↗</span></a>
            </div>
        </section>
    </main>

    <footer>
        <div class="site-shell footer-inner">
            <span>STIKSAFE-67 · SMK N 1 MAS UBUD</span>
            <span>Analisis statis · Gunakan hasil sebagai panduan awal</span>
        </div>
    </footer>

    <nav class="landing-bottom-nav" aria-label="Navigasi seluler">
        <div class="landing-bottom-grid">
            <a class="landing-bottom-tab active" id="nav-tab-home" href="{{ route('home') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/></svg>
                <span>Beranda</span>
            </a>
            <a class="landing-bottom-tab" id="nav-tab-fitur" href="#fitur">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                <span>Fitur</span>
            </a>
            <a class="landing-bottom-tab" href="{{ route('about') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                <span>Tentang</span>
            </a>
        </div>
    </nav>

    <script>
        (() => {
            const canvas = document.getElementById('stiksafe-radar-canvas');
            const card = document.getElementById('visual-radar-card');
            const stage = document.getElementById('visual-radar-stage');
            const statusText = document.getElementById('ml-status-text');
            if (!canvas || !card || !stage) return;
            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            const statusUrl = card.dataset.mlStatusUrl;
            let width = 0, height = 0, dpr = Math.min(window.devicePixelRatio || 1, 2);

            function resize() {
                const rect = canvas.getBoundingClientRect();
                width = Math.max(1, rect.width);
                height = Math.max(1, rect.height);
                canvas.width = Math.round(width * dpr);
                canvas.height = Math.round(height * dpr);
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            }
            resize();
            window.addEventListener('resize', resize);

            // Live ML status colors: Red (#BA1A1A) when offline, Green (#006948) when online
            const RED = { r: 186, g: 26, b: 26 };
            const GREEN = { r: 0, g: 105, b: 72 };
            let mlOnline = false;
            let curColor = { ...RED };

            function applyMlStatus(online) {
                mlOnline = Boolean(online);
                card.classList.toggle('is-ml-online', mlOnline);
                if (statusText) {
                    statusText.textContent = mlOnline ? 'STATUS: ML TERHUBUNG' : 'STATUS: ML OFFLINE';
                }
            }

            async function pollMlStatus() {
                if (!statusUrl) return;
                try {
                    const res = await fetch(statusUrl, {
                        headers: { 'Accept': 'application/json' },
                        cache: 'no-store'
                    });
                    if (!res.ok) {
                        applyMlStatus(false);
                        return;
                    }
                    const data = await res.json();
                    applyMlStatus(Boolean(data && data.online));
                } catch {
                    applyMlStatus(false);
                }
            }

            pollMlStatus();
            setInterval(pollMlStatus, 5000);

            // Interactive 3D perspective tilt on hover
            let targetTiltX = 0, targetTiltY = 0;
            let tiltX = 0, tiltY = 0;

            card.addEventListener('pointermove', (e) => {
                if (reducedMotion) return;
                const r = card.getBoundingClientRect();
                const nx = ((e.clientX - r.left) / r.width - 0.5) * 2;
                const ny = ((e.clientY - r.top) / r.height - 0.5) * 2;
                targetTiltY = nx * 9;
                targetTiltX = -ny * 9;
            });
            card.addEventListener('pointerleave', () => {
                targetTiltX = 0;
                targetTiltY = 0;
            });

            // Radar blips (polar coordinates: angle, normalized radius, glow intensity)
            const blips = [
                { angle: 0.72, dist: 0.62, glow: 0 },
                { angle: 2.08, dist: 0.78, glow: 0 },
                { angle: 3.55, dist: 0.52, glow: 0 },
                { angle: 4.92, dist: 0.84, glow: 0 },
                { angle: 5.75, dist: 0.68, glow: 0 }
            ];

            let sweepAngle = -Math.PI / 2;
            let pulsePhase = 0;

            function rgba(c, a) {
                return `rgba(${Math.round(c.r)}, ${Math.round(c.g)}, ${Math.round(c.b)}, ${a.toFixed(3)})`;
            }

            function render() {
                ctx.clearRect(0, 0, width, height);

                // Smooth color transition between offline red and online green
                const target = mlOnline ? GREEN : RED;
                curColor.r += (target.r - curColor.r) * 0.08;
                curColor.g += (target.g - curColor.g) * 0.08;
                curColor.b += (target.b - curColor.b) * 0.08;

                // Smooth 3D card stage tilt
                tiltX += (targetTiltX - tiltX) * 0.1;
                tiltY += (targetTiltY - tiltY) * 0.1;
                if (!reducedMotion) {
                    stage.style.transform = `rotateX(${tiltX.toFixed(2)}deg) rotateY(${tiltY.toFixed(2)}deg)`;
                }

                const cx = width / 2;
                const cy = height / 2;
                const maxR = Math.min(width, height) * 0.42;

                // Subtle radial background wash
                const bgGrad = ctx.createRadialGradient(cx, cy, maxR * 0.12, cx, cy, maxR * 1.05);
                bgGrad.addColorStop(0, rgba(curColor, 0.07));
                bgGrad.addColorStop(0.65, rgba(curColor, 0.03));
                bgGrad.addColorStop(1, rgba(curColor, 0));
                ctx.beginPath();
                ctx.arc(cx, cy, maxR * 1.05, 0, Math.PI * 2);
                ctx.fillStyle = bgGrad;
                ctx.fill();

                // Concentric range rings
                const ringFactors = [0.36, 0.58, 0.8, 1.0];
                ringFactors.forEach((f, idx) => {
                    ctx.beginPath();
                    ctx.arc(cx, cy, maxR * f, 0, Math.PI * 2);
                    ctx.strokeStyle = rgba(curColor, idx === 3 ? 0.28 : 0.16);
                    ctx.lineWidth = idx === 3 ? 1.25 : 1;
                    if (idx === 1) ctx.setLineDash([4, 4]);
                    else ctx.setLineDash([]);
                    ctx.stroke();
                });
                ctx.setLineDash([]);

                // Outer precision degree ticks
                for (let i = 0; i < 36; i++) {
                    const a = (i / 36) * Math.PI * 2;
                    const major = i % 3 === 0;
                    const r1 = maxR * (major ? 0.955 : 0.975);
                    const r2 = maxR * 1.015;
                    ctx.beginPath();
                    ctx.moveTo(cx + Math.cos(a) * r1, cy + Math.sin(a) * r1);
                    ctx.lineTo(cx + Math.cos(a) * r2, cy + Math.sin(a) * r2);
                    ctx.strokeStyle = rgba(curColor, major ? 0.34 : 0.16);
                    ctx.lineWidth = major ? 1.2 : 0.9;
                    ctx.stroke();
                }

                // Crosshair axes
                ctx.beginPath();
                ctx.moveTo(cx - maxR, cy);
                ctx.lineTo(cx + maxR, cy);
                ctx.moveTo(cx, cy - maxR);
                ctx.lineTo(cx, cy + maxR);
                ctx.strokeStyle = rgba(curColor, 0.12);
                ctx.lineWidth = 1;
                ctx.stroke();

                // Expanding sonar pulse ring
                const pulseR = maxR * (0.28 + pulsePhase * 0.72);
                const pulseAlpha = (1 - pulsePhase) * 0.28;
                ctx.beginPath();
                ctx.arc(cx, cy, pulseR, 0, Math.PI * 2);
                ctx.strokeStyle = rgba(curColor, pulseAlpha);
                ctx.lineWidth = 1.5;
                ctx.stroke();

                // Rotating radar sweep sector (layered slices for smooth conical trail)
                const sweepArc = Math.PI * 0.44;
                const slices = 22;
                for (let i = 0; i < slices; i++) {
                    const ratio = i / slices;
                    const aStart = sweepAngle - sweepArc * (1 - ratio);
                    const aEnd = sweepAngle - sweepArc * (1 - (i + 1) / slices);
                    ctx.beginPath();
                    ctx.moveTo(cx, cy);
                    ctx.arc(cx, cy, maxR, aStart, aEnd);
                    ctx.closePath();
                    ctx.fillStyle = rgba(curColor, Math.pow(ratio, 2.2) * 0.22);
                    ctx.fill();
                }

                // Leading radar sweep beam line
                ctx.beginPath();
                ctx.moveTo(cx, cy);
                ctx.lineTo(cx + Math.cos(sweepAngle) * maxR, cy + Math.sin(sweepAngle) * maxR);
                ctx.strokeStyle = rgba(curColor, 0.85);
                ctx.lineWidth = 1.8;
                ctx.stroke();

                // Radar target blips illuminated as the sweep passes over them
                const normSweep = ((sweepAngle % (Math.PI * 2)) + Math.PI * 2) % (Math.PI * 2);
                for (const b of blips) {
                    let diff = normSweep - b.angle;
                    if (diff < 0) diff += Math.PI * 2;
                    if (diff < 0.12) {
                        b.glow = 1;
                    } else {
                        b.glow = Math.max(0, b.glow - 0.012);
                    }

                    if (b.glow > 0.02) {
                        const bx = cx + Math.cos(b.angle) * (maxR * b.dist);
                        const by = cy + Math.sin(b.angle) * (maxR * b.dist);

                        // Outer ping halo
                        ctx.beginPath();
                        ctx.arc(bx, by, 4 + (1 - b.glow) * 9, 0, Math.PI * 2);
                        ctx.strokeStyle = rgba(curColor, b.glow * 0.45);
                        ctx.lineWidth = 1.1;
                        ctx.stroke();

                        // Core blip dot
                        ctx.beginPath();
                        ctx.arc(bx, by, 3, 0, Math.PI * 2);
                        ctx.fillStyle = rgba(curColor, b.glow * 0.92);
                        ctx.fill();
                    }
                }

                if (!reducedMotion) {
                    sweepAngle += mlOnline ? 0.024 : 0.016;
                    pulsePhase = (pulsePhase + 0.0085) % 1;
                    requestAnimationFrame(render);
                }
            }

            render();
        })();

        (() => {
            const tabHome = document.getElementById('nav-tab-home');
            const tabFitur = document.getElementById('nav-tab-fitur');
            const fiturEl = document.getElementById('fitur');
            if (!tabHome || !tabFitur || !fiturEl) return;

            function setActive(isFitur) {
                tabHome.classList.toggle('active', !isFitur);
                tabFitur.classList.toggle('active', isFitur);
            }

            function syncByScroll() {
                const rect = fiturEl.getBoundingClientRect();
                setActive(window.location.hash === '#fitur' || rect.top <= 160);
            }

            tabFitur.addEventListener('click', () => setActive(true));
            tabHome.addEventListener('click', () => setActive(false));
            window.addEventListener('hashchange', syncByScroll);
            window.addEventListener('scroll', syncByScroll, { passive: true });
            syncByScroll();
        })();

        (() => {
            const key = 'stiksafe_theme';
            const toggle = document.getElementById('landing-theme-toggle');
            const icon = document.getElementById('landing-theme-icon');
            const setTheme = (dark) => {
                document.body.classList.toggle('dark-mode', dark);
                if (icon) icon.textContent = dark ? 'light_mode' : 'dark_mode';
            };
            setTheme(localStorage.getItem(key) === 'dark');
            toggle?.addEventListener('click', () => {
                const dark = !document.body.classList.contains('dark-mode');
                localStorage.setItem(key, dark ? 'dark' : 'light');
                setTheme(dark);
            });
        })();
    </script>
</body>
</html>
