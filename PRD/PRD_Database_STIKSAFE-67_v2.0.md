# PRODUCT REQUIREMENTS DOCUMENT (PRD)

## Database dan Laravel Migration — STIKSAFE-67

| | |
|---|---|
| **Nama Produk** | STIKSAFE-67 (angka 67 merujuk angkatan 2026–2027) |
| **Jenis Dokumen** | PRD Database dan Laravel Migration |
| **Komponen** | Skema database aplikasi (MySQL), migration, relasi Eloquent, seeder, dan factory |
| **Pelaksana Database** | Dwi Yasa (I Made Dwi Yasa) |
| **Mitra** | Dedy Bhaskara dan Arya Putra (Backend: pemakai skema dan pemilik model data logis pada PRD Backend Bagian 9) |
| **Dokumen terpisah** | PRD Backend, PRD ML, PRD HTML, PRD Pengujian, PRD Dokumentasi (lihat 0.3) |
| **Database** | MySQL 8.4 LTS (minimum 8.0.16; lihat 4.2) |
| **Versi Dokumen** | 2.0 |
| **Status** | Draft implementasi |
| **Tanggal** | 23 November 2026 |
| **Menggantikan** | PRD Database dan Laravel Migration v1.0 (nama lama produk: STIKSAFE 6769) |
| **Sinkron dengan** | PRD Backend v2.2 (Bagian 6, 8, 9, 13), PRD ML v2.0 (Bagian 7), PRD Pengujian v2.0 (akun uji dan FX-14) |

> **Cara membaca dokumen ini.** Seluruh skema dikelola lewat **Laravel Migration**; dokumen ini adalah spesifikasinya, migration adalah sumber kebenarannya. Setiap kebutuhan memiliki ID (contoh `FR-DB-INT-03`, `BR-DB-02`, `NFR-DB-01`). Prioritas: **P0** wajib versi pertama, **P1** penting setelah fungsi inti, **P2** lanjutan. Angka operasional (ukuran kolom, retensi, hak akses) adalah usulan awal yang dikonfirmasi pada Decision Log (Bagian 20). Kolom **Rujukan** menunjuk kebutuhan di PRD Backend/ML. Bagian 7 memuat pemetaan ke PRD Backend dan menandai perbedaan yang perlu disepakati.

---

## 0. Kontrol Dokumen

### 0.1 Riwayat Revisi

| Versi | Tanggal | Perubahan |
|---|---|---|
| 1.0 | — | Draf awal: 7 tabel inti (`users`, `model_versions`, `scans`, `scan_results`, `scan_status_logs`, `audit_logs`, `system_settings`), alur data, pengujian DB-01..18 |
| 2.0 | 23 Nov 2026 | Disusun ulang mengikuti format PRD lain dan diselaraskan dengan PRD Backend v2.2, PRD ML v2.0, dan PRD Pengujian v2.0. Perubahan utama: ID publik menjadi ULID (`public_id`, bukan UUID) agar sama dengan kontrak API/ML; tabel baru `scan_attempts`, `idempotency_keys`, `scan_feedback`, `daily_scan_stats`, `ml_callback_events`, dan `personal_access_tokens`; status akun `status` menggantikan `is_active`; status scan ditambah `cancelled` dan `rejected`; MySQL 8.4 LTS direkomendasikan karena MySQL 8.0 telah mencapai akhir masa dukungan (April 2026); constraint CHECK, kolom generated untuk keunikan khusus MySQL, hak akses DB per tabel, retensi, backup dan restore, seeder untuk akun uji dan FX-14, risiko, dan keputusan terbuka |

### 0.2 Pelaksana dan Pembagian Tugas

PRD ini khusus **database aplikasi (MySQL) dan Laravel Migration** dan dikerjakan oleh **Dwi Yasa**. Backend (Dedy dan Arya) memakai skema ini lewat Eloquent dan service.

| Area | Pekerjaan | Penanggung jawab |
|---|---|---|
| **Skema dan migration** | Seluruh tabel, kolom, PK/FK, unique, CHECK, indeks, urutan migration, `up()`/`down()` | Dwi Yasa |
| **Model Eloquent** | Kerangka model: relasi, casts, `$fillable`, `$hidden`, route key `public_id`, PHP backed enum untuk nilai terbatas | Dwi Yasa |
| **Seeder dan factory** | Akun uji, versi model, pengaturan default, data sintetis (FX-14), factory state | Dwi Yasa |
| **Hak akses dan backup** | Akun DB dengan hak minimum, prosedur backup dan uji restore | Dwi Yasa (dukungan Dedy untuk infrastruktur) |
| **Service dan logika bisnis** | `ScanStateMachine`, transaksi, locking, scheduler (purge, watchdog), controller, policy | Dedy Bhaskara dan Arya Putra |

**Titik serah-terima dengan backend:**
- Dwi menyerahkan migration per tahap sesuai jadwal (Bagian 18); nama tabel, kolom, dan nilai enum dibekukan melalui pemetaan di Bagian 7.
- Backend memberi tahu pola query yang memerlukan indeks (lihat 5.3) dan kebutuhan kolom baru lewat issue.
- Perubahan skema selalu berupa migration baru dan diberitahukan ke backend sebelum digabung.

