"""Parsing & normalisasi URL (FITUR 2). URL TIDAK PERNAH dibuka/di-fetch: hanya teksnya dianalisis."""
import ipaddress
import re
from dataclasses import dataclass
from typing import Optional
from urllib.parse import urlsplit

from .errors import MLError

MAX_URL_LEN = 2048
_NUMERIC_HOST = re.compile(r"^(0x[0-9a-f]+|\d+)(\.(0x[0-9a-f]+|\d+)){0,3}$")   # 2130706433, 0x7f.0.0.1
_SCHEME_LIKE = re.compile(r"^[A-Za-z][A-Za-z0-9+.\-]*:(?!\d)")   # "skema:" yang BUKAN diikuti nomor port
_HOST_OK = re.compile(r"^[a-z0-9.\-_:]+$")


@dataclass(frozen=True)
class ParsedUrl:
    scheme: str
    host: str            # huruf kecil, bentuk ASCII/punycode
    port: Optional[int]
    path: str
    query: str
    has_userinfo: bool   # ada "user:pass@" sebelum host (trik penyamaran)
    is_ip: bool
    has_punycode: bool

    @property
    def normalized(self) -> str:
        port = f":{self.port}" if self.port else ""
        return f"{self.scheme}://{self.host}{port}{self.path or '/'}" + (f"?{self.query}" if self.query else "")


def parse_url(raw) -> ParsedUrl:
    """Raise MLError('ML_INVALID_REQUEST') untuk input yang tidak sah. Hanya http/https."""
    if not isinstance(raw, str):
        raise MLError("ML_INVALID_REQUEST")
    s = raw.strip()
    if not s or len(s) > MAX_URL_LEN or any(ord(c) < 32 or ord(c) == 127 for c in s):
        raise MLError("ML_INVALID_REQUEST")
    if "://" not in s:
        if _SCHEME_LIKE.match(s):               # "mailto:x", "javascript:..", "data:.." bukan alamat web
            raise MLError("ML_INVALID_REQUEST")
        s = "http://" + s                       # pengguna sering menempel "contoh.com/login" atau "localhost:8080"
    try:
        parts = urlsplit(s)
        port = parts.port
    except ValueError:
        raise MLError("ML_INVALID_REQUEST")
    scheme = parts.scheme.lower()
    if scheme not in ("http", "https"):
        raise MLError("ML_INVALID_REQUEST")
    host = (parts.hostname or "").rstrip(".")
    if not host:
        raise MLError("ML_INVALID_REQUEST")
    try:
        host = host.encode("idna").decode("ascii").lower()
    except UnicodeError:
        raise MLError("ML_INVALID_REQUEST")
    if not _HOST_OK.match(host):
        raise MLError("ML_INVALID_REQUEST")
    try:
        ipaddress.ip_address(host)
        is_ip = True
    except ValueError:
        is_ip = bool(_NUMERIC_HOST.match(host))
    return ParsedUrl(
        scheme=scheme, host=host, port=port, path=parts.path, query=parts.query,
        has_userinfo="@" in parts.netloc, is_ip=is_ip,
        has_punycode=any(l.startswith("xn--") for l in host.split(".")),
    )
