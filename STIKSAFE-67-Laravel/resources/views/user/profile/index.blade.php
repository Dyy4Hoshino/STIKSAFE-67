<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya | STIKSAFE-67</title>
</head>
<body>
    <a href="#main-content">Lewati ke konten utama</a>
    <header>
        <p>STIKSAFE-67</p>
        <nav aria-label="Navigasi utama">
            <ul>
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('scan.create') }}">Scan File</a></li>
                <li><a href="{{ route('history') }}">Riwayat</a></li>
                <li><a href="{{ route('profile') }}" aria-current="page">Profil</a></li>
                <li><form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form></li>
            </ul>
        </nav>
    </header>
    <main id="main-content">
        @if (session('success'))<p role="status">{{ session('success') }}</p>@endif

        <h1>Profil Saya</h1>

        <section aria-labelledby="akun-title">
            <h2 id="akun-title">Informasi Akun</h2>
            <dl>
                <dt>Nama</dt><dd>{{ $user->name }}</dd>
                <dt>Email</dt><dd>{{ $user->email }}</dd>
                <dt>Bergabung sejak</dt>
                <dd>
                    @if ($user->created_at)
                        <time datetime="{{ $user->created_at->toISOString() }}">{{ $user->created_at->timezone(config('app.timezone'))->format('d M Y H:i:s') }}</time>
                    @else
                        —
                    @endif
                </dd>
            </dl>
        </section>

        <section aria-labelledby="ubah-nama-title">
            <h2 id="ubah-nama-title">Ubah Nama</h2>
            <form method="post" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                <p>
                    <label for="name">Nama lengkap</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="120">
                    @error('name')<span>{{ $message }}</span>@enderror
                </p>
                <button type="submit">Simpan Nama</button>
            </form>
        </section>

        <section aria-labelledby="ganti-password-title">
            <h2 id="ganti-password-title">Ganti Kata Sandi</h2>
            <form method="post" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')
                <p>
                    <label for="current_password">Kata sandi saat ini</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password">
                    @error('current_password')<span>{{ $message }}</span>@enderror
                </p>
                <p>
                    <label for="password">Kata sandi baru</label>
                    <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password">
                    <span>Minimal 12 karakter.</span>
                    @error('password')<span>{{ $message }}</span>@enderror
                </p>
                <p>
                    <label for="password_confirmation">Konfirmasi kata sandi baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password">
                </p>
                <p>Perhatian: Mengganti kata sandi dapat mengakhiri sesi aktif di perangkat lain.</p>
                <button type="submit">Perbarui Kata Sandi</button>
            </form>
        </section>
    </main>
    <footer><p>STIKSAFE-67 — angkatan 2026–2027</p></footer>
</body>
</html>
