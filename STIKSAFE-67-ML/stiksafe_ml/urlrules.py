"""Aturan heuristik URL (FITUR 2, lapis 2). Setiap aturan punya poin; total >= 2 -> minimal 'suspicious'.
Aturan hanya MENAIKKAN kehati-hatian; tidak pernah menyatakan 'malicious' dan tidak pernah menurunkan label."""
from typing import Set, Tuple

from .urlfeatures import has_exec_ext, is_shortener, keyword_hits, n_subdomains
from .urlnorm import ParsedUrl

SUSPICIOUS_POINTS = 2


def evaluate(p: ParsedUrl) -> Tuple[int, Set[str]]:
    flags: Set[str] = set()
    points = 0

    def hit(code: str, pts: int):
        nonlocal points
        flags.add(code); points += pts

    if p.is_ip:
        hit("URL_IP_HOST", 2)
    if p.has_userinfo:
        hit("URL_USERINFO", 2)
    if has_exec_ext(p):
        hit("URL_EXEC_DOWNLOAD", 2)
    if p.has_punycode:
        hit("URL_PUNYCODE", 1)
    if n_subdomains(p) >= 4:
        hit("URL_MANY_SUBDOMAINS", 1)
    default = 443 if p.scheme == "https" else 80
    if p.port is not None and p.port != default:
        hit("URL_NONSTANDARD_PORT", 1)
    if is_shortener(p):
        hit("URL_SHORTENER", 1)
    if keyword_hits(p) >= 3:
        hit("URL_SUSPICIOUS_KEYWORDS", 1)
    return points, flags
