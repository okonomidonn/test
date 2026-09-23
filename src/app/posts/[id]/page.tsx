import Link from "next/link";
import { notFound } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { deletePost, publishNow, updatePost } from "@/app/actions/posts";
import { isAutoPublish } from "@/lib/publisher";
import { StatusBadge } from "@/components/badges";
import { PendingButton } from "@/components/pending-button";
import { PostForm } from "@/components/post-form";
import { ConfirmButton } from "@/components/confirm-button";
import { cardClass, dangerButtonClass } from "@/components/ui";
import { getAccountOptions } from "@/lib/queries";
import { formatDateTime, toJstInputValue } from "@/lib/constants";

export default async function EditPostPage({ params }: PageProps<"/posts/[id]">) {
  const { id } = await params;
  const [post, accounts] = await Promise.all([
    prisma.post.findUnique({ where: { id }, include: { author: { select: { name: true } }, account: true } }),
    getAccountOptions(),
  ]);
  if (!post) notFound();
  const autoPublish = isAutoPublish(post);
  const formStatus = post.status === "PUBLISHING" || post.status === "FAILED" ? "SCHEDULED" : post.status;

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
      {(autoPublish || post.externalId) && (
        <div className={`${cardClass} space-y-2 text-sm`}>
          <div className="flex flex-wrap items-center gap-3">
            <span className="font-bold">{post.imported ? "Instagramから取り込み" : "Instagram自動投稿"}</span>
            <StatusBadge status={post.status} />
            {(post.status === "SCHEDULED" || post.status === "FAILED") && autoPublish && (
              <form action={publishNow.bind(null, post.id)}>
                <PendingButton pendingLabel="投稿中...">{post.status === "FAILED" ? "再試行" : "今すぐ投稿"}</PendingButton>
              </form>
            )}
            {post.permalink && (
              <a href={post.permalink} target="_blank" rel="noreferrer" className="text-brand hover:underline">
                Instagramで見る
              </a>
            )}
          </div>
          {post.status === "SCHEDULED" && <p className="text-slate-600">{formatDateTime(post.scheduledAt)} に自動で投稿されます。</p>}
          {post.status === "PUBLISHING" && (
            <p className="text-slate-600">Instagram側で動画を処理中です。数分で公開されます（ページを再読み込みすると状態が更新されます）。</p>
          )}
          {post.status === "FAILED" && post.publishError && <p className="text-rose-600">{post.publishError}</p>}
          {post.externalId && (
            <p className="text-xs text-slate-500">
              実績の自動取得: {post.metricsSyncedAt ? `${formatDateTime(post.metricsSyncedAt)} 更新` : "未取得（公開後しばらくしてから取得されます）"}
            </p>
          )}
        </div>
      )}

      {post.status === "PUBLISHING" ? null : (
      <div className={cardClass}>
        <PostForm
          action={updatePost.bind(null, post.id)}
          accounts={accounts}
          submitLabel="更新する"
          initial={{
            accountId: post.accountId,
            content: post.content,
            mediaUrl: post.mediaUrl ?? "",
            status: formStatus,
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
      )}
      <form action={deletePost.bind(null, post.id)}>
        <ConfirmButton message="この投稿を削除します。よろしいですか？" className={dangerButtonClass}>
          投稿を削除
        </ConfirmButton>
      </form>
    </div>
  );
}
