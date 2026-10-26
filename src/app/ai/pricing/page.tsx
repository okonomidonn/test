import Link from "next/link";
import { MarketCompare, WHY_AFFORDABLE } from "@/components/ai/marketing";
import { ServicePlans } from "@/components/ai/service-plans";

export const metadata = { title: "料金 | ススメル" };

export default function PricingPage() {
  return (
    <div className="space-y-12 pt-2 sm:space-y-16 sm:pt-6">
      <header className="max-w-3xl">
        <p className="text-sm font-semibold text-brand">料金とサポート</p>
        <h1 className="mt-3 text-3xl font-bold leading-snug text-slate-900 sm:text-[2.75rem]">まず無料で調べて、<br />必要なら改善まで。</h1>
        <p className="mt-5 text-lg leading-relaxed text-slate-700">無料チェックだけのご利用もできます。<br className="hidden sm:block" />有料プランへの自動切り替えはありません。</p>
      </header>
      <section aria-label="3つの料金プラン"><h2 className="sr-only">3つの料金プラン</h2><ServicePlans /></section>
      <section className="grid gap-6 rounded-xl bg-slate-50 p-5 sm:grid-cols-2 sm:p-7">
        <div><h2 className="text-lg font-bold text-slate-900">トライアルで行う改善</h2><p className="mt-3 leading-relaxed text-slate-700">CMSで編集できる既存ページを1ページ改善します。変更案をご確認いただいてから作業します。</p><p className="mt-2 text-sm text-slate-600">新規ページ制作・外部媒体への掲載・大規模な実装は含みません。</p></div>
        <div><h2 className="text-lg font-bold text-slate-900">申し込みから契約まで</h2><p className="mt-3 leading-relaxed text-slate-700">トライアルのご相談には、2営業日以内に進め方と見積もりをご連絡します。内容をご確認いただいてから正式なご依頼となります。</p><p className="mt-2 text-sm text-slate-600">フォームの送信だけでは、契約や自動課金は発生しません。</p></div>
      </section>
      <section className="max-w-4xl">
        <h2 className="text-2xl font-bold text-slate-900">料金について、もう少し詳しく</h2>
        <div className="mt-5 divide-y divide-slate-200 border-y border-slate-200">
          <details className="group py-1"><summary className="flex cursor-pointer list-none items-center justify-between gap-3 py-4 text-lg font-semibold text-slate-900">他社の料金と比べたい<span aria-hidden className="text-brand group-open:hidden">＋</span><span aria-hidden className="hidden text-brand group-open:inline">−</span></summary><div className="pb-6"><p className="mb-4 text-slate-700">公開されている料金の中央値と比べています。各社で作業範囲は異なります。</p><MarketCompare /></div></details>
          <details className="group py-1"><summary className="flex cursor-pointer list-none items-center justify-between gap-3 py-4 text-lg font-semibold text-slate-900">この価格でできる理由は？<span aria-hidden className="text-brand group-open:hidden">＋</span><span aria-hidden className="hidden text-brand group-open:inline">−</span></summary><dl className="space-y-5 pb-6">{WHY_AFFORDABLE.map(([title, body]) => <div key={title}><dt className="font-bold text-slate-900">{title}</dt><dd className="mt-1 text-slate-700">{body}</dd></div>)}</dl></details>
        </div>
      </section>
      <div className="max-w-3xl space-y-3 text-sm text-slate-600"><p>AIの回答は毎回変わります。計測結果は目安で、紹介や問い合わせの増加をお約束するものではありません。</p><p>支払方法・支払時期・解約条件は、正式なご依頼前に見積書・契約書でご案内します。</p><Link href="/ai/legal" className="inline-block py-2 font-semibold text-brand underline underline-offset-4">特定商取引法に基づく表記 →</Link></div>
    </div>
  );
}