**Aturan kerja:** setiap migration direview Dedy (nama, indeks, `down()`); pemetaan Bagian 7 direview Dedy dan Arya; satu migration untuk satu tabel; migration yang sudah digabung ke branch bersama tidak diedit.

*Catatan lingkup:* basis data ML (dataset, label, registry model; PRD ML Bagian 9) adalah database terpisah milik komponen ML dan bukan bagian PRD ini.

### 0.3 Dokumen Terpisah (di Luar PRD Ini)

| Dokumen | Hubungan dengan PRD Database |
|---|---|
| **PRD Backend** | Model data logis (Bagian 9), status scan (Bagian 6), retensi (8.6), konfigurasi (17) |
| **PRD ML** | Kontrak hasil (label, confidence, `model_version`) yang disimpan; basis data ML terpisah |
| **PRD HTML** | Tampilan memakai data dari skema ini lewat Laravel (status, hasil, riwayat) |
| **PRD Pengujian** | Memakai akun uji dan data sintetis yang disediakan seeder (FX-14); menguji perilaku, bukan skema |
| **PRD Dokumentasi** | Menerima ERD dan penjelasan tabel |

---

## 1. Ringkasan Eksekutif

### 1.1 Gambaran Dokumen

STIKSAFE-67 menerima file dari pengguna, menyimpannya di private storage, mengirimkannya ke layanan Machine Learning (ML) terpisah, memvalidasi respons ML, lalu menyimpan hasil dan riwayat di database. Dokumen ini menetapkan struktur database dan cara membangunnya lewat Laravel Migration. Developer tidak membuat tabel inti secara manual lewat phpMyAdmin.

| Komponen | Tanggung jawab terkait data |
|---|---|
| **Laravel** | Mengelola akun, role, autentikasi, upload, status scan, hasil, riwayat; menyimpan file di private storage (database hanya menyimpan metadata dan path relatif); memvalidasi respons ML sebelum disimpan; memastikan pengguna hanya mengakses scan miliknya |
| **ML Service** | Menganalisis file dan mengembalikan klasifikasi serta confidence bila tersedia; tidak mengakses database aplikasi dan bukan sumber data akun atau riwayat |
| **Database (MySQL)** | Menyimpan data aplikasi secara konsisten, dengan constraint sebagai lapisan pengaman terakhir |

### 1.2 Tujuan

1. Mendefinisikan seluruh skema database aplikasi lewat Laravel Migration yang dapat dijalankan berurutan pada database kosong.
2. Menjamin integritas data: PK/FK, unique, CHECK, dan aturan konsistensi status–hasil.
3. Menyelaraskan skema dengan kontrak API, ML, dan tampilan (ID publik, status, label, kode error).
4. Mendukung kebutuhan operasional: retry, watchdog, retensi, dashboard, dan audit.
5. Menegakkan keamanan data: hak akses DB minimum, tanpa secret di data, jejak yang tidak dapat diubah.
6. Menyediakan seeder dan factory yang aman untuk pengembangan dan pengujian.

### 1.3 Bukan Tujuan

- Basis data dan dataset ML (PRD ML Bagian 9).
- Implementasi controller, service, policy, dan scheduler (PRD Backend).
- Desain HTML/CSS (PRD HTML).
- Penyimpanan isi file di database (file selalu di private storage).
- Kebijakan retensi final yang belum disetujui; nilai pada dokumen ini adalah usulan (DEC-DB-08).
- Pengujian dengan malware sungguhan; data uji berupa data sintetis.

### 1.4 Prinsip Database

| Prinsip | Konsekuensi teknis |
|---|---|
| **Migration sebagai sumber kebenaran** | Semua perubahan skema lewat migration; phpMyAdmin hanya untuk inspeksi |
| **Integritas di lapisan DB** | Constraint (FOREIGN KEY, UNIQUE, CHECK) melengkapi validasi aplikasi, bukan menggantikannya |
| **Kegagalan bukan safe** | Skema tidak menyediakan jalur menyimpan hasil safe untuk scan gagal; scan failed tidak memiliki baris hasil |
| **Hasil immutable** | Baris hasil tidak diubah setelah ditulis; ditegakkan lewat service dan hak akses DB |
| **Jejak yang dapat ditelusuri** | Setiap percobaan dan perubahan status tercatat; riwayat tidak terhapus otomatis |
| **Data minimum** | Tidak menyimpan isi file, secret, token, atau payload ML mentah |
| **Hak akses minimum** | Aplikasi tidak memakai root; tabel jejak hanya INSERT/SELECT |
| **UTC dan konsisten** | Semua waktu disimpan UTC; nilai terbatas memiliki satu sumber (PHP enum + CHECK) |

---

## 2. Ruang Lingkup

### 2.1 Termasuk dalam PRD Database

