import Link from "next/link";
import { btn } from "./ds";

// 料金はこの4つ。1枚に「誰向け・いくら・何をするか」を並べ、細かい条件はカードの下にまとめる
const PLANS = [
  { title: "無料チェック", who: "まず今の状態を知りたい", price: "0円", unit: "", features: ["AIに紹介されているかを計測", "最初に直す3つのことをお届け"], href: "/ai/apply", cta: "無料で依頼する" },
  { title: "トライアル", who: "まず1ページ直して確かめたい", price: "49,800円", unit: "1回", features: ["診断と競合2社の比較", "既存ページを1ページ改善", "30日後に測り直して報告"], href: "/ai/apply?plan=trial", cta: "トライアルを相談する", recommended: true },
  { title: "基本プラン", who: "毎月、既存ページを直していきたい", price: "100,000円", unit: "月", features: ["毎月の計測（30問×2つのAI・競合3社）", "優先課題の整理", "既存ページの改善 月2件", "AIからの流入レポート（GA4）", "月1回の報告・打ち合わせ"], href: "/ai/apply?plan=basic", cta: "基本プランを相談する" },
  { title: "制作プラン", who: "新しいページも作って攻めたい", price: "200,000円", unit: "月", features: ["基本プランの内容すべて", "新規ページの制作 月2本（各3,000字まで）", "既存ページの改善を月4件に", "FAQ・構造化データの作成"], href: "/ai/apply?plan=production", cta: "制作プランを相談する" },
];

export function ServicePlans() {
  return (
    <div>
      <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        {PLANS.map((p, i) => (
          <section key={p.title} className={`relative flex min-w-0 flex-col rounded-xl border p-5 sm:p-6 ${p.recommended ? "border-brand bg-blue-50/60" : "border-slate-200 bg-white"}`}>
            {p.recommended && <span className="absolute -top-3 left-5 rounded-full bg-brand px-3 py-0.5 text-xs font-bold text-white">おすすめ</span>}
            <p className="font-num text-sm font-semibold text-slate-500">{i < 2 ? `STEP ${i + 1}` : "月額プラン"}</p>
            <h3 className="mt-1 text-xl font-bold text-slate-900">{p.title}</h3>
            <p className="mt-1 text-sm text-slate-600">{p.who}方に</p>
            <p className="mt-4 font-num text-[2rem] font-bold leading-none tracking-tight text-slate-900">
              {p.price}
              {p.unit && <span className="ml-1 text-base font-medium tracking-normal text-slate-600">/ {p.unit}</span>}
            </p>
            <ul className="mt-5 space-y-2 border-t border-slate-200 pt-5">
              {p.features.map((f) => (
                <li key={f} className="flex gap-2 text-sm text-slate-800">
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
        価格は税別です（税込：トライアル54,780円、基本プラン110,000円/月、制作プラン220,000円/月）。月額プランは1か月単位の契約で、最低期間はありません。当月20日までにご連絡いただくと当月末で終了します。制作プランの制作本数・文字数・実装範囲は契約前に確定し、それを超える作業は別途お見積もりです。
      </p>
      <p className="mt-3 text-sm leading-relaxed text-slate-600">既存ページの改善1件は、1つのURLの文章・見出し・FAQの修正からCMSでの反映まで。月額プランから直接ご相談いただくこともできます。<Link href="/ai/pricing#scope" className="ml-1 font-semibold text-brand underline underline-offset-4">作業範囲と対象外の内容を見る</Link></p>
    </div>
  );
}
