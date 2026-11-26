import json
import os
import unittest

from stiksafe_ml.contract import parse_metadata
from stiksafe_ml.errors import MLError
from stiksafe_ml.formats import extension_mismatch, identify
from tests.helpers import REQ_ID, SCAN_ID, make_pe, meta, tmpdir

GOOD = {"scan_id": SCAN_ID, "request_id": REQ_ID, "sha256": "a" * 64, "size_bytes": 10,
        "submitted_at": "2026-11-14T02:00:00Z"}


class TestMetadata(unittest.TestCase):
    def bad(self, **over):
        d = dict(GOOD); d.update(over)
        with self.assertRaises(MLError) as cm:
            parse_metadata(json.dumps(d))
        self.assertEqual(cm.exception.code, "ML_INVALID_REQUEST")

    def test_ok(self):
        self.assertEqual(parse_metadata(json.dumps(GOOD)).scan_id, SCAN_ID.lower())

    def test_rejects(self):
        self.bad(scan_id="bukan-ulid"); self.bad(request_id="123"); self.bad(sha256="xyz")
        self.bad(size_bytes=0); self.bad(size_bytes=-1); self.bad(size_bytes=True)
        self.bad(size_bytes="10"); self.bad(submitted_at="2026-11-14 02:00:00")
        self.bad(submitted_at="2026-11-14T02:00:00+07:00")

    def test_missing_field_and_bad_json(self):
        d = dict(GOOD); del d["sha256"]
        with self.assertRaises(MLError):
            parse_metadata(json.dumps(d))
        with self.assertRaises(MLError):
            parse_metadata("{bukan json")
        with self.assertRaises(MLError):
            parse_metadata("[1,2]")

    def test_callback_allowlist(self):
        self.bad(callback_url="https://evil.example/hook")
        ok = parse_metadata(json.dumps({**GOOD, "callback_url": "https://be.internal/cb"}), ("be.internal",))
        self.assertEqual(ok.callback_url, "https://be.internal/cb")


class TestFormats(unittest.TestCase):
    def ident(self, data: bytes):
        with tmpdir() as d:
            p = os.path.join(d, "f")
            with open(p, "wb") as fh:
                fh.write(data)
            return identify(p)

    def test_pe_full(self):
        r = self.ident(make_pe())
        self.assertEqual((r.detected, r.family, r.partial), ("pe", "pe", False))

    def test_pe_partial(self):
        self.assertTrue(self.ident(b"MZ" + b"\x00" * 10).partial)
        bad = bytearray(make_pe()); bad[0x80:0x84] = b"XXXX"
        self.assertTrue(self.ident(bytes(bad)).partial)

    def test_known_but_unsupported_and_unknown(self):
        # family (didukung MODEL) selalu None selain PE; `detected` kini mencakup lebih banyak format
        for data, name in [(b"%PDF-1.7 xx", "pdf"), (b"PK\x03\x04abc", "zip"), (b"\x7fELFabc", "elf"),
                           (b"hello world", "text"), (b"\xd0\xcf\x11\xe0\xa1\xb1\x1a\xe1" + b"\0" * 20, "ole"),
                           (b"{\\rtf1 hi}", "rtf"), (b"\x89PNG\r\n\x1a\n" + b"\0" * 8, "png"),
                           (b"\xff\xd8\xff\xe0" + b"\0" * 8, "jpeg"), (b"GIF89a" + b"\0" * 8, "gif"),
                           (b"RIFF\0\0\0\0WEBPVP8 ", "webp"), (b"\x1f\x8b\x08" + b"\0" * 8, "gzip"),
                           (b"Rar!\x1a\x07\x00" + b"\0" * 8, "rar"), (b"7z\xbc\xaf\x27\x1c" + b"\0" * 8, "7z"),
                           (b"L\x00\x00\x00" + bytes.fromhex("0114020000000000c000000000000046") + b"\0" * 20, "lnk"),
                           (b"\x00\x01\x02\x03" * 50, "unknown")]:
            r = self.ident(data)
            self.assertEqual((r.detected, r.family), (name, None), name)

    def test_extension_is_only_a_hint(self):
        # file PE bernama .pdf tetap dikenali PE; hanya diberi flag mismatch
        r = self.ident(make_pe())
        self.assertEqual(r.family, "pe")
        self.assertTrue(extension_mismatch(r, "pdf"))
        self.assertFalse(extension_mismatch(r, "exe"))
        # PDF asli yang dinamai .exe tetap bukan PE (tidak masuk jalur MODEL)
        self.assertIsNone(self.ident(b"%PDF-1.4 abc").family)


if __name__ == "__main__":
    unittest.main()
