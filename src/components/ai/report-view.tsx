import type { AuditCheck } from "@/lib/ai-check/audit";
import type { PagePlan } from "@/lib/ai-check/suggest";
import { FixList, PagePlans } from "@/components/ai/fix-list";

// 報告書の中身。ログイン後の実際の報告書と、紹介ページのサンプルの両方で使う（見本と実物を一致させるため）
export type ReportData = {
  name: string;
  url: string | null;
  createdAt: Date;
  measured: { hits: number; total: number; at: Date } | null;
  score: number | null;
  questions: { no: number; text: string; hits: number; total: number; ranks: number[] }[];
  others: { label: string; count: number; registered: boolean }[];
  sources: { domain: string; count: number; official: boolean }[];
  checks: AuditCheck[];
  plans: PagePlan[];
};

const fmtDay = (d: Date) => new Intl.DateTimeFormat("ja-JP", { timeZone: "Asia/Tokyo", year: "numeric", month: "long", day: "numeric" }).format(d);
const pct = (hits: number, total: number) => (total ? Math.round((hits / total) * 100) : 0);

// 重い順（直す → 直すとよい）
export function todoOf(checks: AuditCheck[]) {
  return [...checks.filter((c) => c.status === "ng"), ...checks.filter((c) => c.status === "warn")];
}

export function ReportSummary({ d }: { d: ReportData }) {
  const todo = todoOf(d.checks);
  const missed = d.questions.filter((q) => q.hits === 0).length;
  return (
    <div className="grid gap-6 sm:grid-cols-3">
      <div>
        <p className="text-sm font-semibold text-slate-600">AIに紹介された割合</p>
        <p className="font-num text-5xl font-semibold text-slate-900">
          {d.measured ? pct(d.measured.hits, d.measured.total) : "—"}
          <span className="text-2xl text-slate-500">%</span>
        </p>
        <p className="text-sm text-slate-600">{d.measured ? `${d.measured.total}回答中 ${d.measured.hits}回（${fmtDay(d.measured.at)}）` : "未計測"}</p>
      </div>
      <div>
        <p className="text-sm font-semibold text-slate-600">サイト診断の点数</p>
        <p className="font-num text-5xl font-semibold text-slate-900">
          {d.score ?? "—"}
          <span className="text-2xl text-slate-500"> / 100</span>
        </p>
        <p className="text-sm text-slate-600">{d.score !== null ? `直すところ ${todo.length}項目` : "未診断"}</p>
      </div>
      <div>
        <p className="text-sm font-semibold text-slate-600">紹介されなかった質問</p>
        <p className="font-num text-5xl font-semibold text-slate-900">{d.measured ? missed : "—"}</p>
        <p className="text-sm text-slate-600">{d.measured ? `${d.questions.length}問中` : "未計測"}</p>
      </div>
    </div>
  );
}

export function NextSteps({ d }: { d: ReportData }) {
  const next = [...todoOf(d.checks).slice(0, 3).map((c) => c.title), ...d.plans.slice(0, 2).map((p) => `ページを作る：${p.title}`)].slice(0, 5);
  if (next.length === 0) return null;
  return (
    <section className="rounded-xl bg-slate-900 p-6 text-white print:border print:border-slate-900 print:bg-white print:text-slate-900">
      <h2 className="text-lg font-bold">次の30日でやること</h2>
      <ol className="mt-3 list-decimal space-y-1 pl-5">
        {next.map((n) => (
          <li key={n}>{n}</li>
        ))}
      </ol>
    </section>
  );
}

export function QuestionTable({ d }: { d: ReportData }) {
  return (
    <table className="w-full text-sm">
      <thead>
        <tr className="border-b border-slate-300 text-left text-slate-600">
          <th className="py-2 font-semibold">見込み客がAIに聞きそうな質問</th>
          <th className="py-2 text-right font-semibold">紹介</th>
          <th className="py-2 text-right font-semibold">順位</th>
        </tr>
      </thead>
      <tbody className="divide-y divide-slate-100">
        {d.questions.map((q) => (
          <tr key={q.no}>
            <td className="py-2 pr-3 text-slate-800">{q.text}</td>
            <td className="py-2 text-right font-num font-semibold">{q.hits ? `${q.hits}/${q.total}` : <span className="text-slate-500">なし</span>}</td>
            <td className="py-2 text-right font-num">{q.ranks.length ? q.ranks.map((r) => `${r}位`).join("・") : q.hits ? "言及" : "—"}</td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}

export function OthersAndSources({ d }: { d: ReportData }) {
  if (d.others.length === 0 && d.sources.length === 0) return null;
  return (
    <section className="grid gap-8 sm:grid-cols-2">
      {d.others.length > 0 && (
        <div className="min-w-0 space-y-2">
          <h2 className="text-xl font-bold text-slate-900">代わりに紹介されているもの</h2>
          <ul className="divide-y divide-slate-100 text-sm">
            {d.others.map((o) => (
              <li key={o.label} className="flex justify-between gap-3 py-1.5">
                <span className="text-slate-800">
                  {o.label}
                  {o.registered && <span className="ml-1 text-slate-500">（競合）</span>}
                </span>
                <span className="font-num">{o.count}回</span>
              </li>
            ))}
          </ul>
        </div>
      )}
      {d.sources.length > 0 && (
        <div className="min-w-0 space-y-2">
          <h2 className="text-xl font-bold text-slate-900">AIが引用したサイト</h2>
          <ul className="divide-y divide-slate-100 text-sm">
            {d.sources.slice(0, 8).map((s) => (
              <li key={s.domain} className="flex justify-between gap-3 py-1.5">
                <span className="min-w-0 break-all text-slate-800">
                  {s.domain}
                  {s.official && <span className="ml-1 font-semibold text-brand">（自社）</span>}
                </span>
                <span className="font-num">{s.count}回</span>
              </li>
            ))}
          </ul>
        </div>
      )}
    </section>
  );
}

// 報告書の全体
export function ReportView({ d }: { d: ReportData }) {
  return (
    <>
      <header className="border-b-2 border-slate-900 pb-5">
        <p className="text-sm font-semibold text-brand">AI検索の診断報告書</p>
        <h1 className="mt-1 text-3xl font-black text-slate-900">{d.name}</h1>
        <p className="mt-1 break-all text-sm text-slate-600">
          {d.url ?? "公式サイト未登録"}・{fmtDay(d.createdAt)}作成・ススメル
        </p>
      </header>
      <ReportSummary d={d} />
      <NextSteps d={d} />
      {d.measured && (
        <section className="space-y-3">
          <h2 className="text-xl font-bold text-slate-900">質問ごとの紹介</h2>
          <QuestionTable d={d} />
        </section>
      )}
      <OthersAndSources d={d} />
      {d.score !== null && (
        <section className="space-y-4">
          <h2 className="text-xl font-bold text-slate-900">サイトの直すところ（上から順に）</h2>
          <FixList checks={d.checks} />
        </section>
      )}
      {d.plans.length > 0 && (
        <section className="space-y-4">
          <h2 className="text-xl font-bold text-slate-900">紹介されなかった質問と、作るページの案</h2>
          <PagePlans plans={d.plans} />
        </section>
      )}
      <footer className="border-t border-slate-200 pt-4 text-sm text-slate-500">
        AIの回答は毎回変わります。この報告書の数字は、決まった質問をAPIで聞いた結果の目安で、すべての利用者への表示を表すものではありません。紹介や問い合わせの増加をお約束するものではありません。
      </footer>
    </>
  );
}
