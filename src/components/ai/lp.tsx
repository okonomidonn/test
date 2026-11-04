import Link from "next/link";
import type { ReactNode } from "react";

// 紹介ページ（トップ・業種別ページ・資料ページ）で共用する帯・見出し・申し込みボタン・よくある質問
// 見た目のきまり：角の丸みは rounded-lg の1種類、色は青1色＋グレー、見出しは左寄せ。
// 「英語の小見出し」「意味のない番号」「チェックマークの箇条書き」は使わない。

// 画面いっぱいの帯。帯どうしは細い線で区切る
export function Band({ className = "bg-white", children, id }: { className?: string; children: ReactNode; id?: string }) {
  return (
    <section id={id} className={`ml-[calc(50%-50vw)] w-screen scroll-mt-16 border-t border-slate-200 ${className}`}>
      <div className="mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20">{children}</div>
    </section>
  );
}

export function Head({ children, sub, light }: { children: ReactNode; sub?: ReactNode; light?: boolean }) {
  return (
    <div className="max-w-3xl">
      <h2 className={`text-[1.5rem] font-bold leading-snug sm:text-[2rem] ${light ? "text-white" : "text-slate-900"}`}>{children}</h2>
      {sub && <p className={`mt-4 leading-relaxed ${light ? "text-slate-300" : "text-slate-600"}`}>{sub}</p>}
    </div>
  );
}

export function Cta({ note = "3営業日以内に、結果をメールでお送りします。" }: { note?: string }) {
  return (
    <div className="mt-10 flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:gap-5">
      <Link href="/ai/apply" className="inline-flex min-h-13 w-full items-center justify-center rounded-lg bg-brand px-7 font-bold text-white transition hover:bg-brand-dark sm:w-auto">
        無料で診断を依頼する
      </Link>
      <p className="text-sm text-slate-600">{note}</p>
    </div>
  );
}

export function Faq({ items }: { items: [string, string][] }) {
  return (
    <div className="mt-8 divide-y divide-slate-200 border-y border-slate-200">
      {items.map(([q, a]) => (
        <details key={q} className="group">
          <summary className="flex cursor-pointer list-none items-start justify-between gap-4 py-5 font-bold text-slate-900">
            {q}
            <span aria-hidden className="shrink-0 text-slate-400 transition group-open:rotate-45">＋</span>
          </summary>
          <p className="max-w-3xl pb-6 leading-relaxed text-slate-700">{a}</p>
        </details>
      ))}
    </div>
  );
}

// 「AIに紹介された」実例の数字（運営する求人サイト zaito の GA4）
export const ZAITO = {
  users: "14.5%",
  events: "51.6%",
  note: "GA4、2026年9月24日〜10月21日の28日間（469人中68人、95件中49件）。キーイベントは「応募ページへ進む」などの操作数で、応募完了率ではありません。サイト1件の数字で、効果を保証するものではありません。",
};
