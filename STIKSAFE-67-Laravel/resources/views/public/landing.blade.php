<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda | STIKSAFE-67</title>
</head>
<body>
    <a href="#main-content">Lewati ke konten utama</a>

    <header>
        <p>STIKSAFE-67</p>
        <nav aria-label="Navigasi utama">
            <ul>
                <li><a href="{{ route('home') }}" aria-current="page">Beranda</a></li>
                <li><a href="#fitur">Fitur</a></li>
                <li><a href="#alur">Alur</a></li>
                <li><a href="#tentang">Tentang</a></li>
                <li><a href="#kontak">Kontak</a></li>
                <li><a href="{{ route('login') }}">Masuk</a></li>
                <li><a href="{{ route('register') }}">Daftar</a></li>
            </ul>
        </nav>
    </header>

    <main id="main-content">
        <section aria-labelledby="hero-title">
            <h1>Pindai File dengan STIKSAFE-67</h1>
            <p>Unggah file untuk dianalisis oleh sistem Machine Learning dan lihat hasil pemindaian serta riwayat Anda.</p>
            <p><a href="{{ route('register') }}">Daftar untuk mulai memindai file</a></p>
        </section>

        <section id="fitur" aria-labelledby="fitur-title">
            <h2>Fitur</h2>
            <ul>
                <li>Unggah file untuk dipindai (mendukung berbagai format hingga file yang berkamuflase).</li>
                <li>Analisis statis berbasis Machine Learning tanpa menjalankan file.</li>
                <li>Hasil klasifikasi: Safe, Suspicious, Malicious, atau Unknown.</li>
                <li>Riwayat pemindaian untuk akun Anda.</li>
            </ul>
        </section>

        <section id="alur" aria-labelledby="alur-title">
            <h2>Alur Penggunaan</h2>
            <ol>
                <li>Daftar atau masuk ke akun Anda.</li>
                <li>Unggah file yang berhak Anda analisis.</li>
                <li>File masuk antrean dan dianalisis oleh sistem.</li>
                <li>Lihat hasil pemindaian setelah proses selesai.</li>
                <li>Buka riwayat untuk melihat pemindaian sebelumnya.</li>
            </ol>
        </section>

        <section id="tentang" aria-labelledby="tentang-title">
            <h2>Tentang STIKSAFE-67</h2>
            <p>STIKSAFE-67 membantu memberikan indikasi awal terhadap file melalui analisis Machine Learning.</p>
            <p>Hasil ini adalah prediksi model dan bukan jaminan keamanan mutlak. Tetap berhati-hati terhadap file yang tidak Anda kenal.</p>
        </section>

        <section id="kontak" aria-labelledby="kontak-title">
            <h2>Kontak</h2>
            <p>Hubungi pengelola STIKSAFE-67 melalui kanal yang disediakan institusi Anda.</p>
        </section>
    </main>

    <footer>
        <p>STIKSAFE-67 — angkatan 2026–2027</p>
    </footer>
</body>
</html>
