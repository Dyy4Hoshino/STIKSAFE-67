# LEMBAR PENGUJIAN (PRD)

## Pengujian / QA & User Scenario Testing — STIKSAFE-67

| | |
|---|---|
| **Nama Produk** | STIKSAFE-67 (angka 67 merujuk angkatan 2026–2027) |
| **Jenis Dokumen** | PRD Pengujian / QA dalam bentuk **lembar soal** (bentuk dari v1.0 dipertahankan) |
| **Cakupan** | Pengujian backend, integrasi ML, dan alur User/Admin |
| **Pelaksana Pengujian** | Dina (Penguji dan Test Error) |
| **Mitra** | Dedy Bhaskara dan Arya Putra (Backend), Dwi Yasa (ML) |
| **Dokumen terpisah** | PRD Backend, PRD ML, PRD Frontend, PRD Dokumentasi (lihat 0.3) |
| **Versi Dokumen** | 2.0 |
| **Status** | Draft awal |
| **Tanggal** | 15 November 2026 |
| **Menggantikan** | PRD Pengujian v1.0 (nama lama produk: STIKSAFE 6769) |
| **Acuan** | PRD Backend STIKSAFE-67 v2.2 dan PRD ML STIKSAFE-67 v2.0 |

> **Cara membaca dokumen ini.** Dokumen ini adalah **lembar soal**: Penguji diberi pertanyaan dan situasi yang menyerupai pengalaman User sehari-hari, lalu mencoba skenario, mengamati respons sistem, dan mencatat hasilnya. Setiap soal memiliki ID (contoh `A11`, `D6`), **rujukan** ke kebutuhan pada PRD Backend/ML (contoh `FR-AUTH-02`, `BR-08`), alat uji, dan **prioritas uji**: **P0** wajib lulus untuk rilis uji pertama, **P1** diuji setelah fiturnya tersedia (tulis N/A bila belum dirilis). Angka seperti batas ukuran, kuota, dan jumlah percobaan ulang mengikuti **default usulan** PRD Backend/ML; **nilai aktual di lingkungan uji ditanyakan ke developer dan dicatat** (4.1).

---

## 0. Kontrol Dokumen

### 0.1 Riwayat Revisi

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0 | 15 Nov 2026 | Draf awal: lembar soal 38 skenario |
| 2.0 | 15 Nov 2026 | Disusun ulang mengikuti format PRD Backend v2.2 dan PRD ML v2.0 **dengan bentuk lembar soal dipertahankan**. Soal diselaraskan dengan kontrak terbaru (kode error, status HTTP, kode `ML_*`) dan ditambah menjadi 72 soal (kuota, rate limit, idempotensi, akses lintas akun, reset password, suspend, step-up, pembatalan, watchdog, retensi, dan lainnya). Ditambah: akun uji, fixture (FX) dan mock (MK), rujukan kebutuhan per soal, alur bug, kriteria kelulusan terukur, rencana bertahap, risiko, keputusan terbuka, dan indeks soal |

### 0.2 Pelaksana Pengujian dan Pembagian Tugas

PRD ini khusus **pengujian** dan dikerjakan oleh **Dina**. Developer menjadi mitra yang menyediakan lingkungan, akun, fixture, dan simulasi.

| Pihak | Peran dalam pengujian |
|---|---|
| **Dina (Penguji)** | Menjalankan soal, mencatat hasil aktual dan bukti, melaporkan bug, melakukan retest, mengisi rekap dan kesimpulan |
| **Dedy Bhaskara** | Menyediakan akun uji dan lingkungan staging; menjalankan simulasi sisi infrastruktur (ML mati, worker mati, kuota, mode pemeliharaan); triage bug; perbaikan backend |
| **Arya Putra** | Perbaikan backend dan frontend; menyediakan data uji yang diperlukan tampilan |
| **Dwi Yasa** | Menyediakan fixture dan mock ML; menjelaskan label, `reason_codes`, dan kode error ML; perbaikan sisi ML |
| **Nofa Ardianto** | Menerima ringkasan hasil dan batasan yang diketahui untuk bahan dokumentasi |

**Aturan kerja:**
1. Dina tidak memperbaiki kode; temuan dilaporkan memakai template (Bagian I).
2. Developer tidak mengubah status PASS/FAIL pada lembar; status **Fixed** menjadi **Closed** hanya setelah *retest* oleh Dina.
3. Setiap putaran pengujian dimulai dengan serah-terima: developer memberi versi aplikasi (commit), `model_version`, dan nilai konfigurasi aktual (4.1); Dina menyerahkan lembar terisi, daftar bug, dan rekap.
4. Rekap dan kesimpulan direview Dedy sebelum rilis.

### 0.3 Dokumen Terpisah (di Luar PRD Ini)

| Dokumen | Penanggung jawab | Hubungan dengan PRD Pengujian |
|---|---|---|
| PRD Backend | Dedy Bhaskara, Arya Putra | Sumber kebutuhan, kode error, aturan bisnis yang diuji |
| PRD ML | Dwi Yasa | Sumber kontrak ML, label, `reason_codes`; evaluasi performa model ada di sana (9.6) |
| PRD Frontend | Arya Putra | Kejelasan pesan dan tampilan diamati lewat soal, tanpa menguji desain |
| PRD Dokumentasi | Nofa Ardianto | Menerima ringkasan hasil pengujian |

---

## 1. Ringkasan Eksekutif

### 1.1 Gambaran Dokumen

Dokumen ini berfungsi sebagai **lembar soal dan panduan eksperimen** bagi Penguji. Pengujian mencakup login dan hak akses, upload, proses scan, hasil ML, riwayat, dashboard Admin, serta kondisi tidak biasa (*edge case*). Backend tetap menjadi sumber kebenaran untuk pemilik scan, status proses, riwayat, dan notifikasi; ML Service hanya mengembalikan hasil klasifikasi (`safe`, `suspicious`, `malicious`, `unknown`) atau error terstruktur.

### 1.2 Tujuan

1. Memastikan alur utama berjalan sesuai PRD Backend dan PRD ML.
2. Menemukan masalah pada kondisi tidak biasa yang mungkin dialami User.
3. Memverifikasi aturan keamanan inti: akses lintas akun tidak mungkin, dan kegagalan **tidak pernah** menjadi `safe`.
4. Menghasilkan bukti yang dapat ditelusuri (ID scan, `request_id`, kode respons).
5. Memberi dasar keputusan kelayakan rilis versi pertama.

### 1.3 Bukan Tujuan

- Menulis kode test otomatis (unit/feature test dikerjakan developer).
- Mengevaluasi performa model (precision, recall, dll.): bagian PRD ML (9.6).
- Load test skala besar dan *penetration test* profesional (pengembangan lanjutan, 2.3).
- Menilai desain UI/UX (PRD Frontend); yang diuji adalah kejelasan perilaku dan pesan.
- Menguji dengan malware sungguhan.

### 1.4 Batas Keselamatan

> **Gunakan hanya file dummy, fixture non-eksekusi, mock response, atau data uji yang disediakan tim.** Jangan mengunduh, membuka, atau menjalankan malware sungguhan. Jangan menguji perangkat atau jaringan tanpa izin. Jangan mengunggah data pribadi atau dokumen rahasia asli. Jangan melakukan serangan atau beban berlebihan di luar yang tertulis pada soal, dan lakukan hanya dengan akun uji. Token, password, dan isi file **tidak boleh** muncul pada bukti (screenshot/log) yang dibagikan.

### 1.5 Prinsip Pengujian

| Prinsip | Artinya |
|---|---|
| **Uji perilaku** | Yang dinilai adalah apa yang dilakukan sistem, bukan dugaan tentang kodenya |
| **Catat bukti** | Setiap hasil punya bukti: screenshot, ID scan, `request_id`, kode respons |
| **Jangan berasumsi** | Bila tidak yakin hasil yang benar, rujuk PRD atau tanyakan developer, lalu catat |
| **BLOCKED ≠ PASS** | Soal yang tidak dapat dijalankan tidak pernah dihitung lulus |
| **Kegagalan ≠ `safe`** | Setiap error, timeout, atau respons tidak valid tidak boleh berakhir sebagai hasil aman |
| **Aman dulu** | Tidak ada malware, tidak ada pengujian tanpa izin |
| **Satu soal, satu hasil** | Setiap soal diisi sendiri-sendiri; jangan menggabungkan status |

---

## 2. Ruang Lingkup

### 2.1 Termasuk dalam Pengujian

| Bagian | Modul | Jumlah soal |
|---|---|:--:|
| A | Akun dan hak akses | 13 |
| B | Upload file | 11 |
| C | Kondisi file khusus (edge case) | 8 |
| D | Hasil Machine Learning | 7 |
| E | Proses scan dan koneksi | 9 |
| F | Riwayat pemindaian, dashboard User, notifikasi | 9 |
| G | Admin dan monitoring | 7 |
| H | Eksperimen terbuka (berpikir kritis) | 8 |
| | **Total** | **72** |

### 2.2 Tidak Termasuk

| Bagian | Penanggung jawab |
|---|---|
| Unit test dan feature test otomatis | Developer (Dedy, Arya, Dwi) |
| Evaluasi performa dan kualitas model | PRD ML |
| Load test, *penetration test* profesional | Pengembangan lanjutan |
| Penilaian desain dan aksesibilitas UI | PRD Frontend |
| Pengujian dengan malware nyata | Tidak dilakukan |

### 2.3 Pengembangan Lanjutan

Otomatisasi sebagian soal (koleksi Postman yang dapat dijalankan ulang), load test dan uji ketahanan lebih dalam, *penetration test* oleh pihak independen, uji kegunaan dengan pengguna nyata, uji kompatibilitas banyak browser/perangkat, dan uji aksesibilitas.

### 2.4 Asumsi

- Pengujian dilakukan di **staging**; pada Tahap 2–3 ML berupa Fake ML Server/mode stub, lalu ML nyata di Tahap 4 (7).
- Developer menyediakan akun uji, fixture, dan mock sebelum soal terkait dijalankan.
- Penguji tunggal (Dina); hasil direview Dedy.
- Fitur berprioritas P1 belum tentu tersedia di setiap putaran; soalnya ditandai N/A bila fitur belum dirilis.
- Waktu pada sistem memakai UTC; tampilan dapat dikonversi di frontend.

---

## 3. Aktor, Peran, dan Akun Uji

### 3.1 Pembagian Tanggung Jawab Sistem

| Komponen | Tanggung jawab | Diuji lewat |
|---|---|---|
| **Backend Laravel** | Akun, role, upload dan validasi, queue, status scan, database, riwayat, dashboard, notifikasi, audit log | Bagian A, B, E, F, G |
| **ML Service** | Validasi input, preprocessing, inferensi, mengembalikan label dan metadata atau error terstruktur | Bagian C, D (lewat backend) |
| **Frontend** | Menampilkan proses dan hasil dari backend | Seluruh bagian (sebagai alat uji) |

Backend adalah sumber kebenaran untuk pemilik scan, status proses, riwayat, dan notifikasi. ML tidak menyimpan riwayat pengguna.

### 3.2 Akun Uji

| Akun | Role | Status | Dipakai untuk |
|---|---|---|---|
| `User-A` | User | Aktif | Sebagian besar soal; pemilik scan pada uji akses lintas akun |
| `User-B` | User | Aktif | Pembanding pada uji akses lintas akun dan pemisahan riwayat |
| `User-Lock` | User | Aktif | **Khusus** A6 (uji pembatasan login) agar tidak mengunci akun lain |
| `User-Suspend` | User | Aktif | A10 (akan disuspend Admin) |
| `User-Baru` | — | Dibuat Penguji sendiri | A1, A9 (email uji melalui Mailpit/kotak masuk uji) |
| `Admin-1` | Admin | Aktif | Bagian G dan soal yang memerlukan Admin |
| `Admin-2` | Admin | Aktif | A12 (uji perubahan role dan perlindungan Admin terakhir) |

Password akun uji disimpan di tempat aman dan **tidak ditulis di lembar ini**.

---

## 4. Lingkungan dan Data Uji

### 4.1 Lingkungan

| Lingkungan | Dipakai untuk | Catatan |
|---|---|---|
| Staging + Fake ML Server / mode stub | Bagian A, B, D–G (Tahap 1–3) | Hasil ML berasal dari mock (MK) |
| Staging + ML nyata | Bagian C, D, E dan regresi (Tahap 4–5) | Pakai fixture aman; **tanpa malware nyata** |
| Local/Development | Hanya developer | Bukan lingkungan resmi pengujian |
| Production | Tidak diuji, kecuali *smoke test* yang disetujui | |

**Catat pada awal setiap putaran** (diisi developer): URL staging, versi aplikasi (commit), `model_version`, serta nilai konfigurasi aktual: batas ukuran file, kuota harian, batas scan aktif, jumlah percobaan ulang, timeout, mode integrasi ML (sinkron/callback).

### 4.2 Alat Bantu

| Alat | Kegunaan |
|---|---|
| Browser + DevTools (tab *Network*) | Melihat status HTTP dan `request_id` pada respons |
| Postman/Insomnia | Soal bertanda **API**; koleksi dibuat dari OpenAPI backend |
| Mailpit/kotak masuk uji | Email verifikasi dan reset password |
| Alat tangkapan layar | Bukti; beri nama berkas `ID-soal_akun_keterangan` (mis. `A11_UserB_404.png`) |

