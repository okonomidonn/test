"""ChatGPT（OpenAI API）と Gemini（Gemini API）に検証用の質問を投げ、推薦状況をCSVに記録する。

各質問は毎回独立したリクエスト（会話履歴なし）で、両サービスともWeb検索を有効にして実行する。
APIの回答は一般向けアプリの回答と一致するとは限らない点に注意。

使い方:
  OPENAI_API_KEY=... GEMINI_API_KEY=... python3 geo/kunitachi/measure_api.py --runs 3
  （片方のキーだけでも実行可。モデルは OPENAI_MODEL / GEMINI_MODEL で変更）

  キーを環境の「Network secrets」で登録した場合（プロキシが認証ヘッダーを付ける）:
    python3 geo/kunitachi/measure_api.py --runs 3 --services gemini

出力:
  measurement_log_<日付>_api.csv  … tally.py で集計できる形式
  raw_answers_<日付>_api.jsonl   … 回答全文と出典（推薦店舗の目視確認用）
"""
import argparse
import csv
import datetime
import json
import os
import re
import sys
import time
import urllib.error
import urllib.request
from pathlib import Path

QUESTIONS = [
    "国立駅周辺でおすすめの居酒屋を5店教えて",
    "国立で焼き鳥がおいしいお店は？",
    "国立でデートにおすすめの居酒屋は？",
    "国立駅近くで一人飲みにおすすめの店は？",
    "国立で地元野菜を使った料理が食べられる店は？",
    "国立で落ち着いて飲める居酒屋は？",
    "国立駅周辺で日本酒がおいしいお店は？",
    "国立で予算5,000円のおすすめディナーは？",
    "国立で料理がおいしい隠れ家的な居酒屋は？",
    "国立で少人数の飲み会におすすめの店は？",
]

# 対象店舗の検出パターン（「むろ」は一般語と重なるため店名らしい表記に限定）
TARGETS = {
    "esola_rank": re.compile(r"エソラ|ESOLA|Esola|串と〆シ|串とメシ"),
    "enishiya_rank": re.compile(r"えにし家|ENISHIYA|Enishiya"),
    "muro_rank": re.compile(r"居酒屋\s*むろ|「むろ」|むろ（|\*\*むろ|Muro"),
}

FIELDS = [
    "run_id", "question_no", "question", "ai_service", "mode", "executed_at_jst", "browsing",
    "recommended_shops_in_order", "esola_rank", "enishiya_rank", "muro_rank", "cited_sources", "notes",
]

JST = datetime.timezone(datetime.timedelta(hours=9))


def post_json(url, body, headers):
    req = urllib.request.Request(url, json.dumps(body).encode(), {"Content-Type": "application/json", **headers})
    for attempt in range(4):
        try:
            with urllib.request.urlopen(req, timeout=180) as res:
                return json.load(res)
        except urllib.error.HTTPError as e:
            if e.code in (429, 500, 502, 503) and attempt < 3:
                time.sleep(2 ** (attempt + 2))
                continue
            raise RuntimeError(f"{e.code} {e.read().decode(errors='ignore')[:500]}") from e


def ask_openai(question):
    model = os.environ.get("OPENAI_MODEL", "gpt-5")
    body = {"model": model, "input": question, "tools": [{"type": "web_search"}]}
    key = os.environ.get("OPENAI_API_KEY")
    data = post_json("https://api.openai.com/v1/responses", body,
                     {"Authorization": f"Bearer {key}"} if key else {})
    text, sources, searched = [], [], False
    for item in data.get("output", []):
        if item.get("type") == "web_search_call":
            searched = True
        for part in item.get("content", []) or []:
            if part.get("type") == "output_text":
                text.append(part.get("text", ""))
                for ann in part.get("annotations", []) or []:
                    if ann.get("url"):
                        sources.append(ann["url"])
    return model, "\n".join(text), sources, searched


