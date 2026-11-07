import Link from "next/link";
import { Stat } from "./lp";

// 料金はこの3つ。1枚に「誰向け・いくら・何をするか」を並べ、細かい条件はカードの下にまとめる
const PLANS = [
  { title: "無料診断", who: "まず今の状態を知りたい方に", price: "0", unit: "円", features: ["AIに紹介されているかを計測", "最初に直す3つのことをお届け"], href: "/ai#apply", cta: "無料で診断する" },
  { title: "基本プラン", who: "毎月、既存ページを直していきたい方に", price: "10", unit: "万円／月", features: ["毎月の計測（2つのAI・競合3社と比較）", "優先課題の整理", "既存ページの改善 月2件", "AIから来た人数のレポート（GA4）", "月1回の報告・打ち合わせ"], href: "/ai/apply?plan=basic", cta: "基本プランを相談する" },
  { title: "制作プラン", who: "新しいページも作って攻めたい方に", price: "20", unit: "万円／月", features: ["基本プランの内容すべて", "選ばれる理由を伝えるページの制作 月2本（各3,000字まで）", "既存ページの改善を月4件に", "求職者の疑問に答える「よくある質問」の作成"], href: "/ai/apply?plan=production", cta: "制作プランを相談する", recommended: true },
];

export function ServicePlans() {
  return (
    <div>
      <div className="grid gap-3 lg:grid-cols-3">
        {PLANS.map((p) => {
          const em = p.recommended;
          return (
            <section key={p.title} className={`flex min-w-0 flex-col rounded-md p-7 ${em ? "bg-brand text-white" : "bg-[#F1F6FE] text-slate-900"}`}>
              <h3 className="flex items-center gap-2 text-xl font-bold">
                {p.title}
                {em && <span className="rounded-sm bg-white px-2 py-0.5 text-xs font-bold text-brand">おすすめ</span>}
              </h3>
              <p className={`mt-1 text-sm ${em ? "text-blue-100" : "text-slate-600"}`}>{p.who}</p>
              <div className="mt-5"><Stat value={p.price} unit={p.unit} className={em ? "text-white" : "text-slate-900"} /></div>
              <ul className={`mt-6 space-y-2 border-t pt-6 text-sm leading-relaxed ${em ? "border-white/30 text-blue-50" : "border-slate-300 text-slate-700"}`}>
                {p.features.map((f) => (
                  <li key={f} className="flex gap-2"><span aria-hidden className={em ? "text-blue-200" : "text-brand"}>・</span>{f}</li>
                ))}
              </ul>
              <div className="mt-auto pt-8">
                <Link href={p.href} className={`group flex min-h-13 items-center justify-between rounded-md px-5 font-bold transition ${em ? "bg-white text-brand hover:bg-blue-50" : "bg-slate-900 text-white hover:bg-black"}`}>
                  {p.cta}
                  <span aria-hidden className="transition group-hover:translate-x-1">→</span>
                </Link>
              </div>
            </section>
          );
        })}
      </div>
      <p className="mt-4 text-xs leading-relaxed text-slate-500">
        価格は税別です（税込：基本プラン110,000円/月、制作プラン220,000円/月）。1か月単位の契約で、最低契約期間はありません。当月20日までにご連絡いただくと当月末で終了します。制作プランの制作本数・文字数・実装範囲は契約前に確定し、それを超える作業は別途お見積もりです。
      </p>
    </div>
  );
}
