import csv
import importlib.util
import json
import math
import os
import unittest

try:
    import numpy as np
    HAVE_NP = True
except ImportError:
    HAVE_NP = False

from stiksafe_ml.blocklist import Blocklist
from stiksafe_ml.config import Settings
from stiksafe_ml.errors import MLError
from stiksafe_ml.labels import Thresholds
from stiksafe_ml.url_service import UrlService
from stiksafe_ml.urlfeatures import FEATURE_NAMES, extract
from stiksafe_ml.urlnorm import MAX_URL_LEN, parse_url
from stiksafe_ml.urlrules import evaluate
from tests.helpers import TOKEN, tmpdir

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
RID = "7f0c1d5e-4b7a-4a39-9c62-0e5a3d1b2f10"


class TestNorm(unittest.TestCase):
    def test_basic(self):
        p = parse_url("HTTPS://WWW.Example.COM:443/Login?a=1#frag")
        self.assertEqual((p.scheme, p.host, p.path, p.query), ("https", "www.example.com", "/Login", "a=1"))
        self.assertEqual(p.normalized, "https://www.example.com:443/Login?a=1")

    def test_no_scheme_gets_http(self):
        self.assertEqual(parse_url("contoh.com/a").scheme, "http")
        self.assertEqual(parse_url("localhost:8080/x").port, 8080)
        self.assertEqual(parse_url("contoh.com:8080").host, "contoh.com")

    def test_ip_forms(self):
        for u in ("http://192.168.1.5/", "http://2130706433/", "http://0x7f.0.0.1/", "http://[::1]/"):
            self.assertTrue(parse_url(u).is_ip, u)
        self.assertFalse(parse_url("http://example.com/").is_ip)

    def test_userinfo_and_punycode(self):
        self.assertTrue(parse_url("http://paypal.com@evil.example/").has_userinfo)
        self.assertEqual(parse_url("http://evil.example/").has_userinfo, False)
        self.assertTrue(parse_url("http://pаypal.com/").has_punycode)       # 'а' Kiril -> xn--

    def test_invalid(self):
        for bad in (None, 123, "", "   ", "javascript:alert(1)", "ftp://a.com/x", "http://", "http://a b.com/",
                    "http://a.com:99999/", "http://a.com/\x00", "x" * (MAX_URL_LEN + 1), "mailto:a@b.c", "data:text/html,hi"):
            with self.assertRaises(MLError, msg=repr(bad)[:40]) as cm:
                parse_url(bad)
            self.assertEqual(cm.exception.code, "ML_INVALID_REQUEST")


class TestFeaturesRules(unittest.TestCase):
    def test_vector_shape_deterministic_finite(self):
        for u in ("https://a.b/", "http://192.168.0.1:8080/x/y?z=1&k=2", "https://sub.a.b.c.d.example.co.id/p//q.exe"):
            p = parse_url(u); v1, v2 = extract(p), extract(p)
            self.assertEqual(v1, v2); self.assertEqual(len(v1), len(FEATURE_NAMES))
            self.assertTrue(all(math.isfinite(x) for x in v1))

    def test_known_values(self):
        f = dict(zip(FEATURE_NAMES, extract(parse_url("https://www.example.com/a/b?x=1&y=2"))))
        self.assertEqual((f["is_https"], f["has_www"], f["n_amp"], f["n_params"], f["n_slash_path"]), (1, 1, 1, 2, 2))
        self.assertEqual(f["is_ip"], 0)

    def test_rules(self):
        self.assertEqual(evaluate(parse_url("https://www.google.com/"))[0], 0)
        self.assertEqual(evaluate(parse_url("http://10.0.0.1/"))[1], {"URL_IP_HOST"})
        self.assertIn("URL_EXEC_DOWNLOAD", evaluate(parse_url("https://a.com/setup.exe"))[1])
        self.assertNotIn("URL_EXEC_DOWNLOAD", evaluate(parse_url("https://a.com/app.js"))[1])     # .js biasa di web
        self.assertIn("URL_SHORTENER", evaluate(parse_url("https://bit.ly/abc"))[1])
        self.assertIn("URL_NONSTANDARD_PORT", evaluate(parse_url("https://a.com:8443/"))[1])
        self.assertNotIn("URL_NONSTANDARD_PORT", evaluate(parse_url("https://a.com:443/"))[1])
        pts, fl = evaluate(parse_url("http://secure-login.verify-account.example.com/signin"))
        self.assertIn("URL_SUSPICIOUS_KEYWORDS", fl)


