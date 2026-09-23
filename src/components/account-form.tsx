"use client";

import { useActionState, useEffect, useRef } from "react";
import type { FormState } from "@/app/actions/clients";
import { PLATFORMS, PLATFORM_LABELS } from "@/lib/constants";
import { inputClass, labelClass, primaryButtonClass } from "@/components/ui";

export function AccountForm({
  action,
}: {
  action: (prev: FormState, formData: FormData) => Promise<FormState>;
}) {
  const [state, formAction, isPending] = useActionState<FormState, FormData>(action, {});
  const formRef = useRef<HTMLFormElement>(null);

  useEffect(() => {
    if (state.ok) formRef.current?.reset();
  }, [state]);

  return (
    <form ref={formRef} action={formAction} className="space-y-3">
      {state.error && (
        <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700">{state.error}</p>
      )}
      <div className="grid gap-3 sm:grid-cols-[10rem_1fr_9rem_auto] sm:items-end">
        <div>
          <label className={labelClass}>SNS</label>
          <select name="platform" required className={inputClass}>
            {PLATFORMS.map((p) => (
              <option key={p} value={p}>
                {PLATFORM_LABELS[p]}
              </option>
            ))}
          </select>
        </div>
        <div>
          <label className={labelClass}>アカウント名</label>
          <input name="handle" required maxLength={100} placeholder="@example" className={inputClass} />
        </div>
        <div>
          <label className={labelClass}>フォロワー数</label>
          <input type="number" name="followers" min={0} defaultValue={0} className={inputClass} />
        </div>
        <button type="submit" disabled={isPending} className={primaryButtonClass}>
          {isPending ? "追加中..." : "追加"}
        </button>
      </div>
    </form>
  );
}
