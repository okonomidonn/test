import Link from "next/link";
import { notFound } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { requireAccount } from "@/lib/session";
import { findStoreFor } from "@/lib/ai-check/access";
import { storeStats } from "@/lib/ai-check/stats";
import { buildReportData } from "@/lib/ai-check/report";
import { ReportDashboard } from "@/components/ai/report-dashboard";
import { PrintButton } from "@/components/ai/print-button";

export const metadata = { title: "診断報告書 | ススメル" };

// お客様にそのまま渡せる報告書（印刷・PDF保存向け）。サンプルと同じ部品で表示する
export default async function ReportPage({ params }: PageProps<"/ai/app/stores/[id]/report">) {
  const user = await requireAccount();
  const { id } = await params;
  const store = await findStoreFor(user, id);
  if (!store) notFound();
  const [stats, audit] = await Promise.all([storeStats(id), prisma.siteAudit.findFirst({ where: { storeId: id }, orderBy: { createdAt: "desc" } })]);

  return (
    <article className="mx-auto max-w-5xl space-y-6 print:max-w-none">
      <div className="flex flex-wrap items-center justify-between gap-3 print:hidden">
        <Link href={`/ai/app/stores/${id}?tab=improve`} className="text-sm text-slate-600 hover:underline">
          ← 戻る
        </Link>
        <PrintButton />
      </div>
      <ReportDashboard d={buildReportData(store, stats, audit)} />
    </article>
  );
}
