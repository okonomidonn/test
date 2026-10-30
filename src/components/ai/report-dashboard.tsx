import Link from "next/link";
import { FIX_KIND, type AuditCheck } from "@/lib/ai-check/audit";
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
  // AIからの流入（GA4）。継続支援で、お客様のGA4を見て記入する
  aiTraffic?: {
    period: string;
    users: number;
    totalUsers: number;
    keyEvents: number;
    totalKeyEvents: number;
    byAi: { label: string; users: number }[];
    pages: { path: string; users: number; keyEvents: number }[];
  };
};

const fmtDay = (d: Date) => new Intl.DateTimeFormat("ja-JP", { timeZone: "Asia/Tokyo", year: "numeric", month: "long", day: "numeric" }).format(d);
const pct = (n: number, total: number) => (total ? Math.round((n / total) * 100) : 0);

// 重い順（直す → 直すとよい）
export function todoOf(checks: AuditCheck[]) {
  const sev = { ng: 0, warn: 1 } as const;
  const kind = { tech: 0, info: 1, hypothesis: 2 } as const;
  return checks
    .filter((c): c is AuditCheck & { status: "ng" | "warn" } => c.status === "ng" || c.status === "warn")
    .sort((a, b) => sev[a.status] - sev[b.status] || kind[a.kind ?? "info"] - kind[b.kind ?? "info"]);
}

// 結論：紹介率と、いちばん紹介されている競合との差、代わりに紹介された質問の数
export function conclusionOf(d: ReportData): string | null {
  if (!d.measured) return null;
  const total = d.measured.total;
  const self = pct(d.measured.hits, total);
  const rival = [...d.ranking].filter((r) => !r.self).sort((a, b) => b.hits - a.hits)[0];
  const missed = d.questions.filter((q) => q.hits === 0);
  const parts = [`AIに紹介された割合は${self}%（${total}回答中${d.measured.hits}回）。`];
  if (rival && rival.hits > d.measured.hits) {
    const lost = missed.filter((q) => q.others.includes(rival.label)).length;
    parts.push(`いちばん多く紹介された「${rival.label}」は${pct(rival.hits, total)}%で、差は${pct(rival.hits, total) - self}ポイントです。`);
    if (lost) parts.push(`紹介されなかった${missed.length}問のうち${lost}問で、代わりに「${rival.label}」が紹介されています。`);
  } else if (rival) {
    parts.push("回答に出たサービスの中で、最も多く紹介されています。");
  }
  return parts.join("");
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

function topRival(d: ReportData) {
  return [...d.ranking].filter((r) => !r.self).sort((a, b) => b.hits - a.hits)[0];
}

export function reportKpis(d: ReportData) {
  const total = d.measured?.total ?? 0;
  const { pos, of } = rankOfSelf(d);
  const citeTotal = d.sources.reduce((n, s) => n + s.count, 0);
  const own = d.sources.filter((s) => s.kind === "official").reduce((n, s) => n + s.count, 0);
  const todo = todoOf(d.checks).length;
  return [
    {
      label: "紹介率",
      value: d.measured ? String(pct(d.measured.hits, total)) : "—",
      unit: "%",
      note: d.measured ? `${total}回答中 ${d.measured.hits}回。${topRival(d) ? `競合の最大は${pct(topRival(d)!.hits, total)}%` : ""}` : "未計測",
      emphasis: true,
    },
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
        <li key={c.key} className="grid gap-x-4 gap-y-1.5 py-3.5 first:pt-0 last:pb-0 sm:grid-cols-[2rem_1fr]">
          <span className="font-num text-lg font-semibold text-slate-400">{String(i + 1).padStart(2, "0")}</span>
          <div className="min-w-0">
            <div className="flex flex-wrap items-center gap-2">
              <PriorityBadge status={c.status} />
              {c.kind && <span className="rounded-md border border-slate-200 px-1.5 py-0.5 text-xs font-semibold text-slate-700">{FIX_KIND[c.kind].label}</span>}
            </div>
            <a href={`#check-${c.key}`} className="mt-1 block font-semibold text-slate-900 hover:text-brand">
              {c.title}
            </a>
            {c.where && <p className="break-all text-sm text-slate-600">{c.where}</p>}
            {c.verify && (
              <p className="mt-1 text-sm text-slate-800">
                <span className="font-semibold">確かめること：</span>
                {c.verify}
              </p>
            )}
          </div>
        </li>
      ))}
    </ol>
  );
}

