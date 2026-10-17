import Link from "next/link";
import { PlanTable } from "@/components/ai/plan-table";

export const metadata = { title: "料金プラン | AI検索チェック" };

export default function PricingPage() {
  return (
    <div className="space-y-8">
      <div className="text-center">
        <h1 className="text-3xl font-bold text-slate-900">料金プラン</h1>
        <p className="mt-2 text-sm text-slate-600">まずはフリープランで1回計測してみてください。有料プランはいつでも解約でき、契約期間の縛りはありません。</p>
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
      <ul className="mx-auto max-w-2xl list-disc space-y-1 pl-5 text-xs text-slate-500">
        <li>お支払いはクレジットカード（Stripe）です。毎月、申し込み日と同じ日に自動で更新されます。</li>
        <li>解約は契約管理画面からいつでもでき、その月の終わりまでご利用いただけます。日割りでの返金はありません。</li>
        <li>AIの回答は毎回変わります。計測結果は目安で、紹介や来店の増加をお約束するものではありません。</li>
        <li>
          <Link href="/ai/legal" className="underline">
            特定商取引法に基づく表記
          </Link>
        </li>
      </ul>
    </div>
  );
}
