import Link from "next/link";
import { notFound } from "next/navigation";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { ApplicationStatusSelect } from "@/components/application-status-select";

export default async function JobApplicationsPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const session = await auth();
  const userId = session!.user.id;

  const job = await prisma.job.findUnique({
    where: { id },
    include: {
      company: true,
      applications: {
        orderBy: { createdAt: "desc" },
        include: { seeker: { select: { name: true, email: true } } },
      },
    },
  });

  if (!job || job.company.userId !== userId) {
    notFound();
  }

  return (
    <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6">
      <Link href="/company" className="text-sm font-medium text-slate-500 hover:text-brand-dark">
        &larr; ダッシュボードに戻る
      </Link>

      <h1 className="mt-3 text-2xl font-black text-slate-900">{job.title}への応募者</h1>
      <p className="mt-1 text-sm text-slate-500">{job.applications.length}件の応募</p>

      {job.applications.length === 0 ? (
        <p className="mt-8 rounded-2xl bg-slate-50 p-8 text-center text-sm text-slate-500">
          まだ応募はありません。
        </p>
      ) : (
        <ul className="mt-6 space-y-4">
          {job.applications.map((application) => (
            <li key={application.id} className="rounded-2xl border border-slate-100 p-5">
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <p className="font-bold text-slate-900">{application.seeker.name}</p>
                  <p className="text-xs text-slate-400">{application.seeker.email}</p>
                  <p className="mt-0.5 text-xs text-slate-400">
                    応募日: {application.createdAt.toLocaleDateString("ja-JP")}
                  </p>
                </div>
                <ApplicationStatusSelect applicationId={application.id} status={application.status} />
              </div>
              <p className="mt-3 whitespace-pre-wrap rounded-xl bg-slate-50 p-4 text-sm text-slate-700">
                {application.coverLetter}
              </p>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
