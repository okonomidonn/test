#!/usr/bin/env python3
"""zaito の「1日の数字」を集めて、JSON で出す（Notion に書き込む前の材料）。

- サイトの登録者数など: 集計API（admin-ajax.php?action=zaito_stats）。合言葉は環境変数 ZAITO_STATS_TOKEN。
- 訪問者・sign_up: Google アナリティクス（GA4 Data API）。鍵は環境変数 ZAITO_GA_SA_JSON_B64、プロパティは ZAITO_GA_PROPERTY_ID。

使い方: python3 ops/daily_numbers.py [YYYY-MM-DD]   （日付を省くと、日本時間の「きのう」）
個人情報は扱わない（どちらも人数の集計だけ）。鍵や合言葉は画面に出さない。
"""
import base64
import datetime as dt
import json
import os
import sys
import time
import urllib.parse
import urllib.request

JST = dt.timezone(dt.timedelta(hours=9))
STATS_URL = "https://zaito-work.com/wp-admin/admin-ajax.php?action=zaito_stats&days=40"


def http(url, data=None, headers=None):
    req = urllib.request.Request(url, data=data, headers=headers or {})
    with urllib.request.urlopen(req, timeout=60) as r:
        return json.loads(r.read().decode("utf-8"))


def b64url(b):
    return base64.urlsafe_b64encode(b).rstrip(b"=").decode()


def ga_token():
    import subprocess
    import tempfile

    raw = "".join(os.environ["ZAITO_GA_SA_JSON_B64"].split())
    sa = json.loads(base64.b64decode(raw))
    now = int(time.time())
    head = b64url(json.dumps({"alg": "RS256", "typ": "JWT"}).encode())
    claim = b64url(json.dumps({
        "iss": sa["client_email"],
        "scope": "https://www.googleapis.com/auth/analytics.readonly",
        "aud": "https://oauth2.googleapis.com/token",
        "iat": now,
        "exp": now + 600,
    }).encode())
    # 署名は openssl で行う（追加のライブラリがいらない）。鍵の一時ファイルは本人だけ読めるようにして、すぐ消す。
    with tempfile.NamedTemporaryFile("w", suffix=".pem", delete=False) as f:
        os.chmod(f.name, 0o600)
        f.write(sa["private_key"])
    try:
        sig = subprocess.run(["openssl", "dgst", "-sha256", "-sign", f.name],
                             input=f"{head}.{claim}".encode(), capture_output=True, check=True).stdout
    finally:
        os.unlink(f.name)
    jwt = f"{head}.{claim}.{b64url(sig)}"
    body = urllib.parse.urlencode({
        "grant_type": "urn:ietf:params:oauth:grant-type:jwt-bearer",
        "assertion": jwt,
    }).encode()
    return http("https://oauth2.googleapis.com/token", body)["access_token"]


def ga_day(day):
    prop = os.environ.get("ZAITO_GA_PROPERTY_ID", "557097653")
    url = f"https://analyticsdata.googleapis.com/v1beta/properties/{prop}:runReport"
    hdr = {"Authorization": "Bearer " + ga_token(), "Content-Type": "application/json"}
    rng = [{"startDate": day, "endDate": day}]
    users = http(url, json.dumps({
        "dateRanges": rng,
        "metrics": [{"name": "activeUsers"}, {"name": "sessions"}, {"name": "screenPageViews"}],
    }).encode(), hdr)
    vals = [int(v["value"]) for v in users["rows"][0]["metricValues"]] if users.get("rows") else [0, 0, 0]
    ev = http(url, json.dumps({
        "dateRanges": rng,
        "dimensions": [{"name": "eventName"}],
        "metrics": [{"name": "eventCount"}],
        "dimensionFilter": {"filter": {"fieldName": "eventName", "stringFilter": {"value": "sign_up"}}},
    }).encode(), hdr)
    signups = int(ev["rows"][0]["metricValues"][0]["value"]) if ev.get("rows") else 0
    return {"visitors": vals[0], "sessions": vals[1], "page_views": vals[2], "sign_up_events": signups}


def site(day):
    tok = os.environ.get("ZAITO_STATS_TOKEN", "")
    if not tok:
        return {"error": "ZAITO_STATS_TOKEN が環境変数にありません"}
    d = http(STATS_URL, headers={"X-Zaito-Token": tok, "User-Agent": "zaito-daily-numbers"})
    reg = d["registrations"]
    # 「その日の終わりの累計」= 今の累計 − その日より後の新規登録（削除された登録は数えない）。
    later = sum(n for k, n in reg["new_by_day"].items() if k > day)
    return {
        "new_registrations": reg["new_by_day"].get(day),
        "total_registrations_end_of_day": reg["total"] - later,
        "total_now": reg["total"],
        "verified_now": reg["verified"],
        "via_google_now": reg["via_google"],
        "via_line_now": reg["via_line"],
        "approved_companies_now": d["direct"]["companies"].get("approved", 0),
        "generated_at": d.get("generated_at"),
    }


def main():
    day = sys.argv[1] if len(sys.argv) > 1 else (dt.datetime.now(JST).date() - dt.timedelta(days=1)).isoformat()
    out = {"date": day}
    for name, fn in (("site", site), ("ga", ga_day)):
        try:
            out[name] = fn(day)
        except Exception as e:  # 片方が失敗しても、もう片方は出す
            out[name] = {"error": f"{type(e).__name__}: {e}"}
    print(json.dumps(out, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
