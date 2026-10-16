# 国立市 GEO診断（2026-10-16）

飲食店向けGEO対策事業の最初の顧客獲得に向けた、国立市3店舗（串と〆シ エソラ／えにし家／むろ）の調査・測定・営業準備。

## 結論

- **最初の営業先は「串と〆シ エソラ」**。AIにすでに推薦され始めている（参考値15%）一方、看板の「国立野菜」「〆」では未推薦。定休日・L.O.・席数の不一致、メニューがJS描画でAIに読めない、店名の読みが伝わらない、など**具体的で直しやすい課題**が揃っている
- 次点は「むろ」（推薦0%、ランチ・定休日の矛盾）。えにし家は既に整っており優先度低
- ChatGPT（OpenAI API・gpt-5.5・検索あり）で22回答を測定（08）：えにし家32%、エソラ14%、むろ9%。**ChatGPTは店の公式サイトを引用して推薦する**ことを確認。Geminiは未測定（API残高0）

## ファイル

| ファイル | 内容 |
|---|---|
| [01_店舗調査_一次情報.md](./01_店舗調査_一次情報.md) | 3店の公式・掲載情報の再確認、不一致一覧、未確認項目、目視確認シート |
| [02_検索意図マップ.md](./02_検索意図マップ.md) | 検索意図×店舗の対応表 |
| [03_AI推薦測定_2026-10-16.md](./03_AI推薦測定_2026-10-16.md) | 測定条件・結果・限界、ChatGPT/Gemini本測定の手順 |
| [measurement_log_2026-10-16.csv](./measurement_log_2026-10-16.csv) | 20回答の生ログ |
| [tally.py](./tally.py) | ログCSVから推薦率を集計 `python3 tally.py <csv>` |
| [08_ChatGPT測定_2026-10-16.md](./08_ChatGPT測定_2026-10-16.md) | ChatGPT（API）22回答の結果、引用元、費用実績 |
| [measurement_log_2026-10-16_api.csv](./measurement_log_2026-10-16_api.csv) / [raw_answers_2026-10-16_api.jsonl](./raw_answers_2026-10-16_api.jsonl) | ChatGPT測定の集計用ログ／回答全文 |
| [04_競合比較と改善余地.md](./04_競合比較と改善余地.md) | 3店比較と営業先の選定理由 |
| [05_改善施策_優先順位.md](./05_改善施策_優先順位.md) | 優先順位付き施策、FAQ・Googleビジネスプロフィール説明文の文案 |
| [esola_restaurant_jsonld_draft.json](./esola_restaurant_jsonld_draft.json) | 構造化データの下書き（店主確認後に確定） |
| [06_無料GEO診断レポート_エソラ.md](./06_無料GEO診断レポート_エソラ.md) | 店主に渡す無料診断レポート |
| [print/esola_diagnosis_report.pdf](./print/esola_diagnosis_report.pdf) | 診断レポートの印刷用PDF（A4・3ページ）。連絡先を入れて作り直す: `node print/build_pdf.js 担当 電話 メール` |
| [measure_api.py](./measure_api.py) | ChatGPT（OpenAI API）・Gemini APIでの自動測定（Web検索あり・10問×3回）。`OPENAI_API_KEY` / `GEMINI_API_KEY` が必要 |
| [07_営業資料_エソラ.md](./07_営業資料_エソラ.md) | DM文面、説明の流れ、ヒアリング、料金案、反論対応、契約後スケジュール |

## 未確認のまま残っている項目

- 食べログ（ボット検証で取得不可）、Googleマップ（店舗パネル描画不可）、Instagram（ログイン必須）の掲載内容
- Geminiでの推薦率（API残高0のため未測定）、ChatGPTアプリ画面での推薦率（APIでの測定のみ）
- エソラの正しい定休日・L.O.・席数・予算（店主へのヒアリングで確定）
