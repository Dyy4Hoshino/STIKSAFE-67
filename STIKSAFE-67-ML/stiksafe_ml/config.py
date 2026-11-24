"""Konfigurasi (PRD 17). Tidak ada angka tetap tersebar di kode."""
import os
from dataclasses import dataclass, field
from typing import Tuple


def _int(name: str, default: int) -> int:
    return int(os.environ.get(name, default))


def _list(name: str) -> Tuple[str, ...]:
    raw = os.environ.get(name, "")
    return tuple(x.strip() for x in raw.split(",") if x.strip())


@dataclass(frozen=True)
class Settings:
    env: str = "local"                      # local | staging | production
    max_file_size_mb: int = 25
    max_concurrent: int = 4
    total_deadline_sec: int = 100           # harus < timeout backend (120)
    extract_timeout_sec: int = 30
    infer_timeout_sec: int = 10
    idempotency_ttl_sec: int = 3600
    tmp_dir: str = "/tmp/stiksafe"
    tmp_sweep_age_sec: int = 300
    retry_after_sec: int = 5
    service_tokens: Tuple[str, ...] = field(default_factory=tuple, repr=False)  # maks 2 aktif
    callback_allowed_hosts: Tuple[str, ...] = ()
    stub_mode: bool = False
    model_dir: str = ""                      # folder berisi model_card.json + berkas model
    url_model_dir: str = ""                  # model URL (opsional; tanpa model -> aturan + daftar hitam saja)
    url_blocklist_path: str = ""             # berkas daftar hitam URL (opsional)
    url_allow_safe: bool = False             # model URL BOLEH berlabel safe? default TIDAK sampai teruji di luar dataset
    url_model_solo_flag: bool = False        # skor model sangat tinggi BOLEH menandai suspicious sendirian? default TIDAK

    @property
    def max_file_size_bytes(self) -> int:
        return self.max_file_size_mb * 1024 * 1024

    def validate(self) -> None:
        if len(self.service_tokens) > 2:
            raise ValueError("maksimal 2 service token aktif (PRD 7.2)")
        if not self.service_tokens:
            raise ValueError("service token wajib diisi (deny by default)")
        if self.total_deadline_sec >= 120:
            raise ValueError("total_deadline_sec harus < timeout backend 120 dtk")
        if self.stub_mode and self.env == "production":
            raise ValueError("mode stub dilarang di production (PRD 18)")

    @classmethod
    def from_env(cls) -> "Settings":
        s = cls(
            env=os.environ.get("ML_ENV", "local"),
            max_file_size_mb=_int("ML_MAX_FILE_SIZE_MB", 25),
            max_concurrent=_int("ML_MAX_CONCURRENT", 4),
            total_deadline_sec=_int("ML_TOTAL_DEADLINE_SEC", 100),
            extract_timeout_sec=_int("ML_EXTRACT_TIMEOUT_SEC", 30),
            infer_timeout_sec=_int("ML_INFER_TIMEOUT_SEC", 10),
            idempotency_ttl_sec=_int("ML_IDEMPOTENCY_TTL_SEC", 3600),
            tmp_dir=os.environ.get("ML_TMP_DIR", "/tmp/stiksafe"),
            retry_after_sec=_int("ML_RETRY_AFTER_SEC", 5),
            service_tokens=_list("ML_SERVICE_TOKENS"),
            callback_allowed_hosts=_list("ML_CALLBACK_ALLOWED_HOSTS"),
            stub_mode=os.environ.get("ML_STUB_MODE", "false").lower() == "true",
            model_dir=os.environ.get("ML_MODEL_DIR", ""),
            url_model_dir=os.environ.get("ML_URL_MODEL_DIR", ""),
            url_blocklist_path=os.environ.get("ML_URL_BLOCKLIST", ""),
            url_allow_safe=os.environ.get("ML_URL_ALLOW_SAFE", "false").lower() == "true",
            url_model_solo_flag=os.environ.get("ML_URL_MODEL_SOLO_FLAG", "false").lower() == "true",
        )
        s.validate()
        return s
