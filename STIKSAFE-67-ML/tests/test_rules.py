"""Pemeriksa file NON-PE berbasis aturan. Semua berkas dibuat SINTETIS saat tes berjalan; potongan kata berisiko dirangkai
saat runtime. Isinya hanya penanda teks, bukan program."""
import io
import os
import random
import unittest
import zipfile
import zlib
from unittest import mock

from stiksafe_ml.analyzers import analyze_file, has_analyzer
from stiksafe_ml.formats import identify
from tests.helpers import TOKEN, files_left, make_pe, meta, new_service, stream, tmpdir

LNK_GUID = bytes.fromhex("0114020000000000c000000000000046")
JS = b"/Java" + b"Script"
OA = b"/Open" + b"Action"
LAUNCH = b"/Lau" + b"nch"


def pdf(*extra, eof=True):
    return b"%PDF-1.4\n1 0 obj\n<< /Type /Catalog " + b" ".join(extra) + b" >>\nendobj\ntrailer\n<<>>\n" + (b"%%EOF\n" if eof else b"")


def zbytes(entries, deflate=False):
    buf = io.BytesIO()
    with zipfile.ZipFile(buf, "w", zipfile.ZIP_DEFLATED if deflate else zipfile.ZIP_STORED) as z:
        for name, data in entries:
            z.writestr(name, data)
    return buf.getvalue()


OOXML = [("[Content_Types].xml", "<Types/>"), ("word/document.xml", "<w:document/>")]
REL = '<Relationships><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/%s" Target="http://x.example/t" TargetMode="External"/></Relationships>'


class Base(unittest.TestCase):
    def an(self, data, ext=None):
        with tmpdir() as d:
            p = os.path.join(d, "f.bin")
            with open(p, "wb") as f:
                f.write(data)
            fmt = identify(p)
            self.assertTrue(has_analyzer(fmt.detected), fmt.detected)
            return fmt.detected, analyze_file(p, fmt.detected, ext)


class TestPdf(Base):
    def test_clean(self):
        det, r = self.an(pdf())
        self.assertEqual((det, r["points"], r["flags"], r["coverage"]), ("pdf", 0, [], 1.0))

    def test_js_alone_is_one_point(self):
        r = self.an(pdf(JS + b" (x)"))[1]
        self.assertEqual((r["points"], r["flags"]), (1, ["PDF_JAVASCRIPT"]))

    def test_js_plus_open_action_is_suspicious_pattern(self):
        r = self.an(pdf(JS + b" (x)", OA + b" 2 0 R"))[1]
        self.assertEqual((r["points"], r["flags"]), (2, ["PDF_AUTO_ACTION", "PDF_JAVASCRIPT"]))

    def test_launch(self):
        self.assertEqual(self.an(pdf(LAUNCH + b" (x)"))[1]["points"], 2)

    def test_hex_obfuscated_names_are_decoded(self):
        r = self.an(pdf(b"/J#61vaScript (x)"))[1]
        self.assertEqual((r["points"], r["flags"]), (2, ["PDF_JAVASCRIPT", "PDF_OBFUSCATED_NAMES"]))

    def test_risk_hidden_in_compressed_stream_is_found(self):
        stream_data = zlib.compress(b"<< " + JS + b" (x) " + OA + b" 2 0 R >>")
        body = b"4 0 obj\n<< /Filter /FlateDecode >>\nstream\n" + stream_data + b"\nendstream\nendobj\n"
        data = pdf() + body + b"%%EOF\n"
        self.assertNotIn(JS, data)                      # memang tidak terlihat di byte mentah
        self.assertEqual(self.an(data)[1]["points"], 2)

    def test_decompression_bomb_is_capped_not_crashing(self):
        bomb = zlib.compress(b"\x00" * 12_000_000)
        data = pdf() + b"5 0 obj\n<<>>\nstream\n" + bomb + b"\nendstream\nendobj\n%%EOF\n"
        r = self.an(data)[1]
        self.assertEqual(r["points"], 0)

    def test_uncompressed_text_stream_is_scanned(self):
        body = b"4 0 obj\n<<>>\nstream\n<< " + JS + b" (x) " + OA + b" 2 0 R >>\nendstream\nendobj\n"
        self.assertEqual(self.an(pdf() + body + b"%%EOF\n")[1]["points"], 2)

    def test_binary_stream_noise_is_not_a_false_alarm(self):
        # data biner acak (gambar/font) di dalam stream: kebetulan membentuk "/JS" atau "/AA" tidak boleh dihitung
        noise = b"/JS " + b"/AA " + os.urandom(5000)
        body = b"4 0 obj\n<<>>\nstream\n" + noise + b"\nendstream\nendobj\n"
        r = self.an(pdf() + body + b"%%EOF\n")[1]
        self.assertEqual((r["points"], r["flags"]), (0, []))

    def test_encrypted_and_truncated(self):
        r = self.an(pdf(b"/Encrypt 5 0 R"))[1]
        self.assertIn("ENCRYPTED_CONTENT", r["flags"]); self.assertEqual(r["coverage"], 0.5)
        r = self.an(pdf(eof=False) + b"x" * 5000)[1]
        self.assertIn("PARSE_PARTIAL", r["flags"]); self.assertLessEqual(r["coverage"], 0.6)


