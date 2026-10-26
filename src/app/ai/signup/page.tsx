import Link from "next/link";
import { SignupForm } from "@/components/ai/signup-form";
import { AuthShell } from "@/components/ai/auth-shell";

export default function AiSignupPage() {
  return (
    <AuthShell
      title="無料で登録"
      sub="登録後、サービスの情報を入力すると計測を始められます。カード登録は不要です。"
      footer={
        <>
          登録済みの方は{" "}
          <Link href="/ai/login" className="font-semibold text-brand hover:underline">
            ログイン
          </Link>
        </>
      }
    >
      <SignupForm />
    </AuthShell>
  );
}
