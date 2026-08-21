import Link from "next/link";

export function Footer() {
  return (
    <footer className="border-t border-slate-100 bg-slate-50">
      <div className="mx-auto max-w-6xl px-4 py-10 sm:px-6">
        <div className="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
          <div>
            <p className="text-xl font-black text-brand-dark">zaito</p>
            <p className="mt-2 max-w-xs text-sm text-slate-500">
              在宅ワークに特化した求人サイト。あなたのライフスタイルに合う「おうちでできる仕事」を見つけよう。
            </p>
          </div>

          <div className="flex gap-12 text-sm">
            <div>
              <p className="font-semibold text-slate-700">求職者の方</p>
              <ul className="mt-3 space-y-2 text-slate-500">
                <li>
                  <Link href="/jobs" className="hover:text-brand-dark">
                    求人を探す
                  </Link>
                </li>
                <li>
                  <Link href="/register" className="hover:text-brand-dark">
                    会員登録
                  </Link>
                </li>
              </ul>
            </div>
            <div>
              <p className="font-semibold text-slate-700">企業の方</p>
              <ul className="mt-3 space-y-2 text-slate-500">
                <li>
                  <Link href="/register" className="hover:text-brand-dark">
                    求人を掲載する
                  </Link>
                </li>
                <li>
                  <Link href="/company" className="hover:text-brand-dark">
                    企業ダッシュボード
                  </Link>
                </li>
              </ul>
            </div>
          </div>
        </div>

        <p className="mt-8 text-xs text-slate-400">&copy; {new Date().getFullYear()} zaito</p>
      </div>
    </footer>
  );
}
