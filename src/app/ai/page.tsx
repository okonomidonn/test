import Link from "next/link";
import { btn } from "@/components/ai/ds";
import { ServicePlans } from "@/components/ai/service-plans";

const RESULTS = [
  ["どんな質問で紹介されるか", "見込み客が聞きそうな質問で、自社がAIの回答に出てくるかを調べます。", "例：未経験から使える求人サイトは？"],
  ["代わりに選ばれている競合", "トライアルでは競合2社と比較し、自社との紹介回数や引用元の違いを確認します。", "有料トライアルで競合2社を比較"],
  ["最初に直す3つのこと", "情報が足りないページと、書き足す内容を優先順位つきでお伝えします。", "直すページと文章の案まで"],
];
const FLOW = [
  ["サイトのURLを送る", "会社・サービス名と、ご連絡先を入力。アカウント登録は不要です。"],
  ["ススメルが調べる", "AIでの紹介状況と、公開されているサイトの情報を確認します。"],
  ["結果がメールで届く", "3営業日以内に、紹介状況と最初に直す3つのことをお送りします。"],
];

export default function AiLandingPage() {
  return (
    <div className="space-y-16 pb-6 sm:space-y-24">
      <section className="grid items-start gap-8 pt-2 sm:pt-8 lg:grid-cols-[1.1fr_1fr] lg:gap-12">
        <div className="min-w-0 lg:py-6">
          <p className="inline-flex rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-800">WebサービスのAI検索診断</p>
          <h1 className="mt-5 text-[clamp(1.5rem,7vw,3rem)] font-bold leading-[1.4] tracking-tight text-slate-900 sm:leading-[1.35]">
            <span className="block">AIでの紹介状況と、</span>
            <span className="block text-brand">直すページが分かる。</span>
          </h1>
          <p className="mt-5 max-w-xl text-base leading-relaxed text-slate-700 sm:text-lg">ChatGPT・Geminiで自社がどう紹介されるかを調査。競合との違いを確かめ、サイトのどこを、どう直すかをご提案します。</p>
          <div className="mt-7">
            <Link href="/ai/apply" className={`${btn("primary", "lg")} w-full sm:w-auto`}>まずは無料でチェックする <span aria-hidden>→</span></Link>
            <p className="mt-3 text-sm text-slate-600">登録不要・費用0円・3営業日以内にメールでお届け</p>
          </div>
        </div>
        <div className="min-w-0 rounded-2xl bg-[#edf3fc] p-4 sm:p-6">
          <div className="mb-3 flex flex-wrap items-center justify-between gap-2 text-sm">
            <p className="font-bold text-slate-900">診断レポートの見本</p>
            <span className="rounded bg-white px-2 py-0.5 text-xs text-slate-600">トライアルの例</span>
          </div>
          <div className="rounded-xl border border-blue-100 bg-white p-5 sm:p-6">
            <p className="text-sm font-semibold text-slate-600">10回答中、紹介された回数</p>
            <p className="mt-2 text-xl font-bold leading-snug text-slate-900"><span className="inline-block">自社は4回。</span><span className="inline-block">競合は7回。</span></p>
            <dl className="mt-5 space-y-4">
              {[{ name: "自社", count: 4, color: "bg-brand" }, { name: "競合の例", count: 7, color: "bg-slate-300" }].map((r) => <div key={r.name}>
                <div className="mb-1.5 flex items-baseline justify-between text-sm"><dt className="font-semibold text-slate-700">{r.name}</dt><dd className="font-num text-slate-600"><strong className="text-xl text-slate-900">{r.count}</strong> / 10回</dd></div>
                <div className="h-3 overflow-hidden rounded-full bg-slate-100"><div className={`h-full rounded-full ${r.color}`} style={{ width: `${r.count * 10}%` }} /></div>
              </div>)}
            </dl>
            <div className="mt-6 border-t border-slate-200 pt-4">
              <p className="text-sm font-semibold text-brand">最初に直すことの例</p>
              <p className="mt-1 font-bold text-slate-900">トップページに「誰のためのサービスか」を書く</p>
            </div>
          </div>
          <Link href="/ai/sample" className="mt-4 inline-flex min-h-10 items-center text-sm font-semibold text-brand underline underline-offset-4">詳しいレポートの見本を見る <span aria-hidden className="ml-2">→</span></Link>
          <p className="mt-1 text-sm text-slate-600">数値は架空の例です。無料チェックは紹介状況と改善点を、トライアルは競合比較までお届けします。</p>
        </div>
      </section>
      <section id="features" className="scroll-mt-24">
        <p className="text-sm font-semibold text-brand">診断で分かること</p>
        <h2 className="mt-2 text-2xl font-bold leading-snug text-slate-900 sm:text-3xl">数字を調べて終わりにせず、<br className="hidden sm:block" />次にやることを明確にします。</h2>
        <div className="mt-7 grid overflow-hidden rounded-xl border border-slate-200 md:grid-cols-3">
          {RESULTS.map(([title, body], i) => <div key={title} className={`bg-white p-5 sm:p-6 ${i ? "border-t border-slate-200 md:border-l md:border-t-0" : ""}`}>
            <p className="font-num text-sm font-semibold text-brand">0{i + 1}</p><h3 className="mt-2 text-lg font-bold text-slate-900">{title}</h3><p className="mt-3 text-slate-700">{body}</p>
          </div>)}
        </div>
      </section>
      <section id="flow" className="scroll-mt-24 rounded-2xl bg-slate-50 p-5 sm:p-8">
        <p className="text-sm font-semibold text-brand">ご利用の流れ</p>
        <h2 className="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">あなたがするのは、URLを送るだけ。</h2>
        <p className="mt-3 text-slate-700">診断はススメルが行います。ツールを覚えたり、自分で計測したりする必要はありません。</p>
        <ol className="mt-7 grid gap-6 md:grid-cols-3">
          {FLOW.map(([title, body], i) => <li key={title} className="flex gap-3"><span className="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-white font-num font-bold text-brand ring-1 ring-slate-200">{i + 1}</span><div><h3 className="font-bold text-slate-900">{title}</h3><p className="mt-2 text-sm leading-relaxed text-slate-700">{body}</p></div></li>)}
        </ol>
      </section>
      <section>
        <p className="text-sm font-semibold text-brand">料金とサポート</p>
        <h2 className="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">まず無料で現状を知る。改善の依頼はそのあとで。</h2>
        <p className="mt-3 text-slate-700">無料チェックだけでもご利用いただけます。有料プランへの自動切り替えはありません。</p>
        <div className="mt-7"><ServicePlans compact /></div>
        <Link href="/ai/pricing" className="mt-5 inline-flex min-h-11 items-center font-semibold text-brand underline underline-offset-4">料金と作業内容を詳しく見る <span aria-hidden className="ml-2">→</span></Link>
      </section>
      <section className="mx-auto max-w-3xl">
        <h2 className="text-2xl font-bold text-slate-900">申し込む前に、気になること</h2>
        <div className="mt-5 divide-y divide-slate-200 border-y border-slate-200">
          {[
            ["どんなサービスが対象ですか？", "求人・人材サービス、スクール、SaaSなど、Webサイトで問い合わせや申し込みを受け付けているサービスが対象です。"],
            ["無料チェックのあと、契約しないといけませんか？", "いいえ。結果を見てからご検討ください。フォーム送信だけで有料契約や自動課金は発生しません。"],
            ["改善すれば、必ずAIに紹介されますか？", "紹介や問い合わせの増加は保証していません。AIの回答は毎回変わるため、同じ条件で計測し、変化を確かめます。"],
          ].map(([q, a]) => <details key={q} className="group py-1"><summary className="flex cursor-pointer list-none items-start justify-between gap-4 py-4 font-semibold text-slate-900">{q}<span aria-hidden className="shrink-0 text-brand group-open:hidden">＋</span><span aria-hidden className="hidden shrink-0 text-brand group-open:inline">−</span></summary><p className="pb-5 leading-relaxed text-slate-700">{a}</p></details>)}
        </div>
        <Link href="/ai/method" className="mt-4 inline-flex min-h-11 items-center text-sm font-semibold text-brand underline underline-offset-4">計測方法・できることと限界を確認する →</Link>
      </section>
      <section className="rounded-2xl bg-[#142b50] px-5 py-9 text-center text-white sm:px-8 sm:py-12">
        <h2 className="text-2xl font-bold leading-snug sm:text-3xl">まずは、今の状態を知るところから。</h2>
        <p className="mt-3 text-blue-100">自社のURLを送ると、3営業日以内に診断結果が届きます。</p>
        <Link href="/ai/apply" className={`${btn("secondary", "lg")} mt-6 w-full sm:w-auto`}>無料チェックを依頼する <span aria-hidden>→</span></Link>
      </section>
    </div>
  );
}
