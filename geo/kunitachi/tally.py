"""測定ログCSVから対象店舗・競合店舗の推薦率を集計する。

使い方: python3 geo/kunitachi/tally.py geo/kunitachi/measurement_log_2026-10-16.csv
"""
import csv
import sys
from collections import Counter

TARGETS = {"esola_rank": "串と〆シ エソラ", "enishiya_rank": "えにし家", "muro_rank": "むろ"}


def normalize(name):
    """表記ゆれ（支店名・業態の前置き・空白）を除いて店舗名を揃える。"""
    name = name.replace(" ", "")
    for suffix in ("国立南口店", "東京国立店", "国立店", "国立"):
        name = name.removesuffix(suffix)
    for prefix in ("居酒屋", "ダイニングバー", "炙り居酒屋", "大衆酒場", "クラフト酒場"):
        name = name.removeprefix(prefix)
    return name


def main(path):
    # API測定でエラーになった行は回答数に含めない
    rows = [r for r in csv.DictReader(open(path, encoding="utf-8")) if not r["notes"].startswith("ERROR")]
    groups = {"全体": rows}
    for row in rows:
        groups.setdefault(f"{row['ai_service']} / {row['mode']}", []).append(row)

    for label, group in groups.items():
        n = len(group)
        print(f"== {label}（{n}回答）")
        for col, shop in TARGETS.items():
            hits = [r[col].strip() for r in group if r[col].strip()]
            ranks = [int(h) for h in hits if h.isdigit()]
            avg = f"平均順位 {sum(ranks) / len(ranks):.1f}" if ranks else "-"
            print(f"  {shop}: {len(hits)}/{n} = {len(hits) / n * 100:.0f}%  {avg}")

    counts = Counter()
    for row in rows:
        # "(他)" 付きは回答末尾で補足的に触れただけの店舗なので数えない
        shops = {normalize(s) for s in row["recommended_shops_in_order"].split("/") if s and not s.startswith("(")}
        counts.update(shops)
    print(f"== 推薦回数上位（全{len(rows)}回答、補足言及は除く）")
    for shop, c in counts.most_common(15):
        print(f"  {shop}: {c}回 ({c / len(rows) * 100:.0f}%)")


if __name__ == "__main__":
    main(sys.argv[1])
