import Link from "next/link";
import { CompetitorTable, MarketCompare, WHY_AFFORDABLE } from "@/components/ai/marketing";
import { ServicePlans } from "@/components/ai/service-plans";
import { ImprovementExample, ImprovementScope } from "@/components/ai/improvement-example";
import { Cta, Lead, Section } from "@/components/ai/lp";

export const metadata = { title: "料金 | AIススメル（求人サイトのAI検索対策）" };

export default function PricingPage() {
  return (
    <div className="-mt-8">
      <Section>
        <Lead line="無料診断だけのご利用もできます。有料プランへの自動切り替えはありません。" em="無料診断から、月額の改善支援まで" />
        <div className="mt-12">
          <ServicePlans />
        </div>
      </Section>

      <Section tone="soft">
        <Lead line="何をどこまで行うかは、契約前に決めます。" em="作業の範囲と、直し方の例" />
        <div className="mx-auto mt-10 max-w-4xl space-y-8">
          <ImprovementScope />
          <ImprovementExample />
        </div>
      </Section>

      <Section>
        <Lead line="AI検索対策の継続支援は、月20万円以上が一般的です。" em="改善作業を含めて、月10万円から始められます" />
        <div className="mt-10">
          <CompetitorTable />
        </div>
        <div className="mx-auto mt-14 max-w-4xl divide-y divide-slate-200 border-y-2 border-t-slate-800 border-b-slate-200">
          <details className="group">
            <summary className="flex cursor-pointer list-none items-center justify-between gap-3 py-6 text-lg font-bold text-slate-900">公開されている料金の中央値と比べたい<span aria-hidden className="text-slate-400 transition group-open:rotate-45">＋</span></summary>
            <div className="pb-6"><p className="mb-4 text-slate-700">各社で作業範囲は異なります。</p><MarketCompare /></div>
          </details>
          <details className="group">
            <summary className="flex cursor-pointer list-none items-center justify-between gap-3 py-6 text-lg font-bold text-slate-900">この価格でできる理由は？<span aria-hidden className="text-slate-400 transition group-open:rotate-45">＋</span></summary>
            <dl className="space-y-5 pb-6">{WHY_AFFORDABLE.map(([title, body]) => <div key={title}><dt className="font-bold text-slate-900">{title}</dt><dd className="mt-1 text-slate-700">{body}</dd></div>)}</dl>
          </details>
        </div>
      </Section>

      <Section tone="soft">
        <Lead em="内容と範囲を確認してから、正式なご依頼に" />
        <div className="mx-auto mt-10 grid max-w-5xl gap-8 sm:grid-cols-2">
          <div className="border-t-4 border-brand bg-white p-7"><p className="text-lg font-bold text-slate-900">月額プランで行う改善</p><p className="mt-3 leading-relaxed text-slate-700">CMSで編集できる既存ページを、変更案をご確認いただいてから改善します。大規模な実装や、範囲を超える制作は別途お見積もりです。</p></div>
          <div className="border-t-4 border-brand bg-white p-7"><p className="text-lg font-bold text-slate-900">ご相談から契約まで</p><p className="mt-3 leading-relaxed text-slate-700">2営業日以内に進め方とお見積もりをご連絡します。対象ページ・作業範囲・支払条件に合意してから正式なご依頼です。フォームの送信だけでは、契約や課金は発生しません。</p></div>
        </div>
        <div className="mx-auto mt-10 max-w-5xl space-y-2 text-sm text-slate-600">
          <p>AIの回答は毎回変わります。計測結果は目安で、紹介や応募の増加をお約束するものではありません。</p>
          <p>お支払いは銀行振込（請求書払い・前払い）です。キャンセル・解約の条件は<Link href="/ai/legal" className="font-bold text-brand underline underline-offset-4">特定商取引法に基づく表記</Link>をご覧ください。</p>
        </div>
        <Cta />
      </Section>
    </div>
  );
}
