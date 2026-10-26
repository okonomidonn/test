import Link from "next/link";

const FEATURES = [
  {
    verb: "測る",
    title: "AIでの表示チェック",
    body: "見込み客がAIに聞きそうな質問をChatGPT・Geminiに投げ、あなたのサービスが紹介されたか、何番目か、どのページが引用されたかを記録します。",
  },
  {
    verb: "知る",
    title: "参考にされたページ",
    body: "AIが紹介の根拠にしたページ（公式サイト・比較記事・レビュー・SNS）を一覧にします。競合はどのページで引用されているかがわかります。",
  },
  {
    verb: "直す",
    title: "改善の提案",
    body: "公式サイトをAIが読めるか、一文で何のサービスか伝わるか、固有の情報が1件1ページになっているかを診断し、直す順番を示します。",
  },
];

const STEPS = [
  { title: "登録する", body: "サービス名・カテゴリ・強み・公式サイトを入力。質問は自動で作られます。" },
  { title: "AIに聞いて計測", body: "ボタンを押すと、AIに質問して回答を記録します（数分〜十数分）。" },
  { title: "結果を見て直す", body: "紹介されなかった質問と、サイトの直すべき点がわかります。" },
];

// AIの回答の画面イメージ。自分の名前が出たところに蛍光ペンを引く
function AnswerMock() {
  const shops = [
    { name: "大手の求人ポータル", note: "掲載数が多く、在宅で絞り込めます。ただし出社ありの求人も混ざります。" },
    { name: "あなたのサービス", note: "公式サイトに「研修も会議も出社なし」とあり、完全在宅の求人だけを掲載しています。", me: true },
    { name: "在宅ワーク専門のマッチングサービス", note: "単発の仕事が中心です。" },
  ];
  return (
    <figure className="rounded-2xl border border-slate-200 bg-white" aria-label="AIの回答の画面イメージ">
      <div className="flex items-center justify-between border-b border-slate-200 px-5 py-3 text-sm">
        <span className="font-semibold text-slate-700">ChatGPT の回答</span>
        <span className="font-num text-slate-500">質問 4 / 10</span>
      </div>
      <div className="space-y-4 px-5 py-5">
        <p className="ml-auto w-fit max-w-[85%] rounded-2xl rounded-br-sm bg-slate-100 px-4 py-2.5 text-slate-800">大学生でも、出社が一切ない長期インターンを探せるサイトは？</p>
        <div className="space-y-3 text-slate-800">
          <p>条件に合いそうなサービスを3つ紹介します。</p>
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
            {["公式サイト", "比較記事", "note"].map((src) => (
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
          <p className="font-semibold text-brand">Webサービスのための AI検索チェック</p>
          <h1 className="mt-4 text-[2rem] font-black leading-[1.3] text-slate-900 sm:text-5xl sm:leading-[1.25]">
            あなたの名前、
            <br />
            AIの答えに
            <br />
            <span className="marker">出てきますか？</span>
          </h1>
          <p className="mt-6 max-w-[34rem] text-slate-700">
            「〇〇できるサービスは？」とChatGPTやGeminiに聞いて、比べて、決める人が増えています。
            比較・検討中の見込み客に、あなたのサービスの情報が正しく届いているか。計測して、直す順番まで示します。
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
        <h2 className="text-2xl font-bold text-slate-900 sm:text-3xl">運営元の自社サイトで起きたこと</h2>
        <div className="grid gap-x-12 gap-y-8 lg:grid-cols-[1fr_1.1fr]">
          <article className="min-w-0 border-t-2 border-slate-900 pt-5">
            <p className="text-sm font-semibold text-slate-600">在宅ワークの求人サイト「zaito」｜2026年9月24日〜10月21日（GA4）</p>
            <h3 className="mt-2 text-xl font-bold text-slate-900">ChatGPT経由の68人が、キーイベントの約半分</h3>
            <p className="mt-3 text-slate-700">
              Googleではほとんど上位に出ない開設2か月のサイトです。求人を1件1ページにし、一文の説明と構造化データを整えたあと、ChatGPTからの訪問が始まりました。
              ChatGPT経由の人が見たのは、主に個別の求人ページ（46種類）でした。
            </p>
            <p className="mt-3 text-sm text-slate-500">
              サイト1件・28日間の数字です。対策と流入増の因果関係は検証していません。キーイベント（興味あり・応募など）は件数で、同じ人の複数回を含みます。
            </p>
          </article>
          <div className="min-w-0 space-y-5 border-t-2 border-slate-900 pt-5">
            <ShareBar label="ユーザー469人のうち、ChatGPT経由（68人）" value={14.5} />
            <ShareBar label="キーイベント95件のうち、ChatGPT経由（49件）" value={51.6} strong />
          </div>
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
          <p className="mt-3 text-slate-300">登録は1分。AIの回答は毎回変わるため、計測結果は目安です。紹介や問い合わせの増加をお約束するものではありません。</p>
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
