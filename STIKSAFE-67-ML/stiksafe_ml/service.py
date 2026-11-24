"""ScanService: orkestrasi alur PRD 8.1. Tidak bergantung pada framework web (mudah diuji).

Jaminan: setiap kegagalan menjadi error terstruktur; TIDAK ADA jalur yang berakhir 'safe' saat gagal.
"""
import hmac
import json
import math
import logging
import threading
import time
import uuid
from datetime import datetime, timezone
from typing import BinaryIO, Dict, Optional, Tuple

from .analyzers import RULESET_VERSION, analyze_file, has_analyzer
from .childproc import run_in_child
from .config import Settings
from .contract import Metadata, parse_metadata, peek_ids
from .engine import Engine, validate_features
from .errors import MLError
from .formats import extension_mismatch, identify
from .labels import REASON_CODES, map_label
from .namecheck import analyze_extensions, apply_name_policy, disguised_executable
from .storage import IdempotencyCache, TempStore

log = logging.getLogger("stiksafe_ml")

Response = Tuple[int, dict, Dict[str, str]]  # (http_status, body, headers)


def _safe_reason(e: Exception) -> str:
    """Alasan kegagalan yang AMAN untuk log: hanya string terkendali dari proses anak atau NAMA jenis error. Pesan bebas
    (bisa memuat path/isi file) tidak pernah dicatat."""
    msg = str(e)
    if isinstance(e, RuntimeError) and (msg == "timeout" or msg.startswith("child_failed")):
        return msg
    return type(e).__name__


