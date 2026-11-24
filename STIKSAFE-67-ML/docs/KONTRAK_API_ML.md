# Kontrak API ML Service STIKSAFE-67 (untuk Backend)

Status: **STAGING**, bukan production. Model file: EMBER2024 resmi (kalibrasi lokal). Model URL: lemah (lihat bagian 8).
Versi dokumen ini mengikuti kode `stiksafe-ml` v12. Perubahan di luar PRD v2.0 ditandai **[PERLU PERSETUJUAN]**.

## 1. Dasar
- Satu layanan, satu alamat, satu token. Hanya dipanggil dari backend (jaringan privat). Browser tidak boleh memanggil langsung.
- Autentikasi: header `Authorization: Bearer <token>`. Maksimal 2 token aktif (rotasi). Tanpa/salah token -> `401 ML_AUTH_FAILED`.
- Setiap respons membawa header `X-Request-ID`. Respons 429/503 membawa `Retry-After` (detik).
- Semua respons JSON. Kegagalan SELALU berbentuk error terstruktur (bagian 5) dan TIDAK PERNAH berisi label.

## 2. Endpoint
| Metode | Path | Fungsi |
|---|---|---|
| POST | `/v1/scans` | Pindai file (fitur 1 + fitur 3) |
| POST | `/v1/urls` | Periksa URL (fitur 2) **[PERLU PERSETUJUAN: endpoint baru, di luar PRD v2.0]** |
| GET | `/v1/health` | 200 `ready` / 503 `not_ready` |
| GET | `/v1/model-info` | Info model file |
| GET | `/v1/url-info` | Info lapis URL (model dimuat?, ukuran daftar hitam) |

## 3. POST /v1/scans  (multipart/form-data)
Field `file` = berkas mentah. Field `metadata` = string JSON:
| Field | Wajib | Keterangan |
|---|---|---|
| `scan_id` | ya | ULID (26 karakter Crockford base32) |
| `request_id` | ya | UUID; kunci idempotensi (hasil sukses di-cache 1 jam) |
| `sha256` | ya | hex 64; dibandingkan dengan hash yang dihitung layanan |
| `size_bytes` | ya | integer > 0; harus sama dengan ukuran sebenarnya |
| `submitted_at` | ya | ISO-8601 UTC berakhiran `Z` |
| `declared_extension` | tidak | ekstensi terakhir nama file (petunjuk saja; isi file yang menentukan) |
| `extension_chain` | tidak | daftar ekstensi dari nama file, mis. `["pdf","exe"]`; maks 8 elemen, tiap elemen <= 32 karakter. **Bukan nama file.** **[PERLU PERSETUJUAN]** |
| `detected_mime`, `callback_url` | tidak | callback_url wajib host yang ada di allowlist |

Respons sukses 200:
```json
{"request_id":"...","scan_id":"...","status":"completed","sha256":"...",
 "result":{"label":"safe|suspicious|malicious|unknown","confidence":0.97,"threat_name":null,
           "threat_category":null,"reason_codes":["DOUBLE_EXTENSION"]},
 "model_version":"ember2024-pe-baseline-1","preprocessing_version":"thrember-ember-feature-v3",
 "scanned_at":"2026-11-20T09:33:22Z","duration_ms":2876}
```
- Jalur MODEL ML hanya untuk Windows PE (`exe`, `dll`). Format lain diperiksa oleh jalur ATURAN (bagian 3b); hanya format yang tidak
  punya pemeriksa DAN bernama tidak mencurigakan yang dibalas `422 ML_UNSUPPORTED_FILE` (BUKAN label).
- Setiap respons sukses kini memuat `analysis`: `{"method":"model"|"rules","family":"pe|pdf|zip|...", ...}` **[PERLU PERSETUJUAN]**.
  Gunakan `analysis.method` untuk memilih teks tampilan (bagian 6).
- `confidence`: untuk `malicious`/`suspicious` = skor model; untuk `safe` = 1 - skor; untuk `unknown` dan label hasil aturan = `null`. **[PERLU PERSETUJUAN: tafsiran DEC-ML-09]**
- `threat_name`/`threat_category` selalu `null` (model biner, tidak mengenali keluarga malware).

