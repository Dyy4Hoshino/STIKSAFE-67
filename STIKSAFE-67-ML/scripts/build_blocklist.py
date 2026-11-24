"""Membuat daftar hitam dari berkas CSV/teks URLhaus (URL jahat).
Ketentuan abuse.ch (Terms of Use, diperbarui 21 Okt 2026): gratis untuk keperluan nirlaba dengan batas pemakaian; pemakaian
komersial dapat butuh langganan berbayar (Spamhaus); data tidak boleh dipakai melatih/meningkatkan model AI tanpa izin tertulis;
jangan didistribusikan ulang. CC0 TIDAK berlaku untuk URLhaus.
Pakai:  python scripts/build_blocklist.py urlhaus.csv models\\url-v1\\blocklist.txt
Hanya URL yang valid dan dinormalisasi yang disimpan (kecocokan nanti PERSIS, bukan per-host)."""
import csv
import os
import sys

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from stiksafe_ml.errors import MLError
from stiksafe_ml.urlnorm import parse_url

if len(sys.argv) < 3:
    sys.exit("Pakai: python scripts/build_blocklist.py <berkas_masuk> <berkas_keluar>")
src, dst = sys.argv[1], sys.argv[2]
urls, total = set(), 0
with open(src, "r", encoding="utf-8", errors="ignore", newline="") as f:
    for line in f:
        if not line.strip() or line.startswith("#"):
            continue
        for field in next(csv.reader([line])):
            if field.strip().lower().startswith("http"):
                total += 1
                try:
                    urls.add(parse_url(field.strip()).normalized)
                except MLError:
                    pass
                break
os.makedirs(os.path.dirname(dst) or ".", exist_ok=True)
with open(dst, "w", encoding="utf-8") as f:
    f.write("# daftar hitam URL (normalisasi); dibuat oleh build_blocklist.py\n")
    f.write("\n".join(sorted(urls)) + "\n")
print(f"URL dibaca: {total}  |  tersimpan unik: {len(urls)}  ->  {dst}")