### 4.3 Fixture File (FX)

Disediakan developer; seluruhnya **dummy dan non-eksekusi**.

| Kode | Fixture | Penyedia |
|---|---|---|
| FX-01 | Dokumen dummy normal, format didukung (kecil) | Dedy/Dwi |
| FX-02 | File kosong (0 byte) | Dedy |
| FX-03 | File dengan ekstensi di luar allowlist backend | Dedy |
| FX-04 | File yang lolos allowlist backend tetapi tidak didukung model ML | Dwi + Dedy |
| FX-05 | File rusak/terpotong (dibuat dari FX-01) | Dwi |
| FX-06 | Ekstensi tidak sesuai isi (non-eksekusi) | Dwi |
| FX-07 | File melebihi batas ukuran (batas + 1 MB, isi dummy) | Dedy |
| FX-08 | File mendekati batas ukuran (batas − 1 MB, isi dummy) | Dedy |
| FX-09 | Himpunan nama file: >255 karakter, spasi, Unicode, tanda kutip, titik berulang, `..` sebagai teks, ekstensi ganda | Dedy |
| FX-10 | Dokumen berpassword/terenkripsi yang tidak berbahaya | Dwi |
| FX-11 | Arsip dummy berisi file teks | Dedy |
| FX-12 | Aplikasi dummy tidak berbahaya (untuk C5) | Dedy/Dwi |
| FX-13 | Dokumen dummy untuk uji "file sedang dibuka" | Dina/Dedy |
| FX-14 | Himpunan ±30 file kecil sintetis untuk riwayat dan pagination | Dedy |
| FX-15 | File dengan struktur cacat sebagian (memicu `PARSE_PARTIAL`) | Dwi |
| FX-16 | File pemicu kegagalan preprocessing terkontrol | Dwi |

### 4.4 Mock Response dan Simulasi (MK)

Disediakan developer pada Fake ML Server/mode stub atau lewat simulasi lingkungan uji.

| Kode | Simulasi | Penyedia |
|---|---|---|
| MK-01 | Hasil `safe` | Dwi/Dedy |
| MK-02 | Hasil `malicious` | Dwi/Dedy |
| MK-03 | Hasil `suspicious` | Dwi/Dedy |
| MK-04 | Hasil `unknown` | Dwi/Dedy |
| MK-05 | Hasil tanpa `confidence` (`null`) | Dwi |
| MK-06 | Label di luar daftar yang diizinkan | Dwi/Dedy |
| MK-07 | JSON rusak/tidak sesuai kontrak | Dedy |
| MK-08 | `sha256` pada respons tidak cocok | Dedy |
| MK-09 | Respons lambat (melewati batas timeout) | Dedy |
| MK-10 | ML Service mati | Dedy |
| MK-11 | ML membalas `429` `ML_OVERLOADED` | Dedy |
| MK-12 | Error `5xx` berulang | Dedy |
| MK-13 | Koneksi terputus di tengah proses | Dedy |
| MK-14 | Hasil datang setelah scan dibatalkan | Dedy |
| MK-15 | Callback tanpa tanda tangan / *replay* (hanya bila Mode callback dipakai) | Dedy |
| MK-16 | `confidence` di luar rentang 0–1 | Dedy |
| MK-17 | ML membalas `ML_UNSUPPORTED_FILE` | Dwi |
| MK-18 | ML membalas `ML_FILE_CORRUPT` | Dwi |
| MK-19 | ML membalas `ML_PREPROCESSING_FAILED` | Dwi |
| MK-20 | ML membalas `ML_HASH_MISMATCH` sekali, lalu sukses pada percobaan berikutnya | Dwi/Dedy |

---

## 5. Cara Mengisi Lembar Pengujian

### 5.1 Yang Diisi pada Setiap Soal

- **Hasil aktual:** apa yang benar-benar terjadi.
- **Status:** PASS / FAIL / BLOCKED / N/A.
- **Bukti:** screenshot, ID scan, `request_id`, kode respons, atau log yang aman.
- **Bug ID:** isi jika ditemukan masalah.
- **Catatan:** informasi tambahan.

Setiap soal diawali baris info: **Rujukan** (kebutuhan pada PRD; ID berawalan `BR-ML` dan `FR-RCV/FMT/PRE/INF/ERR/MDL/DAT/EVA/OPS` dari PRD ML, selain itu dari PRD Backend), **Alat**, dan **Prioritas uji**. Fixture/mock yang diperlukan ditulis sebagai **Siapkan**.

### 5.2 Arti Status

| Status | Arti |
|---|---|
| PASS | Sistem bekerja sesuai hasil yang diharapkan |
| FAIL | Sistem tidak bekerja sesuai hasil yang diharapkan |
| BLOCKED | Tidak dapat diuji karena kendala lingkungan/akses/fixture (tulis alasannya) |
| N/A | Tidak relevan untuk versi yang diuji (tulis alasannya, mis. fitur P1 belum dirilis) |

### 5.3 Prioritas Bug dan Alur Bug

| Prioritas bug | Arti |
|---|---|
| Critical | Risiko serius pada keamanan data/akses, hasil `safe` pada kegagalan, atau layanan utama tidak dapat digunakan |
| High | Fitur utama upload, scan, hasil, atau riwayat gagal |
| Medium | Fitur berjalan tetapi ada kesalahan yang mengganggu |
| Low | Masalah tampilan, teks, atau detail kecil |

**Alur bug:** `Open` → `Triage` (Dedy menetapkan penanggung jawab) → `In Progress` → `Fixed` → `Retest` (**oleh Dina**) → `Closed`. Bug yang gagal *retest* kembali ke `In Progress`. Bug Critical dilaporkan segera ke Dedy, tidak menunggu akhir putaran.

### 5.4 Rujukan Kode Error (untuk Membaca Hasil)

Penguji membandingkan respons dengan tabel ini (lihat tab *Network* pada DevTools atau Postman).

| Kode | HTTP | Muncul ketika |
|---|:--:|---|
| `VALIDATION_FAILED` | 422 | Isian tidak valid |
| `AUTH_UNAUTHENTICATED` | 401 | Token/sesi tidak ada atau sudah tidak berlaku |
| `AUTH_INVALID_CREDENTIALS` | 401 | Email atau password salah (pesan generik) |
| `AUTH_FORBIDDEN` | 403 | Role tidak berhak (mis. User ke endpoint Admin) |
| `AUTH_ACCOUNT_SUSPENDED` | 403 | Akun disuspend (setelah kredensial benar) |
| `AUTH_STEP_UP_REQUIRED` | 403 | Aksi sensitif memerlukan konfirmasi ulang |
| `RESOURCE_NOT_FOUND` | 404 | Sumber daya tidak ada **atau milik orang lain** |
| `SCAN_INVALID_STATE` / `SCAN_NOT_RETRYABLE` | 409 | Transisi status tidak diizinkan / scan tidak dapat di-retry |
| `IDEMPOTENCY_CONFLICT` | 409 | `Idempotency-Key` sama dengan payload berbeda |
| `SCAN_FILE_EXPIRED` | 410 | File sudah dihapus karena retensi |
| `FILE_TOO_LARGE` | 413 | Melebihi batas ukuran |
| `FILE_EMPTY` / `FILE_NAME_INVALID` / `FILE_TYPE_NOT_ALLOWED` / `FILE_CONTENT_MISMATCH` / `FILE_ARCHIVE_UNSAFE` | 422 | Validasi file backend |
| `RATE_LIMITED` / `QUOTA_EXCEEDED` | 429 | Melebihi rate limit / kuota harian atau scan aktif |
| `QUEUE_OVERLOADED` / `MAINTENANCE_MODE` | 503 | Antrean penuh / mode pemeliharaan (disertai `Retry-After`) |
| `ML_UNSUPPORTED_FILE`, `ML_FILE_CORRUPT`, `ML_PREPROCESSING_FAILED`, `ML_HASH_MISMATCH`, `ML_OVERLOADED`, `ML_INFERENCE_TIMEOUT`, `ML_INTERNAL_ERROR` | — | Dari ML Service; muncul sebagai `error_code` pada scan, bukan kode HTTP ke User |
| `ML_INVALID_RESPONSE` / `ML_INTEGRITY_MISMATCH` / `SCAN_TIMEOUT` | — | Dihasilkan backend (respons ML tidak valid / hash tidak cocok / melewati batas total) |

---

# BAGIAN A — AKUN DAN HAK AKSES

*Prasyarat bagian ini:* Akun uji `User-A`, `User-B`, `User-Lock`, `User-Suspend`, `Admin-1`, `Admin-2` (3.2).

## A1. Registrasi akun baru

**Pertanyaan:** Apakah calon pengguna dapat mendaftar dan otomatis menjadi User biasa?

**Rujukan:** FR-AUTH-01, BR-01 · **Alat:** UI · **Prioritas uji:** P0

**Langkah:**
1. Buka halaman registrasi.
2. Isi nama, email baru, password yang memenuhi kebijakan, dan konfirmasi password; setujui syarat bila ada.
3. Kirim formulir.
4. Amati pesan dan status akun (cek email verifikasi bila diwajibkan).

**Hasil yang diharapkan:**
- Akun dibuat (respons 201) dengan role User.
- Formulir tidak menyediakan pilihan role.
- Bila verifikasi email wajib: fitur penuh belum dapat dipakai sebelum verifikasi dan email verifikasi diterima.
- Email yang sama dengan huruf besar/kecil berbeda tidak membuat akun ganda.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## A2. Registrasi dengan isian tidak valid dan percobaan menyisipkan role

**Pertanyaan:** Apakah sistem menolak isian tidak valid dan mengabaikan upaya menaikkan role sendiri?

**Rujukan:** FR-AUTH-01, BR-01 · **Alat:** API · **Prioritas uji:** P0

**Langkah:**
1. Dengan Postman, kirim `POST /api/v1/auth/register` berisi field tambahan `role` = `admin` dan `status` = `active`.
2. Kirim lagi dengan email tidak valid.
3. Kirim lagi dengan `password_confirmation` yang berbeda.

**Hasil yang diharapkan:**
- Field `role` dan `status` diabaikan: akun yang terbentuk tetap berrole User.
- Permintaan tidak valid ditolak (422 `VALIDATION_FAILED`) dengan pesan per field.
- Tidak ada akun yang dibuat dari permintaan tidak valid.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## A3. Kebijakan password

**Pertanyaan:** Apakah sistem menolak password yang lemah?

**Rujukan:** FR-AUTH-08 · **Alat:** UI / API · **Prioritas uji:** P0

**Langkah:**
1. Coba daftar (atau ganti password) dengan password kurang dari 12 karakter.
2. Coba password yang sama dengan email.
3. Coba password yang sangat umum (mis. `password12345`).
4. Coba password yang kuat dan panjang.

**Hasil yang diharapkan:**
- Tiga password pertama ditolak (422) dengan pesan yang jelas.
- Password kuat diterima.
- Sistem tidak menuntut aturan komposisi yang membingungkan.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## A4. Login User dengan akun valid

**Pertanyaan:** Apakah User dapat login menggunakan akun yang benar?

**Rujukan:** FR-AUTH-02 · **Alat:** UI · **Prioritas uji:** P0

**Langkah:**
1. Buka halaman login.
2. Masukkan kredensial `User-A`.
3. Tekan Login.
4. Amati halaman tujuan.

**Hasil yang diharapkan:**
- Login berhasil dan diarahkan ke dashboard User.
- Sesi dibuat dengan benar.
- User tidak memperoleh hak akses Admin.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## A5. Login dengan password salah atau email tidak terdaftar

**Pertanyaan:** Apa yang terjadi jika User memasukkan password yang salah, atau email yang tidak terdaftar?

**Rujukan:** FR-AUTH-02 · **Alat:** UI · **Prioritas uji:** P0

**Langkah:**
1. Masukkan email `User-A` dengan password yang sengaja salah.
2. Masukkan email yang tidak terdaftar dengan password apa pun.
3. Bandingkan pesan kedua kasus.

**Hasil yang diharapkan:**
- Login ditolak (401 `AUTH_INVALID_CREDENTIALS`).
- Pesan mudah dipahami dan **sama** untuk kedua kasus (tidak membocorkan apakah email terdaftar).
- Sistem tidak membocorkan password atau informasi sensitif.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## A6. Pembatasan percobaan login

**Pertanyaan:** Apakah percobaan login yang berulang dibatasi?

**Rujukan:** FR-AUTH-13 · **Alat:** UI / API · **Prioritas uji:** P0

**Langkah:**
1. Gunakan **hanya** akun `User-Lock`.
2. Masukkan password salah 6 kali berturut-turut dalam waktu singkat.
3. Coba login dengan password yang benar saat pembatasan aktif.
4. Tunggu sesuai `Retry-After`, lalu login dengan password benar.

**Hasil yang diharapkan:**
- Setelah melewati batas, permintaan ditolak 429 `RATE_LIMITED` dengan header `Retry-After`.
- Pesan jelas dan tidak menunjukkan bahwa password benar diterima.
- Setelah jeda, login normal kembali berhasil.
- Kejadian tercatat pada audit log.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## A7. User mencoba membuka halaman atau API Admin

**Pertanyaan:** Bagaimana jika User mencoba membuka URL khusus Admin secara langsung?

**Rujukan:** BR-14 · **Alat:** UI / API · **Prioritas uji:** P0