| Area | Rincian |
|---|---|
| **Skema** | Tabel inti, tabel pendukung, kolom, tipe, PK/FK, unique, CHECK, indeks |
| **Migration** | Urutan, standar penulisan, `up()`/`down()`, prosedur menjalankan dan rollback |
| **Model Eloquent** | Relasi, casts, `$fillable`, `$hidden`, route key, enum |
| **Seeder dan factory** | Akun uji, versi model, pengaturan, data sintetis, factory state |
| **Integritas dan alur data** | Aturan transaksi, konsistensi status–hasil, locking, idempotensi |
| **Retensi** | Kolom dan indeks pendukung purge, anonimisasi, dan pembersihan |
| **Keamanan dan backup** | Akun DB, hak per tabel, enkripsi kolom rahasia, backup dan uji restore |
| **Pemeriksaan database** | Daftar pemeriksaan skema dan integritas untuk developer |

### 2.2 Tidak Termasuk

| Bagian | Penanggung jawab |
|---|---|
| Model ML, dataset, registry model ML | PRD ML |
| Controller, service, policy, scheduler command | PRD Backend |
| Tampilan HTML/CSS | PRD HTML |
| Pengujian fungsional dan keamanan dari sisi pengguna | PRD Pengujian |
| Penentuan kebijakan hukum retensi dan privasi | Pihak yang berwenang; dokumen ini hanya menyediakan mekanisme |

### 2.3 Pengembangan Lanjutan

Partisi tabel `audit_logs`/`scan_status_logs` per bulan, arsip data lama, read replica untuk dashboard, schema dump untuk mempercepat migrasi, pencarian teks penuh pada riwayat, pengumuman sistem (*announcements*), dan perpindahan engine (mis. PostgreSQL) bila diperlukan.

### 2.4 Asumsi

- Database: MySQL (InnoDB), dikelola dari Laravel; PRD Backend DEC-02 dijawab MySQL (DEC-DB-03).
- Aplikasi memakai UTC untuk semua kolom waktu; zona tampilan ditangani lapisan tampilan.
- Queue, session, dan cache memakai Redis; tabel DB untuk itu hanya cadangan (DEC-DB-04).
- Kapasitas awal mengikuti PRD Backend: ±5.000 scan/hari (±1,8 juta scan/tahun).
- Dwi Yasa mengerjakan database dan Backend mengerjakan service; keduanya sepakat pada Bagian 7.

---

## 3. Aktor, Peran, dan Hak Akses Database

### 3.1 Aktor

| Aktor | Jenis | Interaksi dengan database |
|---|---|---|
| **Aplikasi Laravel** (web, API, queue worker, scheduler) | Sistem | Membaca dan menulis data aplikasi memakai akun aplikasi |
| **Proses migrasi / CI** | Sistem | Menjalankan DDL memakai akun migrasi |
| **Job backup** | Sistem | Membaca data memakai akun backup |
| **Developer** | Manusia | Mengakses database lokal/dev; tidak memakai akun produksi untuk kerja harian |
| **Admin database** | Manusia | Mengelola akun DB, backup, dan restore (Dedy dengan dukungan Dwi) |
| **ML Service** | Sistem | Tidak mengakses database aplikasi |

### 3.2 Akun Database (Usulan)

| Akun | Dipakai oleh | Hak |
|---|---|---|
| `stiksafe_app` | Aplikasi Laravel | SELECT, INSERT, UPDATE, DELETE pada tabel aplikasi, dengan pengecualian 3.3; tanpa DDL dan tanpa GRANT |
| `stiksafe_migrate` | Migration/CI | CREATE, ALTER, DROP, INDEX, REFERENCES pada skema aplikasi; tidak dipakai oleh aplikasi saat berjalan |
| `stiksafe_backup` | Job backup | SELECT, SHOW VIEW, TRIGGER, LOCK TABLES (sesuai kebutuhan alat backup) |
| `stiksafe_maint` | Retensi arsip (audit log) | DELETE terbatas pada `audit_logs`/`scan_status_logs` untuk purge terjadwal |

Aplikasi tidak pernah memakai root. Kredensial hanya di `.env`/secret manager; `.env` berisi kredensial asli tidak dimasukkan ke repository.

### 3.3 Hak per Tabel untuk `stiksafe_app`

| Tabel | Hak aplikasi | Alasan |
|---|---|---|
| `users`, `scans`, `scan_attempts`, `model_versions`, `system_settings`, `scan_feedback`, `daily_scan_stats`, `idempotency_keys`, `ml_callback_events`, `notifications`, `personal_access_tokens`, `password_reset_tokens` | SELECT, INSERT, UPDATE, DELETE | Data operasional |
| `scan_results` | SELECT, INSERT, DELETE (tanpa UPDATE) | Hasil immutable (BR-09 Backend) |
| `audit_logs`, `scan_status_logs` | SELECT, INSERT (tanpa UPDATE/DELETE) | Append-only (FR-AUD-02 Backend); purge oleh `stiksafe_maint` |

---

## 4. Arsitektur Data dan Teknologi

### 4.1 Gambaran Komponen

```text
[Aplikasi Laravel (web, API, worker, scheduler)]
  ├── Akun stiksafe_app  ──> [MySQL: Skema Aplikasi]
  ├── Akun stiksafe_migrate ──> [Migration / CI]
  ├── Akun stiksafe_backup  ──> [Job Backup]
  ├── Redis: queue, session, cache
  ├── Private storage: file
  └── ML Service (tanpa akses DB)
```

