# PRD Pengujian & QA — STIKSAFE-67

| Informasi | Detail |
|---|---|
| Produk | STIKSAFE-67 — pemeriksaan awal keamanan file dan URL |
| Dokumen | Product Requirements Document (PRD) Pengujian / QA |
| Versi | 3.0 |
| Tanggal | 28 November 2026 |
| Status | Draft untuk pengujian versi aplikasi saat ini |
| Cakupan utama | Website Laravel, akun, scan file/URL, Machine Learning, fallback, dashboard, admin, tema, responsif |
| Dasar penyusunan | Website dan source code di `STIKSAFE-67-Laravel`; dokumen uji lama `PRD/PRD_Pengujian_STIKSAFE-67_v2.0.md` |

> **Penting:** Dokumen v2.0 memuat sejumlah kebutuhan rancangan/API yang tidak seluruhnya terbukti tersedia pada website saat ini. PRD v3.0 mengganti skenario lama dengan fitur web yang teramati. Fitur yang belum diverifikasi ditandai **Perlu konfirmasi**, bukan diasumsikan sudah ada.

---

## 1. Tujuan

1. Memastikan pengguna dapat mendaftar, masuk, menjalankan scan, membaca hasil, dan melihat riwayat.
2. Memastikan akses Admin terpisah dari akses pengguna biasa.
3. Memastikan kegagalan ML tidak ditampilkan sebagai hasil yang menjamin file aman.
4. Memeriksa dark/light mode, pencarian penerima notifikasi, tampilan mobile, dan pesan kesalahan.
5. Mencatat hasil uji yang dapat diulang beserta bukti dan tindak lanjut.

## 2. Batas keselamatan

- Gunakan akun dan file dummy yang disetujui. Jangan unggah data pribadi atau dokumen rahasia.
- **Jangan gunakan malware sungguhan**, jangan eksekusi file yang diperiksa, dan jangan lakukan pengujian serangan tanpa izin.
- Jangan menguji di production tanpa persetujuan tertulis.
- Screenshot/log harus menyamarkan password, token, secret, informasi pribadi.
- `migrate:fresh` menghapus data. Jalankan hanya pada database lokal khusus pengujian yang boleh dikosongkan.

## 3. Lingkungan dan komponen

| Komponen | Fungsi yang diuji |
|---|---|
| Laravel web | Autentikasi, upload, antrean, hasil, riwayat, profil, notifikasi, dashboard |
| Database MySQL lokal | Data akun, scan, hasil, notifikasi, pengaturan |
| Queue worker | Memproses job scan file |
| ML Service Python | Pemeriksaan kesehatan, analisis file/URL saat service aktif |
| Offline fallback | Jalur analisis lokal ketika permintaan ke ML gagal; bukan pengganti akurasi model |
| Browser | UI desktop/mobile, tema, navigasi, polling hasil |

### 3.1 Alamat lokal

- Website: `http://127.0.0.1:8000`
- ML Service: `http://127.0.0.1:8001`
- Status publik ML: `http://127.0.0.1:8000/ml-status`

### 3.2 Setup lingkungan uji lokal

1. Ikuti `C:\Tugas Laravel\README.md` untuk persiapan dependency, `.env`, MySQL, dan ML.
2. Siapkan database **lokal khusus pengujian**, kemudian jalankan migrasi/seed sesuai README.
3. Jalankan ML Service, Laravel, dan queue worker di tiga terminal terpisah.
4. Jalankan tes otomatis dari folder Laravel:

```powershell
php artisan test
```

5. Catat versi/commit lokal, waktu uji, browser, konfigurasi ML, serta apakah worker dan ML aktif.

> Tes otomatis yang diketahui saat dokumen ini disusun: `php artisan test` memiliki 15 test. Jumlah tersebut dapat berubah; selalu catat hasil aktual.

## 4. Peran penguji dan data uji

- **Penguji:** menjalankan setiap skenario, memberi status, menyimpan bukti aman, membuat laporan bug.
- **Developer Laravel:** menyiapkan database, worker, akun uji; membantu simulasi ML offline.
- **Developer ML:** menyiapkan service aktif dan respons uji yang aman.
- Pisahkan akun **Admin**, **User A**, dan **User B**. Jangan gunakan password seeder development di deployment publik.

### 4.1 Fixture aman yang diperlukan

| ID | Fixture |
|---|---|
| FX-01 | File kecil valid dengan format yang didukung, tidak mengandung data pribadi |
| FX-02 | File kosong 0 byte |
| FX-03 | File melebihi batas unggah 25 MB, data dummy |
| FX-04 | Ekstensi tidak diizinkan atau format tidak cocok, non-eksekusi |
| FX-05 | File dokumen/teks non-eksekusi dengan nama Unicode/spasi |
| FX-06 | URL valid ke domain aman yang disetujui penguji |
| FX-07 | URL salah format, URL terlalu panjang, atau skema tidak didukung |
| FX-08 | Data beberapa akun/scan untuk menguji pemisahan akses dan daftar |
| FX-09 | File executable dummy benign yang disiapkan developer, tidak pernah dijalankan |

