"use client";

import { useActionState } from "react";
import { createLead, type LeadState } from "@/app/actions/leads";
import { inputClass, labelClass, primaryButtonClass } from "@/components/ui";

type Defaults = { company?: string; websiteUrl?: string; name?: string; email?: string };

export function LeadForm({ kind, defaults = {} }: { kind: "CHECK" | "TRIAL"; defaults?: Defaults }) {
  const [state, formAction, isPending] = useActionState<LeadState, FormData>(createLead, {});
  if (state.done) {
    return (
      <div className="rounded-xl border-2 border-brand bg-brand-soft p-6">
        <p className="text-lg font-bold text-slate-900">受け付けました</p>
        <p className="mt-2 text-slate-800">
          {kind === "TRIAL"
            ? "2営業日以内に、進め方とお見積もりをメールでお送りします。"
            : "3営業日以内に、計測とサイト診断の結果をメールでお送りします。"}
        </p>
      </div>
    );
  }
  return (
    <form action={formAction} className="space-y-4">
      {state.error && <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700">{state.error}</p>}
      <input type="hidden" name="kind" value={kind} />
      {/* 機械的な送信よけ（人には見えない） */}
      <input type="text" name="fax" tabIndex={-1} autoComplete="off" className="hidden" aria-hidden />
      <div className="grid gap-4 sm:grid-cols-2">
        <div>
          <label htmlFor="lead-company" className={labelClass}>
            会社名・サービス名 *
          </label>
          <input id="lead-company" name="company" required defaultValue={defaults.company} className={inputClass} />
        </div>
        <div>
          <label htmlFor="lead-name" className={labelClass}>
            お名前 *
          </label>
          <input id="lead-name" name="name" required autoComplete="name" defaultValue={defaults.name} className={inputClass} />
        </div>
      </div>
      <div>
        <label htmlFor="lead-email" className={labelClass}>
          メールアドレス *
        </label>
        <input id="lead-email" type="email" name="email" required autoComplete="email" defaultValue={defaults.email} className={inputClass} />
      </div>
      <div>
        <label htmlFor="lead-url" className={labelClass}>
          サービスのサイト *
        </label>
        <input id="lead-url" type="url" name="websiteUrl" required placeholder="https://" defaultValue={defaults.websiteUrl} className={inputClass} />
      </div>
      <div>
        <label htmlFor="lead-message" className={labelClass}>
          {kind === "TRIAL" ? "気になっていること（任意）" : "見込み客がAIに聞きそうな質問（任意）"}
        </label>
        <textarea
          id="lead-message"
          name="message"
          rows={3}
          placeholder={kind === "TRIAL" ? "例：ChatGPTに聞くと、競合ばかり紹介される" : "例：地方在住の大学生が利用できる、完全在宅の求人サイトは？"}
          className={inputClass}
        />
      </div>
      <button type="submit" disabled={isPending} className={`${primaryButtonClass} w-full py-3 text-base`}>
        {isPending ? "送信中..." : kind === "TRIAL" ? "トライアルを申し込む" : "無料チェックを申し込む"}
      </button>
      <p className="text-sm text-slate-600">申し込みの時点では料金はかかりません。{kind === "TRIAL" ? "お見積もりを確認してから正式にご依頼ください。" : ""}</p>
    </form>
  );
}
