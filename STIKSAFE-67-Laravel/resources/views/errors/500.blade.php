<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kesalahan Sistem (500) | STIKSAFE-67</title>
</head>
<body>
    <header><p>STIKSAFE-67</p></header>
    <main>
        <h1>500 — Kesalahan Sistem</h1>
        <p>Terjadi kesalahan pada sistem. Coba lagi nanti.</p>
        @isset($exception)
            @php $requestId = method_exists($exception, 'getMessage') ? request()->header('X-Request-ID') : null; @endphp
            @if ($requestId)
                <p>Kode rujukan: <code>{{ $requestId }}</code></p>
            @endif
        @endisset
        <p><a href="{{ route('home') }}">Kembali ke Beranda</a></p>
    </main>
    <footer><p>STIKSAFE-67 — angkatan 2026–2027</p></footer>
</body>
</html>
