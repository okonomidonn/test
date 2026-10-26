import Link from "next/link";
import { PlanTable } from "@/components/ai/plan-table";

export const metadata = { title: "料金プラン | ススメル" };

// 計測・改善を代わりに行う「おまかせ」。申し込みは相談から（Stripeの自動課金ではない）。法人向けのため税別
const SERVICES = [
  {
    step: "1",
    title: "無料チェック",
    price: "0円",
    unit: "",
    body: "主な質問でAIに紹介されているかを計測し、公式サイトを自動で診断します。",
    items: ["ChatGPTでの計測（10問）", "公式サイトの自動診断", "結果の共有"],
  },
  {
    step: "2",
    title: "GEO診断・改善トライアル",
    price: "49,800円",
    unit: "（1回・30日間）",
    body: "現状を数字で確かめ、改善を1つ実際に行い、30日後に同じ条件で測り直します。",
    items: ["ChatGPT・Geminiで各10問を計測", "競合2社との比較（紹介された回数・引用されたページ）", "直す点を3つ特定", "既存ページ1ページを改善", "30日後に再計測して報告"],
    highlight: true,
  },
  {
    step: "3",
    title: "継続支援",
    price: "月98,000円〜",
    unit: "",
    body: "毎月AIで計測し、紹介されなかった質問に答えるページを作ります。トライアル後に、作業量を決めてお見積もりします。",
    items: ["毎月の計測とレポート", "ページの作成・書き換え（月4ページ〜）", "構造化データの保守", "月1回の打ち合わせ"],
  },
];

export default function PricingPage() {
  return (
    <div className="space-y-12">
      <div className="text-center">
        <h1 className="text-3xl font-bold text-slate-900">料金プラン</h1>
        <p className="mt-2 text-base text-slate-700">計測から改善まで任せる「おまかせ」と、自分で計測する「ツール」があります。</p>
      </div>

      <section className="space-y-4">
        <div>
          <h2 className="text-xl font-bold text-slate-900">おまかせ（計測から改善まで代行）</h2>
          <p className="mt-1 text-sm text-slate-700">法人向け・税別です。まず無料チェック、次に有料のトライアル、効果を確かめてから継続支援へ進みます。</p>
        </div>
        <ol className="grid gap-4 lg:grid-cols-3">
          {SERVICES.map((sv) => (
            <li key={sv.title} className={`flex min-w-0 flex-col rounded-2xl border bg-white p-6 ${sv.highlight ? "border-2 border-brand" : "border-slate-200"}`}>
              <div className="flex items-center gap-2">
                <span className="grid h-7 w-7 place-items-center rounded-full bg-slate-900 font-num text-sm font-semibold text-white">{sv.step}</span>
                {sv.highlight && <span className="rounded-md bg-brand-soft px-2 py-0.5 text-sm font-bold text-brand">はじめての方へ</span>}
              </div>
              <h3 className="mt-3 text-lg font-bold text-slate-900">{sv.title}</h3>
              <p className="mt-1 font-num text-3xl font-semibold text-slate-900">
                {sv.price}
                <span className="font-sans text-sm font-normal text-slate-600">{sv.unit}</span>
              </p>
              <p className="mt-3 text-sm text-slate-700">{sv.body}</p>
              <ul className="mt-4 space-y-1.5 border-t border-slate-200 pt-4 text-sm text-slate-800">
                {sv.items.map((it) => (
                  <li key={it} className="flex gap-2">
                    <span className="text-brand" aria-hidden>
                      ✓
                    </span>
                    {it}
                  </li>
                ))}
              </ul>
            </li>
          ))}
        </ol>
        <p className="text-sm text-slate-600">トライアルに含まないもの：新しいページの制作、外部媒体への掲載、大規模な実装。継続支援は最低3か月、以降は1か月ごとの更新です。</p>
        <div className="flex flex-wrap gap-3">
          <Link href="/ai/apply?plan=trial" className="inline-block rounded-lg bg-brand px-6 py-3 font-bold text-white hover:bg-brand-dark">
            トライアルを申し込む
          </Link>
          <Link href="/ai/apply" className="inline-block rounded-lg border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-800 hover:bg-slate-50">
            まず無料チェック
          </Link>
        </div>
      </section>

      <section className="space-y-4">
        <div>
          <h2 className="text-xl font-bold text-slate-900">ツール（自分で使う）</h2>
          <p className="mt-1 text-sm text-slate-700">自分で計測・診断したい方向け。税込です。いつでも解約でき、契約期間の縛りはありません。</p>
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

      <ul className="mx-auto max-w-2xl list-disc space-y-1 pl-5 text-sm text-slate-700">
        <li>ツールのお支払いはクレジットカード（Stripe）です。毎月、申し込み日と同じ日に自動で更新されます。</li>
        <li>ツールの解約は契約管理画面からいつでもでき、その月の終わりまでご利用いただけます。日割りでの返金はありません。</li>
        <li>AIの回答は毎回変わります。計測結果は目安で、紹介や問い合わせの増加をお約束するものではありません。</li>
        <li>
          <Link href="/ai/legal" className="underline">
            特定商取引法に基づく表記
          </Link>
        </li>
      </ul>
    </div>
  );
}