**Langkah:**
1. Login sebagai `User-A`.
2. Masukkan URL halaman Admin melalui address bar.
3. Dengan token `User-A` di Postman, panggil `GET /api/v1/admin/users`.
4. Amati respons.

**Hasil yang diharapkan:**
- Akses ditolak (403 `AUTH_FORBIDDEN`) atau diarahkan dengan benar.
- Data Admin tidak terlihat.
- Pembatasan dilakukan oleh backend, bukan hanya menyembunyikan menu frontend.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## A8. Logout

**Pertanyaan:** Apakah sesi berakhir setelah User logout?

**Rujukan:** FR-AUTH-03 · **Alat:** UI / API · **Prioritas uji:** P0

**Langkah:**
1. Login dan catat token/sesi (untuk soal API).
2. Tekan Logout.
3. Coba buka kembali halaman privat atau gunakan tombol Back.
4. Dengan Postman, panggil `GET /api/v1/auth/me` memakai token lama.

**Hasil yang diharapkan:**
- Sesi berakhir; halaman privat meminta login kembali.
- Token lama ditolak (401 `AUTH_UNAUTHENTICATED`).
- Data privat tidak dapat diakses melalui sesi yang sudah berakhir.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## A9. Lupa dan reset password

**Pertanyaan:** Apakah proses reset password aman dan token tidak dapat dipakai dua kali?

**Rujukan:** FR-AUTH-06 · **Alat:** UI + email uji · **Prioritas uji:** P0

**Langkah:**
1. Minta reset password untuk email terdaftar dan untuk email tidak terdaftar; bandingkan pesannya.
2. Buka tautan reset dari email, tetapkan password baru.
3. Buka tautan yang sama sekali lagi.
4. Coba memakai token/sesi lama (sebelum reset) pada `GET /api/v1/auth/me`.
5. Login dengan password baru.

**Hasil yang diharapkan:**
- Pesan untuk email terdaftar dan tidak terdaftar sama (generik).
- Tautan hanya berlaku sekali dan berumur pendek.
- Setelah reset, token/sesi lama **dicabut** (401).
- Login dengan password baru berhasil.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## A10. Akun yang disuspend

**Pertanyaan:** Apa yang terjadi pada User yang akunnya disuspend saat sedang login?

**Rujukan:** FR-USR-05, BR-15 · **Alat:** UI / API · **Prioritas uji:** P0

**Langkah:**
1. Login sebagai `User-Suspend` dan simpan token.
2. Minta `Admin-1` menyuspend akun tersebut (alasan wajib).
3. Dengan token lama, coba buka riwayat dan coba upload.
4. Coba login baru dengan kredensial yang benar.

**Hasil yang diharapkan:**
- Token lama ditolak (401).
- Login baru ditolak dengan 403 `AUTH_ACCOUNT_SUSPENDED` setelah kredensial benar.
- Upload tidak dapat dilakukan.
- Audit log mencatat penangguhan beserta alasan.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## A11. User mencoba membuka data milik User lain

**Pertanyaan:** Bisakah User B melihat, membatalkan, atau menghapus scan milik User A bila mengetahui ID-nya?

**Rujukan:** BR-03, FR-HIS-01 · **Alat:** API · **Prioritas uji:** P0

**Langkah:**
1. Login sebagai `User-A`, lakukan satu scan, catat `scan_id`.
2. Login sebagai `User-B` dan simpan tokennya.
3. Dengan token `User-B`, panggil: `GET /scans/{id}`, `GET /scans/{id}/status`, `POST /scans/{id}/cancel`, `DELETE /scans/{id}`.
4. Bandingkan dengan `scan_id` yang sengaja salah (tidak ada).

**Hasil yang diharapkan:**
- Seluruh panggilan mengembalikan **404** `RESOURCE_NOT_FOUND` (bukan 403), sama persis dengan ID yang tidak ada.
- Tidak ada data milik User A yang bocor pada respons.
- Scan User A tidak berubah.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## A12. Perubahan role dan perlindungan Admin

**Pertanyaan:** Apakah perubahan role aman dan Admin tidak dapat merusak dirinya sendiri atau menghilangkan Admin terakhir?

**Rujukan:** FR-USR-06, BR-05 · **Alat:** UI / API · **Prioritas uji:** P0  
**Siapkan:** Bantuan developer untuk skenario Admin terakhir.

**Langkah:**
1. Sebagai `Admin-1`, ubah role `User-B` menjadi Admin **tanpa** konfirmasi ulang.
2. Ulangi setelah melakukan konfirmasi password (*step-up*); kembalikan role `User-B` setelah soal selesai.
3. Coba ubah role atau menangguhkan akun `Admin-1` sendiri.
4. Bersama developer (di staging), coba menurunkan Admin aktif terakhir.

**Hasil yang diharapkan:**
- Tanpa *step-up*: ditolak (403 `AUTH_STEP_UP_REQUIRED`).
- Dengan *step-up*: berhasil dan tercatat pada audit log (nilai sebelum dan sesudah).
- Mengubah role/status diri sendiri ditolak.
- Admin aktif terakhir tidak dapat diturunkan atau dihapus.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## A13. Login Admin dengan 2FA

**Pertanyaan:** Apakah Admin wajib memakai verifikasi dua langkah (jika fitur sudah dirilis)?

**Rujukan:** FR-AUTH-09 · **Alat:** UI · **Prioritas uji:** P1

**Langkah:**
1. Login sebagai Admin yang belum mengaktifkan 2FA.
2. Aktifkan 2FA (TOTP) dan simpan kode pemulihan.
3. Logout, lalu login lagi memakai kode 2FA yang benar dan yang salah.

**Hasil yang diharapkan:**
- Admin yang belum 2FA diarahkan menyelesaikan 2FA sesuai kebijakan.
- Kode salah ditolak; kode benar diterima.
- Tulis **N/A** bila 2FA belum dirilis pada versi yang diuji.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

---

# BAGIAN B — UPLOAD FILE

*Prasyarat bagian ini:* Login sebagai `User-A`. Fixture FX-01 s.d. FX-09. Nilai batas ukuran dan kuota aktual dicatat dari developer (4.1).

## B1. Upload file normal yang didukung

**Pertanyaan:** Apakah sistem menerima file normal dengan format yang didukung?

**Rujukan:** FR-FILE-01, FR-SCN-01 · **Alat:** UI · **Prioritas uji:** P0  
**Siapkan:** FX-01, MK-01

**Langkah:**
1. Login sebagai User.
2. Buka fitur Scan.
3. Pilih file FX-01.
4. Upload dan amati status.

**Hasil yang diharapkan:**
- File diterima (respons 202 Accepted) dan `scan_id` diberikan.
- Informasi file (nama, ukuran) ditampilkan dengan benar.
- Record scan dibuat dengan status awal `queued`, lalu berlanjut sesuai alur.
- Scan muncul pada riwayat User yang benar.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## B2. Upload file kosong

**Pertanyaan:** Apa yang terjadi jika User mengunggah file berukuran 0 byte?

**Rujukan:** FR-FILE-02 · **Alat:** UI / API · **Prioritas uji:** P0  
**Siapkan:** FX-02

**Langkah:** Upload FX-02.

**Hasil yang diharapkan:**
- File ditolak (422 `FILE_EMPTY`).
- Pesan menjelaskan masalah.
- Tidak ada record scan, tidak ada hasil ML palsu, dan tidak ada status `completed`.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## B3. Upload format tidak didukung

**Pertanyaan:** Bagaimana jika User mengunggah format yang belum didukung?

**Rujukan:** FR-FILE-02, FR-FMT-02, BR-ML-10 · **Alat:** UI / API · **Prioritas uji:** P0  
**Siapkan:** FX-03, FX-04, MK-17

**Langkah:**
1. Upload FX-03 (di luar allowlist backend).
2. Upload FX-04 (lolos allowlist backend, tidak didukung model); gunakan MK-17 bila fixture belum tersedia.
3. Amati hasil dan riwayat untuk kedua kasus.

**Hasil yang diharapkan:**
- FX-03: ditolak backend (422 `FILE_TYPE_NOT_ALLOWED`), tanpa record scan.
- FX-04: scan berakhir `failed` dengan `error_code` `ML_UNSUPPORTED_FILE` dan pesan aman kepada User; tidak ada label hasil.
- Kegagalan **tidak diubah** menjadi label `safe`.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## B4. Ekstensi tidak sesuai isi file

**Pertanyaan:** Bagaimana jika ekstensi file terlihat didukung, tetapi isi sebenarnya berbeda format?

**Rujukan:** FR-FILE-02, FR-FMT-01, FR-FMT-04 · **Alat:** UI / API · **Prioritas uji:** P0  
**Siapkan:** FX-06

**Langkah:**
1. Gunakan fixture FX-06 yang disiapkan developer.
2. Upload melalui aplikasi.
3. Amati respons backend dan, bila lolos, hasil scan.

**Hasil yang diharapkan:**
- Sistem tidak hanya mempercayai ekstensi atau MIME type.
- Backend menolak (422 `FILE_CONTENT_MISMATCH`), atau bila lolos, ML menangani sesuai hasil identifikasi format (dapat memberi flag `FORMAT_MISMATCH`).
- Tidak terjadi crash atau prediksi tidak valid.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## B5. Upload melebihi batas ukuran

**Pertanyaan:** Apa yang terjadi jika ukuran file melebihi batas maksimal?

**Rujukan:** FR-FILE-02 · **Alat:** UI / API · **Prioritas uji:** P0  
**Siapkan:** FX-07

**Langkah:** Upload FX-07 (batas + 1 MB).

**Hasil yang diharapkan:**
- Upload ditolak (413 `FILE_TOO_LARGE`) sebelum proses ML.
- Pesan menjelaskan batas ukuran (sesuai nilai aktual di 4.1).
- Server tetap stabil; tidak ada upload parsial yang tercatat sebagai berhasil.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## B6. Nama file panjang atau karakter khusus

**Pertanyaan:** Apakah nama file panjang atau berisi karakter khusus mengganggu proses?

**Rujukan:** FR-FILE-05, FR-FILE-03 · **Alat:** UI / API · **Prioritas uji:** P0  
**Siapkan:** FX-09

**Langkah:**
1. Upload tiap anggota himpunan FX-09 satu per satu.
2. Amati penerimaan, tampilan nama pada riwayat, dan respons.

**Hasil yang diharapkan:**
- Nama ditangani dan ditampilkan dengan benar, atau ditolak dengan 422 `FILE_NAME_INVALID` bila berbahaya.
- Tampilan tidak rusak.
- Nama file tidak menyebabkan akses path yang tidak semestinya.
- Sistem memakai identitas file internal yang aman (tidak berasal dari nama file).

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## B7. Kuota harian dan scan aktif

**Pertanyaan:** Apa yang terjadi ketika User melebihi kuota scan?

**Rujukan:** FR-FILE-06, BR-16 · **Alat:** UI / API · **Prioritas uji:** P0  
**Siapkan:** Bantuan developer (ubah kuota di staging).

**Langkah:**
1. Minta developer menurunkan kuota di staging (mis. 3 scan/hari dan 2 scan aktif).
2. Upload FX-01 berulang kali hingga melewati batas.
3. Amati pesan dan respons; cek scan yang sudah ada.

**Hasil yang diharapkan:**
- Setelah batas, upload ditolak 429 `QUOTA_EXCEEDED` dengan informasi batas, pemakaian, dan waktu reset.
- Scan yang sudah ada tidak terpengaruh.
- Pesan mudah dipahami User.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## B8. Pembatasan laju upload

**Pertanyaan:** Apakah upload beruntun yang terlalu cepat dibatasi?

**Rujukan:** Backend 10.4 (limiter `scans.upload`) · **Alat:** API · **Prioritas uji:** P1  
**Siapkan:** FX-01

**Langkah:**
1. Dengan akun uji, kirim upload FX-01 beberapa kali secara cepat (melebihi batas per menit yang dicatat di 4.1).
2. Amati respons dan header.

**Hasil yang diharapkan:**
- Setelah melewati batas: 429 `RATE_LIMITED` dengan `Retry-After`.
- Setelah jeda, upload normal kembali diterima.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## B9. Pengiriman ganda dengan Idempotency-Key

**Pertanyaan:** Apakah request upload yang diulang tidak membuat scan ganda?

**Rujukan:** FR-FILE-07 · **Alat:** API · **Prioritas uji:** P0  
**Siapkan:** FX-01

**Langkah:**
1. Kirim `POST /scans` dengan header `Idempotency-Key` (UUID) dan FX-01.
2. Kirim ulang persis sama dengan kunci yang sama.
3. Kirim lagi dengan kunci yang sama tetapi file berbeda.

**Hasil yang diharapkan:**
- Pengiriman kedua mengembalikan hasil awal; hanya **satu** scan terbentuk.
- Kunci sama dengan payload berbeda ditolak 409 `IDEMPOTENCY_CONFLICT`.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## B10. File tersimpan privat

**Pertanyaan:** Apakah file yang diunggah tidak dapat diakses publik dan lokasi internalnya tidak bocor?

**Rujukan:** FR-FILE-03, FR-HIS-02 · **Alat:** UI / API · **Prioritas uji:** P0  
**Siapkan:** FX-01

**Langkah:**
1. Upload FX-01 dan buka detail scan di Postman.
2. Periksa seluruh field respons dan tab *Network* browser.
3. Coba menebak URL atau path langsung ke file tersebut.