Database hanya menyimpan metadata dan path relatif; isi file ada di private storage. ML tidak terhubung ke database aplikasi.

### 4.2 Teknologi dan Standar

| Komponen | Ketentuan |
|---|---|
| **Framework** | Laravel (versi stabil yang masih didukung; DEC-01 Backend) |
| **Database engine** | MySQL 8.4 LTS disarankan. MySQL 8.0 telah mencapai akhir masa dukungan resmi pada April 2026. Fitur yang dipakai (CHECK yang ditegakkan, kolom generated, JSON, utf8mb4) tersedia sejak 8.0.16 (DEC-DB-03) |
| **Storage engine** | InnoDB |
| **Charset dan collation** | `utf8mb4` dengan `utf8mb4_unicode_ci` (bawaan Laravel). Kolom identitas teknis (`public_id`, `sha256`, ID permintaan) memakai `ascii` + `ascii_bin` |
| **Schema management** | Laravel Migration (Schema Builder) |
| **ORM** | Eloquent |
| **Strict mode** | `strict` aktif pada koneksi Laravel (`STRICT_TRANS_TABLES`/`STRICT_ALL_TABLES`) |
| **Waktu** | `config('app.timezone') = UTC`; zona sesi database `+00:00` |
| **File storage** | Laravel private disk |
| **Primary key internal** | `BIGINT UNSIGNED` auto increment |
| **Identitas publik** | `public_id` ULID (`CHAR(26)`, unik) untuk entitas yang tampil di URL/API (`users`, `scans`) |

### 4.3 Konvensi Penamaan dan Tipe

| Aspek | Konvensi |
|---|---|
| **Tabel** | Jamak, snake_case (`scan_attempts`) |
| **Kolom** | snake_case; FK memakai `{nama_singular}_id` |
| **Waktu** | `TIMESTAMP NULL` (UTC); kolom `created_at`/`updated_at` standar Laravel kecuali tabel append-only (hanya `created_at`) |
| **Boolean** | `TINYINT(1)` (`boolean()`) |
| **Nilai terbatas** | `VARCHAR` + CHECK, bukan tipe ENUM MySQL (mudah diubah lewat migration dan sejalan dengan PHP enum) |
| **Hash** | `CHAR(64)` huruf kecil heksadesimal (`sha256`) |
| **JSON** | Hanya untuk data terstruktur opsional (`reason_codes`, `metadata`, `response_body`) |
| **Nama indeks/constraint** | Eksplisit: `{tabel}_{kolom}_{jenis}` (mis. `scans_user_id_created_at_index`, `scans_status_check`) |
| **Panjang string** | Selalu eksplisit; tidak memakai TEXT untuk nilai pendek |

---

## 5. Kebutuhan Fungsional

### 5.1 Skema dan Migration (SKM)

| ID | Kebutuhan | Prio | Kriteria penerimaan |
|---|---|:--:|---|
| `FR-DB-SKM-01` | Seluruh tabel inti dibuat lewat Laravel Migration; tidak ada tabel inti dibuat manual lewat phpMyAdmin | P0 | `php artisan migrate:status` menunjukkan semua migration Ran; skema server = hasil migration |
| `FR-DB-SKM-02` | Migration dapat dijalankan berurutan pada database kosong | P0 | `php artisan migrate` pada MySQL kosong berhasil tanpa kesalahan |
| `FR-DB-SKM-03` | Setiap migration memiliki `up()` dan `down()`; `down()` membatalkan dalam urutan dependensi yang benar | P0 | `migrate:rollback` lalu migrate ulang berhasil |
| `FR-DB-SKM-04` | Satu migration untuk satu tabel aplikasi | P0 | Tidak ada migration yang membuat beberapa tabel aplikasi sekaligus |
| `FR-DB-SKM-05` | Migration yang sudah diterapkan pada lingkungan bersama tidak diedit; perubahan berupa migration baru | P0 | Riwayat Git tidak memuat perubahan pada migration lama setelah digabung |
| `FR-DB-SKM-06` | Tabel framework memakai migration bawaan/starter kit yang disesuaikan | P0 | Hanya satu migration per tabel framework |
| `FR-DB-SKM-07` | Nama FK, indeks, dan CHECK eksplisit sesuai 4.3 | P0 | Terlihat pada `SHOW CREATE TABLE` |
| `FR-DB-SKM-08` | Perintah destruktif dilarang di produksi memakai `DB::prohibitDestructiveCommands(app()->isProduction())` | P0 | Perintah gagal di lingkungan produksi |
| `FR-DB-SKM-09` | Perubahan berisiko memakai pola expand/contract | P1 | Terdokumentasi pada migration terkait |
| `FR-DB-SKM-10` | SQL ditinjau dengan `php artisan migrate --pretend` sebelum deploy | P1 | Hasil peninjauan dicatat pada PR |

### 5.2 Integritas Data (INT)

