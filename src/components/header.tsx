import Link from "next/link";
import { auth, signOut } from "@/auth";

export async function Header() {
  const session = await auth();
  const user = session?.user;

  return (
    <header style={{ height: "78px", display: "flex", alignItems: "center" }}>
      <div
        style={{
          width: "min(1160px, calc(100% - 40px))",
          margin: "auto",
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
        }}
      >
        <Link
          href="/"
          style={{
            fontSize: "28px",
            fontWeight: "900",
            letterSpacing: "-1px",
            textDecoration: "none",
            color: "var(--ink)",
          }}
        >
          za<span style={{ color: "var(--aqua)" }}>it</span>o
        </Link>

        <nav
          style={{
            display: "flex",
            gap: "30px",
            fontSize: "14px",
            fontWeight: "700",
            color: "#4c5362",
          }}
        >
          <Link href="/jobs" style={{ textDecoration: "none", color: "inherit" }}>
            求人を探す
          </Link>
          <Link href="#feature" style={{ textDecoration: "none", color: "inherit" }}>
            zaitoとは
          </Link>
        </nav>

        <div style={{ display: "flex", gap: "10px", alignItems: "center" }}>
          {!user && (
            <>
              <Link
                href="/login"
                style={{
                  border: "1px solid #d9d7d0",
                  borderRadius: "999px",
                  padding: "12px 19px",
                  fontWeight: "800",
                  cursor: "pointer",
                  background: "#fff",
                  textDecoration: "none",
                  color: "var(--ink)",
                }}
              >
                ログイン
              </Link>
              <Link
                href="/register"
                style={{
                  border: "0",
                  borderRadius: "999px",
                  padding: "12px 19px",
                  fontWeight: "800",
                  cursor: "pointer",
                  background: "var(--ink)",
                  color: "#fff",
                  textDecoration: "none",
                }}
              >
                無料で登録
              </Link>
            </>
          )}

          {user?.role === "SEEKER" && (
            <>
              <Link
                href="/mypage"
                style={{
                  textDecoration: "none",
                  color: "inherit",
                  padding: "8px 16px",
                  fontSize: "14px",
                }}
              >
                マイページ
              </Link>
              <form
                action={async () => {
                  "use server";
                  await signOut({ redirectTo: "/" });
                }}
              >
                <button
                  type="submit"
                  style={{
                    border: "1px solid #d9d7d0",
                    borderRadius: "999px",
                    padding: "12px 19px",
                    fontWeight: "800",
                    cursor: "pointer",
                    background: "#fff",
                  }}
                >
                  ログアウト
                </button>
              </form>
            </>
          )}

          {user?.role === "COMPANY" && (
            <>
              <Link
                href="/company"
                style={{
                  textDecoration: "none",
                  color: "inherit",
                  padding: "8px 16px",
                  fontSize: "14px",
                }}
              >
                企業ダッシュボード
              </Link>
              <form
                action={async () => {
                  "use server";
                  await signOut({ redirectTo: "/" });
                }}
              >
                <button
                  type="submit"
                  style={{
                    border: "1px solid #d9d7d0",
                    borderRadius: "999px",
                    padding: "12px 19px",
                    fontWeight: "800",
                    cursor: "pointer",
                    background: "#fff",
                  }}
                >
                  ログアウト
                </button>
              </form>
            </>
          )}
        </div>
      </div>
    </header>
  );
}
