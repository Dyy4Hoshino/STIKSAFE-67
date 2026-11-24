"""Mengunduh model LightGBM resmi EMBER2024.  Pakai:  python scripts/download_models.py [folder_tujuan]"""
import os
import sys

import thrember

dest = sys.argv[1] if len(sys.argv) > 1 else os.path.join("models", "downloaded")
os.makedirs(dest, exist_ok=True)
thrember.download_models(dest)
print("\nIsi folder", dest)
for root, _, files in os.walk(dest):
    for n in sorted(files):
        p = os.path.join(root, n)
        print(f"  {os.path.getsize(p) / 1e6:8.1f} MB  {p}")
