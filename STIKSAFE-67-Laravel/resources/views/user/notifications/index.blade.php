@extends('layouts.app')
@section('title', 'Notifikasi | STIKSAFE-67')
@section('content')
<div class="eyebrow">ACCOUNT / NOTIFICATIONS</div>
<h1>Notifikasi.</h1>
<p class="muted">Pembaruan keamanan dan aktivitas pemindaian Anda.</p>

<section class="panel" style="margin-top:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
        <p class="muted" style="margin:0">{{ $unreadCount }} belum dibaca</p>
        @if ($unreadCount > 0)
            <form method="post" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="btn" type="submit">Tandai semua dibaca</button>
            </form>
        @endif
    </div>

    @if ($notifications->isEmpty())
        <p class="muted" style="padding:28px 0">Belum ada notifikasi.</p>
    @else
        <ul style="list-style:none;padding:0;margin:20px 0 0">
            @foreach ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $scanId = $data['scan_id'] ?? null;
                @endphp
                <li style="padding:18px 0;border-top:1px solid rgba(148,163,184,.14)">
                    <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start">
                        <div>
                            <p style="margin:0 0 6px;font-weight:700">{{ $data['title'] ?? 'Pembaruan STIKSAFE' }}</p>
                            <p class="muted" style="margin:0;font-size:13px">{{ $data['message'] ?? 'Anda memiliki pembaruan baru.' }}</p>
                            @if ($notification->created_at)
                                <time class="muted" datetime="{{ $notification->created_at->toISOString() }}" style="display:block;margin-top:8px;font:11px 'JetBrains Mono',monospace">{{ $notification->created_at->timezone(config('app.timezone'))->format('d M Y H:i T') }}</time>
                            @endif
                            @if ($scanId)
                                <a style="display:inline-block;margin-top:10px;color:#a33900" href="{{ route('scans.show', $scanId) }}">Lihat scan →</a>
                            @endif
                        </div>
                        <div style="text-align:right;white-space:nowrap">
                            <span class="eyebrow" style="color:{{ $notification->read_at ? '#565e74' : '#006948' }}">{{ $notification->read_at ? 'SUDAH DIBACA' : 'BELUM DIBACA' }}</span>
                            @if (! $notification->read_at)
                                <form method="post" action="{{ route('notifications.read', $notification->id) }}" style="margin-top:10px">
                                    @csrf
                                    <button class="btn" type="submit">Tandai dibaca</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
        <nav class="actions" aria-label="Navigasi halaman">
            <span class="muted">Halaman {{ $notifications->currentPage() }} dari {{ $notifications->lastPage() }}</span>
            @if ($notifications->onFirstPage())
                <span class="btn" aria-disabled="true">← Sebelumnya</span>
            @else
                <a class="btn" href="{{ $notifications->previousPageUrl() }}">← Sebelumnya</a>
            @endif
            @if ($notifications->hasMorePages())
                <a class="btn" href="{{ $notifications->nextPageUrl() }}">Berikutnya →</a>
            @else
                <span class="btn" aria-disabled="true">Berikutnya →</span>
            @endif
        </nav>
    @endif
</section>
@endsection
