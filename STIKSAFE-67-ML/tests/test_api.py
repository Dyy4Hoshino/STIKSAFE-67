"""Uji lapisan HTTP. BUTUH: pip install -r requirements.txt (FastAPI + httpx).
Dilewati otomatis bila FastAPI belum terpasang."""
import json
import unittest

try:
    from fastapi.testclient import TestClient
    from stiksafe_ml.api import create_app
    HAVE = True
except ImportError:
    HAVE = False

from tests.helpers import TOKEN, make_pe, meta, settings, tmpdir


@unittest.skipUnless(HAVE, "FastAPI belum terpasang")
class TestApi(unittest.TestCase):
    def setUp(self):
        self._d = tmpdir()
        self.client = TestClient(create_app(settings(self._d.name)))
        self.auth = {"Authorization": f"Bearer {TOKEN}"}

    def tearDown(self):
        self._d.cleanup()

    def test_scan_ok(self):
        data = make_pe("malicious")
        r = self.client.post("/v1/scans", headers=self.auth,
                             data={"metadata": meta(data)}, files={"file": ("x.bin", data)})
        self.assertEqual(r.status_code, 200)
        self.assertEqual(r.json()["result"]["label"], "malicious")
        self.assertIn("x-request-id", r.headers)

    def test_no_token_401(self):
        data = make_pe()
        r = self.client.post("/v1/scans", data={"metadata": meta(data)}, files={"file": ("x", data)})
        self.assertEqual(r.status_code, 401)
        self.assertEqual(r.json()["error"]["code"], "ML_AUTH_FAILED")

    def test_missing_file_uses_contract_error_shape(self):
        r = self.client.post("/v1/scans", headers=self.auth, data={"metadata": "{}"})
        self.assertEqual(r.status_code, 422)
        self.assertEqual(r.json()["error"]["code"], "ML_INVALID_REQUEST")

    def test_health_model_info(self):
        self.assertEqual(self.client.get("/v1/health", headers=self.auth).json()["status"], "ready")
        self.assertEqual(self.client.get("/v1/model-info", headers=self.auth).status_code, 200)
        self.assertEqual(self.client.get("/v1/health").status_code, 401)

    def test_no_endpoint_to_change_model(self):
        for m in ("post", "put", "delete"):
            self.assertIn(getattr(self.client, m)("/v1/model-info", headers=self.auth).status_code, (404, 405))