**Hasil yang diharapkan:**
- Respons tidak memuat `stored_path` atau lokasi penyimpanan internal.
- Tidak ada URL publik ke file; tebakan path tidak menghasilkan file.
- Tidak ada unduhan file asli kecuali fitur itu memang diaktifkan.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## B11. Antrean penuh dan mode pemeliharaan

**Pertanyaan:** Apa yang terjadi bila layanan dalam pemeliharaan atau antrean terlalu penuh?

**Rujukan:** FR-SET-02, FR-SCN-14 · **Alat:** UI / API · **Prioritas uji:** P1  
**Siapkan:** Bantuan developer.

**Langkah:**
1. Minta developer mengaktifkan mode pemeliharaan (atau mensimulasikan antrean melewati ambang) di staging.
2. Coba upload FX-01; buka juga halaman riwayat.

**Hasil yang diharapkan:**
- Upload ditolak 503 `MAINTENANCE_MODE` atau `QUEUE_OVERLOADED` dengan `Retry-After` dan pesan jelas.
- Halaman riwayat/detail tetap dapat dibuka.
- Tulis **N/A** bila fitur belum dirilis.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

---

# BAGIAN C — KONDISI FILE KHUSUS (EDGE CASE)

*Prasyarat bagian ini:* Login sebagai `User-A`. Fixture FX-05, FX-08, FX-10 s.d. FX-13, FX-15, FX-16 dan mock MK terkait.

## C1. File sedang dibuka oleh aplikasi lain

**Pertanyaan:** Bagaimana jika User memindai file yang sedang dibuka atau digunakan aplikasi lain?

**Rujukan:** FR-FILE-02 · **Alat:** UI · **Prioritas uji:** P1  
**Siapkan:** FX-13

**Contoh situasi:** User sedang membuka dokumen dummy di aplikasi pengolah dokumen, lalu mengunggah dokumen yang sama ke STIKSAFE.

**Langkah:** (eksperimen aman)
1. Gunakan dokumen dummy biasa (FX-13).
2. Buka dokumen menggunakan aplikasi yang sesuai.
3. Selagi dokumen tetap terbuka, coba pilih dan upload file tersebut melalui STIKSAFE.
4. Amati apakah file dapat dibaca atau sistem memberi pesan bahwa file sedang digunakan.
5. Jangan memaksa menutup aplikasi atau mengubah file sistem.

**Hasil yang diharapkan:**
- Jika sistem operasi mengizinkan file dibaca, scan dapat berjalan.
- Jika file terkunci dan tidak dapat dibaca, sistem memberi pesan yang jelas atau opsi mencoba lagi.
- Sistem tidak crash dan file asli tidak berubah.
- Backend tidak mencatat scan `completed` tanpa hasil ML valid.

**Catatan untuk developer:** File yang sedang dibuka tidak selalu terkunci. Perilaku bergantung pada sistem operasi, izin akses, dan mekanisme file locking. Penguji harus mencatat hasil aktual.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## C2. File berubah ketika proses upload/scan berlangsung

**Pertanyaan:** Apa yang terjadi jika file sumber atau data yang diterima berubah ketika proses berlangsung?

**Rujukan:** FR-FILE-04, FR-RCV-03 · **Alat:** Bantuan developer · **Prioritas uji:** P1  
**Siapkan:** MK-20

**Langkah:**
1. Gunakan file dummy yang tidak penting.
2. Developer menyiapkan simulasi ketidakcocokan hash (MK-20) atau perubahan file saat proses.
3. Jalankan scan dan amati apakah sistem menggunakan salinan yang konsisten.

**Hasil yang diharapkan:**
- Sistem menganalisis salinan file yang konsisten (SHA-256 dihitung saat upload dan dicocokkan oleh ML).
- Bila hash tidak cocok, ML mengembalikan `ML_HASH_MISMATCH` (*retryable*) dan backend mengulang dengan aman.
- Hasil tidak tertaut ke file yang berbeda dari file yang diterima backend.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## C3. File rusak atau tidak lengkap

**Pertanyaan:** Bagaimana jika file yang diunggah rusak?

**Rujukan:** FR-FMT-03, FR-ERR-02, BR-ML-02 · **Alat:** UI · **Prioritas uji:** P0  
**Siapkan:** FX-05, FX-15, MK-18

**Langkah:**
1. Upload FX-05 (terpotong) dan FX-15 (struktur cacat sebagian).
2. Gunakan MK-18 bila fixture belum tersedia.
3. Periksa hasil pada detail dan riwayat.

**Hasil yang diharapkan:**
- Bila file tidak terbaca sama sekali: scan `failed` dengan `error_code` `ML_FILE_CORRUPT`.
- Bila terbaca sebagian: hasil sesuai dokumentasi fixture dari tim ML (mis. `unknown` dengan `reason_codes` `PARSE_PARTIAL`); ML tidak memaksakan prediksi percaya diri.
- Backend mencatat kegagalan sesuai aturan dan User menerima pesan yang mudah dipahami.
- Tidak ada hasil `safe` tanpa dasar.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## C4. Format didukung, tetapi preprocessing gagal

**Pertanyaan:** Bagaimana jika format termasuk daftar dukungan, tetapi isi atau strukturnya tidak dapat diproses model?

**Rujukan:** FR-PRE-04, FR-ERR-04, BR-ML-02 · **Alat:** UI · **Prioritas uji:** P0  
**Siapkan:** FX-16, MK-19

**Langkah:**
1. Gunakan FX-16 (atau MK-19) yang disiapkan tim ML untuk memicu kegagalan preprocessing.
2. Amati status scan dari awal hingga akhir dan jumlah percobaan.

**Hasil yang diharapkan:**
- Preprocessing gagal secara terkontrol.
- ML mengembalikan `ML_PREPROCESSING_FAILED`; backend mengulang terbatas, lalu menandai `failed` dengan `error_code`.
- Sistem **tidak** menganggap file aman hanya karena analisis gagal.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## C5. File program yang sedang berjalan

**Pertanyaan:** Bagaimana jika User memilih file program yang sedang berjalan pada perangkatnya?

**Rujukan:** FR-PRE-01, BR-ML-06 · **Alat:** UI · **Prioritas uji:** P1  
**Siapkan:** FX-12

**Batasan:** Jangan menjalankan malware atau program yang tidak dikenal. Jangan menguji pemindaian memori proses, injeksi, penghentian proses, atau perubahan perilaku program. Gunakan hanya aplikasi dummy yang dibuat dan disetujui developer.

**Langkah:**
1. Developer menyediakan file aplikasi dummy yang tidak melakukan tindakan berbahaya (FX-12).
2. Aplikasi dummy dijalankan pada lingkungan testing yang disetujui.
3. Penguji mencoba mengunggah salinan file aplikasi tersebut melalui STIKSAFE.
4. Amati apakah aplikasi web memindai salinan file atau menampilkan keterbatasan akses.
5. Pastikan proses aplikasi dummy tidak dihentikan atau diubah.

**Hasil yang diharapkan:**
- STIKSAFE menganalisis file yang diunggah, bukan otomatis memeriksa semua proses yang berjalan.
- Jika file dapat dibaca, sistem memproses salinan sesuai dukungan model.
- Jika file terkunci/tidak dapat dibaca, sistem memberi error atau opsi mencoba lagi.
- STIKSAFE tidak menghentikan aplikasi dan tidak mengklaim telah memindai memori proses.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## C6. Dokumen terenkripsi atau berpassword

**Pertanyaan:** Apa hasil yang diberikan untuk dokumen yang terenkripsi sehingga isinya tidak dapat dianalisis?

**Rujukan:** FR-FMT-05, BR-ML-04 · **Alat:** UI · **Prioritas uji:** P1  
**Siapkan:** FX-10

**Langkah:**
1. Upload FX-10 (dokumen berpassword yang tidak berbahaya).
2. Amati hasil dan label.

**Hasil yang diharapkan:**
- Bila formatnya didukung: hasil `unknown` dengan `reason_codes` `ENCRYPTED_CONTENT` (bukan `safe`).
- Bila formatnya tidak didukung: `ML_UNSUPPORTED_FILE`.
- Tidak ada crash; pesan tidak menjanjikan file aman.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## C7. File arsip

**Pertanyaan:** Bagaimana sistem menangani file arsip (mis. zip)?

**Rujukan:** FR-FMT-05, FR-FILE-02 · **Alat:** UI · **Prioritas uji:** P1  
**Siapkan:** FX-11

**Langkah:**
1. Upload FX-11 (arsip dummy berisi file teks).
2. Amati respons backend dan hasil scan.

**Hasil yang diharapkan:**
- Pada versi 1 arsip tidak diekstrak: ditolak backend (`FILE_TYPE_NOT_ALLOWED`/`FILE_ARCHIVE_UNSAFE`) atau scan `failed` dengan `ML_UNSUPPORTED_FILE`.
- Tidak ada hasil `safe` untuk isi arsip; server tetap stabil.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## C8. File mendekati batas ukuran

**Pertanyaan:** Apakah file yang sangat dekat dengan batas ukuran tetap diproses dengan benar tanpa membuat sistem tersangkut?

**Rujukan:** FR-FILE-02, NFR-PERF-02 (ML) · **Alat:** UI · **Prioritas uji:** P1  
**Siapkan:** FX-08

**Langkah:**
1. Upload FX-08 (batas − 1 MB).
2. Pantau status sampai terminal; catat waktu total.
3. Sambil menunggu, jalankan satu scan kecil dari akun lain.

**Hasil yang diharapkan:**
- File diterima.
- Scan berakhir `completed` atau `failed` yang terstruktur dalam batas waktu; tidak tersangkut.
- Scan akun lain tidak ikut terhambat secara berlebihan.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

---

# BAGIAN D — HASIL MACHINE LEARNING

*Prasyarat bagian ini:* Login sebagai `User-A`. Mock MK-01 s.d. MK-08, MK-16 (atau fixture dengan label acuan dari tim ML). **Tanpa malware sungguhan.**

## D1. Hasil `safe`

**Pertanyaan:** Apakah backend menerima dan menyimpan prediksi `safe` dengan benar?

**Rujukan:** FR-ML-02, BR-ML-03 · **Alat:** UI · **Prioritas uji:** P0  
**Siapkan:** FX-01, MK-01

**Langkah:** Gunakan fixture uji dengan label acuan dari tim ML (atau MK-01), jalankan scan, lalu periksa detail dan riwayat.

**Hasil yang diharapkan:**
- Label ditampilkan dengan benar.
- Confidence dan versi model ditampilkan/disimpan jika tersedia.
- UI tidak menjanjikan bahwa file dijamin aman.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## D2. Hasil `malicious`

**Pertanyaan:** Apakah hasil `malicious` diterima dan ditampilkan secara konsisten?

**Rujukan:** FR-ML-02 · **Alat:** UI · **Prioritas uji:** P0  
**Siapkan:** MK-02

**Langkah:** Gunakan mock response atau fixture integrasi dari tim ML (MK-02), **bukan** malware sungguhan.

**Hasil yang diharapkan:**
- Label `malicious` ditampilkan dengan benar.
- Backend menyimpan hasil dan metadata (`model_version`).
- User mendapat peringatan yang jelas.
- File tidak dijalankan oleh sistem.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## D3. Hasil `suspicious`

**Pertanyaan:** Bagaimana sistem menampilkan hasil yang belum cukup meyakinkan?

**Rujukan:** FR-ML-02, BR-ML-04 · **Alat:** UI · **Prioritas uji:** P0  
**Siapkan:** MK-03

**Langkah:** Gunakan mock response dengan label `suspicious` (MK-03).

**Hasil yang diharapkan:**
- Label tidak disamakan dengan `safe`.
- User mendapat penjelasan untuk berhati-hati.
- Metadata valid disimpan.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## D4. Hasil `unknown`

**Pertanyaan:** Apa yang terjadi jika model tidak dapat menentukan klasifikasi?

**Rujukan:** FR-ML-02, BR-ML-04 · **Alat:** UI · **Prioritas uji:** P0  
**Siapkan:** MK-04

**Langkah:**
1. Gunakan mock response dengan label `unknown` (MK-04).
2. Bandingkan tampilannya dengan scan yang berstatus `failed`.

**Hasil yang diharapkan:**
- Sistem menampilkan hasil tidak diketahui; scan berstatus `completed`.
- Label tidak diubah menjadi `safe`.
- User diberi tahu bahwa model belum dapat menentukan hasil.
- Tampilan `unknown` (hasil sah) jelas berbeda dari `failed` (proses gagal, tanpa label).

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## D5. Confidence tidak tersedia

**Pertanyaan:** Bagaimana jika ML memberikan label tanpa confidence score?

**Rujukan:** FR-INF-05, FR-ML-02 · **Alat:** UI · **Prioritas uji:** P0  
**Siapkan:** MK-05

**Langkah:** Developer mengirim mock response tanpa nilai confidence (MK-05).

**Hasil yang diharapkan:**
- Backend tidak crash.
- UI tidak menampilkan confidence palsu (tidak menampilkan angka 0 atau nilai bawaan).
- Informasi lain yang valid tetap ditampilkan.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## D6. Respons ML tidak valid

**Pertanyaan:** Apa yang terjadi jika ML mengirim label di luar daftar yang diizinkan, JSON rusak, confidence di luar rentang, atau hash yang tidak cocok?