class TestFeatureBias(unittest.TestCase):
    """Penjaga terhadap jalan pintas dataset: fitur domain tidak boleh bocor info https/www/path."""
    def test_www_is_ignored_for_host_features(self):
        a = dict(zip(FEATURE_NAMES, extract(parse_url("https://www.example.com/"))))
        b = dict(zip(FEATURE_NAMES, extract(parse_url("https://example.com/"))))
        for n in ("host_len", "host_dots", "n_subdomains", "host_entropy", "longest_label_len", "tld_len", "host_hyphens"):
            self.assertEqual(a[n], b[n], n)
        self.assertEqual((a["has_www"], b["has_www"]), (1, 0))                   # ada, tapi hanya untuk model A

    def test_host_only_vector_blind_to_scheme_path_www(self):
        spec = importlib.util.spec_from_file_location("train_url", os.path.join(ROOT, "scripts", "train_url.py"))
        t = importlib.util.module_from_spec(spec); spec.loader.exec_module(t)
        if not HAVE_NP:
            self.skipTest("numpy belum terpasang")
        v = lambda u: t.mask_columns(np.asarray([extract(parse_url(u))], dtype=np.float32), t.HOST_ONLY_KEEP)
        self.assertTrue((v("https://www.a-b.example.com/x/y/z?q=1&r=2") == v("http://a-b.example.com")).all())
        self.assertFalse((v("http://a-b.example.com") == v("http://a-c-d1.example.com")).all())   # domain beda -> beda
        self.assertTrue(t.HOST_ONLY_KEEP <= set(FEATURE_NAMES))
        for artefak in ("is_https", "has_www", "path_len", "n_slash_path", "has_path", "url_len", "query_len"):
            self.assertNotIn(artefak, t.HOST_ONLY_KEEP)


class TestUrlEngine(unittest.TestCase):
    def card(self, **over):
        from stiksafe_ml.registry import ModelCard
        from stiksafe_ml.urlfeatures import PREPROCESSING_VERSION
        kw = dict(model_version="u1", model_file="m", model_sha256="0" * 64, dataset_version="d",
                  preprocessing_version=PREPROCESSING_VERSION, feature_dim=len(FEATURE_NAMES),
                  thresholds=Thresholds(0.5, 0.9, 0.5), status="staging", provisional=True, metrics={})
        kw.update(over)
        return ModelCard(**kw)

    def test_old_preprocessing_version_rejected(self):
        from stiksafe_ml.url_engine import UrlModelEngine
        with self.assertRaises(ValueError):
            UrlModelEngine(self.card(preprocessing_version="urlfeat-v1"), object())
        with self.assertRaises(ValueError):
            UrlModelEngine(self.card(feature_dim=5), object())

    @unittest.skipUnless(HAVE_NP, "numpy belum terpasang")
    def test_unkept_features_are_zeroed_at_serving(self):
        from stiksafe_ml.url_engine import UrlModelEngine
        seen = {}
        class B:
            def predict(self, arr): seen["row"] = arr[0].tolist(); return [0.3]
        eng = UrlModelEngine(self.card(metrics={"kept_features": ["host_len", "is_ip"]}), B())
        eng.predict([float(i + 1) for i in range(len(FEATURE_NAMES))])
        nonzero = {n for n, v in zip(FEATURE_NAMES, seen["row"]) if v != 0.0}
        self.assertEqual(nonzero, {"host_len", "is_ip"})


class TestBlocklist(unittest.TestCase):
    def test_exact_match_only(self):
        with tmpdir() as d:
            p = os.path.join(d, "bl.txt")
            with open(p, "w") as f:
                f.write("# komentar\nhttp://evil.example/mal.exe\n\nnot a url ::\n")
            bl = Blocklist.from_file(p)
            self.assertEqual(len(bl), 1)
            self.assertTrue(bl.match(parse_url("HTTP://EVIL.example/mal.exe#x")))
            self.assertFalse(bl.match(parse_url("http://evil.example/other")))      # bukan per-host


class FakeEngine:
    model_version = "url-test-1"; status = "staging"; FEATURE_DIM = len(FEATURE_NAMES)
    preprocessing_version = "urlfeat-test-9"
    thresholds = Thresholds(0.5, 0.9, 0.5)
    def __init__(self, score): self.score = score; self.calls = 0
    def predict(self, v):
        self.calls += 1
        if isinstance(self.score, Exception): raise self.score
        return self.score


