"""Pemeriksa file NON-PE berbasis ATURAN (hanya pustaka standar). Tidak pernah mengeksekusi apa pun.

Keluaran: poin risiko + kode alasan. Total poin >= 2 -> 'suspicious'. Aturan TIDAK PERNAH menghasilkan 'safe' atau 'malicious':
tidak ada temuan = 'unknown' + NO_RISK_INDICATORS (pemeriksaan dasar, bukan jaminan). Berjalan di proses anak dengan batas waktu.
"""
import os
import re
import zipfile
import zlib
from typing import Dict, List, Optional, Set, Tuple

from .namecheck import EXEC_EXT, analyze_extensions

RULESET_VERSION = "filerules-v1"

SCRIPT_EXT = frozenset({"js", "jse", "vbs", "vbe", "ps1", "bat", "cmd", "hta", "wsf"})
# ekstensi terdeklarasi -> format isi yang wajar (untuk FORMAT_MISMATCH)
EXT_FAMILY: Dict[str, Set[str]] = {
    "pdf": {"pdf"}, "jpg": {"jpeg"}, "jpeg": {"jpeg"}, "png": {"png"}, "gif": {"gif"}, "rtf": {"rtf"}, "lnk": {"lnk"},
    "docx": {"zip"}, "xlsx": {"zip"}, "pptx": {"zip"}, "docm": {"zip"}, "xlsm": {"zip"}, "pptm": {"zip"}, "zip": {"zip"},
    "odt": {"zip"}, "ods": {"zip"}, "odp": {"zip"},
    "doc": {"ole", "rtf", "zip"}, "xls": {"ole", "text", "zip"}, "ppt": {"ole", "zip"}, "msi": {"ole"},
    "txt": {"text"}, "csv": {"text"}, "html": {"text"}, "htm": {"text"}, "xml": {"text"}, "json": {"text"},
    "exe": {"pe"}, "dll": {"pe"},
}
EXT_FAMILY.update({e: {"text"} for e in SCRIPT_EXT})

ARCHIVE_EXEC_EXT = frozenset(set(EXEC_EXT) | {"dll", "jar", "apk", "iso"})
NESTED_EXT = frozenset({"zip", "rar", "7z", "gz", "tgz", "iso"})
MAX_ZIP_ENTRIES = 10000
MAX_RELS = 300
MAX_REL_BYTES = 2_000_000

# ---------------------------------------------------------------- PDF
_PDF_NAME = re.compile(rb"/([^\s()<>\[\]{}/%]{1,64})")
_HEX = re.compile(rb"#([0-9A-Fa-f]{2})")
_PDF_RISK = {  # nama (huruf kecil) -> (kode, poin)
    b"javascript": ("PDF_JAVASCRIPT", 1), b"js": ("PDF_JAVASCRIPT", 1),
    b"openaction": ("PDF_AUTO_ACTION", 1), b"aa": ("PDF_AUTO_ACTION", 1),
    b"launch": ("PDF_LAUNCH_ACTION", 2), b"embeddedfile": ("PDF_EMBEDDED_FILE", 1),
    b"richmedia": ("PDF_RICH_MEDIA", 1),
}


def _pdf_names(blob: bytes) -> Tuple[Set[bytes], bool]:
    names: Set[bytes] = set()
    obf = False
    for m in _PDF_NAME.finditer(blob):
        raw = m.group(1)
        if b"#" in raw:
            dec = _HEX.sub(lambda mm: bytes([int(mm.group(1), 16)]), raw).lower()
            if dec in _PDF_RISK:
                obf = True
            names.add(dec)
        else:
            names.add(raw.lower())
    return names, obf


def _texty(sample: bytes) -> bool:
    """Isi tampak seperti teks (bukan gambar/font biner). Data biner tidak dipindai: kebetulan membentuk '/JS' sangat mungkin."""
    if not sample:
        return False
    ok = sum(1 for b in sample if b in (9, 10, 13) or 32 <= b < 127)
    return ok / len(sample) >= 0.85