| ID | Kebutuhan | Prio | Kriteria penerimaan |
|---|---|:--:|---|
| `FR-DB-INT-01` | Setiap tabel memiliki PK; FK eksplisit dengan aturan ON DELETE sesuai Bagian 8 | P0 | FK tidak valid ditolak database |
| `FR-DB-INT-02` | Nilai terbatas ditegakkan CHECK (7.4) | P0 | Nilai di luar daftar ditolak |
| `FR-DB-INT-03` | `scan_results.scan_id` unik: maksimal satu hasil akhir per scan | P0 | Penyimpanan hasil kedua untuk scan yang sama ditolak |
| `FR-DB-INT-04` | `confidence` bernilai NULL atau 0–1 | P0 | Nilai di luar rentang ditolak (CHECK) |
| `FR-DB-INT-05` | Status completed ⇔ tepat satu baris `scan_results`; status lain tidak memiliki baris hasil | P0 | Query konsistensi mengembalikan nol baris |
| `FR-DB-INT-06` | Hanya satu percobaan berstatus running per scan (kolom generated + unique) | P0 | Percobaan running kedua untuk scan yang sama ditolak |
| `FR-DB-INT-07` | `idempotency_keys` unik pada (`user_id`, `endpoint`, `idempotency_key`) | P0 | Permintaan ulang tidak membuat scan ganda |
| `FR-DB-INT-08` | `scans.ml_request_id` dan `ml_callback_events.ml_request_id` unik | P0 | Callback/hasil ganda terdeteksi |
| `FR-DB-INT-09` | `scans.sha256` wajib (NOT NULL) dan `file_size_bytes` > 0 | P0 | Baris tanpa hash atau ukuran nol ditolak |
| `FR-DB-INT-10` | Riwayat tidak terhapus karena penghapusan user: `scans.user_id` memakai RESTRICT; penghapusan akun lewat anonimisasi | P0 | Penghapusan fisik user yang memiliki scan ditolak |
| `FR-DB-INT-11` | Perubahan status dan penulisan `scan_status_logs` dalam satu transaksi | P0 | Pembaruan ganda bersamaan tidak merusak status |
| `FR-DB-INT-12` | Worker ganda tidak menyimpan hasil dua kali: SELECT … FOR UPDATE pada baris scan + unique `scan_id` | P0 | Hasil tersimpan sekali |
| `FR-DB-INT-13` | `daily_scan_stats` unik per (`stat_date`, user atau global) | P1 | Dua baris global pada tanggal yang sama ditolak |

### 5.3 Indeks dan Performa (IDX)

| Pola query | Tabel | Indeks |
|---|---|---|
| Riwayat milik user (terbaru dulu) | `scans` | (`user_id`, `created_at`) |
| Filter status/hasil per user | `scans` | (`user_id`, `status`, `created_at`) |
| Daftar scan Admin menurut status | `scans` | (`status`, `created_at`) |
| Watchdog: percobaan running tanpa heartbeat | `scan_attempts` | (`status`, `heartbeat_at`) |
| Purge file kedaluwarsa | `scans` | (`file_purged_at`, `file_expires_at`) |
| Pencarian hash (prefix) | `scans` | (`sha256`) |
| Distribusi hasil | `scan_results` | (`label`, `created_at`) |
| Filter audit log | `audit_logs` | (`actor_user_id`, `created_at`), (`action`, `created_at`), (`target_type`, `target_id`) |
| Notifikasi belum dibaca | `notifications` | (`notifiable_type`, `notifiable_id`, `read_at`, `created_at`) |
| Pemangkasan token/kunci | `personal_access_tokens`, `idempotency_keys` | (`expires_at`) |
| Penghapusan akun jatuh tempo | `users` | (`deletion_scheduled_at`) |

### 5.4 Seeder dan Factory (SDR)

| ID | Kebutuhan | Prio | Kriteria penerimaan |
|---|---|:--:|---|
| `FR-DB-SDR-01` | Seeder akun uji sesuai PRD Pengujian 3.2: User-A, User-B, User-Lock, User-Suspend, Admin-1, Admin-2 | P0 | Hanya berjalan di lingkungan local/staging; password aman |
| `FR-DB-SDR-02` | `ModelVersionSeeder` dan `SettingsSeeder` dengan nilai default dari PRD Backend Bagian 17 | P0 | Tidak memuat secret |
| `FR-DB-SDR-03` | Seeder aman dijalankan berulang (`updateOrCreate`/`firstOrCreate`) | P0 | Menjalankan dua kali tidak membuat duplikat |
| `FR-DB-SDR-04` | Factory: `UserFactory`, `ScanFactory`, `ScanResultFactory`, `ScanAttemptFactory` | P0 | State menghasilkan data konsisten |
| `FR-DB-SDR-05` | `DemoScanSeeder`: ±30 scan sintetis dengan variasi status & hasil (FX-14) | P0 | Dapat dibuka dari akun uji |
| `FR-DB-SDR-06` | Admin produksi dibuat lewat perintah `stiksafe:create-admin`, bukan seeder | P0 | Guard environment aktif |
| `FR-DB-SDR-07` | Tidak ada API key, password produksi, atau file/malware sungguhan pada seeder | P0 | Hasil ML berupa mock data |
| `FR-DB-SDR-08` | Seeder contoh untuk `scan_feedback` dan `notifications` | P1 | Mendukung uji F8 dan F9 PRD Pengujian |

