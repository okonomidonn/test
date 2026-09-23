import Link from "next/link";
import { RegisterForm } from "@/components/register-form";
import { cardClass } from "@/components/ui";

export default function RegisterPage() {
  return (
    <div className="mx-auto max-w-md py-8">
      <h1 className="text-2xl font-bold text-slate-900">スタッフ登録</h1>
      <p className="mt-2 text-sm text-slate-500">運用チームのメンバーとしてアカウントを作成します。</p>
      <div className={`mt-6 ${cardClass}`}>
        <RegisterForm />
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
