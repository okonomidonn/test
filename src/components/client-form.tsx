"use client";

import { useActionState } from "react";
import type { FormState } from "@/app/actions/clients";
import { inputClass, labelClass, primaryButtonClass } from "@/components/ui";

type ClientValues = {
  name: string;
  industry: string | null;
  contactName: string | null;
  contactEmail: string | null;
  notes: string | null;
};

export function ClientForm({
  action,
  initial,
  submitLabel,
}: {
  action: (prev: FormState, formData: FormData) => Promise<FormState>;
  initial?: ClientValues;
  submitLabel: string;
}) {
  const [state, formAction, isPending] = useActionState<FormState, FormData>(action, {});

  return (
    <form action={formAction} className="space-y-4">
      {state.error && (
        <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700">{state.error}</p>
      )}
      {state.ok && (
        <p className="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">保存しました</p>
      )}
      <div>
        <label className={labelClass}>クライアント名 *</label>
        <input name="name" required maxLength={100} defaultValue={initial?.name} className={inputClass} />
      </div>
      <div className="grid gap-4 sm:grid-cols-3">
        <div>
          <label className={labelClass}>業種</label>
          <input name="industry" maxLength={50} defaultValue={initial?.industry ?? ""} className={inputClass} />
        </div>
        <div>
          <label className={labelClass}>担当者名</label>
          <input
            name="contactName"
            maxLength={50}
            defaultValue={initial?.contactName ?? ""}
            className={inputClass}
          />
        </div>
        <div>
          <label className={labelClass}>担当者メール</label>
          <input
            type="email"
            name="contactEmail"
            defaultValue={initial?.contactEmail ?? ""}
            className={inputClass}
          />
        </div>
      </div>
      <div>
        <label className={labelClass}>メモ（運用方針・NG事項など）</label>
        <textarea
          name="notes"
          rows={3}
          maxLength={2000}
          defaultValue={initial?.notes ?? ""}
          className={inputClass}
        />
      </div>
      <button type="submit" disabled={isPending} className={primaryButtonClass}>
        {isPending ? "保存中..." : submitLabel}
      </button>
    </form>
  );
}
