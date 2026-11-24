"""File sementara aman (PRD 8.5), cache idempotensi (FR-RCV-05), dan sweeper (FR-OPS-03)."""
import hashlib
import os
import threading
import time
from typing import BinaryIO, Dict, Optional, Tuple

from .errors import MLError

CHUNK = 64 * 1024


class TempStore:
    def __init__(self, directory: str, sweep_age_sec: int = 300):
        self.dir = directory
        self.sweep_age = sweep_age_sec
        os.makedirs(self.dir, mode=0o700, exist_ok=True)

    def available(self) -> bool:
        return os.path.isdir(self.dir) and os.access(self.dir, os.W_OK)

    def receive(self, stream: BinaryIO, max_bytes: int) -> Tuple[str, int, str]:
        """Stream ke file sementara; hitung SHA-256 dan ukuran sambil jalan.

        Nama acak, izin 0600, tanpa bit eksekusi. Berhenti segera bila melebihi batas.
        Mengembalikan (path, size, sha256_hex). Pemanggil WAJIB menghapus lewat remove().
        """
        import tempfile
        fd, path = tempfile.mkstemp(dir=self.dir, prefix="scan_", suffix=".bin")  # mkstemp = 0600
        if os.name == "posix":          # Linux/Mac: paksa izin 0600. Windows tidak mengenal izin gaya ini
            os.fchmod(fd, 0o600)
        h, total = hashlib.sha256(), 0
        try:
            with os.fdopen(fd, "wb") as out:
                while True:
                    chunk = stream.read(CHUNK)
                    if not chunk:
                        break
                    total += len(chunk)
                    if total > max_bytes:
                        raise MLError("ML_FILE_TOO_LARGE")
                    h.update(chunk)
                    out.write(chunk)
        except BaseException:
            self.remove(path)
            raise
        return path, total, h.hexdigest()

    @staticmethod
    def remove(path: Optional[str]) -> None:
        if path:
            try:
                os.remove(path)
            except FileNotFoundError:
                pass

    def sweep(self, now: Optional[float] = None) -> int:
        """Hapus sisa file sementara yang lebih tua dari batas. Dijadwalkan tiap 5 menit."""
        now = now if now is not None else time.time()
        removed = 0
        for name in os.listdir(self.dir):
            p = os.path.join(self.dir, name)
            try:
                if os.path.isfile(p) and now - os.path.getmtime(p) > self.sweep_age:
                    os.remove(p)
                    removed += 1
            except FileNotFoundError:
                pass
        return removed


class IdempotencyCache:
    """Menyimpan metadata hasil (BUKAN file) per request_id selama TTL."""

    def __init__(self, ttl_sec: int = 3600, clock=time.monotonic):
        self.ttl, self.clock = ttl_sec, clock
        self._data: Dict[str, Tuple[float, dict]] = {}
        self._inflight: set = set()
        self._lock = threading.Lock()

    def get(self, request_id: str) -> Optional[dict]:
        with self._lock:
            item = self._data.get(request_id)
            if item and self.clock() - item[0] <= self.ttl:
                return item[1]
            self._data.pop(request_id, None)
            return None

    def put(self, request_id: str, body: dict) -> None:
        with self._lock:
            self._data[request_id] = (self.clock(), body)

    def begin(self, request_id: str) -> bool:
        """False bila request_id yang sama sedang diproses (tidak membuka jalur kedua)."""
        with self._lock:
            if request_id in self._inflight:
                return False
            self._inflight.add(request_id)
            return True

    def end(self, request_id: str) -> None:
        with self._lock:
            self._inflight.discard(request_id)

    def prune(self) -> int:
        with self._lock:
            now = self.clock()
            dead = [k for k, (t, _) in self._data.items() if now - t > self.ttl]
            for k in dead:
                del self._data[k]
            return len(dead)
