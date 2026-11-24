"""Menilai alur URL LENGKAP (daftar hitam + aturan + model) pada daftar URL milik Anda. URL TIDAK dibuka.

Pakai:
  python scripts/eval_url_set.py --model-dir models\\url-v1 --file urls_sah.txt --expect benign
  python scripts/eval_url_set.py --model-dir models\\url-v1 --file urlhaus.csv --expect malicious --limit 5000
  python scripts/eval_url_set.py --model-dir models\\url-v1 --file tranco.csv --domains --expect benign --limit 20000
--expect benign    : daftar berisi URL SAH -> yang ditandai = SALAH TUDUH
--expect malicious : daftar berisi URL JAHAT -> yang lolos = TERLEWAT
Catatan: untuk evaluasi, label 'safe' ditampilkan apa adanya (di layanan asli ia menjadi 'unknown')."""
import argparse
import csv
import os
import sys
import uuid
from collections import Counter

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from stiksafe_ml.blocklist import load_optional
from stiksafe_ml.config import Settings
from stiksafe_ml.url_service import UrlService

TOKEN = "eval-lokal"


def read_urls(path, domains=False, limit=10 ** 9):
    out = []
    with open(path, "r", encoding="utf-8-sig", errors="ignore", newline="") as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith("#"):
                continue
            fields = next(csv.reader([line]))
            if domains:
                out.append("https://" + fields[-1].strip().lower() + "/")
            else:
                hit = next((x.strip() for x in fields if x.strip().lower().startswith("http")), None)
                out.append(hit if hit else line)
            if len(out) >= limit:
                break
    return out


def evaluate(urls, service):
    labels, reasons, flagged, unflagged, invalid = Counter(), Counter(), [], [], 0
    for u in urls:
        st, body, _ = service.check(TOKEN, {"request_id": str(uuid.uuid4()), "url": u})
        if body.get("status") != "completed":
            invalid += 1
            continue
        res = body["result"]
        labels[res["label"]] += 1
        reasons.update(res["reason_codes"])
        (flagged if res["label"] in ("suspicious", "malicious") else unflagged).append((u, res["label"], res["reason_codes"], res["confidence"]))
    n = sum(labels.values())
    return {"n": n, "invalid": invalid, "labels": labels, "reasons": reasons, "flagged": flagged,
            "unflagged": unflagged, "flagged_pct": (100.0 * len(flagged) / n) if n else 0.0}


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--model-dir", default="")
    ap.add_argument("--blocklist", default="")
    ap.add_argument("--file", required=True)
    ap.add_argument("--domains", action="store_true", help="berkas berisi nama domain (mis. Tranco rank,domain)")
    ap.add_argument("--expect", choices=["benign", "malicious"], required=True)
    ap.add_argument("--limit", type=int, default=20000)
    ap.add_argument("--show", type=int, default=15)
    ap.add_argument("--solo-flag", action="store_true", help="izinkan skor sangat tinggi menandai sendirian")
    a = ap.parse_args()

    engine = None
    if a.model_dir:
        from stiksafe_ml.url_engine import UrlModelEngine
        engine = UrlModelEngine.from_dir(a.model_dir)
    svc = UrlService(Settings(service_tokens=(TOKEN,), stub_mode=True, url_allow_safe=True,
                              url_model_solo_flag=a.solo_flag), engine,
                     load_optional(a.blocklist))
    urls = read_urls(a.file, a.domains, a.limit)
    r = evaluate(urls, svc)

    print(f"model            : {engine.model_version if engine else '(tanpa model: aturan + daftar hitam saja)'}")
    print(f"URL dibaca       : {len(urls)}   dinilai: {r['n']}   tidak valid: {r['invalid']}")
    print("sebaran label    : " + ", ".join(f"{k}={v}" for k, v in sorted(r["labels"].items())))
    if a.expect == "benign":
        print(f"\nSALAH TUDUH (ditandai suspicious/malicious): {len(r['flagged'])} dari {r['n']} = {r['flagged_pct']:.1f}%")
        shown = r["flagged"]
    else:
        print(f"\nTERTANGKAP (suspicious/malicious): {len(r['flagged'])} dari {r['n']} = {r['flagged_pct']:.1f}%")
        print(f"TERLEWAT  (safe/unknown)         : {len(r['unflagged'])} dari {r['n']} = {100 - r['flagged_pct']:.1f}%")
        shown = r["unflagged"]
    if r["reasons"]:
        print("alasan terbanyak : " + ", ".join(f"{k}={v}" for k, v in r["reasons"].most_common(6)))
    if shown:
        print(f"\nContoh yang {'SALAH DITANDAI' if a.expect == 'benign' else 'TERLEWAT'} (maks {a.show}):")
        for u, lab, rs, cf in shown[: a.show]:
            sk = f" skor={cf:.3f}" if cf is not None else ""
            print(f"  [{lab}{sk}] {u[:100]}   {','.join(rs)}")


if __name__ == "__main__":
    main()
