import type { AuditCheck } from "@/lib/ai-check/audit";
import type { PagePlan } from "@/lib/ai-check/suggest";
import { CopyButton } from "@/components/ai/copy-button";
import { KpiStrip, PriorityBadge, ShareRow } from "@/components/ai/ds";

// 診断報告書のダッシュボード。サンプル（/ai/sample）、トップのプレビュー、ログイン後の実際の報告書で共用する
export type ReportData = {
  name: string;
  url: string | null;
  createdAt: Date;
  description: string;
  measured: { hits: number; total: number; at: Date } | null;
  score: number | null;
  questions: { no: number; text: string; hits: number; total: number; ranks: number[]; others: string[] }[];
  // 比較ランキング：自社と、回答に出たほかのサービス（回答のうち何回に出たか）
  ranking: { label: string; hits: number; self: boolean; registered: boolean }[];
  sources: { domain: string; count: number; kind: "official" | "platform" | "other" }[];
  trend: { label: string; rate: number }[];
  checks: AuditCheck[];
  plans: PagePlan[];
};

const fmtDay = (d: Date) => new Intl.DateTimeFormat("ja-JP", { timeZone: "Asia/Tokyo", year: "numeric", month: "long", day: "numeric" }).format(d);
const pct = (n: number, total: number) => (total ? Math.round((n / total) * 100) : 0);

// 重い順（直す → 直すとよい）
export function todoOf(checks: AuditCheck[]) {
  return [...checks.filter((c) => c.status === "ng"), ...checks.filter((c) => c.status === "warn")];
}

const SOURCE_KIND = { official: "自社サイト", platform: "比較・レビュー・SNS", other: "その他の記事" } as const;

// 診断項目の分類（意味のまとまりで見せる）
const GROUPS: { title: string; note: string; keys: string[] }[] = [
  { title: "AIが読めるか", note: "読み取りを止めていないか、文章として読めるか", keys: ["fetch", "robots", "sitemap", "text"] },
  { title: "何のサービスか伝わるか", note: "題名・一文の立ち位置・強みの言葉", keys: ["title", "pitch", "features"] },
  { title: "情報の中身と新しさ", note: "1件1ページの情報、よくある質問、更新日", keys: ["pages", "faq", "fresh"] },
  { title: "機械向けのデータ", note: "構造化データ、AI向けの案内ファイル", keys: ["jsonld", "llms"] },
];

function rankOfSelf(d: ReportData) {
  const sorted = [...d.ranking].sort((a, b) => b.hits - a.hits);
  return { pos: sorted.findIndex((r) => r.self) + 1, of: sorted.length };
}

export function reportKpis(d: ReportData) {
  const total = d.measured?.total ?? 0;
  const { pos, of } = rankOfSelf(d);
  const citeTotal = d.sources.reduce((n, s) => n + s.count, 0);
  const own = d.sources.filter((s) => s.kind === "official").reduce((n, s) => n + s.count, 0);
  const todo = todoOf(d.checks).length;
  return [
    { label: "紹介率", value: d.measured ? String(pct(d.measured.hits, total)) : "—", unit: "%", note: d.measured ? `${total}回答中 ${d.measured.hits}回 紹介` : "未計測", emphasis: true },
    { label: "比較候補の中の順位", value: d.measured && pos ? String(pos) : "—", unit: "位", note: d.measured ? `回答に出た${of}サービス中` : "未計測" },
    { label: "自社サイトが引用された割合", value: citeTotal ? String(pct(own, citeTotal)) : "—", unit: "%", note: citeTotal ? `引用${citeTotal}件中 ${own}件` : "引用なし" },
    { label: "サイト診断", value: d.score !== null ? String(d.score) : "—", unit: "点", note: d.score !== null ? `直すところ ${todo}項目` : "未診断" },
  ];
}

