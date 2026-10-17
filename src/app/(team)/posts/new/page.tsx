import Link from "next/link";
import { createPost } from "@/app/actions/posts";
import { PostForm } from "@/components/post-form";
import { cardClass } from "@/components/ui";
import { getAccountOptions } from "@/lib/queries";

export default async function NewPostPage({ searchParams }: PageProps<"/posts/new">) {
  const { accountId } = await searchParams;
  const accounts = await getAccountOptions();
  const preselected = typeof accountId === "string" && accounts.some((a) => a.id === accountId) ? accountId : "";

  return (
    <div className="space-y-6">
      <div>
        <Link href="/posts" className="text-sm text-slate-500 hover:underline">
          &larr; 投稿一覧
        </Link>
        <h1 className="mt-1 text-2xl font-bold">投稿を作成</h1>
      </div>
      {accounts.length === 0 ? (
        <p className="rounded-xl bg-white p-8 text-center text-sm text-slate-500 ring-1 ring-slate-200">
          先に
          <Link href="/clients" className="mx-1 font-semibold text-brand hover:underline">
            クライアント
          </Link>
          にSNSアカウントを登録してください。
        </p>
      ) : (
        <div className={cardClass}>
          <PostForm
            action={createPost}
            accounts={accounts}
            submitLabel="保存する"
            initial={{
              accountId: preselected,
              content: "",
              mediaUrl: "",
              status: "DRAFT",
              scheduledAt: "",
              publishedAt: "",
              impressions: 0,
              likes: 0,
              comments: 0,
              shares: 0,
              saves: 0,
            }}
          />
        </div>
      )}
    </div>
  );
}
