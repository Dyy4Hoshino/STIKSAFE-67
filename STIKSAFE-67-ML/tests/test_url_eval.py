import importlib.util
import os
import unittest

from stiksafe_ml.config import Settings
from stiksafe_ml.labels import Thresholds
from stiksafe_ml.url_service import UrlService
from stiksafe_ml.urlfeatures import FEATURE_NAMES
from tests.helpers import tmpdir

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
spec = importlib.util.spec_from_file_location("eval_url_set", os.path.join(ROOT, "scripts", "eval_url_set.py"))
ev = importlib.util.module_from_spec(spec); spec.loader.exec_module(ev)


class LenEngine:
    """Skor tiruan: URL panjang -> tinggi."""
    model_version = "fake"; status = "staging"; FEATURE_DIM = len(FEATURE_NAMES)
    preprocessing_version = "urlfeat-test"
    thresholds = Thresholds(0.5, 0.9, 0.5)
    def predict(self, v): return 0.95 if v[0] > 40 else 0.1


class TestEvalScript(unittest.TestCase):
    def svc(self, engine=None):
        return UrlService(Settings(service_tokens=(ev.TOKEN,), stub_mode=True, url_allow_safe=True,
                                   url_model_solo_flag=True), engine)

    def test_read_urls_variants(self):
        with tmpdir() as d:
            p = os.path.join(d, "u.txt")
            with open(p, "w", encoding="utf-8") as f:
                f.write("# komentar\n\nhttps://a.com/x\n1,2024,http://b.com/y,online\n")
            self.assertEqual(ev.read_urls(p), ["https://a.com/x", "http://b.com/y"])
            q = os.path.join(d, "t.csv")
            with open(q, "w") as f:
                f.write("1,google.com\n2,Example.org\n")
            self.assertEqual(ev.read_urls(q, domains=True), ["https://google.com/", "https://example.org/"])
            self.assertEqual(len(ev.read_urls(q, domains=True, limit=1)), 1)

    def test_evaluate_counts(self):
        urls = ["https://a.com/", "https://" + "x" * 60 + ".com/p", "http://10.0.0.1/login", "ftp://bukan.valid/x"]
        r = ev.evaluate(urls, self.svc(LenEngine()))
        self.assertEqual((r["n"], r["invalid"]), (3, 1))
        self.assertEqual(r["labels"]["safe"], 1)
        self.assertEqual(len(r["flagged"]), 2)
        self.assertAlmostEqual(r["flagged_pct"], 100 * 2 / 3)
        self.assertIn("URL_IP_HOST", r["reasons"])

    def test_without_model(self):
        r = ev.evaluate(["https://a.com/", "http://10.0.0.1/"], self.svc())
        self.assertEqual((r["labels"]["unknown"], r["labels"]["suspicious"]), (1, 1))


if __name__ == "__main__":
    unittest.main()
