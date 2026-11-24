"""Daftar hitam URL (FITUR 2, lapis 1): kecocokan PERSIS pada URL ternormalisasi.
Sengaja TIDAK mencocokkan per-host, supaya host bersama (mis. layanan hosting besar) tidak membuat salah tuduh."""
import os
from typing import Iterable, Set

from .errors import MLError
from .urlnorm import ParsedUrl, parse_url


class Blocklist:
    def __init__(self, urls: Iterable[str] = ()):
        self._urls: Set[str] = set(urls)

    def __len__(self) -> int:
        return len(self._urls)

    def match(self, p: ParsedUrl) -> bool:
        return p.normalized in self._urls

    @classmethod
    def from_file(cls, path: str) -> "Blocklist":
        urls = set()
        with open(path, "r", encoding="utf-8") as f:
            for line in f:
                line = line.strip()
                if not line or line.startswith("#"):
                    continue
                try:
                    urls.add(parse_url(line).normalized)
                except MLError:
                    continue
        return cls(urls)


def load_optional(path: str) -> Blocklist:
    return Blocklist.from_file(path) if path and os.path.isfile(path) else Blocklist()
