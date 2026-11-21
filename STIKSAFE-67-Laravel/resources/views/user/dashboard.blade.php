<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | STIKSAFE-67</title>
</head>
<body>
    <a href="#main-content">Lewati ke konten utama</a>
    <header>
        <p>STIKSAFE-67</p>
        <nav aria-label="Navigasi utama">
            <ul>
                <li><a href="{{ route('dashboard') }}" aria-current="page">Dashboard</a></li>
                <li><a href="{{ route('scan.create') }}">Scan File</a></li>
                <li><a href="{{ route('history') }}">Riwayat</a></li>
                <li><form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form></li>
            </ul>
        </nav>
    </header>
    <main id="main-content">
        @if (session('success'))<p role="status">{{ session('success') }}</p>@endif

        <h1>Dashboard</h1>
        <p>Selamat datang, {{ $user->name }}!</p>

        <section aria-labelledby="ringkasan-title">
            <h2 id="ringkasan-title">Ringkasan Pemindaian</h2>
            <dl>
                <dt>Total Scan</dt>
                <dd>{{ $totalScan }}</dd>

                <dt>Safe</dt>
                <dd>{{ $safeCount }}</dd>

                <dt>Suspicious</dt>
                <dd>{{ $suspiciousCount }}</dd>

                <dt>Malicious</dt>
                <dd>{{ $maliciousCount }}</dd>

                <dt>Unknown</dt>
                <dd>{{ $unknownCount }}</dd>

                <dt>Sedang berjalan (antrean + dianalisis)</dt>
                <dd>{{ $runningCount }}</dd>

                <dt>Tidak selesai (gagal / dibatalkan / ditolak)</dt>
                <dd>{{ $incompleteCount }}</dd>
            </dl>
        </section>

        <section aria-labelledby="scan-terbaru-title">
            <h2 id="scan-terbaru-title">Scan Terbaru</h2>
            @if ($recentScans->isEmpty())
                <p>Belum ada scan. Mulai dengan memindai file pertama Anda.</p>
            @else
                <table>
                    <caption>10 scan terbaru</caption>
                    <thead>
                        <tr>
                            <th scope="col">Nama file</th>
                            <th scope="col">Waktu</th>
                            <th scope="col">Status / Hasil</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentScans as $scan)
                            <tr>
                                <td>{{ $scan->original_filename }}</td>
                                <td>
                                    @if ($scan->created_at)
                                        <time datetime="{{ $scan->created_at->toISOString() }}">{{ $scan->created_at->timezone(config('app.timezone'))->format('d M Y H:i:s') }}</time>
                                    @endif
                                </td>
                                <td>
                                    {{ match ($scan->status?->value) { 'queued' => 'Menunggu antrean', 'processing' => 'Sedang dianalisis', 'completed' => 'Selesai', 'failed' => 'Gagal', 'cancelled' => 'Dibatalkan', 'rejected' => 'Ditolak', default => '—' } }}
                                    @if ($scan->status?->value === 'completed')
                                        — {{ match ($scan->result_label?->value) { 'safe' => 'Safe', 'suspicious' => 'Suspicious', 'malicious' => 'Malicious', 'unknown' => 'Unknown', default => '—' } }}
                                    @endif
                                </td>
                                <td><a href="{{ route('scans.show', $scan->id) }}">Lihat Detail</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <p><time datetime="{{ now()->toISOString() }}">Diperbarui pada {{ now()->timezone(config('app.timezone'))->format('d M Y H:i:s T') }}</time></p>
        <p><a href="{{ route('scan.create') }}">Scan File</a></p>
    </main>
    <footer><p>STIKSAFE-67 — angkatan 2026–2027</p></footer>
</body>
</html>
