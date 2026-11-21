<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin | STIKSAFE-67</title>
</head>
<body>
    <a href="#main-content">Lewati ke konten utama</a>
    <header>
        <p>STIKSAFE-67</p>
        <nav aria-label="Navigasi utama">
            <ul>
                <li><a href="{{ route('admin.dashboard') }}" aria-current="page">Dashboard Admin</a></li>
                <li><a href="{{ route('admin.scans') }}">Scan</a></li>
                <li><form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form></li>
            </ul>
        </nav>
    </header>
    <main id="main-content">
        <h1>Dashboard Admin</h1>

        <section aria-labelledby="users-title">
            <h2 id="users-title">Akun per Status</h2>
            <dl>
                @forelse ($usersByStatus as $status => $count)
                    <dt>{{ ucfirst($status) }}</dt><dd>{{ $count }}</dd>
                @empty
                    <dt>Belum ada data</dt><dd>0</dd>
                @endforelse
            </dl>
        </section>

        <section aria-labelledby="scans-title">
            <h2 id="scans-title">Scan</h2>
            <dl>
                @foreach (['queued' => 'Menunggu antrean', 'processing' => 'Sedang dianalisis', 'completed' => 'Selesai', 'failed' => 'Gagal', 'cancelled' => 'Dibatalkan', 'rejected' => 'Ditolak'] as $status => $label)
                    <dt>{{ $label }}</dt><dd>{{ $scansByStatus[$status] ?? 0 }}</dd>
                @endforeach
            </dl>
        </section>

        <section aria-labelledby="hasil-title">
            <h2 id="hasil-title">Distribusi Hasil</h2>
            <dl>
                @foreach (['safe' => 'Safe', 'suspicious' => 'Suspicious', 'malicious' => 'Malicious', 'unknown' => 'Unknown'] as $result => $label)
                    <dt>{{ $label }}</dt><dd>{{ $scansByResult[$result] ?? 0 }}</dd>
                @endforeach
            </dl>
        </section>

        <section aria-labelledby="queue-title">
            <h2 id="queue-title">Antrean</h2>
            <dl><dt>Kedalaman antrean</dt><dd>{{ $queueDepth }}</dd></dl>
        </section>

        <section aria-labelledby="daily-title">
            <h2 id="daily-title">Scan 30 Hari Terakhir</h2>
            @if ($dailyScans->isEmpty())
                <p>Belum ada scan dalam 30 hari terakhir.</p>
            @else
                <table>
                    <caption>Jumlah scan per hari</caption>
                    <thead><tr><th scope="col">Tanggal</th><th scope="col">Jumlah</th></tr></thead>
                    <tbody>
                        @foreach ($dailyScans as $dailyScan)
                            <tr><td>{{ $dailyScan->date }}</td><td>{{ $dailyScan->count }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>
        <p><time datetime="{{ $updatedAt->toISOString() }}">Diperbarui pada {{ $updatedAt->timezone(config('app.timezone'))->format('d M Y H:i:s T') }}</time></p>
    </main>
    <footer><p>STIKSAFE-67 — angkatan 2026–2027</p></footer>
</body>
</html>
