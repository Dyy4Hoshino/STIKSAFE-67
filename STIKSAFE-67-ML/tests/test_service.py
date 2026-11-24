import json
import logging
import math
import os
import random
import stat
import unittest

from stiksafe_ml.config import Settings
from stiksafe_ml.engine import FeatureResult, StubEngine
from stiksafe_ml.labels import LABELS
from stiksafe_ml.storage import TempStore
from tests.helpers import (REQ_ID, SCAN_ID, TOKEN, files_left, make_pe, meta, new_service,
                           stream, tmpdir)


class Base(unittest.TestCase):
    def setUp(self):
        self._d = tmpdir(); self.tmp = self._d.name
        self.svc = new_service(self.tmp)

    def tearDown(self):
        self._d.cleanup()

    def scan(self, data, md=None, token=TOKEN, svc=None):
        return (svc or self.svc).scan(token, md if md is not None else meta(data), stream(data))

    def assertFailed(self, r, code, retryable):
        status, body, _ = r
        self.assertEqual(body["status"], "failed")
        self.assertNotIn("result", body)                       # tidak ada label saat gagal
        self.assertEqual(body["error"]["code"], code)
        self.assertEqual(body["error"]["retryable"], retryable)
        self.assertEqual(files_left(self.tmp), 0)              # file sementara dihapus

    def assertFilesCleaned(self):
        self.assertEqual(files_left(self.tmp), 0)


class TestSuccess(Base):
    def test_success_shape_and_echo(self):
        data = make_pe("malicious")
        status, body, headers = self.scan(data)
        self.assertEqual(status, 200)
        self.assertEqual(body["status"], "completed")
        self.assertEqual(body["scan_id"], SCAN_ID)
        self.assertEqual(body["request_id"], REQ_ID)
        import hashlib
        self.assertEqual(body["sha256"], hashlib.sha256(data).hexdigest())
        self.assertEqual(body["result"]["label"], "malicious")
        self.assertIsNone(body["result"]["threat_name"])        # model tidak mendukung -> null
        self.assertTrue(body["model_version"].endswith("-stub"))
        self.assertIn("X-Request-ID", headers)
        self.assertTrue(body["scanned_at"].endswith("Z"))
        self.assertFilesCleaned()

    def test_all_three_labels_reachable(self):
        for i, target in enumerate(("malicious", "suspicious", "safe")):
            data = make_pe(target)
            md = meta(data, request_id=f"7f0c1d5e-4b7a-4a39-9c62-0e5a3d1b2f1{i}")
            self.assertEqual(self.scan(data, md)[1]["result"]["label"], target)

    def test_partial_pe_becomes_unknown_not_safe(self):
        # header MZ tanpa struktur PE -> coverage rendah -> unknown (hasil sah, bukan error)
        data = None
        for i in range(10000):
            cand = b"MZ" + i.to_bytes(4, "little") + b"\x00" * 20
            if int(__import__("hashlib").sha256(cand).hexdigest()[:8], 16) / 0xFFFFFFFF < 0.5:
                data = cand; break
        status, body, _ = self.scan(data)
        self.assertEqual(status, 200)
        self.assertEqual(body["result"]["label"], "unknown")
        self.assertIsNone(body["result"]["confidence"])
        self.assertIn("PARSE_PARTIAL", body["result"]["reason_codes"])

    def test_fake_extension_flagged_not_rejected(self):
        data = make_pe("safe")
        _, body, _ = self.scan(data, meta(data, declared_extension="pdf"))
        self.assertIn("FORMAT_MISMATCH", body["result"]["reason_codes"])


