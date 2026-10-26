# ススメル / SocialDesk

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

## ススメル（Webサービス向け・`/ai`）

WebサービスがChatGPT・Geminiでどう紹介されるかを計測し、競合・引用元・サイト診断から、直す場所と書き換え例をまとめるサービスです。SocialDeskと同じアプリで動きます。飲食店向けの新規受付と、セルフ版ツールの新規販売は終了しています。

### 現在の販売内容

- 無料チェック：0円。3営業日以内に診断結果を手動でメール送付。
- GEO診断・改善トライアル：49,800円（税別・1回・30日間）。ChatGPT・Gemini各10問、競合2社、改善点3つ、既存ページ1ページの改善、30日後の再計測。
- 継続支援：月98,000円〜（税別・最低3か月）。基本料金の最低総額294,000円（税別）。
- 代行は相談・見積もりから進めます。フォーム送信時に契約・自動課金は発生しません。支払方法・支払期限・解約条件は、確定後に設定してください。

### 画面と権限

- 公開：`/ai`、`/ai/sample`、`/ai/pricing`、`/ai/method`、`/ai/apply`、`/ai/login`、`/ai/legal`。
- `/ai/tool` は料金へ、`/ai/signup` は申し込みへ転送します。サーバー側の新規オーナー登録も停止（`AI_SIGNUP_OPEN=1` での再開は通常行いません）。
- OWNERは自分のサービスだけを利用でき、TEAMは全サービスと申し込み一覧を扱います。
- サービス登録で質問を自動生成し、公式URLがあればサイト診断を開始します。計測終了後は結果へ戻ります。報告書は印刷・PDF保存に対応。
- 無料プランは1サービス・30日間で1回の計測。E2Eには新しいローカル専用アカウントを使うか、対象を確認したうえでローカルのテスト用サービスだけを整理してください。

### 申し込みの運用

- `/ai/apply` からCHECK/TRIALを保存。ハニーポット、10分間の重複抑止、項目別エラーを実装しています。
- TEAM向け `/ai/app/leads` で「未対応／連絡済み／完了」と通知状態を確認・更新できます。メール未設定でも受付は保存されるため、一覧を毎営業日確認してください。
- 申込者への自動返信・診断結果の自動送付は行いません。無料チェックは3営業日以内、トライアルは2営業日以内に担当者が返信します。
- 運営向け通知はResend APIへ送ります。追加SDKは不要です。確認済みドメインの送信元・受信先を決め、Productionにだけ `RESEND_API_KEY`、`MAIL_FROM`、`LEAD_NOTIFY_EMAIL`、`LEAD_NOTIFICATIONS_ENABLED=1` を設定します。未設定時は送信しません。
- Previewでは有効化設定があっても送信しません。通知本文は会社・サービス名と管理画面へのリンクだけで、氏名・連絡先・相談内容は含めません。
- 送信失敗は一覧から再送できます。定期再送 `GET /api/cron/lead-notifications` はBearer `CRON_SECRET` で認証。1回10件、最大5回まで自動再試行し、以後は手動確認します。Hobbyでは1日1回の実行です。
- 配信サービスの受付成功を「通知送信済み」とし、実際の受信は運営側でも確認します。プロバイダーの冪等キーとDBのロックで二重送信を抑止します（プロバイダー側の重複抑止期間には制限があります）。

### 計測と診断

- `OPENAI_API_KEY` / `GEMINI_API_KEY` を使用。ローカルでは `GEO_ENGINES=mock` で固定回答を使え、本番ではmockは無効です。
- 計測画面を開いている間はstep APIが処理し、残りは認証付き `/api/cron/ai-check` が進めます。同一サービスの20時間間隔制限、プラン枠、全体の `GEO_DAILY_ANSWER_LIMIT`（既定300回答）があります。
- 数値はAPIに決まった質問を聞いた目安です。アプリでの表示確率や改善との因果関係を保証しません。改善案は事実・要確認・仮説を区別します。

### 既存セルフ版契約（新規販売終了）

- フリー0円、スターター月4,980円、プロ月14,800円（いずれも税込）。既存ユーザーのログイン・閲覧・Stripe契約管理を維持しています。
- `STRIPE_SECRET_KEY`、`STRIPE_PRICE_STARTER`、`STRIPE_PRICE_PRO`、`STRIPE_WEBHOOK_SECRET` は既存セルフ版契約の設定です。代行トライアルの自動決済設定ではありません。
- Webhookは `/api/stripe/webhook`。subscription created / updated / deleted / paused / resumedを同期し、古い順不同イベントを無視します。

### 検証・公開準備

`npm test` で通知の単体テストを実行します。通知のDBテストは `DATABASE_URL` が `127.0.0.1:55432/susumeru_local` の場合だけ動作し、それ以外ではスキップします。外部メールは送信しません。

`npm run check:launch` は設定値を表示せず不足項目を出し、未完了なら終了コード1にします。本番用の秘密鍵をローカルのテスト環境に取り込まないでください。

本番URL： https://test-one-xi-90.vercel.app/ai

2026-10-26にVercelのNeon接続でPreviewブランチの自動作成を有効化し、`preview/codex/dependency-security` の作成を確認しました。今後もDB変更を含むPRでは、Preview専用ブランチであることを確認してください。本番DBでの書き込みテストは行いません。

運用手順と未完了事項は [公開・受付の手順](docs/launch-runbook.md) を参照してください。

## 技術スタック

- Next.js (App Router, Server Actions) / TypeScript / Tailwind CSS
- Prisma + PostgreSQL (`@prisma/adapter-pg`)
- Auth.js (NextAuth v5) — メール/パスワード認証

## セットアップ

```bash
npm ci
cp .env.example .env   # DATABASE_URL と AUTH_SECRET（`openssl rand -base64 32`）を設定
npx prisma migrate dev
npx prisma db seed
GEO_ENGINES=mock npm run dev   # Windows PowerShell: $env:GEO_ENGINES='mock'; npm run dev
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
