"""Menentukan ambang label + menulis model_card.json (PRD 9.6).

Alur: ambil sampel baris dari split 'test' EMBER2024 (belum pernah dilihat model resmi saat latihan),
ubah ke vektor fitur, minta skor model, pilih ambang dari separuh sampel, ukur di separuh lainnya
dan di 'challenge set'.

Pakai:
  python scripts/calibrate.py --data-dir C:\\ember2024 --model-dir models\\ember2024-pe-v1 --model-name EMBER2024_PE.model
"""
import argparse
import datetime
import json
import os
import random
import sys
from collections import Counter

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

import numpy as np

from stiksafe_ml.registry import sha256_file
from stiksafe_ml.thresholds import select_thresholds, summarize, flagged_fraction


def is_pe(file_type: str) -> bool:
    ft = file_type.lower()
    return ft in {"win32", "win64", ".net", "dotnet", "pe"} or ft.startswith("win") or "net" in ft


def count_lines(path):
    n = 0
    with open(path, "rb") as f:
        while True:
            chunk = f.read(1 << 24)
            if not chunk:
                break
            n += chunk.count(b"\n")
    return n


def collect(paths, rate, max_rows, rng, extractor, dim, seen):
    """Kembalikan (X, y, dilewati, capped). capped=True berarti berhenti dini karena batas -> sampel BIAS."""
    X, y, skipped = [], [], 0
    for p in paths:
        with open(p, "r", encoding="utf-8") as f:
            for line in f:
                if rng.random() > rate:
                    continue
                try:
                    raw = json.loads(line)
                    label = raw.get("label")
                    ft = str(raw.get("file_type", ""))
                    seen[ft] += 1
                    if label not in (0, 1) or not is_pe(ft):
                        continue
                    vec = extractor.process_raw_features(raw)
                    if len(vec) != dim:
                        skipped += 1
                        continue
                except Exception:
                    skipped += 1
                    continue
                X.append(np.asarray(vec, dtype=np.float32)); y.append(int(label))
                if len(X) >= max_rows:
                    return np.vstack(X), np.asarray(y), skipped, True
    return (np.vstack(X) if X else np.empty((0, dim), np.float32)), np.asarray(y), skipped, False


