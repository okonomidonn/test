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
          <h1 className="text-2xl font-bold">店舗一覧</h1>
          <p className="mt-1 text-sm text-slate-500">お店ごとに、AIでの紹介状況と改善点を確認できます。</p>
        </div>
        <Link href="/ai/app/new" className={primaryButtonClass}>
          店舗を追加
        </Link>
      </div>

      {stores.length === 0 ? (
        <div className={`${cardClass} text-center`}>
          <p className="text-sm text-slate-600">まだお店が登録されていません。</p>
          <Link href="/ai/app/new" className={`${primaryButtonClass} mt-4`}>
            お店を登録する
          </Link>
        </div>
      ) : (
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {stores.map((s) => {
            const answers = s.runs[0]?.answers ?? [];
            const hits = answers.filter((a) => a.targetRank !== null).length;
            return (
              <Link key={s.id} href={`/ai/app/stores/${s.id}`} className={`${cardClass} block hover:ring-2 hover:ring-indigo-100`}>
                <p className="font-bold text-slate-900">{s.name}</p>
                <p className="text-xs text-slate-500">
                  {s.area}・{s.genre}
                </p>
                <p className="mt-4 text-xs text-slate-500">最新の推薦率</p>
                <p className="text-2xl font-bold text-slate-900">
                  {answers.length ? `${Math.round((hits / answers.length) * 100)}%` : "未計測"}
                </p>
                {answers.length > 0 && <p className="text-xs text-slate-500">{answers.length}回答中 {hits}回</p>}
              </Link>
            );
          })}
        </div>
      )}
    </div>
  );
}
