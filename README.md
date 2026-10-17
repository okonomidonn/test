# SocialDesk

SNS運用代行チーム向けの投稿管理・予約・エンゲージメント分析ツールです。Next.js (App Router) + TypeScript で構築しています。

## 主な機能

- **クライアント / アカウント管理**: クライアントを登録し、それぞれの SNS アカウント（X, Instagram, Facebook, TikTok, YouTube, Threads, LINE公式）とフォロワー数を管理
- **投稿の作成・予約管理**: 下書き / 予約済み / 公開済みのステータス管理、予約日時（JST）指定、SNS ごとの文字数チェック（X: 280 など）、予約時刻を過ぎた未公開投稿の警告
- **エンゲージメント分析**: 各 SNS のインサイトから入力した実績（インプレッション・いいね・コメント・シェア・保存）を集計し、KPI、日別推移、SNS 別パフォーマンス、上位投稿を表示。クライアント・期間（7/30/90日）で絞り込み可能

> Instagram（リール）は API 連携に対応しています（下記）。その他の SNS は、投稿後に「公開済みにする」で反映し、実績を手入力する運用です。

## Instagram リール連携

Instagram API（Instagram ログイン）を使い、リールの予約投稿と実績（再生数・いいね・コメント・シェア・保存）の自動取得を行います。

- クライアント詳細で Instagram アカウントの「Instagramと連携」を押すと OAuth で接続します（ビジネス / クリエイターアカウントのみ）。アクセストークンは暗号化して保存し、期限前に自動更新します。
- 連携済みアカウント宛ての投稿は動画をアップロード（Vercel Blob）し、「予約済み」にすると予約時刻に自動投稿されます。投稿一覧・投稿詳細の「今すぐ投稿」で即時投稿、失敗時は「再試行」できます。
- Instagram アプリから直接投稿したフィード投稿・リール（直近90日）も自動で取り込み、実績を同様に取得します。クライアント詳細の「今すぐ取り込む」で即時に取り込めます（ストーリーズは対象外）。
- 予約の実行・実績の取得・トークン更新・取り込みは `GET /api/cron/tick`（`Authorization: Bearer $CRON_SECRET`）で行います。
  - `vercel.json` で1日1回（JST 6:00）実行されます（Vercel Hobby の上限）。
  - 分単位で予約を実行するには、Vercel Pro の Cron か、cron-job.org などの外部サービスから5分ごとに上記 URL を呼び出してください。

必要な環境変数は `.env.example` を参照してください（`INSTAGRAM_APP_ID` / `INSTAGRAM_APP_SECRET` / `CRON_SECRET` / `BLOB_READ_WRITE_TOKEN`）。Meta のアプリには OAuth リダイレクト URI として `https://<ドメイン>/api/instagram/callback` を登録します。

## AI検索チェック（飲食店向け・`/ai`）

飲食店が ChatGPT・Gemini でどう紹介されているかを計測し、改善点を提案する店舗オーナー向けのツールです。SocialDesk と同じアプリ・データベースで動きます。

- **公開ページ** `/ai`（紹介ページ）、`/ai/signup`（店舗オーナーの無料登録・招待不要）、`/ai/login`
- **店舗オーナー**（`role = OWNER`）は `/ai/app` 以下だけを使えます。SocialDesk の画面には入れません
- **運用チーム**（`role = TEAM`、従来のメンバー）は全店舗を閲覧・計測でき、各質問の繰り返し回数（1〜3回）も選べます
- 店舗ごとのタブ
  - **AIでの表示チェック**：登録した質問（エリア・業態から自動生成、編集可）を各AIに聞き、推薦率・順位・競合との比較・推移を表示。回答全文も確認できます
  - **参考にされたページ**：AIが参照したサイトを、公式サイト／グルメサイト・地図／その他に分けて集計
  - **改善の提案**：公式サイトを読み取り、メニューや価格が文字で読めるか、営業情報、構造化データ、「一人飲み」「デート」などの利用シーンの記述、robots.txt を診断
  - **設定**：店舗情報・比べたい店・質問の編集、店舗の削除
