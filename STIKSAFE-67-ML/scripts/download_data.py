"""Mengunduh fitur+label EMBER2024 untuk KALIBRASI ambang: bagian PE 'test' (~7,8 GB) dan 'challenge'.
Pakai:  python scripts/download_data.py C:\\ember2024     (pastikan disk kosong > 12 GB)"""
import os
import sys

import thrember

if len(sys.argv) < 2:
    sys.exit("Pakai: python scripts/download_data.py <folder_tujuan>")
dest = sys.argv[1]
os.makedirs(dest, exist_ok=True)
thrember.download_dataset(dest, file_type="PE", split="test")
thrember.download_dataset(dest, split="challenge")
print("\nSelesai. Isi folder:")
total = 0
for root, _, names in os.walk(dest):
    for n in sorted(names):
        p = os.path.join(root, n)
        size = os.path.getsize(p)
        total += size
        print(f"  {size / 1e6:9.1f} MB  {p}")
print(f"\nTotal: {total / 1e9:.2f} GB")