// 数字の見方と計測の条件
export function HowToRead({ d }: { d: ReportData }) {
  return (
    <details className="group rounded-xl border border-slate-200 bg-white">
      <summary className="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-slate-900 sm:px-5">
        <span className="mr-1 inline-block transition group-open:rotate-90" aria-hidden>
          ▸
        </span>
        数字の見方と計測の条件
      </summary>
      <dl className="grid gap-x-6 gap-y-3 border-t border-slate-200 px-4 py-4 text-sm sm:grid-cols-[10rem_1fr] sm:px-5">
        <dt className="font-semibold text-slate-700">計測の条件</dt>
        <dd className="text-slate-800">{d.description}</dd>
        <dt className="font-semibold text-slate-700">紹介率</dt>
        <dd className="text-slate-800">AIの回答のうち、サービス名かサイトのURLが出た回答の割合。良し悪しは、同じ質問での競合の紹介率と比べて判断します。</dd>
        <dt className="font-semibold text-slate-700">比較候補の中の順位</dt>
        <dd className="text-slate-800">同じ質問の回答に出たサービスを、紹介された回数で並べたときの順位です。</dd>
        <dt className="font-semibold text-slate-700">自社サイトが引用された割合</dt>
        <dd className="text-slate-800">AIが根拠として示したサイトのうち、自社サイトの割合です。</dd>
        <dt className="font-semibold text-slate-700">サイト診断の点数</dt>
        <dd className="text-slate-800">各項目を OK＝2点・直すとよい＝1点・直す＝0点 で数え、満点に対する割合を100点満点にしたものです（参考の項目は数えません）。紹介率とは別の指標で、点数が高くても紹介されるとは限りません。</dd>
        <dt className="font-semibold text-slate-700">限界</dt>
        <dd className="text-slate-800">AIの回答は毎回変わります。APIで聞いた結果で、一人ひとりの画面での表示とは一致しないことがあります。</dd>
      </dl>
    </details>
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
        {open && c.kind && <span className="rounded-md border border-slate-200 px-1.5 py-0.5 text-xs font-semibold text-slate-700">{FIX_KIND[c.kind].label}</span>}
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
          {c.verify && (
            <>
              <dt className="font-medium text-slate-600">確かめること</dt>
              <dd className="min-w-0 text-slate-900">{c.verify}</dd>
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
            <span className="flex flex-wrap gap-1.5">
              <span className={`rounded border px-1.5 py-0.5 text-xs font-semibold ${p.action === "既存ページを直す" ? "border-brand/30 bg-brand-soft text-brand-dark" : "border-amber-200 bg-amber-50 text-amber-900"}`}>{p.action}</span>
              <span className="rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-xs font-semibold text-slate-700">{p.type}</span>
            </span>
            <p className="mt-1.5 font-semibold text-slate-900">{p.title}</p>
            <p className="mt-1 text-sm text-slate-700">{p.reason}</p>
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
  { id: "overview", label: "結論" },
  { id: "fixes", label: "今月やること" },
  { id: "numbers", label: "数字" },
  { id: "questions", label: "質問ごとの結果" },
  { id: "sources", label: "競合と引用元" },
  { id: "traffic", label: "AIからの流入" },
  { id: "audit", label: "サイト診断の詳細" },
  { id: "pages", label: "作るページの案" },
] as const;

// AIからの流入：AIに紹介されたあと、実際にサイトに来て行動した人数（GA4）
function AiTrafficPanel({ t }: { t: NonNullable<ReportData["aiTraffic"]> }) {
  const top = Math.max(...t.byAi.map((a) => a.users), 1);
  return (
    <Panel id="traffic" title="AIからの流入（AIの営業成績）" note={`AIに紹介されたあと、サイトに来て行動した人（GA4・${t.period}）`}>
      <dl className="grid grid-cols-2 gap-3">
        {[["AI経由のユーザー", t.users, t.totalUsers, "人"], ["AI経由のキーイベント", t.keyEvents, t.totalKeyEvents, "件"]].map(([k, v, all, u]) => (
          <div key={k as string} className="rounded-lg bg-slate-50 p-4">
            <dt className="text-sm font-medium text-slate-600">{k}</dt>
            <dd className="mt-1 font-num text-[1.75rem] font-semibold leading-tight text-slate-900">
              {v}
              <span className="ml-0.5 text-base font-medium text-slate-500">{u}</span>
            </dd>
            <p className="text-sm text-slate-600">
              全体{all as number}{u}のうち{pct(v as number, all as number)}%
            </p>
          </div>
        ))}
      </dl>
      <div className="mt-5 grid gap-5 md:grid-cols-2">
        <div>
          <p className="mb-2 text-sm font-semibold text-slate-700">AIごとのユーザー</p>
          {t.byAi.map((a) => (
            <ShareRow key={a.label} label={a.label} share={(a.users / top) * 100} value={`${a.users}人`} />
          ))}
        </div>
        <div>
          <p className="mb-2 text-sm font-semibold text-slate-700">AIから来た人が最初に見たページ</p>
          <table className="w-full text-left text-sm">
            <thead className="text-slate-600">
              <tr>
                <th className="py-1.5 font-medium">ページ</th>
                <th className="w-16 py-1.5 text-right font-medium">ユーザー</th>
                <th className="w-24 py-1.5 text-right font-medium">キーイベント</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {t.pages.map((p) => (
                <tr key={p.path}>
                  <td className="max-w-0 truncate py-2 pr-2 font-mono text-slate-800">{p.path}</td>
                  <td className="py-2 text-right font-num text-slate-900">{p.users}</td>
                  <td className="py-2 text-right font-num text-slate-900">{p.keyEvents}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
      <p className="mt-4 text-xs text-slate-500">キーイベントは「応募ページへ進む」などの操作の件数です。AIからの流入は、参照元がAIのサービスと分かる訪問だけを数えています。</p>
    </Panel>
  );
}

// 折りたたみ（印刷用の報告書では最初から開く）
function Fold({ id, title, note, open, children }: { id: string; title: string; note: string; open: boolean; children: React.ReactNode }) {
  return (
    <details id={id} open={open} className="group scroll-mt-24 rounded-xl border border-slate-200 bg-white">
      <summary className="flex cursor-pointer list-none items-start justify-between gap-3 px-4 py-3 sm:px-5">
        <span className="min-w-0">
          <span className="block text-base font-semibold text-slate-900">{title}</span>
          <span className="block text-sm text-slate-600">{note}</span>
        </span>
        <span className="mt-0.5 shrink-0 text-sm font-semibold text-brand">
          <span className="group-open:hidden">開く</span>
          <span className="hidden group-open:inline">閉じる</span>
        </span>
      </summary>
      <div className="border-t border-slate-200 p-4 sm:p-5">{children}</div>
    </details>
  );
}

// 今月やることを1行で言うと（診断項目ごと）
const ACTION_TITLE: Record<string, string> = {
  fetch: "サイトが開ける状態にする",
  robots: "AIの読み取りを許可する",
  sitemap: "サイトマップを登録する",
  text: "説明を画像ではなく文章で書く",
  title: "題名と説明文で、何のサービスかを示す",
  pitch: "サービスを一文で説明する文を、トップに書く",
  features: "強みの言葉を、事実を確かめて書き足す",
  faq: "よくある質問のページを作る",
  fresh: "更新日と掲載件数を書く",
  pages: "固有の情報を1件1ページにする",
  jsonld: "運営者とサービスの情報をデータで追加する",
  llms: "AI向けの案内ファイルを置く",
};

// 状態の一言（競合との比較で決める）
export function verdictOf(d: ReportData) {
  if (!d.measured) return null;
  const total = d.measured.total;
  const self = pct(d.measured.hits, total);
  const rival = topRival(d);
  const rivalRate = rival ? pct(rival.hits, total) : 0;
  const title = !rival ? "紹介の状況を計測しました" : rivalRate - self >= 10 ? "競合より紹介されにくい状態です" : self - rivalRate >= 10 ? "競合より多く紹介されています" : "競合と同じくらい紹介されています";
  const sub = `${total}回答中${d.measured.hits}回答で紹介。${rival ? `最も多く紹介された競合「${rival.label}」との差は${Math.abs(rivalRate - self)}ポイントです。` : ""}`;
  return { title, sub, self, rival, rivalRate };
}

// 報告書の冒頭：一言の結論、2つの数字、今月やること3つ（1行ずつ。押すと詳しく）
export function ReportSummaryHero({ d, linkDetails = true }: { d: ReportData; linkDetails?: boolean }) {
  const v = verdictOf(d);
  const todo = todoOf(d.checks).slice(0, 3);
  return (
    <section className="overflow-hidden rounded-xl border border-slate-200 bg-white">
      {v && (
        <div className="grid gap-5 border-b border-slate-200 p-5 sm:p-6 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
          <div className="min-w-0">
            <h2 className="text-[1.5rem] font-bold leading-snug text-slate-900 sm:text-[1.75rem]">{v.title}</h2>
            <p className="mt-2 text-slate-700">{v.sub}</p>
          </div>
          <dl className="grid grid-cols-2 gap-6">
            <div>
              <dt className="text-sm font-medium text-slate-600">自社の紹介率</dt>
              <dd className="font-num text-[2.5rem] font-semibold leading-none text-brand">
                {v.self}
                <span className="text-xl">%</span>
              </dd>
            </div>
            {v.rival && (
              <div>
                <dt className="text-sm font-medium text-slate-600">最多の競合</dt>
                <dd className="font-num text-[2.5rem] font-semibold leading-none text-slate-900">
                  {v.rivalRate}
                  <span className="text-xl">%</span>
                </dd>
              </div>
            )}
          </dl>
        </div>
      )}
      <div id="fixes" className="scroll-mt-24 p-5 sm:p-6">
        <h3 className="font-semibold text-slate-900">今月、優先して確認する3つのこと</h3>
        {todo.length === 0 ? (
          <p className="mt-2 text-slate-700">直す項目はありません。</p>
        ) : (
          <ol className="mt-3 divide-y divide-slate-200 border-y border-slate-200">
            {todo.map((c, i) => (
              <li key={c.key}>
                <details className="group">
                  <summary className="flex cursor-pointer list-none items-center gap-3 py-3">
                    <span className="font-num text-lg font-semibold text-slate-400">{i + 1}</span>
                    <span className="min-w-0 flex-1 font-semibold text-slate-900">{ACTION_TITLE[c.key] ?? c.title}</span>
                    {c.kind && <span className="hidden shrink-0 rounded-md border border-slate-200 px-1.5 py-0.5 text-xs font-semibold text-slate-700 sm:inline">{FIX_KIND[c.kind].label}</span>}
                    <span className="shrink-0 text-sm font-semibold text-brand">
                      <span className="group-open:hidden">詳しく</span>
                      <span className="hidden group-open:inline">閉じる</span>
                    </span>
                  </summary>
                  <dl className="grid gap-x-4 gap-y-1.5 pb-4 pl-7 text-sm sm:grid-cols-[6.5rem_1fr]">
                    <dt className="font-medium text-slate-600">わかったこと</dt>
                    <dd className="min-w-0 text-slate-900">{c.detail}</dd>
                    {c.where && (
                      <>
                        <dt className="font-medium text-slate-600">直す場所</dt>
                        <dd className="min-w-0 break-all text-slate-900">{c.where}</dd>
                      </>
                    )}
                    {c.verify && (
                      <>
                        <dt className="font-medium text-slate-600">確かめること</dt>
                        <dd className="min-w-0 text-slate-900">{c.verify}</dd>
                      </>
                    )}
                    {linkDetails && c.example && (
                      <>
                        <dt className="font-medium text-slate-600">書き換え例</dt>
                        <dd>
                          <a href={`#check-${c.key}`} className="font-semibold text-brand hover:underline">
                            サイト診断の詳細で見る ↓
                          </a>
                        </dd>
                      </>
                    )}
                  </dl>
                </details>
              </li>
            ))}
          </ol>
        )}
      </div>
    </section>
  );
}

// 報告書の本体：結論 → 今月やること → 数字 → 根拠のデータ → 技術的な詳細（必要な人だけ開く）
export function ReportDashboard({ d, compact = false, expandDetails = false }: { d: ReportData; compact?: boolean; expandDetails?: boolean }) {
  return (
    <div className="space-y-5">
      <header id="overview" className="scroll-mt-24">
        <p className="text-sm font-semibold text-brand">AI検索の診断報告書</p>
        <h1 className="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">{d.name}</h1>
        <p className="mt-1 flex flex-wrap gap-x-3 gap-y-0.5 text-sm text-slate-600">
          {d.url && <span className="break-all">{d.url}</span>}
          <span>{d.measured ? `${fmtDay(d.measured.at)} 計測` : `${fmtDay(d.createdAt)} 作成`}</span>
        </p>
      </header>

      <ReportSummaryHero d={d} linkDetails={!compact} />

      <section id="numbers" className="scroll-mt-24 space-y-3">
        <KpiStrip items={compact ? reportKpis(d).slice(0, 4) : reportKpis(d)} />
        {!compact && <HowToRead d={d} />}
      </section>

      {!compact && (
        <>
          <div className="grid gap-5 lg:grid-cols-[1.25fr_1fr]">
            <Panel title="紹介率の推移" note="計測ごとの、紹介された回答の割合">
              <TrendLine points={d.trend} />
            </Panel>
            <Panel title="比較ランキング" note="同じ質問の回答に出た回数の割合">
              {d.ranking.length ? <RankingTable d={d} /> : <p className="text-sm text-slate-600">計測すると表示されます。</p>}
            </Panel>
          </div>
          {d.measured && (
            <Panel id="questions" title="質問ごとの結果" note="紹介されなかった質問と、代わりに紹介されたサービス">
              <QuestionsTable d={d} />
            </Panel>
          )}
          <Panel id="sources" title="競合と引用元" note="AIが紹介の根拠にしたサイト。競合が引用されている種類のページを、自社にも用意できるかを検討します">
            {d.sources.length ? <SourcesTable d={d} /> : <p className="text-sm text-slate-600">計測すると表示されます。</p>}
          </Panel>
          {d.aiTraffic && <AiTrafficPanel t={d.aiTraffic} />}
          {d.score !== null && (
            <Fold id="audit" title={`サイト診断の詳細（${d.score}点）`} note="技術的な内容です。4つの観点に分け、直す項目には場所・今の内容・書き換え例・確かめることを付けています" open={expandDetails}>
              <AuditGroups checks={d.checks} />
            </Fold>
          )}
          {d.plans.length > 0 && (
            <Fold id="pages" title={`作るページの案（${d.plans.length}件）`} note="紹介されなかった質問への答え方。まず既存ページの修正で済むかを判断しています" open={expandDetails}>
              <PlanList plans={d.plans} />
            </Fold>
          )}
          <p className="border-t border-slate-200 pt-4 text-sm text-slate-500">
            AIの回答は毎回変わります。数字は、決まった質問をAPIで聞いた結果の目安で、すべての利用者への表示を表すものではありません。改善案には仮説を含みます。紹介や問い合わせの増加をお約束するものではありません。
            <Link href="/ai/method" className="ml-1 underline">
              計測のしかたと限界
            </Link>
          </p>
        </>
      )}
    </div>
  );
}
