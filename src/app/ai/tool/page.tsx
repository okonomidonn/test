import Link from "next/link";
import { PLANS, formatYen } from "@/lib/billing/plans";
import { webQuestions } from "@/lib/ai-check/questions";
import { sampleReport } from "@/lib/ai-check/sample";
import { TrendLine } from "@/components/ai/report-dashboard";
import { AppWindow, btn } from "@/components/ai/ds";

export const metadata = { title: "セルフ版ツール | ススメル" };

const STEPS = [
  { title: "サービスを登録", body: "カテゴリ・対象・強み・URLを入れると、見込み客がAIに聞きそうな質問が自動でできます。質問は自由に直せます。" },
  { title: "ボタンで計測", body: "ChatGPTに質問して、紹介されたか・何番目か・どのサイトが引用されたかを記録します。1問30秒〜2分ほどです。" },
  { title: "直して、また測る", body: "サイト診断で直す場所と書き換え例を確認し、直したら測り直して推移を見ます。" },
];

const FEATURES: [string, string][] = [
  ["質問の自動作成と編集", "カテゴリと強みから10問を作成。業界の言い回しに合わせて書き換えられます"],
  ["紹介率と順位の記録", "質問ごとに、自社の順位と、代わりに紹介されたサービスを残します"],
  ["推移のグラフ", "計測するたびに紹介率を記録し、直したあとの変化を追えます"],
  ["サイト診断と書き換え例", "直す場所・今の内容・そのまま使える例を、優先度の順に出します"],
  ["報告書の出力", "社内共有や上司への報告に使える報告書を、印刷・PDFで出せます"],
];

