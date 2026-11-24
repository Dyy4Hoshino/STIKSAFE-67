"""Engine NYATA: ekstraksi fitur thrember (EMBER feature v3) + model LightGBM (PRD 5.3, 5.4).

Dependensi berat (lightgbm, numpy, thrember) diimpor malas agar paket tetap bisa diuji tanpa itu.
"""
import logging
from typing import Callable, List, Set

from .childproc import run_in_child
from .engine import FeatureResult
from .formats import FormatResult
from .registry import ModelCard, load_model_card

log = logging.getLogger("stiksafe_ml")


def signify_compatible(ver: str) -> bool:
    """thrember 0.1.0 hanya cocok dengan signify < 0.8 (>=0.8: CertificateStore bukan list; >=0.9: SignedPEFile dipindah)."""
    try:
        major, minor = (int(x) for x in ver.split(".")[:2])
    except ValueError:
        return False
    return (major, minor) < (0, 8)


def vectorize(extractor, data: bytes):
    """Mengubah byte file menjadi vektor fitur. Nama method dicari dulu karena API thrember
    diverifikasi lewat scripts/check_thrember.py di komputer Anda."""
    if hasattr(extractor, "feature_vector"):
        return extractor.feature_vector(data)
    if hasattr(extractor, "raw_features"):
        return extractor.process_raw_features(extractor.raw_features(data))
    raise RuntimeError("API ekstraktor thrember tidak dikenali")


def thrember_extract(path: str) -> List[float]:
    """Berjalan di PROSES ANAK. Hanya membaca byte, tidak pernah mengeksekusi file."""
    from thrember.features import PEFeatureExtractor
    with open(path, "rb") as f:
        data = f.read()
    return [float(x) for x in vectorize(PEFeatureExtractor(), data)]


class EmberEngine:
    is_stub = False
    confidence_type = "raw_score"
    supported_types = ["exe", "dll"]
    supported_families: Set[str] = {"pe"}

    def __init__(self, card: ModelCard, booster, extract_fn: Callable = thrember_extract,
                 extract_timeout: float = 30.0):
        self.card, self._booster, self._extract_fn = card, booster, extract_fn
        self._timeout = extract_timeout
        self.model_version = card.model_version
        self.preprocessing_version = card.preprocessing_version
        self.dataset_version = card.dataset_version
        self.thresholds = card.thresholds
        self.FEATURE_DIM = card.feature_dim
        self.status = card.status

    @classmethod
    def from_dir(cls, directory: str, extract_timeout: float = 30.0) -> "EmberEngine":
        card, model_path = load_model_card(directory)     # verifikasi hash di sini
        from importlib.metadata import PackageNotFoundError, version
        try:
            sig = version("signify")
        except PackageNotFoundError:
            raise ValueError('pustaka signify tidak terpasang (pip install "signify<0.8")')
        if not signify_compatible(sig):
            raise ValueError(f'signify {sig} tidak cocok dengan thrember; jalankan: pip install "signify<0.8"')
        import lightgbm as lgb
        booster = lgb.Booster(model_file=model_path)
        log.info("model dimuat: %s (%s)", card.model_version, card.status)
        return cls(card, booster, thrember_extract, extract_timeout)

    def extract(self, path: str, fmt: FormatResult, sha256: str) -> FeatureResult:
        vec = run_in_child(self._extract_fn, (path,), self._timeout)   # RuntimeError -> PREPROCESSING_FAILED
        flags: Set[str] = set()
        coverage = 1.0
        if fmt.partial:                       # struktur PE rusak: jangan percaya penuh -> unknown (7.5)
            flags.add("PARSE_PARTIAL")
            coverage = 0.4
        return FeatureResult(list(vec), coverage, flags)

    def predict(self, features: List[float]) -> float:
        import numpy as np
        arr = np.asarray([features], dtype=np.float32)
        return float(self._booster.predict(arr)[0])