Jangan membuat fixture malware nyata. Untuk label model tertentu, gunakan file dummy/mock ML dari developer.

## 5. Status dan prioritas

**Prioritas:** P0 = wajib untuk fungsi inti/keamanan; P1 = penting; P2 = kosmetik/opsional.

| Status | Makna |
|---|---|
| PASS | Hasil sesuai kriteria |
| FAIL | Hasil tidak sesuai; buat bug |
| BLOCKED | Tidak dapat dijalankan; catat alasannya |
| N/A | Tidak relevan pada versi/lingkungan ini; jelaskan |

Satu skenario memiliki satu status. BLOCKED bukan PASS. Retest dilakukan setelah perbaikan.

## 6. Daftar skenario pengujian

### A. Akses publik, akun, tema

| ID | P | Skenario dan kriteria lulus |
|---|:--:|---|
| A01 | P0 | Buka `/`. Halaman publik tampil, tautan utama mengarah ke halaman yang benar. |
| A02 | P0 | Daftar dengan data valid. Akun dibuat sebagai user biasa; form tidak memberi pilihan role admin. |
| A03 | P0 | Daftar dengan email duplikat, format email salah, password pendek, atau konfirmasi tidak cocok. Ditolak dengan pesan yang dapat dipahami; akun tidak tercipta. |
| A04 | P0 | Login user valid berhasil; kredensial salah ditolak tanpa membocorkan informasi sensitif. |
| A05 | P0 | User membuka URL Admin secara langsung. Akses ditolak; data Admin tidak terlihat. |
| A06 | P0 | Logout, lalu akses halaman privat. Sistem meminta login kembali. |
| A07 | P1 | Gunakan ikon mata pada login: password tersembunyi/terlihat bergantian; label aksesibilitas ikut berubah. |
| A08 | P1 | Register: ikon mata bekerja untuk password dan konfirmasi secara terpisah. |
| A09 | P1 | Pilih dark mode di landing, buka register/login, pastikan mode tetap dark; pilih light, pastikan tetap light. Warna teks/input terbaca. |
| A10 | P1 | Profil: ubah tema; navigasikan ke Beranda, Scan, Riwayat, Notifikasi. Preferensi tersimpan pada browser yang sama. |

### B. Scan file dan hasil

| ID | P | Skenario dan kriteria lulus |
|---|:--:|---|
| B01 | P0 | User mengunggah FX-01. File diterima, diarahkan ke detail scan, status awal tampil wajar. |
| B02 | P0 | Scan selesai saat ML dan queue worker aktif. Status/hasil diperbarui otomatis dan hasil tampil konsisten. |
| B03 | P0 | Kirim FX-02. Upload ditolak dengan pesan validasi; scan tidak masuk antrean. |
| B04 | P0 | Kirim FX-03. File di atas 25 MB ditolak; tidak ada scan yang berhasil dibuat. |
| B05 | P0 | Kirim FX-04. Tipe/isi yang tidak diizinkan ditolak. |
| B06 | P1 | Pilih file melalui browse dan drag-and-drop. Nama serta ukuran file benar; mengganti pilihan memperbarui label. |
| B07 | P0 | Selama status queued/processing, halaman menampilkan status yang benar; setelah terminal status polling berhenti. |
| B08 | P0 | Hasil yang tidak dikenal di UI ditampilkan sebagai **Indikasi netral**. Nilai internal tetap `unknown`, jika sesuai respons engine. |
| B09 | P0 | Hasil mencurigakan/berbahaya menampilkan label, alasan/metadata yang tersedia, tanpa menyatakan analisis sebagai jaminan mutlak. |
| B10 | P0 | User A mencoba membuka ID scan milik User B. Sistem menolak akses dan tidak membocorkan detail scan. |
| B11 | P1 | Buka scan yang sudah dihapus/retensi habis. Sistem menampilkan keadaan tidak tersedia dengan aman. **Perlu konfirmasi kebijakan retensi aktual.** |
| B12 | P1 | Coba batal scan melalui alur konfirmasi. Hanya scan yang dapat dibatalkan berubah status; hasil terminal tidak berubah secara ilegal. |

### C. Scan URL

