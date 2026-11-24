import math
import unittest

from stiksafe_ml.labels import Thresholds, map_label

TH = Thresholds(t_suspicious=0.5, t_malicious=0.8, c_min=0.5)


class TestLabelRules(unittest.TestCase):
    def test_malicious(self):
        self.assertEqual(map_label(0.8, TH, coverage=1, flags=set())[0], "malicious")
        self.assertEqual(map_label(0.99, TH, coverage=1, flags=set())[0], "malicious")

    def test_suspicious(self):
        self.assertEqual(map_label(0.5, TH, coverage=1, flags=set())[0], "suspicious")
        self.assertEqual(map_label(0.79, TH, coverage=1, flags=set())[0], "suspicious")

    def test_safe_only_when_no_doubt(self):
        label, conf, reasons = map_label(0.1, TH, coverage=1.0, flags=set())
        self.assertEqual(label, "safe")
        self.assertAlmostEqual(conf, 0.9)
        self.assertEqual(reasons, [])

    def test_doubt_turns_safe_into_unknown(self):
        for flags, cov in [(set(), 0.3), ({"OUT_OF_DISTRIBUTION"}, 1), ({"ENCRYPTED_CONTENT"}, 1),
                           ({"LIMIT_EXCEEDED"}, 1)]:
            label, conf, _ = map_label(0.1, TH, coverage=cov, flags=flags)
            self.assertEqual(label, "unknown")
            self.assertIsNone(conf)  # confidence unknown = null

    def test_doubt_never_downgrades_malicious_or_suspicious(self):
        self.assertEqual(map_label(0.9, TH, coverage=0.1, flags={"OUT_OF_DISTRIBUTION"})[0], "malicious")
        self.assertEqual(map_label(0.6, TH, coverage=0.1, flags={"ENCRYPTED_CONTENT"})[0], "suspicious")

    def test_doubt_never_produces_safe(self):
        # sapu semua kombinasi: bila ada keraguan, hasil tidak boleh 'safe'
        for s in [i / 100 for i in range(101)]:
            for cov in (0.0, 0.49, 1.0):
                for fl in (set(), {"OUT_OF_DISTRIBUTION"}, {"LIMIT_EXCEEDED"}, {"ENCRYPTED_CONTENT"}):
                    label = map_label(s, TH, coverage=cov, flags=fl)[0]
                    if cov < TH.c_min or fl:
                        self.assertNotEqual(label, "safe", (s, cov, fl))

    def test_reason_codes_closed_enum(self):
        _, _, reasons = map_label(0.1, TH, coverage=0.2, flags={"PARSE_PARTIAL", "BUKAN_KODE_SAH"})
        self.assertEqual(reasons, ["LOW_FEATURE_COVERAGE", "PARSE_PARTIAL"])

    def test_invalid_score_raises_not_safe(self):
        for bad in (float("nan"), float("inf"), -0.1, 1.1, None, "0.2", True):
            with self.assertRaises(ValueError):
                map_label(bad, TH, coverage=1, flags=set())

    def test_invalid_thresholds(self):
        for a, b, c in [(0.9, 0.5, 0.5), (-0.1, 0.5, 0.5), (0.5, 1.5, 0.5), (0.5, 0.8, math.nan)]:
            with self.assertRaises(ValueError):
                Thresholds(a, b, c)


if __name__ == "__main__":
    unittest.main()
