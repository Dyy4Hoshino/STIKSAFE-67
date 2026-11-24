"""Diagnostik ekstraksi fitur untuk SATU file .exe: menjalankan ekstraksi di proses ini dan menampilkan penyebab aslinya.
Pakai:  python scripts/debug_extract.py "C:\\Users\\Hype G12\\Downloads\\AutoClicker.exe"
Hanya MEMBACA file; tidak dijalankan dan tidak dikirim ke mana pun."""
import math
import os
import sys
import time
import traceback

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

if len(sys.argv) < 2:
    sys.exit('Pakai: python scripts/debug_extract.py "JALUR_FILE_EXE"')
path = sys.argv[1]

print("Python               :", sys.version.split()[0])
try:
    from importlib.metadata import version
    for pkg in ("thrember", "signify", "pefile", "lightgbm", "numpy"):
        try:
            print(f"{pkg:21s}:", version(pkg))
        except Exception:
            print(f"{pkg:21s}: (tidak ditemukan)")
except Exception:
    pass
print("file                 :", path)
print("ukuran               :", os.path.getsize(path), "byte")

from stiksafe_ml.ember_engine import thrember_extract

t0 = time.perf_counter()
try:
    vec = thrember_extract(path)
except BaseException:
    print(f"\nGAGAL setelah {time.perf_counter() - t0:.1f} detik. Penyebab aslinya:\n")
    traceback.print_exc()
    print("\n>>> Kirim SELURUH tulisan di atas ke pengembang.")
    sys.exit(1)

dt = time.perf_counter() - t0
bad = [i for i, x in enumerate(vec) if not math.isfinite(x)]
print(f"\nEkstraksi BERHASIL dalam {dt:.1f} detik. Panjang vektor: {len(vec)} (seharusnya 2568)")
print(f"Nilai tidak valid (NaN/tak hingga): {len(bad)}" + (f"  indeks pertama: {bad[:10]}" if bad else ""))
if bad:
    print(">>> Inilah penyebabnya: layanan menolak vektor yang memuat NaN/tak hingga (aturan PRD FR-PRE-05).")
elif len(vec) != 2568:
    print(">>> Panjang vektor tidak sesuai: layanan menolaknya.")
elif dt > 25:
    print(">>> Sangat lambat: batas waktu ekstraksi layanan 30 detik, jadi file ini berisiko kena batas waktu.")
else:
    print(">>> Ekstraksi sendiri sehat. Berarti masalahnya ada di cara layanan menjalankannya (proses anak), bukan di file.")
