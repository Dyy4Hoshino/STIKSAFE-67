import hashlib
import unittest

from stiksafe_ml.namecheck import analyze_extensions, apply_name_policy, disguised_executable
from tests.helpers import TOKEN, make_pe, meta, new_service, stream, tmpdir


class TestRules(unittest.TestCase):
    def test_double_extension_detected(self):
        for chain in (["pdf", "exe"], ["docx", "scr"], ["jpg", "bat"], ["txt", "pdf", "exe"], ["zip", "exe"]):
            self.assertIn("DOUBLE_EXTENSION", analyze_extensions(chain)[0], chain)

    def test_normal_names_not_flagged(self):
        for chain in ([], ["exe"], ["pdf"], ["tar", "gz"], ["exe", "pdf"], ["setup", "exe"], ["pdf", "docx"]):
            self.assertNotIn("DOUBLE_EXTENSION", analyze_extensions(chain)[0], chain)

    def test_odd_chars_flagged_and_dropped(self):
        flags, clean = analyze_extensions(["pdf", "ex\u202ee", "exe"])
        self.assertIn("ODD_EXTENSION_CHARS", flags)
        self.assertEqual(clean, ["pdf", "exe"])
        self.assertIn("DOUBLE_EXTENSION", flags)

    def test_disguised_executable(self):
        self.assertTrue(disguised_executable("pe", "pdf"))
        self.assertFalse(disguised_executable("pe", "exe"))
        self.assertFalse(disguised_executable("pe", None))
        self.assertFalse(disguised_executable(None, "pdf"))

    def test_policy_only_raises_caution(self):
        f = {"DOUBLE_EXTENSION"}
        self.assertEqual(apply_name_policy("safe", 0.9, f), ("suspicious", None))
        self.assertEqual(apply_name_policy("unknown", None, f), ("suspicious", None))
        self.assertEqual(apply_name_policy("malicious", 0.95, f), ("malicious", 0.95))   # tidak diturunkan
        self.assertEqual(apply_name_policy("suspicious", 0.6, f), ("suspicious", 0.6))
        self.assertEqual(apply_name_policy("safe", 0.9, set()), ("safe", 0.9))          # tanpa flag: tak berubah
        self.assertEqual(apply_name_policy("safe", 0.9, {"ODD_EXTENSION_CHARS"}), ("safe", 0.9))


class TestEndToEnd(unittest.TestCase):
    def setUp(self):
        self._d = tmpdir(); self.svc = new_service(self._d.name)

    def tearDown(self):
        self._d.cleanup()

    def scan(self, data, **over):
        return self.svc.scan(TOKEN, meta(data, **over), stream(data))

    def test_pdf_exe_is_suspicious_even_if_model_says_safe(self):
        data = make_pe("safe")
        base = self.scan(data, extension_chain=["exe"], request_id="aaaaaaaa-4b7a-4a39-9c62-0e5a3d1b2f10")[1]
        self.assertEqual(base["result"]["label"], "safe")           # pembanding: tanpa tipuan -> safe
        tricked = self.scan(data, extension_chain=["pdf", "exe"], declared_extension=None)[1]
        self.assertEqual(tricked["result"]["label"], "suspicious")
        self.assertIn("DOUBLE_EXTENSION", tricked["result"]["reason_codes"])
        self.assertIsNone(tricked["result"]["confidence"])

    def test_pdf_name_with_pe_content(self):
        data = make_pe("safe")
        r = self.scan(data, declared_extension="pdf")[1]
        self.assertEqual(r["result"]["label"], "suspicious")
        self.assertIn("DISGUISED_EXECUTABLE", r["result"]["reason_codes"])
        self.assertIn("FORMAT_MISMATCH", r["result"]["reason_codes"])

    def test_malicious_stays_malicious(self):
        r = self.scan(make_pe("malicious"), extension_chain=["pdf", "exe"])[1]
        self.assertEqual(r["result"]["label"], "malicious")
        self.assertIn("DOUBLE_EXTENSION", r["result"]["reason_codes"])

    def test_declared_extension_derived_from_chain(self):
        r = self.scan(make_pe("safe"), extension_chain=["pdf"], declared_extension=None)[1]
        self.assertIn("DISGUISED_EXECUTABLE", r["result"]["reason_codes"])

    def test_invalid_chain_rejected(self):
        for bad in ("pdf.exe", [1, 2], ["a"] * 9, ["x" * 33]):
            st, body, _ = self.scan(make_pe(), extension_chain=bad)
            self.assertEqual(st, 422); self.assertEqual(body["error"]["code"], "ML_INVALID_REQUEST")

    def test_odd_chain_never_breaks_scan(self):
        st, body, _ = self.scan(make_pe("safe"), extension_chain=["pdf", "ex\u202ee"])
        self.assertEqual(st, 200)
        self.assertIn("ODD_EXTENSION_CHARS", body["result"]["reason_codes"])


if __name__ == "__main__":
    unittest.main()
