"use server";

import { z } from "zod";
import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";

const emptyToUndefined = (v: unknown) => (v === "" || v == null ? undefined : v);

const jobSchema = z.object({
  title: z.string().trim().min(1, "タイトルを入力してください").max(100),
  description: z.string().trim().min(1, "仕事内容を入力してください").max(5000),
  category: z.string().trim().min(1, "職種を選択してください"),
  employmentType: z.enum(["FULL_TIME", "PART_TIME", "CONTRACT", "FREELANCE", "INTERNSHIP"]),
  workStyle: z.enum(["FULL_REMOTE", "HYBRID", "ON_SITE"]),
  location: z.preprocess(emptyToUndefined, z.string().trim().max(100).optional()),
  salaryMin: z.preprocess(emptyToUndefined, z.coerce.number().int().nonnegative().optional()),
  salaryMax: z.preprocess(emptyToUndefined, z.coerce.number().int().nonnegative().optional()),
  tags: z.preprocess(emptyToUndefined, z.string().trim().max(300).optional()),
});

export type JobFormState = { error?: string };

async function requireCompany() {
  const session = await auth();
  if (!session?.user || session.user.role !== "COMPANY") {
    redirect("/login");
  }
  const company = await prisma.company.findUnique({ where: { userId: session.user.id } });
  if (!company) {
    redirect("/company");
  }
  return company;
}

export async function createJob(
  _prevState: JobFormState,
  formData: FormData,
): Promise<JobFormState> {
  const company = await requireCompany();

  const parsed = jobSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) {
    return { error: parsed.error.issues[0]?.message ?? "入力内容を確認してください" };
  }

  if (
    parsed.data.salaryMin != null &&
    parsed.data.salaryMax != null &&
    parsed.data.salaryMin > parsed.data.salaryMax
  ) {
    return { error: "給与の下限は上限以下にしてください" };
  }

  await prisma.job.create({
    data: {
      title: parsed.data.title,
      description: parsed.data.description,
      category: parsed.data.category,
      employmentType: parsed.data.employmentType,
      workStyle: parsed.data.workStyle,
      location: parsed.data.location ?? null,
      salaryMin: parsed.data.salaryMin ?? null,
      salaryMax: parsed.data.salaryMax ?? null,
      tags: parsed.data.tags ?? "",
      companyId: company.id,
    },
  });

  revalidatePath("/jobs");
  revalidatePath("/company");
  redirect("/company");
}

export async function updateJob(
  jobId: string,
  _prevState: JobFormState,
  formData: FormData,
): Promise<JobFormState> {
  const company = await requireCompany();

  const job = await prisma.job.findUnique({ where: { id: jobId } });
  if (!job || job.companyId !== company.id) {
    return { error: "求人が見つかりません" };
  }

  const parsed = jobSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) {
    return { error: parsed.error.issues[0]?.message ?? "入力内容を確認してください" };
  }

  if (
    parsed.data.salaryMin != null &&
    parsed.data.salaryMax != null &&
    parsed.data.salaryMin > parsed.data.salaryMax
  ) {
    return { error: "給与の下限は上限以下にしてください" };
  }

  await prisma.job.update({
    where: { id: jobId },
    data: {
      title: parsed.data.title,
      description: parsed.data.description,
      category: parsed.data.category,
      employmentType: parsed.data.employmentType,
      workStyle: parsed.data.workStyle,
      location: parsed.data.location ?? null,
      salaryMin: parsed.data.salaryMin ?? null,
      salaryMax: parsed.data.salaryMax ?? null,
      tags: parsed.data.tags ?? "",
    },
  });

  revalidatePath("/jobs");
  revalidatePath(`/jobs/${jobId}`);
  revalidatePath("/company");
  redirect("/company");
}

export async function togglePublish(jobId: string) {
  const company = await requireCompany();
  const job = await prisma.job.findUnique({ where: { id: jobId } });
  if (!job || job.companyId !== company.id) return;

  await prisma.job.update({
    where: { id: jobId },
    data: { isPublished: !job.isPublished },
  });

  revalidatePath("/company");
  revalidatePath("/jobs");
  revalidatePath(`/jobs/${jobId}`);
}

export async function deleteJob(jobId: string) {
  const company = await requireCompany();
  const job = await prisma.job.findUnique({ where: { id: jobId } });
  if (!job || job.companyId !== company.id) return;

  await prisma.job.delete({ where: { id: jobId } });

  revalidatePath("/company");
  revalidatePath("/jobs");
}
