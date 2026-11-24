@echo off
REM Layanan dengan MODEL NYATA (status staging): fitur 1 (file), fitur 3 (ekstensi ganda), fitur 2 (URL).
REM Model URL dan daftar hitam bersifat opsional: bila folder/berkasnya belum ada, URL diperiksa dengan aturan saja.
set ML_ENV=local
set ML_SERVICE_TOKENS=token-lokal-123
set ML_STUB_MODE=false
set ML_MODEL_DIR=models\ember2024-pe-v1
set ML_URL_MODEL_DIR=models\url-v1
set ML_URL_BLOCKLIST=models\url-v1\blocklist.txt
set ML_TMP_DIR=%TEMP%\stiksafe
set ML_EXTRACT_TIMEOUT_SEC=30
echo Layanan (MODEL NYATA) di http://127.0.0.1:8000  (hentikan dengan Ctrl+C)
python -m uvicorn --factory stiksafe_ml.api:create_app --host 127.0.0.1 --port 8000