| ID | P | Skenario dan kriteria lulus |
|---|:--:|---|
| C01 | P0 | Kirim FX-06 melalui tab URL. Hasil analisis URL ditampilkan dengan label dan alasan yang dikembalikan. |
| C02 | P0 | URL kosong atau format salah ditolak dengan pesan validasi; tidak terjadi HTTP 500. |
| C03 | P1 | URL lebih dari 2048 karakter ditolak dengan pesan jelas. |
| C04 | P0 | ML URL offline/timeout. Pengguna mendapat pesan kegagalan yang jelas; UI **tidak** menyebut URL aman. Fallback URL perlu dianggap belum tersedia sampai diverifikasi. |
| C05 | P1 | Berpindah tab file/URL berulang. Hanya form tab aktif yang terlihat dan input tetap dapat digunakan. |

### D. ML, queue, dan fallback

| ID | P | Skenario dan kriteria lulus |
|---|:--:|---|
| D01 | P0 | ML aktif. Status di landing dan admin settings menunjukkan Online/terhubung. |
| D02 | P0 | Matikan ML lokal secara terkontrol. Status berubah menjadi Offline; indikator/radar merah dan teks offline. |
| D03 | P0 | Saat ML mati, scan file masuk jalur Offline Heuristic Engine sesuai implementasi. Hasil harus menyatakan/menyimpan bahwa engine fallback dipakai; tidak boleh disamarkan sebagai prediksi ML. |
| D04 | P0 | Fallback tidak boleh dipahami sebagai pemindaian antivirus penuh. Verifikasi pesan/metadata memberi konteks keterbatasan, dan jangan menyimpulkan file aman hanya dari ekstensi. **Tinjau sebagai risiko produk bila fallback memberi label `safe` berdasarkan ekstensi saja.** |
| D05 | P0 | Matikan queue worker. Scan tetap menunjukkan antrean/processing sesuai keadaan; tidak menampilkan hasil selesai palsu. Setelah worker hidup, pastikan pemrosesan pulih. |
| D06 | P0 | Simulasikan respons ML JSON rusak, hash/ID tidak cocok, HTTP error, atau timeout via mock. Sistem mencatat kegagalan; bukan hasil `safe`. |
| D07 | P1 | Pulihkan ML. Status kembali Online setelah pemeriksaan berikutnya; scan baru memakai engine ML. |
| D08 | P1 | Admin settings menampilkan status ML dan pesan offline tanpa teks putih tak terbaca di dark mode. |

### E. Riwayat, profil, notifikasi

| ID | P | Skenario dan kriteria lulus |
|---|:--:|---|
| E01 | P0 | User melihat riwayat scan miliknya; scan user lain tidak muncul. |
| E02 | P1 | Hasil `unknown` pada riwayat/filter ditulis **Indikasi netral**; filter tetap bekerja berdasarkan nilai internal `unknown`. |
| E03 | P1 | Buka profil. Nama, email, tanggal bergabung terbaca pada light dan dark mode. |
| E04 | P1 | Ubah profil/avatar dengan file valid dan tidak valid. Validasi ukuran/jenis ditampilkan; foto baru tampil setelah sukses. |
| E05 | P0 | Admin mengirim notifikasi ke Semua user, Semua admin, atau Seluruh akun. Penerima yang tepat menerima notifikasi. |
| E06 | P1 | Notifikasi dapat ditandai dibaca satu per satu/semua; jumlah badge diperbarui. |
| E07 | P1 | Menu notifikasi dan dropdown Admin tetap kontras saat hover/aktif dalam dark mode. |

### F. Panel Admin dan pengaturan

| ID | P | Skenario dan kriteria lulus |
|---|:--:|---|
| F01 | P0 | Admin membuka dashboard. Statistik status, hasil, akun, antrean, dan tren tampil tanpa error. |
| F02 | P0 | User biasa membuka semua route `/admin/*`. Akses ditolak. |
| F03 | P1 | Admin mencari/filter scan log dan membuka detail. Data serta label tampil konsisten. |
| F04 | P1 | Admin mengirim notifikasi untuk masing-masing cakupan penerima. Jumlah penerima/konfirmasi benar. |
| F05 | P1 | Admin membuka Feedback dan menandai feedback ditinjau. Status berubah sesuai aksi. |
| F06 | P0 | Admin mengubah setting dalam batas valid. Setting tersimpan dan audit log dibuat. |
| F07 | P0 | Admin mengirim nilai setting di luar batas atau key tidak diizinkan. Ditolak; nilai lama tidak berubah. |
| F08 | P0 | Aktifkan maintenance mode. User non-admin menerima halaman 503; Admin tetap dapat mengakses panel dan menonaktifkannya. |
| F09 | P1 | Dashboard/admin settings tetap rapi dan terbaca pada layar kecil serta kedua tema. |

### G. Responsif, aksesibilitas dasar, dan regresi visual