class TestUrlService(unittest.TestCase):
    def svc(self, engine=None, bl=None, allow_safe=True, solo=False):
        return UrlService(Settings(service_tokens=(TOKEN,), stub_mode=True, url_allow_safe=allow_safe,
                                   url_model_solo_flag=solo), engine, bl)

    def check(self, svc, url, rid=RID, token=TOKEN):
        return svc.check(token, {"request_id": rid, "url": url})

    def test_response_reports_real_preprocessing_version(self):
        from stiksafe_ml.urlfeatures import PREPROCESSING_VERSION
        self.assertEqual(self.check(self.svc(), "https://example.com/")[1]["preprocessing_version"], PREPROCESSING_VERSION)
        b = self.check(self.svc(FakeEngine(0.1)), "https://example.com/", rid="7f0c1d5e-4b7a-4a39-9c62-0e5a3d1b2f18")[1]
        self.assertEqual(b["preprocessing_version"], "urlfeat-test-9")      # mengikuti versi milik MODEL yang dimuat

    def test_no_model_is_unknown_not_safe(self):
        st, b, _ = self.check(self.svc(), "https://www.google.com/")
        self.assertEqual((st, b["result"]["label"]), (200, "unknown"))
        self.assertIn("URL_NO_MODEL", b["result"]["reason_codes"])
        self.assertIsNone(b["model_version"])

    def test_no_model_but_rules_raise(self):
        b = self.check(self.svc(), "http://192.168.1.5/login")[1]
        self.assertEqual(b["result"]["label"], "suspicious")
        self.assertNotIn("URL_NO_MODEL", b["result"]["reason_codes"])
        self.assertIn("URL_IP_HOST", b["result"]["reason_codes"])

    def test_blocklist_is_malicious(self):
        bl = Blocklist(["http://evil.example/mal.exe"])
        for eng in (None, FakeEngine(0.01)):
            b = self.check(self.svc(eng, bl), "http://evil.example/mal.exe")[1]
            self.assertEqual(b["result"]["label"], "malicious")
            self.assertIn("URL_BLOCKLIST_MATCH", b["result"]["reason_codes"])

    def test_model_is_booster_not_sole_judge(self):
        # ambang fake: 0.5 / 0.9
        def run(score, url, i, **kw):
            return self.check(self.svc(FakeEngine(score), **kw), url, rid=f"7f0c1d5e-4b7a-4a39-9c62-0e5a3d1b2f1{i}")[1]
        b = run(0.1, "https://example.com/", 0)
        self.assertEqual((b["result"]["label"], b["result"]["model_points"]), ("safe", 0))
        b = run(0.7, "https://example.com/", 1)                      # menengah: +1 poin, belum cukup sendirian
        self.assertEqual(b["result"]["label"], "unknown")
        self.assertIn("URL_MODEL_ELEVATED", b["result"]["reason_codes"])
        self.assertNotIn("URL_NO_INDICATION", b["result"]["reason_codes"])
        b = run(0.99, "https://example.com/", 2)                     # sangat tinggi: DEFAULT tetap hanya +1 poin
        self.assertEqual(b["result"]["label"], "unknown")
        self.assertIn("URL_MODEL_HIGH", b["result"]["reason_codes"])         # sinyal tetap tercatat
        self.assertEqual(b["result"]["model_points"], 1)
        b = run(0.99, "https://example.com/", 5, solo=True)          # hanya bila diizinkan eksplisit
        self.assertEqual((b["result"]["label"], b["result"]["confidence"], b["result"]["model_points"]), ("suspicious", 0.99, 2))
        b = run(0.99, "https://bit.ly/abc", 6)                       # tinggi + aturan 1 poin = 2 -> suspicious
        self.assertEqual(b["result"]["label"], "suspicious")
        b = run(0.7, "https://bit.ly/abc", 3)                        # menengah + pemendek (1 poin) = 2
        self.assertEqual(b["result"]["label"], "suspicious")
        b = run(0.1, "https://bit.ly/abc", 4)                        # aturan 1 poin saja: tidak boleh 'safe'
        self.assertEqual(b["result"]["label"], "unknown")
        self.assertEqual(b["model_version"], "url-test-1")

    def test_safe_disabled_by_default_becomes_unknown(self):
        self.assertFalse(Settings(service_tokens=(TOKEN,)).url_allow_safe)         # default pengaturan
        self.assertFalse(Settings(service_tokens=(TOKEN,)).url_model_solo_flag)
        b = self.check(self.svc(FakeEngine(0.01), allow_safe=False), "https://example.com/")[1]
        self.assertEqual(b["result"]["label"], "unknown")
        self.assertIsNone(b["result"]["confidence"])
        self.assertIn("URL_NO_INDICATION", b["result"]["reason_codes"])
        # suspicious dari skor sangat tinggi tidak terpengaruh
        self.assertEqual(self.check(self.svc(FakeEngine(0.95), allow_safe=False, solo=True), "https://example.com/",
                                    rid="7f0c1d5e-4b7a-4a39-9c62-0e5a3d1b2f19")[1]["result"]["label"], "suspicious")

    def test_rules_raise_safe_to_suspicious(self):
        b = self.check(self.svc(FakeEngine(0.01)), "http://paypal.com@evil.example/")[1]
        self.assertEqual(b["result"]["label"], "suspicious")
        self.assertIsNone(b["result"]["confidence"])

    def test_failures_never_safe(self):
        for score, code in ((RuntimeError("x"), "ML_INFERENCE_FAILED"), (math.nan, "ML_INVALID_MODEL_OUTPUT"), (1.5, "ML_INVALID_MODEL_OUTPUT")):
            st, b, _ = self.check(self.svc(FakeEngine(score)), "https://example.com/")
            self.assertEqual((st, b["status"], b["error"]["code"]), (500, "failed", code))
            self.assertNotIn("result", b)

    def test_invalid_and_auth(self):
        s = self.svc()
        for body in (None, [], {}, {"request_id": "x", "url": "http://a.com"}, {"request_id": RID}, {"request_id": RID, "url": "ftp://a.com"}):
            st, b, _ = s.check(TOKEN, body)
            self.assertEqual((st, b["error"]["code"]), (422, "ML_INVALID_REQUEST"))
        for tok in (None, "", "salah"):
            self.assertEqual(self.check(s, "http://a.com", token=tok)[0], 401)

    def test_idempotent(self):
        eng = FakeEngine(0.1); s = self.svc(eng)
        a = self.check(s, "https://example.com/"); b = self.check(s, "https://example.com/")
        self.assertEqual(a[1], b[1]); self.assertEqual(eng.calls, 1)

    def test_response_and_logs_do_not_leak_url(self):
        secret = "https://example.com/reset?token=RAHASIA123"
        with self.assertLogs("stiksafe_ml", level="INFO") as cm:
            st, b, _ = self.check(self.svc(FakeEngine(0.1)), secret)
        self.assertNotIn("RAHASIA123", "\n".join(cm.output))
        self.assertNotIn("RAHASIA123", json.dumps(b))
        self.assertEqual(b["target"]["host"], "example.com")

    def test_info(self):
        st, info, _ = self.svc(FakeEngine(0.1), Blocklist(["http://a.b/c"])).info(TOKEN)
        self.assertEqual((info["model_loaded"], info["blocklist_size"]), (True, 1))
        self.assertEqual(self.svc().info("salah")[0], 401)


