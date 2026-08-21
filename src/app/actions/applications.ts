"use server";

import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";

export type ApplyState = { error?: string; success?: boolean };

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

  const coverLetter = String(formData.get("coverLetter") ?? "").trim();
  if (!coverLetter) {
    return { error: "応募メッセージを入力してください" };
  }
  if (coverLetter.length > 3000) {
    return { error: "応募メッセージは3000文字以内で入力してください" };
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

  await prisma.application.create({
    data: { jobId, seekerId: session.user.id, coverLetter },
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
