<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Scan | STIKSAFE-67</title>
</head>
<body>
    <a href="#main-content">Lewati ke konten utama</a>
    <header>
        <p>STIKSAFE-67</p>
        <nav aria-label="Navigasi utama">
            <ul>
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('scan.create') }}">Scan File</a></li>
                <li><a href="{{ route('history') }}" aria-current="page">Riwayat</a></li>
                <li><form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form></li>
            </ul>
        </nav>
    </header>
    <main id="main-content">
        <h1>Riwayat Scan</h1>

        @if (session('success'))<p role="status">{{ session('success') }}</p>@endif

        <fieldset>
            <legend>Cari dan saring</legend>
            <form method="get" action="{{ route('history') }}">
                <p>
                    <label for="q">Cari nama file atau ID</label>
                    <input id="q" name="q" type="search" value="{{ request('q') }}">
                </p>
                <p>
                    <label for="filter-status">Status</label>
                    <select id="filter-status" name="filter[status]">
                        <option value="">Semua Status</option>
                        <option value="queued" @selected(request('filter.status') === 'queued')>Menunggu antrean</option>
                        <option value="processing" @selected(request('filter.status') === 'processing')>Sedang dianalisis</option>
                        <option value="completed" @selected(request('filter.status') === 'completed')>Selesai</option>
                        <option value="failed" @selected(request('filter.status') === 'failed')>Gagal</option>
                        <option value="cancelled" @selected(request('filter.status') === 'cancelled')>Dibatalkan</option>
                        <option value="rejected" @selected(request('filter.status') === 'rejected')>Ditolak</option>
                    </select>
                </p>
                <p>
                    <label for="filter-result">Hasil</label>
                    <select id="filter-result" name="filter[result]">
                        <option value="">Semua Hasil</option>
                        <option value="safe" @selected(request('filter.result') === 'safe')>Safe</option>
                        <option value="suspicious" @selected(request('filter.result') === 'suspicious')>Suspicious</option>
                        <option value="malicious" @selected(request('filter.result') === 'malicious')>Malicious</option>
                        <option value="unknown" @selected(request('filter.result') === 'unknown')>Unknown</option>
                    </select>
                </p>
                <p>
                    <label for="filter-from">Dari tanggal</label>
                    <input id="filter-from" name="filter[from]" type="date" value="{{ request('filter.from') }}">
                </p>
                <p>
                    <label for="filter-to">Sampai tanggal</label>
                    <input id="filter-to" name="filter[to]" type="date" value="{{ request('filter.to') }}">
                </p>
                <p>
                    <label for="sort">Urutkan</label>
                    <select id="sort" name="sort">
                        <option value="-created_at" @selected(request('sort', '-created_at') === '-created_at')>Terbaru</option>
                        <option value="created_at" @selected(request('sort') === 'created_at')>Terlama</option>
                    </select>
                </p>
                <button type="submit">Terapkan</button>
                <a href="{{ route('history') }}">Atur ulang</a>
            </form>
        </fieldset>

        @if ($scans->isEmpty())
            <p>{{ request()->hasAny(['q', 'filter']) ? 'Tidak ada hasil untuk pencarian ini.' : 'Belum ada riwayat scan.' }}</p>
        @else
            <table>
                <caption>Daftar scan milik Anda</caption>
                <thead>
                    <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nama file</th>
                        <th scope="col">Waktu upload</th>
                        <th scope="col">Status</th>
                        <th scope="col">Hasil</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($scans as $scan)
                        <tr>
                            <td>{{ $scans->firstItem() + $loop->index }}</td>
                            <td>{{ $scan->original_filename }}</td>
                            <td>
                                @if ($scan->created_at)
                                    <time datetime="{{ $scan->created_at->toISOString() }}">{{ $scan->created_at->timezone(config('app.timezone'))->format('d M Y H:i:s') }}</time>
                                @endif
                            </td>
                            <td>{{ match ($scan->status?->value) { 'queued' => 'Menunggu antrean', 'processing' => 'Sedang dianalisis', 'completed' => 'Selesai', 'failed' => 'Gagal', 'cancelled' => 'Dibatalkan', 'rejected' => 'Ditolak', default => '—' } }}</td>
                            <td>{{ match ($scan->result_label?->value) { 'safe' => 'Safe', 'suspicious' => 'Suspicious', 'malicious' => 'Malicious', 'unknown' => 'Unknown', default => '—' } }}</td>
                            <td>
                                <a href="{{ route('scans.show', $scan->id) }}">Lihat Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <nav aria-label="Navigasi halaman">
                <p>Halaman {{ $scans->currentPage() }} dari {{ $scans->lastPage() }}</p>
                <ul>
                    @if ($scans->onFirstPage())
                        <li>Sebelumnya</li>
                    @else
                        <li><a href="{{ $scans->previousPageUrl() }}">Sebelumnya</a></li>
                    @endif

                    @if ($scans->hasMorePages())
                        <li><a href="{{ $scans->nextPageUrl() }}">Berikutnya</a></li>
                    @else
                        <li>Berikutnya</li>
                    @endif
                </ul>
            </nav>
        @endif
    </main>
    <footer><p>STIKSAFE-67 — angkatan 2026–2027</p></footer>
</body>
</html>
