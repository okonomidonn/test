import NextAuth from "next-auth";
import { NextResponse } from "next/server";
import { authConfig } from "@/auth.config";

const { auth } = NextAuth(authConfig);

const TEAM_PUBLIC = ["/login", "/register"];
// AI検索チェック（店舗オーナー向け）の公開ページ
const AI_PUBLIC = ["/ai", "/ai/web", "/ai/apply", "/ai/sample", "/ai/tool", "/ai/method", "/ai/login", "/ai/signup", "/ai/pricing", "/ai/legal"];

export default auth((req) => {
  const { pathname } = req.nextUrl;
  const user = req.auth?.user;
  const isAi = pathname === "/ai" || pathname.startsWith("/ai/");

  if (!user) {
    if (TEAM_PUBLIC.includes(pathname) || AI_PUBLIC.includes(pathname)) return;
    const url = new URL(isAi ? "/ai/login" : "/login", req.nextUrl);
    url.searchParams.set("callbackUrl", pathname + req.nextUrl.search);
    return NextResponse.redirect(url);
  }

  // 店舗オーナーはAI検索チェックの画面だけを使う
  if (user.role === "OWNER" && !isAi) {
    return NextResponse.redirect(new URL("/ai/app", req.nextUrl));
  }
  if (pathname === "/ai/login" || pathname === "/ai/signup") {
    return NextResponse.redirect(new URL("/ai/app", req.nextUrl));
  }
  if (TEAM_PUBLIC.includes(pathname)) {
    return NextResponse.redirect(new URL("/dashboard", req.nextUrl));
  }
});

export const config = {
  matcher: ["/((?!api|_next/static|_next/image|favicon.ico).*)"],
};
