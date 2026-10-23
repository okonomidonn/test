import Link from "next/link";

const FEATURES = [
  {
    verb: "測る",
    title: "AIでの表示チェック",
    body: "お客さんがAIに聞きそうな質問をChatGPT・Geminiに投げ、あなたのお店やサービスが何回・何番目に紹介されたかを記録します。",
  },
  {
    verb: "知る",
    title: "参考にされたページ",
    body: "AIが紹介の根拠にしたページ（公式サイト・グルメサイト・比較記事・レビュー）を一覧にします。どこを整えれば効くかがわかります。",
  },
  {
    verb: "直す",
    title: "改善の提案",
    body: "公式サイトがAIに伝わる書き方かを診断します。お店なら営業時間やメニュー、Webサービスなら一文の説明や1件1ページの情報を確認します。",
  },
];

const STEPS = [
  { title: "登録する", body: "お店かWebサービスかを選び、名前と公式サイトを入力。質問は自動で作られます。" },
  { title: "AIに聞いて計測", body: "ボタンを押すと、AIに質問して回答を記録します（数分〜十数分）。" },
  { title: "結果を見て直す", body: "紹介されなかった質問と、サイトの直すべき点がわかります。" },
];

// AIの回答の画面イメージ。自分の名前が出たところに蛍光ペンを引く
function AnswerMock() {
  const shops = [
    { name: "駅前の焼き鳥酒場", note: "カウンター中心で、仕事帰りに立ち寄りやすいお店です。" },
    { name: "あなたのお店", note: "公式サイトに「カウンターは一人飲みに」とあり、店主との会話も楽しめます。", me: true },
    { name: "路地裏の小料理屋", note: "季節の地野菜を使った料理が評判です。" },
  ];
  return (
    <figure className="rounded-2xl border border-slate-200 bg-white" aria-label="AIの回答の画面イメージ">
      <div className="flex items-center justify-between border-b border-slate-200 px-5 py-3 text-sm">
        <span className="font-semibold text-slate-700">ChatGPT の回答</span>
        <span className="font-num text-slate-500">質問 4 / 10</span>
      </div>
      <div className="space-y-4 px-5 py-5">
        <p className="ml-auto w-fit max-w-[85%] rounded-2xl rounded-br-sm bg-slate-100 px-4 py-2.5 text-slate-800">国立駅の近くで、一人飲みにおすすめの店は？</p>
        <div className="space-y-3 text-slate-800">
          <p>一人でも入りやすいお店を3つ紹介します。</p>
          <ol className="space-y-2.5">
            {shops.map((s, i) => (
              <li key={s.name} className="grid grid-cols-[1.75rem_1fr] gap-1">
                <span className="font-num font-semibold text-slate-500">{i + 1}.</span>
                <span>
                  <b className={s.me ? "marker" : undefined}>{s.name}</b>
                  {s.me && <span className="ml-2 rounded-full bg-slate-900 px-2 py-0.5 align-middle text-xs font-bold text-white">2位で紹介</span>}
                  <span className="block text-sm text-slate-600">{s.note}</span>
                </span>
              </li>
            ))}
          </ol>
          <p className="flex flex-wrap items-center gap-1.5 text-sm text-slate-600">
            参考：
            {["公式サイト", "食べログ", "Googleマップ"].map((src) => (
              <span key={src} className="rounded-md border border-slate-200 px-1.5 py-0.5">
                {src}
              </span>
            ))}
          </p>
        </div>
      </div>
      <figcaption className="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 bg-slate-50 px-5 py-3 text-sm text-slate-600">
        <span>
          この質問：<span className="font-num font-semibold text-slate-900">3回中3回</span> 紹介
        </span>
        <span>※ 画面イメージ</span>
      </figcaption>
    </figure>
  );
}

// 同じ条件のお店で、書いてあるかどうかだけが違う比較（●＝紹介された回）
function DotsRow({ label, hits, total }: { label: string; hits: number; total: number }) {
  return (
    <div className="flex items-center justify-between gap-4 py-3">
      <span className="text-slate-800">{label}</span>
      <span className="flex items-center gap-3">
        <span className="flex gap-1.5" aria-hidden>
          {Array.from({ length: total }, (_, i) => (
            <span key={i} className={`h-3.5 w-3.5 rounded-full ${i < hits ? "bg-brand" : "border-2 border-slate-300"}`} />
          ))}
        </span>
        <span className="w-12 text-right font-num font-semibold text-slate-900">
          {hits}/{total}
        </span>
      </span>
    </div>
  );
}

// 0〜100%の同じ目盛りで、2本の割合を並べる
function ShareBar({ label, value, strong }: { label: string; value: number; strong?: boolean }) {
  return (
    <div className="space-y-1.5" title={`${label}：${value}%`}>
      <div className="flex items-baseline justify-between gap-3">
        <span className="text-slate-800">{label}</span>
        <span className={`font-num text-2xl font-semibold ${strong ? "text-brand" : "text-slate-700"}`}>{value}%</span>
      </div>
      <div className="h-3 rounded-full bg-slate-100">
        <div className={`h-3 rounded-full ${strong ? "bg-brand" : "bg-slate-400"}`} style={{ width: `${value}%` }} />
      </div>
    </div>
  );
}