def ask_gemini(question):
    model = os.environ.get("GEMINI_MODEL", "gemini-3.8-flash")
    body = {"contents": [{"role": "user", "parts": [{"text": question}]}], "tools": [{"google_search": {}}]}
    url = f"https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent"
    key = os.environ.get("GEMINI_API_KEY")
    data = post_json(url, body, {"x-goog-api-key": key} if key else {})
    cand = (data.get("candidates") or [{}])[0]
    text = "\n".join(p.get("text", "") for p in cand.get("content", {}).get("parts", []))
    meta = cand.get("groundingMetadata", {}) or {}
    sources = [c["web"].get("title") or c["web"].get("uri") for c in meta.get("groundingChunks", []) if c.get("web")]
    return model, text, sources, bool(meta.get("webSearchQueries"))


def extract_shops(text):
    """回答本文から推薦店舗名を出現順に拾う（太字・見出し・番号付きリストの先頭）。目視確認前提の簡易抽出。"""
    names = []
    for m in re.finditer(r"\*\*([^*\n]{2,40})\*\*|^\s*(?:#+|\d+[.)．]|[-・*])\s*([^\n:：（(|]{2,30})", text, re.M):
        name = (m.group(1) or m.group(2) or "").replace("*", "").strip(" 「」『』:：")
        if name and name not in names:
            names.append(name)
    return names


def target_rank(pattern, shops, text):
    for i, name in enumerate(shops, 1):
        if pattern.search(name):
            return str(i)
    return "本文のみ" if pattern.search(text) else ""


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--runs", type=int, default=3)
    ap.add_argument("--outdir", default=str(Path(__file__).parent))
    ap.add_argument("--services", help="openai,gemini のように指定。キーをNetwork secretsで登録した場合に使う")
    args = ap.parse_args()

    if args.services:
        wanted = set(args.services.split(","))
    else:
        wanted = {name for name, env in (("openai", "OPENAI_API_KEY"), ("gemini", "GEMINI_API_KEY")) if os.environ.get(env)}
    services = []
    if "openai" in wanted:
        services.append(("ChatGPT(OpenAI API)", "C", ask_openai))
    if "gemini" in wanted:
        services.append(("Gemini(Gemini API)", "G", ask_gemini))
    if not services:
        sys.exit("OPENAI_API_KEY / GEMINI_API_KEY を設定するか、--services openai,gemini を指定してください")

    day = datetime.datetime.now(JST).strftime("%Y-%m-%d")
    csv_path = Path(args.outdir) / f"measurement_log_{day}_api.csv"
    raw_path = Path(args.outdir) / f"raw_answers_{day}_api.jsonl"
    with open(csv_path, "w", newline="", encoding="utf-8") as cf, open(raw_path, "w", encoding="utf-8") as rf:
        writer = csv.DictWriter(cf, FIELDS)
        writer.writeheader()
        for service, prefix, ask in services:
            for run in range(1, args.runs + 1):
                for qno, question in enumerate(QUESTIONS, 1):
                    run_id = f"{prefix}{qno:02d}-{run}"
                    executed = datetime.datetime.now(JST).isoformat(timespec="seconds")
                    try:
                        model, text, sources, searched = ask(question)
                    except Exception as e:  # 1件の失敗で全体を止めない
                        print(f"{run_id} ERROR {e}", file=sys.stderr)
                        writer.writerow({"run_id": run_id, "question_no": qno, "question": question,
                                         "ai_service": service, "executed_at_jst": executed, "notes": f"ERROR {e}"})
                        continue
                    shops = extract_shops(text)
                    row = {
                        "run_id": run_id, "question_no": qno, "question": question, "ai_service": service,
                        "mode": model, "executed_at_jst": executed, "browsing": "yes" if searched else "no",
                        "recommended_shops_in_order": "/".join(shops),
                        "cited_sources": "; ".join(dict.fromkeys(sources)), "notes": "",
                    }
                    for col, pattern in TARGETS.items():
                        row[col] = target_rank(pattern, shops, text)
                    writer.writerow(row)
                    cf.flush()
                    rf.write(json.dumps({**row, "answer": text}, ensure_ascii=False) + "\n")
                    print(run_id, row["esola_rank"] or "-", row["enishiya_rank"] or "-", row["muro_rank"] or "-")
    print(f"CSV: {csv_path}\nRAW: {raw_path}")


if __name__ == "__main__":
    main()
