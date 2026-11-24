# STIKSAFE-67 ML Service

Implementasi bertahap dari **PRD ML STIKSAFE-67 v2.0**.

## Status
| Langkah | Isi | Status |
|---|---|---|
| 1 | Kontrak, katalog error, konfigurasi | Selesai, teruji |
| 2 | Layanan mode stub (auth, validasi, idempotensi, tmp, health, model-info) | Selesai, teruji |
| 3 | Identifikasi format dari isi (PE, PDF, ZIP/OOXML, OLE, RTF, LNK, JPEG/PNG/GIF, teks, ELF, RAR, 7z, gzip, WebP) | Selesai, teruji |
| 3b | **Fitur 3**: pemeriksa ekstensi ganda/menyamar (aturan, `namecheck.py`) | Selesai, teruji |
| 4 | Ekstraktor fitur di proses anak (batas CPU/memori/waktu) | Belum |
| 5-7 | Basis data ML, dataset, training, evaluasi, registry | Belum (butuh dataset) |
| 8 | Engine nyata menggantikan stub | Belum |

## Menjalankan test (tanpa dependensi tambahan)
    python3 -m unittest discover -s tests -t .

## Menjalankan layanan (setelah `pip install -r requirements.txt`)
    export ML_SERVICE_TOKENS="token-rahasia-anda"
    export ML_STUB_MODE=true
    export ML_TMP_DIR=/dev/shm/stiksafe     # tmpfs
    export TMPDIR=/dev/shm                  # agar upload Starlette juga di tmpfs
    uvicorn --factory stiksafe_ml.api:create_app --host 127.0.0.1 --port 8000

## Contoh panggilan
    curl -H "Authorization: Bearer token-rahasia-anda" http://127.0.0.1:8000/v1/model-info
    curl -H "Authorization: Bearer token-rahasia-anda" \
         -F 'metadata={"scan_id":"01JCX7Y2K9M4N6P8Q0R3S5T7V9","request_id":"7f0c1d5e-4b7a-4a39-9c62-0e5a3d1b2f10","sha256":"<sha256 file>","size_bytes":<ukuran>,"submitted_at":"2026-11-14T02:00:00Z"}' \
         -F 'file=@contoh.exe' http://127.0.0.1:8000/v1/scans

## Peringatan
Mode stub menghasilkan skor dari SHA-256, BUKAN hasil model. Hanya untuk integrasi dan pengujian; ditolak saat `ML_ENV=production`.

## Tiga fitur web
| Fitur | Cara kerja | Status |
|---|---|---|
| 1. Deteksi file | Model ML pada fitur statis file PE | Kerangka siap (stub); model asli menunggu dataset |
| 2. Deteksi URL | 3 lapis: daftar hitam -> aturan -> model ML. Endpoint sendiri `POST /v1/urls` | Kerangka+aturan+daftar hitam teruji; model menunggu pelatihan |
| 3. Ekstensi ganda/menyamar | Aturan: `laporan.pdf.exe`, atau `.pdf` yang isinya program | Selesai |

## Perubahan kontrak (perlu disetujui Dedy sebelum dipakai backend)
- Request `metadata` punya field opsional baru `extension_chain`, mis. `["pdf","exe"]` (hanya ekstensi, BUKAN nama file).
- `reason_codes` punya nilai baru: `DOUBLE_EXTENSION`, `DISGUISED_EXECUTABLE`, `ODD_EXTENSION_CHARS`.
- Kebijakan baru: dua kode pertama menaikkan label `safe`/`unknown` menjadi minimal `suspicious`.
- Backend sebaiknya juga menolak/menandai nama file yang berisi karakter kontrol (mis. U+202E), karena ML tidak melihat nama file.

## Mencoba fitur 3
    python scripts/make_sample_pe.py contoh.exe
    python scripts/try_scan.py contoh.exe --name laporan.pdf.exe

## Model nyata (fitur 1) - jalur cepat memakai model resmi EMBER2024
Urutan (jalankan di PowerShell, di dalam venv proyek):

    python -m venv .venv
    .\.venv\Scripts\Activate.ps1          # bila diblokir: Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
    python -m pip install --upgrade pip
    python -m pip install -r requirements.txt
    python -m pip install https://github.com/FutureComputing4AI/EMBER2024/archive/refs/heads/main.zip
    python -m pip install "signify<0.8"
    python -m unittest discover -s tests -t .
    python scripts/download_models.py
    python scripts/check_thrember.py                      # GERBANG: kirim seluruh outputnya
    mkdir models\ember2024-pe-v1
    copy models\downloaded\<berkas PE>.model models\ember2024-pe-v1\EMBER2024_PE.model
    python scripts/download_data.py D:\ember2024          # ~8 GB
    python scripts/calibrate.py --data-dir D:\ember2024 --model-dir models\ember2024-pe-v1
    .\run_real.bat
    python scripts/try_scan.py C:\Windows\System32\notepad.exe