// 紹介率の推移（1本の折れ線。目盛りは0〜100%）
export function TrendLine({ points }: { points: { label: string; rate: number }[] }) {
  if (points.length < 2) {
    return <p className="flex h-full min-h-40 items-center text-sm text-slate-600">2回以上計測すると、推移が表示されます。</p>;
  }
  const W = 520;
  const H = 220;
  const pad = { l: 44, r: 16, t: 16, b: 30 };
  const iw = W - pad.l - pad.r;
  const ih = H - pad.t - pad.b;
  const x = (i: number) => pad.l + (i / (points.length - 1)) * iw;
  const y = (v: number) => pad.t + ih - (v / 100) * ih;
  const path = points.map((p, i) => `${i ? "L" : "M"}${x(i).toFixed(1)},${y(p.rate).toFixed(1)}`).join(" ");
  const last = points[points.length - 1];
  return (
    <svg viewBox={`0 0 ${W} ${H}`} className="h-auto w-full" role="img" aria-label={`紹介率の推移。最新は${last.rate}%`}>
      {[0, 25, 50, 75, 100].map((v) => (
        <g key={v}>
          <line x1={pad.l} x2={W - pad.r} y1={y(v)} y2={y(v)} stroke="#e5e7eb" strokeWidth="1" />
          <text x={pad.l - 8} y={y(v) + 4} textAnchor="end" fontSize="13" fill="#6b7280">
            {v}%
          </text>
        </g>
      ))}
      <path d={`${path} L${x(points.length - 1)},${y(0)} L${x(0)},${y(0)} Z`} fill="#2563eb" fillOpacity="0.07" />
      <path d={path} fill="none" stroke="#2563eb" strokeWidth="2" strokeLinejoin="round" strokeLinecap="round" />
      {points.map((p, i) => (
        <g key={p.label}>
          <circle cx={x(i)} cy={y(p.rate)} r={i === points.length - 1 ? 5 : 3.5} fill="#ffffff" stroke="#2563eb" strokeWidth="2">
            <title>{`${p.label}：${p.rate}%`}</title>
          </circle>
          <text x={x(i)} y={H - 8} textAnchor="middle" fontSize="13" fill="#6b7280">
            {p.label}
          </text>
        </g>
      ))}
      <text x={x(points.length - 1) - 8} y={y(last.rate) - 10} textAnchor="end" fontSize="14" fontWeight="600" fill="#111827">
        {last.rate}%
      </text>
    </svg>
  );
}

export function RankingTable({ d }: { d: ReportData }) {
  const total = d.measured?.total ?? 0;
  const rows = [...d.ranking].sort((a, b) => b.hits - a.hits).slice(0, 6);
  return (
    <div className="text-sm">
      <div className="flex justify-between border-b border-slate-200 px-3 pb-2 text-xs font-semibold text-slate-600">
        <span># サービス</span>
        <span>紹介率</span>
      </div>
      <div className="mt-1 space-y-0.5">
        {rows.map((r, i) => (
          <ShareRow
            key={r.label}
            label={
              <>
                <span className="mr-2 font-num text-slate-500">{i + 1}</span>
                {r.label}
              </>
            }
            share={pct(r.hits, total)}
            value={`${pct(r.hits, total)}%`}
            highlight={r.self}
            tag={r.self ? "自社" : r.registered ? "競合" : undefined}
          />
        ))}
      </div>
    </div>
  );
}

// パネル：見出し＋説明＋中身（罫線で囲むだけで、影は付けない）
function Panel({ title, note, children, id }: { title: string; note?: string; children: React.ReactNode; id?: string }) {
  return (
    <section id={id} className="min-w-0 scroll-mt-24 rounded-xl border border-slate-200 bg-white">
      <header className="border-b border-slate-200 px-4 py-3 sm:px-5">
        <h2 className="text-base font-semibold text-slate-900">{title}</h2>
        {note && <p className="text-sm text-slate-600">{note}</p>}
      </header>
      <div className="p-4 sm:p-5">{children}</div>
    </section>
  );
}

