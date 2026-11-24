"""Mengunduh dataset PhiUSIIL (UCI #967, lisensi CC BY 4.0) dan menyimpan hanya kolom URL + label ke CSV.
Prasyarat:  python -m pip install ucimlrepo
Pakai:      python scripts/download_url_data.py data\\phiusiil_urls.csv
Catatan label asli: 1 = sah (legitimate), 0 = phishing."""
import os
import sys

from ucimlrepo import fetch_ucirepo

out = sys.argv[1] if len(sys.argv) > 1 else os.path.join("data", "phiusiil_urls.csv")
os.makedirs(os.path.dirname(out) or ".", exist_ok=True)
ds = fetch_ucirepo(id=967)
df = ds.data.features[["URL"]].copy()
df["label"] = ds.data.targets.iloc[:, 0].values
df.to_csv(out, index=False)
print(f"Tersimpan: {out}  ({len(df)} baris)")
print("Sebaran label (1=sah, 0=phishing):")
print(df["label"].value_counts().to_string())
print("\nSumber: Prasad & Chandra (2024), PhiUSIIL Phishing URL Dataset, UCI ML Repository, CC BY 4.0")
