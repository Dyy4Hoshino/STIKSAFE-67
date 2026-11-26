<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0B1120"><title>@yield('title', 'STIKSAFE-67')</title>
    @vite(['resources/css/app.css'])
    @yield('head')
    <style>
        body{margin:0;background:#0B1120 url('{{ asset('img/cyber-grid.svg') }}') center/cover fixed;color:#f8fafc;font-family:system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif}
        .shell{position:relative;z-index:1;width:min(1120px,calc(100% - 40px));margin:auto}
        .appbar{min-height:74px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(148,163,184,.14);gap:16px}
        .brand{display:flex;align-items:center;gap:10px;font-weight:800;letter-spacing:.04em;text-decoration:none;color:#f8fafc}
        .brand img{width:36px;height:36px}
        .brand em{color:#FF6700;font-style:normal}
        .nav{display:flex;align-items:center;gap:16px;color:#a8b4c7;font-size:13px}
        .nav a{color:#cbd5e1;text-decoration:none;transition:color .15s}
        .nav a:hover{color:#FF6700}
        .nav form{display:inline}
        .nav button,.btn{border:1px solid rgba(70,130,180,.35);border-radius:8px;background:rgba(15,23,42,.8);color:#f8fafc;padding:9px 14px;font-family:inherit;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;display:inline-block;transition:all .15s}
        .btn:hover{border-color:#FF6700}
        .btn.primary{background:#FF6700;border-color:#FF6700;color:#111827}
        .btn.primary:hover{background:#ff7a24}
        
        /* AVATAR TOP RIGHT */
        .nav-profile{display:flex;align-items:center;gap:10px;text-decoration:none;padding:4px 8px;border-radius:30px;border:1px solid rgba(148,163,184,.16);background:rgba(15,23,42,.5);transition:border-color .2s}
        .nav-profile:hover{border-color:#FF6700}
        .nav-avatar{width:32px;height:32px;border-radius:50%;object-fit:cover;display:grid;place-items:center;background:linear-gradient(135deg,#FF6700,#a33b00);color:#fff;font-size:13px;font-weight:700;flex-shrink:0}
        .nav-username{font-size:12px;font-weight:600;color:#f8fafc;max-width:110px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

        .page{padding:36px 0 65px}
        .eyebrow{color:#6ee7b7;font:11px 'JetBrains Mono',monospace;letter-spacing:.08em}
        .page h1{font-size:clamp(28px,4.5vw,46px);letter-spacing:-.04em;margin:8px 0}
        .muted{color:#91a0b4;line-height:1.7}
        .panel{padding:24px;border:1px solid rgba(70,130,180,.25);border-radius:16px;background:rgba(15,23,42,.78);backdrop-filter:blur(12px);box-shadow:0 15px 40px #0004}
        .grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
        .stat{padding:16px;border:1px solid rgba(70,130,180,.2);border-radius:12px;background:#0f172acc}
        .stat strong{display:block;font-size:26px;color:#FF6700}
        .stat span{color:#91a0b4;font-size:12px}
        .table-wrap{overflow:auto}
        .data{width:100%;border-collapse:collapse}
        .data th,.data td{text-align:left;padding:13px 12px;border-bottom:1px solid rgba(148,163,184,.13);font-size:13px}
        .data th{color:#718096;font:11px 'JetBrains Mono',monospace;text-transform:uppercase}
        .form-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
        .field{display:flex;flex-direction:column;gap:7px}
        .field.full{grid-column:1/-1}
        .field label{font:11px 'JetBrains Mono',monospace;letter-spacing:.08em;color:#91a0b4}
        .field input,.field select,.field textarea{background:#0b1120;border:1px solid rgba(70,130,180,.35);border-radius:8px;padding:10px 12px;color:#f8fafc;font-family:inherit;font-size:13px}
        .field input:focus,.field select:focus,.field textarea:focus{outline:none;border-color:#FF6700}
        .field small{color:#718096;font-size:11px}
        .error{color:#fb7185;font-size:12px}
        .alert{padding:12px 16px;border-radius:8px;margin-bottom:18px;font-size:13px;border:1px solid rgba(239,68,68,.3);background:rgba(239,68,68,.1);color:#fca5a5}
        .actions{display:flex;align-items:center;gap:12px;margin-top:18px;flex-wrap:wrap}
        .footer{border-top:1px solid rgba(148,163,184,.14);padding:20px 0;color:#718096;font-size:11px;text-align:center}
        @media(max-width:800px){.appbar{flex-wrap:wrap;padding:12px 0}.grid{grid-template-columns:repeat(2,1fr)}.nav{flex-wrap:wrap;gap:10px}}
    </style>
</head>
<body>
<header class="shell appbar">
    <a class="brand" href="{{ route('home') }}">
        <img src="{{ asset('img/stiksafe-logo.svg') }}" alt="Logo STIKSAFE">
        <span>STIK<em>SAFE</em></span>
    </a>
    
    <nav class="nav">
        <a href="{{ route('home') }}">Beranda</a>
        <a href="{{ route('scan.create') }}">Scan</a>
        <a href="{{ route('history') }}">Riwayat</a>
        <a href="{{ route('notifications') }}">
            Notifikasi
            @php($unreadNotificationCount = auth()->user()?->unreadNotifications()->count() ?? 0)
            @if($unreadNotificationCount > 0)
                <span style="color:#10B981">({{ $unreadNotificationCount }})</span>
            @endif
        </a>

        @if(auth()->user()?->role === 'admin')
            <a href="{{ route('admin.dashboard') }}" style="color:#FF8A38">Admin</a>
            <a href="{{ route('admin.scans') }}">Scan Log</a>
            <a href="{{ route('admin.users') }}">Pengguna</a>
            <a href="{{ route('admin.settings') }}">Setelan</a>
        @endif

        {{-- AVATAR DI POJOK KANAN ATAS --}}
        @auth
            <a href="{{ route('profile') }}" class="nav-profile" title="Buka Profil">
                @if(auth()->user()->avatar_path)
                    <img src="{{ asset('storage/' . auth()->user()->avatar_path) }}" alt="Foto Profil" class="nav-avatar">
                @else
                    <div class="nav-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                @endif
                <span class="nav-username">{{ auth()->user()->name }}</span>
            </a>
        @endauth
    </nav>
</header>

<main id="main-content" class="shell page">
    @if(session('success'))
        <div class="alert" style="border-color:#10b98166;background:#10b98111;color:#6ee7b7">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert">
            <ul style="margin:0;padding-left:18px">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('content')
</main>

<footer class="footer">
    <div class="shell">STIKSAFE-67 · SMK N 1 MAS UBUD · Pemeriksaan Keamanan File & URL</div>
</footer>
</body>
</html>
