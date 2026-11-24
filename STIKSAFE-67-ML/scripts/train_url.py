"""Melatih model URL (LightGBM) dari CSV berkolom URL + label, memilih ambang, lalu menulis model_card.json.

Pakai:
  python scripts/train_url.py --csv data\\phiusiil_urls.csv --out-dir models\\url-v1
Opsional (pemeriksaan di LUAR dataset latih):
  --external-malicious urlhaus.csv      -> berapa persen URL jahat ditandai
  --benign-domains tranco.csv           -> berapa persen homepage situs populer salah ditandai

Skrip melatih DUA model dan membandingkannya terbuka:
  A. semua fitur           (rawan jalan pintas dataset: https, www, panjang path)
  B. hanya fitur domain    (yang DISIMPAN, kecuali --save-all)
"""
import argparse
import csv
import datetime
import hashlib
import json
import os
import sys

sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from stiksafe_ml.errors import MLError
from stiksafe_ml.registry import sha256_file
from stiksafe_ml.urlfeatures import FEATURE_NAMES, PREPROCESSING_VERSION, extract
from stiksafe_ml.urlnorm import parse_url


# Fitur yang dipakai model B. Sengaja tanpa https/www/path/query: di dataset latih ciri-ciri itu hanya jalan pintas.
HOST_ONLY_KEEP = frozenset({"host_len", "host_dots", "n_subdomains", "host_hyphens", "host_digits", "host_digit_ratio",
                            "is_ip", "nonstandard_port", "has_punycode", "has_userinfo", "tld_len", "host_entropy",
                            "longest_label_len", "is_shortener"})


def mask_columns(X, keep):
    """Salinan X dengan kolom di luar `keep` di-nol-kan. keep=None -> tanpa topeng."""
    if keep is None:
        return X
    out = X.copy()
    for i, n in enumerate(FEATURE_NAMES):
        if n not in keep:
            out[:, i] = 0.0
    return out


def group_of(host: str) -> str:
    """Kelompok = domain 'terdaftar' (aproksimasi: dua label terakhir). Dipakai agar satu domain tidak
    terpecah ke train dan test (kebocoran data)."""
    labels = host.split(".")
    return host if len(labels) <= 2 else ".".join(labels[-2:])


def bucket(group: str) -> int:
    return int(hashlib.sha256(group.encode("utf-8")).hexdigest()[:8], 16) % 100


def split_name(group: str) -> str:
    b = bucket(group)
    return "train" if b < 70 else ("val" if b < 85 else "test")


def read_rows(csv_path: str, legit_label: str = "1"):
    """Kembalikan list (ParsedUrl, y) dengan y=1 untuk phishing. Duplikat (setelah normalisasi) dibuang."""
    rows, seen, bad = [], set(), 0
    with open(csv_path, "r", encoding="utf-8-sig", errors="replace", newline="") as f:   # tahan BOM & byte aneh
        rd = csv.DictReader(f)
        cols = {c.lower(): c for c in rd.fieldnames or []}
        if "url" not in cols or "label" not in cols:
            raise SystemExit("CSV harus punya kolom 'URL' dan 'label'.")
        for r in rd:
            try:
                p = parse_url(r[cols["url"]])
            except MLError:
                bad += 1
                continue
            if p.normalized in seen:
                continue
            seen.add(p.normalized)
            rows.append((p, 0 if str(r[cols["label"]]).strip() == legit_label else 1))
    return rows, bad


def url_lines(path: str, limit: int = 10 ** 9):
    """Ambil URL dari berkas teks/CSV apa pun: kolom pertama yang berawalan http, atau baris itu sendiri."""
    out = []
    with open(path, "r", encoding="utf-8", errors="ignore", newline="") as f:
        for line in f:
            if not line.strip() or line.startswith("#"):
                continue
            for field in next(csv.reader([line])):
                if field.strip().lower().startswith("http"):
                    try:
                        out.append(parse_url(field.strip()))
                    except MLError:
                        pass
                    break
            if len(out) >= limit:
                break
    return out


