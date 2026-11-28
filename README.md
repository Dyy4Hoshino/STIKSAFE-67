# 🛡️ STIKSAFE-67

> 🔍 Sistem pemeriksaan awal keamanan file dan URL berbasis Machine Learning dan analisis statis tanpa mengeksekusi file di perangkat pengguna.

---

## 📑 Daftar Isi
1. [Tentang STIKSAFE-67](#tentang-stiksafe-67)
2. [Arsitektur & Cara Kerja](#arsitektur--cara-kerja)
3. [Prasyarat Sistem](#prasyarat-sistem)
4. [Panduan Instalasi & Setup](#panduan-instalasi--setup)
5. [Menjalankan Aplikasi](#menjalankan-aplikasi)
6. [Cara Penggunaan](#cara-penggunaan)
7. [Klasifikasi Hasil Pemeriksaan](#klasifikasi-hasil-pemeriksaan)
8. [Akun Bawaan (Seeder)](#akun-bawaan-seeder)
9. [Hal Penting & Panduan Keamanan](#hal-penting--panduan-keamanan)

---

## 📌 Tentang STIKSAFE-67

STIKSAFE-67 adalah aplikasi web full-stack yang menggabungkan framework backend **Laravel 12** dan microservice Python **FastAPI** berbobot model deteksi AI (EMBER2024 / heuristic analysis). Dirancang untuk memberikan deteksi dini berkas biner, dokumen, skrip, dan tautan yang berpotensi mencurigakan atau berbahaya.

---

## 🏗️ Arsitektur & Cara Kerja

```text
[ Pengguna ] ──> [ Web Laravel (Port 8000) ]
                        │
                        ├─> Simpan berkas ke Karantina Terisolasi (Storage)
                        ├─> Masukkan job ke antrean (Queue Database)
                        │
                  [ Queue Worker ]
                        │
                        ▼ (HTTP Request via Port 8001)
             [ ML Microservice (FastAPI) ]
                        │
                        ├─> Ekstraksi Fitur Statis PE Biner / File
                        ├─> Prediksi Model EMBER2024 / Heuristic Rules
                        └─> Deteksi 3-Lapis Analisis URL
                        │
                        ▼
             [ Simpan Hasil Pemeriksaan ]
                        │
                        └─> Dashboard & Riwayat Pengguna

Gambar ikon aplikasi Gemini.
Mulai percakapan dengan Gemini di aplikasi.
Kini tersedia di Google Play.
Percakapan dengan Gemini
# STIKSAFE-67



Sistem pemeriksaan awal keamanan file dan URL berbasis Machine Learning dan analisis statis tanpa mengeksekusi file di perangkat pengguna.



---



## Daftar Isi

1. [Tentang STIKSAFE-67](#tentang-stiksafe-67)

2. [Arsitektur & Cara Kerja](#arsitektur--cara-kerja)

3. [Prasyarat Sistem](#prasyarat-sistem)

4. [Panduan Instalasi & Setup](#panduan-instalasi--setup)

5. [Menjalankan Aplikasi](#menjalankan-aplikasi)

6. [Cara Penggunaan](#cara-penggunaan)

7. [Klasifikasi Hasil Pemeriksaan](#klasifikasi-hasil-pemeriksaan)

8. [Akun Bawaan (Seeder)](#akun-bawaan-seeder)

9. [Hal Penting & Panduan Keamanan](#hal-penting--panduan-keamanan)



---



## Tentang STIKSAFE-67



STIKSAFE-67 adalah aplikasi web full-stack yang menggabungkan framework backend **Laravel 12** dan microservice Python **FastAPI** berbobot model deteksi AI (EMBER2024 / heuristic analysis). Dirancang untuk memberikan deteksi dini berkas biner, dokumen, skrip, dan tautan yang berpotensi mencurigakan atau berbahaya.



---



## Arsitektur & Cara Kerja



```

[ Pengguna ] ──> [ Web Laravel (Port 8000) ]

│

├─> Simpan berkas ke Karantina Terisolasi (Storage)

├─> Masukkan job ke antrean (Queue Database)

│

[ Queue Worker ]

│

▼ (HTTP Request via Port 8001)

[ ML Microservice (FastAPI) ]

│

├─> Ekstraksi Fitur Statis PE Biner / File

├─> Prediksi Model EMBER2024 / Heuristic Rules

└─> Deteksi 3-Lapis Analisis URL

│

▼

[ Simpan Hasil Pemeriksaan ]

│

└─> Dashboard & Riwayat Pengguna

```



### Mekanisme Offline Fallback

Jika layanan Machine Learning Python sedang tidak aktif (*offline* / *down*), sistem Laravel secara otomatis mengalihkan proses ke **Offline Heuristic Engine**. Analisis berkas tetap berjalan, format berbahaya/eksekutabel diberi penanda kewaspadaan, dan sistem tidak akan mengalami kegagalan proses (*error 500*).



---



## Prasyarat Sistem



Pastikan komputer telah terpasang:

- **PHP >= 8.2** (ekstensi aktif: `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `openssl`)

- **Composer** (Package manager PHP)

- **MySQL Server** (melalui Laragon, XAMPP, atau MySQL mandiri)

- **Python 3.11** (untuk ML Service)

- Web Browser modern (Chrome, Edge, Firefox)



---



## Panduan Instalasi & Setup



### 1. Clone Repository

```powershell

git clone https://github.com/Dyy4Hoshino/STIKSAFE-67.git

cd STIKSAFE-67

```



### 2. Siapkan Database MySQL

Nyalakan MySQL (misalnya klik **Start All** di Laragon), lalu buat database baru:

```sql

CREATE DATABASE stiksafe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

```



### 3. Konfigurasi Backend Laravel

Masuk ke direktori backend:

```powershell

cd STIKSAFE-67-Laravel



# Unduh dependensi PHP

composer install



# Buat file environment lokal

copy .env.example .env



# Generate encryption key

php artisan key:generate

```



Buka file `.env` dan pastikan konfigurasi basis data sesuai:

```env

DB_CONNECTION=mysql

DB_HOST=127.0.0.1

DB_PORT=3306

DB_DATABASE=stiksafe

DB_USERNAME=root

DB_PASSWORD=

```

*(Sesuaikan `DB_PASSWORD` jika MySQL Anda memiliki password).*



Lanjutkan dengan migrasi tabel dan tautan storage:

```powershell

# Migrasi database dan isi akun demo bawaan

php artisan migrate:fresh --seed



# Buat symbolic link untuk file publik (avatar foto profil)

php artisan storage:link

```



### 4. Konfigurasi Microservice Machine Learning (Python)

Buka terminal baru di direktori `STIKSAFE-67-ML`:

```powershell

cd STIKSAFE-67-ML



# Buat virtual environment

python -m venv .venv



# Aktifkan virtual environment

Set-ExecutionPolicy -Scope Process -ExecutionPolicy RemoteSigned

.\.venv\Scripts\Activate.ps1



# Install pustaka ML yang dibutuhkan

pip install -r requirements.txt

```



---



## Menjalankan Aplikasi



Aplikasi membutuhkan **3 terminal terpisah** yang berjalan bersamaan:



### Terminal 1: Machine Learning Service (Port 8001)

```powershell

cd STIKSAFE-67-ML

.\.venv\Scripts\Activate.ps1

.\run_real.bat

```

*Layanan berjalan pada: `http://127.0.0.1:8001`*



### Terminal 2: Website Utama Laravel (Port 8000)

```powershell

cd STIKSAFE-67-Laravel

php artisan serve --port=8000

```

*Akses web di browser: `http://127.0.0.1:8000`*



### Terminal 3: Antrean Latar Belakang (Queue Worker)

```powershell

cd STIKSAFE-67-Laravel

php artisan queue:work --queue=scans

```

*Worker bertugas mengeksekusi analisis berkas yang diunggah pengguna secara asynchronous.*



---



## Cara Penggunaan



### 1. Masuk ke Akun

- Buka `http://127.0.0.1:8000`.

- Masuk melalui tombol **Masuk** atau daftarkan akun baru di menu **Daftar**.

- Terdapat fitur tombol mata untuk menampilkan atau menyembunyikan kata sandi.



### 2. Memindai Berkas (File)

1. Pilih menu **Scan** di navbar atas.

2. Pastikan berada di tab **Pindai Berkas (File)**.

3. Seret (*drag & drop*) berkas atau klik area unggah untuk memilih file dari perangkat (maksimum 25 MB).

4. Klik **Mulai Pemindaian File →**.

5. Sistem akan memasukkan berkas ke antrean dan otomatis memperbarui status hingga selesai.



### 3. Memindai Tautan (URL)

1. Buka menu **Scan** lalu pilih tab **Periksa URL (Link)**.

2. Masukkan alamat URL lengkap yang mencurigakan (contoh: `https://contoh-situs.com/path`).

3. Klik **Analisis URL Sekarang →**.

4. Hasil analisis 3-lapis akan langsung ditampilkan.



### 4. Mengubah Tema (Dark Mode / Light Mode)

- **Halaman Beranda Publik:** Klik ikon bulan/matahari di samping tombol *Mulai scan*.

- **Halaman Profil Akun:** Buka menu **Profil**, lalu klik tombol di bagian **Tampilan website** untuk beralih mode. Preferensi tema tersimpan otomatis di browser.



### 5. Fitur Panel Administrator

Pengguna dengan role `admin` memiliki akses ke **Menu Admin**:

- **Dashboard:** Statistik antrean worker, kesehatan sistem, dan ringkasan deteksi 30 hari terakhir.

- **Scan Log:** Pemantauan berkas dan URL dari seluruh pengguna.

- **Pengguna:** Manajemen akun dan pengaturan hak akses role.

- **Feedback:** Meninjau tanggapan dan masukan dari pengguna.

- **Kirim Notifikasi:** Mengirim pengumuman langsung ke pengguna tertentu (Semua user, Semua admin, atau Seluruh akun).

- **Setelan & Status ML:** Pemantauan status live ML Service dan pengaturan limit aplikasi.



---



## Klasifikasi Hasil Pemeriksaan



Setiap berkas atau tautan yang selesai dianalisis akan diberi salah satu label berikut:



| Label | Keterangan |

|---|---|

| **Aman** | Tidak ditemukan pola biner atau karakteristik ancaman yang mencurigakan. |

| **Mencurigakan** | Terdeteksi indikasi anomali struktural atau perilaku kode yang memerlukan kehati-hatian. |

| **Berbahaya** | Terdeteksi pola malware, virus, atau signature ancaman siber yang terkonfirmasi. |

| **Indikasi netral** | Model belum menemukan ciri khas ancaman yang jelas, namun belum dapat menjamin berkas 100% aman (disarankan untuk tidak langsung dibuka). |



---



## Akun Bawaan (Seeder)



Untuk keperluan pengujian lokal, seeder menyediakan akun bawaan:



- **Akun Administrator:**

- Email: `adminstiksafe@stiksafe.local`

- Password: `TuDeYa22`

- **Akun Pengguna Reguler:**

- Email: `userstiksafe@stiksafe.local`

- Password: `UserStikSafe2026!`



---



## Hal Penting & Panduan Keamanan



1. **Keamanan Kredensial Saat Push ke GitHub:**

- Jangan pernah melakukan *commit* atau *push* file `.env` asli, file database dump berisi data pengguna, token rahasia, atau `APP_KEY` ke repository publik.

- File `.env.example` telah disediakan sebagai acuan konfigurasi tanpa memuat rahasia produksi.

2. **Jangan Jalankan `migrate:fresh` di Lingkungan Produksi:**

- Perintah `migrate:fresh` akan menghapus seluruh data tabel. Gunakan `php artisan migrate --force` saat melakukan pembaruan di server produksi.

3. **Keterbatasan Hasil Analisis:**

- Hasil pemeriksaan adalah estimasi berbasis analisis statis model Machine Learning dan aturan heuristik. Hasil ini berfungsi sebagai panduan mitigasi awal dan bukan jaminan keamanan absolut. Jangan ubah kata katanya tapi buat lebih bagus dengan menambah beberapa stiker atau hiasan untuk merapikan teks ini,ini di gunakan untuk teks Readme.md di github

🛡️ STIKSAFE-67
🔍 Sistem pemeriksaan awal keamanan file dan URL berbasis Machine Learning dan analisis statis tanpa mengeksekusi file di perangkat pengguna.

📑 Daftar Isi
Tentang STIKSAFE-67

Arsitektur & Cara Kerja

Prasyarat Sistem

Panduan Instalasi & Setup

Menjalankan Aplikasi

Cara Penggunaan

Klasifikasi Hasil Pemeriksaan

Akun Bawaan (Seeder)

Hal Penting & Panduan Keamanan

📌 Tentang STIKSAFE-67
STIKSAFE-67 adalah aplikasi web full-stack yang menggabungkan framework backend Laravel 12 dan microservice Python FastAPI berbobot model deteksi AI (EMBER2024 / heuristic analysis). Dirancang untuk memberikan deteksi dini berkas biner, dokumen, skrip, dan tautan yang berpotensi mencurigakan atau berbahaya.

🏗️ Arsitektur & Cara Kerja
Plaintext
[ Pengguna ] ──> [ Web Laravel (Port 8000) ]
                        │
                        ├─> Simpan berkas ke Karantina Terisolasi (Storage)
                        ├─> Masukkan job ke antrean (Queue Database)
                        │
                  [ Queue Worker ]
                        │
                        ▼ (HTTP Request via Port 8001)
             [ ML Microservice (FastAPI) ]
                        │
                        ├─> Ekstraksi Fitur Statis PE Biner / File
                        ├─> Prediksi Model EMBER2024 / Heuristic Rules
                        └─> Deteksi 3-Lapis Analisis URL
                        │
                        ▼
             [ Simpan Hasil Pemeriksaan ]
                        │
                        └─> Dashboard & Riwayat Pengguna
🔄 Mekanisme Offline Fallback
Jika layanan Machine Learning Python sedang tidak aktif (offline / down), sistem Laravel secara otomatis mengalihkan proses ke Offline Heuristic Engine. Analisis berkas tetap berjalan, format berbahaya/eksekutabel diberi penanda kewaspadaan, dan sistem tidak akan mengalami kegagalan proses (error 500).

⚙️ Prasyarat Sistem
Pastikan komputer telah terpasang:

🐘 PHP >= 8.2 (ekstensi aktif: pdo_mysql, curl, mbstring, fileinfo, openssl)

📦 Composer (Package manager PHP)

🗄️ MySQL Server (melalui Laragon, XAMPP, atau MySQL mandiri)

🐍 Python 3.11 (untuk ML Service)

🌐 Web Browser modern (Chrome, Edge, Firefox)

🛠️ Panduan Instalasi & Setup
1️⃣ Clone Repository
PowerShell
git clone https://github.com/Dyy4Hoshino/STIKSAFE-67.git
cd STIKSAFE-67
2️⃣ Siapkan Database MySQL
Nyalakan MySQL (misalnya klik Start All di Laragon), lalu buat database baru:

SQL
CREATE DATABASE stiksafe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
3️⃣ Konfigurasi Backend Laravel
Masuk ke direktori backend:

PowerShell
cd STIKSAFE-67-Laravel

# Unduh dependensi PHP
composer install

# Buat file environment lokal
copy .env.example .env

# Generate encryption key
php artisan key:generate
Buka file .env dan pastikan konfigurasi basis data sesuai:

Cuplikan kode
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stiksafe
DB_USERNAME=root
DB_PASSWORD=
(Sesuaikan DB_PASSWORD jika MySQL Anda memiliki password).

Lanjutkan dengan migrasi tabel dan tautan storage:

PowerShell
# Migrasi database dan isi akun demo bawaan
php artisan migrate:fresh --seed

# Buat symbolic link untuk file publik (avatar foto profil)
php artisan storage:link
4️⃣ Konfigurasi Microservice Machine Learning (Python)
Buka terminal baru di direktori STIKSAFE-67-ML:

PowerShell
cd STIKSAFE-67-ML

# Buat virtual environment
python -m venv .venv

# Aktifkan virtual environment
Set-ExecutionPolicy -Scope Process -ExecutionPolicy RemoteSigned
.\.venv\Scripts\Activate.ps1

# Install pustaka ML yang dibutuhkan
pip install -r requirements.txt
🚀 Menjalankan Aplikasi
Aplikasi membutuhkan 3 terminal terpisah yang berjalan bersamaan:

🖥️ Terminal 1: Machine Learning Service (Port 8001)
PowerShell
cd STIKSAFE-67-ML
.\.venv\Scripts\Activate.ps1
.\run_real.bat
Layanan berjalan pada: [http://127.0.0.1:8001](http://127.0.0.1:8001)

🖥️ Terminal 2: Website Utama Laravel (Port 8000)
PowerShell
cd STIKSAFE-67-Laravel
php artisan serve --port=8000
Akses web di browser: [http://127.0.0.1:8000](http://127.0.0.1:8000)

🖥️ Terminal 3: Antrean Latar Belakang (Queue Worker)
PowerShell
cd STIKSAFE-67-Laravel
php artisan queue:work --queue=scans
Worker bertugas mengeksekusi analisis berkas yang diunggah pengguna secara asynchronous.

📖 Cara Penggunaan
1. Masuk ke Akun
Buka [http://127.0.0.1:8000](http://127.0.0.1:8000).

Masuk melalui tombol Masuk atau daftarkan akun baru di menu Daftar.

Terdapat fitur tombol mata untuk menampilkan atau menyembunyikan kata sandi.

2. Memindai Berkas (File)
Pilih menu Scan di navbar atas.

Pastikan berada di tab Pindai Berkas (File).

Seret (drag & drop) berkas atau klik area unggah untuk memilih file dari perangkat (maksimum 25 MB).

Klik Mulai Pemindaian File →.

Sistem akan memasukkan berkas ke antrean dan otomatis memperbarui status hingga selesai.

3. Memindai Tautan (URL)
Buka menu Scan lalu pilih tab Periksa URL (Link).

Masukkan alamat URL lengkap yang mencurigakan (contoh: [https://contoh-situs.com/path](https://contoh-situs.com/path)).

Klik Analisis URL Sekarang →.

Hasil analisis 3-lapis akan langsung ditampilkan.

4. Mengubah Tema (Dark Mode / Light Mode)
Halaman Beranda Publik: Klik ikon bulan/matahari di samping tombol Mulai scan.

Halaman Profil Akun: Buka menu Profil, lalu klik tombol di bagian Tampilan website untuk beralih mode. Preferensi tema tersimpan otomatis di browser.

5. Fitur Panel Administrator
Pengguna dengan role admin memiliki akses ke Menu Admin:

Dashboard: Statistik antrean worker, kesehatan sistem, dan ringkasan deteksi 30 hari terakhir.

Scan Log: Pemantauan berkas dan URL dari seluruh pengguna.

Pengguna: Manajemen akun dan pengaturan hak akses role.

Feedback: Meninjau tanggapan dan masukan dari pengguna.

Kirim Notifikasi: Mengirim pengumuman langsung ke pengguna tertentu (Semua user, Semua admin, atau Seluruh akun).

Setelan & Status ML: Pemantauan status live ML Service dan pengaturan limit aplikasi.

📊 Klasifikasi Hasil Pemeriksaan
Setiap berkas atau tautan yang selesai dianalisis akan diberi salah satu label berikut:

Label	Keterangan
🟢 Aman	Tidak ditemukan pola biner atau karakteristik ancaman yang mencurigakan.
🟡 Mencurigakan	Terdeteksi indikasi anomali struktural atau perilaku kode yang memerlukan kehati-hatian.
🔴 Berbahaya	Terdeteksi pola malware, virus, atau signature ancaman siber yang terkonfirmasi.
⚪ Indikasi netral	Model belum menemukan ciri khas ancaman yang jelas, namun belum dapat menjamin berkas 100% aman (disarankan untuk tidak langsung dibuka).
🔐 Akun Bawaan (Seeder)
Untuk keperluan pengujian lokal, seeder menyediakan akun bawaan:

👤 Akun Administrator:

Email: adminstiksafe@stiksafe.local

Password: TuDeYa22

👥 Akun Pengguna Reguler:

Email: userstiksafe@stiksafe.local

Password: UserStikSafe2026!

⚠️ Hal Penting & Panduan Keamanan
Keamanan Kredensial Saat Push ke GitHub:

Jangan pernah melakukan commit atau push file .env asli, file database dump berisi data pengguna, token rahasia, atau APP_KEY ke repository publik.

File .env.example telah disediakan sebagai acuan konfigurasi tanpa memuat rahasia produksi.

Jangan Jalankan migrate:fresh di Lingkungan Produksi:

Perintah migrate:fresh akan menghapus seluruh data tabel. Gunakan php artisan migrate --force saat melakukan pembaruan di server produksi.

Keterbatasan Hasil Analisis:

Hasil pemeriksaan adalah estimasi berbasis analisis statis model Machine Learning dan aturan heuristik. Hasil ini berfungsi sebagai panduan mitigasi awal dan bukan jaminan keamanan absolut.

Kasi yang langsung bisa saya copy pastew

Markdown
# 🛡️ STIKSAFE-67

> 🔍 Sistem pemeriksaan awal keamanan file dan URL berbasis Machine Learning dan analisis statis tanpa mengeksekusi file di perangkat pengguna.

---

## 📑 Daftar Isi
1. [Tentang STIKSAFE-67](#tentang-stiksafe-67)
2. [Arsitektur & Cara Kerja](#arsitektur--cara-kerja)
3. [Prasyarat Sistem](#prasyarat-sistem)
4. [Panduan Instalasi & Setup](#panduan-instalasi--setup)
5. [Menjalankan Aplikasi](#menjalankan-aplikasi)
6. [Cara Penggunaan](#cara-penggunaan)
7. [Klasifikasi Hasil Pemeriksaan](#klasifikasi-hasil-pemeriksaan)
8. [Akun Bawaan (Seeder)](#akun-bawaan-seeder)
9. [Hal Penting & Panduan Keamanan](#hal-penting--panduan-keamanan)

---

## 📌 Tentang STIKSAFE-67

STIKSAFE-67 adalah aplikasi web full-stack yang menggabungkan framework backend **Laravel 12** dan microservice Python **FastAPI** berbobot model deteksi AI (EMBER2024 / heuristic analysis). Dirancang untuk memberikan deteksi dini berkas biner, dokumen, skrip, dan tautan yang berpotensi mencurigakan atau berbahaya.

--
## 🏗️ Arsitektur & Cara Kerja

```text
[ Pengguna ] ──> [ Web Laravel (Port 8000) ]
                        │
                        ├─> Simpan berkas ke Karantina Terisolasi (Storage)
                        ├─> Masukkan job ke antrean (Queue Database)
                        │
                  [ Queue Worker ]
                        │
                        ▼ (HTTP Request via Port 8001)
             [ ML Microservice (FastAPI) ]
                        │
                        ├─> Ekstraksi Fitur Statis PE Biner / File
                        ├─> Prediksi Model EMBER2024 / Heuristic Rules
                        └─> Deteksi 3-Lapis Analisis URL
                        │
                        ▼
             [ Simpan Hasil Pemeriksaan ]
                        │
                        └─> Dashboard & Riwayat Pengguna
🔄 Mekanisme Offline Fallback
Jika layanan Machine Learning Python sedang tidak aktif (offline / down), sistem Laravel secara otomatis mengalihkan proses ke Offline Heuristic Engine. Analisis berkas tetap berjalan, format berbahaya/eksekutabel diberi penanda kewaspadaan, dan sistem tidak akan mengalami kegagalan proses (error 500).

⚙️ Prasyarat Sistem
Pastikan komputer telah terpasang:

🐘 PHP >= 8.2 (ekstensi aktif: pdo_mysql, curl, mbstring, fileinfo, openssl)

📦 Composer (Package manager PHP)

🗄️ MySQL Server (melalui Laragon, XAMPP, atau MySQL mandiri)

🐍 Python 3.11 (untuk ML Service)

🌐 Web Browser modern (Chrome, Edge, Firefox)

🛠️ Panduan Instalasi & Setup
1️⃣ Clone l
git clone [https://github.com/Dyy4Hoshino/STIKSAFE-67.git](https://github.com/Dyy4Hoshino/STIKSAFE-67.git)
cd STIKSAFE-67

2️⃣ Siapkan Database MySQL
Nyalakan MySQL (misalnya klik Start All di Laragon), lalu buat database baru:
CREATE DATABASE stiksafe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

3️⃣ Konfigurasi Backend Laravel
Masuk ke direktori backend:
cd STIKSAFE-67-Laravel

# Unduh dependensi PHP
composer install

# Buat file environment lokal
copy .env.example .env

# Generate encryption key
php artisan key:generate

Buka file .env dan pastikan konfigurasi basis data sesuai:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stiksafe
DB_USERNAME=root
DB_PASSWORD=

(Sesuaikan DB_PASSWORD jika MySQL Anda memiliki password).
Lanjutkan dengan migrasi tabel dan tautan storage:
# Migrasi database dan isi akun demo bawaan
php artisan migrate:fresh --seed

# Buat symbolic link untuk file publik (avatar foto profil)
php artisan storage:link

4️⃣ Konfigurasi Microservice Machine Learning (Python)
Buka terminal baru di direktori STIKSAFE-67-ML:
cd STIKSAFE-67-ML

# Buat virtual environment
python -m venv .venv

# Aktifkan virtual environment
Set-ExecutionPolicy -Scope Process -ExecutionPolicy RemoteSigned
.\.venv\Scripts\Activate.ps1

# Install pustaka ML yang dibutuhkan
pip install -r requirements.txt

🚀 Menjalankan Aplikasi
Aplikasi membutuhkan 3 terminal terpisah yang berjalan bersamaan:

🖥️ Terminal 1: Machine Learning Service (Port 8001)
cd STIKSAFE-67-ML
.\.venv\Scripts\Activate.ps1
.\run_real.bat
Layanan berjalan pada: http://127.0.0.1:8001

🖥️ Terminal 2: Website Utama Laravel (Port 8000)
cd STIKSAFE-67-Laravel
php artisan serve --port=8000
Akses web di browser: http://127.0.0.1:8000

🖥️ Terminal 3: Antrean Latar Belakang (Queue Worker)
cd STIKSAFE-67-Laravel
php artisan queue:work --queue=scans



Percakapan dengan Gemini
# STIKSAFE-67



Sistem pemeriksaan awal keamanan file dan URL berbasis Machine Learning dan analisis statis tanpa mengeksekusi file di perangkat pengguna.



---



## Daftar Isi

1. [Tentang STIKSAFE-67](#tentang-stiksafe-67)

2. [Arsitektur & Cara Kerja](#arsitektur--cara-kerja)

3. [Prasyarat Sistem](#prasyarat-sistem)

4. [Panduan Instalasi & Setup](#panduan-instalasi--setup)

5. [Menjalankan Aplikasi](#menjalankan-aplikasi)

6. [Cara Penggunaan](#cara-penggunaan)

7. [Klasifikasi Hasil Pemeriksaan](#klasifikasi-hasil-pemeriksaan)

8. [Akun Bawaan (Seeder)](#akun-bawaan-seeder)

9. [Hal Penting & Panduan Keamanan](#hal-penting--panduan-keamanan)



---



## Tentang STIKSAFE-67



STIKSAFE-67 adalah aplikasi web full-stack yang menggabungkan framework backend **Laravel 12** dan microservice Python **FastAPI** berbobot model deteksi AI (EMBER2024 / heuristic analysis). Dirancang untuk memberikan deteksi dini berkas biner, dokumen, skrip, dan tautan yang berpotensi mencurigakan atau berbahaya.



---



## Arsitektur & Cara Kerja



```

[ Pengguna ] ──> [ Web Laravel (Port 8000) ]

│

├─> Simpan berkas ke Karantina Terisolasi (Storage)

├─> Masukkan job ke antrean (Queue Database)

│

[ Queue Worker ]

│

▼ (HTTP Request via Port 8001)

[ ML Microservice (FastAPI) ]

│

├─> Ekstraksi Fitur Statis PE Biner / File

├─> Prediksi Model EMBER2024 / Heuristic Rules

└─> Deteksi 3-Lapis Analisis URL

│

▼

[ Simpan Hasil Pemeriksaan ]

│

└─> Dashboard & Riwayat Pengguna

```



### Mekanisme Offline Fallback

Jika layanan Machine Learning Python sedang tidak aktif (*offline* / *down*), sistem Laravel secara otomatis mengalihkan proses ke **Offline Heuristic Engine**. Analisis berkas tetap berjalan, format berbahaya/eksekutabel diberi penanda kewaspadaan, dan sistem tidak akan mengalami kegagalan proses (*error 500*).



---



## Prasyarat Sistem



Pastikan komputer telah terpasang:

- **PHP >= 8.2** (ekstensi aktif: `pdo_mysql`, `curl`, `mbstring`, `fileinfo`, `openssl`)

- **Composer** (Package manager PHP)

- **MySQL Server** (melalui Laragon, XAMPP, atau MySQL mandiri)

- **Python 3.11** (untuk ML Service)

- Web Browser modern (Chrome, Edge, Firefox)



---



## Panduan Instalasi & Setup



### 1. Clone Repository

```powershell

git clone https://github.com/Dyy4Hoshino/STIKSAFE-67.git

cd STIKSAFE-67

```



### 2. Siapkan Database MySQL

Nyalakan MySQL (misalnya klik **Start All** di Laragon), lalu buat database baru:

```sql

CREATE DATABASE stiksafe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

```



### 3. Konfigurasi Backend Laravel

Masuk ke direktori backend:

```powershell

cd STIKSAFE-67-Laravel



# Unduh dependensi PHP

composer install



# Buat file environment lokal

copy .env.example .env



# Generate encryption key

php artisan key:generate

```



Buka file `.env` dan pastikan konfigurasi basis data sesuai:

```env

DB_CONNECTION=mysql

DB_HOST=127.0.0.1

DB_PORT=3306

DB_DATABASE=stiksafe

DB_USERNAME=root

DB_PASSWORD=

```

*(Sesuaikan `DB_PASSWORD` jika MySQL Anda memiliki password).*



Lanjutkan dengan migrasi tabel dan tautan storage:

```powershell

# Migrasi database dan isi akun demo bawaan

php artisan migrate:fresh --seed



# Buat symbolic link untuk file publik (avatar foto profil)

php artisan storage:link

```



### 4. Konfigurasi Microservice Machine Learning (Python)

Buka terminal baru di direktori `STIKSAFE-67-ML`:

```powershell

cd STIKSAFE-67-ML



# Buat virtual environment

python -m venv .venv



# Aktifkan virtual environment

Set-ExecutionPolicy -Scope Process -ExecutionPolicy RemoteSigned

.\.venv\Scripts\Activate.ps1



# Install pustaka ML yang dibutuhkan

pip install -r requirements.txt

```



---



## Menjalankan Aplikasi



Aplikasi membutuhkan **3 terminal terpisah** yang berjalan bersamaan:



### Terminal 1: Machine Learning Service (Port 8001)

```powershell

cd STIKSAFE-67-ML

.\.venv\Scripts\Activate.ps1

.\run_real.bat

```

*Layanan berjalan pada: `http://127.0.0.1:8001`*



### Terminal 2: Website Utama Laravel (Port 8000)

```powershell

cd STIKSAFE-67-Laravel

php artisan serve --port=8000

```

*Akses web di browser: `http://127.0.0.1:8000`*



### Terminal 3: Antrean Latar Belakang (Queue Worker)

```powershell

cd STIKSAFE-67-Laravel

php artisan queue:work --queue=scans

```

*Worker bertugas mengeksekusi analisis berkas yang diunggah pengguna secara asynchronous.*



---



## Cara Penggunaan



### 1. Masuk ke Akun

- Buka `http://127.0.0.1:8000`.

- Masuk melalui tombol **Masuk** atau daftarkan akun baru di menu **Daftar**.

- Terdapat fitur tombol mata untuk menampilkan atau menyembunyikan kata sandi.



### 2. Memindai Berkas (File)

1. Pilih menu **Scan** di navbar atas.

2. Pastikan berada di tab **Pindai Berkas (File)**.

3. Seret (*drag & drop*) berkas atau klik area unggah untuk memilih file dari perangkat (maksimum 25 MB).

4. Klik **Mulai Pemindaian File →**.

5. Sistem akan memasukkan berkas ke antrean dan otomatis memperbarui status hingga selesai.



### 3. Memindai Tautan (URL)

1. Buka menu **Scan** lalu pilih tab **Periksa URL (Link)**.

2. Masukkan alamat URL lengkap yang mencurigakan (contoh: `https://contoh-situs.com/path`).

3. Klik **Analisis URL Sekarang →**.

4. Hasil analisis 3-lapis akan langsung ditampilkan.



### 4. Mengubah Tema (Dark Mode / Light Mode)

- **Halaman Beranda Publik:** Klik ikon bulan/matahari di samping tombol *Mulai scan*.

- **Halaman Profil Akun:** Buka menu **Profil**, lalu klik tombol di bagian **Tampilan website** untuk beralih mode. Preferensi tema tersimpan otomatis di browser.



### 5. Fitur Panel Administrator

Pengguna dengan role `admin` memiliki akses ke **Menu Admin**:

- **Dashboard:** Statistik antrean worker, kesehatan sistem, dan ringkasan deteksi 30 hari terakhir.

- **Scan Log:** Pemantauan berkas dan URL dari seluruh pengguna.

- **Pengguna:** Manajemen akun dan pengaturan hak akses role.

- **Feedback:** Meninjau tanggapan dan masukan dari pengguna.

- **Kirim Notifikasi:** Mengirim pengumuman langsung ke pengguna tertentu (Semua user, Semua admin, atau Seluruh akun).

- **Setelan & Status ML:** Pemantauan status live ML Service dan pengaturan limit aplikasi.



---



## Klasifikasi Hasil Pemeriksaan



Setiap berkas atau tautan yang selesai dianalisis akan diberi salah satu label berikut:



| Label | Keterangan |

|---|---|

| **Aman** | Tidak ditemukan pola biner atau karakteristik ancaman yang mencurigakan. |

| **Mencurigakan** | Terdeteksi indikasi anomali struktural atau perilaku kode yang memerlukan kehati-hatian. |

| **Berbahaya** | Terdeteksi pola malware, virus, atau signature ancaman siber yang terkonfirmasi. |

| **Indikasi netral** | Model belum menemukan ciri khas ancaman yang jelas, namun belum dapat menjamin berkas 100% aman (disarankan untuk tidak langsung dibuka). |



---



## Akun Bawaan (Seeder)



Untuk keperluan pengujian lokal, seeder menyediakan akun bawaan:



- **Akun Administrator:**

- Email: `adminstiksafe@stiksafe.local`

- Password: `TuDeYa22`

- **Akun Pengguna Reguler:**

- Email: `userstiksafe@stiksafe.local`

- Password: `UserStikSafe2026!`



---



## Hal Penting & Panduan Keamanan



1. **Keamanan Kredensial Saat Push ke GitHub:**

- Jangan pernah melakukan *commit* atau *push* file `.env` asli, file database dump berisi data pengguna, token rahasia, atau `APP_KEY` ke repository publik.

- File `.env.example` telah disediakan sebagai acuan konfigurasi tanpa memuat rahasia produksi.

2. **Jangan Jalankan `migrate:fresh` di Lingkungan Produksi:**

- Perintah `migrate:fresh` akan menghapus seluruh data tabel. Gunakan `php artisan migrate --force` saat melakukan pembaruan di server produksi.

3. **Keterbatasan Hasil Analisis:**

- Hasil pemeriksaan adalah estimasi berbasis analisis statis model Machine Learning dan aturan heuristik. Hasil ini berfungsi sebagai panduan mitigasi awal dan bukan jaminan keamanan absolut. Jangan ubah kata katanya tapi buat lebih bagus dengan menambah beberapa stiker atau hiasan untuk merapikan teks ini,ini di gunakan untuk teks Readme.md di github

🛡️ STIKSAFE-67
🔍 Sistem pemeriksaan awal keamanan file dan URL berbasis Machine Learning dan analisis statis tanpa mengeksekusi file di perangkat pengguna.

📑 Daftar Isi
Tentang STIKSAFE-67

Arsitektur & Cara Kerja

Prasyarat Sistem

Panduan Instalasi & Setup

Menjalankan Aplikasi

Cara Penggunaan

Klasifikasi Hasil Pemeriksaan

Akun Bawaan (Seeder)

Hal Penting & Panduan Keamanan

📌 Tentang STIKSAFE-67
STIKSAFE-67 adalah aplikasi web full-stack yang menggabungkan framework backend Laravel 12 dan microservice Python FastAPI berbobot model deteksi AI (EMBER2024 / heuristic analysis). Dirancang untuk memberikan deteksi dini berkas biner, dokumen, skrip, dan tautan yang berpotensi mencurigakan atau berbahaya.

🏗️ Arsitektur & Cara Kerja
Plaintext
[ Pengguna ] ──> [ Web Laravel (Port 8000) ]
                        │
                        ├─> Simpan berkas ke Karantina Terisolasi (Storage)
                        ├─> Masukkan job ke antrean (Queue Database)
                        │
                  [ Queue Worker ]
                        │
                        ▼ (HTTP Request via Port 8001)
             [ ML Microservice (FastAPI) ]
                        │
                        ├─> Ekstraksi Fitur Statis PE Biner / File
                        ├─> Prediksi Model EMBER2024 / Heuristic Rules
                        └─> Deteksi 3-Lapis Analisis URL
                        │
                        ▼
             [ Simpan Hasil Pemeriksaan ]
                        │
                        └─> Dashboard & Riwayat Pengguna
🔄 Mekanisme Offline Fallback
Jika layanan Machine Learning Python sedang tidak aktif (offline / down), sistem Laravel secara otomatis mengalihkan proses ke Offline Heuristic Engine. Analisis berkas tetap berjalan, format berbahaya/eksekutabel diberi penanda kewaspadaan, dan sistem tidak akan mengalami kegagalan proses (error 500).

⚙️ Prasyarat Sistem
Pastikan komputer telah terpasang:

🐘 PHP >= 8.2 (ekstensi aktif: pdo_mysql, curl, mbstring, fileinfo, openssl)

📦 Composer (Package manager PHP)

🗄️ MySQL Server (melalui Laragon, XAMPP, atau MySQL mandiri)

🐍 Python 3.11 (untuk ML Service)

🌐 Web Browser modern (Chrome, Edge, Firefox)

🛠️ Panduan Instalasi & Setup
1️⃣ Clone Repository
PowerShell
git clone https://github.com/Dyy4Hoshino/STIKSAFE-67.git
cd STIKSAFE-67
2️⃣ Siapkan Database MySQL
Nyalakan MySQL (misalnya klik Start All di Laragon), lalu buat database baru:

SQL
CREATE DATABASE stiksafe CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
3️⃣ Konfigurasi Backend Laravel
Masuk ke direktori backend:

PowerShell
cd STIKSAFE-67-Laravel

# Unduh dependensi PHP
composer install

# Buat file environment lokal
copy .env.example .env

# Generate encryption key
php artisan key:generate
Buka file .env dan pastikan konfigurasi basis data sesuai:

Cuplikan kode
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stiksafe
DB_USERNAME=root
DB_PASSWORD=
(Sesuaikan DB_PASSWORD jika MySQL Anda memiliki password).

Lanjutkan dengan migrasi tabel dan tautan storage:

PowerShell
# Migrasi database dan isi akun demo bawaan
php artisan migrate:fresh --seed

# Buat symbolic link untuk file publik (avatar foto profil)
php artisan storage:link
4️⃣ Konfigurasi Microservice Machine Learning (Python)
Buka terminal baru di direktori STIKSAFE-67-ML:

PowerShell
cd STIKSAFE-67-ML

# Buat virtual environment
python -m venv .venv

# Aktifkan virtual environment
Set-ExecutionPolicy -Scope Process -ExecutionPolicy RemoteSigned
.\.venv\Scripts\Activate.ps1

# Install pustaka ML yang dibutuhkan
pip install -r requirements.txt
🚀 Menjalankan Aplikasi
Aplikasi membutuhkan 3 terminal terpisah yang berjalan bersamaan:

🖥️ Terminal 1: Machine Learning Service (Port 8001)
PowerShell
cd STIKSAFE-67-ML
.\.venv\Scripts\Activate.ps1
.\run_real.bat
Layanan berjalan pada: [http://127.0.0.1:8001](http://127.0.0.1:8001)

🖥️ Terminal 2: Website Utama Laravel (Port 8000)
PowerShell
cd STIKSAFE-67-Laravel
php artisan serve --port=8000
Akses web di browser: [http://127.0.0.1:8000](http://127.0.0.1:8000)

🖥️ Terminal 3: Antrean Latar Belakang (Queue Worker)
PowerShell
cd STIKSAFE-67-Laravel
php artisan queue:work --queue=scans
Worker bertugas mengeksekusi analisis berkas yang diunggah pengguna secara asynchronous.

📖 Cara Penggunaan
1. Masuk ke Akun
Buka [http://127.0.0.1:8000](http://127.0.0.1:8000).

Masuk melalui tombol Masuk atau daftarkan akun baru di menu Daftar.

Terdapat fitur tombol mata untuk menampilkan atau menyembunyikan kata sandi.

2. Memindai Berkas (File)
Pilih menu Scan di navbar atas.

Pastikan berada di tab Pindai Berkas (File).

Seret (drag & drop) berkas atau klik area unggah untuk memilih file dari perangkat (maksimum 25 MB).

Klik Mulai Pemindaian File →.

Sistem akan memasukkan berkas ke antrean dan otomatis memperbarui status hingga selesai.

3. Memindai Tautan (URL)
Buka menu Scan lalu pilih tab Periksa URL (Link).

Masukkan alamat URL lengkap yang mencurigakan (contoh: [https://contoh-situs.com/path](https://contoh-situs.com/path)).

Klik Analisis URL Sekarang →.

Hasil analisis 3-lapis akan langsung ditampilkan.

4. Mengubah Tema (Dark Mode / Light Mode)
Halaman Beranda Publik: Klik ikon bulan/matahari di samping tombol Mulai scan.

Halaman Profil Akun: Buka menu Profil, lalu klik tombol di bagian Tampilan website untuk beralih mode. Preferensi tema tersimpan otomatis di browser.

5. Fitur Panel Administrator
Pengguna dengan role admin memiliki akses ke Menu Admin:

Dashboard: Statistik antrean worker, kesehatan sistem, dan ringkasan deteksi 30 hari terakhir.

Scan Log: Pemantauan berkas dan URL dari seluruh pengguna.

Pengguna: Manajemen akun dan pengaturan hak akses role.

Feedback: Meninjau tanggapan dan masukan dari pengguna.

Kirim Notifikasi: Mengirim pengumuman langsung ke pengguna tertentu (Semua user, Semua admin, atau Seluruh akun).

Setelan & Status ML: Pemantauan status live ML Service dan pengaturan limit aplikasi.

📊 Klasifikasi Hasil Pemeriksaan
Setiap berkas atau tautan yang selesai dianalisis akan diberi salah satu label berikut:

Label	Keterangan
🟢 Aman	Tidak ditemukan pola biner atau karakteristik ancaman yang mencurigakan.
🟡 Mencurigakan	Terdeteksi indikasi anomali struktural atau perilaku kode yang memerlukan kehati-hatian.
🔴 Berbahaya	Terdeteksi pola malware, virus, atau signature ancaman siber yang terkonfirmasi.
⚪ Indikasi netral	Model belum menemukan ciri khas ancaman yang jelas, namun belum dapat menjamin berkas 100% aman (disarankan untuk tidak langsung dibuka).
🔐 Akun Bawaan (Seeder)
Untuk keperluan pengujian lokal, seeder menyediakan akun bawaan:

👤 Akun Administrator:

Email: adminstiksafe@stiksafe.local

Password: TuDeYa22

👥 Akun Pengguna Reguler:

Email: userstiksafe@stiksafe.local

Password: UserStikSafe2026!

⚠️ Hal Penting & Panduan Keamanan
Keamanan Kredensial Saat Push ke GitHub:

Jangan pernah melakukan commit atau push file .env asli, file database dump berisi data pengguna, token rahasia, atau APP_KEY ke repository publik.

File .env.example telah disediakan sebagai acuan konfigurasi tanpa memuat rahasia produksi.

Jangan Jalankan migrate:fresh di Lingkungan Produksi:

Perintah migrate:fresh akan menghapus seluruh data tabel. Gunakan php artisan migrate --force saat melakukan pembaruan di server produksi.

Keterbatasan Hasil Analisis:

Hasil pemeriksaan adalah estimasi berbasis analisis statis model Machine Learning dan aturan heuristik. Hasil ini berfungsi sebagai panduan mitigasi awal dan bukan jaminan keamanan absolut.

Kasi yang langsung bisa saya copy pastew

Markdown
# 🛡️ STIKSAFE-67

> 🔍 Sistem pemeriksaan awal keamanan file dan URL berbasis Machine Learning dan analisis statis tanpa mengeksekusi file di perangkat pengguna.

---

## 📑 Daftar Isi
1. [Tentang STIKSAFE-67](#tentang-stiksafe-67)
2. [Arsitektur & Cara Kerja](#arsitektur--cara-kerja)
3. [Prasyarat Sistem](#prasyarat-sistem)
4. [Panduan Instalasi & Setup](#panduan-instalasi--setup)
5. [Menjalankan Aplikasi](#menjalankan-aplikasi)
6. [Cara Penggunaan](#cara-penggunaan)
7. [Klasifikasi Hasil Pemeriksaan](#klasifikasi-hasil-pemeriksaan)
8. [Akun Bawaan (Seeder)](#akun-bawaan-seeder)
9. [Hal Penting & Panduan Keamanan](#hal-penting--panduan-keamanan)

---

## 📌 Tentang STIKSAFE-67

STIKSAFE-67 adalah aplikasi web full-stack yang menggabungkan framework backend **Laravel 12** dan microservice Python **FastAPI** berbobot model deteksi AI (EMBER2024 / heuristic analysis). Dirancang untuk memberikan deteksi dini berkas biner, dokumen, skrip, dan tautan yang berpotensi mencurigakan atau berbahaya.

---

## 🏗️ Arsitektur & Cara Kerja

```text
[ Pengguna ] ──> [ Web Laravel (Port 8000) ]
                        │
                        ├─> Simpan berkas ke Karantina Terisolasi (Storage)
                        ├─> Masukkan job ke antrean (Queue Database)
                        │
                  [ Queue Worker ]
                        │
                        ▼ (HTTP Request via Port 8001)
             [ ML Microservice (FastAPI) ]
                        │
                        ├─> Ekstraksi Fitur Statis PE Biner / File
                        ├─> Prediksi Model EMBER2024 / Heuristic Rule