export default function ToolPage() {
  const questions = webQuestions({ genre: "求人サイト", audience: "子育て中の人", features: ["フルリモート", "未経験OK", "週3日から"] }).slice(0, 6);
  const trend = sampleReport().trend;
  const tools = Object.values(PLANS);

  return (
    <div className="space-y-20 pt-8">
      <section className="grid items-start gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
        <div className="min-w-0 max-w-xl">
          <p className="text-sm font-semibold text-brand">セルフ版ツール</p>
          <h1 className="mt-3 text-[2rem] font-bold leading-tight text-slate-900 sm:text-[2.5rem]">
            自分のペースで、<span className="text-slate-500">何度でも測る。</span>
          </h1>
          <p className="mt-5 text-lg text-slate-600">質問づくりから計測、直す場所の確認までを、自分で回したいチーム向けです。月4,980円（税込）から、いつでも解約できます。</p>
          <div className="mt-8 flex flex-wrap gap-3">
            <Link href="/ai/signup" className={btn("primary", "lg")}>
              無料で登録する
            </Link>
            <Link href="#plans" className={btn("secondary", "lg")}>
              プランを見る
            </Link>
          </div>
          <p className="mt-3 text-sm text-slate-600">フリープランはカード登録不要です。</p>
        </div>

        <AppWindow title="ススメル — リモ求人ナビ（デモ）" badge="デモ">
          <div className="divide-y divide-slate-200">
            <div className="flex flex-wrap items-center justify-between gap-3 p-4">
              <div className="min-w-0">
                <p className="text-sm font-semibold text-slate-900">AIに聞く質問</p>
                <p className="text-sm text-slate-600">自動で作成・あとから編集できます</p>
              </div>
              <span className={btn("primary")} aria-hidden>
                AIに聞いて計測する
              </span>
            </div>
            <ol className="divide-y divide-slate-100 px-4 text-sm">
              {questions.map((q, i) => (
                <li key={q} className="flex gap-3 py-2">
                  <span className="w-5 shrink-0 font-num text-slate-400">{i + 1}</span>
                  <span className="min-w-0 text-slate-800">{q}</span>
                </li>
              ))}
            </ol>
            <div className="p-4">
              <p className="mb-2 text-sm font-semibold text-slate-900">紹介率の推移</p>
              <TrendLine points={trend} />
            </div>
          </div>
        </AppWindow>
      </section>

      <section className="space-y-8">
        <h2 className="text-2xl font-bold text-slate-900">使い方</h2>
        <ol className="grid overflow-hidden rounded-xl border border-slate-200 md:grid-cols-3">
          {STEPS.map((s, i) => (
            <li key={s.title} className={`p-5 ${i ? "border-t border-slate-200 md:border-l md:border-t-0" : ""}`}>
              <p className="font-num text-sm font-semibold text-slate-500">STEP {i + 1}</p>
              <h3 className="mt-1 font-bold text-slate-900">{s.title}</h3>
              <p className="mt-2 text-slate-700">{s.body}</p>
            </li>
          ))}
        </ol>
      </section>

      <section className="grid gap-8 lg:grid-cols-[minmax(0,18rem)_minmax(0,1fr)]">
        <h2 className="text-2xl font-bold text-slate-900">できること</h2>
        <dl className="divide-y divide-slate-200 border-y border-slate-200">
          {FEATURES.map(([k, v]) => (
            <div key={k} className="grid gap-1 py-4 sm:grid-cols-[14rem_1fr] sm:gap-6">
              <dt className="font-semibold text-slate-900">{k}</dt>
              <dd className="text-slate-700">{v}</dd>
            </div>
          ))}
        </dl>
      </section>

      <section id="plans" className="scroll-mt-24 space-y-5">
        <div>
          <h2 className="text-2xl font-bold text-slate-900">プラン</h2>
          <p className="mt-1 text-slate-600">税込です。いつでも解約でき、契約期間の縛りはありません。</p>
        </div>
        <div className="grid overflow-hidden rounded-xl border border-slate-200 md:grid-cols-3">
          {tools.map((p, i) => (
            <div key={p.key} className={`flex flex-col p-5 ${i ? "border-t border-slate-200 md:border-l md:border-t-0" : ""} ${p.key === "STARTER" ? "bg-brand-soft" : ""}`}>
              <p className={`text-sm font-semibold ${p.key === "STARTER" ? "text-brand" : "text-slate-500"}`}>{p.key === "STARTER" ? "毎週測りたい方へ" : p.key === "PRO" ? "複数のサービスを測る方へ" : "まず試したい方へ"}</p>
              <h3 className="mt-1 text-lg font-bold text-slate-900">{p.name}</h3>
              <p className="mt-2 font-num text-2xl font-semibold text-slate-900">
                {p.priceYen ? formatYen(p.priceYen) : "0円"}
                {p.priceYen > 0 && <span className="text-sm font-normal text-slate-600">／月（税込）</span>}
              </p>
              <dl className="mt-4 flex-1 space-y-1.5 border-t border-slate-200 pt-4 text-sm">
                {[
                  ["登録できるサービス", `${p.stores}件`],
                  ["計測（1件あたり）", `30日間で${p.runsPerMonth}回`],
                  ["AIに聞く質問", `${p.maxQuestions}問まで`],
                ].map(([k, v]) => (
                  <div key={k} className="flex justify-between gap-3">
                    <dt className="text-slate-600">{k}</dt>
                    <dd className="font-semibold text-slate-900">{v}</dd>
                  </div>
                ))}
              </dl>
              <Link href="/ai/signup" className={`${btn(p.key === "STARTER" ? "primary" : "secondary")} mt-5 w-full`}>
                {p.priceYen ? "登録して申し込む" : "無料で始める"}
              </Link>
            </div>
          ))}
        </div>
        <p className="text-sm text-slate-600">
          お支払いはクレジットカード（Stripe）です。毎月、申し込み日と同じ日に自動で更新されます。解約は契約管理画面からいつでもでき、その月の終わりまでご利用いただけます。日割りでの返金はありません。改善まで任せたい方は
          <Link href="/ai/pricing#managed" className="mx-1 font-semibold text-brand hover:underline">
            おまかせの料金
          </Link>
          をご覧ください。
        </p>
      </section>
    </div>
  );
}