export function TopFixes({ d, limit = 3 }: { d: ReportData; limit?: number }) {
  const todo = todoOf(d.checks).slice(0, limit);
  if (todo.length === 0) return <p className="text-slate-700">直す項目はありません。</p>;
  return (
    <ol className="divide-y divide-slate-200">
      {todo.map((c, i) => (
        <li key={c.key} className="grid gap-x-4 gap-y-1 py-3 first:pt-0 last:pb-0 sm:grid-cols-[2rem_7rem_1fr]">
          <span className="font-num text-lg font-semibold text-slate-400">{String(i + 1).padStart(2, "0")}</span>
          <span>
            <PriorityBadge status={c.status} />
          </span>
          <span className="min-w-0">
            <a href={`#check-${c.key}`} className="font-semibold text-slate-900 hover:text-brand">
              {c.title}
            </a>
            {c.where && <span className="block break-all text-sm text-slate-600">{c.where}</span>}
          </span>
        </li>
      ))}
    </ol>
  );
}

export function QuestionsTable({ d, limit }: { d: ReportData; limit?: number }) {
  const rows = limit ? d.questions.slice(0, limit) : d.questions;
  return (
    <div className="overflow-x-auto">
      <table className="w-full min-w-[560px] text-sm">
        <thead>
          <tr className="border-b border-slate-200 text-left text-xs font-semibold text-slate-600">
            <th className="py-2 pr-3 font-semibold">見込み客がAIに聞きそうな質問</th>
            <th className="w-28 py-2 pr-3 font-semibold">自社</th>
            <th className="py-2 font-semibold">代わりに紹介されたサービス</th>
          </tr>
        </thead>
        <tbody className="divide-y divide-slate-100">
          {rows.map((q) => (
            <tr key={q.no} className="align-top">
              <td className="py-2.5 pr-3 text-slate-900">{q.text}</td>
              <td className="py-2.5 pr-3">
                {q.hits ? (
                  <span className="font-semibold text-slate-900">
                    <span className="text-brand" aria-hidden>
                      ✓{" "}
                    </span>
                    {q.ranks.length ? `${q.ranks[0]}位` : "言及"}
                    {q.total > 1 && <span className="ml-1 font-num font-normal text-slate-500">{`${q.hits}/${q.total}`}</span>}
                  </span>
                ) : (
                  <span className="text-slate-500">— 紹介なし</span>
                )}
              </td>
              <td className="py-2.5">
                <span className="flex flex-wrap gap-1">
                  {q.others.length ? (
                    q.others.map((o) => (
                      <span key={o} className="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-xs text-slate-700">
                        {o}
                      </span>
                    ))
                  ) : (
                    <span className="text-slate-400">—</span>
                  )}
                </span>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export function SourcesTable({ d }: { d: ReportData }) {
  const max = Math.max(1, ...d.sources.map((s) => s.count));
  return (
    <div className="text-sm">
      <div className="grid grid-cols-[1fr_auto] border-b border-slate-200 px-3 pb-2 text-xs font-semibold text-slate-600">
        <span>AIが引用したサイト</span>
        <span>回数</span>
      </div>
      <div className="mt-1 space-y-0.5">
        {d.sources.slice(0, 8).map((s) => (
          <ShareRow
            key={s.domain}
            label={
              <>
                {s.domain}
                <span className="ml-2 text-xs text-slate-500">{SOURCE_KIND[s.kind]}</span>
              </>
            }
            share={(s.count / max) * 100}
            value={`${s.count}`}
            highlight={s.kind === "official"}
          />
        ))}
      </div>
    </div>
  );
}

// 診断項目1つ。直す項目は、場所・今の内容・書き換え例まで開いて見せる
export function AuditItem({ c }: { c: AuditCheck }) {
  const open = c.status === "ng" || c.status === "warn";
  return (
    <li id={`check-${c.key}`} className="scroll-mt-24 py-3 first:pt-0 last:pb-0">
      <div className="flex flex-wrap items-center gap-2">
        <PriorityBadge status={c.status} />
        <span className="font-semibold text-slate-900">{c.title}</span>
      </div>
      <p className="mt-1 text-sm text-slate-700">{c.detail}</p>
      {open && (
        <dl className="mt-2 grid gap-x-4 gap-y-1.5 text-sm sm:grid-cols-[6rem_1fr]">
          {c.where && (
            <>
              <dt className="font-medium text-slate-600">直す場所</dt>
              <dd className="min-w-0 break-all text-slate-900">{c.where}</dd>
            </>
          )}
          {c.current && (
            <>
              <dt className="font-medium text-slate-600">今の内容</dt>
              <dd className="min-w-0 whitespace-pre-wrap break-all text-slate-700">{c.current}</dd>
            </>
          )}
          {c.fix && (
            <>
              <dt className="font-medium text-slate-600">直し方</dt>
              <dd className="min-w-0 text-slate-900">{c.fix}</dd>
            </>
          )}
        </dl>
      )}
      {open && c.example && (
        <div className="mt-2 overflow-hidden rounded-lg border border-slate-200">
          <div className="flex items-center justify-between gap-2 border-b border-slate-200 bg-slate-50 px-3 py-1.5">
            <span className="text-sm font-semibold text-slate-800">{c.exampleLabel ?? "書き換え例"}</span>
            <CopyButton text={c.example} targetId={`ex-${c.key}`} />
          </div>
          <pre id={`ex-${c.key}`} className="overflow-x-auto whitespace-pre-wrap break-all bg-white px-3 py-2 font-sans text-sm text-slate-900">
            {c.example}
          </pre>
        </div>
      )}
    </li>
  );
}

export function AuditGroups({ checks }: { checks: AuditCheck[] }) {
  const known = new Set(GROUPS.flatMap((g) => g.keys));
  const groups = [...GROUPS, { title: "そのほかの項目", note: "", keys: checks.map((c) => c.key).filter((k) => !known.has(k)) }];
  const order = { ng: 0, warn: 1, info: 2, ok: 3 } as const;
  return (
    <div className="grid gap-4 lg:grid-cols-2">
      {groups.map((g) => {
        const items = checks.filter((c) => g.keys.includes(c.key)).sort((a, b) => order[a.status] - order[b.status]);
        if (items.length === 0) return null;
        const todo = items.filter((c) => c.status === "ng" || c.status === "warn").length;
        return (
          <section key={g.title} className="min-w-0 rounded-xl border border-slate-200 bg-white">
            <header className="flex items-start justify-between gap-3 border-b border-slate-200 px-4 py-3">
              <div className="min-w-0">
                <h3 className="font-semibold text-slate-900">{g.title}</h3>
                {g.note && <p className="text-sm text-slate-600">{g.note}</p>}
              </div>
              <span className={`shrink-0 text-sm font-semibold ${todo ? "text-slate-900" : "text-slate-500"}`}>{todo ? `直す ${todo}` : "問題なし"}</span>
            </header>
            <ul className="divide-y divide-slate-100 px-4 py-3">
              {items.map((c) => (
                <AuditItem key={c.key} c={c} />
              ))}
            </ul>
          </section>
        );
      })}
    </div>
  );
}

export function PlanList({ plans }: { plans: PagePlan[] }) {
  return (
    <ol className="divide-y divide-slate-200">
      {plans.map((p, i) => (
        <li key={p.title} className="grid gap-x-6 gap-y-2 py-4 first:pt-0 last:pb-0 lg:grid-cols-[2rem_minmax(0,1fr)_minmax(0,1.2fr)]">
          <span className="font-num text-lg font-semibold text-slate-400">{String(i + 1).padStart(2, "0")}</span>
          <div className="min-w-0">
            <span className="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-xs font-semibold text-slate-700">{p.type}</span>
            <p className="mt-1.5 font-semibold text-slate-900">{p.title}</p>
            <p className="mt-1 text-sm text-slate-600">答える質問：{p.questions.map((q) => `「${q}」`).join("")}</p>
          </div>
          <dl className="min-w-0 space-y-1.5 text-sm">
            <div>
              <dt className="font-medium text-slate-600">最初の一文</dt>
              <dd className="text-slate-900">{p.firstLine}</dd>
            </div>
            <div>
              <dt className="font-medium text-slate-600">入れる内容</dt>
              <dd className="text-slate-900">{p.include.join("／")}</dd>
            </div>
          </dl>
        </li>
      ))}
    </ol>
  );
}

export const REPORT_SECTIONS = [
  { id: "overview", label: "概要" },
  { id: "fixes", label: "最初に直すこと" },
  { id: "questions", label: "質問ごとの結果" },
  { id: "sources", label: "競合と引用元" },
  { id: "audit", label: "サイト診断" },
  { id: "pages", label: "作るページの案" },
] as const;

// 報告書の本体（見出し → 指標 → 推移と比較 → 直すこと → 質問 → 競合と引用元 → 診断 → ページの案）
export function ReportDashboard({ d, compact = false }: { d: ReportData; compact?: boolean }) {
  return (
    <div className="space-y-5">
      <header id="overview" className="scroll-mt-24">
        <p className="text-sm font-semibold text-brand">AI検索の診断報告書</p>
        <h1 className="mt-1 text-2xl font-bold text-slate-900 sm:text-[1.75rem]">{d.name}</h1>
        <p className="mt-1 flex flex-wrap gap-x-3 gap-y-0.5 text-sm text-slate-600">
          {d.url && <span className="break-all">{d.url}</span>}
          <span>{d.measured ? `${fmtDay(d.measured.at)} 計測` : `${fmtDay(d.createdAt)} 作成`}</span>
        </p>
        <p className="mt-2 max-w-3xl text-sm text-slate-700">{d.description}</p>
      </header>

      <KpiStrip items={reportKpis(d)} />

      <div className="grid gap-5 lg:grid-cols-[1.25fr_1fr]">
        <Panel title="紹介率の推移" note="計測ごとの、紹介された回答の割合">
          <TrendLine points={d.trend} />
        </Panel>
        <Panel title="比較ランキング" note="同じ質問の回答に出た回数の割合">
          {d.ranking.length ? <RankingTable d={d} /> : <p className="text-sm text-slate-600">計測すると表示されます。</p>}
        </Panel>
      </div>

      <Panel id="fixes" title="最初に直すこと" note="影響が大きい順。項目名を押すと、直す場所と書き換え例へ移ります">
        <TopFixes d={d} />
      </Panel>

      {!compact && (
        <>
          {d.measured && (
            <Panel id="questions" title="質問ごとの結果" note="紹介されなかった質問が、直すべき場所の手がかりです">
              <QuestionsTable d={d} />
            </Panel>
          )}
          <Panel id="sources" title="競合と引用元" note="AIが紹介の根拠にしたサイト。競合が引用されている種類のページを、自社にも用意します">
            {d.sources.length ? <SourcesTable d={d} /> : <p className="text-sm text-slate-600">計測すると表示されます。</p>}
          </Panel>
          {d.score !== null && (
            <section id="audit" className="scroll-mt-24 space-y-3">
              <div>
                <h2 className="text-lg font-semibold text-slate-900">サイト診断の詳細</h2>
                <p className="text-sm text-slate-600">4つの観点に分けています。直す項目は、場所・今の内容・書き換え例つきです。</p>
              </div>
              <AuditGroups checks={d.checks} />
            </section>
          )}
          {d.plans.length > 0 && (
            <Panel id="pages" title="作るページの案" note="紹介されなかった質問に答えるページ。同じページで答えられる質問はまとめています">
              <PlanList plans={d.plans} />
            </Panel>
          )}
          <p className="border-t border-slate-200 pt-4 text-sm text-slate-500">
            AIの回答は毎回変わります。数字は、決まった質問をAPIで聞いた結果の目安で、すべての利用者への表示を表すものではありません。紹介や問い合わせの増加をお約束するものではありません。
          </p>
        </>
      )}
    </div>
  );
}
