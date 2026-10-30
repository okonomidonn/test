"use client";

import Link from "next/link";
import { useActionState } from "react";
import { createLead, type LeadField, type LeadState } from "@/app/actions/leads";

// ファーストビュー用の短い申し込みフォーム（項目名を左、入力欄を右に並べる）
const FIELDS: { k: LeadField; label: string; type?: string; placeholder: string; auto?: string }[] = [
  { k: "company", label: "会社・サービス名", placeholder: "例）株式会社ススメル", auto: "organization" },
  { k: "websiteUrl", label: "サイトのURL", type: "url", placeholder: "例）https://example.com" },
  { k: "name", label: "お名前", placeholder: "例）鈴木 太郎", auto: "name" },
  { k: "email", label: "メールアドレス", type: "email", placeholder: "例）taro@example.com", auto: "email" },
];

export function HeroLeadForm() {
  const [state, formAction, isPending] = useActionState<LeadState, FormData>(createLead, {});

  if (state.done) {
    return (
      <div role="status" className="py-10 text-center">
        <p className="text-xl font-bold text-brand">受け付けました</p>
        <p className="mt-3 text-slate-800">3営業日以内に、診断結果をメールでお送りします。</p>
      </div>
    );
  }

  return (
    <form action={formAction} noValidate className="space-y-3">
      <input type="hidden" name="kind" value="CHECK" />
      <input type="text" name="fax" tabIndex={-1} autoComplete="off" className="hidden" aria-hidden />
      {state.error && <p role="alert" className="rounded-md bg-rose-50 px-3 py-2 text-sm font-medium text-rose-800">{state.error}</p>}
      {FIELDS.map((f) => {
        const err = state.fieldErrors?.[f.k];
        return (
          <div key={f.k} className="grid items-center gap-1 sm:grid-cols-[9.5rem_1fr] sm:gap-3">
            <label htmlFor={`hero-${f.k}`} className="flex items-center justify-between gap-2 whitespace-nowrap text-sm font-bold text-slate-800 sm:justify-end">
              {f.label}
              <span className="rounded bg-brand px-1.5 py-0.5 text-[11px] font-bold text-white">必須</span>
            </label>
            <div>
              <input
                id={`hero-${f.k}`}
                name={f.k}
                type={f.type ?? "text"}
                required
                autoComplete={f.auto}
                placeholder={f.placeholder}
                defaultValue={state.values?.[f.k]}
                aria-invalid={!!err}
                className={`h-11 w-full rounded-lg border-0 bg-[#eef2fb] px-3 text-base text-slate-900 ring-1 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand ${err ? "ring-rose-500" : "ring-transparent"}`}
              />
              {err && <p className="mt-1 text-xs font-medium text-rose-700">{err}</p>}
            </div>
          </div>
        );
      })}
      <p className="pt-1 text-center text-sm text-slate-700">
        <Link href="/ai/privacy" className="font-semibold text-brand underline">プライバシーポリシー</Link>に同意のうえ送信してください
      </p>
      <button type="submit" disabled={isPending} className="mx-auto flex min-h-14 w-full max-w-xs items-center justify-center gap-2 rounded-full bg-[#ffcc4d] text-lg font-bold text-slate-900 shadow-sm transition hover:bg-[#ffc229] disabled:opacity-60">
        <span aria-hidden className="grid h-5 w-5 place-items-center rounded-full bg-brand text-xs text-white">➜</span>
        {isPending ? "送信しています…" : "無料で診断してみる"}
      </button>
    </form>
  );
}
