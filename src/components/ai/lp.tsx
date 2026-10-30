import Link from "next/link";
import type { ReactNode } from "react";

// 紹介ページ（トップ・業種別ページ）で共用する帯・見出し・申し込みボタン

// 画面いっぱいの帯（背景色をページ幅いっぱいに敷く）
export function Band({ className = "", children, id }: { className?: string; children: ReactNode; id?: string }) {
  return (
    <section id={id} className={`ml-[calc(50%-50vw)] w-screen scroll-mt-16 ${className}`}>
      <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-20">{children}</div>
    </section>
  );
}

// 英字の小見出し＋中央寄せの見出し。強調したい語は青に
export function Head({ en, children, sub, light }: { en: string; children: ReactNode; sub?: string; light?: boolean }) {
  return (
    <div className="text-center">
      <p className={`text-sm font-bold ${light ? "text-blue-100" : "text-brand"}`}>{en}</p>
      <h2 className={`mt-3 text-[1.625rem] font-bold leading-snug sm:text-[2.25rem] ${light ? "text-white" : "text-slate-900"}`}>{children}</h2>
      {sub && <p className={`mx-auto mt-4 max-w-2xl leading-relaxed ${light ? "text-blue-50" : "text-slate-700"}`}>{sub}</p>}
    </div>
  );
}

export const em = "text-brand";

export function Cta() {
  return (
    <div className="mt-10 text-center">
      <Link href="/ai/apply" className="inline-flex min-h-14 w-full max-w-sm items-center justify-center rounded-lg bg-brand px-8 text-lg font-bold text-white shadow-sm transition hover:bg-brand-dark sm:w-auto">
        無料で診断を依頼する
      </Link>
      <p className="mt-3 text-sm text-slate-600">3営業日以内に、結果をメールでお届けします。</p>
    </div>
  );
}