class TestRejections(Base):
    def test_bad_token_no_processing(self):
        data = make_pe()
        for tok in (None, "", "salah"):
            r = self.scan(data, token=tok)
            self.assertEqual(r[0], 401)
            self.assertEqual(r[1]["error"]["code"], "ML_AUTH_FAILED")
        self.assertFilesCleaned()

    def test_second_active_token_works(self):
        svc = new_service(self.tmp, service_tokens=("t-lama", "t-baru"))
        data = make_pe()
        self.assertEqual(svc.scan("t-baru", meta(data), stream(data))[0], 200)

    def test_invalid_metadata(self):
        data = make_pe()
        r = self.scan(data, md="{rusak")
        self.assertEqual(r[0], 422); self.assertFailed(r, "ML_INVALID_REQUEST", False)

    def test_size_mismatch(self):
        data = make_pe()
        self.assertFailed(self.scan(data, meta(data, size_bytes=len(data) + 1)),
                          "ML_INVALID_REQUEST", False)

    def test_hash_mismatch_is_retryable(self):
        data = make_pe()
        r = self.scan(data, meta(data, sha256="0" * 64))
        self.assertEqual(r[0], 422); self.assertFailed(r, "ML_HASH_MISMATCH", True)

    def test_empty_file(self):
        self.assertFailed(self.scan(b"", meta(b"x")), "ML_FILE_CORRUPT", False)

    def test_too_large_declared_and_streamed(self):
        svc = new_service(self.tmp, max_file_size_mb=1)
        big = b"MZ" + b"\x00" * (1024 * 1024 + 10)
        r = self.scan(big, svc=svc)
        self.assertEqual(r[0], 413); self.assertFailed(r, "ML_FILE_TOO_LARGE", False)
        # metadata berbohong (size kecil) tetapi aliran melebihi batas -> berhenti saat streaming
        r2 = self.scan(big, meta(big, size_bytes=100), svc=svc)
        self.assertFailed(r2, "ML_FILE_TOO_LARGE", False)

    def test_unsupported_formats(self):
        # tidak ada pemeriksa ATURAN untuk format ini dan namanya tidak mencurigakan -> 422 (bukan label)
        for data in (b"\x7fELF" + b"\0" * 40, b"Rar!\x1a\x07\x00" + b"\0" * 40, b"\x1f\x8b\x08" + b"\0" * 40,
                     b"7z\xbc\xaf\x27\x1c" + b"\0" * 40, b"RIFF\0\0\0\0WEBPVP8 " + b"\0" * 20, b"\x00\x01\x02\x03" * 50):
            r = self.scan(data, meta(data, declared_extension="bin"))
            self.assertEqual(r[0], 422); self.assertFailed(r, "ML_UNSUPPORTED_FILE", False)

    def test_pdf_named_exe_is_checked_by_rules_and_mismatch_flagged(self):
        data = b"%PDF-1.4 " + b"y" * 40 + b"\n%%EOF\n"
        st, body, _ = self.scan(data, meta(data, declared_extension="exe"))
        self.assertEqual((st, body["result"]["label"]), (200, "unknown"))
        self.assertIn("FORMAT_MISMATCH", body["result"]["reason_codes"])
        self.assertEqual((body["analysis"]["method"], body["analysis"]["family"]), ("rules", "pdf"))

    def test_model_not_loaded(self):
        from stiksafe_ml.service import ScanService
        from tests.helpers import settings
        svc = ScanService(settings(self.tmp), None)
        data = make_pe()
        r = self.scan(data, svc=svc)
        self.assertEqual(r[0], 503); self.assertIn("Retry-After", r[2])
        self.assertEqual(r[1]["error"]["code"], "ML_MODEL_UNAVAILABLE")
        self.assertEqual(svc.health(TOKEN)[0], 503)


class FailingEngine(StubEngine):
    def __init__(self, mode):
        self.mode = mode
    def extract(self, path, fmt, sha256):
        if self.mode == "extract_raises": raise RuntimeError("parser crash /secret/path")
        if self.mode == "bad_vector": return FeatureResult([math.nan, 1.0], 1.0, set())
        return super().extract(path, fmt, sha256)
    def predict(self, f):
        if self.mode == "predict_raises": raise RuntimeError("boom")
        if self.mode == "nan": return math.nan
        if self.mode == "out_of_range": return 1.7
        return super().predict(f)


class TestFailSafely(Base):
    """Aturan mutlak: kegagalan TIDAK PERNAH menjadi 'safe' (BR-ML-02)."""
    CASES = [("extract_raises", "ML_PREPROCESSING_FAILED"), ("bad_vector", "ML_PREPROCESSING_FAILED"),
             ("predict_raises", "ML_INFERENCE_FAILED"), ("nan", "ML_INVALID_MODEL_OUTPUT"),
             ("out_of_range", "ML_INVALID_MODEL_OUTPUT")]

    def test_engine_failures(self):
        for mode, code in self.CASES:
            svc = new_service(self.tmp, engine=FailingEngine(mode))
            data = make_pe("safe")        # file yang normalnya akan 'safe'
            r = self.scan(data, svc=svc)
            self.assertEqual(r[0], 500)
            self.assertFailed(r, code, True)
            self.assertNotIn("/secret/path", json.dumps(r[1]))   # tidak bocor detail internal

    def test_failure_cause_is_logged_safely_but_not_returned(self):
        svc = new_service(self.tmp, engine=FailingEngine("extract_raises"))      # pesan error memuat "/secret/path"
        with self.assertLogs("stiksafe_ml", level="WARNING") as cm:
            r = self.scan(make_pe("safe"), svc=svc)
        text = "\n".join(cm.output)
        self.assertIn("extract_failed", text); self.assertIn("RuntimeError", text)   # jenis error tercatat
        self.assertNotIn("/secret/path", text)                                        # pesan bebas TIDAK tercatat
        self.assertNotIn("RuntimeError", json.dumps(r[1]))                            # dan tak ada di respons

    def test_timeout_by_total_deadline(self):
        t = {"now": 0.0}
        class Slow(StubEngine):
            def predict(s, f):
                t["now"] += 500; return super().predict(f)
        svc = new_service(self.tmp, engine=Slow(), clock=lambda: t["now"])
        r = self.scan(make_pe("safe"), svc=svc)
        self.assertEqual(r[0], 504); self.assertFailed(r, "ML_INFERENCE_TIMEOUT", True)

    def test_failed_request_not_cached_so_retry_can_succeed(self):
        data = make_pe()
        bad = self.scan(data, meta(data, sha256="1" * 64))
        self.assertEqual(bad[1]["status"], "failed")
        self.assertEqual(self.scan(data)[0], 200)                # retry dengan hash benar

    def test_fuzz_never_safe_on_failure(self):
        rnd = random.Random(67)
        for _ in range(300):
            data = bytes(rnd.getrandbits(8) for _ in range(rnd.randint(0, 200)))
            if rnd.random() < 0.5:
                data = b"MZ" + data
            md = meta(data or b"x", size_bytes=rnd.choice([len(data), len(data) + 1]),
                      request_id=f"{rnd.getrandbits(32):08x}-4b7a-4a39-9c62-0e5a3d1b2f10")
            status, body, _ = self.svc.scan(TOKEN, md, stream(data))
            if body["status"] == "failed":
                self.assertNotIn("result", body)
            else:
                self.assertIn(body["result"]["label"], LABELS)
            self.assertFilesCleaned()


