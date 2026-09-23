"use server";

import { z } from "zod";
import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { requireUser } from "@/lib/session";
import { PLATFORM_CHAR_LIMITS, PLATFORM_LABELS, POST_STATUSES, fromJstInputValue } from "@/lib/constants";

export type PostFormState = { error?: string };

const metric = z.preprocess(
  (v) => (v === "" || v == null ? 0 : v),
  z.coerce.number().int().nonnegative("指標は0以上の整数で入力してください"),
);

const postSchema = z.object({
  accountId: z.string().min(1, "投稿先アカウントを選択してください"),
  content: z.string().trim().min(1, "本文を入力してください").max(5000),
  mediaUrl: z.preprocess(
    (v) => (typeof v === "string" && v.trim() === "" ? undefined : v),
    z.url({ protocol: /^https?$/, message: "メディアURLはhttp(s)のURLを入力してください" }).optional(),
  ),
  status: z.enum(POST_STATUSES),
  scheduledAt: z.string().optional(),
  publishedAt: z.string().optional(),
  impressions: metric,
  likes: metric,
  comments: metric,
  shares: metric,
  saves: metric,
});

async function parsePost(formData: FormData) {
  const parsed = postSchema.safeParse(Object.fromEntries(formData));
  if (!parsed.success) {
    return { error: parsed.error.issues[0]?.message ?? "入力内容を確認してください" } as const;
  }
  const d = parsed.data;

  const account = await prisma.socialAccount.findUnique({ where: { id: d.accountId } });
  if (!account) return { error: "投稿先アカウントが見つかりません" } as const;

  const limit = PLATFORM_CHAR_LIMITS[account.platform];
  if (limit && [...d.content].length > limit) {
    return { error: `${PLATFORM_LABELS[account.platform]}の本文は${limit}文字以内にしてください` } as const;
  }

  const scheduledAt = d.scheduledAt ? fromJstInputValue(d.scheduledAt) : null;
  let publishedAt = d.publishedAt ? fromJstInputValue(d.publishedAt) : null;

  if (d.status === "SCHEDULED" && !scheduledAt) {
    return { error: "予約投稿には予約日時を指定してください" } as const;
  }
  if (d.status === "PUBLISHED" && !publishedAt) {
    publishedAt = scheduledAt ?? new Date();
  }

  const isPublished = d.status === "PUBLISHED";
  return {
    data: {
      accountId: d.accountId,
      content: d.content,
      mediaUrl: d.mediaUrl ?? null,
      status: d.status,
      scheduledAt,
      publishedAt: isPublished ? publishedAt : null,
      impressions: isPublished ? d.impressions : 0,
      likes: isPublished ? d.likes : 0,
      comments: isPublished ? d.comments : 0,
      shares: isPublished ? d.shares : 0,
      saves: isPublished ? d.saves : 0,
    },
  } as const;
}

function revalidatePosts(postId?: string) {
  revalidatePath("/posts");
  revalidatePath("/dashboard");
  if (postId) revalidatePath(`/posts/${postId}`);
}

export async function createPost(_prev: PostFormState, formData: FormData): Promise<PostFormState> {
  const user = await requireUser();
  const result = await parsePost(formData);
  if ("error" in result) return { error: result.error };

  await prisma.post.create({ data: { ...result.data, authorId: user.id } });
  revalidatePosts();
  redirect("/posts");
}

export async function updatePost(
  postId: string,
  _prev: PostFormState,
  formData: FormData,
): Promise<PostFormState> {
  await requireUser();
  const result = await parsePost(formData);
  if ("error" in result) return { error: result.error };

  const { count } = await prisma.post.updateMany({ where: { id: postId }, data: result.data });
  if (count === 0) return { error: "投稿が見つかりません" };

  revalidatePosts(postId);
  redirect("/posts");
}

export async function markPublished(postId: string) {
  await requireUser();
  const post = await prisma.post.findUnique({ where: { id: postId } });
  if (!post || post.status === "PUBLISHED") return;

  await prisma.post.update({
    where: { id: postId },
    data: { status: "PUBLISHED", publishedAt: new Date() },
  });
  revalidatePosts(postId);
}

export async function deletePost(postId: string) {
  await requireUser();
  await prisma.post.deleteMany({ where: { id: postId } });
  revalidatePosts();
  redirect("/posts");
}
