import Link from "next/link";
import { prisma } from "@/lib/prisma";
import type { Prisma } from "@/generated/prisma/client";
import { markPublished } from "@/app/actions/posts";
import { PlatformBadge, StatusBadge } from "@/components/badges";
import { primaryButtonClass } from "@/components/ui";
import {
  POST_STATUSES,
  POST_STATUS_LABELS,
  engagementRate,
  formatDateTime,
  formatNumber,
  formatPercent,
  type PostStatusKey,
} from "@/lib/constants";

export default async function PostsPage({ searchParams }: PageProps<"/posts">) {
  const sp = await searchParams;
  const status = POST_STATUSES.includes(sp.status as PostStatusKey) ? (sp.status as PostStatusKey) : undefined;
  const clientId = typeof sp.client === "string" && sp.client ? sp.client : undefined;

  const where: Prisma.PostWhereInput = {
    ...(status && { status }),
    ...(clientId && { account: { clientId } }),
  };
  const orderBy: Prisma.PostOrderByWithRelationInput[] =
    status === "SCHEDULED"
      ? [{ scheduledAt: "asc" }]
      : status === "PUBLISHED"
        ? [{ publishedAt: "desc" }]
        : [{ updatedAt: "desc" }];

  const [posts, clients, counts] = await Promise.all([
    prisma.post.findMany({
      where,
      orderBy,
      take: 200,
      include: { account: { include: { client: { select: { name: true } } } } },
    }),
    prisma.client.findMany({ orderBy: { name: "asc" }, select: { id: true, name: true } }),
    prisma.post.groupBy({
      by: ["status"],
      where: clientId ? { account: { clientId } } : undefined,
      _count: true,
    }),
  ]);

  const countOf = (s?: PostStatusKey) =>
    s ? (counts.find((c) => c.status === s)?._count ?? 0) : counts.reduce((sum, c) => sum + c._count, 0);
  const tabHref = (s?: PostStatusKey) => {
    const q = new URLSearchParams();
    if (s) q.set("status", s);
    if (clientId) q.set("client", clientId);
    const qs = q.toString();
    return qs ? `/posts?${qs}` : "/posts";
  };
  const now = new Date();

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h1 className="text-2xl font-bold">投稿</h1>
        <Link href="/posts/new" className={primaryButtonClass}>
          + 新規投稿
        </Link>
      </div>

      <div className="flex flex-wrap items-center justify-between gap-3">
        <nav className="flex gap-1 rounded-lg bg-slate-100 p-1 text-sm">
          {[undefined, ...POST_STATUSES].map((s) => (
            <Link
              key={s ?? "all"}
              href={tabHref(s)}
              className={`rounded-md px-3 py-1.5 font-medium ${
                s === status ? "bg-white text-slate-900 shadow-sm" : "text-slate-500 hover:text-slate-800"
              }`}
            >
              {s ? POST_STATUS_LABELS[s] : "すべて"} <span className="text-xs text-slate-400">{countOf(s)}</span>
            </Link>
          ))}
        </nav>
        <form className="flex items-center gap-2 text-sm">
          {status && <input type="hidden" name="status" value={status} />}
          <select
            name="client"
            defaultValue={clientId ?? ""}
            className="rounded-lg border border-slate-200 bg-white px-3 py-1.5"
          >
            <option value="">全クライアント</option>
            {clients.map((c) => (
              <option key={c.id} value={c.id}>
                {c.name}
              </option>
            ))}
          </select>
          <button type="submit" className="rounded-lg border border-slate-200 bg-white px-3 py-1.5 hover:bg-slate-50">
            絞り込み
          </button>
        </form>
      </div>

      {posts.length === 0 ? (
        <p className="rounded-xl bg-white p-8 text-center text-sm text-slate-500 ring-1 ring-slate-200">
          該当する投稿はありません。
        </p>
      ) : (
        <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white">
          <table className="w-full min-w-[720px] text-sm">
            <thead className="bg-slate-50 text-left text-xs text-slate-500">
              <tr>
                <th className="px-4 py-2 font-medium">アカウント</th>
                <th className="px-4 py-2 font-medium">本文</th>
                <th className="px-4 py-2 font-medium">ステータス</th>
                <th className="px-4 py-2 font-medium">日時</th>
                <th className="px-4 py-2 text-right font-medium">ER</th>
                <th className="px-4 py-2" />
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {posts.map((p) => {
                const overdue = p.status === "SCHEDULED" && p.scheduledAt != null && p.scheduledAt < now;
                return (
                  <tr key={p.id} className="align-top hover:bg-slate-50">
                    <td className="px-4 py-3">
                      <PlatformBadge platform={p.account.platform} />
                      <p className="mt-1 text-xs text-slate-500">
                        {p.account.client.name} @{p.account.handle}
                      </p>
                    </td>
                    <td className="max-w-sm px-4 py-3">
                      <Link href={`/posts/${p.id}`} className="line-clamp-2 hover:text-brand">
                        {p.content}
                      </Link>
                    </td>
                    <td className="px-4 py-3">
                      <StatusBadge status={p.status} overdue={overdue} />
                    </td>
                    <td className="whitespace-nowrap px-4 py-3 text-xs text-slate-600">
                      {p.status === "PUBLISHED"
                        ? formatDateTime(p.publishedAt)
                        : p.scheduledAt
                          ? formatDateTime(p.scheduledAt)
                          : "—"}
                    </td>
                    <td className="whitespace-nowrap px-4 py-3 text-right text-xs">
                      {p.status === "PUBLISHED" ? (
                        <>
                          {formatPercent(engagementRate(p))}
                          <p className="text-slate-400">imp {formatNumber(p.impressions)}</p>
                        </>
                      ) : (
                        "—"
                      )}
                    </td>
                    <td className="whitespace-nowrap px-4 py-3 text-right">
                      {p.status === "SCHEDULED" && (
                        <form action={markPublished.bind(null, p.id)}>
                          <button type="submit" className="rounded-md px-2 py-1 text-xs font-semibold text-brand hover:bg-indigo-50">
                            公開済みにする
                          </button>
                        </form>
                      )}
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
