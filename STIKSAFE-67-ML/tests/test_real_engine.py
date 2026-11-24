import json
import os
import unittest

try:
    import numpy as np
    HAVE_NP = True
except ImportError:
    HAVE_NP = False

from stiksafe_ml.childproc import run_in_child
from stiksafe_ml.registry import load_model_card, sha256_file
from tests import childfuncs
from tests.helpers import TOKEN, make_pe, meta, settings, stream, tmpdir

TH = {"t_suspicious": 0.5, "t_malicious": 0.8, "c_min": 0.5}


def write_card(d, **over):
    model = os.path.join(d, "m.model")
    with open(model, "wb") as f:
        f.write(b"fake-model-bytes")
    card = {"model_version": "ember-test-1", "model_file": "m.model", "model_sha256": sha256_file(model),
            "dataset_version": "EMBER2024", "preprocessing_version": "thrember-v3", "feature_dim": childfuncs.DIM,
            "thresholds": TH, "status": "staging", "provisional": True, "metrics": {}}
    card.update(over)
    with open(os.path.join(d, "model_card.json"), "w") as f:
        json.dump(card, f)
    return model


class TestRegistry(unittest.TestCase):
    def test_load_ok(self):
        with tmpdir() as d:
            write_card(d)
            card, path = load_model_card(d)
            self.assertEqual(card.thresholds.t_malicious, 0.8)
            self.assertTrue(path.endswith("m.model"))

    def test_tampered_model_rejected(self):
        with tmpdir() as d:
            m = write_card(d)
            with open(m, "ab") as f:
                f.write(b"x")
            with self.assertRaises(ValueError):
                load_model_card(d)

    def test_bad_cards_rejected(self):
        for over in ({"status": "bebas"}, {"thresholds": {"t_suspicious": 0.9, "t_malicious": 0.1, "c_min": 0.5}},
                     {"model_file": "tidak_ada.model"}):
            with tmpdir() as d:
                write_card(d, **over)
                with self.assertRaises((ValueError, FileNotFoundError)):
                    load_model_card(d)
        with tmpdir() as d:
            write_card(d); os.remove(os.path.join(d, "model_card.json"))
            with self.assertRaises(FileNotFoundError):
                load_model_card(d)


class TestChildProcess(unittest.TestCase):
    def test_ok(self):
        self.assertEqual(run_in_child(childfuncs.fake_vec, ("x",), 20), [0.1] * childfuncs.DIM)

    def test_timeout_kills_child(self):
        with self.assertRaises(RuntimeError) as cm:
            run_in_child(childfuncs.sleepy, ("x",), 1.0)
        self.assertEqual(str(cm.exception), "timeout")

    def test_crash_and_exception_contained(self):
        with self.assertRaises(RuntimeError) as cm:
            run_in_child(childfuncs.crash, ("x",), 20)
        self.assertEqual(str(cm.exception), "child_failed")
        with self.assertRaises(RuntimeError) as cm:
            run_in_child(childfuncs.raises, ("x",), 20)
        self.assertEqual(str(cm.exception), "child_failed:ValueError")      # jenis error ada (untuk log), PESAN tidak
        self.assertNotIn("rahasia-internal", str(cm.exception))


class FakeBooster:
    def __init__(self, score): self.score = score
    def predict(self, arr): return [self.score]


