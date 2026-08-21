"use client";

import { useActionState } from "react";
import { applyToJob, type ApplyState } from "@/app/actions/applications";

export function ApplyForm({ jobId }: { jobId: string }) {
  const applyAction = applyToJob.bind(null, jobId);
  const [state, formAction, isPending] = useActionState<ApplyState, FormData>(applyAction, {});

  if (state.success) {
    return (
      <div className="rounded-2xl bg-emerald-50 p-5 text-sm text-brand-dark">
        応募が完了しました!企業からの連絡をお待ちください。
      </div>
    );
  }

  return (
    <form action={formAction} className="space-y-3">
      {state.error && (
        <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700">{state.error}</p>
      )}
      <textarea
        name="coverLetter"
        required
        rows={5}
        maxLength={3000}
        placeholder="自己PRや意気込みなど、応募メッセージを入力してください"
        className="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
      />
      <button
        type="submit"
        disabled={isPending}
        className="w-full rounded-full bg-brand px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-dark disabled:opacity-60"
      >
        {isPending ? "送信中..." : "この求人に応募する"}
      </button>
    </form>
  );
}
