"""Kartu model (model_card.json) + verifikasi integritas berkas model (PRD 9.5, FR-INF-01)."""
import hashlib
import json
import os
from dataclasses import dataclass
from typing import Any, Dict, Tuple

from .labels import Thresholds

STATUSES = ("development", "staging", "production", "rolled_back", "retired")
REQUIRED = ("model_version", "model_file", "model_sha256", "dataset_version",
            "preprocessing_version", "feature_dim", "thresholds", "status")


@dataclass(frozen=True)
class ModelCard:
    model_version: str
    model_file: str
    model_sha256: str
    dataset_version: str
    preprocessing_version: str
    feature_dim: int
    thresholds: Thresholds
    status: str
    provisional: bool
    metrics: Dict[str, Any]


def sha256_file(path: str) -> str:
    h = hashlib.sha256()
    with open(path, "rb") as f:
        for chunk in iter(lambda: f.read(1 << 20), b""):
            h.update(chunk)
    return h.hexdigest()


def load_model_card(directory: str) -> Tuple[ModelCard, str]:
    """Kembalikan (kartu, path_model). Gagal keras bila kartu/berkas/hash tidak sah."""
    with open(os.path.join(directory, "model_card.json"), "r", encoding="utf-8") as f:
        d = json.load(f)
    missing = [k for k in REQUIRED if k not in d]
    if missing:
        raise ValueError(f"model_card.json kurang field: {missing}")
    if d["status"] not in STATUSES:
        raise ValueError("status model tidak dikenal")
    t = d["thresholds"]
    th = Thresholds(t["t_suspicious"], t["t_malicious"], t["c_min"])   # validasi bawaan
    model_path = os.path.join(directory, os.path.basename(d["model_file"]))
    if not os.path.isfile(model_path):
        raise ValueError("berkas model tidak ditemukan")
    if sha256_file(model_path) != d["model_sha256"]:
        raise ValueError("hash berkas model tidak cocok (berkas rusak atau diubah)")
    card = ModelCard(d["model_version"], d["model_file"], d["model_sha256"], d["dataset_version"],
                     d["preprocessing_version"], int(d["feature_dim"]), th, d["status"],
                     bool(d.get("provisional", True)), d.get("metrics", {}))
    return card, model_path
