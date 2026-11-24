"""Antarmuka engine + StubEngine (PRD 18). Engine nyata (LightGBM dll.) mengimplementasi antarmuka yang sama.

StubEngine HANYA untuk non-produksi: hasilnya deterministik dari SHA-256, bukan hasil model.
"""
import math
from dataclasses import dataclass, field
from typing import List, Protocol, Set

from .formats import FormatResult
from .labels import Thresholds


@dataclass
class FeatureResult:
    vector: List[float]
    coverage: float                       # proporsi fitur yang berhasil diekstrak (FR-PRE-07)
    flags: Set[str] = field(default_factory=set)  # PARSE_PARTIAL, ENCRYPTED_CONTENT, ...


class Engine(Protocol):
    model_version: str
    preprocessing_version: str
    dataset_version: str
    confidence_type: str
    supported_types: List[str]
    supported_families: Set[str]
    thresholds: Thresholds
    is_stub: bool

    def extract(self, path: str, fmt: FormatResult, sha256: str) -> FeatureResult: ...
    def predict(self, features: List[float]) -> float: ...


def validate_features(fr: FeatureResult, expected_dim: int) -> bool:
    """FR-PRE-05: dimensi, tipe, NaN/Inf."""
    return (len(fr.vector) == expected_dim
            and all(isinstance(v, (int, float)) and math.isfinite(v) for v in fr.vector)
            and 0.0 <= fr.coverage <= 1.0)


class StubEngine:
    """Skor deterministik dari SHA-256; tetap melewati ResultMapper sehingga aturan 7.5 teruji."""
    is_stub = True
    model_version = "stiksafe-ml-0.1.0-stub"
    preprocessing_version = "feat-0.1.0-stub"
    dataset_version = "none"
    confidence_type = "raw_score"
    supported_types = ["exe", "dll"]
    supported_families = {"pe"}
    FEATURE_DIM = 2
    # Ambang placeholder KHUSUS stub. Ambang nyata berasal dari evaluasi (9.6).
    thresholds = Thresholds(t_suspicious=0.5, t_malicious=0.8, c_min=0.5)

    def extract(self, path: str, fmt: FormatResult, sha256: str) -> FeatureResult:
        score = int(sha256[:8], 16) / 0xFFFFFFFF
        flags: Set[str] = set()
        coverage = 1.0
        if fmt.partial:
            flags.add("PARSE_PARTIAL")
            coverage = 0.4
        return FeatureResult([score, coverage], coverage, flags)

    def predict(self, features: List[float]) -> float:
        return features[0]
