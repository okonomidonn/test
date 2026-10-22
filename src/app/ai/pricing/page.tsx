import Link from "next/link";
import { PlanTable } from "@/components/ai/plan-table";

export const metadata = { title: "料金プラン | AI検索チェック" };

// 計測・改善を代わりに行う「おまかせ」。申し込みは相談から（Stripeの自動課金ではない）
const SERVICES = [
  {
    title: "店舗サポート",
    target: "個人店・小さな店舗",
    rows: [
      ["初期整備", "39,800円（1回）"],
      ["ライト", "月4,980円"],
      ["スタンダード", "月9,800円"],
    ],
    note: "営業情報やメニューを公式サイト・Googleマップ・グルメサイトでそろえ、毎月の更新を代行します。縛りなし。",
  },
  {
    title: "Webおまかせ",
    target: "中小企業のWebサービス",
    rows: [
      ["詳しい診断＋報告会", "98,000円（1回）"],
      ["初期整備", "198,000円〜（見積もり）"],
      ["おまかせ 月4ページ", "月98,000円"],
      ["おまかせ 月8ページ", "月198,000円"],
    ],
    note: "毎月AIで計測し、紹介されなかった質問に答えるページを作ります。最低3か月。",
  },
] as const;

export default function PricingPage() {
  const email = process.env.LEGAL_EMAIL;
  return (
    <div className="space-y-12">
      <div className="text-center">
        <h1 className="text-3xl font-bold text-slate-900">料金プラン</h1>
        <p className="mt-2 text-base text-slate-700">自分で計測する「ツール」と、計測から改善まで任せる「おまかせ」があります。</p>
      </div>

      <section className="space-y-4">
        <div>
          <h2 className="text-xl font-bold text-slate-900">ツール（自分で使う）</h2>
          <p className="mt-1 text-sm text-slate-700">お店・Webサービス共通。いつでも解約でき、契約期間の縛りはありません。</p>
        </div>
        <PlanTable
          action={(p) => (
            <Link
              href="/ai/signup"
              className={`block rounded-lg px-4 py-2.5 text-center text-sm font-semibold ${p.key === "STARTER" ? "bg-brand text-white hover:bg-brand-dark" : "border border-slate-200 text-slate-700 hover:bg-slate-50"}`}
            >
              {p.priceYen ? "登録して申し込む" : "無料で始める"}
            </Link>
          )}
        />
      </section>

      <section className="space-y-4">
        <div>
          <h2 className="text-xl font-bold text-slate-900">おまかせ（代行）</h2>
          <p className="mt-1 text-sm text-slate-700">計測・改善の作業をまとめてお任せいただけます。内容を伺ってからお見積もりします（税別）。</p>
        </div>
        <div className="grid gap-4 md:grid-cols-2">
          {SERVICES.map((s) => (
            <div key={s.title} className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
              <p className="text-sm font-semibold text-brand">{s.target}</p>
              <h3 className="mt-1 text-lg font-bold text-slate-900">{s.title}</h3>
              <dl className="mt-4 divide-y divide-slate-100">
                {s.rows.map(([k, v]) => (
                  <div key={k} className="flex justify-between gap-4 py-2 text-sm">
                    <dt className="text-slate-700">{k}</dt>
                    <dd className="font-bold text-slate-900">{v}</dd>
                  </div>
                ))}
              </dl>
              <p className="mt-3 text-sm text-slate-700">{s.note}</p>
            </div>
          ))}
        </div>
        <div className="text-center">
          {email ? (
            <a href={`mailto:${email}?subject=${encodeURIComponent("おまかせプランの相談")}`} className="inline-block rounded-lg bg-slate-900 px-6 py-3 font-semibold text-white hover:bg-slate-800">
              おまかせについて相談する
            </a>
          ) : (
            <p className="text-sm text-slate-700">ご相談は担当者までご連絡ください。</p>
          )}
        </div>
      </section>

      <ul className="mx-auto max-w-2xl list-disc space-y-1 pl-5 text-sm text-slate-700">
        <li>ツールのお支払いはクレジットカード（Stripe）です。毎月、申し込み日と同じ日に自動で更新されます。</li>
        <li>ツールの解約は契約管理画面からいつでもでき、その月の終わりまでご利用いただけます。日割りでの返金はありません。</li>
        <li>AIの回答は毎回変わります。計測結果は目安で、紹介や来店・問い合わせの増加をお約束するものではありません。</li>
        <li>
          <Link href="/ai/legal" className="underline">
            特定商取引法に基づく表記
          </Link>
        </li>
      </ul>
    </div>
  );
}
