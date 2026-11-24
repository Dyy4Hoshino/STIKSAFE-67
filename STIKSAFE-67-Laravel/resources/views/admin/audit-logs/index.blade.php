@extends('layouts.app')
@section('title', 'Admin Audit Logs | STIKSAFE-67')
@section('content')
<div class="eyebrow">ADMIN / AUDIT LOGS</div>
<h1>Jejak audit sistem</h1>
<p class="muted">Catatan historis tindakan dan kejadian sistem.</p>

<form method="GET" style="margin:24px 0;display:flex;gap:12px;flex-wrap:wrap">
    <select name="action" style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:8px 12px;border-radius:8px">
        <option value="">Semua Aksi</option>
        <option value="account.status.changed" @selected(request('action')==='account.status.changed')>Status Akun Diubah</option>
        <option value="account.role.changed" @selected(request('action')==='account.role.changed')>Role Akun Diubah</option>
        <option value="scan.retried" @selected(request('action')==='scan.retried')>Scan Dicoba Ulang</option>
    </select>
    <select name="severity" style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:8px 12px;border-radius:8px">
        <option value="">Semua Tingkat (Severity)</option>
        <option value="info" @selected(request('severity')==='info')>Info</option>
        <option value="warn" @selected(request('severity')==='warn')>Warning</option>
        <option value="critical" @selected(request('severity')==='critical')>Critical</option>
    </select>
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
                <td><time datetime="{{ $log->occurred_at?->toISOString() }}">{{ $log->occurred_at?->format('d M Y H:i:s') }}</time></td>
                <td>{{ $log->actor?->name ?? 'System' }} <span class="muted">({{ $log->actor_type }})</span></td>
                <td><code>{{ $log->action }}</code></td>
                <td>{{ $log->target_type }} #{{ $log->target_id }}</td>
                <td><span style="color:{{ $log->outcome==='success'?'#10b981':'#ef4444' }}">{{ $log->outcome }}</span></td>
                <td><span style="color:{{ $log->severity==='critical'?'#ef4444':($log->severity==='warn'?'#f59e0b':'#38bdf8') }}">{{ strtoupper($log->severity) }}</span></td>
                <td class="muted">{{ $log->ip_address }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="muted" style="text-align:center;padding:32px">Belum ada jejak audit.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $logs->links() }}
</div>
@endsection
