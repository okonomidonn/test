import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { createClient } from "@/app/actions/clients";
import { ClientForm } from "@/components/client-form";
import { PlatformBadge } from "@/components/badges";
import { cardClass } from "@/components/ui";
import { formatNumber } from "@/lib/constants";

export default async function ClientsPage() {
  const clients = await prisma.client.findMany({
    orderBy: { createdAt: "desc" },
    include: {
      accounts: {
        select: { id: true, platform: true, followers: true, _count: { select: { posts: true } } },
      },
    },
  });

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-bold">クライアント</h1>
        <p className="mt-1 text-sm text-slate-500">運用を担当しているクライアントとSNSアカウントを管理します。</p>
      </div>

      {clients.length === 0 ? (
        <p className="rounded-xl bg-white p-8 text-center text-sm text-slate-500 ring-1 ring-slate-200">
          まだクライアントが登録されていません。下のフォームから追加してください。
        </p>
      ) : (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {clients.map((c) => {
            const followers = c.accounts.reduce((s, a) => s + a.followers, 0);
            const posts = c.accounts.reduce((s, a) => s + a._count.posts, 0);
            return (
              <Link key={c.id} href={`/clients/${c.id}`} className={`${cardClass} block hover:ring-2 hover:ring-indigo-100`}>
                <p className="font-bold text-slate-900">{c.name}</p>
                {c.industry && <p className="text-xs text-slate-500">{c.industry}</p>}
                <div className="mt-3 flex flex-wrap gap-1">
                  {c.accounts.length === 0 ? (
                    <span className="text-xs text-slate-400">アカウント未登録</span>
                  ) : (
                    c.accounts.map((a) => <PlatformBadge key={a.id} platform={a.platform} />)
                  )}
                </div>
                <p className="mt-3 text-xs text-slate-500">
                  総フォロワー {formatNumber(followers)} ・ 投稿 {formatNumber(posts)}件
                </p>
              </Link>
            );
          })}
        </div>
      )}

      <section className={cardClass}>
        <h2 className="mb-4 font-bold">クライアントを追加</h2>
        <ClientForm action={createClient} submitLabel="追加する" />
      </section>
    </div>
  );
}
