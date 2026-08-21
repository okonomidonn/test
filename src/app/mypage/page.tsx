import Link from "next/link";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import {
  APPLICATION_STATUS_LABELS,
  APPLICATION_STATUS_STYLES,
} from "@/lib/constants";

export default async function MyPage() {
  const session = await auth();
  const userId = session!.user.id;

  const [user, applications] = await Promise.all([
    prisma.user.findUnique({ where: { id: userId } }),
    prisma.application.findMany({
      where: { seekerId: userId },
      orderBy: { createdAt: "desc" },
      include: { job: { include: { company: { select: { name: true } } } } },
    }),
  ]);

  return (
    <div className="mx-auto max-w-4xl px-4 py-10 sm:px-6">
      <h1 className="text-2xl font-black text-slate-900">マイページ</h1>
      <p className="mt-1 text-sm text-slate-500">{user?.name}さん、こんにちは。</p>

      <h2 className="mt-8 text-lg font-bold text-slate-900">応募履歴</h2>

      {applications.length === 0 ? (
        <p className="mt-4 rounded-2xl bg-slate-50 p-8 text-center text-sm text-slate-500">
          まだ応募した求人はありません。
          <Link href="/jobs" className="mx-1 font-semibold text-brand-dark hover:underline">
            求人を探す
          </Link>
        </p>
      ) : (
        <ul className="mt-4 space-y-3">
          {applications.map((application) => (
            <li
              key={application.id}
              className="flex flex-col gap-2 rounded-2xl border border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between"
            >
              <div>
                <Link
                  href={`/jobs/${application.jobId}`}
                  className="font-bold text-slate-900 hover:text-brand-dark"
                >
                  {application.job.title}
                </Link>
                <p className="mt-0.5 text-sm text-slate-500">{application.job.company.name}</p>
                <p className="mt-0.5 text-xs text-slate-400">
                  応募日: {application.createdAt.toLocaleDateString("ja-JP")}
                </p>
              </div>
              <span
                className={`w-fit rounded-full px-3 py-1 text-xs font-semibold ${
                  APPLICATION_STATUS_STYLES[application.status]
                }`}
              >
                {APPLICATION_STATUS_LABELS[application.status]}
              </span>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
