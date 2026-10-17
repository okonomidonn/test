"use server";

import { z } from "zod";
import { hash } from "bcryptjs";
import { redirect } from "next/navigation";
import { revalidatePath } from "next/cache";
import { prisma } from "@/lib/prisma";
import { signIn } from "@/auth";
import { requireAccount } from "@/lib/session";
import { findStoreFor } from "@/lib/ai-check/access";
import { auditSite } from "@/lib/ai-check/audit";
import { createRun } from "@/lib/ai-check/runner";
import { OWNER_LIMITS, TEAM_MAX_REPEATS, defaultQuestions } from "@/lib/ai-check/questions";
import { ownerPlan, ownerRunQuota } from "@/lib/billing/limits";

export type FormState = { error?: string; ok?: string };

const signupSchema = z.object({
  name: z.string().trim().min(1, "お名前を入力してください").max(50),
  email: z.string().trim().email("メールアドレスの形式が正しくありません"),
  password: z.string().min(8, "パスワードは8文字以上で入力してください").max(100),
});

export async function signupOwner(_prev: FormState, formData: FormData): Promise<FormState> {
  const parsed = signupSchema.safeParse({
    name: formData.get("name"),
    email: formData.get("email"),
    password: formData.get("password"),
  });
  if (!parsed.success) return { error: parsed.error.issues[0]?.message ?? "入力内容を確認してください" };

  const { name, email, password } = parsed.data;
  if (await prisma.user.findUnique({ where: { email } })) {
    return { error: "このメールアドレスは既に登録されています" };
  }
  await prisma.user.create({ data: { name, email, role: "OWNER", passwordHash: await hash(password, 10) } });
  await signIn("credentials", { email, password, redirectTo: "/ai/app/new" });
  return {};
}

const lines = (v: FormDataEntryValue | null) =>
  String(v ?? "")
    .split(/[\n,、]/)
    .map((s) => s.trim())
    .filter(Boolean);

