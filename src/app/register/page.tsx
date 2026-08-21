import Link from "next/link";
import { RegisterForm } from "@/components/register-form";

export default async function RegisterPage({
  searchParams,
}: {
  searchParams: Promise<{ role?: string }>;
}) {
  const { role } = await searchParams;
  const defaultRole = role === "COMPANY" ? "COMPANY" : "SEEKER";

  return (
    <div className="mx-auto flex max-w-md flex-col px-4 py-16 sm:px-6">
      <h1 className="text-2xl font-black text-slate-900">会員登録</h1>
      <p className="mt-2 text-sm text-slate-500">
        求職者・企業のどちらでも無料で登録できます。
      </p>

      <div className="mt-8 rounded-2xl border border-slate-100 p-6 shadow-sm">
        <RegisterForm defaultRole={defaultRole} />
      </div>

      <p className="mt-6 text-center text-sm text-slate-500">
        既にアカウントをお持ちの方は{" "}
        <Link href="/login" className="font-semibold text-brand-dark hover:underline">
          ログイン
        </Link>
      </p>
    </div>
  );
}
