@extends('layouts.app')
@section('title', 'Admin Settings | STIKSAFE-67')
@section('content')
<div class="eyebrow">ADMIN / SETTINGS</div>
<h1>Pengaturan Sistem & Status ML</h1>
<p class="muted">Konfigurasi batas aplikasi, retensi, timeout, dan kesehatan layanan Machine Learning.</p>

<div class="grid" style="grid-template-columns:1fr 1fr;gap:20px;align-items:start;margin-top:24px">
    <section class="panel">
        <h3>Status Layanan ML</h3>
        <p class="muted">Koneksi ke ML Service (<code>{{ config('services.ml.url', 'Belum diset') }}</code>)</p>
        <div style="margin:16px 0;padding:14px;border-radius:10px;background:{{ $mlStatus['available']?'#10b98115':'#ef444415' }};border:1px solid {{ $mlStatus['available']?'#10b98144':'#ef444444' }};color:{{ $mlStatus['available']?'#6ee7b7':'#fca5a5' }}">
            <strong>{{ $mlStatus['available'] ? '● ONLINE' : '✕ OFFLINE / GAGAL' }}</strong>
            <p style="margin:6px 0 0;font-size:13px">{{ $mlStatus['message'] }}</p>
        </div>
    </section>

    <section class="panel">
        <h3>Formulir Pengaturan</h3>
        <form method="POST" action="{{ route('admin.settings.update') }}" style="display:flex;flex-direction:column;gap:14px">
            @csrf
            @method('PUT')
            <div class="field">
                <label>Max Ukuran File Upload (MB)</label>
                <input type="number" name="settings[scan.max_file_size_mb]" value="{{ old('settings.scan.max_file_size_mb', data_get($settings, 'scan.max_file_size_mb.setting_value', 25)) }}" min="1" max="100" style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:8px 12px;border-radius:8px">
            </div>
            <div class="field">
                <label>Batas Waktu Total Scan (menit)</label>
                <input type="number" name="settings[scan.max_total_duration_min]" value="{{ old('settings.scan.max_total_duration_min', data_get($settings, 'scan.max_total_duration_min.setting_value', 15)) }}" min="1" max="60" style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:8px 12px;border-radius:8px">
            </div>
            <div class="field">
                <label>Retensi File di Quarantina (hari)</label>
                <input type="number" name="settings[retention.file_days]" value="{{ old('settings.retention.file_days', data_get($settings, 'retention.file_days.setting_value', 7)) }}" min="1" max="365" style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:8px 12px;border-radius:8px">
            </div>
            <div class="field">
                <label>Timeout HTTP ML Service (detik)</label>
                <input type="number" name="settings[ml.timeout_seconds]" value="{{ old('settings.ml.timeout_seconds', data_get($settings, 'ml.timeout_seconds.setting_value', 30)) }}" min="1" max="120" style="background:#0b1120;border:1px solid rgba(70,130,180,.35);color:#f8fafc;padding:8px 12px;border-radius:8px">
            </div>
            <button type="submit" class="btn primary" style="align-self:flex-start">Simpan Pengaturan</button>
        </form>
    </section>
</div>
@endsection
