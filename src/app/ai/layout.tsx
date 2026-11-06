import type { Metadata } from "next";
import Link from "next/link";
import { Montserrat, Noto_Sans_JP } from "next/font/google";
import { auth, signOut } from "@/auth";
import { btn } from "@/components/ai/ds";
import { Logo } from "@/components/ai/logo";
import { CONTACT } from "@/lib/contact";

// 数字は Montserrat、文章は Noto Sans JP（日本語は文字数が多いため先読みせず、使う文字だけ読み込む）
const latin = Montserrat({ variable: "--font-ai-latin", subsets: ["latin"], weight: ["600", "700", "800"], display: "swap" });
const jp = Noto_Sans_JP({ variable: "--font-ai-jp", weight: ["400", "500", "700", "900"], preload: false, display: "swap" });

export const metadata: Metadata = {
  title: "AIススメル | 求人サイトのAI検索対策",
  description: "求人サイト・人材サービス専門のAI検索対策。ChatGPT・Geminiでの紹介状況を調べ、サイトの中と外の直し方までご提案します。",
};

export default async function AiLayout({ children }: LayoutProps<"/ai">) {
  const session = await auth();
  const user = session?.user;

  const nav = "rounded-md px-1.5 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 sm:px-2.5";

  return (
    <div className={`${latin.variable} ${jp.variable} ai-scope flex flex-1 flex-col overflow-x-clip`}>
      <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur print:hidden">
        <div className="mx-auto flex min-h-14 max-w-6xl flex-wrap items-center gap-2 px-3 py-1.5 sm:h-14 sm:flex-nowrap sm:gap-6 sm:px-6 sm:py-0">
          <Link href={user ? "/ai/app" : "/ai"} aria-label="AIススメル" className="flex min-h-11 shrink-0 items-center">
            <Logo />
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
              <nav className="hidden items-center gap-1 lg:flex" aria-label="メインメニュー">
                <Link href="/ai#why" className={nav}>
                  求職者のAI利用
                </Link>
                <Link href="/ai#case" className={nav}>
                  実例
                </Link>
                <Link href="/ai#features" className={nav}>
                  診断の内容
                </Link>
                <Link href="/ai/pricing" className={nav}>
                  料金
                </Link>
                <Link href="/ai#faq" className={nav}>
                  よくある質問
                </Link>
              </nav>
              <div className="ml-auto flex items-center gap-1 whitespace-nowrap sm:gap-2">
                <Link href="/ai/apply?plan=trial" className="hidden min-h-9 items-center rounded-md border border-slate-300 px-4 text-sm font-bold text-slate-800 transition hover:bg-slate-50 lg:inline-flex">
                  ご相談
                </Link>
                <Link href="/ai/apply" className={`${btn("primary")} min-h-11 max-[359px]:px-2 lg:min-h-9`} aria-label="無料で診断">
                  無料で診断
                </Link>
                <details className="relative lg:hidden">
                  <summary aria-label="メニュー" className="flex min-h-11 min-w-11 cursor-pointer list-none items-center justify-center rounded-lg px-2 text-sm font-semibold text-slate-700 focus-visible:outline-2 focus-visible:outline-brand"><span className="hidden min-[400px]:inline">メニュー</span><span aria-hidden className="text-lg min-[400px]:ml-1">☰</span></summary>
                  <nav aria-label="メインメニュー" className="absolute right-0 top-full z-50 mt-2 w-64 rounded-xl border border-slate-200 bg-white p-2 shadow-lg">
                    {[["/ai#why", "求職者のAI利用"], ["/ai#case", "実例"], ["/ai#features", "診断の内容"], ["/ai/pricing", "料金"], ["/ai#faq", "よくある質問"]].map(([href, label]) => <a key={href} href={href} className="block rounded-lg px-4 py-3 text-base font-medium text-slate-800 hover:bg-blue-50">{label}</a>)}
                  </nav>
                </details>
              </div>
            </>
          )}
        </div>
      </header>
      <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8 sm:px-6 print:max-w-none print:p-0">{children}</main>
      <footer className="bg-[#0B1530] text-slate-300 print:hidden">
        <div className="mx-auto grid max-w-6xl gap-10 px-4 py-14 sm:grid-cols-[1fr_auto_auto] sm:gap-16 sm:px-6">
          <div>
            <Logo light />
            <p className="mt-3 text-sm text-slate-400">求人サイト・人材サービスのAI検索対策</p>
            <p className="mt-3 text-sm text-slate-400">お問い合わせ：{CONTACT.email}</p>
            <p className="mt-8 text-xs text-slate-500">© 2026 AIススメル</p>
          </div>
          <nav aria-label="サービス" className="space-y-2 text-sm">
            <p className="font-semibold text-white">サービス</p>
            <Link href="/ai/sample" className="block text-slate-400 hover:text-white">
              レポートの見本
            </Link>
            <Link href="/ai/pricing" className="block text-slate-400 hover:text-white">
              料金
            </Link>
          </nav>
          <nav aria-label="運営" className="space-y-2 text-sm">
            <p className="font-semibold text-white">運営</p>
            <Link href="/ai/apply" className="block text-slate-400 hover:text-white">
              お申し込み
            </Link>
            <Link href="/ai/method" className="block text-slate-400 hover:text-white">
              計測のしかたと進め方
            </Link>
            <Link href="/ai/privacy" className="block text-slate-400 hover:text-white">
              プライバシーポリシー
            </Link>
            <Link href="/ai/legal" className="block text-slate-400 hover:text-white">
              特定商取引法に基づく表記
            </Link>
            <Link href="/ai/login" className="block text-slate-500 hover:text-white">
              ログイン
            </Link>
          </nav>
        </div>
      </footer>
    </div>
  );
}
