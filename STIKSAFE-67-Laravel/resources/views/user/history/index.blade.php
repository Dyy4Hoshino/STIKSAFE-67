@extends('layouts.app')
@section('title', 'Riwayat Scan | STIKSAFE-67')

@section('content')
@if ($errors->has('delete'))
    <p class="alert" role="alert">{{ $errors->first('delete') }}</p>
@endif
@if (session('success'))
    <p class="alert" role="status">{{ session('success') }}</p>
@endif

<div class="eyebrow">AUDIT LOG / PERSONAL</div>
<h1>Riwayat scan.</h1>
<p class="muted">Cari dan tinjau hasil pemeriksaan sebelumnya.</p>

<section class="panel" style="margin:22px 0">
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
        <p class="muted">{{ request()->hasAny(['q','filter']) ? 'Tidak ada hasil untuk pencarian ini.' : 'Belum ada riwayat scan.' }}</p>
    @else
        <div class="table-wrap">
            <table class="data">
                <caption style="text-align:left;padding:12px;color:#91a0b4">Daftar scan milik Anda</caption>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama file</th>
                        <th>Waktu upload</th>
                        <th>Status</th>
                        <th>Hasil</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($scans as $scan)
                        <tr>
                            <td>{{ $scans->firstItem() + $loop->index }}</td>
                            <td>{{ $scan->original_filename }}</td>
                            <td>
                                @if ($scan->created_at)
                                    <time datetime="{{ $scan->created_at->toISOString() }}">{{ $scan->created_at->timezone(config('app.timezone'))->format('d M Y H:i') }}</time>
                                @endif
                            </td>
                            <td>{{ match($scan->status?->value){'queued'=>'Antrean','processing'=>'Diproses','completed'=>'Selesai','failed'=>'Gagal','cancelled'=>'Dibatalkan','rejected'=>'Ditolak',default=>'—'} }}</td>
                            <td>{{ match($scan->result_label?->value){'safe'=>'Aman','suspicious'=>'Mencurigakan','malicious'=>'Berbahaya','unknown'=>'Tidak diketahui',default=>'—'} }}</td>
                            <td>
                                <a style="color:#FF8A38" href="{{ route('scans.show', $scan->id) }}">Lihat detail</a><br>
                                <a style="color:#fca5a5" href="{{ route('confirm', ['action'=>'delete-scan','target'=>$scan->id]) }}">Hapus</a>
                            </td>
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