## Keterbatasan (jujur)
- Model resmi dilatih dari data 2023-09..2024-12; kemampuan pada malware 2026 BELUM terukur. Status: staging.
- Hanya Windows PE. Format lain ditolak (ML_UNSUPPORTED_FILE).
- Ekstraksi berjalan di proses anak dengan batas WAKTU. Batas MEMORI belum ada di Windows; untuk lalu lintas publik
  jalankan di Linux/Docker dengan batas memori kontainer (PRD langkah 9).
- Lisensi berkas model harus diperiksa sendiri di halaman modelnya (yang diverifikasi baru lisensi dataset).
- thrember/LightGBM tidak bisa diuji di lingkungan pengembang awal; `scripts/check_thrember.py` adalah gerbang verifikasinya.

## Fitur 2: deteksi URL (usulan PRD v2.1, BELUM ada di PRD v2.0)
Request  `POST /v1/urls`  JSON: `{"request_id": "<uuid>", "url": "https://..."}`  (header `Authorization: Bearer <token>`)
Response `{request_id, status, result:{label, confidence, reason_codes, rule_points}, target:{scheme, host, host_is_ip, normalized_sha256}, model_version, ...}`
- URL TIDAK PERNAH dibuka. Hanya teksnya dianalisis. URL penuh tidak dicatat di log (hanya hash dan panjang host).
- Lapis 1 daftar hitam (cocok PERSIS) -> `malicious`. Lapis 2 aturan (poin >= 2) -> minimal `suspicious`.
  Lapis 3 model: skor -> label, tetapi berdiri sendiri DIBATASI maksimal `suspicious` (model teks-URL tidak boleh memvonis malicious).
- Tanpa model dan tanpa bukti lain -> `unknown` (bukan `safe`).
- Label `safe` untuk URL DIMATIKAN secara default (`ML_URL_ALLOW_SAFE=false`): hasil terendah model menjadi `unknown` + `URL_NO_INDICATION`.
  Nyalakan hanya setelah model teruji di data luar dataset latih dan disetujui.
- reason_codes baru: URL_BLOCKLIST_MATCH, URL_IP_HOST, URL_USERINFO, URL_EXEC_DOWNLOAD, URL_PUNYCODE, URL_MANY_SUBDOMAINS,
  URL_NONSTANDARD_PORT, URL_SHORTENER, URL_SUSPICIOUS_KEYWORDS, URL_NO_MODEL.
- Env: ML_URL_MODEL_DIR, ML_URL_BLOCKLIST.

Urutan kerja (PowerShell, di dalam venv):

    python -m pip install ucimlrepo
    python scripts/download_url_data.py data\phiusiil_urls.csv
    python scripts/train_url.py --csv data\phiusiil_urls.csv --out-dir models\url-v1
    .\run_real.bat
    python scripts/try_url.py "http://192.168.1.5/login"

Daftar hitam (opsional): unduh CSV dari URLhaus (abuse.ch; lihat bagian Lisensi data abuse.ch) secara manual, lalu
    python scripts/build_blocklist.py urlhaus.csv models\url-v1\blocklist.txt

## Keterbatasan fitur 2 (jujur)
- Dataset PhiUSIIL dikenal TERLALU MUDAH (banyak penelitian melaporkan akurasi > 99% dari fitur sederhana):
  angka di data uji hampir pasti jauh lebih tinggi daripada kinerja nyata di web. Perhatikan daftar "fitur paling berpengaruh".
- Domain sah di dataset kebanyakan homepage tanpa path -> model bisa belajar "ada path = jahat". Ini sebab ada cap `suspicious`.
- Model hanya melihat teks URL, bukan isi halaman, reputasi, atau umur domain.
- Daftar hitam statis cepat basi; perlu pembaruan berkala.

## Temuan: pelatihan pertama model URL (urlfeat-v1) TERNYATA MENGHAFAL JALAN PINTAS DATASET
AUC 0,9989 dengan 94% pengaruh dari is_https, path_len, n_slash_path, has_www. Di dataset, URL sah umumnya homepage
`https://www.situs.com`, sedangkan phishing sering punya path/tanpa https. Di web nyata ini terbalik (link sah biasanya punya path).
Perbaikan (urlfeat-v2): fitur www diabaikan; skrip latih melatih model A (semua fitur) dan B (hanya fitur domain),
menampilkan perbandingannya, dan MENYIMPAN B. Model lama (urlfeat-v1) ditolak saat dimuat; latih ulang.
Angka B akan lebih rendah, dan itu angka yang lebih jujur. Tetap belum teruji di data nyata.

## Hasil pelatihan ulang model URL (urlfeat-v2, data uji dipecah per domain)
A (semua fitur): AUC 0,999 (menipu, jalan pintas). B (hanya domain, DISIMPAN): AUC 0,864; salah tuduh 1,03%;
phishing tertangkap 60,7% (suspicious) / 38,2% (malicious). Fitur terbesar B: host_len 39%.
Kewaspadaan: B mungkin belajar "domain pendek = terkenal = sah" -> situs lokal berdomain panjang berisiko salah ditandai.
Uji dengan daftar URL sendiri:  python scripts/eval_url_set.py --model-dir models\url-v1 --file urls_sah.txt --expect benign