reason_codes file: `PARSE_PARTIAL`, `FORMAT_MISMATCH`, `ENCRYPTED_CONTENT`, `LIMIT_EXCEEDED`, `LOW_FEATURE_COVERAGE`,
`OUT_OF_DISTRIBUTION`, plus **[PERLU PERSETUJUAN]** `DOUBLE_EXTENSION` (laporan.pdf.exe), `DISGUISED_EXECUTABLE` (isi PE tetapi
ekstensi dokumen/gambar), `ODD_EXTENSION_CHARS`. Dua kode pertama dari tiga ini menaikkan `safe`/`unknown` menjadi minimal `suspicious`
(confidence `null`); `malicious`/`suspicious` tidak pernah diturunkan.

## 3b. Jalur ATURAN untuk file selain PE  **[PERLU PERSETUJUAN: perilaku baru]**
Isi file dikenali dari byte-nya (bukan ekstensi). Tidak ada yang dieksekusi; berjalan di proses anak dengan batas waktu.
Setiap temuan memberi poin; **total >= 2 -> `suspicious`**; selain itu `unknown`. Jalur ini **TIDAK PERNAH** menghasilkan `safe` atau `malicious`
(bukan pemindai malware). Tanpa temuan sama sekali: `unknown` + `NO_RISK_INDICATORS` (artinya hanya "tidak ada ciri berisiko pada pemeriksaan dasar").
Respons: `model_version`/`preprocessing_version` = `filerules-v1`, `confidence` = `null`, `analysis` = `{"method":"rules","family":...,"rule_points":N,"coverage":0.0-1.0}`.
| Format | Yang diperiksa | reason_codes utama |
|---|---|---|
| PDF | JavaScript, aksi otomatis (OpenAction/AA), Launch, file tertanam, RichMedia, nama disamarkan (#xx), isi stream terkompresi, enkripsi | `PDF_JAVASCRIPT` `PDF_AUTO_ACTION` `PDF_LAUNCH_ACTION` `PDF_EMBEDDED_FILE` `PDF_RICH_MEDIA` `PDF_OBFUSCATED_NAMES` |
| ZIP / OOXML (docx, xlsx, pptx, ...) | rasio kompresi tak wajar & terlalu banyak entri (bom zip), path traversal, enkripsi, executable/ekstensi ganda di dalam; Office: makro, template eksternal, objek tertanam, ActiveX | `ARCHIVE_*` `OFFICE_MACRO` `OFFICE_EXTERNAL_TEMPLATE` `OFFICE_EMBEDDED_OBJECT` `OFFICE_ACTIVEX` `EXECUTABLE_PACKAGE` |
| OLE (doc/xls/ppt lama) | makro VBA, Equation Editor, objek Package | `OFFICE_MACRO` `OFFICE_EQUATION_EDITOR` |
| RTF | objek tertanam (`\objdata`) | `RTF_EMBEDDED_OBJECT` |
| LNK | semua jalan pintas Windows (2 poin) | `LNK_FILE` |
| JPEG/PNG/GIF | payload ditempel setelah akhir gambar | `IMAGE_EMBEDDED_PAYLOAD` `IMAGE_TRAILING_DATA` |
| Teks / skrip / SVG | pola PowerShell/WSH/JS berisiko per kategori, blob base64, `<script>` di SVG | `SCRIPT_RISKY_PATTERN` `SCRIPT_OBFUSCATED` |
Selain itu, untuk SEMUA jenis: `FORMAT_MISMATCH` (+1 poin, ekstensi terdeklarasi tidak cocok dengan isi), `DOUBLE_EXTENSION` (+2 poin, dari `extension_chain`;
berlaku juga bila isi tidak didukung, mis. ELF bernama `laporan.pdf.exe` -> `suspicious`, bukan 422).
Tanpa pemeriksa dan nama tidak mencurigakan (ELF, RAR, 7z, gzip, WebP, biner tak dikenal) -> `422 ML_UNSUPPORTED_FILE`.
Catatan: makro Office = 2 poin (`suspicious`) karena risikonya tinggi untuk unggahan web, meski dokumen makro yang sah akan ikut tertandai.

## 4. POST /v1/urls  (application/json)
Request: `{"request_id":"<uuid>","url":"https://..."}` (http/https saja; maks 2048 karakter; tanpa skema akan diberi `http://`).
Respons 200:
```json
{"request_id":"...","status":"completed",
 "result":{"label":"unknown","confidence":null,"reason_codes":["URL_NO_INDICATION"],"rule_points":0,"model_points":0},
 "target":{"scheme":"https","host":"www.google.com","host_is_ip":false,"normalized_sha256":"..."},
 "model_version":"url-lgbm-hostonly-1","preprocessing_version":"urlfeat-v2","scanned_at":"...","duration_ms":21}
```
- **Layanan TIDAK PERNAH membuka/mengunduh URL.** Hanya teks URL yang dianalisis. URL penuh tidak ditulis di log (hanya hash + panjang host).
- Tiga lapis: (1) daftar hitam, kecocokan PERSIS -> `malicious`; (2) aturan berpoin; (3) model = penguat +1 poin (bukan penentu).
  Total poin >= 2 -> `suspicious`.
- `safe` TIDAK PERNAH keluar secara default (`ML_URL_ALLOW_SAFE=false`): tanpa indikasi -> `unknown` + `URL_NO_INDICATION`.
- `confidence` URL = skor model bila model ikut menyumbang poin, selain itu `null`. Bukan "keyakinan label".
- reason_codes URL: `URL_BLOCKLIST_MATCH`, `URL_IP_HOST`, `URL_USERINFO`, `URL_EXEC_DOWNLOAD`, `URL_PUNYCODE`, `URL_MANY_SUBDOMAINS`,
  `URL_NONSTANDARD_PORT`, `URL_SHORTENER`, `URL_SUSPICIOUS_KEYWORDS`, `URL_MODEL_ELEVATED`, `URL_MODEL_HIGH`, `URL_NO_INDICATION`, `URL_NO_MODEL`.
- URL tidak valid (skema selain http/https, kosong, > 2048, karakter kontrol) -> `422 ML_INVALID_REQUEST`.

## 5. Error (body: `{request_id, scan_id?, status:"failed", error:{code,message,retryable}, model_version}`)
| Kode | HTTP | retryable | Arti |
|---|---|---|---|
| ML_AUTH_FAILED | 401 | tidak | token salah/tidak ada |
| ML_INVALID_REQUEST | 422 | tidak | metadata/URL tidak sesuai kontrak, ukuran tidak cocok |
| ML_FILE_TOO_LARGE | 413 | tidak | melebihi batas (default 25 MB) |
| ML_HASH_MISMATCH | 422 | ya | hash terhitung != metadata |
| ML_UNSUPPORTED_FILE | 422 | tidak | format tanpa pemeriksa (bukan PE, dan bukan format di bagian 3b) dan nama tidak mencurigakan |
| ML_FILE_CORRUPT | 422 | tidak | file kosong |
| ML_PREPROCESSING_FAILED | 500 | ya | ekstraksi fitur gagal/timeout |
| ML_MODEL_UNAVAILABLE | 503 | ya | model belum termuat (`Retry-After`) |
| ML_INFERENCE_TIMEOUT | 504 | ya | melewati batas waktu total |
| ML_INFERENCE_FAILED | 500 | ya | inferensi gagal |
| ML_INVALID_MODEL_OUTPUT | 500 | ya | skor tidak valid |
| ML_OVERLOADED | 429 | ya | slot penuh, atau request_id sama sedang diproses (`Retry-After`) |
| ML_INTERNAL_ERROR | 500 | ya | tak terduga (detail hanya di log layanan) |
Aturan backend: **error TIDAK PERNAH ditampilkan atau disimpan sebagai `safe`.**

## 6. Saran teks tampilan (jangan sebut "aman")
| Label | Teks |
|---|---|
| safe (file) | "Tidak terdeteksi ancaman oleh pemindai kami" (bukan "aman") |
| unknown + `analysis.method="rules"` + `NO_RISK_INDICATORS` | "Pemeriksaan dasar: tidak ditemukan ciri berisiko. Ini bukan pemindaian malware dan bukan jaminan aman" |
| suspicious + `analysis.method="rules"` | "Ditemukan ciri berisiko" + daftar alasan dari `reason_codes` |
| unknown | "Tidak dapat dipastikan" (URL: "Tidak ada indikasi ancaman yang kami kenali") |
| suspicious | "Mencurigakan, hati-hati" |
| malicious | "Berbahaya" |
Alasan: malware yang sengaja dibuat licin (challenge set) hanya tertandai ~58%, jadi `safe` bukan jaminan.

## 7. Konfigurasi (environment)
`ML_ENV`, `ML_SERVICE_TOKENS` (maks 2, dipisah koma, WAJIB), `ML_MODEL_DIR`, `ML_TMP_DIR` (arahkan ke tmpfs), `ML_MAX_FILE_SIZE_MB`=25,
`ML_MAX_CONCURRENT`=4, `ML_TOTAL_DEADLINE_SEC`=100 (harus < timeout backend 120), `ML_EXTRACT_TIMEOUT_SEC`=30, `ML_INFER_TIMEOUT_SEC`=10,
`ML_IDEMPOTENCY_TTL_SEC`=3600, `ML_RETRY_AFTER_SEC`=5, `ML_CALLBACK_ALLOWED_HOSTS`, `ML_URL_MODEL_DIR`, `ML_URL_BLOCKLIST`,
`ML_URL_ALLOW_SAFE`=false, `ML_URL_MODEL_SOLO_FLAG`=false. `ML_STUB_MODE=true` DILARANG di production (layanan menolak).

## 8. Kinerja terukur dan batasannya (jujur)
**Model file** (EMBER2024 resmi; data 2023-09..2024-12; ambang sementara 0,636 / 0,947, status staging):
salah tuduh 0,86% (suspicious) dan 0,13% (malicious); malware tertandai 96,7% / 89,5% pada data uji; challenge set 57,7% / 37,2%.
Belum terukur pada malware 2026. Data uji seimbang 50:50; di dunia nyata mayoritas file normal.
**URL**: model hanya-fitur-domain, AUC 0,864 pada dataset PhiUSIIL yang dikenal terlalu mudah. Dengan 26 URL sah buatan sendiri: 0 salah ditandai
(sampel kecil). Pada 5000 URL malware URLhaus tanpa daftar hitam: 60,5% tertandai (kebanyakan oleh aturan IP/port), 7,5% bahkan berlabel safe
bila `ALLOW_SAFE` dinyalakan. Kelas yang tidak tertangkap: URL jahat berbentuk normal (mis. berkas di hosting sah); hanya daftar hitam yang membantu.
**Jalur aturan (file non-PE)**: diuji hanya dengan berkas SINTETIS (155 test), BELUM dengan korpus dokumen/malware nyata. Ini heuristik: tidak mendeteksi
malware baru, dan akan salah menandai sebagian file sah (mis. dokumen bermakro, skrip administrasi). Isi di dalam arsip belum dianalisis (PE di dalam zip belum
dikirim ke model). Tidak ada kalibrasi ambang; aturan poin ditetapkan manual.
**Daftar hitam**: dari URLhaus (abuse.ch), 30 hari terakhir. Cepat basi: perbarui berkala (harian disarankan). Cocok PERSIS, bukan per host.
**Lisensi (Terms of Use abuse.ch, diperbarui 21 Okt 2026): gratis hanya untuk keperluan nirlaba/non-komersial dengan batas pemakaian; pemakaian komersial dapat memerlukan langganan berbayar (Spamhaus); data tidak boleh dipakai melatih/meningkatkan model AI tanpa izin tertulis; jangan didistribusikan ulang (jangan commit daftar hitam ke repositori). CC0 TIDAK berlaku untuk URLhaus.** Auth-Key abuse.ch adalah rahasia.

## 9. Keamanan
Token acak panjang (bukan `token-lokal-123`); jaringan privat; TLS antar-layanan di production; ekstraksi fitur berjalan di proses anak dengan batas WAKTU
(batas MEMORI belum ada di Windows, gunakan Linux/Docker dengan batas memori kontainer); file sementara dihapus selalu; log tidak memuat isi file, token, atau URL penuh.
Backend sebaiknya menolak/menandai nama file berisi karakter kontrol (mis. U+202E); ML tidak melihat nama file.

## 10. Keputusan yang perlu disepakati Backend/PO
1. Field `extension_chain` + tiga reason code file baru + aturan naik ke `suspicious`.
2. Endpoint `/v1/urls` dan seluruh reason code URL.
3. Tafsiran `confidence` (di atas).
4. Ambang file sementara; target kesiapan (PRD 9.6) belum ditetapkan.
5. Request ganda dengan `request_id` sama yang sedang diproses dibalas `429 ML_OVERLOADED`; hanya hasil sukses di-cache.
6. Jadwal pembaruan daftar hitam dan pemilik prosesnya.
7. **Status lisensi data abuse.ch**: apakah proyek ini nirlaba (kuliah/riset/sekolah) atau komersial. Jika komersial, hubungi abuse.ch/Spamhaus sebelum dipakai di produksi.
8. Perilaku baru: file non-PE tidak lagi selalu 422; ada respons 200 dari jalur aturan + field `analysis` + reason code baru (bagian 3b); makro Office = `suspicious`.
