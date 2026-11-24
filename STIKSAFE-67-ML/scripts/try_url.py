"""Mengirim satu URL ke layanan ML lokal.  Pakai:  python scripts/try_url.py "http://192.168.1.5/login"
(URL TIDAK dibuka oleh layanan; hanya teksnya yang dianalisis.)"""
import argparse
import json
import sys
import uuid

import httpx

ap = argparse.ArgumentParser()
ap.add_argument("url")
ap.add_argument("--base", default="http://127.0.0.1:8000")
ap.add_argument("--token", default="token-lokal-123")
a = ap.parse_args()
try:
    r = httpx.post(f"{a.base}/v1/urls", headers={"Authorization": f"Bearer {a.token}"}, timeout=60,
                   json={"request_id": str(uuid.uuid4()), "url": a.url})
except httpx.ConnectError:
    sys.exit("Tidak bisa terhubung. Apakah layanan sudah dijalankan (run_real.bat)?")
print("Status HTTP:", r.status_code)
print(json.dumps(r.json(), indent=2, ensure_ascii=False))
