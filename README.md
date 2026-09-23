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
prisma/schema.prisma      データモデル（User / Invite / Client / SocialAccount / Post）
prisma/seed.ts            デモデータ投入
src/app/dashboard/        エンゲージメント分析
src/app/posts/            投稿一覧・作成・編集
src/app/clients/          クライアント・SNSアカウント管理
src/app/team/             メンバー一覧・招待リンク発行
src/app/actions/          Server Actions
src/app/api/instagram/    Instagram OAuth（連携・コールバック）
src/app/api/cron/tick/    予約投稿の実行・実績取得（定期実行）
src/lib/publisher.ts      自動投稿の状態管理
src/proxy.ts              未ログイン時のリダイレクト
```