def _split_streams(data: bytes, max_streams: int = 20000):
    """Pisahkan PDF menjadi (bagian di luar stream, daftar isi stream). LINEAR: tiap pencarian maju ke depan, tidak ada regex
    berbalik-arah, sehingga PDF cacat (banyak 'stream' tanpa 'endstream') tidak bisa membuat waktu meledak."""
    outside, bodies, pos = [], [], 0
    while len(bodies) < max_streams:
        i = data.find(b"stream", pos)
        if i < 0:
            break
        if data[max(0, i - 3):i] == b"end":                 # token 'endstream', bukan pembuka
            pos = i + 6
            continue
        j = i + 6
        if data[j:j + 2] == b"\r\n":
            j += 2
        elif data[j:j + 1] in (b"\n", b"\r"):
            j += 1
        k = data.find(b"endstream", j)
        if k < 0:
            break                                            # tak berpasangan: sisanya tetap dianggap 'di luar'
        outside.append(data[pos:i])
        bodies.append(data[j:k])
        pos = k + 9
    outside.append(data[pos:])
    return b"\n".join(outside), bodies


def _pdf_blobs(data: bytes, max_total=64 * 1024 * 1024, max_per=8 * 1024 * 1024):
    """Bagian yang dipindai: (1) badan PDF di LUAR stream, (2) isi stream yang berupa teks (didekompresi bila perlu)."""
    outside, bodies = _split_streams(data)
    yield outside
    total = 0
    for body in bodies:
        try:
            d = zlib.decompressobj().decompress(body, max_per)        # batas ukuran: anti bom dekompresi
        except zlib.error:
            d = body[:max_per]                                         # stream tak terkompresi
        if d and _texty(d[:4096]):
            yield d
        total += len(d)
        if total >= max_total:
            break


def _analyze_pdf(path: str, detected: str) -> Tuple[int, Set[str], float]:
    with open(path, "rb") as f:
        data = f.read()
    flags: Set[str] = set()
    names: Set[bytes] = set()
    obf = False
    for blob in _pdf_blobs(data):
        n, o = _pdf_names(blob)
        names |= n
        obf = obf or o
    points, seen = 0, set()
    for name, (code, pts) in _PDF_RISK.items():
        if name in names and code not in seen:
            seen.add(code); flags.add(code); points += pts
    if obf:
        flags.add("PDF_OBFUSCATED_NAMES"); points += 1
    coverage = 1.0
    if b"/Encrypt" in data:
        flags.add("ENCRYPTED_CONTENT"); coverage = 0.5       # isi stream terenkripsi: tidak bisa dilihat
    if b"%%EOF" not in data[-2048:]:
        flags.add("PARSE_PARTIAL"); coverage = min(coverage, 0.6)
    return points, flags, coverage


# ---------------------------------------------------------------- ZIP / OOXML
_REL_TAG = re.compile(r"<Relationship\b[^>]*>", re.I)


def _attr(tag: str, name: str) -> str:
    m = re.search(name + r"""\s*=\s*["']([^"']*)["']""", tag, re.I)
    return m.group(1) if m else ""


