"use client";

import { useActionState } from "react";
import { registerUser, type AuthFormState } from "@/app/actions/auth";
import { inputClass, labelClass, primaryButtonClass } from "@/components/ui";

export function RegisterForm({ invite, callbackUrl }: { invite?: string; callbackUrl?: "/ai/app" }) {
  const [state, formAction, isPending] = useActionState<AuthFormState, FormData>(registerUser, {});

  return (
    <form action={formAction} className="space-y-4">
      {invite && <input type="hidden" name="invite" value={invite} />}
      {callbackUrl && <input type="hidden" name="callbackUrl" value={callbackUrl} />}
      {state.error && (
        <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700">{state.error}</p>
      )}
      <div>
        <label htmlFor="register-name" className={labelClass}>お名前</label>
        <input id="register-name" type="text" name="name" required maxLength={50} autoComplete="name" className={inputClass} />
      </div>
      <div>
        <label htmlFor="register-email" className={labelClass}>メールアドレス</label>
        <input id="register-email" type="email" name="email" required autoComplete="email" className={inputClass} />
      </div>
      <div>
        <label htmlFor="register-password" className={labelClass}>パスワード</label>
        <input
          id="register-password"
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