## Uji dengan 26 URL sah buatan sendiri: 3 salah ditandai (11,5%)
docs.python.org, learn.microsoft.com, bali-villa-sewa-murah-seminyak.blogspot.com (model hanya-domain, urlfeat-v2).
Sampel kecil, tetapi dua situs resmi terkenal ikut salah ditandai. Dugaan: situs sah bersubdomain jarang ada di data latih.
KEBIJAKAN BARU: model URL hanyalah PENGUAT BERPOIN. Skor menengah (antara dua ambang) = +1 poin; sangat tinggi (>= ambang malicious) = +2.
Aturan memberi poin sendiri. Total >= 2 -> suspicious. Skor menengah sendirian TIDAK lagi menandai apa pun.
Poin 0 + model termuat -> safe hanya bila ML_URL_ALLOW_SAFE=true, selain itu unknown + URL_NO_INDICATION.
reason_codes baru: URL_MODEL_ELEVATED, URL_MODEL_HIGH. Respons memuat `rule_points` dan `model_points`.

## Kebijakan model URL diperketat lagi (uji 26 URL sah, setelah poin: 1 salah ditandai, skor 0,999)
URL contoh `bali-villa-sewa-murah-seminyak.blogspot.com` mendapat skor 0,999 (di atas ambang tinggi) padahal situs sah:
model menganggap domain panjang berhubung-hubung = berbahaya. KEPUTUSAN: secara default skor tingkat tertinggi pun hanya +1 poin,
jadi model TIDAK bisa menandai `suspicious` sendirian; ia butuh aturan lain. Sinyal tetap tercatat di reason_codes (URL_MODEL_HIGH).
Untuk mengembalikan perilaku lama: ML_URL_MODEL_SOLO_FLAG=true (atau --solo-flag di eval_url_set.py).
Imbalannya: phishing yang tidak memicu aturan apa pun tidak ditandai (unknown).

## Serah-terima
Lihat `docs/KONTRAK_API_ML.md` (kontrak API, kode error, kinerja terukur, keputusan yang butuh persetujuan).
Perbaikan v8: respons URL kini melaporkan versi fitur milik MODEL yang dimuat (sebelumnya tertulis tetap `urlfeat-v1`).

## Lisensi data abuse.ch (URLhaus, MalwareBazaar) - KOREKSI
Terms of Use abuse.ch (berlaku 4 Nov 2025, diperbarui 21 Okt 2026): gratis untuk keperluan NIRLABA/non-komersial dengan batas pemakaian;
pemakaian komersial (perusahaan/untuk laba) dapat memerlukan langganan berbayar lewat Spamhaus; data TIDAK boleh dipakai melatih atau
meningkatkan model AI tanpa izin tertulis Spamhaus; jangan menyalin/mendistribusikan ulang data. CC0 hanya berlaku untuk Feodo Tracker,
SSL Blacklist, dan YARAify, BUKAN URLhaus/MalwareBazaar (catatan CC0 sebelumnya keliru).
Pemakaian di proyek ini: URLhaus hanya sebagai daftar hitam (pencarian) dan satu kali evaluasi; TIDAK dipakai melatih model. Pertahankan begitu.
Jangan commit blocklist.txt / berkas unduhan ke GitHub (redistribusi + berisi URL malware asli).

## File selain PE: jalur ATURAN (filerules-v1)
PE tetap diperiksa MODEL. PDF, ZIP/OOXML (docx/xlsx/pptx), OLE (doc/xls/ppt lama), RTF, LNK, JPEG/PNG/GIF, dan teks/skrip/SVG diperiksa aturan
(`stiksafe_ml/analyzers.py`, hanya pustaka standar, di proses anak dengan batas waktu). Hasil: `suspicious` (poin >= 2) atau `unknown`;
TIDAK PERNAH `safe`/`malicious`. Tanpa temuan: `unknown` + `NO_RISK_INDICATORS`. Detail: `docs/KONTRAK_API_ML.md` bagian 3b.
Keterbatasan: heuristik, hanya diuji dengan berkas sintetis, bukan pemindai malware; isi arsip belum dianalisis lebih dalam.
Catatan Windows Defender: berkas sintetis yang dibuat test meniru pola berisiko (tanpa program); bila dikarantina antivirus, izinkan atau abaikan.

## Ketidakcocokan versi thrember <-> signify (ditemukan dari file nyata)
Gejala: file .exe bertanda tangan digital (mis. installer WinRAR) gagal dengan ML_PREPROCESSING_FAILED, file bawaan Windows (notepad) lolos.
Penyebab: thrember 0.1.0 mengiris `certs[:-1]`; sejak signify 0.8.0 CertificateStore bukan lagi `list`; sejak 0.9.0 SignedPEFile dipindah.
Solusi: `python -m pip install "signify<0.8"`. Layanan kini menolak memuat model bila signify >= 0.8 (gagal cepat, bukan gagal per-file).
Diagnosis satu file: `python scripts/debug_extract.py "<jalur.exe>"`.
