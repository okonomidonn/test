import NextAuth from "next-auth";
import { NextResponse } from "next/server";
import { authConfig } from "@/auth.config";

const { auth } = NextAuth(authConfig);

export default auth((req) => {
  const { nextUrl } = req;
  const isLoggedIn = !!req.auth;
  const role = req.auth?.user?.role;

  const isMypage = nextUrl.pathname.startsWith("/mypage");
  const isCompanyArea = nextUrl.pathname.startsWith("/company");

  if (isMypage) {
    if (!isLoggedIn) {
      return NextResponse.redirect(new URL("/login", nextUrl));
    }
    if (role !== "SEEKER") {
      return NextResponse.redirect(new URL("/", nextUrl));
    }
  }

  if (isCompanyArea) {
    if (!isLoggedIn) {
      return NextResponse.redirect(new URL("/login", nextUrl));
    }
    if (role !== "COMPANY") {
      return NextResponse.redirect(new URL("/", nextUrl));
    }
  }
});

export const config = {
  matcher: ["/mypage/:path*", "/company/:path*"],
};
