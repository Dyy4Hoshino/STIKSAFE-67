"""Katalog kode error ML (PRD 7.6). Kode error TIDAK PERNAH dipakai sebagai label (BR-ML-16)."""
from dataclasses import dataclass
from typing import Optional


@dataclass(frozen=True)
class ErrorSpec:
    http: int
    retryable: bool
    message: str  # Bahasa Indonesia, tanpa detail internal (PRD 10.1)


CATALOG = {
    "ML_AUTH_FAILED": ErrorSpec(401, False, "Autentikasi layanan tidak sah."),
    "ML_INVALID_REQUEST": ErrorSpec(422, False, "Request tidak sesuai kontrak."),
    "ML_FILE_TOO_LARGE": ErrorSpec(413, False, "Ukuran file melebihi batas."),
    "ML_HASH_MISMATCH": ErrorSpec(422, True, "Hash file yang diterima tidak sesuai dengan metadata."),
    "ML_UNSUPPORTED_FILE": ErrorSpec(422, False, "Tipe file tidak didukung oleh model."),
    "ML_FILE_CORRUPT": ErrorSpec(422, False, "File kosong atau tidak dapat dibaca."),
    "ML_PREPROCESSING_FAILED": ErrorSpec(500, True, "Ekstraksi fitur gagal."),
    "ML_MODEL_UNAVAILABLE": ErrorSpec(503, True, "Model belum siap."),
    "ML_INFERENCE_TIMEOUT": ErrorSpec(504, True, "Inferensi melewati batas waktu."),
    "ML_INFERENCE_FAILED": ErrorSpec(500, True, "Inferensi gagal."),
    "ML_INVALID_MODEL_OUTPUT": ErrorSpec(500, True, "Output model tidak valid."),
    "ML_OVERLOADED": ErrorSpec(429, True, "Layanan sedang penuh, coba lagi nanti."),
    "ML_INTERNAL_ERROR": ErrorSpec(500, True, "Terjadi kesalahan internal."),
}


class MLError(Exception):
    """Kegagalan terstruktur. Tidak pernah membawa isi file, path, atau stack trace."""

    def __init__(self, code: str, retry_after: Optional[int] = None):
        if code not in CATALOG:
            raise ValueError(f"kode error tidak dikenal: {code}")
        super().__init__(code)
        self.code = code
        self.spec = CATALOG[code]
        self.retry_after = retry_after