def _analyze_zip(path: str, detected: str) -> Tuple[int, Set[str], float]:
    flags: Set[str] = set()
    points, coverage = 0, 1.0
    try:
        zf = zipfile.ZipFile(path)
    except (zipfile.BadZipFile, OSError, NotImplementedError, ValueError):
        return 0, {"PARSE_PARTIAL"}, 0.4
    with zf:
        infos = zf.infolist()
        if len(infos) > MAX_ZIP_ENTRIES:
            flags.add("ARCHIVE_TOO_MANY_ENTRIES"); points += 2
            infos = infos[:MAX_ZIP_ENTRIES]
        csize = max(1, os.path.getsize(path))
        total = sum(i.file_size for i in infos)
        if total > 500 * 1024 * 1024 or total / csize > 200:
            flags.add("ARCHIVE_HIGH_RATIO"); points += 2
        names = [i.filename for i in infos]
        lower = [n.lower() for n in names]
        if any(i.flag_bits & 0x1 for i in infos):
            flags.add("ENCRYPTED_CONTENT"); coverage = 0.5
        if any(n.startswith(("/", "\\")) or "../" in n or "..\\" in n or re.match(r"^[A-Za-z]:", n) for n in names):
            flags.add("ARCHIVE_PATH_TRAVERSAL"); points += 2
        exec_hit = dbl_hit = nested = False
        for n in lower:
            base = n.replace("\\", "/").rsplit("/", 1)[-1]
            parts = base.split(".")[1:]
            if parts and parts[-1] in ARCHIVE_EXEC_EXT:
                exec_hit = True
            if parts and parts[-1] in NESTED_EXT:
                nested = True
            if "DOUBLE_EXTENSION" in analyze_extensions(parts)[0]:
                dbl_hit = True
        if exec_hit:
            flags.add("ARCHIVE_CONTAINS_EXECUTABLE"); points += 1
        if dbl_hit:
            flags.add("ARCHIVE_DOUBLE_EXTENSION"); points += 2
        if nested:
            flags.add("ARCHIVE_NESTED")
        ooxml = "[content_types].xml" in lower and any(n.startswith(("word/", "xl/", "ppt/")) for n in lower)
        if ooxml:
            if any(n.endswith("vbaproject.bin") for n in lower):
                flags.add("OFFICE_MACRO"); points += 2
            if any("/embeddings/" in n for n in lower):
                flags.add("OFFICE_EMBEDDED_OBJECT"); points += 1
            if any("/activex/" in n for n in lower):
                flags.add("OFFICE_ACTIVEX"); points += 1
            rels = [i for i in infos if i.filename.lower().endswith(".rels")][:MAX_RELS]
            tmpl = obj = False
            for info in rels:
                if info.file_size > MAX_REL_BYTES:
                    continue
                try:
                    with zf.open(info) as fh:
                        text = fh.read(MAX_REL_BYTES + 1).decode("utf-8", "replace")
                except (zipfile.BadZipFile, RuntimeError, OSError, NotImplementedError):
                    continue
                for tag in _REL_TAG.findall(text):
                    if _attr(tag, "TargetMode").lower() != "external":
                        continue
                    typ = _attr(tag, "Type").lower()
                    tmpl = tmpl or typ.endswith("attachedtemplate")
                    obj = obj or typ.endswith("oleobject")
            if tmpl:
                flags.add("OFFICE_EXTERNAL_TEMPLATE"); points += 2
            if obj:
                flags.add("OFFICE_EXTERNAL_OBJECT"); points += 1
        elif "androidmanifest.xml" in lower or ("meta-inf/manifest.mf" in lower and any(n.endswith(".class") for n in lower)):
            flags.add("EXECUTABLE_PACKAGE"); points += 1
    return points, flags, coverage


# ---------------------------------------------------------------- OLE (doc/xls/ppt lama), RTF, LNK
def _analyze_ole(path: str, detected: str) -> Tuple[int, Set[str], float]:
    with open(path, "rb") as f:
        data = f.read()
    flags: Set[str] = set()
    points = 0
    if b"_\x00V\x00B\x00A\x00_\x00P\x00R\x00O\x00J\x00E\x00C\x00T\x00" in data or b"V\x00B\x00A\x00" in data:
        flags.add("OFFICE_MACRO"); points += 2
    if b"Equation.3" in data or b"E\x00q\x00u\x00a\x00t\x00i\x00o\x00n\x00.\x003\x00" in data:
        flags.add("OFFICE_EQUATION_EDITOR"); points += 2
    if b"P\x00a\x00c\x00k\x00a\x00g\x00e\x00" in data:
        flags.add("OFFICE_EMBEDDED_OBJECT"); points += 1
    return points, flags, 1.0


def _analyze_rtf(path: str, detected: str) -> Tuple[int, Set[str], float]:
    with open(path, "rb") as f:
        data = f.read().lower()
    flags: Set[str] = set()
    points = 0
    if b"\\objdata" in data:
        flags.add("RTF_EMBEDDED_OBJECT"); points += 1
        if b"\\objupdate" in data:
            points += 1
    return points, flags, 1.0


def _analyze_lnk(path: str, detected: str) -> Tuple[int, Set[str], float]:
    return 2, {"LNK_FILE"}, 1.0          # jalan pintas Windows hampir tak pernah sah sebagai unggahan web


# ---------------------------------------------------------------- Gambar
_PAYLOAD_SIGS = (b"PK\x03\x04", b"%PDF-", b"Rar!", b"7z\xbc\xaf")      # cukup panjang untuk dicari di mana saja di ekor


