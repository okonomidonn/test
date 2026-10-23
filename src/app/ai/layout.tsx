import type { Metadata } from "next";
import Link from "next/link";
import { BIZ_UDPGothic, IBM_Plex_Mono, Zen_Kaku_Gothic_New } from "next/font/google";
import { auth, signOut } from "@/auth";

// 本文：UD書体（読みやすさ優先）、見出し：角ゴシックの太字、数字：等幅
const body = BIZ_UDPGothic({ variable: "--font-ai-body", weight: ["400", "700"], subsets: ["latin"], display: "swap" });
const display = Zen_Kaku_Gothic_New({ variable: "--font-ai-display", weight: ["700", "900"], subsets: ["latin"], display: "swap" });
const num = IBM_Plex_Mono({ variable: "--font-ai-num", weight: ["500", "600"], subsets: ["latin"], display: "swap" });

export const metadata: Metadata = {
  title: "AI検索チェック | 飲食店・WebサービスのAI集客",
  description: "ChatGPT・Geminiでお店やWebサービスがどう紹介されているかを計測し、改善点を提案するツール",
};

export default async function AiLayout({ children }: LayoutProps<"/ai">) {
  const session = await auth();
  const user = session?.user;

  return (
    <div className={`${body.variable} ${display.variable} ${num.variable} ai-scope flex flex-1 flex-col`}>
      <header className="sticky top-0 z-40 border-b border-slate-200 bg-white">
        <div className="mx-auto flex max-w-6xl items-center justify-between gap-2 px-4 py-3 sm:gap-4 sm:px-6">
          <Link href={user ? "/ai/app" : "/ai"} className="flex shrink-0 items-center gap-2.5 font-display text-lg font-black tracking-tight text-slate-900">
            <span className="relative grid h-8 w-8 place-items-center overflow-hidden rounded-md bg-slate-900 text-sm text-white" aria-hidden>
              AI
              <span className="absolute inset-x-1.5 bottom-1.5 h-1 rounded-full bg-mark" />
            </span>
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
        <div className="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-2 px-4 py-6 text-sm text-slate-600 sm:px-6">
          <span className="font-display font-bold text-slate-800">AI検索チェック</span>
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
