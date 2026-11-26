"""Validasi metadata request (PRD 7.3 dan 7.4)."""
import json
import re
from dataclasses import dataclass
from datetime import datetime
from typing import Any, Optional, Tuple
from urllib.parse import urlparse

from .errors import MLError

# Laravel HasUlids menghasilkan ULID lowercase; kontrak menerima uppercase/lowercase.
ULID_RE = re.compile(r"^[0-7][0-9A-HJKMNP-TV-Z]{25}$", re.IGNORECASE)
UUID_RE = re.compile(r"^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$")
SHA256_RE = re.compile(r"^[0-9a-fA-F]{64}$")


@dataclass(frozen=True)
class Metadata:
    scan_id: str
    request_id: str
    sha256: str
    size_bytes: int
    submitted_at: str
    declared_extension: Optional[str] = None
    detected_mime: Optional[str] = None
    callback_url: Optional[str] = None
    extension_chain: Tuple[str, ...] = ()   # mis. ("pdf", "exe"); BUKAN nama file


def peek_ids(raw: Any) -> Tuple[Optional[str], Optional[str]]:
    """Ambil (scan_id, request_id) apa adanya untuk body error; aman bila metadata rusak."""
    try:
        d = raw if isinstance(raw, dict) else json.loads(raw)
        s, r = d.get("scan_id"), d.get("request_id")
        return (s.lower() if isinstance(s, str) and ULID_RE.match(s) else None,
                r if isinstance(r, str) and UUID_RE.match(r) else None)
    except Exception:
        return None, None


def parse_metadata(raw: Any, allowed_callback_hosts: Tuple[str, ...] = ()) -> Metadata:
    try:
        d = raw if isinstance(raw, dict) else json.loads(raw)
    except Exception:
        raise MLError("ML_INVALID_REQUEST")
    if not isinstance(d, dict):
        raise MLError("ML_INVALID_REQUEST")

    def need_str(key: str) -> str:
        v = d.get(key)
        if not isinstance(v, str) or not v:
            raise MLError("ML_INVALID_REQUEST")
        return v

    scan_id, request_id = need_str("scan_id"), need_str("request_id")
    sha256, submitted_at = need_str("sha256"), need_str("submitted_at")
    if not ULID_RE.match(scan_id) or not UUID_RE.match(request_id) or not SHA256_RE.match(sha256):
        raise MLError("ML_INVALID_REQUEST")

    size = d.get("size_bytes")
    if isinstance(size, bool) or not isinstance(size, int) or size <= 0:
        raise MLError("ML_INVALID_REQUEST")

    try:  # ISO-8601 UTC
        if not submitted_at.endswith("Z"):
            raise ValueError
        datetime.fromisoformat(submitted_at[:-1] + "+00:00")
    except ValueError:
        raise MLError("ML_INVALID_REQUEST")

    def opt_str(key: str) -> Optional[str]:
        v = d.get(key)
        if v is None:
            return None
        if not isinstance(v, str):
            raise MLError("ML_INVALID_REQUEST")
        return v

    callback_url = opt_str("callback_url")
    if callback_url is not None:
        host = urlparse(callback_url).hostname
        if not host or host not in allowed_callback_hosts:  # cegah SSRF (7.8)
            raise MLError("ML_INVALID_REQUEST")

    chain_raw = d.get("extension_chain")
    chain: Tuple[str, ...] = ()
    if chain_raw is not None:
        if (not isinstance(chain_raw, list) or len(chain_raw) > 8
                or any(not isinstance(x, str) or len(x) > 32 for x in chain_raw)):
            raise MLError("ML_INVALID_REQUEST")
        chain = tuple(x.lower().lstrip(".") for x in chain_raw)

    ext = opt_str("declared_extension")
    declared = ext.lower().lstrip(".") if ext else (chain[-1] if chain else None)
    return Metadata(
        scan_id=scan_id.lower(), request_id=request_id.lower(), sha256=sha256.lower(),
        size_bytes=size, submitted_at=submitted_at,
        declared_extension=declared,
        detected_mime=opt_str("detected_mime"), callback_url=callback_url,
        extension_chain=chain,
    )
