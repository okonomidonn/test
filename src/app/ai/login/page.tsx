import Link from "next/link";
import { LoginForm } from "@/components/login-form";
import { cardClass } from "@/components/ui";

export default function AiLoginPage() {
  return (
    <div className="mx-auto max-w-md py-8">
      <h1 className="text-2xl font-bold text-slate-900">ログイン</h1>
      <div className={`mt-6 ${cardClass}`}>
        <LoginForm callbackUrl="/ai/app" />
      </div>
      <p className="mt-6 text-center text-sm text-slate-600">
        はじめての方は{" "}
        <Link href="/ai/signup" className="font-semibold text-brand hover:underline">
          無料で登録
        </Link>
      </p>
    </div>
  );
}
