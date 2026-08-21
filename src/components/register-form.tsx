"use client";

import { useActionState, useState } from "react";
import { registerUser, type RegisterState } from "@/app/actions/auth";

export function RegisterForm({ defaultRole }: { defaultRole?: "SEEKER" | "COMPANY" }) {
  const [role, setRole] = useState<"SEEKER" | "COMPANY">(defaultRole ?? "SEEKER");
  const [state, formAction, isPending] = useActionState<RegisterState, FormData>(registerUser, {});

  return (
    <form action={formAction} className="space-y-4">
      {state.error && (
        <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700">{state.error}</p>
      )}

      <div>
        <span className="block text-sm font-medium text-slate-700">登録区分</span>
        <div className="mt-2 grid grid-cols-2 gap-2">
          <button
            type="button"
            onClick={() => setRole("SEEKER")}
            className={`rounded-xl border px-4 py-2.5 text-sm font-semibold transition ${
              role === "SEEKER"
                ? "border-brand bg-emerald-50 text-brand-dark"
                : "border-slate-200 text-slate-500 hover:border-slate-300"
            }`}
          >
            求職者として登録
          </button>
          <button
            type="button"
            onClick={() => setRole("COMPANY")}
            className={`rounded-xl border px-4 py-2.5 text-sm font-semibold transition ${
              role === "COMPANY"
                ? "border-brand bg-emerald-50 text-brand-dark"
                : "border-slate-200 text-slate-500 hover:border-slate-300"
            }`}
          >
            企業として登録
          </button>
        </div>
        <input type="hidden" name="role" value={role} />
      </div>

      <div>
        <label className="block text-sm font-medium text-slate-700">
          {role === "COMPANY" ? "ご担当者名" : "お名前"}
        </label>
        <input
          type="text"
          name="name"
          required
          maxLength={50}
          className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
        />
      </div>

      {role === "COMPANY" && (
        <div>
          <label className="block text-sm font-medium text-slate-700">会社名・屋号</label>
          <input
            type="text"
            name="companyName"
            required
            maxLength={100}
            className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
          />
        </div>
      )}

      <div>
        <label className="block text-sm font-medium text-slate-700">メールアドレス</label>
        <input
          type="email"
          name="email"
          required
          autoComplete="email"
          className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
        />
      </div>

      <div>
        <label className="block text-sm font-medium text-slate-700">パスワード</label>
        <input
          type="password"
          name="password"
          required
          minLength={8}
          autoComplete="new-password"
          className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
        />
        <p className="mt-1 text-xs text-slate-400">8文字以上で入力してください</p>
      </div>

      <button
        type="submit"
        disabled={isPending}
        className="w-full rounded-full bg-brand px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-dark disabled:opacity-60"
      >
        {isPending ? "登録中..." : "登録する"}
      </button>
    </form>
  );
}
