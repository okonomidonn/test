import { PLANS, formatYen, type PlanDef } from "@/lib/billing/plans";

const ROWS: { label: string; value: (p: PlanDef) => string }[] = [
  { label: "店舗数", value: (p) => `${p.stores}店舗` },
  { label: "計測（1店舗あたり）", value: (p) => `30日間で${p.runsPerMonth}回` },
  { label: "AIに聞く質問", value: (p) => `${p.maxQuestions}問まで` },
  { label: "ChatGPT・Gemini", value: () => "○" },
  { label: "参考にされたページ", value: () => "○" },
  { label: "公式サイトの診断・改善の提案", value: () => "○" },
];

// 料金表。各プランの下に、申し込みボタンなどを差し込めるようにする
export function PlanTable({ current, action }: { current?: PlanDef["key"]; action?: (p: PlanDef) => React.ReactNode }) {
  return (
    <div className="grid gap-4 md:grid-cols-3">
      {Object.values(PLANS).map((p) => (
        <div
          key={p.key}
          className={`flex flex-col rounded-2xl border bg-white p-6 shadow-sm ${p.key === "STARTER" ? "border-brand ring-2 ring-indigo-100" : "border-slate-200"}`}
        >
          <div className="flex items-center justify-between">
            <h3 className="text-lg font-bold text-slate-900">{p.name}</h3>
            {current === p.key && <span className="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-brand">ご利用中</span>}
            {current !== p.key && p.key === "STARTER" && <span className="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-brand">おすすめ</span>}
          </div>
          <p className="mt-1 text-sm text-slate-500">{p.summary}</p>
          <p className="mt-4 text-3xl font-bold text-slate-900">
            {p.priceYen ? formatYen(p.priceYen) : "0円"}
            <span className="text-sm font-medium text-slate-500">{p.priceYen ? "／月（税込）" : ""}</span>
          </p>
          <dl className="mt-5 flex-1 space-y-2 text-sm">
            {ROWS.map((r) => (
              <div key={r.label} className="flex justify-between gap-3 border-b border-slate-100 pb-2">
                <dt className="text-slate-500">{r.label}</dt>
                <dd className="font-medium text-slate-800">{r.value(p)}</dd>
              </div>
            ))}
          </dl>
          {action && <div className="mt-6">{action(p)}</div>}
        </div>
      ))}
    </div>
  );
}
