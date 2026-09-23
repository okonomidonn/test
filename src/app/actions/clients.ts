"use server";

import { z } from "zod";
import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/session";
import { PLATFORMS } from "@/lib/constants";

export type FormState = { error?: string; ok?: boolean };

const optionalText = (max: number) =>
  z.preprocess(
    (v) => (typeof v === "string" && v.trim() === "" ? undefined : v),
    z.string().trim().max(max).optional(),
  );

const clientSchema = z.object({
  name: z.string().trim().min(1, "クライアント名を入力してください").max(100),
  industry: optionalText(50),
  contactName: optionalText(50),
  contactEmail: z.preprocess(
    (v) => (typeof v === "string" && v.trim() === "" ? undefined : v),
    z.string().trim().email("担当者メールアドレスの形式が正しくありません").optional(),
  ),
  notes: optionalText(2000),
});

function firstError(error: z.ZodError): string {
  return error.issues[0]?.message ?? "入力内容を確認してください";
}

function toClientData(d: z.infer<typeof clientSchema>) {
  return {
    name: d.name,
    industry: d.industry ?? null,
    contactName: d.contactName ?? null,
    contactEmail: d.contactEmail ?? null,
    notes: d.notes ?? null,
  };
}

export async function createClient(_prev: FormState, formData: FormData): Promise<FormState> {
  await requireUser();
  const parsed = clientSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) return { error: firstError(parsed.error) };

  const client = await prisma.client.create({ data: toClientData(parsed.data) });
  revalidatePath("/clients");
  redirect(`/clients/${client.id}`);
}

export async function updateClient(
  clientId: string,
  _prev: FormState,
  formData: FormData,
): Promise<FormState> {
  await requireUser();
  const parsed = clientSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) return { error: firstError(parsed.error) };

  const { count } = await prisma.client.updateMany({
    where: { id: clientId },
    data: toClientData(parsed.data),
  });
  if (count === 0) return { error: "クライアントが見つかりません" };

  revalidatePath("/clients");
  revalidatePath(`/clients/${clientId}`);
  return { ok: true };
}

export async function deleteClient(clientId: string) {
  await requireUser();
  await prisma.client.deleteMany({ where: { id: clientId } });
  revalidatePath("/clients");
  revalidatePath("/dashboard");
  revalidatePath("/posts");
  redirect("/clients");
}

const accountSchema = z.object({
  platform: z.enum(PLATFORMS, { message: "SNSを選択してください" }),
  handle: z
    .string()
    .trim()
    .transform((v) => v.replace(/^@/, ""))
    .pipe(z.string().min(1, "アカウント名を入力してください").max(100)),
  followers: z.coerce.number().int().nonnegative("フォロワー数は0以上で入力してください").default(0),
});

export async function createAccount(
  clientId: string,
  _prev: FormState,
  formData: FormData,
): Promise<FormState> {
  await requireUser();
  const parsed = accountSchema.safeParse({
    platform: formData.get("platform"),
    handle: formData.get("handle"),
    followers: formData.get("followers") || 0,
  });
  if (!parsed.success) return { error: firstError(parsed.error) };

  const client = await prisma.client.findUnique({ where: { id: clientId }, select: { id: true } });
  if (!client) return { error: "クライアントが見つかりません" };

  const duplicate = await prisma.socialAccount.findUnique({
    where: { platform_handle: { platform: parsed.data.platform, handle: parsed.data.handle } },
  });
  if (duplicate) return { error: "このアカウントは既に登録されています" };

  await prisma.socialAccount.create({ data: { ...parsed.data, clientId } });
  revalidatePath(`/clients/${clientId}`);
  revalidatePath("/clients");
  return { ok: true };
}

export async function updateFollowers(accountId: string, formData: FormData) {
  await requireUser();
  const followers = z.coerce.number().int().nonnegative().safeParse(formData.get("followers"));
  if (!followers.success) return;

  const account = await prisma.socialAccount.findUnique({ where: { id: accountId } });
  if (!account) return;
  await prisma.socialAccount.update({
    where: { id: accountId },
    data: { followers: followers.data },
  });
  revalidatePath(`/clients/${account.clientId}`);
  revalidatePath("/dashboard");
}

export async function deleteAccount(accountId: string) {
  await requireUser();
  const account = await prisma.socialAccount.findUnique({ where: { id: accountId } });
  if (!account) return;
  await prisma.socialAccount.delete({ where: { id: accountId } });
  revalidatePath(`/clients/${account.clientId}`);
  revalidatePath("/clients");
  revalidatePath("/posts");
  revalidatePath("/dashboard");
}

export async function disconnectInstagram(accountId: string) {
  await requireUser();
  const account = await prisma.socialAccount.findUnique({ where: { id: accountId } });
  if (!account) return;
  await prisma.socialAccount.update({
    where: { id: accountId },
    data: { igUserId: null, accessToken: null, tokenExpiresAt: null, connectedAt: null },
  });
  revalidatePath(`/clients/${account.clientId}`);
  revalidatePath("/posts");
}
