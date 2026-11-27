<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#f5f7fa">
    <link rel="icon" type="image/jpeg" href="{{ asset('img/stiksafe-shield-logo.jpg') }}">
    <title>@yield('title', 'STIKSAFE-67')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400..600,0..1,0&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    @yield('head')
    <style>
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
            font-size: 14px;
            line-height: 1.5;
        }
        a { color: inherit; text-decoration: none; }

        .shell {
            width: min(1152px, calc(100% - 48px));
            margin: 0 auto;
        }

        /* Clean Light Precision Topbar — exact original nav text */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid #e6e8ea;
        }
        .appbar {
            min-height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 16px;
            letter-spacing: 0.04em;
            color: #191c1e;
            white-space: nowrap;
        }
        .brand img {
            width: 34px;
            height: 34px;
            object-fit: contain;
            background: #ffffff;
            border: 1px solid #e6e8ea;
            border-radius: 8px;
            padding: 2px;
        }
        .brand em {
            color: #a33900;
            font-style: normal;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .nav-group {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .nav-group a {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 12px;
            border-radius: 7px;
            font-size: 13.5px;
            font-weight: 500;
            color: #565e74;
            transition: background-color 0.15s, color 0.15s;
            white-space: nowrap;
        }
        .nav-icon {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            stroke-width: 1.9;
        }
        .admin-dropdown-item-left {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .nav-group a:hover {
            background: #f2f4f6;
            color: #191c1e;
        }
        .nav-group a.active {
            background: #f2f4f6;
            color: #a33900;
            font-weight: 600;
        }
        .nav-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            background: #a33900;
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            font-weight: 700;
        }

        /* Admin Dropdown */
        .nav-admin-wrapper { position: relative; }
        .nav-admin-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 6px;
            border: 1px solid #e6e8ea;
            background: #ffffff;
            color: #191c1e;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.15s, border-color 0.15s;
            white-space: nowrap;
        }
        .nav-admin-btn:hover {
            background: #f2f4f6;
            border-color: #cbd5e1;
        }
        .admin-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 215px;
            padding: 8px;
            background: #ffffff;
            border: 1px solid #e6e8ea;
            border-radius: 10px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.1);
            display: flex;
            flex-direction: column;
            gap: 2px;
            z-index: 200;
        }
        .admin-dropdown[hidden] { display: none; }
        .nav-admin-label {
            display: block;
            padding: 6px 10px 8px;
            margin-bottom: 4px;
            border-bottom: 1px solid #f2f4f6;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.08em;
            color: #a33900;
        }
        .admin-dropdown a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #565e74;
            transition: background-color 0.15s, color 0.15s;
        }
        .admin-dropdown a:hover,
        .admin-dropdown a.active {
            background: #fff3ed;
            color: #a33900;
        }

        /* User Profile Pill */
        .nav-profile {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 4px 10px 4px 4px;
            border-radius: 999px;
            border: 1px solid #e6e8ea;
            background: #ffffff;
            transition: background-color 0.15s, border-color 0.15s;
        }
        .nav-profile:hover {
            background: #f2f4f6;
            border-color: #cbd5e1;
        }
        .nav-avatar {
            width: 30px;
            height: 30px;
            border-radius: 999px;
            object-fit: cover;
            display: grid;
            place-items: center;
            background: #a33900;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .nav-username {
            font-size: 12.5px;
            font-weight: 600;
            color: #191c1e;
            max-width: 120px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Mobile Actions & Bottom Tab Bar */
        .mobile-actions {
            display: none;
            align-items: center;
            gap: 6px;
        }
        .mobile-icon-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #565e74;
        }
        .mobile-icon-btn:hover { background: #f2f4f6; color: #191c1e; }
        .mobile-unread-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: #a33900;
            border: 2px solid #ffffff;
        }
        .mobile-bottom-nav {
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
        .mobile-bottom-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            height: 64px;
            max-width: 640px;
            margin: 0 auto;
        }
        .mobile-bottom-grid.guest-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        .mobile-tab {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            min-height: 48px;
            font-size: 10.5px;
            font-weight: 500;
            color: #565e74;
            transition: color 0.15s;
        }
        .mobile-tab svg {
            width: 20px;
            height: 20px;
            stroke-width: 1.9;
        }
        .mobile-tab.active {
            color: #a33900;
            font-weight: 700;
        }
        .mobile-tab.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 22%;
            right: 22%;
            height: 2.5px;
            border-radius: 0 0 4px 4px;
            background: #a33900;
        }
        .mobile-tab-dot {
            position: absolute;
            top: 10px;
            right: calc(50% - 14px);
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #a33900;
            border: 1.5px solid #ffffff;
        }

        /* Page & Shared Components */
        .page { flex: 1; padding: 32px 0 56px; }
        .eyebrow {
            display: inline-block;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #a33900;
        }
        .page h1 { font-size: clamp(24px, 3.2vw, 32px); font-weight: 700; line-height: 1.22; margin: 6px 0 8px; color: #191c1e; }
        .page h2 { font-size: 19px; font-weight: 600; line-height: 1.35; color: #191c1e; }
        .page h3 { font-size: 16px; font-weight: 600; color: #191c1e; }
        .muted { color: #565e74; line-height: 1.6; }

        .panel {
            background: #ffffff;
            border: 1px solid #e6e8ea;
            border-radius: 10px;
            padding: 24px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 9px 16px;
            border-radius: 6px;
            border: 1px solid #e6e8ea;
            background: #ffffff;
            color: #191c1e;
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.15s, border-color 0.15s;
            text-decoration: none;
            line-height: 1.35;
        }
        .btn:hover { background: #f2f4f6; border-color: #cbd5e1; }
        .btn.primary { background: #a33900; border-color: #a33900; color: #ffffff; font-weight: 600; }
        .btn.primary:hover { background: #cc4900; border-color: #cc4900; }
        .btn.danger { background: #ffffff; border-color: #fecaca; color: #ba1a1a; }
        .btn.danger:hover { background: #fef2f2; border-color: #f87171; }

        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
        .stat { padding: 18px 20px; background: #ffffff; border: 1px solid #e6e8ea; border-radius: 10px; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04); }
        .stat strong { display: block; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 28px; font-weight: 700; color: #191c1e; font-variant-numeric: tabular-nums; line-height: 1.15; margin-bottom: 4px; }
        .stat span { font-size: 12px; font-weight: 500; color: #565e74; }

        .table-wrap { overflow-x: auto; }
        .data { width: 100%; border-collapse: collapse; }
        .data th, .data td { text-align: left; padding: 13px 14px; border-bottom: 1px solid #eceef0; font-size: 13.5px; color: #191c1e; vertical-align: middle; }
        .data tbody tr:last-child td { border-bottom: none; }
        .data tbody tr:hover td { background: #f7f9fb; }
        .data th { font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #565e74; background: #f7f9fb; }

        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field.full { grid-column: 1 / -1; }
        .field label { font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600; letter-spacing: 0.02em; color: #565e74; }
        .field input, .field select, .field textarea {
            background: #ffffff;
            border: 1px solid #d8dadc;
            border-radius: 6px;
            padding: 10px 12px;
            color: #191c1e;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .field input:focus, .field select:focus, .field textarea:focus {
            outline: none;
            border-color: #a33900;
            box-shadow: 0 0 0 3px rgba(163, 57, 0, 0.1);
        }
        .field small { color: #64748b; font-size: 12px; }

        .error { color: #ba1a1a; font-size: 12.5px; font-weight: 500; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 13.5px; border: 1px solid #fecaca; background: #fef2f2; color: #93000a; }
        .alert.alert-success { border-color: #a7f3d0; background: #ecfdf5; color: #005137; }
        .actions { display: flex; align-items: center; gap: 10px; margin-top: 18px; flex-wrap: wrap; }

        .footer { background: #ffffff; border-top: 1px solid #e6e8ea; padding: 18px 0; color: #565e74; font-size: 12px; }

        @media (max-width: 900px) { .grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 820px) {
            body { background-attachment: scroll; }
            .shell { width: calc(100% - 28px); }
            .appbar { min-height: 58px; }
            .nav { display: none; }
            .mobile-actions { display: flex; }
            .mobile-bottom-nav { display: block !important; }
            .page { padding: 18px 0 96px; }
            .form-grid { grid-template-columns: 1fr; }
            .panel { padding: 16px; }
            .table-wrap { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .data th, .data td { padding: 10px 10px; font-size: 12.5px; }
            .footer { margin-bottom: 66px; font-size: 11.5px; }
        }
        @media (max-width: 480px) {
            .grid { grid-template-columns: 1fr; }
            .actions { flex-direction: column; align-items: stretch; }
            .actions .btn { width: 100%; }
        }
    </style>
</head>
<body>
<header class="site-header">
    <div class="shell appbar">
        <a class="brand" href="{{ route('home') }}">
            <img src="{{ asset('img/stiksafe-shield-logo.jpg') }}" alt="Logo STIKSAFE">
            <span>STIK<em>SAFE</em></span>
        </a>

        <nav class="nav" aria-label="Navigasi utama">
            <div class="nav-group" aria-label="Menu bersama">
                <a class="{{ request()->routeIs('home', 'dashboard') ? 'active' : '' }}" href="{{ route('home') }}">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/></svg>
                    <span>Beranda</span>
                </a>
                @auth
                    <a class="{{ request()->routeIs('scan.*', 'scans.*') ? 'active' : '' }}" href="{{ route('scan.create') }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><circle cx="12" cy="12" r="3"/><path d="M12 7v2"/><path d="M12 15v2"/><path d="M7 12h2"/><path d="M15 12h2"/></svg>
                        <span>Scan</span>
                    </a>
                    <a class="{{ request()->routeIs('history*') ? 'active' : '' }}" href="{{ route('history') }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l3 2"/></svg>
                        <span>Riwayat</span>
                    </a>
                    <a class="{{ request()->routeIs('notifications*') ? 'active' : '' }}" href="{{ route('notifications') }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        <span>Notifikasi</span>
                        @php
                            $unreadNotificationCount = auth()->user()->unreadNotifications()->count();
                        @endphp
                        @if($unreadNotificationCount > 0)
                            <span class="nav-badge" aria-label="{{ $unreadNotificationCount }} belum dibaca">{{ $unreadNotificationCount }}</span>
                        @endif
                    </a>
                @else
                    <a href="{{ route('home') }}#fitur">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                        <span>Fitur</span>
                    </a>
                    <a class="{{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        <span>Tentang</span>
                    </a>
                    <a class="{{ request()->routeIs('login') ? 'active' : '' }}" href="{{ route('login') }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                        <span>Masuk</span>
                    </a>
                @endauth
            </div>

            @if(auth()->user()?->role === 'admin')
                @php
                    try {
                        $newFeedbackCount = \App\Models\ScanFeedback::query()->whereNull('reviewed_at')->count();
                    } catch (\Throwable $e) {
                        $newFeedbackCount = 0;
                    }
                    $isAdminRoute = request()->routeIs('admin.*');
                    $adminMenuOpen = $isAdminRoute || session('admin_menu_open', false);
                @endphp
                <div class="nav-admin-wrapper">
                    <button class="nav-admin-btn" type="button" id="admin-menu-toggle" aria-controls="admin-menu" aria-expanded="{{ isset($adminMenuOpen) && $adminMenuOpen ? 'true' : 'false' }}">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                        <span>Menu Admin</span> <span aria-hidden="true">▾</span>
                    </button>
                    <div class="admin-dropdown" id="admin-menu" @if (! isset($adminMenuOpen) || ! $adminMenuOpen) hidden @endif>
                        <span class="nav-admin-label">ADMIN</span>
                        <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <span class="admin-dropdown-item-left"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>Dashboard</span>
                        </a>
                        <a class="{{ request()->routeIs('admin.scans*') ? 'active' : '' }}" href="{{ route('admin.scans') }}">
                            <span class="admin-dropdown-item-left"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>Scan Log</span>
                        </a>
                        <a class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                            <span class="admin-dropdown-item-left"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Pengguna</span>
                        </a>
                        <a class="{{ request()->routeIs('admin.feedback*') ? 'active' : '' }}" href="{{ route('admin.feedback') }}">
                            <span class="admin-dropdown-item-left"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>Feedback</span>
                            <span class="nav-badge">{{ $newFeedbackCount ?? 0 }}</span>
                        </a>
                        <a class="{{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.create') }}">
                            <span class="admin-dropdown-item-left"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>Kirim Notifikasi</span>
                        </a>
                        <a class="{{ request()->routeIs('admin.audit-logs') ? 'active' : '' }}" href="{{ route('admin.audit-logs') }}">
                            <span class="admin-dropdown-item-left"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>Audit Log</span>
                        </a>
                        <a class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}" href="{{ route('admin.settings') }}">
                            <span class="admin-dropdown-item-left"><svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>Setelan</span>
                        </a>
                    </div>
                </div>
            @endif

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

        {{-- Mobile Header Right --}}
        <div class="mobile-actions">
            @auth
                @if(auth()->user()?->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="mobile-icon-btn" aria-label="Menu Admin" title="Menu Admin">
                        <svg class="nav-icon" style="width:20px;height:20px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    </a>
                @endif
                <a href="{{ route('notifications') }}" class="mobile-icon-btn" aria-label="Notifikasi">
                    <svg class="nav-icon" style="width:20px;height:20px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    @if(($unreadNotificationCount ?? 0) > 0)
                        <span class="mobile-unread-dot"></span>
                    @endif
                </a>
                <a href="{{ route('profile') }}" aria-label="Profil" style="margin-left:2px">
                    @if(auth()->user()->avatar_path)
                        <img src="{{ asset('storage/' . auth()->user()->avatar_path) }}" alt="Foto Profil" class="nav-avatar">
                    @else
                        <div class="nav-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    @endif
                </a>
            @else
                <a class="btn" href="{{ route('login') }}" style="padding:7px 12px;font-size:12.5px;font-weight:600">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                    <span>Masuk</span>
                </a>
            @endauth
        </div>
    </div>
</header>

<main id="main-content" class="shell page">
    @if(session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert" role="alert">
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
    <div class="shell">STIKSAFE-67 · SMK N 1 MAS UBUD · Pemeriksaan Keamanan File &amp; URL</div>
</footer>

<nav class="mobile-bottom-nav" aria-label="Navigasi seluler">
    @auth
        <div class="mobile-bottom-grid">
            <a class="mobile-tab {{ request()->routeIs('home', 'dashboard') ? 'active' : '' }}" href="{{ route('home') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/></svg>
                <span>Beranda</span>
            </a>
            <a class="mobile-tab {{ request()->routeIs('scan.*', 'scans.*') ? 'active' : '' }}" href="{{ route('scan.create') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><circle cx="12" cy="12" r="3"/></svg>
                <span>Scan</span>
            </a>
            <a class="mobile-tab {{ request()->routeIs('history*') ? 'active' : '' }}" href="{{ route('history') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l3 2"/></svg>
                <span>Riwayat</span>
            </a>
            <a class="mobile-tab {{ request()->routeIs('notifications*') ? 'active' : '' }}" href="{{ route('notifications') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                @if(($unreadNotificationCount ?? 0) > 0)
                    <span class="mobile-tab-dot" aria-hidden="true"></span>
                @endif
                <span>Notifikasi</span>
            </a>
            <a class="mobile-tab {{ request()->routeIs('profile*') ? 'active' : '' }}" href="{{ route('profile') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Profil</span>
            </a>
        </div>
    @else
        <div class="mobile-bottom-grid guest-grid">
            <a class="mobile-tab {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/></svg>
                <span>Beranda</span>
            </a>
            <a class="mobile-tab" href="{{ route('home') }}#fitur">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                <span>Fitur</span>
            </a>
            <a class="mobile-tab {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                <span>Tentang</span>
            </a>
        </div>
    @endauth
</nav>

@auth
    @if (auth()->user()?->role === 'admin')
        <script>
            (() => {
                const button = document.getElementById('admin-menu-toggle');
                const menu = document.getElementById('admin-menu');
                if (!button || !menu) return;

                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const isHidden = menu.hasAttribute('hidden');
                    if (isHidden) {
                        menu.removeAttribute('hidden');
                        button.setAttribute('aria-expanded', 'true');
                    } else {
                        menu.setAttribute('hidden', '');
                        button.setAttribute('aria-expanded', 'false');
                    }
                });

                document.addEventListener('click', (event) => {
                    if (!menu.contains(event.target) && !button.contains(event.target)) {
                        menu.setAttribute('hidden', '');
                        button.setAttribute('aria-expanded', 'false');
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        menu.setAttribute('hidden', '');
                        button.setAttribute('aria-expanded', 'false');
                    }
                });
            })();
        </script>
    @endif
@endauth
</body>
</html>
