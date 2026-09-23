import Link from "next/link";
import { notFound } from "next/navigation";
import { prisma } from "@/lib/prisma";
import {
  createAccount,
  deleteAccount,
  deleteClient,
  disconnectInstagram,
  updateClient,
  updateFollowers,
} from "@/app/actions/clients";
import { ClientForm } from "@/components/client-form";
import { AccountForm } from "@/components/account-form";
import { PlatformBadge } from "@/components/badges";
import { ConfirmButton } from "@/components/confirm-button";
import { cardClass, dangerButtonClass, secondaryButtonClass } from "@/components/ui";
import { formatNumber } from "@/lib/constants";

const IG_MESSAGES: Record<string, { text: string; ok: boolean }> = {
  connected: { text: "Instagramと連携しました", ok: true },
  denied: { text: "Instagram連携がキャンセルされました", ok: false },
  invalid_state: { text: "連携の有効期限が切れました。もう一度お試しください", ok: false },
  error: { text: "Instagram連携に失敗しました。プロアカウントか確認してもう一度お試しください", ok: false },
  not_configured: { text: "Instagram連携の設定（INSTAGRAM_APP_ID / INSTAGRAM_APP_SECRET）がまだありません", ok: false },
};

export default async function ClientDetailPage({ params, searchParams }: PageProps<"/clients/[id]">) {
  const { id } = await params;
  const { ig } = await searchParams;
  const igMessage = typeof ig === "string" ? IG_MESSAGES[ig] : undefined;
  const client = await prisma.client.findUnique({
    where: { id },
    include: {
      accounts: {
        orderBy: { createdAt: "asc" },
        include: { _count: { select: { posts: true } } },
      },
    },
  });
  if (!client) notFound();

  return (
    <div className="space-y-8">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <Link href="/clients" className="text-sm text-slate-500 hover:underline">
            &larr; クライアント一覧
          </Link>
          <h1 className="mt-1 text-2xl font-bold">{client.name}</h1>
        </div>
        <div className="flex gap-2">
          <Link href={`/dashboard?client=${client.id}`} className={secondaryButtonClass}>
            分析を見る
          </Link>
          <Link href={`/posts?client=${client.id}`} className={secondaryButtonClass}>
            投稿一覧
          </Link>
        </div>
      </div>

      {igMessage && (
        <p
          className={`rounded-lg px-4 py-2.5 text-sm ${igMessage.ok ? "bg-emerald-50 text-emerald-700" : "bg-rose-50 text-rose-700"}`}
        >
          {igMessage.text}
        </p>
      )}

      <section className={cardClass}>
        <h2 className="mb-4 font-bold">SNSアカウント</h2>
        {client.accounts.length === 0 ? (
          <p className="mb-4 text-sm text-slate-500">まだアカウントが登録されていません。</p>
        ) : (
          <ul className="mb-6 divide-y divide-slate-100">
            {client.accounts.map((a) => (
              <li key={a.id} className="flex flex-wrap items-center gap-3 py-3">
                <PlatformBadge platform={a.platform} />
                <span className="font-medium">@{a.handle}</span>
                <span className="text-xs text-slate-500">投稿 {formatNumber(a._count.posts)}件</span>
                {a.platform === "INSTAGRAM" &&
                  (a.accessToken ? (
                    <span className="flex items-center gap-2 text-xs">
                      <span className="rounded bg-emerald-50 px-2 py-0.5 font-semibold text-emerald-700">
                        ✓ 連携済み
                      </span>
                      <form action={disconnectInstagram.bind(null, a.id)}>
                        <ConfirmButton
                          message="Instagram連携を解除します。予約中のリールは自動投稿されなくなります。よろしいですか？"
                          className="text-slate-400 hover:text-rose-600 hover:underline"
                        >
                          連携解除
                        </ConfirmButton>
                      </form>
                    </span>
                  ) : (
                    <a
                      href={`/api/instagram/connect?accountId=${a.id}`}
                      className="rounded-md bg-gradient-to-r from-pink-500 to-orange-400 px-2.5 py-1 text-xs font-semibold text-white hover:opacity-90"
                    >
                      Instagramと連携
                    </a>
                  ))}
                <div className="ml-auto flex items-center gap-2">
                  <form action={updateFollowers.bind(null, a.id)} className="flex items-center gap-1">
                    <input
                      type="number"
                      name="followers"
                      min={0}
                      defaultValue={a.followers}
                      aria-label="フォロワー数"
                      className="w-28 rounded-md border border-slate-200 px-2 py-1 text-right text-sm"
                    />
                    <span className="text-xs text-slate-500">人</span>
                    <button type="submit" className="rounded-md px-2 py-1 text-xs text-brand hover:bg-indigo-50">
                      更新
                    </button>
                  </form>
                  <Link
                    href={`/posts/new?accountId=${a.id}`}
                    className="rounded-md px-2 py-1 text-xs font-semibold text-brand hover:bg-indigo-50"
                  >
                    投稿作成
                  </Link>
                  <form action={deleteAccount.bind(null, a.id)}>
                    <ConfirmButton
                      message={`@${a.handle} と関連する投稿をすべて削除します。よろしいですか？`}
                      className="rounded-md px-2 py-1 text-xs text-rose-600 hover:bg-rose-50"
                    >
                      削除
                    </ConfirmButton>
                  </form>
                </div>
              </li>
            ))}
          </ul>
        )}
        <AccountForm action={createAccount.bind(null, client.id)} />
      </section>

      <section className={cardClass}>
        <h2 className="mb-4 font-bold">クライアント情報</h2>
        <ClientForm action={updateClient.bind(null, client.id)} initial={client} submitLabel="保存する" />
      </section>

      <form action={deleteClient.bind(null, client.id)}>
        <ConfirmButton
          message={`「${client.name}」と全アカウント・投稿を削除します。よろしいですか？`}
          className={dangerButtonClass}
        >
          クライアントを削除
        </ConfirmButton>
      </form>
    </div>
  );
}
