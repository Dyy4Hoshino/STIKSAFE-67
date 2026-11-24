import importlib.util
import os
import sys
import types
import unittest

try:
    import httpx  # noqa: F401
except ImportError:                                  # lingkungan tanpa httpx: cukup tiruan agar fungsi murni bisa diuji
    sys.modules["httpx"] = types.ModuleType("httpx")

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
sys.path.insert(0, os.path.join(ROOT, "scripts"))
spec = importlib.util.spec_from_file_location("bench_scan", os.path.join(ROOT, "scripts", "bench_scan.py"))
bs = importlib.util.module_from_spec(spec); spec.loader.exec_module(bs)


class TestBench(unittest.TestCase):
    def test_percentile(self):
        v = [1.0, 2.0, 3.0, 4.0, 5.0, 6.0, 7.0, 8.0, 9.0, 10.0]
        self.assertEqual((bs.percentile(v, 50), bs.percentile(v, 95), bs.percentile(v, 100)), (5.0, 10.0, 10.0))
        self.assertTrue(bs.percentile([], 50) != bs.percentile([], 50))   # NaN

    def test_summarize(self):
        res = [(1.0, 200, "safe"), (3.0, 200, "suspicious"), (2.0, 200, "safe"), (0.1, 429, None), (0.5, 0, None)]
        s = bs.summarize(res, wall=10.0)
        self.assertEqual((s["n"], s["codes"], s["labels"]), (5, {200: 3, 429: 1, 0: 1}, {"safe": 2, "suspicious": 1}))
        self.assertAlmostEqual(s["per_minute"], 18.0)
        self.assertEqual((s["p50"], s["max"]), (2.0, 3.0))                # hanya yang sukses dihitung

    def test_summarize_all_failed(self):
        s = bs.summarize([(0.1, 429, None)], wall=1.0)
        self.assertEqual(s["per_minute"], 0.0)
        self.assertTrue(s["max"] != s["max"])


if __name__ == "__main__":
    unittest.main()
