import { notFound } from "next/navigation";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { JobForm } from "@/components/job-form";
import { updateJob } from "@/app/actions/jobs";

export default async function EditJobPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const session = await auth();
  const userId = session!.user.id;

  const job = await prisma.job.findUnique({
    where: { id },
    include: { company: true },
  });

  if (!job || job.company.userId !== userId) {
    notFound();
  }

  const boundUpdateJob = updateJob.bind(null, job.id);

  return (
    <div className="mx-auto max-w-2xl px-4 py-10 sm:px-6">
      <h1 className="text-2xl font-black text-slate-900">求人を編集する</h1>
      <p className="mt-1 text-sm text-slate-500">{job.title}</p>

      <div className="mt-8">
        <JobForm
          action={boundUpdateJob}
          submitLabel="変更を保存する"
          defaultValues={{
            title: job.title,
            description: job.description,
            category: job.category,
            employmentType: job.employmentType,
            workStyle: job.workStyle,
            location: job.location,
            salaryMin: job.salaryMin,
            salaryMax: job.salaryMax,
            tags: job.tags,
          }}
        />
      </div>
    </div>
  );
}
