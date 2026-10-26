"use client";

import { useActionState } from "react";
import { signupOwner, type FormState } from "@/app/actions/ai-check";
import { inputClass, labelClass, primaryButtonClass } from "@/components/ui";

export function SignupForm() {
  const [state, formAction, isPending] = useActionState<FormState, FormData>(signupOwner, {});
  return (
    <form action={formAction} className="space-y-4">
      {state.error && <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700">{state.error}</p>}
      <div>
        <label className={labelClass}>お名前</label>
        <input name="name" required autoComplete="name" className={inputClass} />
      </div>
      <div>
        <label className={labelClass}>メールアドレス</label>
        <input type="email" name="email" required autoComplete="email" className={inputClass} />
      </div>
      <div>
        <label className={labelClass}>パスワード（8文字以上）</label>
        <input type="password" name="password" required minLength={8} autoComplete="new-password" className={inputClass} />
      </div>
      <button type="submit" disabled={isPending} className={`${primaryButtonClass} w-full`}>
        {isPending ? "登録中..." : "登録する"}
      </button>
    </form>
  );
}
