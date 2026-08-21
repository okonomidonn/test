"use server";

import { z } from "zod";
import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";

export type ApplyState = { error?: string; success?: boolean };

const educationEntrySchema = z.object({
  school: z.string().trim().min(1).max(100),
  faculty: z.string().trim().max(100).optional().default(""),
  graduationDate: z.string().trim().max(20).optional().default(""),
});

const workExperienceEntrySchema = z.object({
  company: z.string().trim().min(1).max(100),
  period: z.string().trim().max(50).optional().default(""),
  description: z.string().trim().max(1000).optional().default(""),
});

const applySchema = z.object({
  fullName: z.string().trim().min(1, "氏名を入力してください").max(50),
  fullNameKana: z.string().trim().max(50).optional().default(""),
  birthDate: z.string().trim().max(10).optional().default(""),
  phone: z.string().trim().max(20).optional().default(""),
  education: z.array(educationEntrySchema).max(10).optional().default([]),
  workExperience: z.array(workExperienceEntrySchema).max(10).optional().default([]),
  motivation: z.string().trim().min(1, "志望動機を入力してください").max(2000),
  coverLetter: z.string().trim().min(1, "自己PRを入力してください").max(3000),
});

function parseJsonArray(raw: FormDataEntryValue | null): unknown[] {
  if (typeof raw !== "string" || !raw) return [];
  try {
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

export async function applyToJob(
  jobId: string,
  _prevState: ApplyState,
  formData: FormData,
): Promise<ApplyState> {
  const session = await auth();
  if (!session?.user) {
    redirect(`/login?callbackUrl=${encodeURIComponent(`/jobs/${jobId}`)}`);
  }
  if (session.user.role !== "SEEKER") {
    return { error: "求職者アカウントでログインしてください" };
  }

  const parsed = applySchema.safeParse({
    fullName: formData.get("fullName"),
    fullNameKana: formData.get("fullNameKana"),
    birthDate: formData.get("birthDate"),
    phone: formData.get("phone"),
    education: parseJsonArray(formData.get("educationJson")),
    workExperience: parseJsonArray(formData.get("workExperienceJson")),
    motivation: formData.get("motivation"),
    coverLetter: formData.get("coverLetter"),
  });

  if (!parsed.success) {
    return { error: parsed.error.issues[0]?.message ?? "入力内容を確認してください" };
  }

  const job = await prisma.job.findUnique({ where: { id: jobId } });
  if (!job || !job.isPublished) {
    return { error: "この求人は現在応募を受け付けていません" };
  }

  const existing = await prisma.application.findUnique({
    where: { jobId_seekerId: { jobId, seekerId: session.user.id } },
  });
  if (existing) {
    return { error: "この求人には既に応募済みです" };
  }

  const { fullName, fullNameKana, birthDate, phone, education, workExperience, motivation, coverLetter } =
    parsed.data;

  await prisma.application.create({
    data: {
      jobId,
      seekerId: session.user.id,
      fullName,
      fullNameKana: fullNameKana || null,
      birthDate: birthDate ? new Date(birthDate) : null,
      phone: phone || null,
      education,
      workExperience,
      motivation,
      coverLetter,
    },
  });

  revalidatePath(`/jobs/${jobId}`);
  revalidatePath("/mypage");

  return { success: true };
}

const VALID_STATUSES = ["REVIEWING", "OFFERED", "REJECTED"] as const;

export async function updateApplicationStatus(
  applicationId: string,
  status: (typeof VALID_STATUSES)[number],
) {
  const session = await auth();
  if (!session?.user || session.user.role !== "COMPANY") {
    redirect("/login");
  }
  if (!VALID_STATUSES.includes(status)) return;

  const application = await prisma.application.findUnique({
    where: { id: applicationId },
    include: { job: { include: { company: true } } },
  });

  if (!application || application.job.company.userId !== session.user.id) {
    return;
  }

  await prisma.application.update({ where: { id: applicationId }, data: { status } });

  revalidatePath(`/company/jobs/${application.jobId}/applications`);
}
