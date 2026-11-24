"""Model URL (LightGBM) + kartu model. Opsional: tanpa model, layanan memakai aturan + daftar hitam saja."""
import logging
from typing import List

from .registry import ModelCard, load_model_card
from .urlfeatures import FEATURE_NAMES, PREPROCESSING_VERSION

log = logging.getLogger("stiksafe_ml")


class UrlModelEngine:
    confidence_type = "raw_score"

    def __init__(self, card: ModelCard, booster):
        if card.feature_dim != len(FEATURE_NAMES):
            raise ValueError("jumlah fitur model URL tidak cocok dengan kode ekstraksi")
        if card.preprocessing_version != PREPROCESSING_VERSION:
            raise ValueError("versi ekstraksi fitur model URL tidak cocok; latih ulang model")
        kept = card.metrics.get("kept_features")          # fitur yang dipakai; sisanya di-nol-kan (sama dengan saat latih)
        self._keep = [n in set(kept) for n in FEATURE_NAMES] if kept else None
        self.card, self._booster = card, booster
        self.model_version = card.model_version
        self.preprocessing_version = card.preprocessing_version
        self.dataset_version = card.dataset_version
        self.thresholds = card.thresholds
        self.status = card.status
        self.FEATURE_DIM = card.feature_dim

    @classmethod
    def from_dir(cls, directory: str) -> "UrlModelEngine":
        card, path = load_model_card(directory)          # verifikasi hash
        import lightgbm as lgb
        log.info("model URL dimuat: %s (%s)", card.model_version, card.status)
        return cls(card, lgb.Booster(model_file=path))

    def predict(self, features: List[float]) -> float:
        import numpy as np
        if self._keep:
            features = [v if k else 0.0 for v, k in zip(features, self._keep)]
        return float(self._booster.predict(np.asarray([features], dtype=np.float32))[0])
