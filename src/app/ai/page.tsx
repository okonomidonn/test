import Link from "next/link";

const FEATURES = [
  {
    title: "AIでの表示チェック",
    body: "お客さんが実際にAIに聞きそうな質問をChatGPT・Geminiに投げ、あなたのお店やサービスが何回・何番目に紹介されたかを計測します。",
  },
  {
    title: "参考にされたページ",
    body: "AIが紹介の根拠にしたページ（公式サイト・グルメサイト・比較記事・レビューなど）を一覧にします。どこを整えれば効くのかがわかります。",
  },
  {
    title: "改善の提案",
    body: "公式サイトを読み取り、AIに伝わる書き方になっているかを診断します。お店なら営業時間やメニュー、Webサービスなら一文の説明や1件1ページの情報などを確認します。",
  },
];

const STEPS = [
  { no: "1", title: "登録する", body: "お店かWebサービスかを選び、名前と公式サイトを入力するだけ。質問は自動で作られます。" },
  { no: "2", title: "AIに聞いて計測", body: "ボタンを押すと、AIに質問して回答を記録します（数分〜十数分）。" },
  { no: "3", title: "結果を見て直す", body: "紹介されなかった質問と、サイトの直すべき点がわかります。" },
];

function Preview() {
  const rows = [
    { q: "駅近くで一人飲みにおすすめの店は？", v: "1位", hit: true },
    { q: "未経験OKの在宅バイトの求人サイトは？", v: "2位", hit: true },
    { q: "デートにおすすめの居酒屋は？", v: "紹介なし", hit: false },
    { q: "大学生向けの長期インターンの求人サイトは？", v: "紹介なし", hit: false },
  ];
  return (
    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl" aria-label="画面イメージ">
      <div className="grid grid-cols-3 border-b border-slate-200 text-center text-sm font-semibold">
        <span className="border-b-2 border-brand py-3 text-brand">表示チェック</span>
        <span className="py-3 text-slate-500">参考ページ</span>
        <span className="py-3 text-slate-500">改善の提案</span>
      </div>
      <div className="space-y-4 p-5">
        <div className="grid grid-cols-3 gap-3">
          {[
            ["推薦率", "30%"],
            ["ChatGPT", "3 / 10"],
            ["Gemini", "3 / 10"],
          ].map(([k, v]) => (
            <div key={k} className="rounded-lg bg-slate-50 p-3">
              <p className="text-sm text-slate-600">{k}</p>
              <p className="text-xl font-bold text-slate-900">{v}</p>
            </div>
          ))}
        </div>
        <ul className="divide-y divide-slate-100 text-sm">
          {rows.map((r) => (
            <li key={r.q} className="flex items-center justify-between gap-3 py-2">
              <span className="text-slate-800">{r.q}</span>
              <span className={`shrink-0 rounded-full px-2.5 py-0.5 text-sm font-semibold ${r.hit ? "bg-emerald-50 text-emerald-700" : "bg-slate-100 text-slate-600"}`}>
                {r.v}
              </span>
            </li>
          ))}
        </ul>
        <p className="text-right text-sm text-slate-500">※ 画面イメージ（数値は例です）</p>
      </div>
    </div>
  );
}

