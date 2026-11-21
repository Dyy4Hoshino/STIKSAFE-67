<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan File | STIKSAFE-67</title>
</head>
<body>
    <a href="#main-content">Lewati ke konten utama</a>
    <header>
        <p>STIKSAFE-67</p>
        <nav aria-label="Navigasi utama">
            <ul>
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('scan.create') }}" aria-current="page">Scan File</a></li>
                <li><form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form></li>
            </ul>
        </nav>
    </header>
    <main id="main-content">
        <h1>Scan File</h1>
        <p>File diterima, masuk antrean, dianalisis, lalu hasilnya dapat dilihat.</p>

        @if ($errors->any())
            <section role="alert" aria-labelledby="error-title">
                <h2 id="error-title">Terjadi kesalahan</h2>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li><a href="#file">{{ $error }}</a></li>
                    @endforeach
                </ul>
            </section>
        @endif

        <form method="post" action="{{ route('scan.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
            <p>
                <label for="file">Pilih file</label>
                <input id="file" name="file" type="file" required>
            </p>
            <p>Format yang didukung: Semua jenis file (termasuk deteksi file eksekusi yang menyamar sebagai dokumen atau gambar).</p>
            <p>Batas ukuran: 25 MB.</p>
            <p>Perhatian: Unggah hanya file yang Anda berhak analisis. Jangan menutup halaman sampai konfirmasi muncul.</p>
            <p>Perhatian: Atribut penerimaan file hanya petunjuk browser. Backend tetap memvalidasi jenis, isi, dan ukuran file.</p>
            <button type="submit">Mulai Scan</button>
        </form>
    </main>
    <footer><p>STIKSAFE-67 — angkatan 2026–2027</p></footer>
</body>
</html>