class TestIdempotencyAndLoad(Base):
    def test_same_request_id_single_inference(self):
        calls = {"n": 0}
        class Counting(StubEngine):
            def predict(s, f):
                calls["n"] += 1; return super().predict(f)
        svc = new_service(self.tmp, engine=Counting())
        data = make_pe()
        a = self.scan(data, svc=svc); b = self.scan(data, svc=svc)
        self.assertEqual(a[1], b[1]); self.assertEqual(calls["n"], 1)

    def test_overloaded_has_retry_after_and_releases(self):
        svc = new_service(self.tmp, max_concurrent=1)
        self.assertTrue(svc._slots.acquire(blocking=False))       # simulasikan slot penuh
        data = make_pe()
        r = self.scan(data, svc=svc)
        self.assertEqual(r[0], 429); self.assertEqual(r[1]["error"]["code"], "ML_OVERLOADED")
        self.assertIn("Retry-After", r[2])
        svc._slots.release()
        self.assertEqual(self.scan(data, svc=svc)[0], 200)        # pulih setelah slot bebas

    def test_slot_released_after_failures(self):
        svc = new_service(self.tmp, max_concurrent=1)
        for _ in range(3):
            self.scan(b"%PDF-1.4 " + b"x" * 30, svc=svc)
        self.assertEqual(self.scan(make_pe(), svc=svc)[0], 200)


class TestSafetyAndOps(Base):
    @unittest.skipUnless(os.name == "posix", "izin file 0600 hanya berlaku di Linux/Mac")
    def test_temp_file_permissions_and_name(self):
        store = TempStore(self.tmp)
        path, size, _ = store.receive(stream(b"abc"), 100)
        mode = stat.S_IMODE(os.stat(path).st_mode)
        self.assertEqual(mode, 0o600)
        self.assertFalse(mode & stat.S_IXUSR)
        store.remove(path)

    def test_sweeper_removes_old_files(self):
        store = TempStore(self.tmp, sweep_age_sec=300)
        p, _, _ = store.receive(stream(b"abc"), 100)
        self.assertEqual(store.sweep(), 0)
        self.assertEqual(store.sweep(now=os.path.getmtime(p) + 301), 1)

    def test_logs_never_contain_token_or_content(self):
        with self.assertLogs("stiksafe_ml", level="INFO") as cm:
            data = make_pe(); self.scan(data); self.scan(data, token="salah")
            self.scan(b"%PDF-1.4 RAHASIA-ISI-FILE " + b"x" * 20)
        text = "\n".join(cm.output)
        self.assertNotIn(TOKEN, text); self.assertNotIn("RAHASIA-ISI-FILE", text)
        self.assertNotIn(self.tmp, text)

    def test_health_and_model_info(self):
        st, body, _ = self.svc.health(TOKEN)
        self.assertEqual(st, 200); self.assertEqual(body["status"], "ready"); self.assertTrue(body["stub"])
        st, info, _ = self.svc.model_info(TOKEN)
        self.assertEqual(info["supported_types"], ["exe", "dll"])
        self.assertEqual(info["labels"], ["safe", "suspicious", "malicious", "unknown"])
        self.assertEqual(self.svc.health("salah")[0], 401)
        self.assertEqual(self.svc.model_info(None)[0], 401)

    def test_settings_guards(self):
        with self.assertRaises(ValueError):
            Settings(service_tokens=("a",), stub_mode=True, env="production").validate()
        with self.assertRaises(ValueError):
            Settings(service_tokens=("a",), total_deadline_sec=120).validate()
        with self.assertRaises(ValueError):
            Settings(service_tokens=("a", "b", "c")).validate()
        with self.assertRaises(ValueError):
            Settings(service_tokens=()).validate()


if __name__ == "__main__":
    unittest.main()