export default function AiLandingPage() {
  return (
    <div className="space-y-20 pb-12">
      <section className="grid items-center gap-10 pt-6 lg:grid-cols-2">
        <div>
          <p className="font-semibold text-brand">お店・Webサービスのための AI検索チェック</p>
          <h1 className="mt-3 text-3xl font-bold leading-tight text-slate-900 sm:text-5xl lg:text-[2.75rem]">
            あなたのお店やサービス、
            <br />
            AIに紹介されていますか？
          </h1>
          <p className="mt-5 text-base leading-relaxed text-slate-700">
            「近くでおすすめの居酒屋は？」「〇〇できるサービスは？」とChatGPTやGeminiに聞いて決める人が増えています。
            AIが答えるのは数件だけ。紹介されているか、何を直せば紹介されやすくなるかを、無料で確かめられます。
          </p>
          <div className="mt-8 flex flex-wrap gap-3">
            <Link href="/ai/signup" className="rounded-lg bg-brand px-6 py-3.5 text-lg font-semibold text-white shadow-sm hover:bg-brand-dark">
              無料で診断する
            </Link>
            <Link href="/ai/login" className="rounded-lg border border-slate-300 bg-white px-6 py-3.5 text-lg font-semibold text-slate-800 hover:bg-slate-50">
              ログイン
            </Link>
          </div>
          <p className="mt-3 text-sm text-slate-600">カード登録は不要です。有料プランは月4,980円から、いつでも解約できます。</p>
        </div>
        <Preview />
      </section>

      <section>
        <h2 className="text-2xl font-bold text-slate-900">実際に測ってわかったこと</h2>
        <div className="mt-6 grid gap-4 md:grid-cols-2">
          <div className="rounded-2xl bg-white p-7 ring-1 ring-slate-200">
            <p className="font-semibold text-brand">飲食店</p>
            <h3 className="mt-1 text-lg font-bold text-slate-900">書いてあるお店が、紹介される</h3>
            <p className="mt-3 leading-relaxed text-slate-700">
              東京都国立市の居酒屋についてChatGPTに22回質問したところ、「一人飲みにおすすめの店は？」では、公式サイトに「カウンターは一人飲みに」と書いてあるお店が3回中3回紹介されました。
              同じくカウンターがあっても、書いていないお店は0回でした。
            </p>
          </div>
          <div className="rounded-2xl bg-white p-7 ring-1 ring-slate-200">
            <p className="font-semibold text-brand">Webサービス（運営元の自社サイト）</p>
            <h3 className="mt-1 text-lg font-bold text-slate-900">ChatGPT経由のユーザーが、成果の半分</h3>
            <div className="mt-4 grid grid-cols-2 gap-3">
              <div className="rounded-xl bg-slate-50 p-4">
                <p className="text-sm text-slate-600">ユーザーに占める割合</p>
                <p className="text-3xl font-bold text-slate-900">14.5%</p>
              </div>
              <div className="rounded-xl bg-emerald-50 p-4">
                <p className="text-sm text-emerald-800">成果に占める割合</p>
                <p className="text-3xl font-bold text-emerald-700">51.6%</p>
              </div>
            </div>
            <p className="mt-3 leading-relaxed text-slate-700">
              在宅ワークの求人サイト「zaito」。求人を1件1ページにし、一文の説明と構造化データを整えたところ、Googleではほとんど出てこないのに、ChatGPTから人が来て応募につながるようになりました。
            </p>
            <p className="mt-2 text-sm text-slate-500">GA4・2026年9月24日〜10月21日。開設まもないサイト1件・28日間の数字です。</p>
          </div>
        </div>
      </section>

      <section>
        <h2 className="text-2xl font-bold text-slate-900">できること</h2>
        <div className="mt-6 grid gap-4 md:grid-cols-3">
          {FEATURES.map((f) => (
            <div key={f.title} className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
              <h3 className="text-lg font-bold text-slate-900">{f.title}</h3>
              <p className="mt-2 leading-relaxed text-slate-700">{f.body}</p>
            </div>
          ))}
        </div>
      </section>

      <section>
        <h2 className="text-2xl font-bold text-slate-900">使い方</h2>
        <ol className="mt-6 grid gap-4 md:grid-cols-3">
          {STEPS.map((s) => (
            <li key={s.no} className="rounded-xl border border-slate-200 bg-white p-6">
              <span className="grid h-10 w-10 place-items-center rounded-full bg-indigo-50 text-lg font-bold text-brand">{s.no}</span>
              <h3 className="mt-3 text-lg font-bold text-slate-900">{s.title}</h3>
              <p className="mt-1 text-slate-700">{s.body}</p>
            </li>
          ))}
        </ol>
      </section>

      <section className="rounded-2xl bg-slate-900 p-10 text-center text-white">
        <h2 className="text-2xl font-bold">まずは1件、無料で計測してみませんか</h2>
        <p className="mt-2 text-slate-300">登録は1分。AIの回答は毎回変わるため、計測結果は目安です。紹介や来店・問い合わせの増加をお約束するものではありません。</p>
        <div className="mt-6 flex flex-wrap justify-center gap-3">
          <Link href="/ai/signup" className="rounded-lg bg-white px-6 py-3.5 text-lg font-semibold text-slate-900 hover:bg-slate-100">
            無料で始める
          </Link>
          <Link href="/ai/pricing" className="rounded-lg border border-slate-600 px-6 py-3.5 text-lg font-semibold text-white hover:bg-slate-800">
            料金を見る
          </Link>
        </div>
      </section>
    </div>
  );
}
