import Link from "next/link";
import { auth, signOut } from "@/auth";

const NAV = [
  { href: "/dashboard", label: "分析" },
  { href: "/posts", label: "投稿" },
  { href: "/clients", label: "クライアント" },
];

export async function Header() {
  const session = await auth();
  const user = session?.user;

  return (
    <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
      <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6">
        <div className="flex items-center gap-6">
          <Link href="/" className="text-xl font-bold tracking-tight text-brand">
            SocialDesk
          </Link>
          {user && (
            <nav className="flex items-center gap-1 text-sm">
              {NAV.map((item) => (
                <Link
                  key={item.href}
                  href={item.href}
                  className="rounded-md px-3 py-1.5 font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900"
                >
                  {item.label}
                </Link>
              ))}
            </nav>
          )}
        </div>

        {user && (
          <div className="flex items-center gap-3 text-sm">
            <span className="hidden text-slate-500 sm:inline">{user.name}</span>
            <form
              action={async () => {
                "use server";
                await signOut({ redirectTo: "/login" });
              }}
            >
              <button
                type="submit"
                className="rounded-md border border-slate-200 px-3 py-1.5 text-slate-600 hover:bg-slate-50"
              >
                ログアウト
              </button>
            </form>
          </div>
        )}
      </div>
    </header>
  );
}
