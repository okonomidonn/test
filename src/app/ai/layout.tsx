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
  description: "Webサービス向けのAI検索診断・改善サービス。ChatGPT・Geminiでの紹介状況を調べ、サイトのどこをどう直すかをご提案します。",
};

export default async function AiLayout({ children }: LayoutProps<"/ai">) {
  const session = await auth();
  const user = session?.user;

  const nav = "rounded-md px-1.5 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 sm:px-2.5";

  return (
    <div className={`${latin.variable} ${jp.variable} ai-scope flex flex-1 flex-col`}>
      <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur print:hidden">
        <div className="mx-auto flex min-h-14 max-w-6xl flex-wrap items-center gap-2 px-3 py-1.5 sm:h-14 sm:flex-nowrap sm:gap-6 sm:px-6 sm:py-0">
          <Link href={user ? "/ai/app" : "/ai"} className="flex min-h-11 shrink-0 items-center gap-2 text-[1.0625rem] font-bold tracking-tight text-slate-900">
            <span className="hidden h-7 w-7 place-items-center rounded-md bg-brand text-sm font-bold text-white min-[360px]:grid" aria-hidden>
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
                  診断の内容
                </Link>
                <Link href="/ai/sample" className={nav}>
                  レポートの見本
                </Link>
                <Link href="/ai/pricing" className={nav}>
                  料金
                </Link>
                <Link href="/ai/method" className={nav}>
                  進め方
                </Link>
              </nav>
              <div className="ml-auto flex items-center gap-1 whitespace-nowrap sm:gap-2">
                <Link href="/ai/login" className={`${nav} hidden md:inline-flex`}>
                  ログイン
                </Link>
                <Link href="/ai/apply" className={`${btn("primary")} min-h-11 md:min-h-9`} aria-label="無料チェック">
                  無料チェック
                </Link>
                <details className="relative md:hidden">
                  <summary aria-label="メニュー" className="flex min-h-11 min-w-11 cursor-pointer list-none items-center justify-center rounded-lg px-2 text-sm font-semibold text-slate-700 focus-visible:outline-2 focus-visible:outline-brand"><span className="hidden min-[400px]:inline">メニュー</span><span aria-hidden className="text-lg min-[400px]:ml-1">☰</span></summary>
                  <nav aria-label="メインメニュー" className="absolute right-0 top-full z-50 mt-2 w-64 rounded-xl border border-slate-200 bg-white p-2 shadow-lg">
                    {[["/ai#features", "診断の内容"], ["/ai/sample", "レポートの見本"], ["/ai/pricing", "料金"], ["/ai/method", "進め方・計測方法"], ["/ai/login", "ログイン"]].map(([href, label]) => <a key={href} href={href} className="block rounded-lg px-4 py-3 text-base font-medium text-slate-800 hover:bg-blue-50">{label}</a>)}
                  </nav>
                </details>
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
          </nav>
          <nav aria-label="運営" className="space-y-2 text-sm">
            <p className="font-semibold text-slate-900">運営</p>
            <Link href="/ai/apply" className="block text-slate-600 hover:text-slate-900">
              お申し込み
            </Link>
            <Link href="/ai/method" className="block text-slate-600 hover:text-slate-900">
              計測のしかたと進め方
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
