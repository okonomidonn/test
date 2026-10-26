"use server";

import { z } from "zod";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";

export type LeadField = "company" | "name" | "email" | "websiteUrl" | "message";
export type LeadState = {
  error?: string;
  fieldErrors?: Partial<Record<LeadField, string>>;
  // 入力エラーのとき、入力した内容を戻すため
  values?: Partial<Record<LeadField, string>>;
  done?: boolean;
};

const leadSchema = z.object({
  kind: z.enum(["CHECK", "TRIAL"]),
  company: z.string().trim().min(1, "会社名・サービス名を入力してください").max(100),
  name: z.string().trim().min(1, "お名前を入力してください").max(50),
  email: z.string().trim().email("メールアドレスの形式が正しくありません").max(200),
  websiteUrl: z
    .string()
    .trim()
    .max(300)
    .refine((v) => /^https?:\/\/[^\s]+\.[^\s]+/.test(v), "サイトのURLは https:// から入力してください"),
  message: z.string().trim().max(2000).optional(),
});

// 無料チェック・トライアルの申し込みを受け付ける（ログインは不要）
export async function createLead(_prev: LeadState, formData: FormData): Promise<LeadState> {
  // 機械的な送信よけ：人には見えない欄に入力があれば、受け付けたように見せて保存しない
  if (String(formData.get("fax") ?? "")) return { done: true };

  const parsed = leadSchema.safeParse({
    kind: formData.get("kind") === "TRIAL" ? "TRIAL" : "CHECK",
    company: formData.get("company"),
    name: formData.get("name"),
    email: formData.get("email"),
    websiteUrl: formData.get("websiteUrl"),
    message: formData.get("message") || undefined,
  });
  if (!parsed.success) {
    const fieldErrors: LeadState["fieldErrors"] = {};
    for (const issue of parsed.error.issues) {
      const key = issue.path[0] as LeadField;
      if (key && !fieldErrors[key]) fieldErrors[key] = issue.message;
    }
    const values = Object.fromEntries(
      (["company", "name", "email", "websiteUrl", "message"] as const).map((k) => [k, String(formData.get(k) ?? "")]),
    ) as LeadState["values"];
    return { error: "入力内容を確認してください", fieldErrors, values };
  }

  // 同じメールアドレスから10分以内の重複は保存しない
  const since = new Date(Date.now() - 10 * 60_000);
  if (await prisma.lead.findFirst({ where: { email: parsed.data.email, createdAt: { gt: since } } })) return { done: true };

  const session = await auth();
  await prisma.lead.create({ data: { ...parsed.data, userId: session?.user?.id ?? null } });
  return { done: true };
}
