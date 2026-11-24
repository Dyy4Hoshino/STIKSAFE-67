"""Uji beban sederhana untuk layanan ML: kirim beberapa file sekaligus dan ukur kecepatannya.

Pakai (layanan harus sedang menyala lewat run_real.bat):
  python scripts/bench_scan.py C:\\Windows\\System32\\notepad.exe --n 20 --concurrency 2
Sambil jalan, buka Task Manager (Ctrl+Shift+Esc) > Processes > cari 'Python' dan catat angka Memory TERTINGGI.
Catatan: layanan hanya mengerjakan --max-concurrent (default 4) file sekaligus; sisanya dibalas 429 (normal, bukan error)."""
import argparse
import math
import os
import sys
import time
from collections import Counter
from concurrent.futures import ThreadPoolExecutor

import httpx

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from try_scan import build_metadata, chain_from_name  # noqa: E402  (skrip tetangga)


def percentile(sorted_vals, p):
    if not sorted_vals:
        return float("nan")
    k = max(0, min(len(sorted_vals) - 1, math.ceil(p / 100.0 * len(sorted_vals)) - 1))
    return sorted_vals[k]


def summarize(results, wall):
    """results = list of (detik, status_http, label_atau_None)."""
    ok = sorted(d for d, st, _ in results if st == 200)
    codes = Counter(st for _, st, _ in results)
    labels = Counter(lb for _, st, lb in results if st == 200)
    return {
        "n": len(results), "wall_s": wall, "per_minute": (len(ok) / wall * 60.0) if wall > 0 else 0.0,
        "p50": percentile(ok, 50), "p95": percentile(ok, 95), "max": ok[-1] if ok else float("nan"),
        "codes": dict(codes), "labels": dict(labels),
    }


def one(base, token, data, name):
    chain = chain_from_name(name)
    md = build_metadata(data, chain[-1] if chain else "", chain)
    t0 = time.perf_counter()
    try:
        import json
        r = httpx.post(f"{base}/v1/scans", headers={"Authorization": f"Bearer {token}"}, timeout=120,
                       data={"metadata": json.dumps(md)}, files={"file": ("upload.bin", data)})
        label = None
        if r.status_code == 200:
            label = r.json()["result"]["label"]
        return time.perf_counter() - t0, r.status_code, label
    except httpx.HTTPError:
        return time.perf_counter() - t0, 0, None          # 0 = gagal tersambung / timeout


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("file")
    ap.add_argument("--n", type=int, default=20)
    ap.add_argument("--concurrency", type=int, default=2)
    ap.add_argument("--base", default="http://127.0.0.1:8000")
    ap.add_argument("--token", default="token-lokal-123")
    ap.add_argument("--name", help="nama file yang dianggap diunggah, mis. laporan.pdf.exe")
    a = ap.parse_args()
    data = open(a.file, "rb").read()
    name = a.name or a.file
    print(f"Mengirim {a.n} permintaan, {a.concurrency} sekaligus, file {len(data)} byte. Pantau Task Manager sekarang...")
    t0 = time.perf_counter()
    with ThreadPoolExecutor(max_workers=a.concurrency) as ex:
        results = list(ex.map(lambda _: one(a.base, a.token, data, name), range(a.n)))
    s = summarize(results, time.perf_counter() - t0)
    print(f"\nTotal waktu          : {s['wall_s']:.1f} detik")
    print(f"Kecepatan            : {s['per_minute']:.1f} file/menit (hanya yang sukses)")
    print(f"Waktu per file       : median {s['p50']:.2f} dtk | p95 {s['p95']:.2f} dtk | terlama {s['max']:.2f} dtk")
    print(f"Kode HTTP            : {s['codes']}   (200=sukses, 429=penuh, 0=gagal tersambung)")
    print(f"Label                : {s['labels']}")
    print("\nCatat juga: angka Memory tertinggi proses Python di Task Manager selama tes tadi.")


if __name__ == "__main__":
    main()