@unittest.skipUnless(HAVE_NP, "numpy belum terpasang")
class TestTrainHelpers(unittest.TestCase):
    def setUp(self):
        spec = importlib.util.spec_from_file_location("train_url", os.path.join(ROOT, "scripts", "train_url.py"))
        self.t = importlib.util.module_from_spec(spec); spec.loader.exec_module(self.t)

    def test_split_by_domain_no_leakage(self):
        hosts = [f"sub{i}.site{j}.com" for j in range(300) for i in range(3)]
        by = {}
        for h in hosts:
            by.setdefault(self.t.group_of(h), set()).add(self.t.split_name(self.t.group_of(h)))
        self.assertTrue(all(len(v) == 1 for v in by.values()))
        names = [next(iter(v)) for v in by.values()]
        self.assertTrue(all(n in names for n in ("train", "val", "test")))
        self.assertGreater(names.count("train"), names.count("val"))

    def test_read_rows_inverts_label_dedups_and_skips_bad(self):
        with tmpdir() as d:
            p = os.path.join(d, "x.csv")
            with open(p, "w", newline="") as f:
                w = csv.writer(f); w.writerow(["URL", "label"])
                w.writerow(["https://good.com/", 1]); w.writerow(["https://good.com/", 1])
                w.writerow(["http://phish.example/login", 0]); w.writerow(["ftp://x.y/z", 0])
            rows, bad = self.t.read_rows(p, "1")
            self.assertEqual((len(rows), bad), (2, 1))
            self.assertEqual(sorted(y for _, y in rows), [0, 1])      # phishing = 1
            self.assertEqual([y for p_, y in rows if "phish" in p_.host], [1])

    def test_url_lines_and_domain_homepages(self):
        with tmpdir() as d:
            u = os.path.join(d, "u.csv"); dm = os.path.join(d, "d.csv")
            with open(u, "w") as f:
                f.write('# komentar\n"1","2024","http://evil.example/a.exe","online"\nbukan url\n')
            with open(dm, "w") as f:
                f.write("1,google.com\n2,facebook.com\n")
            self.assertEqual([p.host for p in self.t.url_lines(u)], ["evil.example"])
            self.assertEqual([p.normalized for p in self.t.domain_homepages(dm, 10)], ["https://google.com/", "https://facebook.com/"])


if __name__ == "__main__":
    unittest.main()
