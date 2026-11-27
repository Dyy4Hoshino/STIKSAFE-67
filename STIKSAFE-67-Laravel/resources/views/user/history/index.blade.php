@extends('layouts.app')
@section('title', 'Riwayat Scan | STIKSAFE-67')

@section('content')
@if ($errors->has('delete'))
    <p class="alert" role="alert">{{ $errors->first('delete') }}</p>
@endif

<div style="display:flex;justify-content:space-between;align-items:flex-end;gap:14px;flex-wrap:wrap;margin-bottom:20px">
    <div>
        <div class="eyebrow">RIWAYAT PRIBADI</div>
        <h1 style="margin:4px 0">Riwayat scan.</h1>
        <p class="muted" style="margin:0">Cari dan tinjau hasil pemeriksaan sebelumnya.</p>
    </div>
    <a class="btn primary" href="{{ route('scan.create') }}">+ Scan Baru</a>
</div>

<section class="panel" style="margin-bottom:18px">
    <form method="get" action="{{ route('history') }}">
        <div class="form-grid">
            <div class="field">
                <label for="q">CARI FILE / ID</label>
                <input id="q" name="q" type="search" value="{{ request('q') }}" placeholder="Nama file atau ID">
            </div>
            <div class="field">
                <label for="filter-status">STATUS</label>
                <select id="filter-status" name="filter[status]">
                    <option value="">Semua status</option>
                    @foreach(['queued'=>'Menunggu antrean','processing'=>'Sedang dianalisis','completed'=>'Selesai','failed'=>'Gagal','cancelled'=>'Dibatalkan','rejected'=>'Ditolak'] as $value=>$label)
                        <option value="{{ $value }}" @selected(request('filter.status')===$value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="filter-result">HASIL</label>
                <select id="filter-result" name="filter[result]">
                    <option value="">Semua hasil</option>
                    @foreach(['safe'=>'Aman','suspicious'=>'Mencurigakan','malicious'=>'Berbahaya','unknown'=>'Tidak diketahui'] as $value=>$label)
                        <option value="{{ $value }}" @selected(request('filter.result')===$value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="sort">URUTAN</label>
                <select id="sort" name="sort">
                    <option value="-created_at" @selected(request('sort','-created_at')==='-created_at')>Terbaru</option>
                    <option value="created_at" @selected(request('sort')==='created_at')>Terlama</option>
                </select>
            </div>
            <div class="field">
                <label for="filter-from">DARI TANGGAL</label>
                <input id="filter-from" name="filter[from]" type="date" value="{{ request('filter.from') }}">
            </div>
            <div class="field">
                <label for="filter-to">SAMPAI TANGGAL</label>
                <input id="filter-to" name="filter[to]" type="date" value="{{ request('filter.to') }}">
            </div>
        </div>
        <div class="actions">
            <button class="btn primary" type="submit">Terapkan filter</button>
            <a class="btn" href="{{ route('history') }}">Atur ulang</a>
        </div>
    </form>
</section>

<section class="panel">
    @if ($scans->isEmpty())
        <div style="text-align:center;padding:42px 16px">
            <div style="width:46px;height:46px;border-radius:10px;background:#f2f4f6;border:1px solid #e6e8ea;color:#565e74;display:grid;place-items:center;margin:0 auto 12px">
                <span class="material-symbols-outlined" style="font-size:22px">history</span>
            </div>
            <h3 style="margin:0 0 4px">{{ request()->hasAny(['q','filter']) ? 'Tidak ada hasil ditemukan' : 'Belum ada riwayat scan' }}</h3>
            <p class="muted" style="margin:0 0 16px;font-size:13.5px">{{ request()->hasAny(['q','filter']) ? 'Coba ubah atau atur ulang filter pencarian Anda.' : 'Mulai dengan memindai file atau URL pertama Anda.' }}</p>
            <a class="btn primary" href="{{ route('scan.create') }}">+ Mulai Scan Pertama</a>
        </div>
    @else
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama file</th>
                        <th>Waktu upload</th>
                        <th>Status</th>
                        <th>Hasil</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($scans as $scan)
                        <tr>
                            <td class="tabular-nums">{{ $scans->firstItem() + $loop->index }}</td>
                            <td style="font-weight:500">{{ $scan->original_filename }}</td>
                            <td class="muted" style="font-size:12.5px;white-space:nowrap">
                                @if ($scan->created_at)
                                    <time datetime="{{ $scan->created_at->toISOString() }}">{{ $scan->created_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</time>
                                @endif
                            </td>
                            <td>{{ match($scan->status?->value){'queued'=>'Antrean','processing'=>'Diproses','completed'=>'Selesai','failed'=>'Gagal','cancelled'=>'Dibatalkan','rejected'=>'Ditolak',default=>'—'} }}</td>
                            <td>{{ match($scan->result_label?->value){'safe'=>'Aman','suspicious'=>'Mencurigakan','malicious'=>'Berbahaya','unknown'=>'Tidak diketahui',default=>'—'} }}</td>
                            <td style="text-align:right;white-space:nowrap">
                                <a style="color:#a33900;font-weight:600;margin-right:10px" href="{{ route('scans.show', $scan->id) }}">Lihat detail</a>
                                <a style="color:#ba1a1a;font-weight:500" href="{{ route('confirm', ['action'=>'delete-scan','target'=>$scan->id]) }}">Hapus</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="actions" style="justify-content:space-between">
            <span class="muted" style="font-size:12.5px">Halaman {{ $scans->currentPage() }} dari {{ $scans->lastPage() }}</span>
            <div style="display:flex;gap:8px">
                @if (! $scans->onFirstPage())
                    <a class="btn" href="{{ $scans->previousPageUrl() }}">← Sebelumnya</a>
                @endif
                @if ($scans->hasMorePages())
                    <a class="btn" href="{{ $scans->nextPageUrl() }}">Berikutnya →</a>
                @endif
            </div>
        </div>
    @endif
</section>
@endsection
