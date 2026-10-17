import Link from "next/link";
import { SignupForm } from "@/components/ai/signup-form";
import { cardClass } from "@/components/ui";

export default function AiSignupPage() {
  return (
    <div className="mx-auto max-w-md py-8">
      <h1 className="text-2xl font-bold text-slate-900">無料で登録</h1>
      <p className="mt-2 text-sm text-slate-500">登録後、お店の情報を入力すると計測を始められます。カード登録は不要です。</p>
      <div className={`mt-6 ${cardClass}`}>
        <SignupForm />
      </div>
      <p className="mt-6 text-center text-sm text-slate-500">
        登録済みの方は{" "}
        <Link href="/ai/login" className="font-semibold text-brand hover:underline">
          ログイン
        </Link>
      </p>
    </div>
  );
}
