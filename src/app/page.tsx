import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { SearchForm } from "@/components/search-form";
import { JobCard } from "@/components/job-card";
import { CATEGORIES } from "@/lib/constants";

export default async function Home() {
  const featuredJobs = await prisma.job.findMany({
    where: { isPublished: true },
    orderBy: { createdAt: "desc" },
    take: 6,
    include: { company: { select: { name: true } } },
  });

  const jobCount = await prisma.job.count({ where: { isPublished: true } });

  return (
    <div>
      <section className="relative overflow-hidden bg-gradient-to-b from-emerald-50 via-white to-white">
        <div className="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24">
          <p className="text-sm font-bold tracking-wide text-brand-dark">在宅ワーク専門の求人サイト</p>
          <h1 className="mt-3 max-w-2xl text-3xl font-black leading-tight text-slate-900 sm:text-5xl">
            「おうちで働く」を、
            <br />
            もっと当たり前に。
          </h1>
          <p className="mt-4 max-w-xl text-base text-slate-600 sm:text-lg">
            zaitoは、フルリモートの正社員から業務委託・アルバイトまで、在宅でできる仕事だけを集めた求人サイトです。
            {jobCount > 0 && `現在 ${jobCount} 件の求人を掲載中。`}
          </p>

          <div className="mt-8">
            <SearchForm compact />
          </div>

          <div className="mt-6 flex flex-wrap gap-2">
            {CATEGORIES.slice(0, 6).map((c) => (
              <Link
                key={c}
                href={`/jobs?category=${encodeURIComponent(c)}`}
                className="rounded-full bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm ring-1 ring-slate-200 hover:text-brand-dark hover:ring-brand"
              >
                {c}
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section className="mx-auto max-w-6xl px-4 py-14 sm:px-6">
        <div className="flex items-end justify-between">
          <h2 className="text-xl font-bold text-slate-900 sm:text-2xl">新着の在宅求人</h2>
          <Link href="/jobs" className="text-sm font-semibold text-brand-dark hover:underline">
            すべて見る &rarr;
          </Link>
        </div>

        {featuredJobs.length === 0 ? (
          <p className="mt-8 rounded-2xl bg-slate-50 p-8 text-center text-sm text-slate-500">
            まだ求人が掲載されていません。企業の方は
            <Link href="/register" className="mx-1 font-semibold text-brand-dark hover:underline">
              会員登録
            </Link>
            して求人を掲載してみましょう。
          </p>
        ) : (
          <div className="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {featuredJobs.map((job) => (
              <JobCard key={job.id} job={job} />
            ))}
          </div>
        )}
      </section>

      <section className="bg-slate-50">
        <div className="mx-auto max-w-6xl px-4 py-14 sm:px-6">
          <div className="grid grid-cols-1 gap-8 sm:grid-cols-3">
            <div>
              <p className="text-3xl">🏠</p>
              <h3 className="mt-3 font-bold text-slate-900">在宅求人だけを厳選</h3>
              <p className="mt-2 text-sm text-slate-500">
                フルリモート・ハイブリッドなど働き方で絞り込み、通勤なしで働ける仕事だけを掲載しています。
              </p>
            </div>
            <div>
              <p className="text-3xl">📝</p>
              <h3 className="mt-3 font-bold text-slate-900">かんたん応募</h3>
              <p className="mt-2 text-sm text-slate-500">
                会員登録後は応募メッセージを送るだけ。応募状況はマイページでいつでも確認できます。
              </p>
            </div>
            <div>
              <p className="text-3xl">🏢</p>
              <h3 className="mt-3 font-bold text-slate-900">企業も無料で求人掲載</h3>
              <p className="mt-2 text-sm text-slate-500">
                企業アカウントを作成すれば、求人の投稿・編集・応募者管理までダッシュボードで完結します。
              </p>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