---

## 6. Siklus Hidup Data Scan

### 6.1 Status Scan dan Kolom Terkait

| Status | Kolom yang terisi | Baris `scan_results` | Terminal? |
|---|---|:--:|:--:|
| `queued` | `queued_at`; `started_at`, `completed_at`, `error_*` kosong | Tidak ada | Tidak |
| `processing` | `queued_at`, `started_at`; ada satu percobaan running | Tidak ada | Tidak |
| `completed` | `completed_at`; `error_code` dan `error_message` kosong | Tepat satu | Ya |
| `failed` | `completed_at` (waktu berhenti); `error_code` dan `error_message` aman | Tidak ada | Ya* |
| `cancelled` | `completed_at`; `cancel_requested_at` bila dibatalkan saat proses | Tidak ada | Ya |
| `rejected` | `completed_at`; `error_code` | Tidak ada | Ya |

*\* `failed` dapat kembali ke `queued` lewat retry manual Admin.*

### 6.2 Query Pemeriksaan Konsistensi (Hasil Benar = Nol Baris)

```sql
-- 1. Scan completed tanpa hasil
SELECT s.id FROM scans s
LEFT JOIN scan_results r ON r.scan_id = s.id
WHERE s.status = 'completed' AND r.id IS NULL;

-- 2. Hasil untuk scan yang bukan completed
SELECT r.id FROM scan_results r
JOIN scans s ON s.id = r.scan_id
WHERE s.status <> 'completed';

-- 3. Scan processing tanpa percobaan running
SELECT s.id FROM scans s
LEFT JOIN scan_attempts a ON a.scan_id = s.id AND a.status = 'running'
WHERE s.status = 'processing' AND a.id IS NULL;

-- 4. attempts_count tidak sama dengan jumlah percobaan
SELECT s.id FROM scans s
JOIN (SELECT scan_id, COUNT(*) AS c FROM scan_attempts GROUP BY scan_id) x ON x.scan_id = s.id
WHERE s.attempts_count <> x.c;
```

---

## 7. Keselarasan Nilai Terbatas (Enum & CHECK)

| Enum / Kolom | Nilai |
|---|---|
| `UserRole` — `users.role` | `user`, `admin` |
| `UserStatus` — `users.status` | `pending_verification`, `active`, `suspended`, `inactive`, `pending_deletion`, `deleted` |
| `ScanStatus` — `scans.status` | `queued`, `processing`, `completed`, `failed`, `cancelled`, `rejected` |
| `ScanLabel` — `scan_results.label` | `safe`, `suspicious`, `malicious`, `unknown` |
| `AttemptStatus` — `scan_attempts.status` | `running`, `succeeded`, `failed`, `timed_out`, `cancelled` |
| `AttemptTrigger` — `scan_attempts.trigger` | `initial`, `auto_retry`, `manual_retry` |
| `FeedbackType` — `scan_feedback.type` | `false_positive`, `false_negative`, `other` |
| `FeedbackStatus` — `scan_feedback.status` | `open`, `reviewed`, `closed` |
| `AuditActorType` — `audit_logs.actor_type` | `user`, `admin`, `system`, `service` |
| `AuditOutcome` — `audit_logs.outcome` | `success`, `failure` |
| `AuditSeverity` — `audit_logs.severity` | `info`, `warn`, `critical` |
| `SettingValueType` — `system_settings.value_type` | `string`, `integer`, `boolean`, `json` |

---

## 8. Spesifikasi Tabel

### 8.0 Aturan Foreign Key

| Foreign key | Induk | ON DELETE | Alasan |
|---|---|---|---|
| `scans.user_id` | `users` | RESTRICT | Riwayat tidak terhapus karena penghapusan user |
| `scan_results.scan_id` | `scans` | CASCADE | Hasil mengikuti scan |
| `scan_results.model_version_id` | `model_versions` | RESTRICT | Versi yang dipakai hasil tidak boleh hilang |
| `scan_attempts.scan_id` | `scans` | CASCADE | Percobaan mengikuti scan |
| `scan_attempts.triggered_by` | `users` | SET NULL | Jejak tetap ada bila akun dihapus |
| `scan_status_logs.scan_id` | `scans` | CASCADE | Log mengikuti scan |
| `scan_status_logs.actor_user_id` | `users` | SET NULL | Jejak tetap ada |
| `idempotency_keys.user_id` | `users` | CASCADE | Data sementara |
| `idempotency_keys.scan_id` | `scans` | SET NULL | Kunci tetap valid |
| `audit_logs.actor_user_id` | `users` | SET NULL | Audit tidak boleh hilang |
| `system_settings.updated_by` | `users` | SET NULL | Pengaturan tetap ada |
| `scan_feedback.scan_id` | `scans` | CASCADE | Mengikuti scan |
| `scan_feedback.user_id` | `users` | RESTRICT | Umpan balik tidak hilang diam-diam |
| `scan_feedback.reviewed_by` | `users` | SET NULL | Jejak tetap ada |
| `daily_scan_stats.user_id` | `users` | CASCADE | Agregat per user |

