import Link from "next/link";
import { btn } from "./ds";

// 料金は3つだけ。1枚に「誰向け・いくら・何をするか」を並べ、細かい条件はカードの下にまとめる
const PLANS = [
  { title: "無料チェック", who: "まず今の状態を知りたい", price: "0円", unit: "", features: ["AIに紹介されているかを計測", "最初に直す3つのことをお届け"], href: "/ai/apply", cta: "無料で依頼する" },
  { title: "トライアル", who: "実際に1ページ直してみたい", price: "49,800円", unit: "1回", features: ["競合2社と比較して計測", "1ページを改善", "30日後に測り直して報告"], href: "/ai/apply?plan=trial", cta: "トライアルを相談する", recommended: true },
  { title: "継続支援", who: "毎月改善を続けたい", price: "98,000円〜", unit: "月", features: ["毎月の計測とレポート", "AIからの流入レポート（GA4）", "毎月4ページ〜を作成・改善", "月1回の打ち合わせ"], href: "/ai/apply?plan=trial", cta: "まずトライアルから" },
];

export function ServicePlans() {
  return (
    <div>
      <div className="grid gap-4 md:grid-cols-3">
        {PLANS.map((p, i) => (
          <section key={p.title} className={`relative flex min-w-0 flex-col rounded-xl border p-5 sm:p-6 ${p.recommended ? "border-brand bg-blue-50/60" : "border-slate-200 bg-white"}`}>
            {p.recommended && <span className="absolute -top-3 left-5 rounded-full bg-brand px-3 py-0.5 text-xs font-bold text-white">おすすめ</span>}
            <p className="font-num text-sm font-semibold text-slate-500">STEP {i + 1}</p>
            <h3 className="mt-1 text-xl font-bold text-slate-900">{p.title}</h3>
            <p className="mt-1 text-sm text-slate-600">{p.who}方に</p>
            <p className="mt-4 font-num text-[2.25rem] font-bold leading-none tracking-tight text-slate-900">
              {p.price}
              {p.unit && <span className="ml-1 text-base font-medium tracking-normal text-slate-600">/ {p.unit}</span>}
            </p>
            <ul className="mt-5 space-y-2 border-t border-slate-200 pt-5">
              {p.features.map((f) => (
                <li key={f} className="flex gap-2 text-slate-800">
                  <span aria-hidden className="font-bold text-brand">✓</span>
                  {f}
                </li>
              ))}
            </ul>
            <div className="mt-auto pt-6">
              <Link href={p.href} className={`${btn(p.recommended ? "primary" : "secondary", "lg")} w-full`}>
                {p.cta}
              </Link>
            </div>
          </section>
        ))}
      </div>
      <p className="mt-4 text-sm leading-relaxed text-slate-600">
        価格は税別です（税込：トライアル54,780円、継続支援107,800円〜/月）。継続支援は最低3か月です。無料チェックから有料プランへの自動切り替えはありません。
      </p>
    </div>
  );
}