**Rujukan:** FR-ML-02, BR-08 · **Alat:** UI + Admin · **Prioritas uji:** P0  
**Siapkan:** MK-06, MK-07, MK-08, MK-16

**Langkah:**
1. Developer mengirim berturut-turut: MK-06 (label tidak dikenal), MK-07 (JSON rusak), MK-16 (confidence di luar 0–1), MK-08 (`sha256` tidak cocok).
2. Untuk tiap kasus catat status akhir scan dan `error_code` (lihat detail Admin).
3. Periksa pesan yang diterima User.

**Hasil yang diharapkan:**
- Backend menolak respons tidak valid; attempt dicatat gagal (`ML_INVALID_RESPONSE`), diulang terbatas, lalu scan `failed`.
- `sha256` tidak cocok menghasilkan `ML_INTEGRITY_MISMATCH` tanpa diulang dan menimbulkan peringatan keamanan.
- Scan **tidak** ditandai `completed` dengan hasil yang tidak sah.
- Error tercatat tanpa membocorkan informasi sensitif.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## D7. Disclaimer hasil

**Pertanyaan:** Apakah setiap tampilan hasil menyatakan bahwa ini prediksi model, bukan jaminan keamanan mutlak?

**Rujukan:** BR-18, BR-ML-03 · **Alat:** UI · **Prioritas uji:** P0  
**Siapkan:** MK-01 s.d. MK-04

**Langkah:**
1. Untuk hasil `safe`, `suspicious`, `malicious`, dan `unknown`, periksa layar detail, riwayat, dan notifikasi (bila ada).
2. Cari kata atau kalimat yang menjanjikan keamanan mutlak (mis. "pasti aman").

**Hasil yang diharapkan:**
- Setiap tampilan hasil menyertakan informasi bahwa hasil adalah prediksi model.
- Tidak ada teks yang menjanjikan file dijamin aman.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

---

# BAGIAN E — PROSES SCAN DAN KONEKSI

*Prasyarat bagian ini:* Login sebagai `User-A`. Simulasi dari developer (MK-09 s.d. MK-15). Catat nilai timeout dan jumlah percobaan ulang aktual (4.1). **Admin-1** diperlukan untuk memeriksa attempt.

## E1. Scan berlangsung lama

**Pertanyaan:** Bagaimana jika analisis membutuhkan waktu lebih lama dari biasanya?

**Rujukan:** FR-SCN-03, FR-SCN-15 · **Alat:** UI + Admin · **Prioritas uji:** P0  
**Siapkan:** FX-01, MK-09

**Langkah:** Developer mensimulasikan keterlambatan respons ML (MK-09), lalu Penguji menjalankan scan dummy dan memantau sampai status akhir.

**Hasil yang diharapkan:**
- Status menunjukkan proses berjalan (`queued`/`processing`) dan hanya bergerak maju.
- User tidak menerima hasil palsu; tidak ada persentase progres yang dikarang.
- UI tidak membeku dan scan lain tetap dapat dibuka.
- Timeout ditangani sesuai aturan backend: percobaan ulang lalu `failed` bila tetap lambat (tidak `completed`).

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## E2. ML Service tidak tersedia

**Pertanyaan:** Apa yang terjadi jika ML Service sedang mati atau tidak dapat diakses?

**Rujukan:** FR-SCN-06, BR-08 · **Alat:** UI + Admin · **Prioritas uji:** P0  
**Siapkan:** FX-01, MK-10

**Langkah:**
1. Dengan bantuan developer, nonaktifkan ML Service di lingkungan testing (MK-10).
2. Jalankan scan dummy dan pantau status.
3. Aktifkan kembali ML Service, lalu jalankan scan baru.

**Hasil yang diharapkan:**
- Backend tidak crash.
- Scan tidak dinyatakan selesai; ada percobaan ulang dengan jeda, lalu `failed` dengan `error_code` yang sesuai bila ML tetap tidak tersedia.
- User mendapat pesan gangguan layanan yang jelas, bukan hasil `safe`.
- Setelah ML pulih, scan baru berhasil normal.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## E3. Koneksi terputus saat scan

**Pertanyaan:** Bagaimana jika koneksi Backend–ML terputus ketika scan berlangsung?

**Rujukan:** FR-SCN-07, FR-SCN-06 · **Alat:** Bantuan developer + Admin · **Prioritas uji:** P0  
**Siapkan:** FX-01, MK-13

**Langkah:**
1. Developer mensimulasikan gangguan jaringan pada lingkungan testing (MK-13).
2. Jalankan satu scan dan pantau.
3. Buka detail scan pada Admin dan lihat daftar attempt.

**Hasil yang diharapkan:**
- Backend menangani kegagalan koneksi.
- Scan yang belum memiliki hasil valid tidak dianggap selesai.
- Retry tercatat sebagai attempt baru pada scan yang **sama**; tidak membuat record riwayat ganda yang tidak semestinya.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## E4. Tombol Scan ditekan berkali-kali

**Pertanyaan:** Apa yang terjadi jika User menekan tombol Scan berulang kali?

**Rujukan:** FR-FILE-07, FR-SCN-01 · **Alat:** UI · **Prioritas uji:** P0  
**Siapkan:** FX-01

**Langkah:** Pilih satu file dummy (FX-01) dan tekan tombol Scan beberapa kali dalam waktu berdekatan.

**Hasil yang diharapkan:**
- Request ganda yang tidak disengaja dicegah atau ditangani konsisten (tidak ada duplikat tak terduga).
- Hasil tidak tertukar.
- Setiap scan yang terbentuk memiliki identitas (`scan_id`) yang jelas.
- UI memberi umpan balik bahwa proses sedang berlangsung (mis. tombol dinonaktifkan).

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## E5. Membatalkan scan

**Pertanyaan:** Apakah User dapat membatalkan scan, dan apa yang terjadi bila hasil ML datang setelah pembatalan?

**Rujukan:** FR-SCN-10 · **Alat:** UI / API · **Prioritas uji:** P0  
**Siapkan:** FX-01, MK-09, MK-14

**Langkah:**
1. Mulai scan dengan MK-09 (lambat) agar sempat dibatalkan.
2. Batalkan scan saat masih `queued`; ulangi pada scan lain saat sudah `processing`.
3. Biarkan developer mengirim hasil ML terlambat (MK-14).
4. Periksa status akhir dan riwayat.

**Hasil yang diharapkan:**
- Scan `queued` langsung menjadi `cancelled`.
- Pembatalan saat `processing` bersifat *best effort*.
- Hasil ML yang datang terlambat **tidak** mengubah status `cancelled` menjadi `completed`.
- Riwayat menampilkan status yang benar.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## E6. ML kelebihan beban

**Pertanyaan:** Bagaimana jika ML menolak sementara karena penuh (`ML_OVERLOADED`)?

**Rujukan:** FR-SCN-06 dan ML 7.6 · **Alat:** UI + Admin · **Prioritas uji:** P1  
**Siapkan:** FX-01, MK-11

**Langkah:**
1. Developer mensimulasikan ML yang membalas `429` dengan `Retry-After` (MK-11) pada beberapa percobaan pertama, lalu normal.
2. Jalankan scan dan amati statusnya serta daftar attempt.

**Hasil yang diharapkan:**
- Backend mengulang setelah jeda yang wajar; scan tidak langsung `failed`.
- Bila ML pulih, scan berakhir `completed` dengan hasil yang benar.
- Tidak ada hasil palsu selama percobaan ulang.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## E7. Scan tersangkut karena worker mati

**Pertanyaan:** Apakah scan yang sedang diproses dapat pulih bila proses worker berhenti mendadak?

**Rujukan:** FR-SCN-09 · **Alat:** Bantuan developer + Admin · **Prioritas uji:** P0  
**Siapkan:** FX-01, MK-09

**Langkah:**
1. Jalankan scan dengan MK-09 agar status `processing` berlangsung.
2. Developer menghentikan worker secara paksa di tengah proses, lalu menyalakannya kembali.
3. Pantau scan sampai batas waktu yang dicatat (4.1).

**Hasil yang diharapkan:**
- Scan tidak tersangkut selamanya pada `processing`.
- Watchdog memulihkan: attempt ditandai gagal lalu dijadwal ulang atau scan berakhir dengan status terminal yang benar.
- Tidak ada hasil palsu dan tidak ada scan ganda.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## E8. Jumlah percobaan ulang otomatis

**Pertanyaan:** Berapa kali sistem mengulang sebelum menyerah, dan apakah setiap percobaan tercatat?

**Rujukan:** FR-SCN-06, FR-SCN-07 · **Alat:** Admin · **Prioritas uji:** P0  
**Siapkan:** FX-01, MK-12

**Langkah:**
1. Developer mensimulasikan error `5xx` berulang dari ML (MK-12).
2. Jalankan scan hingga status akhir.
3. Buka detail scan pada Admin dan hitung jumlah attempt serta kode error tiap attempt.

**Hasil yang diharapkan:**
- Percobaan tidak melebihi batas yang dicatat di 4.1 (default usulan: 3).
- Setiap attempt tercatat dengan nomor, waktu, dan `error_code`; attempt lama tidak hilang.
- Setelah batas habis, scan `failed` dengan `error_code`; tidak pernah `safe`.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## E9. Callback palsu atau ganda

**Pertanyaan:** Apakah hasil dari pihak yang tidak sah atau callback yang diulang ditolak? (hanya bila Mode callback dipakai)

**Rujukan:** FR-ML-05, ML 7.8 · **Alat:** API + Bantuan developer · **Prioritas uji:** P1  
**Siapkan:** MK-15

**Langkah:**
1. Pada staging Mode callback, developer mengirim callback tanpa tanda tangan, dengan tanda tangan salah, dan mengulang callback yang sama (MK-15).
2. Amati status scan dan audit log.

**Hasil yang diharapkan:**
- Callback tanpa/salah tanda tangan ditolak (401) dan tercatat (`ml.callback.rejected`).
- Callback ganda diproses sekali; hasil tidak berubah.
- Tulis **N/A** bila integrasi memakai Mode sinkron.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

---

# BAGIAN F — RIWAYAT PEMINDAIAN, DASHBOARD USER, DAN NOTIFIKASI

*Prasyarat bagian ini:* Akun `User-A` dan `User-B`. FX-14 (±30 file kecil) untuk uji daftar. Hasil ML dari mock.

## F1. Hasil scan masuk ke riwayat

**Pertanyaan:** Apakah scan yang selesai muncul pada riwayat User yang benar?

**Rujukan:** FR-HIS-01, BR-03 · **Alat:** UI · **Prioritas uji:** P0  
**Siapkan:** FX-01, MK-01

**Langkah:**
1. Login sebagai User A.
2. Scan file dummy.
3. Tunggu hingga selesai.
4. Buka Riwayat.
5. Login sebagai User B dan buka Riwayat.

**Hasil yang diharapkan:**
- Scan muncul di riwayat User A.
- Nama file, waktu, label, dan status sesuai.
- Data tidak muncul pada akun User lain.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## F2. Scan gagal masuk riwayat

**Pertanyaan:** Apakah scan yang gagal tercatat dengan status yang benar?

**Rujukan:** FR-SCN-07, BR-08 · **Alat:** UI · **Prioritas uji:** P0  
**Siapkan:** MK-17, MK-18

**Langkah:** Jalankan scan menggunakan fixture/mock yang memicu error terkontrol (MK-18 atau MK-17), lalu periksa riwayat dan detail.

**Hasil yang diharapkan:**
- Kegagalan tercatat sesuai aturan backend (`failed` dengan `error_code`).
- Tidak ada label hasil yang dibuat-buat.
- Detail error aman dapat dilihat sesuai hak akses (User melihat pesan aman; Admin melihat rincian attempt).

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## F3. Membuka detail riwayat

**Pertanyaan:** Apakah User dapat membuka detail scan lama dan data tidak tertukar?

**Rujukan:** FR-HIS-02 · **Alat:** UI / API · **Prioritas uji:** P0  
**Siapkan:** Beberapa scan dengan hasil berbeda (MK-01 s.d. MK-04).

**Langkah:** Buka Riwayat, pilih scan yang selesai, lalu buka detailnya; bandingkan dengan respons `GET /scans/{id}` di Postman.

**Hasil yang diharapkan:**
- Detail sesuai scan yang dipilih.
- Label, confidence, versi model, dan waktu tidak tertukar.
- Tidak ada `stored_path` pada respons; `file.available` sesuai kondisi.
- Data User lain tidak dapat diakses.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## F4. Filter, pencarian, pengurutan, dan halaman

**Pertanyaan:** Apakah User dapat menemukan scan tertentu dengan benar saat datanya banyak?

**Rujukan:** FR-HIS-03, FR-HIS-04 · **Alat:** UI / API · **Prioritas uji:** P0  
**Siapkan:** FX-14

**Langkah:**
1. Buat ±30 scan dummy (FX-14) dengan hasil beragam.
2. Gunakan filter status dan hasil, filter tanggal, dan pencarian nama file; gunakan juga pengurutan.
3. Telusuri semua halaman; di Postman coba `per_page` yang sangat besar.
4. Cari kata yang tidak ada.