def _analyze_image(path: str, detected: str) -> Tuple[int, Set[str], float]:
    with open(path, "rb") as f:
        data = f.read()
    flags: Set[str] = set()
    points = 0
    end = -1
    if detected == "png":
        i = data.rfind(b"IEND\xaeB`\x82")
        end = i + 8 if i >= 0 else -1
    elif detected == "jpeg":
        i = data.rfind(b"\xff\xd9")
        end = i + 2 if i >= 0 else -1
    if end > 0:
        tail = data[end:]
        if len(tail) > 64:
            flags.add("IMAGE_TRAILING_DATA")
        if tail.startswith(b"MZ") or any(s in tail for s in _PAYLOAD_SIGS):     # "MZ" 2 byte: hanya tepat di awal ekor
            flags.add("IMAGE_EMBEDDED_PAYLOAD"); points += 2
    if b"This program cannot be run in DOS mode" in data[16:] and "IMAGE_EMBEDDED_PAYLOAD" not in flags:
        flags.add("IMAGE_EMBEDDED_PAYLOAD"); points += 2
    return points, flags, 1.0


# ---------------------------------------------------------------- Teks / skrip / SVG
_TEXT_CATS: Dict[str, List[re.Pattern]] = {}
for _cat, _pats in {
    "ps_encoded": [r"frombase64string", r"powershell[^\n]{0,200}\s-e(nc|ncodedcommand)?\s+[a-z0-9+/=]{20,}"],
    "ps_hidden": [r"-w(indowstyle)?\s+hidden", r"-executionpolicy\s+bypass", r"-nop(rofile)?\s"],
    "download": [r"downloadstring", r"downloadfile", r"invoke-webrequest", r"net\.webclient", r"start-bitstransfer",
                 r"certutil[^\n]{0,80}-urlcache", r"bitsadmin[^\n]{0,80}/transfer", r"(curl|wget)\s[^|\n]{0,200}\|\s*(ba)?sh"],
    "exec": [r"invoke-expression", r"\biex[\s(]", r"wscript\.shell", r"shell\.application", r"activexobject", r"\bmshta\b",
             r"regsvr32[^\n]{0,80}(/i:|scrobj)", r"rundll32[^\n]{0,20}javascript"],
    "js_obf": [r"fromcharcode", r"unescape\(", r"eval\(\s*(atob|unescape)"],
    "persist": [r"schtasks\s+/create", r"vssadmin\s+delete\s+shadows", r"set-mppreference\s+-disable", r"add-mppreference\s+-exclusion"],
    "svg_script": [r"<svg[^>]{0,500}>[\s\S]{0,5000}?<script"],
}.items():
    _TEXT_CATS[_cat] = [re.compile(p) for p in _pats]
_B64_BLOB = re.compile(r"[a-z0-9+/=]{500,}")


def _analyze_text(path: str, detected: str) -> Tuple[int, Set[str], float]:
    with open(path, "rb") as f:
        raw = f.read(1_000_000)
    if raw[:2] in (b"\xff\xfe", b"\xfe\xff"):
        text = raw.decode("utf-16", "replace")
    else:
        text = raw.decode("utf-8", "replace")
    text = text.lower()
    hits = [c for c, pats in _TEXT_CATS.items() if any(p.search(text) for p in pats)]
    flags: Set[str] = set()
    if hits:
        flags.add("SCRIPT_RISKY_PATTERN")
    points = len(hits)
    if _B64_BLOB.search(text):
        flags.add("SCRIPT_OBFUSCATED"); points += 1
    return min(points, 4), flags, 1.0


ANALYZERS = {
    "pdf": _analyze_pdf, "zip": _analyze_zip, "ole": _analyze_ole, "rtf": _analyze_rtf, "lnk": _analyze_lnk,
    "jpeg": _analyze_image, "png": _analyze_image, "gif": _analyze_image, "text": _analyze_text,
}


def has_analyzer(detected: str) -> bool:
    return detected in ANALYZERS


def analyze_file(path: str, detected: str, declared_ext: Optional[str]) -> dict:
    """Dijalankan di PROSES ANAK. Hasil sederhana agar bisa dikirim antar-proses."""
    points, flags, coverage = ANALYZERS[detected](path, detected)
    flags = set(flags)
    allowed = EXT_FAMILY.get(declared_ext or "")
    if allowed and detected not in allowed:
        flags.add("FORMAT_MISMATCH"); points += 1
    return {"points": int(points), "flags": sorted(flags), "coverage": float(coverage)}
