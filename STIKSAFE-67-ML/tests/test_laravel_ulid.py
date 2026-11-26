import json
import unittest

from stiksafe_ml.contract import parse_metadata


class LaravelUlidContractTest(unittest.TestCase):
    def test_accepts_lowercase_laravel_ulid_and_normalizes_it(self):
        metadata = parse_metadata(json.dumps({
            "scan_id": "01m8601mzacrmhtzyfva91sbqj",
            "request_id": "6d891d66-4946-4aa8-810a-ac3197fe74e3",
            "sha256": "a" * 64,
            "size_bytes": 1,
            "submitted_at": "2026-11-24T05:21:50Z",
        }))

        self.assertEqual(metadata.scan_id, "01m8601mzacrmhtzyfva91sbqj")
