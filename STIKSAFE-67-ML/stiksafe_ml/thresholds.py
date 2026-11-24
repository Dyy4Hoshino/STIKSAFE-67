"""Menentukan ambang label dari skor validasi (PRD 9.6, BR-ML-05). Hanya butuh numpy."""
import math
from typing import Dict, List

import numpy as np


def flagged_fraction(scores: np.ndarray, threshold: float) -> float:
    return float(np.mean(scores >= threshold)) if len(scores) else 0.0


def threshold_for_fpr(benign_scores: np.ndarray, target_fpr: float) -> float:
    """Ambang TERKECIL sehingga paling banyak `target_fpr` file benign berskor >= ambang."""
    if len(benign_scores) == 0:
        raise ValueError("tidak ada sampel benign")
    s = np.sort(benign_scores)
    n = len(s)
    k = min(n, max(1, math.ceil(n * (1.0 - target_fpr))))
    t = float(np.nextafter(s[k - 1], np.float64(1.0)))   # tepat di atas skor ke-k -> sisanya < target
    return min(t, 1.0)


def select_thresholds(y_true, scores, fpr_malicious: float = 0.001,
                      fpr_suspicious: float = 0.01) -> Dict[str, float]:
    y = np.asarray(y_true); s = np.asarray(scores, dtype=np.float64)
    benign = s[y == 0]
    t_m = threshold_for_fpr(benign, fpr_malicious)
    t_s = min(threshold_for_fpr(benign, fpr_suspicious), t_m)
    return {"t_suspicious": t_s, "t_malicious": t_m, "c_min": 0.5}


def summarize(y_true, scores, th: Dict[str, float]) -> Dict[str, float]:
    y = np.asarray(y_true); s = np.asarray(scores, dtype=np.float64)
    ben, mal = s[y == 0], s[y == 1]
    return {
        "n_benign": int(len(ben)), "n_malicious": int(len(mal)),
        "fpr_at_t_suspicious": flagged_fraction(ben, th["t_suspicious"]),
        "fpr_at_t_malicious": flagged_fraction(ben, th["t_malicious"]),
        "recall_at_t_suspicious": flagged_fraction(mal, th["t_suspicious"]),
        "recall_at_t_malicious": flagged_fraction(mal, th["t_malicious"]),
        "roc_auc": roc_auc(y, s),
    }


def roc_auc(y_true, scores) -> float:
    y = np.asarray(y_true); s = np.asarray(scores, dtype=np.float64)
    pos, neg = s[y == 1], s[y == 0]
    if len(pos) == 0 or len(neg) == 0:
        return float("nan")
    order = np.argsort(np.concatenate([pos, neg]), kind="mergesort")
    allv = np.concatenate([pos, neg])[order]
    ranks = np.empty(len(allv)); i = 0
    while i < len(allv):                       # peringkat rata-rata untuk nilai kembar
        j = i
        while j + 1 < len(allv) and allv[j + 1] == allv[i]:
            j += 1
        ranks[i:j + 1] = (i + j) / 2.0 + 1.0
        i = j + 1
    rank_pos = np.empty(len(allv)); rank_pos[order] = ranks
    r = rank_pos[:len(pos)].sum()
    return float((r - len(pos) * (len(pos) + 1) / 2.0) / (len(pos) * len(neg)))
