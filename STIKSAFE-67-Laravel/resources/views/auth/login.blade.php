<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | STIKSAFE-67</title>
</head>
<body>
    <a href="#main-content">Lewati ke konten utama</a>

    <header>
        <p>STIKSAFE-67</p>
        <nav aria-label="Navigasi utama">
            <ul>
                <li><a href="{{ route('home') }}">Beranda</a></li>
                <li><a href="{{ route('login') }}" aria-current="page">Masuk</a></li>
                <li><a href="{{ route('register') }}">Daftar</a></li>
            </ul>
        </nav>
    </header>

    <main id="main-content">
        <h1>Masuk</h1>

        @if ($errors->any())
            <section role="alert" aria-labelledby="error-title">
                <h2 id="error-title">Terjadi kesalahan</h2>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li><a href="#email">{{ $error }}</a></li>
                    @endforeach
                </ul>
            </section>
        @endif

        <form method="post" action="{{ route('login.submit') }}">
            @csrf
            <p>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                       required autocomplete="username" aria-describedby="email-error">
                @error('email')<span id="email-error">{{ $message }}</span>@enderror
            </p>
            <p>
                <label for="password">Kata sandi</label>
                <input id="password" name="password" type="password"
                       required autocomplete="current-password">
            </p>
            <p>
                <button type="submit">Masuk</button>
            </p>
        </form>

        <p>Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>.</p>
        <p><a href="{{ route('home') }}">Kembali ke Beranda</a></p>
    </main>

    <footer>
        <p>STIKSAFE-67 — angkatan 2026–2027</p>
    </footer>
</body>
</html>
