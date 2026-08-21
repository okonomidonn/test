# zaito

在宅ワークに特化した求人サイトです。Next.js (App Router) + TypeScript によるフルスタックアプリケーションで、求職者の会員登録・応募機能と、企業の求人投稿・応募者管理機能を備えています。

## 主な機能

- 求人一覧・検索・絞り込み(キーワード / 職種 / 働き方 / 雇用形態)
- 求人詳細ページ
- 求職者アカウント登録・ログイン、求人への応募、応募履歴の確認(マイページ)
- 企業アカウント登録・ログイン、求人の投稿・編集・公開/非公開切り替え・削除
- 企業ダッシュボードでの応募者一覧確認・選考ステータス更新

## 技術スタック

- [Next.js](https://nextjs.org/) (App Router, Server Actions)
- TypeScript
- Tailwind CSS
- [Prisma](https://www.prisma.io/) + SQLite (`@prisma/adapter-better-sqlite3`)
- [Auth.js (NextAuth v5)](https://authjs.dev/) — Credentials(メール/パスワード)認証

## セットアップ

```bash
npm install
cp .env.example .env   # AUTH_SECRET は `openssl rand -base64 32` などで生成
npx prisma migrate dev
npx prisma db seed
npm run dev
```

[http://localhost:3000](http://localhost:3000) を開いてください。

## デモアカウント(`npx prisma db seed` 実行後)

パスワードはすべて `password123` です。

| 種別 | メールアドレス |
| --- | --- |
| 求職者 | `seeker@example.com` |
| 企業 | `hr@cloudworks.example.com` |
| 企業 | `recruit@homeletter.example.com` |
| 企業 | `jobs@pixeldesign.example.com` |

## ディレクトリ構成(抜粋)

```
prisma/schema.prisma       データモデル(User / Company / Job / Application)
prisma/seed.ts             サンプルデータ投入スクリプト
src/auth.ts / auth.config.ts  認証設定(Node用 / Edge対応の分割構成)
src/app/                   ルーティング(App Router)
src/app/actions/           Server Actions(登録・ログイン・求人CRUD・応募)
src/components/            UIコンポーネント
```
