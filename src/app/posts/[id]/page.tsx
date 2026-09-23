import Link from "next/link";
import { notFound } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { deletePost, updatePost } from "@/app/actions/posts";
import { PostForm } from "@/components/post-form";
import { ConfirmButton } from "@/components/confirm-button";
import { cardClass, dangerButtonClass } from "@/components/ui";
import { getAccountOptions } from "@/lib/queries";
import { formatDateTime, toJstInputValue } from "@/lib/constants";

export default async function EditPostPage({ params }: PageProps<"/posts/[id]">) {
  const { id } = await params;
  const [post, accounts] = await Promise.all([
    prisma.post.findUnique({ where: { id }, include: { author: { select: { name: true } } } }),
    getAccountOptions(),
  ]);
  if (!post) notFound();

  return (
    <div className="space-y-6">
      <div>
        <Link href="/posts" className="text-sm text-slate-500 hover:underline">
          &larr; 投稿一覧
        </Link>
        <h1 className="mt-1 text-2xl font-bold">投稿を編集</h1>
        <p className="mt-1 text-xs text-slate-500">
          作成者 {post.author?.name ?? "—"} ・ 作成 {formatDateTime(post.createdAt)} ・ 更新{" "}
          {formatDateTime(post.updatedAt)}
        </p>
      </div>
      <div className={cardClass}>
        <PostForm
          action={updatePost.bind(null, post.id)}
          accounts={accounts}
          submitLabel="更新する"
          initial={{
            accountId: post.accountId,
            content: post.content,
            mediaUrl: post.mediaUrl ?? "",
            status: post.status,
            scheduledAt: toJstInputValue(post.scheduledAt),
            publishedAt: toJstInputValue(post.publishedAt),
            impressions: post.impressions,
            likes: post.likes,
            comments: post.comments,
            shares: post.shares,
            saves: post.saves,
          }}
        />
      </div>
      <form action={deletePost.bind(null, post.id)}>
        <ConfirmButton message="この投稿を削除します。よろしいですか？" className={dangerButtonClass}>
          投稿を削除
        </ConfirmButton>
      </form>
    </div>
  );
}
