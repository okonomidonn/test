import Link from "next/link";
import { notFound } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { requireAccount } from "@/lib/session";
import { findStoreFor } from "@/lib/ai-check/access";
import { storeStats } from "@/lib/ai-check/stats";
import { pagePlansFor } from "@/lib/ai-check/suggest";
import type { AuditCheck } from "@/lib/ai-check/audit";
import { ReportView, type ReportData } from "@/components/ai/report-view";
import { PrintButton } from "@/components/ai/print-button";

export const metadata = { title: "診断報告書 | ススメル" };

// お客様にそのまま渡せる報告書（印刷・PDF保存向け）
export default async function ReportPage({ params }: PageProps<"/ai/app/stores/[id]/report">) {
  const user = await requireAccount();
  const { id } = await params;
  const store = await findStoreFor(user, id);
  if (!store) notFound();

  const [stats, audit] = await Promise.all([storeStats(id), prisma.siteAudit.findFirst({ where: { storeId: id }, orderBy: { createdAt: "desc" } })]);
  const missed = stats.questions.filter((q) => q.hits === 0);
  const data: ReportData = {
    name: store.name,
    url: store.websiteUrl,
    createdAt: new Date(),
    measured: stats.latest ? { hits: stats.latest.targetHits, total: stats.latest.answers, at: stats.latest.createdAt } : null,
    score: audit?.score ?? null,
    questions: stats.questions,
    others: [
      ...stats.competitors.filter((c) => c.hits > 0).map((c) => ({ label: c.name, count: c.hits, registered: true })),
      ...stats.frequentShops.map((s) => ({ label: s.label, count: s.count, registered: false })),
    ].slice(0, 8),
    sources: stats.sources.map((s) => ({ domain: s.domain, count: s.count, official: s.kind === "official" })),
    checks: (audit?.checks ?? []) as AuditCheck[],
    plans: store.kind === "WEB" ? pagePlansFor(missed.map((q) => q.text), store) : [],
  };

  return (
    <article className="mx-auto max-w-3xl space-y-10 bg-white p-6 sm:p-10 print:max-w-none print:p-0">
      <div className="flex flex-wrap items-center justify-between gap-3 print:hidden">
        <Link href={`/ai/app/stores/${id}?tab=improve`} className="text-sm text-slate-600 hover:underline">
          ← 戻る
        </Link>
        <PrintButton />
      </div>
      <ReportView d={data} />
    </article>
  );
}
