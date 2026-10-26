import Link from "next/link";
import type { ReactNode } from "react";

// ススメルの共通部品。色・余白・文字の大きさはここでそろえる（docs/susumeru-redesign.md）

type Variant = "primary" | "secondary" | "ghost" | "dark";
type Size = "md" | "lg";

const VARIANT: Record<Variant, string> = {
  primary: "bg-brand text-white hover:bg-brand-dark",
  secondary: "border border-slate-300 bg-white text-slate-900 hover:bg-slate-50",
  ghost: "text-slate-700 hover:bg-slate-100 hover:text-slate-900",
  dark: "bg-slate-900 text-white hover:bg-slate-800",
};
const SIZE: Record<Size, string> = { md: "h-9 px-3.5 text-sm", lg: "h-11 px-5 text-base" };

export function btn(variant: Variant = "primary", size: Size = "md") {
  return `inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-lg font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand disabled:opacity-50 ${VARIANT[variant]} ${SIZE[size]}`;
}

export function ButtonLink({ href, variant, size, children }: { href: string; variant?: Variant; size?: Size; children: ReactNode }) {
  return (
    <Link href={href} className={btn(variant, size)}>
      {children}
    </Link>
  );
}

// セクションの見出し：小さなラベル＋二段の見出し（主文は濃く、補足はグレー）
export function SectionHead({ label, title, sub, id }: { label?: string; title: string; sub?: string; id?: string }) {
  return (
    <div id={id} className="max-w-3xl scroll-mt-24">
      {label && <p className="text-sm font-semibold text-brand">{label}</p>}
      <h2 className="mt-2 text-[1.625rem] font-bold text-slate-900 sm:text-[2rem]">
        {title}
        {sub && <span className="text-slate-500"> {sub}</span>}
      </h2>
    </div>
  );
}

// 製品画面の窓枠（影はここだけに使う）
export function AppWindow({ title, badge, children }: { title: string; badge?: string; children: ReactNode }) {
  return (
    <div className="min-w-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_1px_2px_rgba(17,24,39,0.04),0_12px_32px_-12px_rgba(17,24,39,0.18)]">
      <div className="flex items-center gap-3 border-b border-slate-200 bg-slate-50 px-4 py-2.5">
        <span className="flex gap-1.5" aria-hidden>
          <span className="h-2.5 w-2.5 rounded-full bg-slate-300" />
          <span className="h-2.5 w-2.5 rounded-full bg-slate-300" />
          <span className="h-2.5 w-2.5 rounded-full bg-slate-300" />
        </span>
        <span className="min-w-0 truncate text-sm text-slate-600">{title}</span>
        {badge && <span className="ml-auto shrink-0 rounded-md border border-slate-300 bg-white px-2 py-0.5 text-xs font-semibold text-slate-700">{badge}</span>}
      </div>
      {children}
    </div>
  );
}

// 指標の帯：ラベル・値・補足を横一列に（スマホは2列）
export type Kpi = { label: string; value: string; unit?: string; note?: string; emphasis?: boolean };
export function KpiStrip({ items }: { items: Kpi[] }) {
  return (
    <dl className={`grid grid-cols-2 overflow-hidden rounded-xl border border-slate-200 ${items.length >= 4 ? "lg:grid-cols-4" : items.length === 3 ? "lg:grid-cols-3" : ""}`}>
      {items.map((k, i) => (
        <div
          key={k.label}
          className={`min-w-0 border-slate-200 p-4 sm:p-5 ${i % 2 === 1 ? "border-l" : ""} ${i >= 2 ? "border-t lg:border-t-0" : ""} ${i === 2 ? "lg:border-l" : ""} ${k.emphasis ? "bg-brand-soft" : "bg-white"}`}
        >
          <dt className="text-sm font-medium text-slate-600">{k.label}</dt>
          <dd className="mt-1 font-num text-[1.75rem] font-semibold leading-tight text-slate-900">
            {k.value}
            {k.unit && <span className="ml-0.5 text-base font-medium text-slate-500">{k.unit}</span>}
          </dd>
          {k.note && <p className="mt-1 text-sm text-slate-600">{k.note}</p>}
        </div>
      ))}
    </dl>
  );
}

// 行の背景に割合の棒を敷いた表の1行（Plausible型）
export function ShareRow({ label, share, value, highlight, tag }: { label: ReactNode; share: number; value: string; highlight?: boolean; tag?: string }) {
  return (
    <div className="relative flex items-center justify-between gap-3 px-3 py-2" title={`${typeof label === "string" ? label : ""} ${value}`}>
      <span
        className={`absolute inset-y-0.5 left-0 rounded-md ${highlight ? "bg-brand/15" : "bg-slate-100"}`}
        style={{ width: `${Math.max(2, Math.min(100, share))}%` }}
        aria-hidden
      />
      <span className={`relative min-w-0 truncate ${highlight ? "font-semibold text-slate-900" : "text-slate-800"}`}>
        {label}
        {tag && <span className="ml-2 rounded border border-brand/30 bg-white px-1.5 py-px text-xs font-semibold text-brand">{tag}</span>}
      </span>
      <span className="relative shrink-0 font-num font-semibold text-slate-900">{value}</span>
    </div>
  );
}

// 優先度：色だけでなく記号と文字で示す
export const PRIORITY = {
  ng: { label: "優先度 高", symbol: "✕", cls: "border-rose-200 bg-rose-50 text-rose-800" },
  warn: { label: "優先度 中", symbol: "!", cls: "border-amber-200 bg-amber-50 text-amber-900" },
  ok: { label: "OK", symbol: "✓", cls: "border-emerald-200 bg-emerald-50 text-emerald-800" },
  info: { label: "参考", symbol: "i", cls: "border-slate-200 bg-slate-50 text-slate-700" },
} as const;

export function PriorityBadge({ status }: { status: keyof typeof PRIORITY }) {
  const p = PRIORITY[status];
  return (
    <span className={`inline-flex shrink-0 items-center gap-1 whitespace-nowrap rounded-md border px-1.5 py-0.5 text-xs font-semibold ${p.cls}`}>
      <span aria-hidden>{p.symbol}</span>
      {p.label}
    </span>
  );
}