def _utc_now() -> str:
    return datetime.now(timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ")


class ScanService:
    def __init__(self, settings: Settings, engine: Optional[Engine], clock=time.monotonic):
        self.s, self.engine, self.clock = settings, engine, clock
        self.tmp = TempStore(settings.tmp_dir, settings.tmp_sweep_age_sec)
        self.cache = IdempotencyCache(settings.idempotency_ttl_sec)
        self._slots = threading.BoundedSemaphore(settings.max_concurrent)
        self._active = 0
        self._lock = threading.Lock()
        self.loaded_at = _utc_now() if engine else None

    # ---------- autentikasi ----------
    def _authenticate(self, token: Optional[str]) -> None:
        if token and any(hmac.compare_digest(token, t) for t in self.s.service_tokens):
            return
        raise MLError("ML_AUTH_FAILED")

    # ---------- endpoint: scan ----------
    def scan(self, token: Optional[str], metadata_raw, file_stream: Optional[BinaryIO],
             x_request_id: Optional[str] = None) -> Response:
        started = self.clock()
        scan_id, request_id = peek_ids(metadata_raw)
        trace_id = x_request_id or request_id or str(uuid.uuid4())
        path = None
        slot_taken = False
        began = False
        model_version = self.engine.model_version if self.engine else None
        stage = "received"
        try:
            self._authenticate(token)
            if self.engine is None:
                raise MLError("ML_MODEL_UNAVAILABLE", retry_after=self.s.retry_after_sec)
            if file_stream is None:
                raise MLError("ML_INVALID_REQUEST")
            md = parse_metadata(metadata_raw, self.s.callback_allowed_hosts)
            scan_id, request_id = md.scan_id, md.request_id

            cached = self.cache.get(md.request_id)          # idempotensi (BR-ML-09)
            if cached is not None:
                return 200, cached, {"X-Request-ID": trace_id}
            if not self.cache.begin(md.request_id):         # duplikat yang sedang berjalan
                raise MLError("ML_OVERLOADED", retry_after=self.s.retry_after_sec)
            began = True

            if md.size_bytes > self.s.max_file_size_bytes:
                raise MLError("ML_FILE_TOO_LARGE")
            if not self._slots.acquire(blocking=False):     # backpressure (FR-RCV-09)
                raise MLError("ML_OVERLOADED", retry_after=self.s.retry_after_sec)
            slot_taken = True
            with self._lock:
                self._active += 1

            deadline = started + self.s.total_deadline_sec

            # --- validating ---
            stage = "validating"
            path, size, sha = self.tmp.receive(file_stream, self.s.max_file_size_bytes)
            if size == 0:
                raise MLError("ML_FILE_CORRUPT")
            if size != md.size_bytes:
                raise MLError("ML_INVALID_REQUEST")
            if sha != md.sha256:
                raise MLError("ML_HASH_MISMATCH")
            fmt = identify(path)
            if fmt.family is None or fmt.family not in self.engine.supported_families:
                return self._rules_scan(md, sha, size, fmt, path, started, trace_id)   # jalur ATURAN (bukan PE)
            t_validate = self.clock()

            # --- extracting ---
            stage = "extracting"
            try:
                feats = self.engine.extract(path, fmt, sha)
            except MLError:
                raise
            except Exception as e:
                log.warning("extract_failed request=%s reason=%s", trace_id, _safe_reason(e))       # penyebab hanya di log server
                raise MLError("ML_PREPROCESSING_FAILED")
            dim = getattr(self.engine, "FEATURE_DIM", len(feats.vector))
            if not validate_features(feats, dim):
                bad = sum(1 for v in feats.vector if not isinstance(v, (int, float)) or not math.isfinite(v))
                log.warning("extract_invalid_vector request=%s dim=%d expected=%d nonfinite=%d",
                            trace_id, len(feats.vector), dim, bad)
                raise MLError("ML_PREPROCESSING_FAILED")
            if self.clock() > deadline or self.clock() - t_validate > self.s.extract_timeout_sec:
                raise MLError("ML_PREPROCESSING_FAILED")
            flags = set(feats.flags)
            if extension_mismatch(fmt, md.declared_extension):
                flags.add("FORMAT_MISMATCH")
            chain_flags, _ = analyze_extensions(md.extension_chain)     # fitur 3
            flags |= chain_flags
            if disguised_executable(fmt.family, md.declared_extension):
                flags.add("DISGUISED_EXECUTABLE")

            # --- inferring ---
            stage = "inferring"
            t_infer = self.clock()
            try:
                score = self.engine.predict(feats.vector)
            except Exception:
                raise MLError("ML_INFERENCE_FAILED")
            if self.clock() > deadline or self.clock() - t_infer > self.s.infer_timeout_sec:
                raise MLError("ML_INFERENCE_TIMEOUT")
            try:
                label, conf, reasons = map_label(score, self.engine.thresholds,
                                                 coverage=feats.coverage, flags=flags)
            except ValueError:
                raise MLError("ML_INVALID_MODEL_OUTPUT")
            label, conf = apply_name_policy(label, conf, flags)         # fitur 3: hanya menaikkan kehati-hatian

            body = {
                "request_id": md.request_id, "scan_id": md.scan_id, "status": "completed",
                "sha256": sha,
                "result": {"label": label, "confidence": conf, "threat_name": None,
                           "threat_category": None, "reason_codes": reasons},
                "model_version": self.engine.model_version,
                "preprocessing_version": self.engine.preprocessing_version,
                "analysis": {"method": "model", "family": fmt.detected},
                "scanned_at": _utc_now(),
                "duration_ms": int((self.clock() - started) * 1000),
            }
            self.cache.put(md.request_id, body)
            self._log(trace_id, md.scan_id, "completed", None, size, fmt.detected, started)
            return 200, body, {"X-Request-ID": trace_id}

        except MLError as e:
            return self._error(e, trace_id, scan_id, request_id, model_version, stage, started)
        except Exception:  # tak terduga: detail hanya di log terlindungi (FR-ERR-03)
            log.exception("unexpected", extra={"request_id": trace_id})
            return self._error(MLError("ML_INTERNAL_ERROR"), trace_id, scan_id, request_id,
                               model_version, stage, started)
        finally:
            self.tmp.remove(path)                           # selalu hapus (FR-RCV-06)
            if began and request_id:
                self.cache.end(request_id)
            if slot_taken:
                with self._lock:
                    self._active -= 1
                self._slots.release()

    def _rules_scan(self, md, sha, size, fmt, path, started, trace_id) -> Response:
        """Jalur untuk file selain PE: pemeriksaan ATURAN. Tidak pernah 'safe'/'malicious'; tanpa temuan -> 'unknown'."""
        chain_flags, _ = analyze_extensions(md.extension_chain)
        flags = set(chain_flags)
        points = 2 if "DOUBLE_EXTENSION" in chain_flags else 0          # nama menyamar: berlaku untuk jenis apa pun
        coverage, analyzed = 1.0, False
        if has_analyzer(fmt.detected):
            try:
                res = run_in_child(analyze_file, (path, fmt.detected, md.declared_extension), self.s.extract_timeout_sec)
                pts, fl, cov = int(res["points"]), set(res["flags"]), float(res["coverage"])
            except Exception as e:
                log.warning("rules_failed request=%s reason=%s", trace_id, _safe_reason(e))
                raise MLError("ML_PREPROCESSING_FAILED")
            points += pts; flags |= fl; coverage = cov; analyzed = True
        elif points == 0:
            raise MLError("ML_UNSUPPORTED_FILE")                        # tak ada pemeriksa dan nama tidak mencurigakan
        if points >= 2:
            label = "suspicious"
        else:
            label = "unknown"
            if analyzed and not flags:
                flags.add("NO_RISK_INDICATORS")
        body = {
            "request_id": md.request_id, "scan_id": md.scan_id, "status": "completed", "sha256": sha,
            "result": {"label": label, "confidence": None, "threat_name": None, "threat_category": None,
                       "reason_codes": sorted(f for f in flags if f in REASON_CODES)},
            "model_version": RULESET_VERSION, "preprocessing_version": RULESET_VERSION,
            "analysis": {"method": "rules", "family": fmt.detected, "rule_points": points, "coverage": coverage},
            "scanned_at": _utc_now(), "duration_ms": int((self.clock() - started) * 1000),
        }
        self.cache.put(md.request_id, body)
        self._log(trace_id, md.scan_id, "completed", None, size, fmt.detected, started)
        return 200, body, {"X-Request-ID": trace_id}

    def _error(self, e: MLError, trace_id, scan_id, request_id, model_version, stage, started) -> Response:
        body = {"request_id": request_id, "scan_id": scan_id, "status": "failed",
                "error": {"code": e.code, "message": e.spec.message, "retryable": e.spec.retryable},
                "model_version": model_version}
        headers = {"X-Request-ID": trace_id}
        if e.retry_after is not None or e.spec.http in (429, 503):
            headers["Retry-After"] = str(e.retry_after or self.s.retry_after_sec)
        self._log(trace_id, scan_id, "failed", e.code, None, None, started)
        return e.spec.http, body, headers

    def _log(self, trace_id, scan_id, status, code, size, family, started):
        # Hanya metadata. Tidak pernah isi file, token, atau path (PRD 12.2).
        log.info(json.dumps({"request_id": trace_id, "scan_id": scan_id, "status": status,
                             "error_code": code, "size_bytes": size, "format_family": family,
                             "total_ms": int((self.clock() - started) * 1000),
                             "model_version": self.engine.model_version if self.engine else None}))

    # ---------- endpoint: health & model-info ----------
    def health(self, token: Optional[str]) -> Response:
        try:
            self._authenticate(token)
        except MLError as e:
            return e.spec.http, {"status": "failed", "error": {"code": e.code}}, {}
        ready = self.engine is not None and self.tmp.available()
        body = {"status": "ready" if ready else "not_ready", "model_loaded": self.engine is not None,
                "model_version": self.engine.model_version if self.engine else None,
                "active_requests": self._active, "max_concurrent": self.s.max_concurrent,
                "stub": bool(self.engine and self.engine.is_stub)}
        return (200 if ready else 503), body, ({} if ready else {"Retry-After": str(self.s.retry_after_sec)})

    def model_info(self, token: Optional[str]) -> Response:
        try:
            self._authenticate(token)
        except MLError as e:
            return e.spec.http, {"status": "failed", "error": {"code": e.code}}, {}
        if self.engine is None:
            e = MLError("ML_MODEL_UNAVAILABLE")
            return e.spec.http, {"status": "failed", "error": {"code": e.code}}, {}
        en = self.engine
        return 200, {
            "model_version": en.model_version, "preprocessing_version": en.preprocessing_version,
            "dataset_version": en.dataset_version,
            "status": "staging" if en.is_stub else getattr(en, "status", "staging"),
            "labels": ["safe", "suspicious", "malicious", "unknown"],
            "supported_types": list(en.supported_types),
            "max_file_size_bytes": self.s.max_file_size_bytes,
            "confidence_type": en.confidence_type, "loaded_at": self.loaded_at,
        }, {}
