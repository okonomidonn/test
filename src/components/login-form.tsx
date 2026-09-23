"use client";

import { useActionState } from "react";
import { loginUser, type AuthFormState } from "@/app/actions/auth";
import { inputClass, labelClass, primaryButtonClass } from "@/components/ui";

export function LoginForm({ callbackUrl }: { callbackUrl?: string }) {
  const [state, formAction, isPending] = useActionState<AuthFormState, FormData>(loginUser, {});

  return (
    <form action={formAction} className="space-y-4">
      {callbackUrl && <input type="hidden" name="callbackUrl" value={callbackUrl} />}
      {state.error && (
        <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700">{state.error}</p>
      )}
      <div>
        <label className={labelClass}>メールアドレス</label>
        <input type="email" name="email" required autoComplete="email" className={inputClass} />
      </div>
      <div>
        <label className={labelClass}>パスワード</label>
        <input
          type="password"
          name="password"
          required
          autoComplete="current-password"
          className={inputClass}
        />
      </div>
      <button type="submit" disabled={isPending} className={`${primaryButtonClass} w-full`}>
        {isPending ? "ログイン中..." : "ログイン"}
      </button>
    </form>
  );
}
