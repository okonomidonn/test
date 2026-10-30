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
          <div key={f.k} className="space-y-1.5">
            <label htmlFor={`hero-${f.k}`} className="flex items-center gap-2 text-sm font-bold text-slate-800">
              {f.label}
              
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
                className={`h-11 w-full rounded-lg border-0 bg-white px-3 text-base text-slate-900 ring-1 ring-inset placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand ${err ? "ring-rose-500" : "ring-slate-300"}`}
              />
              {err && <p className="mt-1 text-xs font-medium text-rose-700">{err}</p>}
            </div>
          </div>
        );
      })}
      <p className="pt-1 text-center text-sm text-slate-700">
        <Link href="/ai/privacy" className="font-semibold text-brand underline">プライバシーポリシー</Link>に同意のうえ送信してください
      </p>
      <button type="submit" disabled={isPending} className="flex min-h-13 w-full items-center justify-center rounded-lg bg-brand py-3.5 text-lg font-bold text-white shadow-sm transition hover:bg-brand-dark disabled:opacity-60">
        {isPending ? "送信しています…" : "無料で診断を依頼する →"}
      </button>
    </form>
  );
}
