"use client";

import Link from "next/link";
import { useActionState } from "react";
import { createLead, type LeadField, type LeadState } from "@/app/actions/leads";

// 資料請求フォーム。送信するとその場でダウンロードできる
const FIELDS: { k: LeadField; label: string; type?: string; placeholder: string; auto?: string; optional?: boolean }[] = [
  { k: "company", label: "会社名・サイト名", placeholder: "例）株式会社ススメル", auto: "organization" },
  { k: "name", label: "お名前", placeholder: "例）鈴木 太郎", auto: "name" },
  { k: "email", label: "メールアドレス", type: "email", placeholder: "例）taro@example.com", auto: "email" },
  { k: "websiteUrl", label: "求人サイトのURL", type: "url", placeholder: "例）https://example.com", optional: true },
];

export function BookForm({ pdf }: { pdf: string }) {
  const [state, formAction, isPending] = useActionState<LeadState, FormData>(createLead, {});

  if (state.done) {
    return (
      <div role="status" className="text-center">
        <p className="text-xl font-bold text-slate-900">お申し込みありがとうございます</p>
        {state.mailed ? (
          <>
            <p className="mt-3 text-slate-700">ご入力のメールアドレスに、資料のダウンロードURLをお送りしました。</p>
            <p className="mt-2 text-sm text-slate-600">数分たっても届かない場合は、迷惑メールのフォルダをご確認いただくか、info@aisusumeru.com までご連絡ください。</p>
          </>
        ) : (
          <>
            <p className="mt-2 text-slate-700">下のボタンから資料をダウンロードできます。</p>
            <a href={pdf} download className="mt-5 inline-flex min-h-13 w-full items-center justify-center rounded-lg bg-brand py-3.5 text-lg font-bold text-white hover:bg-brand-dark">
              資料をダウンロードする（PDF）
            </a>
          </>
        )}
        <p className="mt-4 text-sm text-slate-600">
          自社の状況をすぐ知りたい方は、
          <Link href="/ai/apply" className="font-semibold text-brand underline">
            無料チェック
          </Link>
          もご利用ください。
        </p>
      </div>
    );
  }

  return (
    <form action={formAction} noValidate className="space-y-3">
      <input type="hidden" name="plan" value="book" />
      <input type="text" name="fax" tabIndex={-1} autoComplete="off" className="hidden" aria-hidden />
      {state.error && <p role="alert" className="rounded-md bg-rose-50 px-3 py-2 text-sm font-medium text-rose-800">{state.error}</p>}
      {FIELDS.map((f) => {
        const err = state.fieldErrors?.[f.k];
        return (
          <div key={f.k} className="space-y-1.5">
            <label htmlFor={`book-${f.k}`} className="flex items-center gap-2 text-sm font-bold text-slate-800">
              {f.label}
              {f.optional && <span className="text-xs font-medium text-slate-500">（任意）</span>}
            </label>
            <input
              id={`book-${f.k}`}
              name={f.k}
              type={f.type ?? "text"}
              required={!f.optional}
              autoComplete={f.auto}
              placeholder={f.placeholder}
              defaultValue={state.values?.[f.k]}
              aria-invalid={!!err}
              className={`h-11 w-full rounded-lg border-0 bg-white px-3 text-base text-slate-900 ring-1 ring-inset placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand ${err ? "ring-rose-500" : "ring-slate-300"}`}
            />
            {err && <p className="text-xs font-medium text-rose-700">{err}</p>}
          </div>
        );
      })}
      <p className="pt-1 text-center text-sm text-slate-700">
        <Link href="/ai/privacy" className="font-semibold text-brand underline">
          プライバシーポリシー
        </Link>
        に同意のうえ送信してください
      </p>
      <button type="submit" disabled={isPending} className="flex min-h-13 w-full items-center justify-center rounded-lg bg-brand py-3.5 text-lg font-bold text-white shadow-sm transition hover:bg-brand-dark disabled:opacity-60">
        {isPending ? "送信しています…" : "無料でダウンロードする →"}
      </button>
    </form>
  );
}