@unittest.skipUnless(HAVE_NP, "numpy belum terpasang")
class TestEmberEngineInService(unittest.TestCase):
    def setUp(self):
        self._d = tmpdir(); self.tmp = self._d.name
        self.mdir = os.path.join(self.tmp, "model"); os.makedirs(self.mdir)
        write_card(self.mdir)
        self.card, _ = load_model_card(self.mdir)

    def tearDown(self):
        self._d.cleanup()

    def service(self, score=0.1, fn=childfuncs.fake_vec, timeout=20):
        from stiksafe_ml.ember_engine import EmberEngine
        from stiksafe_ml.service import ScanService
        eng = EmberEngine(self.card, FakeBooster(score), fn, timeout)
        return ScanService(settings(os.path.join(self.tmp, "t"), stub_mode=False), eng)

    def scan(self, svc, data):
        return svc.scan(TOKEN, meta(data), stream(data))

    def test_labels_follow_card_thresholds(self):
        data = make_pe()
        for score, want in ((0.1, "safe"), (0.6, "suspicious"), (0.95, "malicious")):
            st, body, _ = self.scan(self.service(score), data)
            self.assertEqual((st, body["result"]["label"]), (200, want), score)
            self.assertEqual(body["model_version"], "ember-test-1")

    def test_extraction_failures_are_errors_never_safe(self):
        data = make_pe()
        for fn, tmo in ((childfuncs.sleepy, 1.0), (childfuncs.crash, 20), (childfuncs.raises, 20)):
            st, body, _ = self.scan(self.service(0.0, fn, tmo), data)
            self.assertEqual(st, 500)
            self.assertEqual(body["error"]["code"], "ML_PREPROCESSING_FAILED")
            self.assertNotIn("result", body)
            self.assertEqual(os.listdir(os.path.join(self.tmp, "t")), [])      # file sementara terhapus

    def test_wrong_dimension_rejected(self):
        from stiksafe_ml.ember_engine import EmberEngine
        from stiksafe_ml.service import ScanService
        card = self.card.__class__(**{**self.card.__dict__, "feature_dim": 99})
        svc = ScanService(settings(os.path.join(self.tmp, "t")), EmberEngine(card, FakeBooster(0.0), childfuncs.fake_vec, 20))
        st, body, _ = self.scan(svc, make_pe())
        self.assertEqual(body["error"]["code"], "ML_PREPROCESSING_FAILED")

    def test_partial_pe_is_unknown(self):
        data = b"MZ" + b"\x01" * 60                                   # MZ tanpa struktur PE
        st, body, _ = self.scan(self.service(0.05), data)
        self.assertEqual((st, body["result"]["label"]), (200, "unknown"))
        self.assertIn("PARSE_PARTIAL", body["result"]["reason_codes"])

    def test_model_info_not_stub(self):
        st, info, _ = self.service().model_info(TOKEN)
        self.assertEqual((info["status"], info["dataset_version"]), ("staging", "EMBER2024"))
        h = self.service().health(TOKEN)[1]
        self.assertFalse(h["stub"])

    def test_pdf_named_exe_double_extension_still_applies(self):
        st, body, _ = self.service(0.05).scan(TOKEN, meta(make_pe(), extension_chain=["pdf", "exe"]), stream(make_pe()))
        self.assertEqual(body["result"]["label"], "suspicious")


@unittest.skipUnless(HAVE_NP, "numpy belum terpasang")
class TestThresholds(unittest.TestCase):
    def test_fpr_budget_respected(self):
        from stiksafe_ml.thresholds import select_thresholds, summarize
        rng = np.random.default_rng(67)
        ben = rng.beta(1, 12, 20000); mal = rng.beta(6, 2, 4000)
        y = np.r_[np.zeros(len(ben)), np.ones(len(mal))]; s = np.r_[ben, mal]
        th = select_thresholds(y, s, 0.001, 0.01)
        self.assertLessEqual(th["t_suspicious"], th["t_malicious"])
        m = summarize(y, s, th)
        self.assertLessEqual(m["fpr_at_t_malicious"], 0.001 + 1e-9)
        self.assertLessEqual(m["fpr_at_t_suspicious"], 0.01 + 1e-9)
        self.assertGreater(m["recall_at_t_suspicious"], m["recall_at_t_malicious"])
        self.assertGreater(m["roc_auc"], 0.95)

    def test_auc_known_values(self):
        from stiksafe_ml.thresholds import roc_auc
        self.assertEqual(roc_auc([0, 0, 1, 1], [0.1, 0.2, 0.8, 0.9]), 1.0)
        self.assertEqual(roc_auc([0, 0, 1, 1], [0.9, 0.8, 0.2, 0.1]), 0.0)
        self.assertAlmostEqual(roc_auc([0, 1, 0, 1], [0.5, 0.5, 0.5, 0.5]), 0.5)

    def test_thresholds_valid_for_label_mapper(self):
        from stiksafe_ml.labels import Thresholds
        from stiksafe_ml.thresholds import select_thresholds
        rng = np.random.default_rng(1)
        y = rng.integers(0, 2, 5000); s = np.where(y == 1, rng.beta(5, 2, 5000), rng.beta(1, 10, 5000))
        Thresholds(**select_thresholds(y, s))                      # tidak boleh melempar error


class TestSignifyGuard(unittest.TestCase):
    def test_version_rules(self):
        from stiksafe_ml.ember_engine import signify_compatible as ok
        self.assertTrue(all(ok(v) for v in ("0.7.1", "0.7.9", "0.6.1", "0.5.2")))
        self.assertFalse(any(ok(v) for v in ("0.8.0", "0.8.1", "0.9.2", "1.0.0", "abc", "")))


if __name__ == "__main__":
    unittest.main()
