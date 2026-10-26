import Link from "next/link";
import { LoginForm } from "@/components/login-form";
import { AuthShell } from "@/components/ai/auth-shell";

export default function AiLoginPage() {
  return (
    <AuthShell
      title="ログイン"
      sub="登録したメールアドレスとパスワードを入力してください。"
      footer={
        <>
          はじめての方は{" "}
          <Link href="/ai/apply" className="font-semibold text-brand hover:underline">
            無料で診断する
          </Link>
        </>
      }
    >
      <LoginForm callbackUrl="/ai/app" />
    </AuthShell>
  );
}
