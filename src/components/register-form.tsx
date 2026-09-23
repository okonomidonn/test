"use client";

import { useActionState } from "react";
import { registerUser, type AuthFormState } from "@/app/actions/auth";
import { inputClass, labelClass, primaryButtonClass } from "@/components/ui";

export function RegisterForm() {
  const [state, formAction, isPending] = useActionState<AuthFormState, FormData>(registerUser, {});

  return (
    <form action={formAction} className="space-y-4">
      {state.error && (
        <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700">{state.error}</p>
      )}
      <div>
        <label className={labelClass}>お名前</label>
        <input type="text" name="name" required maxLength={50} className={inputClass} />
      </div>
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
          minLength={8}
          autoComplete="new-password"
          className={inputClass}
        />
        <p className="mt-1 text-xs text-slate-400">8文字以上で入力してください</p>
      </div>
      <button type="submit" disabled={isPending} className={`${primaryButtonClass} w-full`}>
        {isPending ? "登録中..." : "登録する"}
      </button>
    </form>
  );
}
