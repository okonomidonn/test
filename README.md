# SocialDesk

SNS運用代行チーム向けの投稿管理・予約・エンゲージメント分析ツールです。Next.js (App Router) + TypeScript で構築しています。

## 主な機能

- **クライアント / アカウント管理**: クライアントを登録し、それぞれの SNS アカウント（X, Instagram, Facebook, TikTok, YouTube, Threads, LINE公式）とフォロワー数を管理
- **投稿の作成・予約管理**: 下書き / 予約済み / 公開済みのステータス管理、予約日時（JST）指定、SNS ごとの文字数チェック（X: 280 など）、予約時刻を過ぎた未公開投稿の警告
- **エンゲージメント分析**: 各 SNS のインサイトから入力した実績（インプレッション・いいね・コメント・シェア・保存）を集計し、KPI、日別推移、SNS 別パフォーマンス、上位投稿を表示。クライアント・期間（7/30/90日）で絞り込み可能

> 現時点では各 SNS の API とは連携していません。予約投稿は実際には自動投稿されないため、投稿後に「公開済みにする」で反映し、実績を手入力する運用です。

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

[http://localhost:3000](http://localhost:3000) を開き、デモアカウント `demo@example.com` / `password123` でログインできます。

## ディレクトリ構成（抜粋）

```
prisma/schema.prisma      データモデル（User / Client / SocialAccount / Post）
prisma/seed.ts            デモデータ投入
src/app/dashboard/        エンゲージメント分析
src/app/posts/            投稿一覧・作成・編集
src/app/clients/          クライアント・SNSアカウント管理
src/app/actions/          Server Actions
src/proxy.ts              未ログイン時のリダイレクト
```
