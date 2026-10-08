# Search Console 登録手順（所要時間 10〜15分）

Search Consoleは、Googleでどんな言葉で検索されて、何回表示・クリックされたかが分かる無料ツール。登録してサイトマップを送ると、新しいページがGoogleに早く見つけてもらえる。

## 1. プロパティを作る
1. https://search.google.com/search-console を、GA4と同じGoogleアカウントで開く
2. 「プロパティを追加」→ 右側の **「URLプレフィックス」** を選ぶ
3. `https://zaito-work.com/` と入力して「続行」

（左側の「ドメイン」はDNSの設定が必要なので、今回は使わない）

## 2. 所有権を確認する（どちらか一つ）

**A. Googleアナリティクスで確認（いちばん簡単）**
- 確認方法の一覧に「Google アナリティクス」が出ていれば、それを選んで「確認」を押すだけ。
- zaitoには GA4（G-0X7SF35P0L）が入っているので、同じアカウントなら通る。

**B. HTMLタグで確認（Aで失敗したとき）**
1. 確認方法から「HTMLタグ」を選び、表示された `<meta name="google-site-verification" content="……">` をコピー
2. WordPress管理画面 → **設定 > 一般** → 「Search Console 確認コード（zaito）」にそのまま貼り付けて「変更を保存」
3. Search Consoleに戻って「確認」を押す

## 3. サイトマップを送る
1. 左メニュー「サイトマップ」
2. 「新しいサイトマップの追加」に `wp-sitemap.xml` と入力して「送信」
3. 状態が「成功しました」になればOK（数時間〜数日かかることもある）

サイトマップには、トップページ・企業向けページ・条件別ページ10件・求人ページ52件が入っている。

## 4. 大事なページはインデックス登録をリクエスト
左メニュー「URL検査」に次のURLを1つずつ入れて、「インデックス登録をリクエスト」を押す（1日の回数に上限があるので、上から順に）。
1. https://zaito-work.com/
2. https://zaito-work.com/zaitaku/mikeiken/
3. https://zaito-work.com/zaitaku/jimu/
4. https://zaito-work.com/zaitaku/tanjikan/
5. https://zaito-work.com/zaitaku/shakaijin/
6. https://zaito-work.com/zaitaku/sns/
7. https://zaito-work.com/for-companies/

## 5. その後に見るところ（週1回でOK）
- 「検索パフォーマンス」→ どんな言葉で表示されたか（例：「在宅 事務 未経験」）。表示が多いのにクリックが少ない言葉があれば、そのページのタイトルや説明文を直す
- 「ページ」→ 「登録されていません」の理由。「クロール済み - インデックス未登録」が多い場合は、中身が薄いと判断されているので文章を足す

## 6. GA4側でやっておくこと
- GA4管理画面 → 「イベント」→ `sign_up`（登録）と `generate_lead`（企業の問い合わせ）を **キーイベント** にする。広告の効果を測るのに必要
- GA4 → 管理 → 「Search Consoleのリンク」でつなぐと、GA4の中で検索キーワードも見られる
