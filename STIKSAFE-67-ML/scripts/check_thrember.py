"""Diagnostik: memastikan thrember + model + ekstraksi file asli bekerja di komputer ini.
Pakai:  python scripts/check_thrember.py --models models/downloaded [--exe C:\\Windows\\System32\\notepad.exe]
Tujuan: memeriksa nama fungsi thrember yang sebenarnya, jadi kirim SELURUH output ke pengembang."""
import argparse
import os
import sys

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

ap = argparse.ArgumentParser()
ap.add_argument("--models", default=os.path.join("models", "downloaded"))
ap.add_argument("--model-name", default="EMBER2024_PE.model")
ap.add_argument("--exe", default=os.path.join(os.environ.get("SystemRoot", r"C:\Windows"), "System32", "notepad.exe"))
a = ap.parse_args()

import thrember
from thrember.features import PEFeatureExtractor

fe = PEFeatureExtractor()
print("thrember dimuat dari :", os.path.dirname(thrember.__file__))
print("dimensi fitur (dim)  :", fe.dim)
print("method ekstraktor    :", [m for m in dir(fe) if not m.startswith("_")])

found = []
for root, _, files in os.walk(a.models):
    found += [os.path.join(root, n) for n in files if n.endswith(".model")]
print("berkas model         :", sorted(os.path.basename(m) for m in found) or "TIDAK ADA")
pe = [m for m in found if os.path.basename(m) == a.model_name]     # nama harus PERSIS sama
if not pe:
    sys.exit(f"{a.model_name} tidak ditemukan di {a.models}. Kirim daftar di atas ke pengembang.")
print("lokasi model dipakai :", pe[0])

import lightgbm as lgb
from stiksafe_ml.ember_engine import vectorize

booster = lgb.Booster(model_file=pe[0])
print("model dipakai        :", os.path.basename(pe[0]), "| jumlah fitur model:", booster.num_feature())

with open(a.exe, "rb") as f:
    data = f.read()
vec = vectorize(fe, data)
print("file uji             :", a.exe, f"({len(data)} byte)")
print("panjang vektor       :", len(vec))
import numpy as np
score = float(booster.predict(np.asarray([vec], dtype=np.float32))[0])
print("skor model           :", round(score, 6), "(file Windows asli, seharusnya rendah)")
ok = len(vec) == fe.dim == booster.num_feature()
print("\nHASIL:", "OK - dimensi cocok" if ok else "GAGAL - dimensi fitur tidak cocok")
