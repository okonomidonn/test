const GRAPH = "https://graph.instagram.com/v25.0";

export const OAUTH_COOKIE = "ig_oauth";

export const INSTAGRAM_SCOPES = [
  "instagram_business_basic",
  "instagram_business_content_publish",
  "instagram_business_manage_insights",
];

export class InstagramError extends Error {}

export function instagramConfig() {
  const appId = process.env.INSTAGRAM_APP_ID;
  const appSecret = process.env.INSTAGRAM_APP_SECRET;
  if (!appId || !appSecret) return null;
  return { appId, appSecret };
}

export function redirectUri(origin: string) {
  return `${process.env.APP_URL ?? origin}/api/instagram/callback`;
}

export function authorizeUrl(appId: string, redirect: string, state: string) {
  const url = new URL("https://www.instagram.com/oauth/authorize");
  url.searchParams.set("client_id", appId);
  url.searchParams.set("redirect_uri", redirect);
  url.searchParams.set("response_type", "code");
  url.searchParams.set("scope", INSTAGRAM_SCOPES.join(","));
  url.searchParams.set("state", state);
  return url.toString();
}

async function parse<T>(res: Response): Promise<T> {
  const body = await res.json().catch(() => ({}));
  if (!res.ok || body.error) {
    const msg = body.error?.message ?? body.error_message ?? `HTTP ${res.status}`;
    throw new InstagramError(msg);
  }
  return body as T;
}

export async function exchangeCode(code: string, redirect: string) {
  const cfg = instagramConfig();
  if (!cfg) throw new InstagramError("Instagram連携が設定されていません");

  const res = await fetch("https://api.instagram.com/oauth/access_token", {
    method: "POST",
    body: new URLSearchParams({
      client_id: cfg.appId,
      client_secret: cfg.appSecret,
      grant_type: "authorization_code",
      redirect_uri: redirect,
      code,
    }),
  });
  const short = await parse<{ access_token?: string; data?: { access_token: string }[] }>(res);
  const shortToken = short.access_token ?? short.data?.[0]?.access_token;
  if (!shortToken) throw new InstagramError("アクセストークンを取得できませんでした");

  const long = await parse<{ access_token: string; expires_in: number }>(
    await fetch(
      `https://graph.instagram.com/access_token?${new URLSearchParams({
        grant_type: "ig_exchange_token",
        client_secret: cfg.appSecret,
        access_token: shortToken,
      })}`,
    ),
  );
  return { accessToken: long.access_token, expiresAt: new Date(Date.now() + long.expires_in * 1000) };
}

export async function refreshToken(accessToken: string) {
  const r = await parse<{ access_token: string; expires_in: number }>(
    await fetch(
      `https://graph.instagram.com/refresh_access_token?${new URLSearchParams({
        grant_type: "ig_refresh_token",
        access_token: accessToken,
      })}`,
    ),
  );
  return { accessToken: r.access_token, expiresAt: new Date(Date.now() + r.expires_in * 1000) };
}

export async function getProfile(accessToken: string) {
  return parse<{ user_id: string; username: string; followers_count?: number }>(
    await fetch(`${GRAPH}/me?${new URLSearchParams({ fields: "user_id,username,followers_count", access_token: accessToken })}`),
  );
}

export async function createReelContainer(igUserId: string, accessToken: string, videoUrl: string, caption: string) {
  const r = await parse<{ id: string }>(
    await fetch(`${GRAPH}/${igUserId}/media`, {
      method: "POST",
      body: new URLSearchParams({ media_type: "REELS", video_url: videoUrl, caption, access_token: accessToken }),
    }),
  );
  return r.id;
}

export type ContainerStatus = "EXPIRED" | "ERROR" | "FINISHED" | "IN_PROGRESS" | "PUBLISHED";

export async function getContainerStatus(containerId: string, accessToken: string) {
  return parse<{ status_code: ContainerStatus; status?: string }>(
    await fetch(`${GRAPH}/${containerId}?${new URLSearchParams({ fields: "status_code,status", access_token: accessToken })}`),
  );
}

export async function publishContainer(igUserId: string, accessToken: string, containerId: string) {
  const r = await parse<{ id: string }>(
    await fetch(`${GRAPH}/${igUserId}/media_publish`, {
      method: "POST",
      body: new URLSearchParams({ creation_id: containerId, access_token: accessToken }),
    }),
  );
  return r.id;
}

export async function getMedia(mediaId: string, accessToken: string) {
  return parse<{ permalink?: string; timestamp?: string }>(
    await fetch(`${GRAPH}/${mediaId}?${new URLSearchParams({ fields: "permalink,timestamp", access_token: accessToken })}`),
  );
}

const REEL_METRICS = ["views", "likes", "comments", "shares", "saved"] as const;

export async function getReelInsights(mediaId: string, accessToken: string) {
  const r = await parse<{ data: { name: string; values?: { value: number }[]; total_value?: { value: number } }[] }>(
    await fetch(
      `${GRAPH}/${mediaId}/insights?${new URLSearchParams({ metric: REEL_METRICS.join(","), access_token: accessToken })}`,
    ),
  );
  const get = (name: string) => {
    const m = r.data.find((d) => d.name === name);
    return m?.values?.[0]?.value ?? m?.total_value?.value ?? 0;
  };
  return {
    impressions: get("views"),
    likes: get("likes"),
    comments: get("comments"),
    shares: get("shares"),
    saves: get("saved"),
  };
}
