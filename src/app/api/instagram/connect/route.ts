import { randomBytes } from "node:crypto";
import { NextResponse, type NextRequest } from "next/server";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { OAUTH_COOKIE, authorizeUrl, instagramConfig, redirectUri } from "@/lib/instagram";

export async function GET(req: NextRequest) {
  const session = await auth();
  if (!session?.user?.id) return NextResponse.redirect(new URL("/login", req.url));

  const accountId = req.nextUrl.searchParams.get("accountId") ?? "";
  const account = await prisma.socialAccount.findUnique({ where: { id: accountId } });
  if (!account || account.platform !== "INSTAGRAM") {
    return NextResponse.redirect(new URL("/clients", req.url));
  }

  const cfg = instagramConfig();
  if (!cfg) {
    return NextResponse.redirect(new URL(`/clients/${account.clientId}?ig=not_configured`, req.url));
  }

  const state = randomBytes(16).toString("base64url");
  const res = NextResponse.redirect(authorizeUrl(cfg.appId, redirectUri(req.nextUrl.origin), state));
  res.cookies.set(OAUTH_COOKIE, `${state}.${account.id}`, {
    httpOnly: true,
    secure: req.nextUrl.protocol === "https:",
    sameSite: "lax",
    maxAge: 600,
    path: "/api/instagram",
  });
  return res;
}
