import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { RegisterForm } from "@/components/register-form";
import { AuthShell } from "@/components/ai/auth-shell";

export const metadata = { title: "運営アカウントを作成 | AIススメル" };

export default async function OperatorRegisterPage({ searchParams }: PageProps<"/ai/register">) {
  const { invite } = await searchParams;
  const token = typeof invite === "string" ? invite : undefined;
  const isFirstUser = (await prisma.user.count({ where: { role: "TEAM" } })) === 0;
  return <AuthShell title="運営アカウントを作成" sub="診断と申し込みを管理する、運営者用のアカウントです。" footer={<Link href="/ai/login" className="font-semibold text-brand underline">登録済みの方はログイン</Link>}>
    {isFirstUser || token ? <>
      <p className="mb-5 text-sm text-slate-700">{isFirstUser ? "最初の運営アカウントを作成します。" : "招待された運営メンバーとして登録します。"}登録後はAIススメルの管理画面へ進みます。</p>
      <RegisterForm invite={token} callbackUrl="/ai/app" />
      <p className="mt-5 border-t border-slate-200 pt-4 text-xs text-slate-600">運営アカウントはSocialDeskと共通で、両方の管理画面を利用できます。お客様用の登録画面ではありません。</p>
    </> : <p className="text-sm text-slate-700">運営アカウントは招待制です。既存の運営メンバーに招待リンクを発行してもらってください。</p>}
  </AuthShell>;
}
