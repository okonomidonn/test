import Link from "next/link";
import { LoginForm } from "@/components/login-form";

export default async function LoginPage({
  searchParams,
}: {
  searchParams: Promise<{ callbackUrl?: string }>;
}) {
  const { callbackUrl } = await searchParams;

  return (
    <div className="mx-auto flex max-w-md flex-col px-4 py-16 sm:px-6">
      <h1 className="text-2xl font-black text-slate-900">ログイン</h1>
      <p className="mt-2 text-sm text-slate-500">zaitoアカウントにログインしてください。</p>

      <div className="mt-8 rounded-2xl border border-slate-100 p-6 shadow-sm">
        <LoginForm callbackUrl={callbackUrl} />
      </div>

      <p className="mt-6 text-center text-sm text-slate-500">
        アカウントをお持ちでない方は{" "}
        <Link href="/register" className="font-semibold text-brand-dark hover:underline">
          会員登録
        </Link>
      </p>
    </div>
  );
}
