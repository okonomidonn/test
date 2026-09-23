import { headers } from "next/headers";
import { prisma } from "@/lib/prisma";
import { createInvite, revokeInvite } from "@/app/actions/invites";
import { CopyField } from "@/components/copy-field";
import { cardClass, primaryButtonClass } from "@/components/ui";
import { formatDateTime } from "@/lib/constants";

export default async function TeamPage() {
  const now = new Date();
  const [members, invites, h] = await Promise.all([
    prisma.user.findMany({ orderBy: { createdAt: "asc" }, select: { id: true, name: true, email: true, createdAt: true } }),
    prisma.invite.findMany({
      where: { usedAt: null, expiresAt: { gt: now } },
      orderBy: { createdAt: "desc" },
      include: { createdBy: { select: { name: true } } },
    }),
    headers(),
  ]);
  const origin = `${h.get("x-forwarded-proto") ?? "http"}://${h.get("host")}`;

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-bold">チーム</h1>
        <p className="mt-1 text-sm text-slate-500">
          新しいメンバーは招待リンクからのみ登録できます。リンクは1回限り有効で、7日で期限切れになります。
        </p>
      </div>

      <section className={cardClass}>
        <div className="flex flex-wrap items-center justify-between gap-3">
          <h2 className="font-bold">有効な招待リンク</h2>
          <form action={createInvite}>
            <button type="submit" className={primaryButtonClass}>
              招待リンクを発行
            </button>
          </form>
        </div>
        {invites.length === 0 ? (
          <p className="mt-3 text-sm text-slate-500">有効な招待リンクはありません。</p>
        ) : (
          <ul className="mt-4 space-y-3">
            {invites.map((inv) => (
              <li key={inv.id} className="rounded-lg border border-slate-200 p-3">
                <CopyField value={`${origin}/register?invite=${inv.token}`} />
                <div className="mt-2 flex items-center justify-between text-xs text-slate-500">
                  <span>
                    発行 {inv.createdBy.name} ・ 期限 {formatDateTime(inv.expiresAt)}
                  </span>
                  <form action={revokeInvite.bind(null, inv.id)}>
                    <button type="submit" className="text-rose-600 hover:underline">
                      取り消す
                    </button>
                  </form>
                </div>
              </li>
            ))}
          </ul>
        )}
      </section>

      <section className={cardClass}>
        <h2 className="font-bold">メンバー（{members.length}人）</h2>
        <ul className="mt-3 divide-y divide-slate-100 text-sm">
          {members.map((m) => (
            <li key={m.id} className="flex flex-wrap items-center justify-between gap-2 py-2">
              <span className="font-medium">{m.name}</span>
              <span className="text-slate-500">{m.email}</span>
              <span className="text-xs text-slate-400">登録 {formatDateTime(m.createdAt)}</span>
            </li>
          ))}
        </ul>
      </section>
    </div>
  );
}
