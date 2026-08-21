import Link from "next/link";
import { auth, signOut } from "@/auth";

export async function Header() {
  const session = await auth();
  const user = session?.user;

  return (
    <header className="sticky top-0 z-40 border-b border-slate-100 bg-white/90 backdrop-blur">
      <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
        <Link href="/" className="flex items-center gap-1.5 text-2xl font-black tracking-tight text-brand-dark">
          zaito
          <span className="hidden text-xs font-medium text-slate-400 sm:inline">在宅ワーク専門</span>
        </Link>

        <nav className="flex items-center gap-2 text-sm sm:gap-4">
          <Link
            href="/jobs"
            className="rounded-full px-3 py-2 font-medium text-slate-700 hover:bg-emerald-50 hover:text-brand-dark"
          >
            求人を探す
          </Link>

          {!user && (
            <>
              <Link
                href="/company"
                className="hidden rounded-full px-3 py-2 font-medium text-slate-700 hover:bg-emerald-50 hover:text-brand-dark sm:inline"
              >
                企業の方へ
              </Link>
              <Link
                href="/login"
                className="rounded-full px-3 py-2 font-medium text-slate-700 hover:bg-emerald-50 hover:text-brand-dark"
              >
                ログイン
              </Link>
              <Link
                href="/register"
                className="rounded-full bg-brand px-4 py-2 font-semibold text-white shadow-sm hover:bg-brand-dark"
              >
                会員登録
              </Link>
            </>
          )}

          {user?.role === "SEEKER" && (
            <Link
              href="/mypage"
              className="rounded-full px-3 py-2 font-medium text-slate-700 hover:bg-emerald-50 hover:text-brand-dark"
            >
              マイページ
            </Link>
          )}

          {user?.role === "COMPANY" && (
            <Link
              href="/company"
              className="rounded-full px-3 py-2 font-medium text-slate-700 hover:bg-emerald-50 hover:text-brand-dark"
            >
              企業ダッシュボード
            </Link>
          )}

          {user && (
            <form
              action={async () => {
                "use server";
                await signOut({ redirectTo: "/" });
              }}
            >
              <button
                type="submit"
                className="rounded-full border border-slate-200 px-3 py-2 font-medium text-slate-600 hover:border-slate-300 hover:bg-slate-50"
              >
                ログアウト
              </button>
            </form>
          )}
        </nav>
      </div>
    </header>
  );
}