def domain_homepages(path: str, limit: int):
    out = []
    with open(path, "r", encoding="utf-8", errors="ignore") as f:
        for line in f:
            dom = line.strip().split(",")[-1].strip().lower()
            if not dom or dom.startswith("#"):
                continue
            try:
                out.append(parse_url("https://" + dom + "/"))
            except MLError:
                pass
            if len(out) >= limit:
                break
    return out


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--csv", required=True)
    ap.add_argument("--out-dir", required=True)
    ap.add_argument("--legit-label", default="1", help="nilai kolom label untuk URL sah (PhiUSIIL: 1)")
    ap.add_argument("--model-version", default="url-lgbm-hostonly-1")
    ap.add_argument("--save-all", action="store_true", help="simpan model A (semua fitur), BUKAN yang disarankan")
    ap.add_argument("--external-malicious")
    ap.add_argument("--benign-domains")
    ap.add_argument("--benign-limit", type=int, default=20000)
    ap.add_argument("--fpr-malicious", type=float, default=0.001)
    ap.add_argument("--fpr-suspicious", type=float, default=0.01)
    ap.add_argument("--seed", type=int, default=67)
    a = ap.parse_args()

    import lightgbm as lgb
    import numpy as np
    from stiksafe_ml.thresholds import flagged_fraction, select_thresholds, summarize

    print("Membaca dan membersihkan data...")
    rows, bad = read_rows(a.csv, a.legit_label)
    print(f"baris dipakai: {len(rows)} (URL tak terbaca dibuang: {bad}); phishing={sum(y for _, y in rows)}")
    X = np.asarray([extract(p) for p, _ in rows], dtype=np.float32)
    y = np.asarray([y for _, y in rows])
    split = np.asarray([split_name(group_of(p.host)) for p, _ in rows])
    tr, va, te = split == "train", split == "val", split == "test"
    print(f"train={tr.sum()} val={va.sum()} test={te.sum()} (dipecah per DOMAIN, bukan per baris)")
    if min(tr.sum(), va.sum(), te.sum()) < 500 or len(set(y[te])) < 2:
        sys.exit("Data terlalu sedikit/tidak seimbang setelah pemecahan.")

    params = dict(objective="binary", metric="auc", learning_rate=0.05, num_leaves=63, min_data_in_leaf=50,
                  feature_fraction=0.8, bagging_fraction=0.8, bagging_freq=1, seed=a.seed, verbose=-1)

    def fit(keep):
        M = mask_columns(X, keep)
        dtr = lgb.Dataset(M[tr], y[tr], feature_name=FEATURE_NAMES)
        dva = lgb.Dataset(M[va], y[va], reference=dtr)
        booster = lgb.train(params, dtr, num_boost_round=800, valid_sets=[dva],
                            callbacks=[lgb.early_stopping(40, verbose=False)])
        it = booster.best_iteration or booster.current_iteration()
        pred = lambda A: booster.predict(mask_columns(A, keep), num_iteration=it)
        th = select_thresholds(y[va], pred(X[va]), a.fpr_malicious, a.fpr_suspicious)
        imp = sorted(zip(FEATURE_NAMES, booster.feature_importance(importance_type="gain")), key=lambda t: -t[1])
        total = sum(v for _, v in imp) or 1.0
        return {"booster": booster, "it": it, "pred": pred, "th": th, "m": summarize(y[te], pred(X[te]), th),
                "top": [(n, round(100 * v / total, 1)) for n, v in imp[:6]]}

    print("\nMelatih model A (semua fitur)...")
    A = fit(None)
    print("Melatih model B (hanya fitur domain)...")
    B = fit(HOST_ONLY_KEEP)

    def show(name, r):
        m = r["m"]
        print(f"\n--- {name} ---")
        print(f"  ambang suspicious/malicious : {r['th']['t_suspicious']:.3f} / {r['th']['t_malicious']:.3f}")
        print(f"  roc_auc                     : {m['roc_auc']:.4f}")
        print(f"  salah tuduh (suspicious)    : {100 * m['fpr_at_t_suspicious']:.2f}%   (malicious): {100 * m['fpr_at_t_malicious']:.2f}%")
        print(f"  phishing tertangkap (susp.) : {100 * m['recall_at_t_suspicious']:.1f}%   (malicious): {100 * m['recall_at_t_malicious']:.1f}%")
        print("  fitur paling berpengaruh    : " + ", ".join(f"{n} {v}%" for n, v in r["top"]))

    print("\n=== PERBANDINGAN di data uji (domain yang belum pernah dilihat model) ===")
    show("A. SEMUA FITUR (rawan jalan pintas)", A)
    show("B. HANYA FITUR DOMAIN (disimpan)", B)

    chosen, keep = (A, None) if a.save_all else (B, HOST_ONLY_KEEP)
    pred, th = chosen["pred"], chosen["th"]
    ext = {}
    if a.external_malicious:
        mal = url_lines(a.external_malicious)
        if mal:
            s_ = pred(np.asarray([extract(p) for p in mal], dtype=np.float32))
            ext["external_malicious"] = {"n": len(mal), "flagged_at_t_suspicious": flagged_fraction(s_, th["t_suspicious"]),
                                         "flagged_at_t_malicious": flagged_fraction(s_, th["t_malicious"])}
    if a.benign_domains:
        ben = domain_homepages(a.benign_domains, a.benign_limit)
        if ben:
            s_ = pred(np.asarray([extract(p) for p in ben], dtype=np.float32))
            ext["popular_homepages"] = {"n": len(ben), "flagged_at_t_suspicious": flagged_fraction(s_, th["t_suspicious"]),
                                        "flagged_at_t_malicious": flagged_fraction(s_, th["t_malicious"])}

    os.makedirs(a.out_dir, exist_ok=True)
    model_name = "URL_LGBM.model"
    model_path = os.path.join(a.out_dir, model_name)
    chosen["booster"].save_model(model_path, num_iteration=chosen["it"])
    card = {
        "model_version": a.model_version, "model_file": model_name, "model_sha256": sha256_file(model_path),
        "dataset_version": "PhiUSIIL (UCI #967, CC BY 4.0), dipecah per domain", "preprocessing_version": PREPROCESSING_VERSION,
        "feature_dim": len(FEATURE_NAMES), "thresholds": {k: float(v) for k, v in th.items()},
        "status": "staging", "provisional": True,
        "metrics": {"kept_features": sorted(keep) if keep else None,
                    "test": chosen["m"], "comparison": {"all_features": A["m"], "host_only": B["m"]},
                    "top_features_gain_pct": chosen["top"], "external": ext,
                    "caveat": "Dataset PhiUSIIL diketahui terlalu mudah; kinerja nyata di web belum terukur. Label safe dimatikan untuk URL."},
        "created_at": datetime.datetime.now(datetime.timezone.utc).strftime("%Y-%m-%dT%H:%M:%SZ"),
    }
    with open(os.path.join(a.out_dir, "model_card.json"), "w", encoding="utf-8") as f:
        json.dump(card, f, indent=2, ensure_ascii=False)
    print("\n=== Pemeriksaan di luar dataset ===");   print(json.dumps(ext, indent=2) if ext else "(tidak diberikan)")
    print("\nModel yang disimpan:", "A (semua fitur)" if a.save_all else "B (hanya fitur domain)")
    print("Tulis ke:", os.path.join(a.out_dir, "model_card.json"))


if __name__ == "__main__":
    main()
