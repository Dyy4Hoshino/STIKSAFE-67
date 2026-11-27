@extends('layouts.app')
@section('title', 'Admin Settings | STIKSAFE-67')

@section('content')
<div class="eyebrow">ADMIN / SETELAN</div>
<h1>Pengaturan Sistem &amp; Status ML</h1>
<p class="muted" style="margin:0 0 22px">Konfigurasi batas aplikasi, retensi, timeout, dan kesehatan layanan Machine Learning.</p>

<div class="form-grid" style="align-items:start">
    <section class="panel">
        <h3 style="margin:0 0 6px">Status Layanan ML</h3>
        <p class="muted" style="margin:0 0 14px;font-size:13px">Koneksi ke ML Service (<code>{{ config('services.ml.base_url', 'Belum diset') }}</code>)</p>
        <div class="ml-status-badge {{ $mlStatus['available'] ? 'online' : 'offline' }}">
            <div style="display:flex;align-items:center;gap:8px">
                <span class="status-indicator"></span>
                <strong>{{ $mlStatus['available'] ? 'ONLINE — Layanan Aktif' : 'OFFLINE — Fallback Aktif' }}</strong>
            </div>
            <p style="margin:8px 0 0;font-size:13px">{{ $mlStatus['message'] }}</p>
            @if(!$mlStatus['available'])
                <small style="display:block;margin-top:6px;opacity:0.85">Sistem secara otomatis beralih ke <em>Offline Fallback Engine</em> agar scan tetap berfungsi normal.</small>
            @endif
        </div>
    </section>

    <section class="panel">
        <h3 style="margin:0 0 14px">Formulir Pengaturan</h3>
        <form method="POST" action="{{ route('admin.settings.update') }}" style="display:flex;flex-direction:column;gap:14px">
            @csrf
            @method('PUT')
            <div class="field">
                <label>Max Ukuran File Upload (MB)</label>
                <input type="number" name="settings[scan.max_file_size_mb]" value="{{ old('settings.scan.max_file_size_mb', data_get($settings, 'scan.max_file_size_mb.setting_value', 25)) }}" min="1" max="100">
            </div>
            <div class="field">
                <label>Batas Waktu Total Scan (menit)</label>
                <input type="number" name="settings[scan.max_total_duration_min]" value="{{ old('settings.scan.max_total_duration_min', data_get($settings, 'scan.max_total_duration_min.setting_value', 15)) }}" min="1" max="60">
            </div>
            <div class="field">
                <label>Retensi File di Karantina (hari)</label>
                <input type="number" name="settings[retention.file_days]" value="{{ old('settings.retention.file_days', data_get($settings, 'retention.file_days.setting_value', 7)) }}" min="1" max="365">
            </div>
            <div class="field">
                <label>Timeout HTTP ML Service (detik)</label>
                <input type="number" name="settings[ml.timeout_seconds]" value="{{ old('settings.ml.timeout_seconds', data_get($settings, 'ml.timeout_seconds.setting_value', 30)) }}" min="1" max="120">
            </div>
            <button type="submit" class="btn primary" style="align-self:flex-start">Simpan Pengaturan</button>
        </form>
    </section>
</div>
@endsection
