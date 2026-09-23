import { prisma } from "@/lib/prisma";
import { decrypt, encrypt } from "@/lib/crypto";
import {
  InstagramError,
  createReelContainer,
  getContainerStatus,
  getMedia,
  getProfile,
  getReelInsights,
  publishContainer,
  refreshToken,
} from "@/lib/instagram";

const LOCK_TTL_MS = 2 * 60 * 1000;
const PROCESSING_TIMEOUT_MS = 60 * 60 * 1000;
const METRICS_INTERVAL_MS = 6 * 60 * 60 * 1000;
const METRICS_WINDOW_MS = 90 * 24 * 60 * 60 * 1000;
const TOKEN_REFRESH_BEFORE_MS = 10 * 24 * 60 * 60 * 1000;

type PostWithAccount = NonNullable<Awaited<ReturnType<typeof loadPost>>>;

function loadPost(id: string) {
  return prisma.post.findUnique({ where: { id }, include: { account: true } });
}

export function isAutoPublish(post: { mediaUrl: string | null; account: { platform: string; igUserId: string | null; accessToken: string | null } }) {
  return post.account.platform === "INSTAGRAM" && !!post.account.igUserId && !!post.account.accessToken && !!post.mediaUrl;
}

function errorMessage(e: unknown) {
  return e instanceof InstagramError ? e.message : e instanceof Error ? e.message : String(e);
}

async function withLock<T>(postId: string, fn: () => Promise<T>): Promise<T | undefined> {
  const now = new Date();
  const { count } = await prisma.post.updateMany({
    where: { id: postId, OR: [{ lockedAt: null }, { lockedAt: { lt: new Date(now.getTime() - LOCK_TTL_MS) } }] },
    data: { lockedAt: now },
  });
  if (count === 0) return undefined;
  try {
    return await fn();
  } finally {
    await prisma.post.update({ where: { id: postId }, data: { lockedAt: null } });
  }
}

async function fail(postId: string, message: string) {
  await prisma.post.update({ where: { id: postId }, data: { status: "FAILED", publishError: message } });
}

async function startPublish(post: PostWithAccount) {
  const token = decrypt(post.account.accessToken!);
  try {
    const containerId = await createReelContainer(post.account.igUserId!, token, post.mediaUrl!, post.content);
    await prisma.post.update({
      where: { id: post.id },
      data: {
        status: "PUBLISHING",
        containerId,
        publishStartedAt: new Date(),
        publishError: null,
        publishAttempts: { increment: 1 },
      },
    });
  } catch (e) {
    await fail(post.id, `コンテナ作成に失敗: ${errorMessage(e)}`);
  }
}

async function checkAndPublish(post: PostWithAccount) {
  const token = decrypt(post.account.accessToken!);
  try {
    const status = await getContainerStatus(post.containerId!, token);
    if (status.status_code === "IN_PROGRESS") {
      if (post.publishStartedAt && Date.now() - post.publishStartedAt.getTime() > PROCESSING_TIMEOUT_MS) {
        await fail(post.id, "Instagram側の動画処理がタイムアウトしました");
      }
      return;
    }
    if (status.status_code === "ERROR" || status.status_code === "EXPIRED") {
      await fail(post.id, `Instagram側で動画を処理できませんでした（${status.status ?? status.status_code}）`);
      return;
    }
    const mediaId = status.status_code === "FINISHED" ? await publishContainer(post.account.igUserId!, token, post.containerId!) : null;
    const media = mediaId ? await getMedia(mediaId, token).catch(() => null) : null;
    await prisma.post.update({
      where: { id: post.id },
      data: {
        status: "PUBLISHED",
        publishedAt: new Date(),
        externalId: mediaId,
        permalink: media?.permalink ?? null,
        publishError: null,
      },
    });
  } catch (e) {
    await fail(post.id, `公開に失敗: ${errorMessage(e)}`);
  }
}