class TestZipOffice(Base):
    def test_plain_zip_clean(self):
        det, r = self.an(zbytes([("a.txt", "hi")]))
        self.assertEqual((det, r["points"], r["flags"]), ("zip", 0, []))

    def test_executable_and_double_extension_inside(self):
        self.assertEqual(self.an(zbytes([("setup.exe", "x")]))[1]["flags"], ["ARCHIVE_CONTAINS_EXECUTABLE"])
        r = self.an(zbytes([("invoice.pdf.exe", "x")]))[1]
        self.assertEqual((r["points"], r["flags"]), (3, ["ARCHIVE_CONTAINS_EXECUTABLE", "ARCHIVE_DOUBLE_EXTENSION"]))
        self.assertEqual(self.an(zbytes([("docs/readme.pdf", "x")]))[1]["points"], 0)

    def test_path_traversal(self):
        r = self.an(zbytes([("../evil.txt", "x")]))[1]
        self.assertEqual((r["points"], r["flags"]), (2, ["ARCHIVE_PATH_TRAVERSAL"]))
        self.assertIn("ARCHIVE_PATH_TRAVERSAL", self.an(zbytes([("C:/x.txt", "x")]))[1]["flags"])

    def test_high_ratio_bomb_indicator(self):
        r = self.an(zbytes([("zeros.bin", b"\x00" * 3_000_000)], deflate=True))[1]
        self.assertIn("ARCHIVE_HIGH_RATIO", r["flags"]); self.assertGreaterEqual(r["points"], 2)

    def test_too_many_entries(self):
        r = self.an(zbytes([(f"f{i}", "") for i in range(10_050)]))[1]
        self.assertIn("ARCHIVE_TOO_MANY_ENTRIES", r["flags"]); self.assertGreaterEqual(r["points"], 2)

    def test_encrypted_flag(self):
        raw = bytearray(zbytes([("secret.txt", "x")]))
        i = raw.find(b"PK\x01\x02")
        raw[i + 8] |= 0x01                              # bit enkripsi di header direktori pusat
        r = self.an(bytes(raw))[1]
        self.assertIn("ENCRYPTED_CONTENT", r["flags"]); self.assertEqual(r["coverage"], 0.5)

    def test_corrupt_zip(self):
        r = self.an(b"PK\x03\x04" + bytes(range(50)))[1]
        self.assertEqual((r["flags"], r["coverage"]), (["PARSE_PARTIAL"], 0.4))

    def test_ooxml_clean_macro_template_embed(self):
        self.assertEqual(self.an(zbytes(OOXML))[1]["flags"], [])
        r = self.an(zbytes(OOXML + [("word/vbaProject.bin", "x")]))[1]
        self.assertEqual((r["points"], r["flags"]), (2, ["OFFICE_MACRO"]))
        r = self.an(zbytes(OOXML + [("word/_rels/document.xml.rels", REL % "attachedTemplate")]))[1]
        self.assertEqual((r["points"], r["flags"]), (2, ["OFFICE_EXTERNAL_TEMPLATE"]))
        r = self.an(zbytes(OOXML + [("word/_rels/document.xml.rels", REL % "hyperlink")]))[1]
        self.assertEqual((r["points"], r["flags"]), (0, []))                      # hyperlink biasa tidak dihitung
        r = self.an(zbytes(OOXML + [("word/_rels/document.xml.rels", REL % "oleObject")]))[1]
        self.assertEqual(r["flags"], ["OFFICE_EXTERNAL_OBJECT"])
        r = self.an(zbytes(OOXML + [("word/embeddings/oleObject1.bin", "x")]))[1]
        self.assertEqual(r["flags"], ["OFFICE_EMBEDDED_OBJECT"])

    def test_apk_like(self):
        self.assertEqual(self.an(zbytes([("AndroidManifest.xml", "x"), ("classes.dex", "x")]))[1]["flags"], ["EXECUTABLE_PACKAGE"])


