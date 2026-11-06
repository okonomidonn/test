import Link from "next/link";
import type { ReactNode } from "react";

// 紹介ページ（トップ・料金・資料ページ）で共用する部品。
// 見た目のきまり（打ち合わせ資料と同じ）：白地に青1色、見出しは「ふつうの一文」＋「青い太字の一文」を中央に、
// 数字は Montserrat、線で区切る、角の丸みは小さく、飾りの図形やアイコンは使わない。

// 画面いっぱいの帯
export function Section({ children, id, tone = "white" }: { children: ReactNode; id?: string; tone?: "white" | "soft" | "blue" | "navy" }) {
  const bg = tone === "soft" ? "bg-[#F5F8FD]" : tone === "blue" ? "bg-brand text-white" : tone === "navy" ? "bg-[linear-gradient(160deg,#0A2463_0%,#123A9C_100%)] text-white" : "bg-white";
  return (
    <section id={id} className={`ml-[calc(50%-50vw)] w-screen scroll-mt-16 ${bg}`}>
      <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">{children}</div>
    </section>
  );
}

// 見出し：ふつうの一文＋青い太字の一文（中央寄せ）。見出しの上の小さなラベルは付けない
export function Lead({ line, em, light }: { line?: ReactNode; em: ReactNode; light?: boolean }) {
  return (
    <div className="mx-auto max-w-4xl text-center [word-break:auto-phrase]">
      {line && <p className={`text-base leading-relaxed sm:text-xl ${light ? "text-blue-50" : "text-slate-600"}`}>{line}</p>}
      <h2 className={`${line ? "mt-2" : ""} text-balance text-[1.625rem] font-bold leading-snug sm:text-[2.25rem] ${light ? "text-white" : "text-[#1E66E0]"}`}>{em}</h2>
    </div>
  );
}

// 左寄せの見出し（大きな見出し＋説明文）。トップページでは中央寄せの Lead と混ぜて使う
export function Head({ title, desc, light, center }: { title: ReactNode; desc?: ReactNode; light?: boolean; center?: boolean }) {
  return (
    <div className={`[word-break:auto-phrase] ${center ? "mx-auto max-w-3xl text-center" : "max-w-3xl"}`}>
      <h2 className={`text-balance text-[1.75rem] font-bold leading-snug sm:text-[2.5rem] ${light ? "text-white" : "text-slate-900"}`}>{title}</h2>
      {desc && <p className={`mt-4 leading-relaxed sm:text-lg ${light ? "text-blue-100" : "text-slate-600"}`}>{desc}</p>}
    </div>
  );
}

// 申し込みへの帯（ページの途中に挟む）
export function CtaBand() {
  return (
    <section className="ml-[calc(50%-50vw)] w-screen bg-[linear-gradient(110deg,#0A2463_0%,#1D4ED8_100%)] text-white">
      <div className="mx-auto flex max-w-6xl flex-col items-start justify-between gap-6 px-4 py-12 sm:px-6 md:flex-row md:items-center">
        <div>
          <p className="text-2xl font-bold sm:text-3xl">まずは、今の紹介のされ方を知る。</p>
          <p className="mt-2 text-blue-100">求人サイトのURLを送るだけ。費用0円、3営業日以内に結果をお送りします。</p>
        </div>
        <Link href="/ai/apply" className="inline-flex min-h-14 w-full shrink-0 items-center justify-center gap-3 rounded-md bg-white px-10 text-lg font-bold text-brand shadow-[0_8px_24px_rgba(0,0,0,0.18)] transition hover:bg-blue-50 md:w-auto">無料で診断する<span aria-hidden>→</span></Link>
      </div>
    </section>
  );
}

export function Num({ children, className = "" }: { children: ReactNode; className?: string }) {
  return <span className={`font-num font-extrabold ${className}`}>{children}</span>;
}

export function Cta({ light }: { light?: boolean }) {
  return (
    <div className="mt-12 flex flex-col items-center gap-3 sm:flex-row sm:justify-center">
      <Link href="/ai/apply" className={`inline-flex min-h-13 w-full items-center justify-center rounded-md px-8 font-bold transition sm:w-auto ${light ? "bg-white text-brand hover:bg-blue-50" : "bg-brand text-white hover:bg-brand-dark"}`}>
        無料で診断する
      </Link>
    </div>
  );
}

export function Faq({ items }: { items: [string, string][] }) {
  return (
    <div className="mx-auto mt-10 max-w-4xl divide-y divide-slate-200 border-y-2 border-t-slate-800 border-b-slate-200">
      {items.map(([q, a]) => (
        <details key={q} className="group">
          <summary className="flex cursor-pointer list-none items-start justify-between gap-4 py-6 text-base font-bold text-slate-900 sm:text-lg">
            {q}
            <span aria-hidden className="shrink-0 text-slate-400 transition group-open:rotate-45">＋</span>
          </summary>
          <p className="max-w-3xl pb-6 leading-relaxed text-slate-700">{a}</p>
        </details>
      ))}
    </div>
  );
}

// 運営する求人サイト zaito の実例（GA4）
export const ZAITO = {
  period: "2026年10月8日〜11月4日の28日間",
  users: { rate: "35.5%", n: "1,390人中494人" },
  events: { rate: "48.7%", n: "610件中297件" },
  growthUsers: "68人 → 494人",
  growthEvents: "49件 → 297件",
  signups: "143人中60人",
  note: "GA4。ChatGPTはchatgpt.comからの分のみ。行動（GA4のキーイベント）は「応募ページへ進む」「登録」などの数で、応募完了の数ではありません。この期間はInstagram広告から来た人も含みます。「約7倍」「約6倍」は9月24日〜10月21日の28日間との比較で、サイト公開直後の伸びも含みます。サイト1件の数字で、同じ効果を保証するものではありません。",
};
