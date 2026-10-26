import type { Metadata } from "next";
import Link from "next/link";
import { Inter, Noto_Sans_JP } from "next/font/google";
import { auth, signOut } from "@/auth";
import { btn } from "@/components/ai/ds";

// 英数字は Inter、日本語は Noto Sans JP（日本語は文字数が多いため先読みせず、使う文字だけ読み込む）
const latin = Inter({ variable: "--font-ai-latin", subsets: ["latin"], display: "swap" });
const jp = Noto_Sans_JP({ variable: "--font-ai-jp", weight: ["400", "500", "700"], preload: false, display: "swap" });

export const metadata: Metadata = {
  title: "ススメル | WebサービスのAI検索対策",
  description: "ChatGPT・GeminiでWebサービスがどう紹介されているかを計測し、改善点を提案するツール",
};

export default async function AiLayout({ children }: LayoutProps<"/ai">) {
  const session = await auth();
  const user = session?.user;

  const nav = "rounded-md px-2.5 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900";

  return (
    <div className={`${latin.variable} ${jp.variable} ai-scope flex flex-1 flex-col`}>
      <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur print:hidden">
        <div className="mx-auto flex h-14 max-w-6xl items-center gap-2 px-4 sm:gap-6 sm:px-6">
          <Link href={user ? "/ai/app" : "/ai"} className="flex shrink-0 items-center gap-2 text-[1.0625rem] font-bold tracking-tight text-slate-900">
            <span className="grid h-7 w-7 place-items-center rounded-md bg-brand text-sm font-bold text-white" aria-hidden>
              ス
            </span>
            ススメル
          </Link>
          {user ? (
            <nav className="ml-auto flex items-center gap-0.5 whitespace-nowrap sm:gap-1">
              <Link href="/ai/app" className={nav}>
                サービス一覧
              </Link>
              {user.role === "OWNER" && (
                <Link href="/ai/app/billing" className={nav}>
                  料金
                </Link>
              )}
              {user.role === "TEAM" && (
                <Link href="/ai/app/leads" className={nav}>
                  申し込み
                </Link>
              )}
              {user.role === "TEAM" && (
                <Link href="/dashboard" className={`${nav} hidden sm:inline-flex`}>
                  SocialDesk
                </Link>
              )}
              <form
                action={async () => {
                  "use server";
                  await signOut({ redirectTo: "/ai" });
                }}
              >
                <button type="submit" className={btn("secondary")}>
                  ログアウト
                </button>
              </form>
            </nav>
          ) : (
            <>
              <nav className="hidden items-center gap-1 md:flex" aria-label="メインメニュー">
                <Link href="/ai#features" className={nav}>
                  できること
                </Link>
                <Link href="/ai/sample" className={nav}>
                  サンプルレポート
                </Link>
                <Link href="/ai/pricing" className={nav}>
                  料金
                </Link>
                <Link href="/ai/tool" className={nav}>
                  セルフ版
                </Link>
              </nav>
              <div className="ml-auto flex items-center gap-1 whitespace-nowrap sm:gap-2">
                <Link href="/ai/pricing" className={`${nav} md:hidden`}>
                  料金
                </Link>
                <Link href="/ai/login" className={nav}>
                  ログイン
                </Link>
                <Link href="/ai/apply" className={btn("primary")}>
                  無料で診断する
                </Link>
              </div>
            </>
          )}
        </div>
      </header>
      <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 print:max-w-none print:p-0">{children}</main>
      <footer className="border-t border-slate-200 bg-slate-50 print:hidden">
        <div className="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:grid-cols-[1fr_auto_auto] sm:px-6">
          <div>
            <p className="flex items-center gap-2 font-bold text-slate-900">
              <span className="grid h-6 w-6 place-items-center rounded bg-brand text-xs font-bold text-white" aria-hidden>
                ス
              </span>
              ススメル
            </p>
            <p className="mt-2 text-sm text-slate-600">WebサービスのAI検索診断と改善</p>
          </div>
          <nav aria-label="サービス" className="space-y-2 text-sm">
            <p className="font-semibold text-slate-900">サービス</p>
            <Link href="/ai/sample" className="block text-slate-600 hover:text-slate-900">
              サンプルレポート
            </Link>
            <Link href="/ai/pricing" className="block text-slate-600 hover:text-slate-900">
              料金
            </Link>
            <Link href="/ai/tool" className="block text-slate-600 hover:text-slate-900">
              セルフ版ツール
            </Link>
          </nav>
          <nav aria-label="運営" className="space-y-2 text-sm">
            <p className="font-semibold text-slate-900">運営</p>
            <Link href="/ai/apply" className="block text-slate-600 hover:text-slate-900">
              お申し込み
            </Link>
            <Link href="/ai/legal" className="block text-slate-600 hover:text-slate-900">
              特定商取引法に基づく表記
            </Link>
          </nav>
        </div>
      </footer>
    </div>
  );
}