class TestOleRtfLnk(Base):
    OLE = b"\xd0\xcf\x11\xe0\xa1\xb1\x1a\xe1" + b"\x00" * 64

    def test_ole(self):
        self.assertEqual(self.an(self.OLE)[1]["points"], 0)
        r = self.an(self.OLE + "_VBA_PROJECT".encode("utf-16-le"))[1]
        self.assertEqual((r["points"], r["flags"]), (2, ["OFFICE_MACRO"]))
        r = self.an(self.OLE + b"Equation.3")[1]
        self.assertEqual(r["flags"], ["OFFICE_EQUATION_EDITOR"])

    def test_rtf(self):
        self.assertEqual(self.an(b"{\\rtf1\\ansi Hello world}")[1]["points"], 0)
        r = self.an(b"{\\rtf1{\\*\\objdata 0105}\\objupdate}")[1]
        self.assertEqual((r["points"], r["flags"]), (2, ["RTF_EMBEDDED_OBJECT"]))

    def test_lnk(self):
        det, r = self.an(b"L\x00\x00\x00" + LNK_GUID + b"\x00" * 40)
        self.assertEqual((det, r["points"], r["flags"]), ("lnk", 2, ["LNK_FILE"]))


class TestImages(Base):
    PNG = b"\x89PNG\r\n\x1a\n" + b"\x00" * 30 + b"\x00\x00\x00\x00IEND\xaeB`\x82"
    JPG = b"\xff\xd8\xff\xe0" + b"\x00" * 50 + b"\xff\xd9"

    def test_clean(self):
        for data in (self.PNG, self.JPG, b"GIF89a" + b"\x00" * 30 + b";"):
            self.assertEqual(self.an(data)[1]["points"], 0)

    def test_payload_appended(self):
        r = self.an(self.PNG + b"PK\x03\x04" + b"x" * 100)[1]
        self.assertEqual((r["points"], r["flags"]), (2, ["IMAGE_EMBEDDED_PAYLOAD", "IMAGE_TRAILING_DATA"]))
        self.assertEqual(self.an(self.JPG + b"MZ" + b"\x90" * 100)[1]["points"], 2)
        r = self.an(b"GIF89a" + b"\x00" * 20 + b"This program cannot be run in DOS mode" + b";")[1]
        self.assertEqual(r["points"], 2)

    def test_trailing_data_alone_is_info_only(self):
        r = self.an(self.JPG + b"\x00" * 300)[1]
        self.assertEqual((r["points"], r["flags"]), (0, ["IMAGE_TRAILING_DATA"]))

    def test_signature_inside_body_is_not_a_false_alarm(self):
        body = b"\xff\xd8\xff\xe0" + b"PK\x03\x04MZ%PDF-" * 20 + b"\xff\xd9"
        self.assertEqual(self.an(body)[1]["points"], 0)