**Hasil yang diharapkan:**
- Hasil sesuai kata kunci/filter, termasuk kombinasi beberapa filter.
- Tidak ada data duplikat atau hilang antar halaman; total sesuai.
- Data tidak bercampur dengan akun lain.
- `per_page` dibatasi pada batas maksimum (100) dan *empty state* tampil jika tidak ada hasil.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## F5. Menghapus item riwayat

**Pertanyaan:** Apa yang terjadi ketika User menghapus scan dari riwayatnya?

**Rujukan:** FR-HIS-05, BR-12 · **Alat:** UI / API + Admin · **Prioritas uji:** P0  
**Siapkan:** Scan milik User-A.

**Langkah:**
1. Hapus satu scan dari riwayat `User-A`.
2. Coba membuka scan itu lagi lewat ID.
3. Periksa riwayat `User-B`.
4. Minta Admin memeriksa audit log; minta developer memastikan file sudah dihapus dari storage.

**Hasil yang diharapkan:**
- Scan hilang dari daftar User; akses lewat ID memberi 404.
- Riwayat User lain tidak berubah.
- File fisik dipurge sesuai kebijakan; audit log tetap ada.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## F6. File kedaluwarsa karena retensi

**Pertanyaan:** Bagaimana tampilan scan lama yang file aslinya sudah dihapus otomatis?

**Rujukan:** FR-FILE-08, ML 8.6 · **Alat:** UI / API + Bantuan developer · **Prioritas uji:** P1  
**Siapkan:** Bantuan developer.

**Langkah:**
1. Minta developer mempercepat retensi di staging atau menjalankan perintah purge.
2. Buka scan lama tersebut dan coba aksi yang memerlukan file (mis. unduh bila tersedia).

**Hasil yang diharapkan:**
- Hasil scan tetap tampil; `file.available` bernilai `false`.
- Aksi yang memerlukan file ditolak 410 `SCAN_FILE_EXPIRED` dengan pesan jelas.
- Tulis **N/A** bila retensi otomatis belum dirilis.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## F7. Dashboard User

**Pertanyaan:** Apakah angka pada dashboard User sama dengan data riwayat?

**Rujukan:** FR-DSH-01, FR-DSH-05 · **Alat:** UI · **Prioritas uji:** P1  
**Siapkan:** Scan dengan MK-01 s.d. MK-04 dan satu kegagalan.

**Langkah:**
1. Siapkan beberapa scan dengan hasil `safe`, `suspicious`, `malicious`, `unknown`, dan `failed`.
2. Hitung manual dari riwayat, lalu bandingkan dengan dashboard.

**Hasil yang diharapkan:**
- Jumlah per hasil dan per status sama dengan riwayat.
- Scan `failed`/`cancelled` **tidak** dihitung sebagai `safe` atau `malicious`.
- Aktivitas terakhir sesuai; tidak ada hitungan ganda.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## F8. Notifikasi

**Pertanyaan:** Apakah User menerima pemberitahuan ketika scan selesai atau gagal?

**Rujukan:** FR-NTF-01, FR-NTF-02, BR-19 · **Alat:** UI / API · **Prioritas uji:** P1  
**Siapkan:** MK-01, MK-18

**Langkah:**
1. Jalankan satu scan yang berhasil dan satu yang gagal (termasuk satu yang melalui percobaan ulang).
2. Buka daftar notifikasi, periksa jumlah belum dibaca, tandai dibaca, dan tandai semua dibaca.
3. Login sebagai User B dan periksa notifikasinya.

**Hasil yang diharapkan:**
- Ada satu notifikasi per scan yang mencapai status terminal (tidak berulang akibat retry).
- Jumlah belum dibaca dan penandaan dibaca benar.
- Notifikasi hanya terlihat oleh pemiliknya.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## F9. Umpan balik hasil

**Pertanyaan:** Apakah User dapat melaporkan hasil yang dianggap keliru (false positive/negative)?

**Rujukan:** FR-HIS-07 · **Alat:** UI / API · **Prioritas uji:** P1  
**Siapkan:** Satu scan selesai.

**Langkah:**
1. Pada scan milik sendiri, kirim umpan balik "hasil keliru" dengan komentar.
2. Kirim umpan balik kedua pada scan yang sama.
3. Coba mengirim umpan balik pada scan milik `User-B`.

**Hasil yang diharapkan:**
- Umpan balik tersimpan dan dapat ditinjau Admin.
- Satu umpan balik aktif per scan per user.
- Pada scan milik orang lain: 404.
- Tulis **N/A** bila fitur belum dirilis.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

---

# BAGIAN G — ADMIN DAN MONITORING

*Prasyarat bagian ini:* Login sebagai `Admin-1` (dan `User-A` sebagai pembanding). Data scan dari Bagian B–F sudah ada.

## G1. Statistik dashboard Admin

**Pertanyaan:** Apakah statistik dashboard sesuai dengan data scan?

**Rujukan:** FR-DSH-02, FR-DSH-03 · **Alat:** UI + Admin · **Prioritas uji:** P1

**Langkah:** Login sebagai Admin, buka dashboard, lalu bandingkan jumlah scan/status/kategori dengan data testing (daftar seluruh scan Admin).

**Hasil yang diharapkan:**
- Statistik sesuai data backend.
- Tidak ada hitungan ganda; `failed`/`cancelled` tidak masuk hitungan `safe`/`malicious`.
- Informasi diperbarui sesuai mekanisme refresh/cache (±1 menit).

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## G2. Status ML Service

**Pertanyaan:** Apakah Admin dapat mengetahui apakah ML Service tersedia?

**Rujukan:** FR-ML-06, FR-OPS-01 · **Alat:** UI / API · **Prioritas uji:** P1  
**Siapkan:** MK-10

**Langkah:**
1. Buka bagian monitoring/status ML (`/admin/ml-health`).
2. Bandingkan dengan keadaan sebenarnya, termasuk saat developer mematikan ML (MK-10).
3. Periksa isi respons.

**Hasil yang diharapkan:**
- Status layanan akurat (tersedia/tidak) dan berubah mengikuti keadaan.
- Versi model dan status circuit breaker ditampilkan jika tersedia.
- Informasi internal sensitif (token, URL internal, path) tidak bocor.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## G3. Audit log

**Pertanyaan:** Apakah aktivitas penting tercatat dalam audit log tanpa membocorkan rahasia?

**Rujukan:** FR-AUD-01, FR-AUD-03 · **Alat:** UI / API · **Prioritas uji:** P0

**Langkah:**
1. Lakukan aktivitas testing: login berhasil dan gagal, upload, scan, penangguhan akun, perubahan role, perubahan pengaturan.
2. Periksa audit log pada Admin dan cari entri setiap aktivitas.
3. Periksa isi entri dan cari tombol/aksi ubah atau hapus.

**Hasil yang diharapkan:**
- Aktivitas penting tercatat sesuai kebijakan; aktor, aksi, target, waktu (UTC), dan `request_id` benar.
- Password, token, dan isi file tidak masuk log.
- Tidak ada cara mengubah atau menghapus entri audit dari aplikasi.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## G4. Daftar pengguna oleh Admin

**Pertanyaan:** Apakah Admin dapat mencari dan memfilter pengguna tanpa melihat data rahasia?

**Rujukan:** FR-USR-04 · **Alat:** UI / API · **Prioritas uji:** P0

**Langkah:**
1. Buka daftar pengguna; cari berdasarkan nama/email; filter status dan role.
2. Periksa respons pada Postman.

**Hasil yang diharapkan:**
- Pencarian dan filter sesuai; pagination benar.
- Tidak ada hash password, rahasia 2FA, atau token pada respons maupun tampilan.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## G5. Admin mengulang scan yang gagal

**Pertanyaan:** Bagaimana Admin mengulang scan gagal, dan apakah riwayat percobaan lama tetap utuh?

**Rujukan:** FR-SCN-11, BR-10 · **Alat:** UI / API · **Prioritas uji:** P0  
**Siapkan:** MK-10, MK-17

**Langkah:**
1. Siapkan satu scan `failed` yang *retryable* (mis. dari MK-10) dan satu yang tidak *retryable* (mis. MK-17).
2. Sebagai Admin, jalankan Retry pada keduanya; klik Retry dua kali berturut-turut pada yang pertama.
3. Buka detail dan lihat daftar attempt serta audit log.

**Hasil yang diharapkan:**
- Scan *retryable* membuat **satu** attempt baru (`manual_retry`); attempt lama tetap tersimpan.
- Scan tidak *retryable* ditolak 409 `SCAN_NOT_RETRYABLE`.
- Tindakan tercatat pada audit log (`scan.retried`).

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## G6. Admin melihat seluruh scan

**Pertanyaan:** Apa yang boleh dan tidak boleh dilihat Admin pada scan milik pengguna?

**Rujukan:** FR-HIS-08, BR-13 · **Alat:** UI / API · **Prioritas uji:** P0

**Langkah:**
1. Buka daftar seluruh scan dan detail milik `User-A`.
2. Cari cara melihat atau mengunduh isi file asli.
3. Periksa audit log setelahnya.

**Hasil yang diharapkan:**
- Admin dapat melihat metadata dan hasil.
- Isi file asli tidak dapat diakses oleh Admin (kebijakan default).
- Akses lintas pengguna tercatat pada audit log.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

## G7. Pengaturan sistem

**Pertanyaan:** Apakah pengaturan dapat diubah dengan aman dan berlaku tanpa deploy ulang?

**Rujukan:** FR-SET-01, FR-SET-03, BR-17 · **Alat:** UI / API · **Prioritas uji:** P1

**Langkah:**
1. Ubah satu pengaturan (mis. batas scan aktif) menjadi nilai tidak valid, lalu nilai valid.
2. Uji efeknya dengan upload sebagai User.
3. Periksa audit log untuk perubahan tersebut.

**Hasil yang diharapkan:**
- Nilai tidak valid ditolak dengan pesan yang jelas.
- Nilai valid berlaku dalam ±1 menit tanpa deploy.
- Audit mencatat nilai lama dan baru; nilai rahasia dimasker.
- Tulis **N/A** bila fitur belum dirilis.

**Hasil aktual:** ........................................................  
**Status:** PASS / FAIL / BLOCKED / N/A  
**Bukti:** ........................................................  
**Bug ID/Catatan:** ............................................

---

# BAGIAN H — EKSPERIMEN TERBUKA (PENGUJI BERPIKIR KRITIS)

*Prasyarat bagian ini:* Untuk setiap soal, Penguji membuat hipotesis, menentukan eksperimen aman, lalu mencatat kesimpulan. Boleh menambah akun dan fixture yang aman.

## H1. Bagaimana jika User mengunggah dua file sekaligus?

**Rujukan petunjuk:** FR-FILE-01 · **Alat:** UI / API · **Prioritas uji:** P1

**Petunjuk:** Perhatikan apakah satu file per permintaan ditegakkan dan apakah ada pesan yang jelas.

**Hipotesis:** ........................................................  
**Cara pengujian aman:** ............................................  
**Hasil yang diharapkan:** ......................................  
**Hasil aktual:** ......................................  
**Kesimpulan:** ......................................  
**Status:** PASS / FAIL / BLOCKED / N/A

## H2. Bagaimana jika User menutup browser ketika scan berlangsung?

**Rujukan petunjuk:** FR-SCN-03 · **Alat:** UI · **Prioritas uji:** P1

**Petunjuk:** Perhatikan nasib scan dan hasilnya saat User kembali.

**Hipotesis:** ........................................................  
**Cara pengujian aman:** ............................................  
**Hasil yang diharapkan:** ......................................  
**Hasil aktual:** ......................................  
**Kesimpulan:** ......................................  
**Status:** PASS / FAIL / BLOCKED / N/A

## H3. Bagaimana jika User login dari dua perangkat?

**Rujukan petunjuk:** FR-AUTH-03, FR-AUTH-07 · **Alat:** UI / API · **Prioritas uji:** P1

**Petunjuk:** Perhatikan apakah logout/ganti password di satu perangkat memengaruhi perangkat lain.

**Hipotesis:** ........................................................  
**Cara pengujian aman:** ............................................  
**Hasil yang diharapkan:** ......................................  
**Hasil aktual:** ......................................  
**Kesimpulan:** ......................................  
**Status:** PASS / FAIL / BLOCKED / N/A

## H4. Bagaimana jika Admin menonaktifkan layanan scan ketika masih ada antrean?

**Rujukan petunjuk:** FR-SET-02 · **Alat:** UI + Admin · **Prioritas uji:** P1

**Petunjuk:** Perhatikan nasib scan yang sudah antre dan perilaku upload baru.

**Hipotesis:** ........................................................  
**Cara pengujian aman:** ............................................  
**Hasil yang diharapkan:** ......................................  
**Hasil aktual:** ......................................  
**Kesimpulan:** ......................................  
**Status:** PASS / FAIL / BLOCKED / N/A

## H5. Bagaimana jika dua versi model memberikan hasil berbeda?

**Rujukan petunjuk:** FR-MDL-01, BR-ML-08 · **Alat:** Mock · **Prioritas uji:** P1

**Petunjuk:** Perhatikan apakah `model_version` tersimpan dan riwayat lama tetap utuh.

**Hipotesis:** ........................................................  
**Cara pengujian aman:** Gunakan mock response atau dataset evaluasi dari tim ML.  
**Hasil yang diharapkan:** ......................................  
**Hasil aktual:** ......................................  
**Kesimpulan:** ......................................  
**Status:** PASS / FAIL / BLOCKED / N/A

