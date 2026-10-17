import Link from "next/link";
import { LoginForm } from "@/components/login-form";
import { cardClass } from "@/components/ui";

export default async function LoginPage({ searchParams }: PageProps<"/login">) {
  const { callbackUrl } = await searchParams;

  return (
    <div className="mx-auto max-w-md py-8">
      <h1 className="text-2xl font-bold text-slate-900">ログイン</h1>
      <p className="mt-2 text-sm text-slate-500">SocialDeskのスタッフアカウントでログインしてください。</p>
      <div className={`mt-6 ${cardClass}`}>
        <LoginForm callbackUrl={typeof callbackUrl === "string" ? callbackUrl : undefined} />
      </div>
      <p className="mt-6 text-center text-sm text-slate-500">
        アカウントをお持ちでない方は{" "}
        <Link href="/register" className="font-semibold text-brand hover:underline">
          新規登録
        </Link>
      </p>
    </div>
  );
}
