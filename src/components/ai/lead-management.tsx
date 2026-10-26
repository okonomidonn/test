"use client";

import { useActionState } from "react";
import { retryLeadNotification, updateLeadStatus } from "@/app/actions/leads";

export function LeadStatusForm({ id, company, status }: { id: string; company: string; status: string }) {
  const [message, action, pending] = useActionState(async (_previous: string, data: FormData) => {
    try {
      await updateLeadStatus(id, data);
      return "保存しました";
    } catch {
      return "保存できませんでした。ログイン状態を確認して、再度お試しください。";
    }
  }, "");
  return <form action={action} className="flex flex-wrap gap-2">
    <select name="status" aria-label={`${company}の対応状況`} defaultValue={status} disabled={pending} className="rounded border border-slate-300 bg-white p-1.5 text-base">
      <option value="NEW">未対応</option><option value="CONTACTED">連絡済み</option><option value="COMPLETED">完了</option>
    </select>
    <button type="submit" disabled={pending} className="rounded border border-slate-300 px-2 py-1.5 font-semibold hover:bg-slate-50 disabled:opacity-50">{pending ? "保存中…" : "更新"}</button>
    {message && <p role="status" className="w-full text-xs text-slate-700">{message}</p>}
  </form>;
}

export function LeadRetryForm({ id, enabled }: { id: string; enabled: boolean }) {
  const [, action, pending] = useActionState(async () => { await retryLeadNotification(id); }, undefined);
  return <form action={action}><button type="submit" disabled={!enabled || pending} className="font-semibold text-brand hover:underline disabled:cursor-not-allowed disabled:text-slate-400">{pending ? "送信中…" : "通知メールを送る"}</button></form>;
}