## H6. Bagaimana jika dua User mengunggah file yang sama persis?

**Rujukan petunjuk:** BR-03 · **Alat:** UI / API · **Prioritas uji:** P1

**Petunjuk:** Perhatikan apakah ada informasi yang bocor di antara kedua akun.

**Hipotesis:** ........................................................  
**Cara pengujian aman:** ............................................  
**Hasil yang diharapkan:** ......................................  
**Hasil aktual:** ......................................  
**Kesimpulan:** ......................................  
**Status:** PASS / FAIL / BLOCKED / N/A

## H7. Bagaimana tampilan waktu pada zona waktu berbeda?

**Rujukan petunjuk:** AS-06 (Backend), 10.1 · **Alat:** UI · **Prioritas uji:** P1

**Petunjuk:** Waktu pada sistem disimpan dalam UTC; perhatikan konsistensi tampilan antara daftar, detail, dan notifikasi.

**Hipotesis:** ........................................................  
**Cara pengujian aman:** ............................................  
**Hasil yang diharapkan:** ......................................  
**Hasil aktual:** ......................................  
**Kesimpulan:** ......................................  
**Status:** PASS / FAIL / BLOCKED / N/A

## H8. Buat satu skenario edge case baru

**Rujukan:** bebas · **Alat:** bebas (aman) · **Prioritas uji:** P1

**Skenario:** ........................................................  
**Mengapa penting:** ............................................  
**Hipotesis:** ........................................................  

**Langkah eksperimen aman:**
1. ........................................................
2. ........................................................
3. ........................................................

**Hasil yang diharapkan:** ......................................  
**Hasil aktual:** ......................................  
**Kesimpulan:** ......................................  
**Status:** PASS / FAIL / BLOCKED / N/A

---

# BAGIAN I — LEMBAR LAPORAN BUG

| Informasi | Isian |
|---|---|
| Bug ID | |
| Judul bug | |
| Tanggal dan waktu | |
| Nama Penguji | |
| Soal terkait (ID) | |
| Role akun | User / Admin |
| Lingkungan | Development / Staging |
| Versi aplikasi (commit) dan `model_version` | |
| Modul | Login / Upload / Scan / ML / Riwayat / Admin / Lainnya |
| Prioritas | Critical / High / Medium / Low |
| Status bug | Open / In Progress / Fixed / Retest / Closed |
| Hasil yang diharapkan | |
| Hasil aktual | |
| Langkah reproduksi | |
| `request_id` / ID scan | |
| Bukti pendukung | |
| Catatan tambahan | |

### Contoh laporan bug (ilustrasi)

**Bug ID:** BUG-SCAN-001  
**Judul:** File yang sedang digunakan tidak menghasilkan pesan error yang jelas  
**Soal terkait:** C1  
**Modul:** Upload / Scan  
**Prioritas:** Medium  
**Langkah reproduksi:**
1. Buka dokumen dummy menggunakan aplikasi pengolah dokumen.
2. Upload dokumen tersebut melalui STIKSAFE.
3. Amati pesan yang ditampilkan.

**Hasil yang diharapkan:** Jika file tidak dapat dibaca, sistem menampilkan pesan jelas dan tidak crash.

**Hasil aktual:** Diisi berdasarkan pengujian nyata.

Catatan: contoh ini bukan klaim bahwa bug tersebut benar-benar ditemukan.

---

# BAGIAN J — REKAP HASIL PENGUJIAN

### J1. Rekap per modul

| No. | Bagian | Modul | Total soal | PASS | FAIL | BLOCKED | N/A |
|---|---|---|---:|---:|---:|---:|---:|
| 1 | A | Akun dan hak akses | 13 | | | | |
| 2 | B | Upload file | 11 | | | | |
| 3 | C | Kondisi file khusus | 8 | | | | |
| 4 | D | Hasil Machine Learning | 7 | | | | |
| 5 | E | Proses scan dan koneksi | 9 | | | | |
| 6 | F | Riwayat pemindaian | 9 | | | | |
| 7 | G | Admin dan monitoring | 7 | | | | |
| 8 | H | Eksperimen terbuka | 8 | | | | |
| | | **Total** | **72** | | | | |

### J2. Rekap per prioritas uji

| Prioritas uji | Total soal | PASS | FAIL | BLOCKED | N/A |
|---|---:|---:|---:|---:|---:|
| P0 | 46 | | | | |
| P1 | 26 | | | | |

### J3. Rekap bug

| Prioritas bug | Ditemukan | Sudah Closed | Masih terbuka |
|---|---:|---:|---:|
| Critical | | | |
| High | | | |
| Medium | | | |
| Low | | | |

### J4. Kesimpulan Pengujian

**Apakah aplikasi layak dilanjutkan?**
- [ ] Ya, seluruh skenario penting lulus.
- [ ] Ya, dengan catatan perbaikan minor.
- [ ] Belum, masih ada bug penting.
- [ ] Belum dapat disimpulkan karena pengujian terblokir.

**Temuan paling penting:**

........................................................................  
........................................................................  
........................................................................

**Rekomendasi Penguji:**

........................................................................  
........................................................................  
........................................................................

**Nama Penguji:** ......................................................  
**Tanggal:** ...........................................................  
**Paraf:** .............................................................

**Direview oleh (Head Project):** ......................................  
**Tanggal review:** ....................................................

---

## 6. Kriteria Kelulusan

### 6.1 Syarat Lulus Pengujian Versi Pertama

STIKSAFE-67 dinyatakan lolos pengujian versi pertama apabila:

1. Login, logout, reset password, dan pembatasan role bekerja (A1–A10, A12).
2. User **tidak dapat** mengakses data User lain atau fitur Admin (A7, A11, F1, F5). Pelanggaran dihitung bug **Critical**.
3. Upload memvalidasi file kosong, format, ukuran, isi, dan nama (B2–B6), serta menegakkan kuota dan idempotensi (B7, B9).
4. Alur scan berkomunikasi dengan ML Service dan memproses hasilnya (B1, D1–D4).
5. Label `safe`, `suspicious`, `malicious`, dan `unknown` ditangani benar, dan respons ML tidak valid ditolak (D1–D6).
6. Kegagalan ML **tidak pernah** diubah menjadi hasil `safe` (C3, C4, D6, E2, E3, E8).
7. Riwayat menyimpan hasil dan status milik User yang benar (F1–F4).
8. Timeout, gangguan koneksi, dan worker mati ditangani tanpa hasil palsu dan tanpa scan tersangkut (E1–E8).
9. Informasi monitoring dan audit Admin akurat sesuai fitur yang tersedia (G1–G6).
10. Setiap tampilan hasil menyertakan keterangan bahwa hasil adalah prediksi model (D7).
11. Tidak ada bug Critical atau High yang belum diselesaikan sebelum rilis yang diuji.

### 6.2 Aturan Penghitungan (Usulan, DEC-QA-04)

| Kategori | Syarat |
|---|---|
| Soal **P0** | Seluruhnya PASS. Soal P0 berstatus BLOCKED harus diselesaikan penyebabnya dan dijalankan ulang sebelum keputusan akhir |
| Soal **P1** | PASS, atau FAIL dengan bug Medium/Low yang terdokumentasi, atau N/A dengan alasan (fitur belum dirilis) |
| Bug | Tidak ada Critical/High yang masih terbuka |
| BLOCKED | **Tidak pernah** dihitung sebagai PASS |
| Kesimpulan | Hanya mempertimbangkan soal yang benar-benar dijalankan dan memiliki bukti |

### 6.3 Putaran Regresi

Setelah perbaikan bug Critical/High, soal yang terkait diulang bersama seluruh soal **P0** pada Bagian A, B, D, dan E. Regresi penuh dilakukan sebelum rilis beta.

---

## 7. Rencana Pengujian Bertahap

> Estimasi kasar mengikuti sprint backend (2 minggu); pelaksana pengujian: Dina. Dikonfirmasi ulang setelah fitur dan fixture tersedia.

| Tahap | Sprint | Bagian soal | Pekerjaan Dina | Dukungan developer | Kriteria keluar |
|---|:--:|---|---|---|---|
| **0. Penyiapan** | 0 | — | Review PRD Backend dan ML, siapkan template bug dan lembar, tentukan alat | Daftar akun uji, daftar fixture dan mock, lingkungan staging | DEC-QA prioritas diputuskan; daftar FX/MK disepakati |
| **1. Uji Fondasi** | 1–2 | A | Jalankan Bagian A, lapor bug | Akun uji, OpenAPI, koleksi Postman | Seluruh soal A P0 berstatus PASS atau bug tercatat |
| **2. Uji Upload dan Scan** | 3–4 | B, C, D, E | Jalankan dengan Fake ML/mode stub, lalu ML nyata di staging; uji fixture dan mock | Fixture FX, mock MK, kontrol simulasi (ML mati, worker mati, timeout) | Alur upload → hasil teruji; bug Critical/High ditangani |
| **3. Uji Riwayat dan Admin** | 5 | F, G | Jalankan Bagian F dan G | Data seeder FX-14, akses Admin | Riwayat dan Admin lulus |
| **4. Uji Operasional dan P1** | 6 | Soal P1, H | Jalankan soal P1 yang sudah dirilis dan eksperimen terbuka | Fitur P1 siap; konfigurasi staging dapat diubah | Soal P1 berstatus jelas (PASS/FAIL/N/A) |
| **5. Regresi dan Penerimaan** | 7 | Semua | Regresi P0, ulang soal Critical/High yang diperbaiki, isi rekap dan kesimpulan | Perbaikan akhir, reset data staging | Kriteria kelulusan (6) terpenuhi; rekap direview Dedy |

---

## 8. Risiko dan Mitigasi

Skala: Probabilitas dan Dampak = Rendah (R) / Sedang (S) / Tinggi (T).

| ID | Risiko | Prob. | Dampak | Mitigasi | Pemilik |
|---|---|:--:|:--:|---|---|
| R-01 | Fixture/mock terlambat sehingga banyak soal BLOCKED | S | T | Daftar FX/MK disepakati di Sprint 0; soal terkait dijadwalkan setelah fixture siap | Dedy + Dwi |
| R-02 | Kontrak API/ML berubah sehingga soal usang | S | S | Perubahan kontrak wajib diikuti pembaruan soal; rujukan ID menunjukkan soal terdampak | Dedy + Dwi |
| R-03 | Penguji tanpa sengaja menjalankan atau membuka file berbahaya | R | T | Hanya fixture dan mock; batas keselamatan (1.4); tidak ada malware di lingkungan uji | Dina |
| R-04 | Staging tidak stabil atau berbeda dari konfigurasi yang diasumsikan | S | S | Catat versi dan konfigurasi tiap putaran (4.1); reset data sebelum regresi | Dedy |
| R-05 | Penguji tunggal: kelelahan, bias, atau berhalangan | S | S | Review rekap oleh Dedy; soal berprioritas jelas; template baku agar dapat dilanjutkan orang lain | Dina + Dedy |
| R-06 | BLOCKED atau soal yang tidak dijalankan dihitung sebagai lulus | R | T | Aturan 6.2; rekap menampilkan BLOCKED/N/A terpisah | Dina |
| R-07 | Bukti pengujian memuat token, password, atau data sensitif | S | T | Aturan 1.4; saring tangkapan layar sebelum dibagikan | Dina |
| R-08 | Masalah konkurensi dan *race condition* tidak tertangkap uji manual | S | S | Developer menulis test otomatis untuk jalur tersebut; Bagian E dan H menguji sebagian secara manual | Dedy + Arya |
| R-09 | Perbaikan dan pengujian berjalan bersamaan sehingga hasil membingungkan | S | S | Putaran pengujian dimulai dengan serah-terima versi; bug dicatat dengan versi aplikasi | Dedy + Dina |
| R-10 | Akun uji atau data uji bocor atau terbawa ke lingkungan lain | R | S | Akun uji hanya di staging; password tidak ditulis di lembar; reset berkala | Dedy |

---

## 9. Keputusan Terbuka (Decision Log)

| ID | Pertanyaan | Opsi | **Rekomendasi default** | Pemilik | Batas waktu |
|---|---|---|---|---|---|
| DEC-QA-01 | Lingkungan pengujian utama | Local / Staging | **Staging** (konfigurasi menyerupai produksi) | Dedy | Sprint 0 |
| DEC-QA-02 | Alat uji API | Postman / Insomnia / curl | **Postman** dengan koleksi dari OpenAPI | Dina + Arya | Sprint 0 |
| DEC-QA-03 | Pencatatan bug | Tabel di lembar / issue tracker repositori | **Issue tracker** repositori; ID bug dicatat pada lembar | Dina + Dedy | Sprint 0 |
| DEC-QA-04 | Ambang kelulusan | — | Seperti 6.2 | Dina + Dedy | Sprint 2 |
| DEC-QA-05 | Penyedia fixture dan mock | — | Dwi: fixture/mock ML; Dedy: fixture file dan simulasi infrastruktur | Dedy + Dwi | Sprint 0 |
| DEC-QA-06 | Mode integrasi ML saat pengujian | Fake/stub / ML nyata | Fake/stub pada Tahap 2–3, **ML nyata** pada Tahap 4–5 | Dedy + Dwi | Sprint 2 |
| DEC-QA-07 | Cara menguji hasil `malicious` tanpa malware | Mock / dataset evaluasi aman | **Mock response** (MK-02) dan dataset evaluasi aman dari Dwi | Dwi + Dina | Sprint 1 |
| DEC-QA-08 | Load test dan *penetration test* | Dilakukan sekarang / ditunda / oleh pihak lain | **Ditunda** ke pengembangan lanjutan; developer menjaga batas dasar | Dedy | Sprint 6 |
| DEC-QA-09 | Reset data staging antar putaran | Ya / Tidak | **Ya** sebelum regresi penuh | Dedy | Sprint 3 |

