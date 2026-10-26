import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { requireAccount } from "@/lib/session";
import { cardClass, primaryButtonClass } from "@/components/ui";

export default async function StoresPage() {
  const user = await requireAccount();
  const stores = await prisma.store.findMany({
    where: user.role === "OWNER" ? { ownerId: user.id } : {},
    orderBy: { createdAt: "desc" },
    include: {
      runs: {
        where: { status: "DONE" },
        orderBy: { createdAt: "desc" },
        take: 1,
        include: { answers: { where: { status: "DONE" }, select: { targetRank: true } } },
      },
    },
  });

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold">サービス一覧</h1>
          <p className="mt-1 text-sm text-slate-600">サービスごとに、AIでの紹介状況と改善点を確認できます。</p>
        </div>
        <Link href="/ai/app/new" className={primaryButtonClass}>
          追加する
        </Link>
      </div>

      {stores.length === 0 ? (
        <div className={`${cardClass} text-center`}>
          <p className="text-sm text-slate-700">まだ登録がありません。</p>
          <Link href="/ai/app/new" className={`${primaryButtonClass} mt-4`}>
            サービスを登録する
          </Link>
        </div>
      ) : (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {stores.map((s) => {
            const answers = s.runs[0]?.answers ?? [];
            const hits = answers.filter((a) => a.targetRank !== null).length;
            return (
              <Link key={s.id} href={`/ai/app/stores/${s.id}`} className={`${cardClass} group flex flex-col gap-4 transition hover:border-brand`}>
                <div className="min-w-0">
                  <span className="rounded-md bg-slate-100 px-2 py-0.5 text-sm font-semibold text-slate-700">{s.kind === "WEB" ? "Webサービス" : "お店"}</span>
                  <p className="mt-2 font-display text-lg font-bold text-slate-900 group-hover:text-brand">{s.name}</p>
                  <p className="text-sm text-slate-600">{s.kind === "WEB" ? s.genre : `${s.area}・${s.genre}`}</p>
                </div>
                <div className="mt-auto">
                  <div className="flex items-baseline justify-between gap-2">
                    <span className="text-sm text-slate-600">最新の紹介率</span>
                    <span className="font-num text-2xl font-semibold text-slate-900">{answers.length ? `${Math.round((hits / answers.length) * 100)}%` : "—"}</span>
                  </div>
                  <div className="mt-1.5 h-2 rounded-full bg-slate-100">
                    {answers.length > 0 && <div className="h-2 rounded-full bg-brand" style={{ width: `${Math.round((hits / answers.length) * 100)}%` }} />}
                  </div>
                  <p className="mt-1.5 text-sm text-slate-600">{answers.length ? `${answers.length}回答中 ${hits}回 紹介` : "まだ計測していません"}</p>
                </div>
              </Link>
            );
          })}
        </div>
      )}
    </div>
  );
}
