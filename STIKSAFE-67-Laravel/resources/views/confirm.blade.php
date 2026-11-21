<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Batalkan Scan | STIKSAFE-67</title>
</head>
<body>
    <a href="#main-content">Lewati ke konten utama</a>
    <header><p>STIKSAFE-67</p></header>
    <main id="main-content">
        <h1>Batalkan Scan</h1>
        <p>Anda akan membatalkan pemindaian file berikut:</p>
        <dl>
            <dt>ID Scan</dt><dd>{{ $scan->id }}</dd>
            <dt>Nama file</dt><dd>{{ $scan->original_filename }}</dd>
        </dl>
        <p>Proses pemindaian tidak dapat dilanjutkan setelah dibatalkan.</p>
        <form method="post" action="{{ route('scans.cancel.confirm', $scan->id) }}">
            @csrf
            <button type="submit">Ya, lanjutkan</button>
        </form>
        <p><a href="{{ route('scans.show', $scan->id) }}">Batal</a></p>
    </main>
    <footer><p>STIKSAFE-67 — angkatan 2026–2027</p></footer>
</body>
</html>