---

## 10. Catatan untuk Tim Pengembang

Agar pengujian dapat berjalan, developer menyediakan:

| Kebutuhan | Penyedia | Dibutuhkan sebelum |
|---|---|---|
| Akun uji User/Admin (3.2) dan catatan versi aplikasi + `model_version` tiap putaran | Dedy | Tahap 1 |
| OpenAPI dan koleksi Postman | Arya | Tahap 1 |
| Fixture file FX-01 s.d. FX-16 untuk format didukung, file kosong, format tidak didukung, file rusak, ekstensi tidak sesuai, dan lainnya (4.3) | Dedy + Dwi | Tahap 2 |
| Mock response untuk seluruh label dan kondisi error (4.4) pada Fake ML Server/mode stub | Dwi + Dedy | Tahap 2 |
| Cara mensimulasikan timeout, ML tidak tersedia, respons tidak valid, koneksi terputus, dan worker mati di staging | Dedy | Tahap 2–3 |
| Kemampuan mengubah kuota, retensi, dan mode pemeliharaan di staging | Dedy | Tahap 2 |
| Mailpit/kotak masuk uji untuk email verifikasi dan reset password | Dedy | Tahap 1 |
| Akses Admin ke detail scan beserta daftar attempt dan audit log | Dedy + Arya | Tahap 3 |
| `request_id` tampil pada setiap respons/log agar bug dapat ditelusuri | Backend | Tahap 1 |
| Data seeder (FX-14) untuk uji riwayat dan pagination | Dedy | Tahap 3 |

**Catatan tambahan:**
- Untuk kasus file sedang digunakan (C1), cukup uji akses baca terhadap file dummy; tidak perlu menguji memori proses, injeksi, atau penghentian proses.
- Perubahan kontrak API Backend–ML harus diikuti pembaruan skenario integrasi pada lembar ini.
- Developer tidak mengubah status PASS/FAIL pada lembar; perbaikan dikonfirmasi melalui *retest* oleh Penguji.

---

## 11. Definition of Done (Pengujian)

Satu putaran pengujian dianggap selesai bila:
- Semua soal pada cakupan putaran dijalankan dan setiap soal memiliki hasil aktual, status, dan bukti.
- Soal berstatus BLOCKED atau N/A memiliki alasan tertulis.
- Setiap FAIL dilaporkan memakai template (Bagian I) dengan langkah reproduksi dan `request_id`.
- Bug yang berstatus Fixed sudah di-*retest* dan dinyatakan Closed atau dikembalikan.
- Rekap (Bagian J) terisi dan kesimpulan ditulis dengan mempertimbangkan 6.2.
- Bukti tidak memuat token, password, atau data sensitif.
- Rekap direview Dedy dan ringkasan hasil diteruskan ke tim dokumentasi.

---

## Lampiran A — Glosarium

| Istilah | Arti |
|---|---|
| **Soal** | Satu skenario pengujian pada lembar ini (contoh A11) |
| **Fixture (FX)** | File uji dummy yang aman dan non-eksekusi |
| **Mock (MK)** | Respons ML tiruan atau simulasi kondisi untuk menguji perilaku backend |
| **Fake ML Server / mode stub** | Layanan ML tiruan yang mengikuti kontrak, dipakai sebelum model nyata siap |
| **PASS / FAIL / BLOCKED / N/A** | Status hasil pengujian (5.2) |
| **Hasil aktual** | Yang benar-benar terjadi saat soal dijalankan |
| **`request_id`** | ID yang menempel pada respons dan log untuk menelusuri satu permintaan |
| **BOLA / IDOR** | Akses ke data milik pengguna lain karena otorisasi tingkat objek lemah |
| **Idempotensi** | Mengirim permintaan yang sama berulang tidak membuat hasil ganda |
| **Retry / backoff** | Percobaan ulang otomatis dengan jeda yang makin panjang |
| **Watchdog** | Proses yang memulihkan scan yang tersangkut |
| **Edge case** | Situasi tidak biasa di luar alur normal |
| **Regresi** | Menjalankan ulang pengujian setelah perbaikan untuk memastikan tidak ada yang rusak |
| **Staging** | Lingkungan uji yang menyerupai produksi |

---

## Lampiran B — Indeks Soal dan Rujukan Kebutuhan

| ID | Soal | Rujukan | Alat | Prioritas |
|---|---|---|---|:--:|
| A1 | Registrasi akun baru | FR-AUTH-01, BR-01 | UI | P0 |
| A2 | Registrasi dengan isian tidak valid dan percobaan menyisipkan role | FR-AUTH-01, BR-01 | API | P0 |
| A3 | Kebijakan password | FR-AUTH-08 | UI / API | P0 |
| A4 | Login User dengan akun valid | FR-AUTH-02 | UI | P0 |
| A5 | Login dengan password salah atau email tidak terdaftar | FR-AUTH-02 | UI | P0 |
| A6 | Pembatasan percobaan login | FR-AUTH-13 | UI / API | P0 |
| A7 | User mencoba membuka halaman atau API Admin | BR-14 | UI / API | P0 |
| A8 | Logout | FR-AUTH-03 | UI / API | P0 |
| A9 | Lupa dan reset password | FR-AUTH-06 | UI + email uji | P0 |
| A10 | Akun yang disuspend | FR-USR-05, BR-15 | UI / API | P0 |
| A11 | User mencoba membuka data milik User lain | BR-03, FR-HIS-01 | API | P0 |
| A12 | Perubahan role dan perlindungan Admin | FR-USR-06, BR-05 | UI / API | P0 |
| A13 | Login Admin dengan 2FA | FR-AUTH-09 | UI | P1 |
| B1 | Upload file normal yang didukung | FR-FILE-01, FR-SCN-01 | UI | P0 |
| B2 | Upload file kosong | FR-FILE-02 | UI / API | P0 |
| B3 | Upload format tidak didukung | FR-FILE-02, FR-FMT-02, BR-ML-10 | UI / API | P0 |
| B4 | Ekstensi tidak sesuai isi file | FR-FILE-02, FR-FMT-01, FR-FMT-04 | UI / API | P0 |
| B5 | Upload melebihi batas ukuran | FR-FILE-02 | UI / API | P0 |
| B6 | Nama file panjang atau karakter khusus | FR-FILE-05, FR-FILE-03 | UI / API | P0 |
| B7 | Kuota harian dan scan aktif | FR-FILE-06, BR-16 | UI / API | P0 |
| B8 | Pembatasan laju upload | Backend 10.4 (limiter `scans.upload`) | API | P1 |
| B9 | Pengiriman ganda dengan Idempotency-Key | FR-FILE-07 | API | P0 |
| B10 | File tersimpan privat | FR-FILE-03, FR-HIS-02 | UI / API | P0 |
| B11 | Antrean penuh dan mode pemeliharaan | FR-SET-02, FR-SCN-14 | UI / API | P1 |
| C1 | File sedang dibuka oleh aplikasi lain | FR-FILE-02 | UI | P1 |
| C2 | File berubah ketika proses upload/scan berlangsung | FR-FILE-04, FR-RCV-03 | Bantuan developer | P1 |
| C3 | File rusak atau tidak lengkap | FR-FMT-03, FR-ERR-02, BR-ML-02 | UI | P0 |
| C4 | Format didukung, tetapi preprocessing gagal | FR-PRE-04, FR-ERR-04, BR-ML-02 | UI | P0 |
| C5 | File program yang sedang berjalan | FR-PRE-01, BR-ML-06 | UI | P1 |
| C6 | Dokumen terenkripsi atau berpassword | FR-FMT-05, BR-ML-04 | UI | P1 |
| C7 | File arsip | FR-FMT-05, FR-FILE-02 | UI | P1 |
| C8 | File mendekati batas ukuran | FR-FILE-02, NFR-PERF-02 (ML) | UI | P1 |
| D1 | Hasil `safe` | FR-ML-02, BR-ML-03 | UI | P0 |
| D2 | Hasil `malicious` | FR-ML-02 | UI | P0 |
| D3 | Hasil `suspicious` | FR-ML-02, BR-ML-04 | UI | P0 |
| D4 | Hasil `unknown` | FR-ML-02, BR-ML-04 | UI | P0 |
| D5 | Confidence tidak tersedia | FR-INF-05, FR-ML-02 | UI | P0 |
| D6 | Respons ML tidak valid | FR-ML-02, BR-08 | UI + Admin | P0 |
| D7 | Disclaimer hasil | BR-18, BR-ML-03 | UI | P0 |
| E1 | Scan berlangsung lama | FR-SCN-03, FR-SCN-15 | UI + Admin | P0 |
| E2 | ML Service tidak tersedia | FR-SCN-06, BR-08 | UI + Admin | P0 |
| E3 | Koneksi terputus saat scan | FR-SCN-07, FR-SCN-06 | Bantuan developer + Admin | P0 |
| E4 | Tombol Scan ditekan berkali-kali | FR-FILE-07, FR-SCN-01 | UI | P0 |
| E5 | Membatalkan scan | FR-SCN-10 | UI / API | P0 |
| E6 | ML kelebihan beban | FR-SCN-06 dan ML 7.6 | UI + Admin | P1 |
| E7 | Scan tersangkut karena worker mati | FR-SCN-09 | Bantuan developer + Admin | P0 |
| E8 | Jumlah percobaan ulang otomatis | FR-SCN-06, FR-SCN-07 | Admin | P0 |
| E9 | Callback palsu atau ganda | FR-ML-05, ML 7.8 | API + Bantuan developer | P1 |
| F1 | Hasil scan masuk ke riwayat | FR-HIS-01, BR-03 | UI | P0 |
| F2 | Scan gagal masuk riwayat | FR-SCN-07, BR-08 | UI | P0 |
| F3 | Membuka detail riwayat | FR-HIS-02 | UI / API | P0 |
| F4 | Filter, pencarian, pengurutan, dan halaman | FR-HIS-03, FR-HIS-04 | UI / API | P0 |
| F5 | Menghapus item riwayat | FR-HIS-05, BR-12 | UI / API + Admin | P0 |
| F6 | File kedaluwarsa karena retensi | FR-FILE-08, ML 8.6 | UI / API + Bantuan developer | P1 |
| F7 | Dashboard User | FR-DSH-01, FR-DSH-05 | UI | P1 |
| F8 | Notifikasi | FR-NTF-01, FR-NTF-02, BR-19 | UI / API | P1 |
| F9 | Umpan balik hasil | FR-HIS-07 | UI / API | P1 |
| G1 | Statistik dashboard Admin | FR-DSH-02, FR-DSH-03 | UI + Admin | P1 |
| G2 | Status ML Service | FR-ML-06, FR-OPS-01 | UI / API | P1 |
| G3 | Audit log | FR-AUD-01, FR-AUD-03 | UI / API | P0 |
| G4 | Daftar pengguna oleh Admin | FR-USR-04 | UI / API | P0 |
| G5 | Admin mengulang scan yang gagal | FR-SCN-11, BR-10 | UI / API | P0 |
| G6 | Admin melihat seluruh scan | FR-HIS-08, BR-13 | UI / API | P0 |
| G7 | Pengaturan sistem | FR-SET-01, FR-SET-03, BR-17 | UI / API | P1 |
| H1 | Bagaimana jika User mengunggah dua file sekaligus? | FR-FILE-01 | UI / API | P1 |
| H2 | Bagaimana jika User menutup browser ketika scan berlangsung? | FR-SCN-03 | UI | P1 |
| H3 | Bagaimana jika User login dari dua perangkat? | FR-AUTH-03, FR-AUTH-07 | UI / API | P1 |
| H4 | Bagaimana jika Admin menonaktifkan layanan scan ketika masih ada antrean? | FR-SET-02 | UI + Admin | P1 |
| H5 | Bagaimana jika dua versi model memberikan hasil berbeda? | FR-MDL-01, BR-ML-08 | Mock | P1 |
| H6 | Bagaimana jika dua User mengunggah file yang sama persis? | BR-03 | UI / API | P1 |
| H7 | Bagaimana tampilan waktu pada zona waktu berbeda? | AS-06 (Backend), 10.1 | UI | P1 |
| H8 | Buat satu skenario edge case baru | — | Bebas (aman) | P1 |

---

## Penutup

Lembar pengujian ini memastikan Penguji tidak hanya mencoba fitur dalam kondisi normal, tetapi juga memikirkan situasi nyata yang mungkin dialami User. Melalui skenario terstruktur, eksperimen edge case, dan pencatatan hasil, tim dapat menemukan masalah lebih awal serta meningkatkan stabilitas dan keamanan STIKSAFE-67. Pengujian dilaksanakan oleh **Dina**, dengan dukungan Dedy Bhaskara, Arya Putra, dan Dwi Yasa.

**Prinsip utama: uji perilaku sistem, catat bukti, jangan berasumsi, dan jangan menggunakan malware sungguhan.**
