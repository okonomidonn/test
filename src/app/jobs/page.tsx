import { prisma } from "@/lib/prisma";
import { SearchForm } from "@/components/search-form";
import { JobCard } from "@/components/job-card";
import type { Prisma } from "@/generated/prisma/client";

type SearchParams = {
  q?: string;
  category?: string;
  workStyle?: string;
  employmentType?: string;
};

export default async function JobsPage({
  searchParams,
}: {
  searchParams: Promise<SearchParams>;
}) {
  const params = await searchParams;
  const q = params.q?.trim();
  const category = params.category?.trim();
  const workStyle = params.workStyle?.trim();
  const employmentType = params.employmentType?.trim();

  const where: Prisma.JobWhereInput = {
    isPublished: true,
    ...(category ? { category } : {}),
    ...(workStyle ? { workStyle: workStyle as Prisma.JobWhereInput["workStyle"] } : {}),
    ...(employmentType
      ? { employmentType: employmentType as Prisma.JobWhereInput["employmentType"] }
      : {}),
    ...(q
      ? {
          OR: [
            { title: { contains: q } },
            { description: { contains: q } },
            { tags: { contains: q } },
          ],
        }
      : {}),
  };

  const jobs = await prisma.job.findMany({
    where,
    orderBy: { createdAt: "desc" },
    include: { company: { select: { name: true } } },
  });

  return (
    <div className="mx-auto max-w-6xl px-4 py-10 sm:px-6">
      <h1 className="text-2xl font-black text-slate-900">在宅ワークの求人を探す</h1>
      <p className="mt-1 text-sm text-slate-500">{jobs.length}件の求人が見つかりました</p>

      <div className="mt-6">
        <SearchForm defaultValues={params} />
      </div>

      {jobs.length === 0 ? (
        <p className="mt-10 rounded-2xl bg-slate-50 p-8 text-center text-sm text-slate-500">
          条件に合う求人が見つかりませんでした。検索条件を変えてお試しください。
        </p>
      ) : (
        <div className="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {jobs.map((job) => (
            <JobCard key={job.id} job={job} />
          ))}
        </div>
      )}
    </div>
  );
}
