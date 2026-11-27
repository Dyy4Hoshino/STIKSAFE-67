<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#131924">
    <link rel="icon" type="image/jpeg" href="{{ asset('img/stiksafe-shield-logo.jpg') }}">
    <title>@yield('title', 'STIKSAFE-67')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400..600,0..1,0&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        html, body { height: 100%; margin: 0; padding: 0; background: #ffffff; color: #191c1e; font-family: 'Inter', system-ui, sans-serif; }
        a { color: inherit; text-decoration: none; }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(380px, 44%) 1fr;
        }

        /* Left Brand Editorial Panel — Full Height Matte Slate */
        .auth-aside {
            position: relative;
            background: #131924;
            color: #f8fafc;
            padding: 40px 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
        }
        .auth-aside-dots {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.12) 1px, transparent 1px);
            background-size: 24px 24px;
            mask-image: radial-gradient(ellipse 80% 70% at 20% 25%, #000 50%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 80% 70% at 20% 25%, #000 50%, transparent 100%);
            pointer-events: none;
        }
        .auth-aside-top,
        .auth-aside-mid,
        .auth-aside-foot {
            position: relative;
            z-index: 1;
        }
        .auth-brand-row {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }
        .auth-brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 17px;
            letter-spacing: 0.04em;
            color: #ffffff;
        }
        .auth-brand img {
            width: 36px;
            height: 36px;
            object-fit: contain;
            background: #ffffff;
            border-radius: 9px;
            padding: 3px;
        }
        .auth-brand em { color: #ff7a33; font-style: normal; }
        .auth-brand-div { width: 1px; height: 22px; background: rgba(255, 255, 255, 0.16); }
        .auth-school {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10.5px;
            letter-spacing: 0.08em;
            color: #94a3b8;
        }
        .auth-school img { width: 22px; height: 24px; object-fit: contain; }

        .auth-aside-mid {
            max-width: 420px;
            padding: 32px 0;
        }
        .auth-aside-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 11px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(255, 255, 255, 0.04);
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }
        .auth-aside-tag-dot { width: 6px; height: 6px; border-radius: 999px; background: #10b981; }
        .auth-aside-mid h2 {
            margin: 0 0 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(26px, 2.8vw, 34px);
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.025em;
            color: #ffffff;
        }
        .auth-aside-mid p {
            margin: 0 0 28px;
            font-size: 14.5px;
            line-height: 1.65;
            color: #94a3b8;
        }
        .auth-pillars {
            display: grid;
            gap: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 22px;
        }
        .auth-pillar {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: #cbd5e1;
        }
        .auth-pillar-icon {
            width: 30px;
            height: 30px;
            border-radius: 7px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ff7a33;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .auth-aside-foot {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* Right Full-Height Form Area */
        .auth-main {
            background-color: #f8fafc;
            background-image:
                radial-gradient(circle at 85% 0%, rgba(163, 57, 0, 0.035) 0%, transparent 45%),
                radial-gradient(rgba(86, 94, 116, 0.16) 1px, transparent 1px),
                linear-gradient(to right, rgba(25, 28, 30, 0.025) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(25, 28, 30, 0.025) 1px, transparent 1px);
            background-size: 100% 100%, 24px 24px, 96px 96px, 96px 96px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding: 32px 56px;
        }
        .auth-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 24px;
        }
        .auth-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #565e74;
            padding: 6px 10px;
            border-radius: 6px;
            transition: background-color 0.15s, color 0.15s;
        }
        .auth-back:hover { background: #f2f4f6; color: #191c1e; }

        .auth-form-center {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px 0 32px;
        }
        .auth-form-box {
            width: 100%;
            max-width: 440px;
        }
        .auth-form-box.wide { max-width: 500px; }

        .eyebrow {
            display: inline-block;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            color: #a33900;
        }
        .auth-form-box h1 {
            margin: 8px 0 8px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: clamp(26px, 3vw, 34px);
            font-weight: 700;
            line-height: 1.18;
            letter-spacing: -0.025em;
            color: #191c1e;
        }
        .auth-form-box h1 span { color: #a33900; }
        .muted { color: #565e74; font-size: 14px; line-height: 1.6; }

        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field.full { grid-column: 1 / -1; }
        .field label { font-size: 12px; font-weight: 600; letter-spacing: 0.02em; color: #565e74; }
        .field input {
            background: #f7f9fb;
            border: 1px solid #d8dadc;
            border-radius: 7px;
            padding: 11px 13px;
            color: #191c1e;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.15s, background-color 0.15s, box-shadow 0.15s;
        }
        .field input:focus {
            outline: none;
            background: #ffffff;
            border-color: #a33900;
            box-shadow: 0 0 0 3px rgba(163, 57, 0, 0.1);
        }
        .field small { color: #64748b; font-size: 12px; }
        .error { color: #ba1a1a; font-size: 12.5px; font-weight: 500; }

        .alert { padding: 12px 14px; border-radius: 8px; margin: 14px 0 18px; font-size: 13px; border: 1px solid #fecaca; background: #fef2f2; color: #93000a; }
        .alert-success { border-color: #a7f3d0; background: #ecfdf5; color: #005137; }

        .actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 20px; }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 18px;
            border-radius: 7px;
            border: 1px solid #e6e8ea;
            background: #ffffff;
            color: #191c1e;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s, border-color 0.15s;
        }
        .btn:hover { background: #f2f4f6; border-color: #cbd5e1; }
        .btn.primary { background: #f97316; border-color: #f97316; color: #ffffff; font-weight: 600; }
        .btn.primary:hover { background: #ea580c; border-color: #ea580c; }
        .password-control { position:relative; }
        .password-control input { width:100%; box-sizing:border-box; padding-right:46px; }
        .password-toggle { position:absolute; right:8px; bottom:7px; width:32px; height:32px; display:grid; place-items:center; padding:0; border:0; border-radius:6px; background:transparent; color:#64748b; cursor:pointer; }
        .password-toggle:hover { background:#eaf0f7; color:#f97316; }
        .password-toggle .material-symbols-outlined { font-size:19px; }

        body.dark-mode { background:#0b0f17; color:#f1f5f9; }
        body.dark-mode .auth-aside { background:#0e1726; }
        body.dark-mode .auth-main { background-color:#0b0f17; background-image:radial-gradient(circle at 85% 0%,rgba(30,144,255,.12),transparent 45%),radial-gradient(rgba(30,144,255,.08) 1px,transparent 1px),linear-gradient(to right,rgba(255,255,255,.02) 1px,transparent 1px),linear-gradient(to bottom,rgba(255,255,255,.02) 1px,transparent 1px); }
        body.dark-mode .auth-back { color:#cbd5e1; }
        body.dark-mode .auth-back:hover { background:#1e293b; color:#fff; }
        body.dark-mode .auth-form-box h1 { color:#f8fafc; }
        body.dark-mode .eyebrow, body.dark-mode .auth-form-box h1 span { color:#ff7b00; }
        body.dark-mode .muted, body.dark-mode .field label, body.dark-mode .field small { color:#94a3b8; }
        body.dark-mode .field input { background:#101722; border-color:#2a384e; color:#f8fafc; }
        body.dark-mode .field input:focus { background:#101722; border-color:#ff7b00; box-shadow:0 0 0 3px rgba(249,115,22,.18); }
        body.dark-mode .btn:not(.primary) { background:#1e293b; border-color:#334155; color:#f1f5f9; }
        body.dark-mode .password-toggle:hover { background:#1e293b; color:#ff7b00; }

        @media (max-width: 960px) {
            .auth-shell { grid-template-columns: 1fr; }
            .auth-aside { padding: 24px 20px; }
            .auth-aside-mid, .auth-aside-foot { display: none; }
            .auth-main { min-height: auto; padding: 24px 20px 40px; }
            .auth-form-center { align-items: flex-start; padding-top: 12px; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<script>
    if (localStorage.getItem('stiksafe_theme') === 'dark') document.body.classList.add('dark-mode');
</script>
    <div class="auth-shell">
        <aside class="auth-aside" aria-label="Informasi STIKSAFE-67">
            <div class="auth-aside-dots" aria-hidden="true"></div>
            <div class="auth-aside-top">
                <div class="auth-brand-row">
                    <a class="auth-brand" href="{{ route('home') }}">
                        <img src="{{ asset('img/stiksafe-shield-logo.jpg') }}" alt="Logo STIKSAFE">
                        <span>STIK<em>SAFE</em></span>
                    </a>
                    <span class="auth-brand-div" aria-hidden="true"></span>
                    <div class="auth-school">
                        <img src="{{ asset('img/smkn1masubud-logo.svg') }}" alt="Logo SMK Negeri 1 Mas Ubud">
                        <span>SMK N 1 MAS UBUD</span>
                    </div>
                </div>
            </div>

            <div class="auth-aside-mid">
                <div class="auth-aside-tag">
                    <span class="auth-aside-tag-dot"></span>
                    <span>STATIC ANALYSIS · FILE &amp; URL</span>
                </div>
                <h2>Kenali risiko.<br>Lindungi data.</h2>
                <p>Pemeriksaan awal keamanan file dan tautan berbasis machine learning tanpa menjalankan file di perangkat Anda.</p>
                <div class="auth-pillars">
                    <div class="auth-pillar">
                        <span class="auth-pillar-icon"><span class="material-symbols-outlined" style="font-size:17px">memory</span></span>
                        <span>Analisis statis EMBER2024 &amp; aturan dokumen</span>
                    </div>
                    <div class="auth-pillar">
                        <span class="auth-pillar-icon"><span class="material-symbols-outlined" style="font-size:17px">link</span></span>
                        <span>Pemeriksaan URL 3 lapis tanpa membuka tautan</span>
                    </div>
                    <div class="auth-pillar">
                        <span class="auth-pillar-icon"><span class="material-symbols-outlined" style="font-size:17px">history</span></span>
                        <span>Riwayat pemeriksaan tersimpan per akun</span>
                    </div>
                </div>
            </div>

            <div class="auth-aside-foot">
                <span>STIKSAFE-67 · FILE SECURITY LAB</span>
                <span>SMK N 1 MAS UBUD</span>
            </div>
        </aside>

        <main class="auth-main">
            <div class="auth-topbar">
                <a class="auth-back" href="{{ route('home') }}">
                    <span class="material-symbols-outlined" style="font-size:17px">arrow_back</span>
                    <span>Beranda</span>
                </a>
                @yield('auth_topbar_right')
            </div>

            <div class="auth-form-center">
                @yield('content')
            </div>
        </main>
    </div>
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);
                if (!input) return;
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                button.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
                button.querySelector('.material-symbols-outlined').textContent = show ? 'visibility_off' : 'visibility';
            });
        });
    </script>
</body>
</html>