const storeSchema = z.object({
  name: z.string().trim().min(1, "店名を入力してください").max(80),
  area: z.string().trim().min(1, "エリア（市区町村や街の名前）を入力してください").max(30),
  station: z.string().trim().max(30).optional(),
  genre: z.string().trim().min(1, "業態を入力してください").max(40),
  websiteUrl: z
    .string()
    .trim()
    .max(300)
    .refine((v) => v === "" || /^https?:\/\//.test(v), "公式サイトは http:// または https:// から入力してください")
    .optional(),
  aliases: z.array(z.string().max(60)).max(10, "別の表記は10件までです"),
  competitors: z.array(z.string().max(60)).max(10, "競合店は10件までです"),
});

function parseStore(formData: FormData) {
  return storeSchema.safeParse({
    name: formData.get("name"),
    area: formData.get("area"),
    station: formData.get("station") || undefined,
    genre: formData.get("genre"),
    websiteUrl: formData.get("websiteUrl") || undefined,
    aliases: lines(formData.get("aliases")),
    competitors: lines(formData.get("competitors")),
  });
}

export async function createStore(_prev: FormState, formData: FormData): Promise<FormState> {
  const user = await requireAccount();
  const parsed = parseStore(formData);
  if (!parsed.success) return { error: parsed.error.issues[0]?.message ?? "入力内容を確認してください" };

  if (user.role === "OWNER") {
    const plan = await ownerPlan(user.id);
    if ((await prisma.store.count({ where: { ownerId: user.id } })) >= plan.stores) {
      return { error: `${plan.name}プランで登録できるのは${plan.stores}店舗までです。「料金プラン」からプランを変更できます` };
    }
  }
  const d = parsed.data;
  const store = await prisma.store.create({
    data: {
      ownerId: user.id,
      name: d.name,
      area: d.area,
      station: d.station || null,
      genre: d.genre,
      websiteUrl: d.websiteUrl || null,
      aliases: d.aliases,
      competitors: d.competitors,
      questions: { create: defaultQuestions(d).map((text, sort) => ({ text, sort })) },
    },
  });
  redirect(`/ai/app/stores/${store.id}`);
}

export async function updateStore(storeId: string, _prev: FormState, formData: FormData): Promise<FormState> {
  const user = await requireAccount();
  if (!(await findStoreFor(user, storeId))) return { error: "店舗が見つかりません" };
  const parsed = parseStore(formData);
  if (!parsed.success) return { error: parsed.error.issues[0]?.message ?? "入力内容を確認してください" };
  const d = parsed.data;
  await prisma.store.update({
    where: { id: storeId },
    data: {
      name: d.name,
      area: d.area,
      station: d.station || null,
      genre: d.genre,
      websiteUrl: d.websiteUrl || null,
      aliases: d.aliases,
      competitors: d.competitors,
    },
  });
  revalidatePath(`/ai/app/stores/${storeId}`);
  return { ok: "保存しました" };
}

export async function updateQuestions(storeId: string, _prev: FormState, formData: FormData): Promise<FormState> {
  const user = await requireAccount();
  if (!(await findStoreFor(user, storeId))) return { error: "店舗が見つかりません" };
  const texts = String(formData.get("questions") ?? "")
    .split("\n")
    .map((s) => s.trim())
    .filter(Boolean);
  if (texts.length === 0) return { error: "質問を1つ以上入力してください" };
  const max = user.role === "OWNER" ? (await ownerPlan(user.id)).maxQuestions : 50;
  if (texts.length > max) return { error: `質問は${max}個までです` };
  if (texts.some((t) => t.length > 120)) return { error: "質問は1つ120文字までです" };

  await prisma.$transaction([
    prisma.question.deleteMany({ where: { storeId } }),
    prisma.question.createMany({ data: texts.map((text, sort) => ({ storeId, text, sort })) }),
  ]);
  revalidatePath(`/ai/app/stores/${storeId}`);
  return { ok: "保存しました（次回の計測から使われます）" };
}

export async function startRun(storeId: string, _prev: FormState, formData: FormData): Promise<FormState> {
  const user = await requireAccount();
  if (!(await findStoreFor(user, storeId))) return { error: "店舗が見つかりません" };

  if (await prisma.measurementRun.findFirst({ where: { storeId, status: "RUNNING" } })) {
    return { error: "計測中です。終わるまでお待ちください" };
  }
  let repeats = OWNER_LIMITS.repeats;
  if (user.role === "OWNER") {
    const quota = await ownerRunQuota(user.id, storeId);
    if (!quota.allowed) return { error: quota.reason };
  } else {
    repeats = Math.min(Math.max(Number(formData.get("repeats")) || 1, 1), TEAM_MAX_REPEATS);
  }

  let runId: string;
  try {
    runId = (await createRun(storeId, repeats)).id;
  } catch (e) {
    return { error: e instanceof Error ? e.message : "計測を開始できませんでした" };
  }
  redirect(`/ai/app/stores/${storeId}/runs/${runId}`);
}

export async function runAudit(storeId: string): Promise<FormState> {
  const user = await requireAccount();
  const store = await findStoreFor(user, storeId);
  if (!store) return { error: "店舗が見つかりません" };
  if (!store.websiteUrl) return { error: "店舗情報に公式サイトのURLを登録してください" };

  if (user.role === "OWNER") {
    const since = new Date(Date.now() - 10 * 60_000);
    if (await prisma.siteAudit.findFirst({ where: { storeId, createdAt: { gt: since } } })) {
      return { error: "サイト診断は10分に1回までです" };
    }
  }
  const result = await auditSite({ websiteUrl: store.websiteUrl, area: store.area, genre: store.genre });
  await prisma.siteAudit.create({
    data: { storeId, url: result.url, score: result.score, checks: result.checks },
  });
  revalidatePath(`/ai/app/stores/${storeId}`);
  return { ok: "診断しました" };
}

export async function deleteStore(storeId: string) {
  const user = await requireAccount();
  if (!(await findStoreFor(user, storeId))) return;
  await prisma.store.delete({ where: { id: storeId } });
  redirect("/ai/app");
}
