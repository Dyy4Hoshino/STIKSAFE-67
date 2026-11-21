<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | STIKSAFE-67</title>
</head>
<body>
    <a href="#main-content">Lewati ke konten utama</a>

    <header>
        <p>STIKSAFE-67</p>
        <nav aria-label="Navigasi utama">
            <ul>
                <li><a href="{{ route('home') }}">Beranda</a></li>
                <li><a href="{{ route('login') }}">Masuk</a></li>
                <li><a href="{{ route('register') }}" aria-current="page">Daftar</a></li>
            </ul>
        </nav>
    </header>

    <main id="main-content">
        <h1>Registrasi Akun</h1>

        @if ($errors->any())
            <section role="alert" aria-labelledby="error-title">
                <h2 id="error-title">Terjadi kesalahan</h2>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li><a href="#name">{{ $error }}</a></li>
                    @endforeach
                </ul>
            </section>
        @endif

        <form method="post" action="{{ route('register.submit') }}">
            @csrf
            <p>
                <label for="name">Nama lengkap</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}"
                       required maxlength="120" autocomplete="name">
                @error('name')<span>{{ $message }}</span>@enderror
            </p>

            <p>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}"
                       required maxlength="254" autocomplete="email">
                @error('email')<span>{{ $message }}</span>@enderror
            </p>

            <p>
                <label for="password">Kata sandi</label>
                <input id="password" name="password" type="password"
                       required minlength="12" autocomplete="new-password" aria-describedby="password-hint">
                <span id="password-hint">Minimal 12 karakter.</span>
                @error('password')<span>{{ $message }}</span>@enderror
            </p>

            <p>
                <label for="password_confirmation">Konfirmasi kata sandi</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                       required minlength="12" autocomplete="new-password">
            </p>

            <p>
                <label for="accept_terms">
                    <input id="accept_terms" name="accept_terms" type="checkbox" value="1" required>
                    Saya menyetujui syarat penggunaan layanan STIKSAFE-67
                </label>
                @error('accept_terms')<span>{{ $message }}</span>@enderror
            </p>

            <p>
                <button type="submit">Daftar</button>
            </p>
        </form>

        <p>Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>.</p>
    </main>

    <footer>
        <p>STIKSAFE-67 — angkatan 2026–2027</p>
    </footer>
</body>
</html>
