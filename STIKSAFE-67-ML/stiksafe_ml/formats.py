"""Identifikasi format dari ISI file (PRD 5.2). Ekstensi/MIME hanya hint (FR-FMT-01).

Hanya membaca byte; tidak pernah mengeksekusi atau mem-parse lebih dalam dari header.
`family` hanya terisi untuk format yang didukung MODEL ML (sekarang: pe). `detected` mencakup lebih banyak format,
yang sebagian diperiksa oleh pemeriksa berbasis aturan (lihat analyzers.py).
"""
import os
from dataclasses import dataclass
from typing import Optional

# keluarga format -> ekstensi yang wajar (untuk flag FORMAT_MISMATCH pada jalur MODEL)
EXTENSIONS = {"pe": {"exe", "dll"}}

_LNK_GUID = bytes.fromhex("0114020000000000c000000000000046")


@dataclass(frozen=True)
class FormatResult:
    detected: str            # pe|pdf|zip|ole|rtf|lnk|jpeg|png|gif|webp|elf|gzip|rar|7z|text|unknown
    family: Optional[str]    # keluarga yang bisa dianalisis MODEL; None bila tidak
    partial: bool = False    # struktur rusak sebagian (FR-FMT-03)


def _looks_text(head: bytes) -> bool:
    if not head:
        return False
    if head[:2] in (b"\xff\xfe", b"\xfe\xff") or head[:3] == b"\xef\xbb\xbf":
        return True
    if b"\x00" in head:
        return False
    ok = sum(1 for b in head if b in (9, 10, 13) or 32 <= b < 127 or b >= 128)
    return ok / len(head) >= 0.95


def identify(path: str) -> FormatResult:
    size = os.path.getsize(path)
    with open(path, "rb") as f:
        head = f.read(4096)
        if head[:2] == b"MZ":
            partial = True
            if len(head) >= 0x40:
                e_lfanew = int.from_bytes(head[0x3C:0x40], "little")
                if 0 < e_lfanew <= size - 4:
                    f.seek(e_lfanew)
                    partial = f.read(4) != b"PE\x00\x00"
            return FormatResult("pe", "pe", partial)
    if b"%PDF-" in head[:1024]:
        return FormatResult("pdf", None)
    if head[:4] in (b"PK\x03\x04", b"PK\x05\x06", b"PK\x07\x08"):
        return FormatResult("zip", None)
    if head[:8] == b"\xd0\xcf\x11\xe0\xa1\xb1\x1a\xe1":
        return FormatResult("ole", None)
    if head[:5] == b"{\\rtf":
        return FormatResult("rtf", None)
    if head[:4] == b"L\x00\x00\x00" and head[4:20] == _LNK_GUID:
        return FormatResult("lnk", None)
    if head[:3] == b"\xff\xd8\xff":
        return FormatResult("jpeg", None)
    if head[:8] == b"\x89PNG\r\n\x1a\n":
        return FormatResult("png", None)
    if head[:6] in (b"GIF87a", b"GIF89a"):
        return FormatResult("gif", None)
    if head[:4] == b"RIFF" and head[8:12] == b"WEBP":
        return FormatResult("webp", None)
    if head[:4] == b"\x7fELF":
        return FormatResult("elf", None)
    if head[:2] == b"\x1f\x8b":
        return FormatResult("gzip", None)
    if head[:4] == b"Rar!":
        return FormatResult("rar", None)
    if head[:6] == b"7z\xbc\xaf\x27\x1c":
        return FormatResult("7z", None)
    if _looks_text(head):
        return FormatResult("text", None)
    return FormatResult("unknown", None)   # format tidak ditebak (BR-ML-10)


def extension_mismatch(fmt: FormatResult, declared_ext: Optional[str]) -> bool:
    if not declared_ext or not fmt.family:
        return False
    return declared_ext not in EXTENSIONS.get(fmt.family, set())
