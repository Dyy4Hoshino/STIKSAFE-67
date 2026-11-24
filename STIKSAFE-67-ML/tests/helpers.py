import hashlib
import io
import json
import os
import tempfile

from stiksafe_ml.config import Settings
from stiksafe_ml.engine import StubEngine
from stiksafe_ml.service import ScanService

TOKEN = "token-test-aktif"
SCAN_ID = "01JCX7Y2K9M4N6P8Q0R3S5T7V9"
REQ_ID = "7f0c1d5e-4b7a-4a39-9c62-0e5a3d1b2f10"


def make_pe(target: str = "any", counter_start: int = 0) -> bytes:
    """File PE sintetis AMAN (hanya header MZ/PE + padding, bukan program). Dicari agar skor stub
    masuk rentang: 'malicious' (>=0.8), 'suspicious' (0.5-0.8), 'safe' (<0.5)."""
    base = bytearray(b"MZ" + b"\x00" * 0x3A)
    base += (0x80).to_bytes(4, "little") + b"\x00" * (0x80 - 0x40) + b"PE\x00\x00" + b"\x00" * 64
    for i in range(counter_start, counter_start + 100000):
        data = bytes(base) + i.to_bytes(4, "little")
        s = int(hashlib.sha256(data).hexdigest()[:8], 16) / 0xFFFFFFFF
        if target == "any" or (target == "malicious" and s >= 0.8) \
                or (target == "suspicious" and 0.5 <= s < 0.8) or (target == "safe" and s < 0.5):
            return data
    raise RuntimeError("tidak ketemu")


def meta(data: bytes, **over) -> str:
    d = {"scan_id": SCAN_ID, "request_id": REQ_ID, "sha256": hashlib.sha256(data).hexdigest(),
         "size_bytes": len(data), "declared_extension": "exe", "submitted_at": "2026-11-14T02:00:00Z"}
    d.update(over)
    return json.dumps(d)


def settings(tmp: str, **over) -> Settings:
    kw = dict(tmp_dir=tmp, service_tokens=(TOKEN,), stub_mode=True)
    kw.update(over)
    return Settings(**kw)


def new_service(tmp: str, engine=None, clock=None, **over) -> ScanService:
    kwargs = {"clock": clock} if clock else {}
    return ScanService(settings(tmp, **over), StubEngine() if engine is None else engine, **kwargs)


def tmpdir() -> tempfile.TemporaryDirectory:
    return tempfile.TemporaryDirectory(prefix="stiksafe_test_")


def files_left(path: str) -> int:
    return len(os.listdir(path))


def stream(b: bytes):
    return io.BytesIO(b)