### 8.1 users
- `id` (BIGINT UNSIGNED, PK, auto-increment)
- `public_id` (CHAR(26) ascii_bin, ULID, UNIQUE)
- `name` (VARCHAR(120), NOT NULL)
- `email` (VARCHAR(254), NOT NULL, UNIQUE, lowercase)
- `email_verified_at` (TIMESTAMP NULL)
- `password` (VARCHAR(255), NOT NULL, hash)
- `role` (VARCHAR(16), DEFAULT 'user')
- `status` (VARCHAR(24), DEFAULT 'active')
- `status_reason` (VARCHAR(255) NULL)
- `two_factor_secret`, `two_factor_recovery_codes` (TEXT NULL, encrypted)
- `two_factor_confirmed_at`, `last_login_at`, `last_login_ip`
- `deletion_requested_at`, `deletion_scheduled_at`
- `remember_token`, `created_at`, `updated_at`, `deleted_at`

### 8.2 model_versions
- `id` (BIGINT UNSIGNED, PK)
- `version` (VARCHAR(64), UNIQUE)
- `model_name` (VARCHAR(150), NOT NULL)
- `preprocessing_version` (VARCHAR(64) NULL)
- `confidence_type` (VARCHAR(30) NULL)
- `description` (TEXT NULL)
- `is_active` (TINYINT(1), DEFAULT 0)
- `released_at`, `first_seen_at`, `created_at`, `updated_at`

### 8.3 scans
- `id` (BIGINT UNSIGNED, PK)
- `public_id` (CHAR(26) ascii_bin, ULID, UNIQUE)
- `user_id` (BIGINT UNSIGNED, FK → `users.id`, RESTRICT)
- `original_filename` (VARCHAR(255), sanitized)
- `stored_filename` (VARCHAR(255), internal UUID)
- `storage_disk` (VARCHAR(50), default private)
- `storage_path` (VARCHAR(500) NULL, NULL after purge)
- `file_extension` (VARCHAR(20) NULL)
- `detected_mime` (VARCHAR(150) NULL)
- `file_size_bytes` (BIGINT UNSIGNED, CHECK > 0)
- `sha256` (CHAR(64) ascii_bin, NOT NULL)
- `status` (VARCHAR(16), DEFAULT 'queued')
- `ml_request_id` (CHAR(36) ascii_bin NULL, UNIQUE)
- `error_code` (VARCHAR(64) NULL), `error_message` (VARCHAR(500) NULL)
- `attempts_count` (SMALLINT UNSIGNED, DEFAULT 0)
- `cancel_requested_at`, `queued_at`, `started_at`, `completed_at`
- `file_expires_at`, `file_purged_at`, `user_deleted_at`
- `created_at`, `updated_at`

### 8.4 scan_results
- `id` (BIGINT UNSIGNED, PK)
- `scan_id` (BIGINT UNSIGNED, FK → `scans.id`, CASCADE, UNIQUE)
- `model_version_id` (BIGINT UNSIGNED NULL, FK → `model_versions.id`, RESTRICT)
- `label` (VARCHAR(16), CHECK: safe, suspicious, malicious, unknown)
- `confidence` (DECIMAL(6,5) NULL, CHECK: NULL atau 0.00000 - 1.00000)
- `threat_name` (VARCHAR(255) NULL), `threat_category` (VARCHAR(64) NULL)
- `reason_codes` (JSON NULL)
- `result_summary` (TEXT NULL)
- `raw_response_reference` (VARCHAR(255) NULL)
- `scanned_at`, `created_at`, `updated_at`

### 8.5 scan_attempts
- `id` (BIGINT UNSIGNED, PK)
- `scan_id` (BIGINT UNSIGNED, FK → `scans.id`, CASCADE)
- `attempt_number` (SMALLINT UNSIGNED)
- `trigger` (VARCHAR(16)), `status` (VARCHAR(16))
- `ml_request_id` (CHAR(36) ascii_bin NULL)
- `worker_host` (VARCHAR(64) NULL)
- `http_status` (SMALLINT UNSIGNED NULL)
- `error_code` (VARCHAR(64) NULL), `error_message` (VARCHAR(500) NULL)
- `started_at`, `heartbeat_at`, `finished_at`
- `duration_ms` (INT UNSIGNED NULL)
- `triggered_by` (BIGINT UNSIGNED NULL, FK → `users.id`, SET NULL)
- `running_scan_id` (BIGINT UNSIGNED NULL, virtualAs: `IF(status = 'running', scan_id, NULL)`)
- `created_at`, `updated_at`

### 8.6 scan_status_logs
- `id` (BIGINT UNSIGNED, PK)
- `scan_id` (BIGINT UNSIGNED, FK → `scans.id`, CASCADE)
- `from_status` (VARCHAR(16) NULL), `to_status` (VARCHAR(16))
- `message` (VARCHAR(500) NULL)
- `actor_user_id` (BIGINT UNSIGNED NULL, FK → `users.id`, SET NULL)
- `created_at` (TIMESTAMP)

