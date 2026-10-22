import type { Metadata } from "next";
import Link from "next/link";
import { auth, signOut } from "@/auth";

export const metadata: Metadata = {
  title: "AI検索チェック | 飲食店・WebサービスのAI集客",
  description: "ChatGPT・Geminiでお店やWebサービスがどう紹介されているかを計測し、改善点を提案するツール",
};

export default async function AiLayout({ children }: LayoutProps<"/ai">) {
  const session = await auth();
  const user = session?.user;

  return (
    <div className="ai-scope flex flex-1 flex-col">
      <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div className="mx-auto flex max-w-6xl items-center justify-between gap-2 px-4 py-3 sm:gap-4 sm:px-6">
          <Link href={user ? "/ai/app" : "/ai"} className="flex shrink-0 items-center gap-2 text-lg font-bold tracking-tight text-slate-900">
            <span className="grid h-8 w-8 place-items-center rounded-lg bg-brand text-sm text-white">AI</span>
            {/* スマホでは右側のメニューを優先し、名前を隠す */}
            <span className={user ? "hidden sm:inline" : "hidden min-[400px]:inline"}>AI検索チェック</span>
          </Link>
          {user ? (
            <div className="flex items-center gap-1 whitespace-nowrap text-sm sm:gap-3">
              <Link href="/ai/app" className="rounded-md px-2 py-1.5 font-medium sm:px-3 text-slate-700 hover:bg-slate-100">
                登録一覧
              </Link>
              {user.role === "OWNER" && (
                <Link href="/ai/app/billing" className="rounded-md px-2 py-1.5 font-medium sm:px-3 text-slate-700 hover:bg-slate-100">
                  料金
                </Link>
              )}
              {user.role === "TEAM" && (
                <Link href="/dashboard" className="rounded-md px-2 py-1.5 font-medium sm:px-3 text-slate-700 hover:bg-slate-100">
                  SocialDesk
                </Link>
              )}
              <form
                action={async () => {
                  "use server";
                  await signOut({ redirectTo: "/ai" });
                }}
              >
                <button type="submit" className="rounded-md border border-slate-200 px-2 py-1.5 sm:px-3 text-slate-700 hover:bg-slate-50">
                  ログアウト
                </button>
              </form>
            </div>
          ) : (
            <div className="flex items-center gap-1 whitespace-nowrap text-sm sm:gap-2">
              <Link href="/ai/pricing" className="rounded-md px-2 py-1.5 font-medium sm:px-3 text-slate-700 hover:bg-slate-100">
                料金
              </Link>
              <Link href="/ai/login" className="rounded-md px-2 py-1.5 font-medium sm:px-3 text-slate-700 hover:bg-slate-100">
                ログイン
              </Link>
              <Link href="/ai/signup" className="rounded-lg bg-brand px-3 py-2 font-semibold sm:px-4 text-white hover:bg-brand-dark">
                無料で始める
              </Link>
            </div>
          )}
        </div>
      </header>
      <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6">{children}</main>
      <footer className="border-t border-slate-200 bg-white">
        <div className="mx-auto flex max-w-6xl flex-wrap gap-4 px-4 py-6 text-sm text-slate-600 sm:px-6">
          <Link href="/ai/pricing" className="hover:underline">
            料金プラン
          </Link>
          <Link href="/ai/legal" className="hover:underline">
            特定商取引法に基づく表記
          </Link>
        </div>
      </footer>
    </div>
  );
}
