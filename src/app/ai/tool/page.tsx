import Link from "next/link";
import { PLANS, formatYen } from "@/lib/billing/plans";
import { webQuestions } from "@/lib/ai-check/questions";
import { sampleReport } from "@/lib/ai-check/sample";
import { AuditItem, QuestionsTable, reportKpis } from "@/components/ai/report-dashboard";
import { AppWindow, KpiStrip, btn } from "@/components/ai/ds";

export const metadata = { title: "セルフ版ツール | ススメル" };

const FACTS: [string, string][] = [
  ["計測にかかる時間", "1問30秒〜2分ほど。10問で5〜20分です。画面を閉じても計測は続きます"],
  ["専門知識", "質問は自動で作られ、直す項目には書き換え例が付きます。構造化データのコードはコピーして貼るだけです。HTMLの編集が必要な項目もあります"],
  ["無料で分かること", "フリープランで1サービス・30日に1回・10問まで。紹介率、質問ごとの結果、サイト診断、報告書を使えます"],
];

// 操作の1手順：左に説明、右に実際の画面の一部
function Step({ no, title, body, children }: { no: number; title: string; body: string; children: React.ReactNode }) {
  return (
    <li className="grid gap-5 border-t border-slate-200 py-8 lg:grid-cols-[minmax(0,18rem)_minmax(0,1fr)] lg:gap-12">
      <div className="min-w-0">
        <p className="font-num text-sm font-semibold text-slate-500">STEP {no}</p>
        <h3 className="mt-1 text-xl font-bold text-slate-900">{title}</h3>
        <p className="mt-2 text-slate-600">{body}</p>
      </div>
      <div className="min-w-0 rounded-xl border border-slate-200 bg-white p-4 sm:p-5">{children}</div>
    </li>
  );
}

export default function ToolPage() {
  const questions = webQuestions({ genre: "求人サイト", audience: "子育て中の人", features: ["フルリモート", "未経験OK", "週3日から"] }).slice(0, 5);
  const d = sampleReport();
  const tools = Object.values(PLANS);
  const fix = d.checks.find((c) => c.key === "features");

  return (
    <div className="space-y-20 pt-8">
      <section className="grid items-start gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)]">
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

        {/* 実際のアプリの画面（デモ） */}
        <AppWindow title="ススメル — リモ求人ナビ" badge="デモ">
          <div className="space-y-4 p-4 sm:p-5">
            <nav className="flex gap-4 overflow-x-auto border-b border-slate-200 text-sm" aria-hidden>
              {["AIでの紹介", "引用されたページ", "直すところ", "設定"].map((t, i) => (
                <span key={t} className={`whitespace-nowrap border-b-2 pb-2 font-semibold ${i === 0 ? "border-brand text-brand" : "border-transparent text-slate-500"}`}>
                  {t}
                </span>
              ))}
            </nav>
            <div className="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-brand/30 bg-brand-soft p-3">
              <p className="min-w-0 text-sm">
                <span className="block font-bold text-brand">次にやること</span>
                <span className="text-slate-900">サイト診断で直すとよい点が4個あります。上から順に直しましょう。</span>
              </p>
              <span className={btn("primary")} aria-hidden>
                直す点を見る
              </span>
            </div>
            <KpiStrip items={reportKpis(d).slice(0, 2)} />
            <QuestionsTable d={d} limit={3} />
          </div>
        </AppWindow>
      </section>

      <section>
        <h2 className="text-2xl font-bold text-slate-900">操作の流れ</h2>
        <ol className="mt-6">
          <Step no={1} title="サービスを登録" body="サービス名・カテゴリ・誰向けか・強み・URLを入れます。入力は1分ほどです。">
            <dl className="grid gap-3 text-sm sm:grid-cols-2">
              {[
                ["サービス名", "リモ求人ナビ"],
                ["カテゴリ", "求人サイト"],
                ["誰向けのサービスか", "子育て中の人"],
                ["強み・特徴の言葉", "フルリモート／未経験OK／週3日から"],
              ].map(([k, v]) => (
                <div key={k}>
                  <dt className="font-semibold text-slate-900">{k}</dt>
                  <dd className="mt-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-slate-800">{v}</dd>
                </div>
              ))}
            </dl>
          </Step>
          <Step no={2} title="質問を確認" body="見込み客がAIに聞きそうな質問が自動でできます。業界の言い回しに合わせて書き換えられます。">
            <ol className="divide-y divide-slate-100 text-sm">
              {questions.map((q, i) => (
                <li key={q} className="flex gap-3 py-2">
                  <span className="w-5 shrink-0 font-num text-slate-400">{i + 1}</span>
                  <span className="min-w-0 text-slate-800">{q}</span>
                </li>
              ))}
            </ol>
          </Step>
          <Step no={3} title="ボタンで計測" body="ChatGPTに1問ずつ聞いて、紹介されたか・何番目か・どのサイトが引用されたかを記録します。">
            <div className="space-y-2" aria-label="計測中の画面（デモ）">
              <div className="flex items-center justify-between text-sm">
                <span className="font-semibold text-slate-900">AIに質問中… 6 / 10</span>
                <span className="font-num text-slate-600">60%</span>
              </div>
              <div className="h-2 overflow-hidden rounded-full bg-slate-100">
                <div className="h-2 w-[60%] rounded-full bg-brand" />
              </div>
              <p className="text-sm text-slate-600">このページを開いたままにすると早く終わります。閉じても、残りは自動で続きます。終わると結果の画面に戻ります。</p>
            </div>
          </Step>
          <Step no={4} title="直して、また測る" body="直す場所・書き換え例・事前に確かめることを確認して直し、同じ質問で測り直します。">
            {fix ? (
              <ul>
                <AuditItem c={fix} />
              </ul>
            ) : null}
          </Step>
        </ol>
      </section>

      <section className="grid gap-8 lg:grid-cols-[minmax(0,18rem)_minmax(0,1fr)]">
        <h2 className="text-2xl font-bold text-slate-900">よく聞かれること</h2>
        <dl className="divide-y divide-slate-200 border-y border-slate-200">
          {FACTS.map(([k, v]) => (
            <div key={k} className="grid gap-1 py-4 sm:grid-cols-[11rem_1fr] sm:gap-6">
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
