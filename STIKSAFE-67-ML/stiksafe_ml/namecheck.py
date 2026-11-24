"""Pemeriksa nama/ekstensi file (FITUR 3). Berbasis ATURAN, bukan ML.

ML tidak menerima nama file asli (data minimum, PRD 1.4). Backend hanya mengirim DAFTAR ekstensi
dari nama file, mis. "laporan.pdf.exe" -> ["pdf", "exe"].

Dua kasus yang ditangkap:
  A. DISGUISED_EXECUTABLE : isinya program Windows (PE) tetapi ekstensinya terlihat seperti dokumen/gambar
                            (mis. laporan.pdf yang isinya PE).
  B. DOUBLE_EXTENSION     : ekstensi dokumen diikuti ekstensi program (mis. laporan.pdf.exe).
"""
import re
from typing import Iterable, Optional, Set, Tuple

EXEC_EXT = frozenset({"exe", "scr", "com", "bat", "cmd", "pif", "cpl", "msi", "vbs", "vbe",
                      "js", "jse", "wsf", "ps1", "hta", "jar", "lnk"})
DECOY_EXT = frozenset({"pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "rtf", "csv",
                       "jpg", "jpeg", "png", "gif", "bmp", "mp3", "mp4", "avi", "zip", "rar", "7z"})
_EXT_RE = re.compile(r"^[a-z0-9]{1,10}$")

RAISE_TO_SUSPICIOUS = frozenset({"DOUBLE_EXTENSION", "DISGUISED_EXECUTABLE"})


def analyze_extensions(chain: Iterable[str]) -> Tuple[Set[str], list]:
    """Kembalikan (flags, chain_bersih). Elemen berkarakter aneh dibuang dan ditandai."""
    flags: Set[str] = set()
    clean = []
    for e in chain:
        if _EXT_RE.match(e):
            clean.append(e)
        else:
            flags.add("ODD_EXTENSION_CHARS")
    if len(clean) >= 2 and clean[-1] in EXEC_EXT and any(e in DECOY_EXT for e in clean[:-1]):
        flags.add("DOUBLE_EXTENSION")
    return flags, clean


def disguised_executable(family: Optional[str], declared_ext: Optional[str]) -> bool:
    return family == "pe" and declared_ext in DECOY_EXT


def apply_name_policy(label: str, confidence: Optional[float], flags: Set[str]):
    """Kebijakan: ekstensi menyamar -> minimal 'suspicious'. Hanya MENAIKKAN kehati-hatian:
    malicious/suspicious tidak diubah; safe/unknown dinaikkan. Confidence jadi null karena
    label ini bukan berasal dari skor model."""
    if flags & RAISE_TO_SUSPICIOUS and label in ("safe", "unknown"):
        return "suspicious", None
    return label, confidence