def predict(booster, X, batch=5000):
    return np.concatenate([booster.predict(X[i:i + batch]) for i in range(0, len(X), batch)]) if len(X) else np.array([])


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--data-dir", required=True)
    ap.add_argument("--model-dir", required=True)
    ap.add_argument("--model-name", default="EMBER2024_PE.model")
    ap.add_argument("--model-version", default="ember2024-pe-baseline-1")
    ap.add_argument("--rate", type=float, default=1.0, help="batas atas peluang ambil; yang dipakai dihitung otomatis")
    ap.add_argument("--max-rows", type=int, default=80000)
    ap.add_argument("--fpr-malicious", type=float, default=0.001)
    ap.add_argument("--fpr-suspicious", type=float, default=0.01)
    ap.add_argument("--seed", type=int, default=67)
    a = ap.parse_args()

    model_path = os.path.join(a.model_dir, a.model_name)
    if not os.path.isfile(model_path):
        sys.exit(f"Salin {a.model_name} ke folder {a.model_dir} dulu.")

    import lightgbm as lgb
    from thrember.features import PEFeatureExtractor
    extractor, booster = PEFeatureExtractor(), lgb.Booster(model_file=model_path)
    dim = int(extractor.dim)
    if booster.num_feature() != dim:
        sys.exit(f"Dimensi tidak cocok: ekstraktor {dim}, model {booster.num_feature()}")

    def files(subset):          # cari sampai ke subfolder (unduhan thrember bisa bersarang)
        out = []
        for root, _, names in os.walk(a.data_dir):
            out += [os.path.join(root, n) for n in names if n.endswith(".jsonl") and subset in n]
        return sorted(out)

    rng = random.Random(a.seed)
    seen = Counter()
    test_files = files("test")
    if not test_files:
        sys.exit("Tidak ada berkas *test*.jsonl di --data-dir. Jalankan download_data.py dulu.")
    print(f"Tahap 1/2: menghitung baris di {len(test_files)} berkas test (beberapa menit, layar mungkin diam)...")
    total = sum(count_lines(p) for p in test_files)
    rate = min(a.rate, a.max_rows * 0.9 / total)       # sama untuk SEMUA baris -> sampel merata di semua minggu
    print(f"total baris test     : {total}\npeluang ambil/baris  : {rate:.4f}")
    print("Tahap 2/2: mengambil sampel dan mengubahnya jadi fitur (beberapa menit)...")
    X, y, skipped, capped = collect(test_files, rate, a.max_rows, rng, extractor, dim, seen)
    if capped:
        sys.exit("Batas --max-rows tercapai sebelum semua berkas terbaca: sampel akan bias. Naikkan --max-rows.")
    print("tipe file terlihat   :", dict(seen))
    print(f"sampel dipakai       : {len(y)} (dilewati {skipped}); malicious={int(y.sum())} benign={int((y == 0).sum())}")
    if len(y) < 1000 or y.sum() < 50 or (y == 0).sum() < 50:
        sys.exit("Sampel terlalu sedikit/tidak seimbang. Naikkan --rate atau cek tipe file di atas.")

    scores = predict(booster, X)
    idx = list(range(len(y))); rng.shuffle(idx)
    half = len(idx) // 2
    cal, rep = np.asarray(idx[:half]), np.asarray(idx[half:])
    th = select_thresholds(y[cal], scores[cal], a.fpr_malicious, a.fpr_suspicious)
    m_cal, m_rep = summarize(y[cal], scores[cal], th), summarize(y[rep], scores[rep], th)

    chal = {}
    ch_files = files("challenge")
    if ch_files:
        Xc, yc, _, _ = collect(ch_files, 1.0, 10 ** 9, rng, extractor, dim, Counter())
        mal = yc == 1
        if mal.sum():
            sc = predict(booster, Xc[mal])
            chal = {"n_malicious": int(mal.sum()),
                    "recall_at_t_suspicious": flagged_fraction(sc, th["t_suspicious"]),
                    "recall_at_t_malicious": flagged_fraction(sc, th["t_malicious"])}

    card = {
        "model_version": a.model_version, "model_file": a.model_name,
        "model_sha256": sha256_file(model_path),
        "dataset_version": "EMBER2024 (model resmi; kalibrasi dari sampel split test)",
        "preprocessing_version": "thrember-ember-feature-v3", "feature_dim": dim,
        "thresholds": {k: float(v) for k, v in th.items()},
        "status": "staging", "provisional": True,
        "metrics": {"sampling": {"total_rows": total, "rate": rate, "rows_used": int(len(y)), "seed": a.seed},
                    "policy": {"fpr_malicious": a.fpr_malicious, "fpr_suspicious": a.fpr_suspicious},
                    "calibration_half": m_cal, "report_half": m_rep, "challenge": chal,
                    "caveat": "Model resmi dilatih dari data 2023-09..2024-12; performa pada malware 2026 belum terukur."},
        "created_at": datetime.datetime.now(datetime.timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ"),
    }
    with open(os.path.join(a.model_dir, "model_card.json"), "w", encoding="utf-8") as f:
        json.dump(card, f, indent=2, ensure_ascii=False)

    print("\n=== AMBANG (sementara, status: staging) ===")
    print(json.dumps(card["thresholds"], indent=2))
    print("\n=== Ukuran di separuh sampel yang TIDAK dipakai memilih ambang ===")
    print(json.dumps(m_rep, indent=2))
    print("\n=== Challenge set (malware yang lolos banyak antivirus) ===")
    print(json.dumps(chal, indent=2) if chal else "(tidak ada / tidak terbaca)")
    print("\nTulis ke:", os.path.join(a.model_dir, "model_card.json"))


if __name__ == "__main__":
    main()
