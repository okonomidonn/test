import Link from "next/link";
import { PlanTable } from "@/components/ai/plan-table";

export const metadata = { title: "セルフ版ツール | ススメル" };

// 自分で計測したい人向けのツール。代行（料金ページ）とは分けて見せる
export default function ToolPage() {
  return (
    <div className="space-y-8">
      <div className="max-w-2xl">
        <p className="font-semibold text-brand">自分で計測したい方へ</p>
        <h1 className="mt-2 text-3xl font-black text-slate-900">セルフ版ツール</h1>
        <p className="mt-3 text-slate-700">
          サービスを登録すると、見込み客がAIに聞きそうな質問を自動で作り、ChatGPTで紹介されているかを計測します。サイト診断と直すところ、報告書の出力も使えます。税込・いつでも解約できます。
        </p>
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
      <ul className="max-w-2xl list-disc space-y-1 pl-5 text-sm text-slate-600">
        <li>お支払いはクレジットカード（Stripe）です。毎月、申し込み日と同じ日に自動で更新されます。</li>
        <li>解約は契約管理画面からいつでもでき、その月の終わりまでご利用いただけます。日割りでの返金はありません。</li>
        <li>
          直すところまで任せたい方は、
          <Link href="/ai/pricing" className="underline">
            おまかせの料金
          </Link>
          をご覧ください。
        </li>
      </ul>
    </div>
  );
}
