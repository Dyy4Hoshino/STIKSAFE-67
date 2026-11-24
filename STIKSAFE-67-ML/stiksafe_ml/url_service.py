"""UrlService: alur pemeriksaan URL (FITUR 2). Tanpa file, tanpa jaringan keluar.

Lapis: (1) daftar hitam -> malicious  (2) aturan heuristik -> poin  (3) model ML -> +1 poin (penguat; +2 hanya bila ML_URL_MODEL_SOLO_FLAG=true).
       Total poin >= 2 -> suspicious. Secara default model TIDAK bisa menandai sendirian.
Tanpa model dan tanpa bukti lain, hasilnya 'unknown', BUKAN 'safe'.
"""
import hashlib
import hmac
import json
import logging
import math
import time
from datetime import datetime, timezone
from typing import Dict, Optional, Tuple

from .blocklist import Blocklist
from .config import Settings
from .contract import UUID_RE
from .errors import MLError
from .storage import IdempotencyCache
from .urlfeatures import FEATURE_NAMES, PREPROCESSING_VERSION, extract
from .urlnorm import parse_url
from .urlrules import SUSPICIOUS_POINTS, evaluate

log = logging.getLogger("stiksafe_ml")
Response = Tuple[int, dict, Dict[str, str]]


def _utc_now() -> str:
    return datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")


class UrlService:
    def __init__(self, settings: Settings, engine=None, blocklist: Optional[Blocklist] = None,
                 clock=time.monotonic):
        self.s, self.engine, self.blocklist, self.clock = settings, engine, blocklist or Blocklist(), clock
        self.cache = IdempotencyCache(settings.idempotency_ttl_sec)

    def _authenticate(self, token: Optional[str]) -> None:
        if token and any(hmac.compare_digest(token, t) for t in self.s.service_tokens):
            return
        raise MLError("ML_AUTH_FAILED")

    def check(self, token: Optional[str], body, x_request_id: Optional[str] = None) -> Response:
        started = self.clock()
        request_id: Optional[str] = None
        trace_id = x_request_id or "-"
        began = False
        try:
            self._authenticate(token)
            if not isinstance(body, dict):
                raise MLError("ML_INVALID_REQUEST")
            rid = body.get("request_id")
            if not isinstance(rid, str) or not UUID_RE.match(rid):
                raise MLError("ML_INVALID_REQUEST")
            request_id = rid.lower()
            trace_id = x_request_id or request_id
            cached = self.cache.get(request_id)
            if cached is not None:
                return 200, cached, {"X-Request-ID": trace_id}
            if not self.cache.begin(request_id):
                raise MLError("ML_OVERLOADED", retry_after=self.s.retry_after_sec)
            began = True

            parsed = parse_url(body.get("url"))
            points, rule_flags = evaluate(parsed)
            reasons = set(rule_flags)

            ml_points = 0
            if self.blocklist.match(parsed):
                label, conf = "malicious", None
                reasons.add("URL_BLOCKLIST_MATCH")
            else:
                score = None
                if self.engine is not None:
                    feats = extract(parsed)
                    try:
                        score = self.engine.predict(feats)
                    except Exception:
                        raise MLError("ML_INFERENCE_FAILED")
                    if (isinstance(score, bool) or not isinstance(score, (int, float))
                            or not math.isfinite(score) or not (0.0 <= score <= 1.0)):
                        raise MLError("ML_INVALID_MODEL_OUTPUT")
                    th = self.engine.thresholds
                    if score >= th.t_malicious:           # model teks-URL: penguat, bukan penentu tunggal
                        ml_points = 2 if self.s.url_model_solo_flag else 1     # default: butuh aturan lain untuk menandai
                        reasons.add("URL_MODEL_HIGH")
                    elif score >= th.t_suspicious:
                        ml_points = 1; reasons.add("URL_MODEL_ELEVATED")
                total = points + ml_points
                if total >= SUSPICIOUS_POINTS:
                    label, conf = "suspicious", (float(score) if ml_points else None)
                elif self.engine is None:
                    label, conf = "unknown", None
                    reasons.add("URL_NO_MODEL")
                elif total == 0 and self.s.url_allow_safe:
                    label, conf = "safe", 1.0 - float(score)
                else:
                    label, conf = "unknown", None
                    if total == 0:
                        reasons.add("URL_NO_INDICATION")

            norm_hash = hashlib.sha256(parsed.normalized.encode("utf-8")).hexdigest()
            out = {
                "request_id": request_id, "status": "completed",
                "result": {"label": label, "confidence": conf, "reason_codes": sorted(reasons),
                           "rule_points": points, "model_points": ml_points},
                "target": {"scheme": parsed.scheme, "host": parsed.host, "host_is_ip": parsed.is_ip,
                           "normalized_sha256": norm_hash},
                "model_version": self.engine.model_version if self.engine else None,
                "preprocessing_version": self.engine.preprocessing_version if self.engine else PREPROCESSING_VERSION,
                "scanned_at": _utc_now(),
                "duration_ms": int((self.clock() - started) * 1000),
            }
            self.cache.put(request_id, out)
            self._log(trace_id, "completed", None, label, len(parsed.host), norm_hash, started)
            return 200, out, {"X-Request-ID": trace_id}
        except MLError as e:
            return self._error(e, trace_id, request_id, started)
        except Exception:
            log.exception("unexpected-url")
            return self._error(MLError("ML_INTERNAL_ERROR"), trace_id, request_id, started)
        finally:
            if began and request_id:
                self.cache.end(request_id)

    def _error(self, e: MLError, trace_id, request_id, started) -> Response:
        body = {"request_id": request_id, "status": "failed",
                "error": {"code": e.code, "message": e.spec.message, "retryable": e.spec.retryable},
                "model_version": self.engine.model_version if self.engine else None}
        headers = {"X-Request-ID": trace_id}
        if e.retry_after is not None or e.spec.http in (429, 503):
            headers["Retry-After"] = str(e.retry_after or self.s.retry_after_sec)
        self._log(trace_id, "failed", e.code, None, None, None, started)
        return e.spec.http, body, headers

    def _log(self, trace_id, status, code, label, host_len, norm_hash, started):
        # URL penuh BERISIKO memuat token/data pribadi -> TIDAK dicatat. Hanya hash dan panjang host.
        log.info(json.dumps({"request_id": trace_id, "kind": "url", "status": status, "error_code": code,
                             "label": label, "host_len": host_len, "url_sha256": norm_hash,
                             "total_ms": int((self.clock() - started) * 1000)}))

    def info(self, token: Optional[str]) -> Response:
        try:
            self._authenticate(token)
        except MLError as e:
            return e.spec.http, {"status": "failed", "error": {"code": e.code}}, {}
        en = self.engine
        return 200, {
            "model_loaded": en is not None, "model_version": en.model_version if en else None,
            "status": getattr(en, "status", None) if en else None,
            "blocklist_size": len(self.blocklist), "feature_count": len(FEATURE_NAMES),
            "layers": ["blocklist", "rules", "model" if en else "model (belum dimuat)"],
        }, {}
