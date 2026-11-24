"""Mengirim satu file ke layanan ML lokal dan menampilkan jawabannya.
Pemakaian:  python scripts/try_scan.py contoh.exe
Ini meniru apa yang nanti dilakukan backend Laravel."""
import argparse
import hashlib
import json
import os
import sys
import time
import uuid
from datetime import datetime, timezone

import httpx

ALPHABET = "0123456789ABCDEFGHJKMNPQRSTVWXYZ"


def new_ulid() -> str:
    t, head = int(time.time() * 1000), ""
    for _ in range(10):
        head, t = ALPHABET[t % 32] + head, t // 32
    return head + "".join(ALPHABET[b % 32] for b in os.urandom(16))


def chain_from_name(name: str) -> list:
    """'laporan.pdf.exe' -> ['pdf', 'exe'] (nama dasarnya dibuang, hanya ekstensi yang dikirim)."""
    parts = os.path.basename(name).split(".")
    return [x.lower() for x in parts[1:]]


def build_metadata(data: bytes, ext: str, chain=None) -> dict:
    md = {
        "scan_id": new_ulid(), "request_id": str(uuid.uuid4()),
        "sha256": hashlib.sha256(data).hexdigest(), "size_bytes": len(data),
        "declared_extension": ext,
        "submitted_at": datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ"),
    }
    if chain:
        md["extension_chain"] = chain
    return md


def main() -> None:
    p = argparse.ArgumentParser()
    p.add_argument("file")
    p.add_argument("--url", default="http://127.0.0.1:8000")
    p.add_argument("--token", default="token-lokal-123")
    p.add_argument("--name", help="nama file yang dianggap diunggah pengguna, mis. laporan.pdf.exe")
    a = p.parse_args()
    data = open(a.file, "rb").read()
    shown_name = a.name or a.file
    chain = chain_from_name(shown_name)
    ext = chain[-1] if chain else ""
    headers = {"Authorization": f"Bearer {a.token}"}
    try:
        r = httpx.post(f"{a.url}/v1/scans", headers=headers, timeout=120,
                       data={"metadata": json.dumps(build_metadata(data, ext, chain))},
                       files={"file": ("upload.bin", data)})
    except httpx.ConnectError:
        sys.exit("Tidak bisa terhubung. Apakah layanan sudah dijalankan (run_local.bat)?")
    print("Status HTTP:", r.status_code)
    print(json.dumps(r.json(), indent=2, ensure_ascii=False))


if __name__ == "__main__":
    main()
