import importlib.util
import json
import os
import random
import unittest
from collections import Counter

try:
    import numpy as np
    HAVE_NP = True
except ImportError:
    HAVE_NP = False

from tests.helpers import tmpdir

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))


class FakeExtractor:
    def process_raw_features(self, raw):
        return [float(raw["x"])] * 4


@unittest.skipUnless(HAVE_NP, "numpy belum terpasang")
class TestSampling(unittest.TestCase):
    def setUp(self):
        spec = importlib.util.spec_from_file_location("calibrate", os.path.join(ROOT, "scripts", "calibrate.py"))
        self.cal = importlib.util.module_from_spec(spec); spec.loader.exec_module(self.cal)

    def make_files(self, d, weeks=6, per_week=2000):
        paths = []
        for w in range(weeks):
            p = os.path.join(d, f"w{w}_Win32_test.jsonl")
            with open(p, "w") as f:
                for i in range(per_week):
                    f.write(json.dumps({"label": i % 2, "file_type": "Win32", "x": w}) + "\n")
            paths.append(p)
        return paths

    def test_count_lines(self):
        with tmpdir() as d:
            paths = self.make_files(d, 2, 123)
            self.assertEqual([self.cal.count_lines(p) for p in paths], [123, 123])

    def test_uniform_rate_covers_all_weeks(self):
        with tmpdir() as d:
            paths = self.make_files(d)
            total = sum(self.cal.count_lines(p) for p in paths)
            rate = min(1.0, 3000 * 0.9 / total)
            X, y, skipped, capped = self.cal.collect(paths, rate, 3000, random.Random(1), FakeExtractor(), 4, Counter())
            self.assertFalse(capped)
            weeks = Counter(int(v) for v in X[:, 0])
            self.assertEqual(set(weeks), set(range(6)))             # SEMUA minggu terwakili
            self.assertTrue(all(300 < c < 700 for c in weeks.values()), weeks)   # kira-kira merata

    def test_cap_is_reported_as_biased(self):
        with tmpdir() as d:
            paths = self.make_files(d)
            _, _, _, capped = self.cal.collect(paths, 1.0, 500, random.Random(1), FakeExtractor(), 4, Counter())
            self.assertTrue(capped)

    def test_bad_rows_skipped_not_crash(self):
        with tmpdir() as d:
            p = os.path.join(d, "a_test.jsonl")
            with open(p, "w") as f:
                f.write("bukan json\n"); f.write(json.dumps({"label": 1, "file_type": "APK", "x": 1}) + "\n")
                f.write(json.dumps({"label": 1, "file_type": "Win32", "x": 2}) + "\n")
            X, y, skipped, capped = self.cal.collect([p], 1.0, 100, random.Random(1), FakeExtractor(), 4, Counter())
            self.assertEqual((len(y), skipped), (1, 1))             # APK diabaikan, baris rusak dihitung


if __name__ == "__main__":
    unittest.main()
