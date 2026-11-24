"""Menjalankan ekstraksi fitur di PROSES ANAK dengan batas waktu (PRD 5.3, 8.5).

Parser yang macet atau crash hanya mematikan proses anak, bukan layanan.
CATATAN: batas MEMORI belum diterapkan (Windows tidak punya setrlimit); lihat README 'Keterbatasan'.
"""
import multiprocessing as mp
from typing import Any, Callable, Tuple


def _worker(func: Callable, args: Tuple, conn) -> None:
    try:
        conn.send(("ok", func(*args)))
    except BaseException as e:                       # noqa: BLE001 - semua jadi 'error' tanpa detail
        conn.send(("error", type(e).__name__))   # hanya NAMA jenis error; pesan bisa memuat path/isi
    finally:
        conn.close()


def run_in_child(func: Callable, args: Tuple, timeout: float) -> Any:
    """Jalankan func(*args) di proses baru. Raise RuntimeError('timeout' | 'child_failed' | 'child_failed:<JenisError>')."""
    ctx = mp.get_context("spawn")                    # sama di Windows/Linux; tidak mewarisi state
    parent, child = ctx.Pipe(duplex=False)
    p = ctx.Process(target=_worker, args=(func, args, child), daemon=True)
    p.start()
    child.close()
    try:
        if not parent.poll(timeout):
            raise RuntimeError("timeout")
        try:
            status, payload = parent.recv()
        except EOFError:
            raise RuntimeError("child_failed")
        if status != "ok":
            raise RuntimeError(f"child_failed:{payload}")
        return payload
    finally:
        if p.is_alive():
            p.terminate()
        p.join(2)
        parent.close()
