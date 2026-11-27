@extends('layouts.app')
@section('title', 'Pemantauan Scan | STIKSAFE-67')
@section('content')
<div class="eyebrow">OPERATIONS / SCAN MONITOR</div>
<h1>Pemantauan scan.</h1>
<p class="muted">Telusuri status, hasil, dan error dari seluruh pemindaian.</p>

<section class="panel" style="margin:22px 0">
    <form method="get" action="{{ route('admin.scans') }}">
        <div class="form-grid">
            <div class="field">
                <label for="q">CARI ID / NAMA FILE</label>
                <input id="q" name="q" type="search" value="{{ request('q') }}">
            </div>
            <div class="field">
                <label for="status">STATUS</label>
                <select id="status" name="filter[status]">
                    <option value="">Semua status</option>
                    @foreach (['queued' => 'Menunggu antrean', 'processing' => 'Sedang dianalisis', 'completed' => 'Selesai', 'failed' => 'Gagal', 'cancelled' => 'Dibatalkan', 'rejected' => 'Ditolak'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('filter.status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="result">HASIL</label>
                <select id="result" name="filter[result]">
                    <option value="">Semua hasil</option>
                    @foreach (['safe' => 'Aman', 'suspicious' => 'Mencurigakan', 'malicious' => 'Berbahaya', 'unknown' => 'Indikasi netral'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('filter.result') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="from">DARI TANGGAL</label>
                <input id="from" name="filter[from]" type="date" value="{{ request('filter.from') }}">
            </div>
            <div class="field">
                <label for="to">SAMPAI TANGGAL</label>
                <input id="to" name="filter[to]" type="date" value="{{ request('filter.to') }}">
            </div>
        </div>
        <div class="actions">
            <button class="btn primary" type="submit">Terapkan</button>
            <a class="btn" href="{{ route('admin.scans') }}">Atur ulang</a>
        </div>
    </form>
</section>

<section class="panel">
    @if ($scans->isEmpty())
        <p class="muted">Tidak ada scan yang cocok.</p>
    @else
        <div class="table-wrap">
            <table class="data">
                <caption style="text-align:left;padding:12px;color:#565e74">Daftar seluruh scan</caption>
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Pemilik</th>
                        <th scope="col">Nama file</th>
                        <th scope="col">Dibuat</th>
                        <th scope="col">Status</th>
                        <th scope="col">Hasil</th>
                        <th scope="col">Error</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($scans as $scan)
                        <tr>
                            <td>{{ $scan->id }}</td>
                            <td>{{ $scan->user?->name ?? '—' }}</td>
                            <td>{{ $scan->original_filename }}</td>
                            <td>
                                @if ($scan->created_at)
                                    <time datetime="{{ $scan->created_at->toISOString() }}">{{ $scan->created_at->format('d M Y H:i') }}</time>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $scan->status?->value ?? '—' }}</td>
                            <td>{{ $scan->result_label?->value ?? '—' }}</td>
                            <td>{{ $scan->error_code ?? '—' }}</td>
                            <td><a style="color:#a33900" href="{{ route('admin.scans.show', $scan->id) }}">Detail →</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="actions">
            <span class="muted">Halaman {{ $scans->currentPage() }} dari {{ $scans->lastPage() }}</span>
            @if (! $scans->onFirstPage())
                <a class="btn" href="{{ $scans->previousPageUrl() }}">← Sebelumnya</a>
            @endif
            @if ($scans->hasMorePages())
                <a class="btn" href="{{ $scans->nextPageUrl() }}">Berikutnya →</a>
            @endif
        </div>
    @endif
</section>
@endsection
