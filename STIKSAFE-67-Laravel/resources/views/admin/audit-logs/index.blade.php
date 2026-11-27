@extends('layouts.app')
@section('title', 'Admin Audit Logs | STIKSAFE-67')

@section('content')
<div class="eyebrow">ADMIN / AUDIT LOGS</div>
<h1>Jejak audit sistem</h1>
<p class="muted" style="margin:0 0 20px">Catatan historis tindakan dan kejadian sistem.</p>

<form method="GET" style="margin-bottom:18px;display:flex;gap:10px;flex-wrap:wrap;align-items:center">
    <div class="field" style="min-width:200px">
        <select name="action">
            <option value="">Semua Aksi</option>
            <option value="account.status.changed" @selected(request('action') === 'account.status.changed')>Status Akun Diubah</option>
            <option value="account.role.changed" @selected(request('action') === 'account.role.changed')>Role Akun Diubah</option>
            <option value="scan.retried" @selected(request('action') === 'scan.retried')>Scan Dicoba Ulang</option>
        </select>
    </div>
    <div class="field" style="min-width:190px">
        <select name="severity">
            <option value="">Semua Tingkat (Severity)</option>
            <option value="info" @selected(request('severity') === 'info')>Info</option>
            <option value="warn" @selected(request('severity') === 'warn')>Warning</option>
            <option value="critical" @selected(request('severity') === 'critical')>Critical</option>
        </select>
    </div>
    <button type="submit" class="btn primary">Filter</button>
</form>

<div class="panel table-wrap">
    <table class="data">
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Aktor</th>
                <th>Aksi</th>
                <th>Target</th>
                <th>Outcome</th>
                <th>Severity</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="white-space:nowrap;font-size:12.5px"><time datetime="{{ $log->occurred_at?->toISOString() }}">{{ $log->occurred_at?->format('d M Y H:i:s') }}</time></td>
                    <td>{{ $log->actor?->name ?? 'System' }} <span class="muted">({{ $log->actor_type }})</span></td>
                    <td><code style="font-size:12px">{{ $log->action }}</code></td>
                    <td style="font-family:'JetBrains Mono',monospace;font-size:12px">{{ $log->target_type }} #{{ $log->target_id }}</td>
                    <td><span style="font-weight:600;color:{{ $log->outcome === 'success' ? '#006948' : '#ba1a1a' }}">{{ $log->outcome }}</span></td>
                    <td><span style="font-weight:600;color:{{ $log->severity === 'critical' ? '#ba1a1a' : ($log->severity === 'warn' ? '#b45309' : '#0369a1') }}">{{ strtoupper($log->severity) }}</span></td>
                    <td class="muted" style="font-family:'JetBrains Mono',monospace;font-size:12px">{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted" style="text-align:center;padding:32px">Belum ada jejak audit.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="margin-top:14px">{{ $logs->links() }}</div>
</div>
@endsection