/** Advances an auto-publish post by one step. `force` publishes a scheduled post before its time. */
export async function advancePost(postId: string, { force = false } = {}) {
  return withLock(postId, async () => {
    const post = await loadPost(postId);
    if (!post || !isAutoPublish(post)) return;

    if (post.status === "SCHEDULED" && (force || (post.scheduledAt && post.scheduledAt <= new Date()))) {
      // A retry keeps the previous container; if Instagram already has it, resume instead of posting twice.
      if (post.containerId) {
        const prev = await getContainerStatus(post.containerId, decrypt(post.account.accessToken!)).catch(() => null);
        if (prev && prev.status_code !== "ERROR" && prev.status_code !== "EXPIRED") {
          await prisma.post.update({
            where: { id: post.id },
            data: { status: "PUBLISHING", publishStartedAt: new Date(), publishError: null },
          });
          return;
        }
      }
      await startPublish(post);
      return;
    }
    if (post.status === "PUBLISHING" && post.containerId) {
      await checkAndPublish(post);
    }
  });
}

async function syncMetrics() {
  const now = Date.now();
  const posts = await prisma.post.findMany({
    where: {
      status: "PUBLISHED",
      externalId: { not: null },
      publishedAt: { gte: new Date(now - METRICS_WINDOW_MS) },
      OR: [{ metricsSyncedAt: null }, { metricsSyncedAt: { lt: new Date(now - METRICS_INTERVAL_MS) } }],
      account: { accessToken: { not: null } },
    },
    include: { account: true },
    orderBy: { metricsSyncedAt: { sort: "asc", nulls: "first" } },
    take: 50,
  });
  let synced = 0;
  for (const post of posts) {
    try {
      const metrics = await getReelInsights(post.externalId!, decrypt(post.account.accessToken!));
      await prisma.post.update({ where: { id: post.id }, data: { ...metrics, metricsSyncedAt: new Date() } });
      synced++;
    } catch {
      // Insights can lag right after publishing; retry on the next run.
      await prisma.post.update({ where: { id: post.id }, data: { metricsSyncedAt: new Date() } });
    }
  }
  return synced;
}

export async function syncAccount(accountId: string) {
  const account = await prisma.socialAccount.findUnique({ where: { id: accountId } });
  if (!account?.accessToken) return;
  let token = decrypt(account.accessToken);
  const data: { accessToken?: string; tokenExpiresAt?: Date; followers?: number } = {};

  if (account.tokenExpiresAt && account.tokenExpiresAt.getTime() - Date.now() < TOKEN_REFRESH_BEFORE_MS) {
    const refreshed = await refreshToken(token);
    token = refreshed.accessToken;
    data.accessToken = encrypt(token);
    data.tokenExpiresAt = refreshed.expiresAt;
  }
  const profile = await getProfile(token);
  if (profile.followers_count != null) data.followers = profile.followers_count;
  await prisma.socialAccount.update({ where: { id: accountId }, data });
}

export async function runTick() {
  const now = new Date();
  const due = await prisma.post.findMany({
    where: {
      OR: [{ status: "SCHEDULED", scheduledAt: { lte: now } }, { status: "PUBLISHING" }],
      mediaUrl: { not: null },
      account: { platform: "INSTAGRAM", igUserId: { not: null }, accessToken: { not: null } },
    },
    select: { id: true },
    orderBy: { scheduledAt: "asc" },
    take: 20,
  });
  for (const { id } of due) await advancePost(id);

  const accounts = await prisma.socialAccount.findMany({
    where: { accessToken: { not: null } },
    select: { id: true },
  });
  const accountErrors: string[] = [];
  for (const { id } of accounts) {
    await syncAccount(id).catch((e) => accountErrors.push(`${id}: ${errorMessage(e)}`));
  }

  const synced = await syncMetrics();
  return { advanced: due.length, accounts: accounts.length, accountErrors, metricsSynced: synced };
}
