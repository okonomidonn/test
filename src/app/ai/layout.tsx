import type { Metadata } from "next";
import Link from "next/link";
import { auth, signOut } from "@/auth";

export const metadata: Metadata = {
  title: "AI検索チェック | 飲食店のAI集客",
  description: "ChatGPT・Geminiでお店がどう紹介されているかを計測し、改善点を提案する飲食店向けツール",
};

export default async function AiLayout({ children }: LayoutProps<"/ai">) {
  const session = await auth();
  const user = session?.user;

  return (
    <>
      <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
          <Link href={user ? "/ai/app" : "/ai"} className="flex items-center gap-2 text-lg font-bold tracking-tight text-slate-900">
            <span className="grid h-8 w-8 place-items-center rounded-lg bg-brand text-sm text-white">AI</span>
            AI検索チェック
          </Link>
          {user ? (
            <div className="flex items-center gap-3 text-sm">
              <Link href="/ai/app" className="rounded-md px-3 py-1.5 font-medium text-slate-600 hover:bg-slate-100">
                店舗一覧
              </Link>
              {user.role === "TEAM" && (
                <Link href="/dashboard" className="rounded-md px-3 py-1.5 font-medium text-slate-600 hover:bg-slate-100">
                  SocialDesk
                </Link>
              )}
              <form
                action={async () => {
                  "use server";
                  await signOut({ redirectTo: "/ai" });
                }}
              >
                <button type="submit" className="rounded-md border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50">
                  ログアウト
                </button>
              </form>
            </div>
          ) : (
            <div className="flex items-center gap-2 text-sm">
              <Link href="/ai/login" className="rounded-md px-3 py-1.5 font-medium text-slate-600 hover:bg-slate-100">
                ログイン
              </Link>
              <Link href="/ai/signup" className="rounded-lg bg-brand px-4 py-2 font-semibold text-white hover:bg-brand-dark">
                無料で始める
              </Link>
            </div>
          )}
        </div>
      </header>
      <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6">{children}</main>
    </>
  );
}
