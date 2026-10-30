import Link from "next/link";
import { btn } from "./ds";

// 初回の2つの入口と、継続支援の2プランを分けて表示する。
const ENTRY_PLANS = [
  { title: "無料チェック", who: "まず今の状態を知りたい", price: "0円", unit: "", features: ["AIに紹介されているかを計測", "最初に直す3つのことをお届け"], href: "/ai/apply", cta: "無料で依頼する" },
  { title: "トライアル", who: "実際に1ページ直してみたい", price: "49,800円", unit: "1回", features: ["競合2社と比較して計測", "1ページを改善", "30日後に測り直して報告"], href: "/ai/apply?plan=trial", cta: "トライアルを相談する", recommended: true },
];

const MONTHLY_PLANS = [
  { title: "スタンダード", who: "既存ページの改善を続けたい", price: "100,000円", tax: "110,000円", features: ["毎月のAI計測・競合比較とレポート", "AIからの流入の確認（GA4）", "既存ページの改善・修正", "月1回の打ち合わせ"], href: "/ai/apply?plan=standard" },
  { title: "プレミアム", who: "新しいページの制作も任せたい", price: "200,000円", tax: "220,000円", features: ["スタンダードの内容すべて", "新規ページの企画・原稿作成", "CMSでのページ制作・公開", "制作ページの計測と改善"], href: "/ai/apply?plan=premium" },
];

export function ServicePlans() {
  return (
    <div>
      <div className="grid gap-4 md:grid-cols-2">
        {ENTRY_PLANS.map((p, i) => (
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
      <div className="mt-10">
        <h3 className="text-xl font-bold text-slate-900">継続支援は2プラン。1か月単位で選べます。</h3>
        <p className="mt-2 text-sm leading-relaxed text-slate-600">最低契約期間の縛りはありません。トライアルの結果や、必要な改善内容を見てご検討ください。</p>
        <div className="mt-5 grid gap-4 md:grid-cols-2">
          {MONTHLY_PLANS.map((p) => (
            <section key={p.title} className="flex min-w-0 flex-col rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
              <h4 className="text-xl font-bold text-slate-900">{p.title}</h4>
              <p className="mt-1 text-sm text-slate-600">{p.who}方に</p>
              <p className="mt-4 font-num text-[2.25rem] font-bold leading-none tracking-tight text-slate-900">{p.price}<span className="ml-1 text-base font-medium">/ 月</span></p>
              <p className="mt-2 text-sm text-slate-600">税込 {p.tax} / 月</p>
              <ul className="mt-5 space-y-2 border-t border-slate-200 pt-5">
                {p.features.map((f) => <li key={f} className="flex gap-2 text-slate-800"><span aria-hidden className="font-bold text-brand">✓</span>{f}</li>)}
              </ul>
              <div className="mt-auto pt-6"><Link href={p.href} className={`${btn("secondary", "lg")} w-full`}>このプランを相談する</Link></div>
            </section>
          ))}
        </div>
        <p className="mt-4 text-sm leading-relaxed text-slate-600">対象は1サービスです。改善・制作するページの本数、原稿量、修正回数、CMSの作業範囲を契約前に見積書で合意します。外部媒体への掲載、取材・撮影、独自デザイン、大規模な開発は含みません。GA4未導入の場合は、確認できる範囲をご案内します。</p>
      </div>
      <p className="mt-4 text-sm leading-relaxed text-slate-600">
        価格は税別です（トライアルは税込54,780円）。継続支援は1か月契約・毎月更新で、当月末までのメール連絡で翌月からの更新を停止できます。無料チェック・トライアルから継続支援への自動切り替えはありません。
      </p>
    </div>
  );
}
