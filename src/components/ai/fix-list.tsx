import type { AuditCheck } from "@/lib/ai-check/audit";
import type { PagePlan } from "@/lib/ai-check/suggest";
import { CopyButton } from "@/components/ai/copy-button";

export const STATUS_STYLE = { ok: "bg-emerald-50 text-emerald-800", warn: "bg-amber-50 text-amber-900", ng: "bg-rose-50 text-rose-800", info: "bg-slate-100 text-slate-700" } as const;
export const STATUS_LABEL = { ok: "✓ OK", warn: "! 直すとよい", ng: "✕ 直す", info: "i 参考" } as const;
const ORDER = { ng: 0, warn: 1, info: 2, ok: 3 } as const;

// 直す項目（問題あり・直すとよい）を、場所・今の内容・書き換え例つきで並べる。問題のない項目は1行にまとめる
export function FixList({ checks }: { checks: AuditCheck[] }) {
  const todo = [...checks].filter((c) => c.status === "ng" || c.status === "warn").sort((a, b) => ORDER[a.status] - ORDER[b.status]);
  const rest = checks.filter((c) => c.status === "ok" || c.status === "info");
  return (
    <div className="space-y-4">
      {todo.length === 0 && <p className="text-slate-700">直す項目はありません。</p>}
      <ol className="space-y-4">
        {todo.map((c, i) => (
          <li key={c.key} className="break-inside-avoid rounded-xl border border-slate-200 bg-white p-5">
            <div className="flex flex-wrap items-center gap-2">
              <span className="font-num text-sm font-semibold text-slate-500">{i + 1}</span>
              <span className={`whitespace-nowrap rounded-full px-2.5 py-0.5 text-sm font-semibold ${STATUS_STYLE[c.status]}`}>{STATUS_LABEL[c.status]}</span>
              <h3 className="text-lg font-bold text-slate-900">{c.title}</h3>
            </div>
            <dl className="mt-3 grid gap-x-4 gap-y-2 text-sm sm:grid-cols-[7rem_1fr]">
              <dt className="font-semibold text-slate-600">わかったこと</dt>
              <dd className="min-w-0 text-slate-800">{c.detail}</dd>
              {c.where && (
                <>
                  <dt className="font-semibold text-slate-600">直す場所</dt>
                  <dd className="min-w-0 break-all text-slate-800">{c.where}</dd>
                </>
              )}
              {c.current && (
                <>
                  <dt className="font-semibold text-slate-600">今の内容</dt>
                  <dd className="min-w-0 whitespace-pre-wrap break-all rounded-md bg-slate-50 px-3 py-2 text-slate-700">{c.current}</dd>
                </>
              )}
              {c.fix && (
                <>
                  <dt className="font-semibold text-slate-600">直し方</dt>
                  <dd className="min-w-0 text-slate-800">{c.fix}</dd>
                </>
              )}
            </dl>
            {c.example && (
              <div className="mt-3 rounded-lg border border-brand/30 bg-brand-soft">
                <div className="flex items-center justify-between gap-2 border-b border-brand/20 px-3 py-2">
                  <span className="text-sm font-bold text-brand-dark">{c.exampleLabel ?? "書き換え例"}</span>
                  <CopyButton text={c.example} targetId={`ex-${c.key}`} />
                </div>
                <pre id={`ex-${c.key}`} className="overflow-x-auto whitespace-pre-wrap break-all px-3 py-2.5 font-sans text-sm text-slate-900">
                  {c.example}
                </pre>
              </div>
            )}
          </li>
        ))}
      </ol>
      {rest.length > 0 && (
        <details className="rounded-xl border border-slate-200 bg-white px-5 py-3">
          <summary className="cursor-pointer text-sm font-semibold text-slate-700">問題のない項目・参考（{rest.length}項目）</summary>
          <ul className="mt-2 divide-y divide-slate-100 text-sm">
            {rest.map((c) => (
              <li key={c.key} className="flex flex-wrap items-baseline gap-x-3 gap-y-1 py-2">
                <span className={`whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold ${STATUS_STYLE[c.status]}`}>{STATUS_LABEL[c.status]}</span>
                <span className="font-semibold text-slate-800">{c.title}</span>
                <span className="min-w-0 text-slate-600">{c.detail}</span>
              </li>
            ))}
          </ul>
        </details>
      )}
    </div>
  );
}

// 紹介されなかった質問ごとの、作るページの案
export function PagePlans({ plans }: { plans: PagePlan[] }) {
  return (
    <ol className="space-y-4">
      {plans.map((p, i) => (
        <li key={p.title} className="break-inside-avoid rounded-xl border border-slate-200 bg-white p-5">
          <p className="text-sm text-slate-600">
            <span className="font-num font-semibold">{i + 1}</span>　紹介されなかった質問：
          </p>
          <ul className="mt-1 space-y-0.5">
            {p.questions.map((q) => (
              <li key={q} className="font-semibold text-slate-900">
                「{q}」
              </li>
            ))}
          </ul>
          <p className="mt-2">
            <span className="rounded-md bg-slate-100 px-2 py-0.5 text-sm font-semibold text-slate-700">{p.type}</span>
          </p>
          <dl className="mt-3 grid gap-x-4 gap-y-2 text-sm sm:grid-cols-[7rem_1fr]">
            <dt className="font-semibold text-slate-600">題名の例</dt>
            <dd className="min-w-0 font-semibold text-slate-900">{p.title}</dd>
            <dt className="font-semibold text-slate-600">最初の一文</dt>
            <dd className="min-w-0 text-slate-800">{p.firstLine}</dd>
            <dt className="font-semibold text-slate-600">入れる内容</dt>
            <dd className="min-w-0">
              <ul className="list-disc space-y-0.5 pl-5 text-slate-800">
                {p.include.map((x) => (
                  <li key={x}>{x}</li>
                ))}
              </ul>
            </dd>
          </dl>
        </li>
      ))}
    </ol>
  );
}
