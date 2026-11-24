@echo off
REM Menjalankan layanan ML di komputer Anda (mode stub, hanya untuk uji coba).
set ML_ENV=local
set ML_SERVICE_TOKENS=token-lokal-123
set ML_STUB_MODE=true
set ML_TMP_DIR=%TEMP%\stiksafe
echo Layanan berjalan di http://127.0.0.1:8000  (hentikan dengan Ctrl+C)
python -m uvicorn --factory stiksafe_ml.api:create_app --host 127.0.0.1 --port 8000