class TestText(Base):
    def test_clean_prose_and_csv(self):
        self.assertEqual(self.an(b"Halo, ini catatan rapat.\nTotal 12, 13, 14\n" * 50)[1]["flags"], [])

    def test_one_category_is_info(self):
        r = self.an(("Invoke-" + "WebRequest http://x.example").encode())[1]
        self.assertEqual((r["points"], r["flags"]), (1, ["SCRIPT_RISKY_PATTERN"]))

    def test_two_categories_suspicious(self):
        t = "-Window" + "Style Hidden ; $c.Download" + "String('http://x.example')"
        r = self.an(t.encode())[1]
        self.assertEqual((r["points"], r["flags"]), (2, ["SCRIPT_RISKY_PATTERN"]))

    def test_svg_script_and_base64_blob(self):
        svg = '<svg xmlns="http://www.w3.org/2000/svg"><scr' + "ipt>alert(1)</scr" + "ipt></svg>"
        self.assertEqual(self.an(svg.encode())[1]["flags"], ["SCRIPT_RISKY_PATTERN"])
        self.assertEqual(self.an(b"data " + b"A" * 600)[1]["flags"], ["SCRIPT_OBFUSCATED"])

    def test_utf16_text(self):
        t = "-Window" + "Style Hidden ; Invoke-" + "Expression x"
        self.assertEqual(self.an(b"\xff\xfe" + t.encode("utf-16-le"))[1]["points"], 2)


class TestMismatch(Base):
    def test_mismatch_cases(self):
        self.assertEqual(self.an(pdf(), "jpg")[1]["flags"], ["FORMAT_MISMATCH"])
        self.assertEqual(self.an(pdf(), "jpg")[1]["points"], 1)
        self.assertEqual(self.an(b"teks biasa", "pdf")[1]["flags"], ["FORMAT_MISMATCH"])
        self.assertEqual(self.an(pdf(), "pdf")[1]["flags"], [])
        self.assertEqual(self.an(zbytes(OOXML), "docx")[1]["flags"], [])
        self.assertEqual(self.an(pdf(), "xyz")[1]["flags"], [])          # ekstensi tak dikenal: tidak ditebak
        self.assertEqual(self.an(pdf(), "exe")[1]["flags"], ["FORMAT_MISMATCH"])


