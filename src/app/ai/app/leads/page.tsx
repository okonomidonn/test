import { notFound } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { requireAccount } from "@/lib/session";
import { LeadRetryForm, LeadStatusForm } from "@/components/ai/lead-management";
import { leadMailConfig } from "@/lib/leads/mail";

export const metadata = { title: "申し込み一覧 | AIススメル" };

const fmt = (d: Date) =>
  new Intl.DateTimeFormat("ja-JP", { timeZone: "Asia/Tokyo", year: "numeric", month: "numeric", day: "numeric", hour: "2-digit", minute: "2-digit" }).format(d);

// 運営チームだけが見る、無料チェック・トライアルの申し込み一覧
export default async function LeadsPage({ searchParams }: PageProps<"/ai/app/leads">) {
  const user = await requireAccount();
  if (user.role !== "TEAM") notFound();
  const leads = await prisma.lead.findMany({ orderBy: { createdAt: "desc" }, take: 200 });
  const { notification } = await searchParams;
  const mailEnabled = !!leadMailConfig();
  const messages: Record<string, string> = {
    sent: "通知メールを配信サービスへ送信しました。受信箱も確認してください。",
    failed: "通知メールを送信できませんでした。設定を確認して再送してください。",
    skipped: "通知メールの送信設定が完了していません。申し込みは保存されています。",
    busy: "送信済み、または別の処理で送信中です。",
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-black text-slate-900">申し込み一覧</h1>
        <p className="mt-1 text-slate-700">無料チェックとトライアルの申し込みです（新しい順）。</p>
        <p className="mt-2 text-sm text-slate-600">無料チェックは3営業日以内に結果を、トライアルは2営業日以内に進め方と見積もりを連絡します。通知メールは運営向けです。申し込み者への結果送付は手動で行ってください。</p>
      </div>
      {!mailEnabled && <p role="status" className="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">通知メールは未設定、またはこの環境では停止中です。申し込みは保存されるため、この一覧を毎営業日確認してください。</p>}
      {typeof notification === "string" && messages[notification] && <p role="status" className="rounded-lg border border-slate-200 bg-slate-50 p-4">{messages[notification]}</p>}
      <p className="text-sm text-slate-700">表示中の{leads.length}件：未対応 {leads.filter((l) => l.status === "NEW").length}件 ／ 連絡済み {leads.filter((l) => l.status === "CONTACTED").length}件 ／ 完了 {leads.filter((l) => l.status === "COMPLETED").length}件</p>
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
                <th className="px-4 py-3 font-semibold">対応・通知</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {leads.map((l) => (
                <tr key={l.id} id={`lead-${l.id}`} className="scroll-mt-20 align-top">
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
                  <td className="min-w-48 space-y-3 px-4 py-3">
                    <LeadStatusForm id={l.id} company={l.company} status={l.status} />
                    <p className="text-slate-600">{l.notifiedAt ? `通知送信済み：${fmt(l.notifiedAt)}` : l.notificationLockedAt ? "通知処理中・確認待ち" : l.notificationError ? "通知失敗" : "未通知"}</p>
                    {l.notificationError && <p className="max-w-xs text-rose-700">{l.notificationError}</p>}
                    {!l.notifiedAt && <LeadRetryForm id={l.id} enabled={mailEnabled} />}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
