import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { RegisterForm } from "@/components/register-form";
import { cardClass } from "@/components/ui";

export default async function RegisterPage({ searchParams }: PageProps<"/register">) {
  const { invite } = await searchParams;
  const token = typeof invite === "string" ? invite : undefined;
  const isFirstUser = (await prisma.user.count()) === 0;
  const canRegister = isFirstUser || !!token;

  return (
    <div className="mx-auto max-w-md py-8">
      <h1 className="text-2xl font-bold text-slate-900">スタッフ登録</h1>
      <p className="mt-2 text-sm text-slate-500">
        {isFirstUser
          ? "最初の管理者アカウントを作成します。以降のメンバーは招待リンクから登録します。"
          : "招待リンクから運用チームのメンバーとして登録します。"}
      </p>
      <div className={`mt-6 ${cardClass}`}>
        {canRegister ? (
          <RegisterForm invite={token} />
        ) : (
          <p className="text-sm text-slate-600">
            新規登録は招待制です。チームのメンバーに招待リンクを発行してもらってください。
          </p>
        )}
      </div>
      <p className="mt-6 text-center text-sm text-slate-500">
        既にアカウントをお持ちの方は{" "}
        <Link href="/login" className="font-semibold text-brand hover:underline">
          ログイン
        </Link>
      </p>
    </div>
  );
}