class TestServiceRules(unittest.TestCase):
    def setUp(self):
        self._d = tmpdir(); self.tmp = self._d.name; self.svc = new_service(self.tmp)

    def tearDown(self):
        self._d.cleanup()

    def scan(self, data, **over):
        return self.svc.scan(TOKEN, meta(data, **over), stream(data))

    def test_clean_pdf_is_unknown_with_no_risk_indicators(self):
        st, b, _ = self.scan(pdf(), declared_extension="pdf", extension_chain=["pdf"])
        self.assertEqual(st, 200)
        self.assertEqual((b["result"]["label"], b["result"]["confidence"]), ("unknown", None))
        self.assertEqual(b["result"]["reason_codes"], ["NO_RISK_INDICATORS"])
        self.assertEqual(b["analysis"], {"method": "rules", "family": "pdf", "rule_points": 0, "coverage": 1.0})
        self.assertEqual((b["model_version"], b["status"]), ("filerules-v1", "completed"))
        self.assertEqual(files_left(self.tmp), 0)

    def test_risky_pdf_is_suspicious(self):
        st, b, _ = self.scan(pdf(JS + b" (x)", OA + b" 2 0 R"), declared_extension="pdf")
        self.assertEqual((st, b["result"]["label"]), (200, "suspicious"))
        self.assertEqual(b["analysis"]["rule_points"], 2)
        self.assertIn("PDF_JAVASCRIPT", b["result"]["reason_codes"])

    def test_double_extension_on_non_pe_content(self):
        st, b, _ = self.scan(pdf(), declared_extension="exe", extension_chain=["pdf", "exe"])
        self.assertEqual(b["result"]["label"], "suspicious")
        self.assertIn("DOUBLE_EXTENSION", b["result"]["reason_codes"])

    def test_unsupported_content_with_double_extension_is_flagged_not_rejected(self):
        elf = b"\x7fELF" + b"\x00" * 40
        st, b, _ = self.scan(elf, declared_extension="exe", extension_chain=["pdf", "exe"])
        self.assertEqual((st, b["result"]["label"]), (200, "suspicious"))
        self.assertEqual((b["analysis"]["family"], b["analysis"]["method"]), ("elf", "rules"))
        st, b, _ = self.scan(elf, declared_extension="bin", extension_chain=["bin"],
                             request_id="1a2b3c4d-4b7a-4a39-9c62-0e5a3d1b2f10")        # ID baru: hindari cache idempotensi
        self.assertEqual((st, b["error"]["code"]), (422, "ML_UNSUPPORTED_FILE"))

    def test_zip_macro_office_and_lnk(self):
        b = self.scan(zbytes(OOXML + [("word/vbaProject.bin", "x")]), declared_extension="docx")[1]
        self.assertEqual((b["result"]["label"], b["analysis"]["family"]), ("suspicious", "zip"))
        self.assertIn("OFFICE_MACRO", b["result"]["reason_codes"])
        lnk = b"L\x00\x00\x00" + LNK_GUID + b"\x00" * 40
        self.assertEqual(self.scan(lnk, declared_extension="lnk")[1]["result"]["label"], "suspicious")

    def test_pe_flow_unchanged_and_reports_model_method(self):
        b = self.scan(make_pe("safe"), declared_extension="exe")[1]
        self.assertEqual(b["result"]["label"], "safe")
        self.assertEqual(b["analysis"], {"method": "model", "family": "pe"})

    def test_rules_never_safe_or_malicious(self):
        rnd = random.Random(7)
        samples = [pdf(), pdf(JS, OA), zbytes([("a", "b")]), b"{\\rtf1 x}", b"plain text", b"\x7fELF" + b"\x00" * 20]
        samples += [bytes(rnd.getrandbits(8) for _ in range(rnd.randint(1, 300))) for _ in range(80)]
        for i, data in enumerate(samples):
            st, b, _ = self.svc.scan(TOKEN, meta(data, declared_extension="bin",
                                                 request_id=f"{i:08x}-4b7a-4a39-9c62-0e5a3d1b2f10"), stream(data))
            if b["status"] == "completed" and b["analysis"]["method"] == "rules":
                self.assertIn(b["result"]["label"], ("unknown", "suspicious"))
            else:
                self.assertTrue(b["status"] == "failed" or b["analysis"]["method"] == "model")
            self.assertEqual(files_left(self.tmp), 0)

    def test_child_failure_is_error_not_a_label(self):
        with mock.patch("stiksafe_ml.service.run_in_child", side_effect=RuntimeError("timeout")):
            st, b, _ = self.scan(pdf(), declared_extension="pdf")
        self.assertEqual((st, b["error"]["code"]), (500, "ML_PREPROCESSING_FAILED"))
        self.assertNotIn("result", b)
        self.assertEqual(files_left(self.tmp), 0)

    def test_idempotent_for_rules_result(self):
        a = self.scan(pdf(), declared_extension="pdf")[1]
        b = self.scan(pdf(), declared_extension="pdf")[1]
        self.assertEqual(a, b)


if __name__ == "__main__":
    unittest.main()
