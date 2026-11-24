"""Membuat file contoh 'contoh.exe' yang AMAN: hanya berisi header MZ/PE palsu dan padding.
File ini BUKAN program, tidak bisa dijalankan, dan tidak berbahaya. Dipakai untuk uji coba layanan."""
import os
import sys

name = sys.argv[1] if len(sys.argv) > 1 else "contoh.exe"
data = bytearray(b"MZ" + b"\x00" * 0x3A)
data += (0x80).to_bytes(4, "little") + b"\x00" * (0x80 - 0x40) + b"PE\x00\x00" + b"\x00" * 64
data += os.urandom(16)   # supaya tiap file contoh punya hash berbeda
with open(name, "wb") as f:
    f.write(data)
print(f"File contoh dibuat: {name} ({len(data)} byte)")
