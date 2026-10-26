import Link from "next/link";
import { btn } from "./ds";

const PLANS = [
  { title: "無料チェック", purpose: "まず現状を知りたい方へ", price: "0", unit: "円", tax: "費用はかかりません", description: "AIでの紹介状況と、最初に直す3つのことをお届け。", features: ["ChatGPTで10問を計測", "公式サイトの自動診断", "3営業日以内に結果をメールで送付"], href: "/ai/apply", cta: "無料チェックを依頼する", term: "登録不要・契約の義務なし" },
  { title: "診断・改善トライアル", purpose: "実際にサイトを直してみたい方へ", price: "49,800", unit: "円 / 1回", tax: "税別・税込54,780円", description: "現状を調べて1ページ改善。30日後に変化を確認。", features: ["ChatGPT・Gemini各10問と競合2社の比較", "直す点を3つ特定し、既存1ページを改善", "30日後に同じ条件で再計測・報告"], href: "/ai/apply?plan=trial", cta: "トライアルについて相談する", term: "30日間・1回のご依頼", recommended: true },
  { title: "継続支援", purpose: "改善を毎月続けたい方へ", price: "98,000", unit: "円〜 / 月", tax: "税別・税込107,800円〜 / 月", description: "毎月の計測とページ改善を、継続してお手伝い。", features: ["毎月の計測とレポート", "月4ページ〜の作成・書き換えと技術保守", "月1回の打ち合わせ"], href: "/ai/apply?plan=trial", cta: "まずはトライアルで相談する", term: "最低3か月・総額294,000円〜（税別）" },
];

export function ServicePlans({ compact = false }: { compact?: boolean }) {
  return <div className="grid gap-4 lg:grid-cols-3">
    {PLANS.map((p, i) => <section key={p.title} className={`flex min-w-0 flex-col rounded-xl border p-5 sm:p-6 ${p.recommended ? "border-blue-300 bg-blue-50/60" : "border-slate-200 bg-white"}`}>
      {!compact && <p className="text-sm font-semibold text-brand">0{i + 1} <span className="ml-1 text-slate-600">{p.purpose}</span></p>}
      <h3 className={`${compact ? "" : "mt-4"} text-xl font-bold text-slate-900`}>{p.title}</h3>
      <p className="mt-3 font-num text-[2rem] font-bold leading-tight tracking-tight text-slate-900">{p.price}<span className="ml-1 text-sm font-medium tracking-normal">{p.unit}</span></p>
      <p className="mt-1 text-xs text-slate-600">{p.tax}</p>
      <p className={`mt-4 text-slate-700 ${compact ? "text-sm" : ""}`}>{p.description}</p>
      {!compact && <ul className="mt-5 space-y-3 border-t border-slate-200 pt-5">{p.features.map(f => <li key={f} className="flex gap-2 text-sm text-slate-800"><span aria-hidden className="font-bold text-brand">✓</span>{f}</li>)}</ul>}
      <p className={`${compact ? "mt-3 text-xs" : "mt-5 text-sm"} text-slate-600`}>{p.term}</p>
      {!compact && p.title === "継続支援" && <p className="mt-1 text-xs text-slate-600">最低総額：税込323,400円〜。以降は1か月ごと。</p>}
      {!compact && <div className="mt-auto pt-6"><Link href={p.href} className={`${btn(p.recommended ? "primary" : "secondary")} min-h-11 w-full !h-auto !whitespace-normal px-2 py-3 text-center leading-snug`}>{p.cta}</Link></div>}
    </section>)}
  </div>;
}
