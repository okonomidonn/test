import Link from "next/link";
import { CompetitorTable, MarketCompare, WHY_AFFORDABLE } from "@/components/ai/marketing";
import { ServicePlans } from "@/components/ai/service-plans";
import { ImprovementExample, ImprovementScope } from "@/components/ai/improvement-example";
import { CtaBand, Head, Section } from "@/components/ai/lp";

export const metadata = { title: "料金 | AIススメル（求人サイトのAI検索対策）", description: "AIススメルの料金。無料診断0円、基本プラン月10万円、制作プラン月20万円（税別）。最低契約期間はありません。", alternates: { canonical: "/ai/pricing" } };

// トップと同じ作り：濃い青の帯で始め、見出しは左寄せ、カードは薄い青、最後に申し込みの帯
export default function PricingPage() {
  return (
    <div className="-mb-8 -mt-8">
      <section className="relative ml-[calc(50%-50vw)] w-screen overflow-hidden bg-brand text-white">
        <div className="relative mx-auto max-w-6xl px-4 py-14 sm:px-6 sm:py-20">
          <p className="palt text-sm font-bold text-blue-200">料金</p>
          <h1 className="mt-4 text-[2.25rem] font-black leading-[1.25] sm:text-[3.25rem]">まずは無料診断から。</h1>
          <p className="palt mt-5 text-lg font-bold text-blue-50 sm:text-xl">改善のご依頼は、結果を見てから。最低契約期間はありません。</p>
        </div>
      </section>

      <Section>
        <ServicePlans />
      </Section>

      <Section id="scope-section" tone="soft">
        <div className="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
          <Head title={<>何をどこまで行うかは、<br />契約前に決めます。</>} />
        </div>
        <div className="mt-10 space-y-10">
          <ImprovementScope />
          <ImprovementExample />
        </div>
      </Section>

      <Section>
        <Head title={<>改善作業を含めて、<br />月10万円から。</>} desc="AI検索対策の継続支援は、月20万円以上が一般的です。" />
        <div className="mt-10 rounded-md bg-[#F5F8FD] p-2 sm:p-3">
          <CompetitorTable />
        </div>
        <div className="mt-12 rounded-md bg-[#F5F8FD] px-6 sm:px-8">
          <details className="group border-b border-slate-200">
            <summary className="palt flex cursor-pointer list-none items-center justify-between gap-3 py-6 text-lg font-bold text-slate-900">公開されている料金の中央値と比べたい<span aria-hidden className="text-slate-400 transition group-open:rotate-45">＋</span></summary>
            <div className="pb-6"><MarketCompare /></div>
          </details>
          <details className="group">
            <summary className="palt flex cursor-pointer list-none items-center justify-between gap-3 py-6 text-lg font-bold text-slate-900">この価格でできる理由は？<span aria-hidden className="text-slate-400 transition group-open:rotate-45">＋</span></summary>
            <dl className="space-y-5 pb-6">{WHY_AFFORDABLE.map(([title, body]) => <div key={title}><dt className="font-bold text-slate-900">{title}</dt><dd className="mt-1 text-slate-700">{body}</dd></div>)}</dl>
          </details>
        </div>
      </Section>

      <Section tone="soft">
        <Head title="ご相談から契約まで" />
        <div className="mt-10 grid gap-3 lg:grid-cols-2">
          <div className="rounded-md bg-white p-7"><p className="palt text-xl font-bold text-brand">月額プランで行う改善</p><p className="mt-3 leading-relaxed text-slate-700">CMSで編集できる既存ページを、変更案をご確認いただいてから改善します。大規模な実装や、範囲を超える制作は別途お見積もりです。</p></div>
          <div className="rounded-md bg-white p-7"><p className="palt text-xl font-bold text-brand">正式なご依頼まで</p><p className="mt-3 leading-relaxed text-slate-700">2営業日以内に進め方とお見積もりをご連絡します。対象ページ・作業範囲・支払条件に合意してから正式なご依頼です。フォームの送信だけでは、契約や課金は発生しません。</p></div>
        </div>
        <div className="mt-6 space-y-1 text-xs leading-relaxed text-slate-500">
          <p>AIの回答は毎回変わります。計測結果は目安で、紹介や問い合わせの増加をお約束するものではありません。</p>
          <p>お支払いは銀行振込（請求書払い・前払い）です。キャンセル・解約の条件は<Link href="/ai/legal" className="font-bold text-brand underline underline-offset-4">特定商取引法に基づく表記</Link>をご覧ください。</p>
        </div>
      </Section>

      <CtaBand title="まずは、今の紹介のされ方を知る。" />
    </div>
  );
}
