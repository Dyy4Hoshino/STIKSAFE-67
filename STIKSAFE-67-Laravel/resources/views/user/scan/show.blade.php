<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Scan | STIKSAFE-67</title>
    @if (in_array($scan->status?->value, ['queued', 'processing'], true) && isset($scan->poll_after_ms) && $scan->poll_after_ms >= 5000)
        <meta http-equiv="refresh" content="{{ (int) ($scan->poll_after_ms / 1000) }}">
    @endif
</head>
<body>
    <a href="#main-content">Lewati ke konten utama</a>
    <header>
        <p>STIKSAFE-67</p>
        <nav aria-label="Navigasi utama">
            <ul>
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('scan.create') }}">Scan File</a></li>
                <li><a href="{{ route('scans.show', $scan->id) }}" aria-current="page">Detail Scan</a></li>
                <li><form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form></li>
            </ul>
        </nav>
    </header>
    <main id="main-content">
        @if (session('success'))<p role="status">{{ session('success') }}</p>@endif
        <h1>Detail Scan</h1>
        <dl>
            <dt>ID Scan</dt><dd>{{ $scan->id }}</dd>
            <dt>Nama file</dt><dd>{{ $scan->original_filename }}</dd>
            <dt>Ukuran</dt><dd>{{ number_format($scan->file_size / 1024, 2, ',', '.') }} KB</dd>
            <dt>Jenis file</dt><dd>{{ $scan->mime_type }}</dd>
            <dt>Status</dt><dd>{{ $scan->status?->value === 'queued' ? 'Menunggu antrean' : ($scan->status?->value === 'processing' ? 'Sedang dianalisis' : ucfirst($scan->status?->value ?? 'Tidak diketahui')) }}</dd>
            @if ($scan->created_at)<dt>Waktu upload</dt><dd><time datetime="{{ $scan->created_at->toISOString() }}">{{ $scan->created_at->timezone(config('app.timezone'))->format('d M Y H:i:s T') }}</time></dd>@endif
        </dl>

        @if (in_array($scan->status?->value, ['queued', 'processing'], true))
            <section aria-labelledby="running-title">
                <h2 id="running-title">Pemindaian Sedang Berlangsung</h2>
                <p>Jangan mengirim file yang sama berulang. Hasil akan tersedia di Riwayat.</p>
                <p><a href="{{ route('scans.show', $scan->id) }}">Segarkan status</a></p>
                <p><a href="{{ route('scans.cancel', $scan->id) }}">Batalkan scan</a></p>
            </section>
        @elseif ($scan->status?->value === 'completed')
            <section aria-labelledby="result-title">
                <h2 id="result-title">Hasil Pemindaian</h2>
                <p>Hasil: {{ match ($scan->result_label?->value) { 'safe' => 'Safe — tidak ditemukan indikator berbahaya berdasarkan analisis sistem', 'suspicious' => 'Suspicious — perlu pemeriksaan lebih lanjut', 'malicious' => 'Malicious — diklasifikasikan berbahaya', 'unknown' => 'Unknown — sistem tidak dapat menentukan klasifikasi', default => '—' } }}</p>
                @if ($scan->confidence !== null)<p>Skor model: {{ $scan->confidence }}</p><p>Skor model berada pada skala 0–1 dan bukan persentase peluang.</p>@endif
                @if ($scan->model_version)<p>Versi model: {{ $scan->model_version }}</p>@endif
                <p>Hasil ini adalah prediksi model dan bukan jaminan keamanan mutlak. Tetap berhati-hati terhadap file yang tidak Anda kenal.</p>
                <p>File asli: {{ $scan->file_purged_at ? 'sudah dihapus sesuai kebijakan retensi' : 'tersedia' }}</p>
            </section>
        @else
            <section aria-labelledby="incomplete-title">
                <h2 id="incomplete-title">Pemindaian Tidak Selesai</h2>
                <p>Status: {{ ucfirst($scan->status?->value ?? 'Tidak diketahui') }}</p>
                <p>Pemindaian gagal atau tidak dapat diselesaikan. Silakan coba lagi.</p>
                <p><a href="{{ route('scan.create') }}">Pindai file lain</a></p>
            </section>
        @endif
        <p><a href="{{ route('dashboard') }}">Kembali ke Dashboard</a></p>
    </main>
    <footer><p>STIKSAFE-67 — angkatan 2026–2027</p></footer>
</body>
</html>
