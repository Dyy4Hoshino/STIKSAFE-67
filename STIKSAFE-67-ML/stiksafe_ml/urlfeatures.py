"""Fitur leksikal URL. KODE INI DIPAKAI SAMA PERSIS saat melatih dan saat melayani (anti training-serving skew)."""
import math
from collections import Counter
from typing import Dict, List

from .urlnorm import ParsedUrl

PREPROCESSING_VERSION = "urlfeat-v2"
SHORTENERS = frozenset({"bit.ly", "tinyurl.com", "t.co", "goo.gl", "ow.ly", "is.gd", "buff.ly", "cutt.ly",
                        "rebrand.ly", "shorturl.at", "s.id", "rb.gy", "tiny.cc", "lnkd.in"})
URL_EXEC_EXT = frozenset({"exe", "scr", "bat", "cmd", "com", "pif", "msi", "vbs", "vbe", "hta", "lnk", "ps1", "apk"})
KEYWORDS = ("login", "signin", "verify", "secure", "account", "update", "bank", "password", "confirm",
            "wallet", "paypal", "ebay", "apple", "microsoft", "support", "billing", "unlock", "invoice")

FEATURE_NAMES: List[str] = [
    "url_len", "host_len", "path_len", "query_len", "host_dots", "n_subdomains", "host_hyphens",
    "host_digits", "host_digit_ratio", "url_digit_ratio", "url_letter_ratio", "n_amp", "n_eq",
    "n_underscore", "n_percent", "n_slash_path", "is_ip", "nonstandard_port", "is_https",
    "has_punycode", "has_userinfo", "tld_len", "host_entropy", "path_entropy", "url_entropy",
    "longest_label_len", "max_segment_len", "n_params", "exec_ext", "keyword_count", "is_shortener",
    "has_path", "double_slash_in_path", "has_www",
]


def entropy(s: str) -> float:
    if not s:
        return 0.0
    n = len(s)
    return -sum(c / n * math.log2(c / n) for c in Counter(s).values())


def core_host(p: ParsedUrl) -> str:
    """Host tanpa awalan 'www.' (www tidak informatif; di dataset latih ia jalan pintas)."""
    return p.host[4:] if p.host.startswith("www.") and len(p.host) > 4 else p.host


def n_subdomains(p: ParsedUrl) -> int:
    return 0 if p.is_ip else max(0, len(core_host(p).split(".")) - 2)    # aproksimasi tanpa daftar public suffix


def is_shortener(p: ParsedUrl) -> bool:
    return any(p.host == d or p.host.endswith("." + d) for d in SHORTENERS)


def has_exec_ext(p: ParsedUrl) -> bool:
    last = p.path.rsplit("/", 1)[-1].lower()
    return "." in last and last.rsplit(".", 1)[-1] in URL_EXEC_EXT


def keyword_hits(p: ParsedUrl) -> int:
    text = f"{p.host}{p.path}?{p.query}".lower()
    return sum(1 for k in KEYWORDS if k in text)


def extract(p: ParsedUrl) -> List[float]:
    url = p.normalized
    ch = core_host(p)
    labels = ch.split(".")
    segs = [x for x in p.path.split("/") if x]
    default_port = 443 if p.scheme == "https" else 80
    letters = sum(c.isalpha() for c in url)
    digits = sum(c.isdigit() for c in url)
    hd = sum(c.isdigit() for c in ch)
    f: Dict[str, float] = {
        "url_len": len(url), "host_len": len(ch), "path_len": len(p.path), "query_len": len(p.query),
        "host_dots": ch.count("."), "n_subdomains": n_subdomains(p), "host_hyphens": ch.count("-"),
        "host_digits": hd, "host_digit_ratio": hd / len(ch), "url_digit_ratio": digits / len(url),
        "url_letter_ratio": letters / len(url), "n_amp": p.query.count("&"), "n_eq": p.query.count("="),
        "n_underscore": url.count("_"), "n_percent": url.count("%"), "n_slash_path": p.path.count("/"),
        "is_ip": int(p.is_ip), "nonstandard_port": int(p.port is not None and p.port != default_port),
        "is_https": int(p.scheme == "https"), "has_punycode": int(p.has_punycode),
        "has_userinfo": int(p.has_userinfo), "tld_len": 0 if p.is_ip else len(labels[-1]),
        "host_entropy": entropy(ch), "path_entropy": entropy(p.path), "url_entropy": entropy(url),
        "longest_label_len": max(len(l) for l in labels), "max_segment_len": max((len(x) for x in segs), default=0),
        "n_params": sum(1 for x in p.query.split("&") if x), "exec_ext": int(has_exec_ext(p)),
        "keyword_count": keyword_hits(p), "is_shortener": int(is_shortener(p)),
        "has_path": int(p.path not in ("", "/")), "double_slash_in_path": int("//" in p.path),
        "has_www": int(p.host.startswith("www.")),
    }
    return [float(f[n]) for n in FEATURE_NAMES]
