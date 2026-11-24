# Panduan Menjalankan STIKSAFE-67 ML Service (untuk Backend / Dedy)

Panduan singkat ini untuk menerima, memasang, dan menjalankan layanan ML v12 secara lokal agar siap diintegrasikan dengan backend Laravel. Spesifikasi lengkap endpoint, skema JSON, kode error, dan keputusan terbuka ada di `docs/KONTRAK_API_ML.md`.

---

## 1. Isi Paket yang Diterima

Ada 2 arsip ZIP terpisah (sengaja dipisah agar kode aman masuk Git, sedangkan model biner & daftar hitam tidak masuk repositori publik):

1. **`stiksafe-ml-v12-kode.zip`** — seluruh kode layanan, unit test (155 test), skrip uji, `demo_files/`, dan dokumentasi kontrak API.
2. **`stiksafe-ml-v12-models.zip`** — model yang sudah terkalibrasi:
   - `models/ember2024-pe-v1/` (`EMBER2024_PE.model` + `model_card.json`)
   - `models/url-v1/` (`URL_LGBM.model` + `model_card.json` + `blocklist.txt` berisi 1 URL simulasi aman untuk uji integrasi)

---

## 2. Cara Ekstrak & Menggabungkan

1. Ekstrak **`stiksafe-ml-v12-kode.zip`** → akan muncul folder `stiksafe-ml`.
2. Ekstrak **`stiksafe-ml-v12-models.zip`** → salin folder `models` di dalamnya, lalu tempel ke dalam folder `stiksafe-ml` (pilih **Replace/Merge** bila ditanya).
3. Pastikan struktur akhir terlihat seperti ini:
   ```text
   stiksafe-ml/
     stiksafe_ml/
     docs/
       KONTRAK_API_ML.md
       PANDUAN_JALANKAN_DEDY.md
     models/
       ember2024-pe-v1/
         EMBER2024_PE.model
         model_card.json
       url-v1/
         URL_LGBM.model
         model_card.json
         blocklist.txt
     demo_files/
     scripts/
     tests/
     requirements.txt
     run_real.bat
     run_local.bat
   ```

---

## 3. Instalasi Lingkungan Python (Cukup Sekali)

Syarat: **Python 3.10–3.13** terpasang.

Buka PowerShell di dalam folder `stiksafe-ml`, lalu jalankan berurutan:

```powershell
python -m venv .venv
.\.venv\Scripts\Activate.ps1
python -m pip install --upgrade pip
python -m pip install -r requirements.txt
python -m pip install https://github.com/FutureComputing4AI/EMBER2024/archive/refs/heads/main.zip
python -m pip install "signify<0.8"
```

> **Penting soal `signify<0.8`:** `thrember` (ekstraktor fitur EMBER2024) hanya cocok dengan `signify < 0.8`. Layanan akan otomatis menolak memuat model jika versi `signify >= 0.8`.

Verifikasi instalasi dengan menjalankan unit test:

```powershell
python -m unittest discover -s tests -t .
```

Hasil yang diharapkan: `Ran 155 tests` → **`OK`** (`skipped=1` di Windows adalah normal untuk tes izin file POSIX `0600`).

---

## 4. Menyalakan Layanan ML

Di PowerShell pertama (dengan `(.venv)` aktif):

```powershell
.\run_real.bat
```

Tunggu hingga muncul:
```text
Uvicorn running on http://127.0.0.1:8000
```

Biarkan jendela ini tetap terbuka selama pengujian. Token lokal default untuk pengembangan: `token-lokal-123` (wajib diganti token acak panjang sebelum staging/produksi).

*(Catatan untuk Linux/macOS: jalankan environment variable yang sama seperti isi `run_real.bat`, lalu `uvicorn --factory stiksafe_ml.api:create_app --host 127.0.0.1 --port 8000`.)*

---

## 5. Verifikasi Cepat (PowerShell Kedua, `.venv` Aktif)

**1. Cek kesehatan & info model:**
```powershell
Invoke-RestMethod -Uri http://127.0.0.1:8000/v1/health -Headers @{Authorization="Bearer token-lokal-123"}
Invoke-RestMethod -Uri http://127.0.0.1:8000/v1/model-info -Headers @{Authorization="Bearer token-lokal-123"}
Invoke-RestMethod -Uri http://127.0.0.1:8000/v1/url-info -Headers @{Authorization="Bearer token-lokal-123"}
```
Pastikan `status: ready`, `stub: False`, dan `model_loaded: True`.

**2. Uji pemindaian file (model PE + aturan non-PE + ekstensi ganda):**
```powershell
python scripts/try_scan.py C:\Windows\System32\notepad.exe
python scripts/try_scan.py C:\Windows\System32\notepad.exe --name laporan.pdf.exe
python scripts/try_scan.py demo_files\01_dokumen_bersih.pdf
python scripts/try_scan.py demo_files\02_pdf_berisiko.pdf
```

**3. Uji pemindaian URL:**
```powershell
python scripts/try_url.py "https://www.google.com/"
python scripts/try_url.py "http://192.0.2.10:8080/login"
python scripts/try_url.py "http://uji-malware-stiksafe.invalid/dropper.exe"
```

---

## 6. Catatan Daftar Hitam URLhaus (Opsional untuk Staging/Hosting)

File `models/url-v1/blocklist.txt` di paket serah-terima hanya memuat **1 URL simulasi aman** (tidak mendistribusikan ulang data URLhaus sesuai Terms of Use abuse.ch). Untuk mengisi daftar hitam URLhaus penuh di server uji nanti:
1. Unduh CSV terbaru dari URLhaus memakai Auth-Key gratis akun non-komersial/pendidikan.
2. Jalankan:
   ```powershell
   python scripts/build_blocklist.py data\urlhaus_recent.csv models\url-v1\blocklist.txt
   ```
3. Jangan commit `blocklist.txt` ke GitHub publik.

---

## 7. Hal yang Perlu Disepakati Sebelum Integrasi Final

Mohon review **Bagian 10** di `docs/KONTRAK_API_ML.md` (terutama endpoint baru `/v1/urls`, field `extension_chain`, field baru `analysis`, perilaku jalur aturan file non-PE, dan teks tampilan label `safe`).
