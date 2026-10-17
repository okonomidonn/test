import Link from "next/link";

const FEATURES = [
  {
    title: "AIでの表示チェック",
    body: "「駅周辺でおすすめの居酒屋は？」など、お客さんが実際にAIに聞きそうな質問をChatGPT・Geminiに投げ、お店が何回・何番目に紹介されたかを計測します。",
  },
  {
    title: "参考にされたページ",
    body: "AIが紹介の根拠にしたサイト（公式サイト・グルメサイト・まとめ記事など）を一覧にします。どこを整えれば効くのかがわかります。",
  },
  {
    title: "改善の提案",
    body: "公式サイトを読み取り、営業時間や定休日の記載、メニューの書き方、「一人飲み」「デート」など利用シーンの記述が足りているかを診断します。",
  },
];

const STEPS = [
  { no: "1", title: "お店を登録", body: "店名・エリア・業態・公式サイトを入力するだけ。質問は自動で作られます。" },
  { no: "2", title: "AIに聞いて計測", body: "ボタンを押すと、AIに質問して回答を記録します（数分〜十数分）。" },
  { no: "3", title: "結果を見て直す", body: "紹介されなかった質問と、サイトの直すべき点がわかります。" },
];

function Preview() {
  const rows = [
    { q: "駅近くで一人飲みにおすすめの店は？", v: "1位", hit: true },
    { q: "焼き鳥がおいしいお店は？", v: "3位", hit: true },
    { q: "地元の食材を使った料理が食べられる店は？", v: "紹介なし", hit: false },
    { q: "デートにおすすめの居酒屋は？", v: "紹介なし", hit: false },
  ];
  return (
    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl" aria-label="画面イメージ">
      <div className="grid grid-cols-3 border-b border-slate-200 text-center text-sm font-semibold">
        <span className="border-b-2 border-brand py-3 text-brand">AIでの表示チェック</span>
        <span className="py-3 text-slate-400">参考にされたページ</span>
        <span className="py-3 text-slate-400">改善の提案</span>
      </div>
      <div className="space-y-4 p-5">
        <div className="grid grid-cols-3 gap-3">
          {[
            ["推薦率", "30%"],
            ["ChatGPT", "3 / 10"],
            ["Gemini", "3 / 10"],
          ].map(([k, v]) => (
            <div key={k} className="rounded-lg bg-slate-50 p-3">
              <p className="text-xs text-slate-500">{k}</p>
              <p className="text-xl font-bold text-slate-900">{v}</p>
            </div>
          ))}
        </div>
        <ul className="divide-y divide-slate-100 text-sm">
          {rows.map((r) => (
            <li key={r.q} className="flex items-center justify-between gap-3 py-2">
              <span className="text-slate-700">{r.q}</span>
              <span className={`shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold ${r.hit ? "bg-emerald-50 text-emerald-700" : "bg-slate-100 text-slate-500"}`}>
                {r.v}
              </span>
            </li>
          ))}
        </ul>
        <p className="text-right text-xs text-slate-400">※ 画面イメージ（数値は例です）</p>
      </div>
    </div>
  );
}

export default function AiLandingPage() {
  return (
    <div className="space-y-20 pb-12">
      <section className="grid items-center gap-10 pt-6 lg:grid-cols-2">
        <div>
          <p className="text-sm font-semibold text-brand">飲食店のためのAI検索チェック</p>
          <h1 className="mt-3 text-4xl font-bold leading-tight text-slate-900 sm:text-5xl lg:text-[2.75rem]">
            あなたのお店、
            <br />
            AIに紹介されていますか？
          </h1>
          <p className="mt-5 text-base leading-relaxed text-slate-600">
            「近くでおすすめの居酒屋は？」とChatGPTやGeminiに聞いてお店を決める人が増えています。
            AIが答えるのは数店だけ。お店が紹介されているか、何を直せば紹介されやすくなるかを、無料で確かめられます。
          </p>
          <div className="mt-8 flex flex-wrap gap-3">
            <Link href="/ai/signup" className="rounded-lg bg-brand px-6 py-3 font-semibold text-white shadow-sm hover:bg-brand-dark">
              無料でお店を診断する
            </Link>
            <Link href="/ai/login" className="rounded-lg border border-slate-200 bg-white px-6 py-3 font-semibold text-slate-700 hover:bg-slate-50">
              ログイン
            </Link>
          </div>
          <p className="mt-3 text-xs text-slate-500">カード登録不要・自動課金なし（β版）</p>
        </div>
        <Preview />
      </section>

      <section>
        <h2 className="text-2xl font-bold text-slate-900">できること</h2>
        <div className="mt-6 grid gap-4 md:grid-cols-3">
          {FEATURES.map((f) => (
            <div key={f.title} className="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
              <h3 className="font-bold text-slate-900">{f.title}</h3>
              <p className="mt-2 text-sm leading-relaxed text-slate-600">{f.body}</p>
            </div>
          ))}
        </div>
      </section>

      <section className="rounded-2xl bg-white p-8 ring-1 ring-slate-200">
        <h2 className="text-2xl font-bold text-slate-900">実際に測ってわかったこと</h2>
        <p className="mt-3 max-w-3xl text-sm leading-relaxed text-slate-600">
          東京都国立市の居酒屋についてChatGPTに22回質問したところ、紹介されたお店の多くで「そのお店の公式サイトの文章」が紹介理由として使われていました。
          「一人飲みにおすすめの店は？」という質問では、公式サイトに「カウンターは一人飲みに」と書いてあるお店が3回中3回紹介され、同じくカウンターがあっても書いていないお店は0回でした。
          AIは、書いてあることしか知りません。
        </p>
      </section>

      <section>
        <h2 className="text-2xl font-bold text-slate-900">使い方</h2>
        <ol className="mt-6 grid gap-4 md:grid-cols-3">
          {STEPS.map((s) => (
            <li key={s.no} className="rounded-xl border border-slate-200 bg-white p-6">
              <span className="grid h-9 w-9 place-items-center rounded-full bg-indigo-50 font-bold text-brand">{s.no}</span>
              <h3 className="mt-3 font-bold text-slate-900">{s.title}</h3>
              <p className="mt-1 text-sm text-slate-600">{s.body}</p>
            </li>
          ))}
        </ol>
      </section>

      <section className="rounded-2xl bg-slate-900 p-10 text-center text-white">
        <h2 className="text-2xl font-bold">まずは1店舗、無料で計測してみませんか</h2>
        <p className="mt-2 text-sm text-slate-300">登録は1分。AIの回答は毎回変わるため、計測結果は目安です。紹介や来店の増加をお約束するものではありません。</p>
        <Link href="/ai/signup" className="mt-6 inline-block rounded-lg bg-white px-6 py-3 font-semibold text-slate-900 hover:bg-slate-100">
          無料で始める
        </Link>
      </section>
    </div>
  );
}
