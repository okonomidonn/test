import Link from "next/link";
import { notFound } from "next/navigation";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import {
  EMPLOYMENT_TYPE_LABELS,
  WORK_STYLE_LABELS,
  formatSalary,
} from "@/lib/constants";
import { ApplyForm } from "@/components/apply-form";

export default async function JobDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const session = await auth();

  const job = await prisma.job.findUnique({
    where: { id },
    include: { company: true },
  });

  if (!job) notFound();

  const isOwner = session?.user?.role === "COMPANY" && job.company.userId === session.user.id;
  if (!job.isPublished && !isOwner) notFound();

  let alreadyApplied = false;
  if (session?.user?.role === "SEEKER") {
    const existing = await prisma.application.findUnique({
      where: { jobId_seekerId: { jobId: job.id, seekerId: session.user.id } },
    });
    alreadyApplied = !!existing;
  }

  const tagList = job.tags
    .split(",")
    .map((t) => t.trim())
    .filter(Boolean);

  return (
    <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6">
      {!job.isPublished && (
        <div className="mb-4 rounded-xl bg-amber-50 px-4 py-2.5 text-sm text-amber-800">
          この求人は現在非公開です(あなたの企業アカウントにのみ表示されています)
        </div>
      )}

      <div className="flex flex-wrap items-center gap-2">
        <span className="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-brand-dark">
          {WORK_STYLE_LABELS[job.workStyle] ?? job.workStyle}
        </span>
        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
          {job.category}
        </span>
        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
          {EMPLOYMENT_TYPE_LABELS[job.employmentType] ?? job.employmentType}
        </span>
      </div>

      <h1 className="mt-4 text-2xl font-black text-slate-900 sm:text-3xl">{job.title}</h1>
      <p className="mt-2 text-base text-slate-500">{job.company.name}</p>

      <div className="mt-6 grid grid-cols-1 gap-4 rounded-2xl bg-slate-50 p-5 sm:grid-cols-3">
        <div>
          <p className="text-xs text-slate-400">給与</p>
          <p className="mt-1 font-bold text-brand-dark">{formatSalary(job.salaryMin, job.salaryMax)}</p>
        </div>
        <div>
          <p className="text-xs text-slate-400">勤務地</p>
          <p className="mt-1 font-medium text-slate-700">{job.location || "指定なし(在宅)"}</p>
        </div>
        <div>
          <p className="text-xs text-slate-400">掲載日</p>
          <p className="mt-1 font-medium text-slate-700">
            {job.createdAt.toLocaleDateString("ja-JP")}
          </p>
        </div>
      </div>

      <div className="mt-8">
        <h2 className="text-lg font-bold text-slate-900">仕事内容</h2>
        <p className="mt-3 whitespace-pre-wrap text-sm leading-relaxed text-slate-700">
          {job.description}
        </p>
      </div>

      {tagList.length > 0 && (
        <div className="mt-6 flex flex-wrap gap-1.5">
          {tagList.map((tag) => (
            <span key={tag} className="rounded bg-amber-50 px-2.5 py-1 text-xs text-amber-700">
              #{tag}
            </span>
          ))}
        </div>
      )}

      {job.company.description && (
        <div className="mt-8 rounded-2xl border border-slate-100 p-5">
          <h2 className="font-bold text-slate-900">{job.company.name}について</h2>
          <p className="mt-2 whitespace-pre-wrap text-sm text-slate-600">{job.company.description}</p>
          {job.company.website && (
            <a
              href={job.company.website}
              target="_blank"
              rel="noopener noreferrer"
              className="mt-3 inline-block text-sm font-medium text-brand-dark hover:underline"
            >
              企業サイトを見る &rarr;
            </a>
          )}
        </div>
      )}

      <div className="mt-10 border-t border-slate-100 pt-8">
        {job.isPublished && session?.user?.role === "SEEKER" && !alreadyApplied && (
          <ApplyForm jobId={job.id} />
        )}

        {alreadyApplied && (
          <div className="rounded-2xl bg-emerald-50 p-5 text-sm text-brand-dark">
            この求人には既に応募済みです。マイページから応募状況を確認できます。
          </div>
        )}

        {!session?.user && job.isPublished && (
          <div className="rounded-2xl bg-slate-50 p-5 text-center text-sm text-slate-600">
            応募するには
            <Link
              href={`/login?callbackUrl=${encodeURIComponent(`/jobs/${job.id}`)}`}
              className="mx-1 font-semibold text-brand-dark hover:underline"
            >
              ログイン
            </Link>
            または
            <Link href="/register" className="mx-1 font-semibold text-brand-dark hover:underline">
              会員登録
            </Link>
            が必要です。
          </div>
        )}

        {session?.user?.role === "COMPANY" && !isOwner && (
          <div className="rounded-2xl bg-slate-50 p-5 text-center text-sm text-slate-500">
            企業アカウントでは求人へ応募できません。
          </div>
        )}
      </div>
    </div>
  );
}
