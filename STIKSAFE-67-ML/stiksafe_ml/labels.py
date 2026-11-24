"""ResultMapper: skor -> label (PRD 7.5). Aturan konservatif (BR-ML-04).

Ambang TIDAK ditulis di sini; selalu datang dari registry bersama versi model (BR-ML-05).
"""
import math
from dataclasses import dataclass
from typing import List, Optional, Set, Tuple

LABELS = ("safe", "suspicious", "malicious", "unknown")
REASON_CODES = frozenset({
    "PARSE_PARTIAL", "FORMAT_MISMATCH", "ENCRYPTED_CONTENT",
    "LIMIT_EXCEEDED", "LOW_FEATURE_COVERAGE", "OUT_OF_DISTRIBUTION",
    "DOUBLE_EXTENSION", "DISGUISED_EXECUTABLE", "ODD_EXTENSION_CHARS",  # fitur 3 (usulan v2.1)
    "URL_BLOCKLIST_MATCH", "URL_IP_HOST", "URL_USERINFO", "URL_EXEC_DOWNLOAD", "URL_PUNYCODE",  # fitur 2
    "NO_RISK_INDICATORS", "PDF_JAVASCRIPT", "PDF_AUTO_ACTION", "PDF_LAUNCH_ACTION", "PDF_EMBEDDED_FILE", "PDF_RICH_MEDIA",  # jalur aturan
    "PDF_OBFUSCATED_NAMES", "OFFICE_MACRO", "OFFICE_EXTERNAL_TEMPLATE", "OFFICE_EXTERNAL_OBJECT", "OFFICE_EMBEDDED_OBJECT",
    "OFFICE_ACTIVEX", "OFFICE_EQUATION_EDITOR", "RTF_EMBEDDED_OBJECT", "ARCHIVE_CONTAINS_EXECUTABLE", "ARCHIVE_DOUBLE_EXTENSION",
    "ARCHIVE_PATH_TRAVERSAL", "ARCHIVE_HIGH_RATIO", "ARCHIVE_TOO_MANY_ENTRIES", "ARCHIVE_NESTED", "EXECUTABLE_PACKAGE", "LNK_FILE",
    "SCRIPT_RISKY_PATTERN", "SCRIPT_OBFUSCATED", "IMAGE_EMBEDDED_PAYLOAD", "IMAGE_TRAILING_DATA",
    "URL_MANY_SUBDOMAINS", "URL_NONSTANDARD_PORT", "URL_SHORTENER", "URL_SUSPICIOUS_KEYWORDS", "URL_NO_MODEL", "URL_NO_INDICATION", "URL_MODEL_ELEVATED", "URL_MODEL_HIGH",
})


@dataclass(frozen=True)
class Thresholds:
    t_suspicious: float
    t_malicious: float
    c_min: float

    def __post_init__(self):
        ok = all(isinstance(v, (int, float)) and math.isfinite(v) for v in
                 (self.t_suspicious, self.t_malicious, self.c_min))
        if not ok or not (0.0 <= self.t_suspicious <= self.t_malicious <= 1.0) \
                or not (0.0 <= self.c_min <= 1.0):
            raise ValueError("ambang tidak valid")


def map_label(score: float, th: Thresholds, *, coverage: float,
              flags: Set[str]) -> Tuple[str, Optional[float], List[str]]:
    """Kembalikan (label, confidence, reason_codes).

    Aturan 7.5:
      1. skor >= T_malicious                       -> malicious
      2. T_suspicious <= skor < T_malicious        -> suspicious
      3. skor < T_suspicious TETAPI ragu           -> unknown
      4. selain itu                                -> safe
    Ragu = coverage < C_min, OOD, terenkripsi, atau batas struktur terlampaui.
    Kondisi ragu TIDAK menurunkan malicious/suspicious dan tidak pernah menaikkan ke safe.
    """
    if not isinstance(score, (int, float)) or isinstance(score, bool) \
            or not math.isfinite(score) or not (0.0 <= score <= 1.0):
        raise ValueError("skor di luar rentang")  # pemanggil mengubahnya jadi ML_INVALID_MODEL_OUTPUT

    reasons: Set[str] = {f for f in flags if f in REASON_CODES}
    low_cov = coverage < th.c_min
    if low_cov:
        reasons.add("LOW_FEATURE_COVERAGE")

    if score >= th.t_malicious:
        label, conf = "malicious", float(score)
    elif score >= th.t_suspicious:
        label, conf = "suspicious", float(score)
    else:
        doubt = low_cov or bool(flags & {"OUT_OF_DISTRIBUTION", "ENCRYPTED_CONTENT", "LIMIT_EXCEEDED"})
        if doubt:
            label, conf = "unknown", None          # confidence unknown = null (7.5)
        else:
            label, conf = "safe", 1.0 - float(score)  # raw_score; lihat DEC-ML-09
    return label, conf, sorted(reasons)
