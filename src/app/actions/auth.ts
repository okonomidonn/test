"use server";

import { z } from "zod";
import { hash } from "bcryptjs";
import { prisma } from "@/lib/prisma";
import { signIn } from "@/auth";

const registerSchema = z.object({
  name: z.string().trim().min(1, "お名前を入力してください").max(50),
  email: z.string().trim().email("メールアドレスの形式が正しくありません"),
  password: z.string().min(8, "パスワードは8文字以上で入力してください").max(100),
});

export type AuthFormState = { error?: string };

export async function registerUser(
  _prevState: AuthFormState,
  formData: FormData,
): Promise<AuthFormState> {
  const parsed = registerSchema.safeParse({
    name: formData.get("name"),
    email: formData.get("email"),
    password: formData.get("password"),
  });
  if (!parsed.success) {
    return { error: parsed.error.issues[0]?.message ?? "入力内容を確認してください" };
  }

  const { name, email, password } = parsed.data;
  const token = formData.get("invite");
  const passwordHash = await hash(password, 10);

  const error = await prisma.$transaction(async (tx) => {
    // The very first account bootstraps the workspace; everyone after needs an invite.
    if ((await tx.user.count()) > 0) {
      if (typeof token !== "string" || !token) {
        return "新規登録には招待リンクが必要です";
      }
      const { count } = await tx.invite.updateMany({
        where: { token, usedAt: null, expiresAt: { gt: new Date() } },
        data: { usedAt: new Date() },
      });
      if (count === 0) return "招待リンクが無効か、期限切れです";
    }
    if (await tx.user.findUnique({ where: { email } })) {
      return "このメールアドレスは既に登録されています";
    }
    await tx.user.create({ data: { name, email, passwordHash } });
    return null;
  });
  if (error) return { error };

  await signIn("credentials", { email, password, redirectTo: "/dashboard" });
  return {};
}

const loginSchema = z.object({
  email: z.string().trim().email("メールアドレスの形式が正しくありません"),
  password: z.string().min(1, "パスワードを入力してください"),
});

function safeCallbackUrl(v: FormDataEntryValue | null): string {
  if (typeof v === "string" && v.startsWith("/") && !v.startsWith("//")) return v;
  return "/dashboard";
}

export async function loginUser(
  _prevState: AuthFormState,
  formData: FormData,
): Promise<AuthFormState> {
  const parsed = loginSchema.safeParse({
    email: formData.get("email"),
    password: formData.get("password"),
  });
  if (!parsed.success) {
    return { error: parsed.error.issues[0]?.message ?? "入力内容を確認してください" };
  }

  try {
    await signIn("credentials", {
      email: parsed.data.email,
      password: parsed.data.password,
      redirectTo: safeCallbackUrl(formData.get("callbackUrl")),
    });
  } catch (error) {
    if (error && typeof error === "object" && "type" in error) {
      return { error: "メールアドレスまたはパスワードが正しくありません" };
    }
    throw error;
  }
  return {};
}