- 計測は「AIに聞いて計測する」で開始。計測画面を開いている間は `POST /api/ai/runs/[id]/step` で順に処理され、閉じた後に残った分は `GET /api/cron/ai-check`（`Authorization: Bearer $CRON_SECRET`、`vercel.json` で1日1回）が進めます
- **費用の上限**：APIは1回答あたり数十円かかります。オーナーの計測回数・店舗数・質問数はプランで決まり（下記）、同じ店舗の計測は20時間に1回まで。サービス全体でも24時間に `GEO_DAILY_ANSWER_LIMIT`（既定300）回答まで
- 必要な環境変数は `.env.example` の「AI検索チェック」欄（`OPENAI_API_KEY` / `GEMINI_API_KEY` ほか）。開発環境では `GEO_ENGINES=mock` で固定回答のテスト用エンジンを使えます（本番では無効）

> AIの回答は毎回変わります。推薦率は決まった質問で測った目安で、APIの回答はアプリの回答と完全には一致しない場合があります。

### 料金プランと課金（Stripe）

| プラン | 月額（税込） | 店舗数 | 計測（1店舗・30日間） | 質問数 |
|---|---|---|---|---|
| フリー | 0円 | 1 | 1回 | 10問 |
| スターター | 4,980円 | 1 | 4回 | 15問 |
| プロ | 14,800円 | 3 | 4回 | 15問 |

プランの内容は `src/lib/billing/plans.ts` で変更できます。

- 申し込みはStripe Checkout、プラン変更・カード変更・解約・領収書はStripeのカスタマーポータル（`/ai/app/billing` から開く）で行います
- 契約状態は Webhook `POST /api/stripe/webhook` で同期します（`customer.subscription.created / updated / deleted / paused / resumed`）。順不同で届いた古いイベントは無視します
- 支払い遅延（`past_due`）の間は有料プランのまま使え、解約・失効するとフリーに戻ります。プランの店舗数を超えた店舗は閲覧できますが、計測は古い順に枠内の店舗だけです
- `/ai/pricing`（料金）と `/ai/legal`（特定商取引法に基づく表記）は公開ページです

**Stripeの設定手順**

1. Stripeで商品を2つ作り、それぞれに月額・日本円・税込の価格（4,980円／14,800円）を作る → 価格ID（`price_...`）を `STRIPE_PRICE_STARTER` / `STRIPE_PRICE_PRO` に設定
2. APIキー（シークレットキー）を `STRIPE_SECRET_KEY` に設定
3. Webhookの送信先に `https://<ドメイン>/api/stripe/webhook` を登録し、上記の5種類のイベントを選ぶ → 署名シークレットを `STRIPE_WEBHOOK_SECRET` に設定
4. カスタマーポータルを有効にし、プラン変更で2つの価格を選べるようにする
5. `/ai/legal` に表示する事業者情報（`LEGAL_*`）を設定する

3つ（`STRIPE_SECRET_KEY` と2つの価格ID）が揃うまでは、申し込みボタンは「準備中」と表示されます。

## 技術スタック

- Next.js (App Router, Server Actions) / TypeScript / Tailwind CSS
- Prisma + PostgreSQL (`@prisma/adapter-pg`)
- Auth.js (NextAuth v5) — メール/パスワード認証

## セットアップ

```bash
npm install
cp .env.example .env   # DATABASE_URL と AUTH_SECRET（`openssl rand -base64 32`）を設定
npx prisma migrate dev
npx prisma db seed
npm run dev
```

[http://localhost:3000](http://localhost:3000) を開き、デモアカウント `demo@example.com` / `password123` でログインできます（`prisma db seed` はローカル専用で、本番ビルドでは実行されません）。

## メンバー登録（招待制）

- アカウントが1つも無い状態では、最初の1人だけ `/register` から招待なしで登録できます。
- 以降のメンバーは「チーム」ページで発行した招待リンク（1回限り・7日間有効）からのみ登録できます。

## ディレクトリ構成（抜粋）

```
prisma/schema.prisma      データモデル（User / Invite / Client / SocialAccount / Post / Store / Question / MeasurementRun / Answer / SiteAudit）
prisma/seed.ts            デモデータ投入
src/app/(team)/           SocialDesk（運用チーム）の画面：dashboard / posts / clients / team / login / register
src/app/ai/               AI検索チェック（紹介ページ・オーナー登録・店舗画面）
src/lib/ai-check/         計測（AIへの質問・回答の解析・集計）とサイト診断
src/app/actions/          Server Actions
src/app/api/instagram/    Instagram OAuth（連携・コールバック）
src/app/api/cron/tick/    予約投稿の実行・実績取得（定期実行）
src/lib/publisher.ts      自動投稿の状態管理
src/proxy.ts              未ログイン時のリダイレクト
```