export default function AiLandingPage() {
  return (
    <div className="space-y-24 pb-12">
      <section className="grid items-center gap-12 pt-6 lg:grid-cols-[1.05fr_1fr]">
        <div className="min-w-0">
          <p className="font-semibold text-brand">お店・Webサービスのための AI検索チェック</p>
          <h1 className="mt-4 text-[2rem] font-black leading-[1.3] text-slate-900 sm:text-5xl sm:leading-[1.25]">
            あなたの名前、
            <br />
            AIの答えに
            <br />
            <span className="marker">出てきますか？</span>
          </h1>
          <p className="mt-6 max-w-[34rem] text-slate-700">
            「近くでおすすめの居酒屋は？」「〇〇できるサービスは？」とChatGPTやGeminiに聞いて決める人が増えています。
            AIが答えるのは数件だけ。紹介されているか、何を直せば紹介されやすくなるかを、無料で確かめられます。
          </p>
          <div className="mt-8 flex flex-wrap items-center gap-3">
            <Link href="/ai/signup" className="rounded-lg bg-brand px-6 py-3.5 text-lg font-bold text-white hover:bg-brand-dark">
              無料で診断する
            </Link>
            <Link href="/ai/login" className="rounded-lg px-4 py-3.5 text-lg font-semibold text-slate-800 underline decoration-slate-300 underline-offset-4 hover:decoration-slate-800">
              ログイン
            </Link>
          </div>
          <p className="mt-3 text-sm text-slate-600">カード登録は不要です。有料プランは月4,980円から、いつでも解約できます。</p>
        </div>
        <AnswerMock />
      </section>

      <section className="space-y-8">
        <h2 className="text-2xl font-bold text-slate-900 sm:text-3xl">実際に測ってわかったこと</h2>
        <div className="grid gap-x-12 gap-y-12 lg:grid-cols-2">
          <article className="min-w-0 border-t-2 border-slate-900 pt-5">
            <p className="text-sm font-semibold text-slate-600">飲食店｜東京都国立市の居酒屋、ChatGPTに22回質問</p>
            <h3 className="mt-2 text-xl font-bold text-slate-900">書いてあるお店が、紹介される</h3>
            <p className="mt-3 text-slate-700">「一人飲みにおすすめの店は？」と聞いたときに紹介された回数です。どちらもカウンター席があるお店です。</p>
            <div className="mt-4 divide-y divide-slate-200 border-y border-slate-200">
              <DotsRow label="公式サイトに「一人飲みに」と書いてある" hits={3} total={3} />
              <DotsRow label="書いていない" hits={0} total={3} />
            </div>
          </article>
          <article className="min-w-0 border-t-2 border-slate-900 pt-5">
            <p className="text-sm font-semibold text-slate-600">Webサービス｜運営元の求人サイト「zaito」、28日間</p>
            <h3 className="mt-2 text-xl font-bold text-slate-900">ChatGPT経由の人が、成果の半分</h3>
            <p className="mt-3 text-slate-700">求人を1件1ページにし、一文の説明と構造化データを整えたところ、Googleではほとんど出てこないのに、ChatGPTから人が来て応募につながるようになりました。</p>
            <div className="mt-5 space-y-4">
              <ShareBar label="ユーザーのうち、ChatGPT経由" value={14.5} />
              <ShareBar label="成果（応募など）のうち、ChatGPT経由" value={51.6} strong />
            </div>
            <p className="mt-3 text-sm text-slate-500">GA4・2026年9月24日〜10月21日。開設まもないサイト1件の数字です。</p>
          </article>
        </div>
      </section>

      <section className="space-y-8">
        <h2 className="text-2xl font-bold text-slate-900 sm:text-3xl">できること</h2>
        <div className="grid gap-8 md:grid-cols-3 md:gap-0 md:divide-x md:divide-slate-200">
          {FEATURES.map((f) => (
            <div key={f.title} className="min-w-0 md:px-8 md:first:pl-0 md:last:pr-0">
              <p className="font-display text-4xl font-black text-brand">{f.verb}</p>
              <h3 className="mt-3 text-lg font-bold text-slate-900">{f.title}</h3>
              <p className="mt-2 text-slate-700">{f.body}</p>
            </div>
          ))}
        </div>
      </section>

      <section className="space-y-8">
        <h2 className="text-2xl font-bold text-slate-900 sm:text-3xl">使い方</h2>
        <ol className="grid gap-6 md:grid-cols-3">
          {STEPS.map((s, i) => (
            <li key={s.title} className="min-w-0">
              <div className="flex items-center gap-3">
                <span className="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-slate-900 font-num text-lg font-semibold text-white">{i + 1}</span>
                {i < STEPS.length - 1 && <span className="hidden h-px flex-1 bg-slate-300 md:block" aria-hidden />}
              </div>
              <h3 className="mt-4 text-lg font-bold text-slate-900">{s.title}</h3>
              <p className="mt-1 text-slate-700">{s.body}</p>
            </li>
          ))}
        </ol>
      </section>

      <section className="rounded-2xl bg-slate-900 px-6 py-12 text-white sm:px-12">
        <div className="max-w-2xl">
          <h2 className="text-2xl font-bold sm:text-3xl">
            まずは1件、<span className="text-mark">無料で計測</span>してみませんか
          </h2>
          <p className="mt-3 text-slate-300">登録は1分。AIの回答は毎回変わるため、計測結果は目安です。紹介や来店・問い合わせの増加をお約束するものではありません。</p>
          <div className="mt-7 flex flex-wrap gap-3">
            <Link href="/ai/signup" className="rounded-lg bg-mark px-6 py-3.5 text-lg font-bold text-slate-900 hover:brightness-95">
              無料で始める
            </Link>
            <Link href="/ai/pricing" className="rounded-lg border border-slate-600 px-6 py-3.5 text-lg font-semibold text-white hover:bg-slate-800">
              料金を見る
            </Link>
          </div>
        </div>
      </section>
    </div>
  );
}
