import { notFound } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { requireAccount } from "@/lib/session";

export const metadata = { title: "申し込み一覧 | ススメル" };

const fmt = (d: Date) =>
  new Intl.DateTimeFormat("ja-JP", { timeZone: "Asia/Tokyo", year: "numeric", month: "numeric", day: "numeric", hour: "2-digit", minute: "2-digit" }).format(d);

// 運営チームだけが見る、無料チェック・トライアルの申し込み一覧
export default async function LeadsPage() {
  const user = await requireAccount();
  if (user.role !== "TEAM") notFound();
  const leads = await prisma.lead.findMany({ orderBy: { createdAt: "desc" }, take: 200 });

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-black text-slate-900">申し込み一覧</h1>
        <p className="mt-1 text-slate-700">無料チェックとトライアルの申し込みです（新しい順）。</p>
      </div>
      {leads.length === 0 ? (
        <p className="rounded-xl border border-slate-200 bg-white p-6 text-slate-700">まだ申し込みはありません。紹介ページと料金ページの申し込みボタンから届きます。</p>
      ) : (
        <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white">
          <table className="w-full min-w-[720px] text-sm">
            <thead>
              <tr className="border-b border-slate-200 text-left text-slate-600">
                <th className="px-4 py-3 font-semibold">日時</th>
                <th className="px-4 py-3 font-semibold">種類</th>
                <th className="px-4 py-3 font-semibold">会社・サービス</th>
                <th className="px-4 py-3 font-semibold">お名前・メール</th>
                <th className="px-4 py-3 font-semibold">内容</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {leads.map((l) => (
                <tr key={l.id} className="align-top">
                  <td className="whitespace-nowrap px-4 py-3 font-num text-slate-700">{fmt(l.createdAt)}</td>
                  <td className="px-4 py-3">
                    <span className={`whitespace-nowrap rounded-md px-2 py-0.5 font-semibold ${l.kind === "TRIAL" ? "bg-brand text-white" : "bg-slate-100 text-slate-700"}`}>
                      {l.kind === "TRIAL" ? "トライアル" : "無料チェック"}
                    </span>
                  </td>
                  <td className="px-4 py-3">
                    <p className="font-semibold text-slate-900">{l.company}</p>
                    <a href={l.websiteUrl} target="_blank" rel="noopener noreferrer nofollow" className="break-all text-brand hover:underline">
                      {l.websiteUrl}
                    </a>
                  </td>
                  <td className="px-4 py-3">
                    <p className="text-slate-900">{l.name}</p>
                    <p className="break-all text-slate-700">{l.email}</p>
                  </td>
                  <td className="max-w-xs whitespace-pre-wrap px-4 py-3 text-slate-700">{l.message ?? "—"}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