### 8.7 idempotency_keys
- `id` (BIGINT UNSIGNED, PK)
- `user_id` (BIGINT UNSIGNED, FK → `users.id`, CASCADE)
- `endpoint` (VARCHAR(100)), `idempotency_key` (VARCHAR(64) ascii_bin)
- `request_hash` (CHAR(64) ascii_bin)
- `response_code` (SMALLINT UNSIGNED NULL), `response_body` (JSON NULL)
- `scan_id` (BIGINT UNSIGNED NULL, FK → `scans.id`, SET NULL)
- `expires_at`, `created_at`

### 8.8 audit_logs
- `id` (BIGINT UNSIGNED, PK)
- `actor_type` (VARCHAR(16)), `actor_user_id` (BIGINT UNSIGNED NULL, FK → `users.id`, SET NULL)
- `action` (VARCHAR(100))
- `target_type` (VARCHAR(100) NULL), `target_id` (VARCHAR(100) NULL)
- `outcome` (VARCHAR(16)), `severity` (VARCHAR(16))
- `description` (VARCHAR(500) NULL)
- `ip_address` (VARCHAR(45) NULL), `user_agent` (VARCHAR(500) NULL), `request_id` (VARCHAR(64) NULL)
- `metadata` (JSON NULL)
- `created_at` (TIMESTAMP)

### 8.9 system_settings
- `id` (BIGINT UNSIGNED, PK)
- `setting_key` (VARCHAR(150), UNIQUE)
- `setting_value` (JSON NULL), `value_type` (VARCHAR(16))
- `description` (VARCHAR(500) NULL), `is_public` (TINYINT(1), DEFAULT 0)
- `updated_by` (BIGINT UNSIGNED NULL, FK → `users.id`, SET NULL)
- `created_at`, `updated_at`

### 8.10 scan_feedback (P1)
- `id` (BIGINT UNSIGNED, PK)
- `scan_id` (BIGINT UNSIGNED, FK → `scans.id`, CASCADE)
- `user_id` (BIGINT UNSIGNED, FK → `users.id`, RESTRICT)
- `type` (VARCHAR(20)), `comment` (VARCHAR(1000) NULL), `status` (VARCHAR(16))
- `reviewed_by` (BIGINT UNSIGNED NULL, FK → `users.id`, SET NULL), `reviewed_at`
- `created_at`, `updated_at`

### 8.11 daily_scan_stats (P1)
- `id` (BIGINT UNSIGNED, PK)
- `stat_date` (DATE)
- `user_id` (BIGINT UNSIGNED NULL, FK → `users.id`, CASCADE)
- `user_key` (BIGINT UNSIGNED, virtualAs: `IFNULL(user_id, 0)`)
- `total_count`, `safe_count`, `suspicious_count`, `malicious_count`, `unknown_count` (INT UNSIGNED, DEFAULT 0)
- `failed_count`, `cancelled_count`, `rejected_count` (INT UNSIGNED, DEFAULT 0)
- `avg_duration_ms` (INT UNSIGNED NULL)
- `created_at`, `updated_at`

---

## 9. Urutan dan Standar Migration

Urutan eksekusi migration:
1. `users` (bawaan, disesuaikan) + `password_reset_tokens` + `sessions`
2. `personal_access_tokens` (Sanctum)
3. `create_model_versions_table`
4. `create_scans_table`
5. `create_scan_results_table`
6. `create_scan_attempts_table`
7. `create_scan_status_logs_table`
8. `create_idempotency_keys_table`
9. `create_audit_logs_table`
10. `create_system_settings_table`
11. `notifications` (bawaan Laravel)
12. `create_scan_feedback_table`
13. `create_daily_scan_stats_table`
14. Infrastruktur driver (`jobs`, `cache`, `failed_jobs`, dsb.)

---

## 10. Catatan Penyelarasan Kode Riil di STIKSAFE-67-Laravel

Berdasarkan pengecekan folder kerja `C:\Tugas Laravel\STIKSAFE-67\STIKSAFE-67-Laravel`:
1. **Migration Tersedia:**
   - `0001_01_01_000000_create_users_table.php` (termasuk password_reset_tokens dan sessions)
   - `2026_11_14_123542_create_scans_table.php`
   - `2026_11_14_123543_create_personal_access_tokens_table.php`
   - `2026_11_14_123544_create_scan_attempts_table.php`
   - `2026_11_14_124854_add_role_and_status_to_users_table.php`
   - `2026_11_15_000001_create_audit_logs_table.php`
2. **Kesesuaian dengan Todo List Rekan Tim:**
   - Tabel `notifications` perlu dibuatkan migration resmi `php artisan make:notifications-table`.
   - Tabel `scan_feedback` disiapkan untuk fitur *Feedback hasil scan* (Pekerjaan 1).
   - Tabel `model_versions` dan `scan_status_logs` disiapkan sesuai PRD untuk menjaga integritas transisi scan.
   - Status scan dan enum hasil (`safe`, `suspicious`, `malicious`, `unknown`) telah selaras dengan PHP Enum `ScanStatus` dan `ScanResultLabel`.