| ID | P | Skenario dan kriteria lulus |
|---|:--:|---|
| G01 | P1 | Uji landing, login/register, dashboard, scan, riwayat, profil, admin pada desktop dan lebar mobile. Tidak ada konten utama terpotong/overflow horizontal. |
| G02 | P1 | Uji semua halaman di light/dark: tidak ada kartu/input putih menyilaukan pada dark mode; teks tetap kontras dan terbaca. |
| G03 | P1 | Hover/focus tombol dan navbar tidak berubah menjadi blok putih menyilaukan pada dark mode. |
| G04 | P1 | Navigasi keyboard dapat mencapai input/tombol; label form berhubungan dengan input; tombol mata memiliki nama aksesibel. |
| G05 | P2 | Browser refresh setelah tema diubah. Tema tetap sesuai preferensi localStorage browser. |

## 7. Prosedur pencatatan per uji

Salin template ini untuk setiap ID:

```text
ID skenario:
Tanggal/waktu:
Versi/commit:
Browser/perangkat:
Akun (role; tanpa password):
ML Service: Online / Offline
Queue Worker: Aktif / Mati
Langkah aktual:
Hasil yang diharapkan:
Hasil aktual:
Status: PASS / FAIL / BLOCKED / N/A
Bukti aman:
Bug ID:
Catatan:
```

### 7.1 Template laporan bug

```text
Judul:
Prioritas: Critical / High / Medium / Low
ID skenario:
Prasyarat:
Langkah reproduksi:
Hasil aktual:
Hasil yang diharapkan:
Browser/commit:
Bukti (hapus secret/data pribadi):
Dampak:
```

## 8. Klasifikasi prioritas bug

| Prioritas | Contoh |
|---|---|
| Critical | Kebocoran data lintas akun; user dapat menjadi Admin; kegagalan ML dilabeli aman; data sensitif bocor |
| High | Scan utama tidak dapat digunakan; hasil tidak tersimpan; queue tidak pulih; semua user/admin tidak dapat login |
| Medium | Fitur admin/riwayat/notifikasi sebagian gagal; tema atau responsif mengganggu penggunaan |
| Low | Teks, spasi, warna, atau detail visual kecil tanpa dampak fungsi/keamanan |

Alur: `Open → Triage → In Progress → Fixed → Retest → Closed`. Hanya penguji yang menjalankan retest mengubah `Fixed` menjadi `Closed`.

## 9. Exit criteria / syarat lulus rilis uji

1. Semua skenario P0 berstatus PASS; tidak ada BLOCKED yang belum diputuskan.
2. Tidak ada bug Critical atau High yang masih terbuka.
3. Semua skenario hak akses, isolasi data, fallback, validasi file/URL telah diuji.
4. Tes otomatis `php artisan test` lulus pada build yang diuji.
5. Bug Medium/Low yang diterima memiliki penanggung jawab atau catatan risiko.
6. Laporan berisi versi build, konfigurasi lingkungan, rekap PASS/FAIL/BLOCKED/N/A, bukti, dan keputusan rilis.

## 10. Rekap putaran

| Ukuran | Nilai |
|---|---|
| Versi/commit | ______________________________ |
| Tanggal pengujian | ______________________________ |
| Penguji | ______________________________ |
| P0 PASS / total | ______________________________ |
| P1 PASS / total | ______________________________ |
| FAIL / BLOCKED / N/A | ______________________________ |
| Bug Critical / High terbuka | ______________________________ |
| Hasil `php artisan test` | ______________________________ |
| Keputusan | Lulus / Lulus bersyarat / Tidak lulus |
| Catatan risiko | ______________________________ |

---

## Lampiran A — Catatan perbedaan terhadap lembar v2.0

Lembar v2.0 mencakup 72 skenario dan banyak kontrak API/fitur yang harus diverifikasi terhadap aplikasi aktual. PRD v3.0 memusatkan pengujian pada website saat ini: dashboard user/admin, scan file dan URL, ML status, offline fallback, MySQL lokal, queue, tema, notifikasi broadcast, profil, feedback, maintenance mode, serta tampilan responsif.

Fitur v2.0 seperti API token, email verification wajib, kuota harian, step-up authentication, callback ML, tanda tangan callback, rate limit tertentu, retry terjadwal, dan kebijakan detail retensi **tidak dinyatakan sebagai fitur tersedia** di sini. Tambahkan pengujian untuk fitur tersebut hanya setelah developer mengonfirmasi implementasi dan kontraknya.

## Lampiran B — Catatan risiko Offline Fallback

Fallback heuristik bukan model EMBER2024 dan bukan antivirus penuh. Pengujian harus mencatat engine yang menghasilkan keputusan, isi pesan untuk pengguna, jenis file yang diperiksa, dan label hasil. Jangan gunakan hasil fallback sebagai bukti file aman. Jika implementasi fallback memberi label aman tanpa analisis isi yang memadai, laporkan sebagai bug/risiko **High** untuk ditinjau sebelum deployment.
