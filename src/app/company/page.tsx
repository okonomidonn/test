import Link from "next/link";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { EMPLOYMENT_TYPE_LABELS, WORK_STYLE_LABELS } from "@/lib/constants";
import { togglePublish, deleteJob } from "@/app/actions/jobs";

export default async function CompanyDashboardPage() {
  const session = await auth();
  const userId = session!.user.id;

  const company = await prisma.company.findUnique({
    where: { userId },
    include: {
      jobs: {
        orderBy: { createdAt: "desc" },
        include: { _count: { select: { applications: true } } },
      },
    },
  });

  if (!company) {
    return (
      <div className="mx-auto max-w-3xl px-4 py-16 text-center sm:px-6">
        <p className="text-sm text-slate-500">企業プロフィールが見つかりませんでした。</p>
      </div>
    );
  }

  return (
    <div className="mx-auto max-w-5xl px-4 py-10 sm:px-6">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div>
          <h1 className="text-2xl font-black text-slate-900">企業ダッシュボード</h1>
          <p className="mt-1 text-sm text-slate-500">{company.name}</p>
        </div>
        <Link
          href="/company/jobs/new"
          className="rounded-full bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-dark"
        >
          + 求人を投稿する
        </Link>
      </div>

      <h2 className="mt-10 text-lg font-bold text-slate-900">掲載中の求人 ({company.jobs.length}件)</h2>

      {company.jobs.length === 0 ? (
        <p className="mt-4 rounded-2xl bg-slate-50 p-8 text-center text-sm text-slate-500">
          まだ求人がありません。最初の求人を投稿してみましょう。
        </p>
      ) : (
        <ul className="mt-4 space-y-3">
          {company.jobs.map((job) => (
            <li key={job.id} className="rounded-2xl border border-slate-100 p-4">
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <div className="flex flex-wrap items-center gap-2">
                    <Link href={`/jobs/${job.id}`} className="font-bold text-slate-900 hover:text-brand-dark">
                      {job.title}
                    </Link>
                    <span
                      className={`rounded-full px-2.5 py-0.5 text-xs font-semibold ${
                        job.isPublished
                          ? "bg-emerald-100 text-emerald-800"
                          : "bg-slate-100 text-slate-500"
                      }`}
                    >
                      {job.isPublished ? "公開中" : "非公開"}
                    </span>
                  </div>
                  <p className="mt-1 text-xs text-slate-400">
                    {job.category} / {EMPLOYMENT_TYPE_LABELS[job.employmentType]} /{" "}
                    {WORK_STYLE_LABELS[job.workStyle]}
                  </p>
                </div>

                <div className="flex flex-wrap items-center gap-2 text-sm">
                  <Link
                    href={`/company/jobs/${job.id}/applications`}
                    className="rounded-full border border-slate-200 px-3 py-1.5 font-medium text-slate-600 hover:border-brand hover:text-brand-dark"
                  >
                    応募者 ({job._count.applications})
                  </Link>
                  <Link
                    href={`/company/jobs/${job.id}/edit`}
                    className="rounded-full border border-slate-200 px-3 py-1.5 font-medium text-slate-600 hover:border-brand hover:text-brand-dark"
                  >
                    編集
                  </Link>
                  <form action={togglePublish.bind(null, job.id)}>
                    <button
                      type="submit"
                      className="rounded-full border border-slate-200 px-3 py-1.5 font-medium text-slate-600 hover:border-brand hover:text-brand-dark"
                    >
                      {job.isPublished ? "非公開にする" : "公開する"}
                    </button>
                  </form>
                  <form action={deleteJob.bind(null, job.id)}>
                    <button
                      type="submit"
                      className="rounded-full border border-rose-100 px-3 py-1.5 font-medium text-rose-500 hover:border-rose-300 hover:bg-rose-50"
                    >
                      削除
                    </button>
                  </form>
                </div>
              </div>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
