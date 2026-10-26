import Link from "next/link";
import { sampleReport } from "@/lib/ai-check/sample";
import { ReportView } from "@/components/ai/report-view";

export const metadata = { title: "サンプルの報告書 | ススメル", description: "ススメルの診断報告書の見本（架空の求人サイト）" };

// 誰でも見られる、報告書の見本。中身は実際の報告書と同じ部品で作る
export default function SamplePage() {
  return (
    <div className="space-y-6">
      <div className="mx-auto flex max-w-3xl flex-col gap-4 rounded-xl border-2 border-brand bg-brand-soft p-5 sm:flex-row sm:items-center sm:justify-between">
        <div className="min-w-0">
          <p className="font-bold text-slate-900">これはサンプルです</p>
          <p className="text-sm text-slate-700">架空の求人サイトで作った報告書です。無料チェックでは、この中の「紹介された割合」と「直すところ」の上位3つをお送りします。</p>
        </div>
        <Link href="/ai/apply" className="shrink-0 rounded-lg bg-brand px-5 py-3 text-center font-bold text-white hover:bg-brand-dark">
          無料チェックを申し込む
        </Link>
      </div>
      <article className="mx-auto max-w-3xl space-y-10 rounded-2xl border border-slate-200 bg-white p-6 sm:p-10">
        <ReportView d={sampleReport()} />
      </article>
    </div>
  );
}
