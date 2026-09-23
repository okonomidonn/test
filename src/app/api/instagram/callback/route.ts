import { NextResponse, type NextRequest } from "next/server";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { encrypt } from "@/lib/crypto";
import { OAUTH_COOKIE, exchangeCode, getProfile, redirectUri } from "@/lib/instagram";

export async function GET(req: NextRequest) {
  const session = await auth();
  if (!session?.user?.id) return NextResponse.redirect(new URL("/login", req.url));

  const [expectedState, accountId] = (req.cookies.get(OAUTH_COOKIE)?.value ?? "").split(".");
  const account = accountId ? await prisma.socialAccount.findUnique({ where: { id: accountId } }) : null;
  if (!account) return NextResponse.redirect(new URL("/clients", req.url));

  const back = (result: string) => {
    const res = NextResponse.redirect(new URL(`/clients/${account.clientId}?ig=${result}`, req.url));
    res.cookies.delete({ name: OAUTH_COOKIE, path: "/api/instagram" });
    return res;
  };

  const params = req.nextUrl.searchParams;
  const code = params.get("code");
  if (!expectedState || params.get("state") !== expectedState) return back("invalid_state");
  if (params.get("error") || !code) return back("denied");

  try {
    const { accessToken, expiresAt } = await exchangeCode(code, redirectUri(req.nextUrl.origin));
    const profile = await getProfile(accessToken);

    const conflict = await prisma.socialAccount.findFirst({
      where: { platform: "INSTAGRAM", handle: profile.username, id: { not: account.id } },
    });

    await prisma.socialAccount.update({
      where: { id: account.id },
      data: {
        ...(!conflict && { handle: profile.username }),
        igUserId: profile.user_id,
        accessToken: encrypt(accessToken),
        tokenExpiresAt: expiresAt,
        connectedAt: new Date(),
        ...(profile.followers_count != null && { followers: profile.followers_count }),
      },
    });
    return back("connected");
  } catch (e) {
    console.error("Instagram connect failed", e);
    return back("error");
  }
}
