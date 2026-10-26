import Link from "next/link";
import { sampleReport } from "@/lib/ai-check/sample";
import { REPORT_SECTIONS, ReportDashboard } from "@/components/ai/report-dashboard";
import { btn } from "@/components/ai/ds";

export const metadata = { title: "サンプルレポート | ススメル", description: "ススメルの診断報告書のデモ（架空の求人サイト）" };

// 誰でも見られる報告書のデモ。ログイン後の実際の報告書と同じ部品で表示する
export default function SamplePage() {
  const d = sampleReport();
  return (
    <div className="lg:grid lg:grid-cols-[13rem_minmax(0,1fr)] lg:gap-8">
      <aside className="hidden lg:block">
        <div className="sticky top-20 space-y-6">
          <div>
            <span className="rounded-md border border-slate-300 px-2 py-0.5 text-xs font-semibold text-slate-700">デモ・架空のサービス</span>
            <p className="mt-2 font-semibold text-slate-900">{d.name}</p>
          </div>
          <nav aria-label="報告書の目次" className="space-y-0.5 text-sm">
            {REPORT_SECTIONS.map((s) => (
              <a key={s.id} href={`#${s.id}`} className="block rounded-md px-2.5 py-1.5 text-slate-700 hover:bg-slate-100 hover:text-slate-900">
                {s.label}
              </a>
            ))}
          </nav>
          <div className="space-y-2 border-t border-slate-200 pt-4">
            <p className="text-sm text-slate-700">御社のサービスで、この報告書を作ります。</p>
            <Link href="/ai/apply" className={`${btn("primary")} w-full`}>
              無料で診断する
            </Link>
          </div>
        </div>
      </aside>

      <div className="min-w-0 space-y-6">
        {/* スマホでは目次を上に横並びで */}
        <nav aria-label="報告書の目次" className="sticky top-[57px] z-10 -mx-4 overflow-x-auto border-b border-slate-200 bg-white px-4 lg:hidden">
          <div className="flex gap-1 py-2 text-sm">
            {REPORT_SECTIONS.map((s) => (
              <a key={s.id} href={`#${s.id}`} className="whitespace-nowrap rounded-md px-2.5 py-1 text-slate-700 hover:bg-slate-100">
                {s.label}
              </a>
            ))}
          </div>
        </nav>
        <div className="flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
          <p className="text-sm text-slate-700">
            <span className="font-semibold text-slate-900">これはデモです。</span>
            架空の求人サイトで作りました。無料診断では、この中の「紹介率」と「最初に直すこと」をお送りします。
          </p>
          <Link href="/ai/apply" className={`${btn("primary")} shrink-0 lg:hidden`}>
            無料で診断する
          </Link>
        </div>
        <ReportDashboard d={d} />
      </div>
    </div>
  );
}
