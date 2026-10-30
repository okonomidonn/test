"use client";

import { useActionState, type ReactNode } from "react";
import { createLead, type LeadField, type LeadState } from "@/app/actions/leads";
import { btn } from "@/components/ai/ds";
import type { LeadPlan } from "@/lib/leads/plans";

type Defaults = { company?: string; websiteUrl?: string; name?: string; email?: string };

const inputCls = (invalid: boolean) =>
  `mt-1.5 block h-11 w-full rounded-lg border bg-white px-3 text-base text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 ${
    invalid ? "border-rose-500 focus:ring-rose-200" : "border-slate-300 focus:border-brand focus:ring-brand/20"
  }`;

function Field({ id, label, hint, error, children }: { id: string; label: string; hint?: string; error?: string; children: ReactNode }) {
  return (
    <div>
      <label htmlFor={id} className="block text-sm font-semibold text-slate-900">
        {label}
      </label>
      {children}
      {error ? (
        <p id={`${id}-error`} className="mt-1.5 text-sm font-medium text-rose-700">
          ✕ {error}
        </p>
      ) : (
        hint && <p className="mt-1.5 text-sm text-slate-600">{hint}</p>
      )}
    </div>
  );
}

export function LeadForm({ kind, defaults = {}, plan, submitLabel }: { kind: "CHECK" | "TRIAL"; defaults?: Defaults; plan?: LeadPlan; submitLabel?: string }) {
  const [state, formAction, isPending] = useActionState<LeadState, FormData>(createLead, {});
  const err = (k: LeadField) => state.fieldErrors?.[k];
  const val = (k: LeadField) => state.values?.[k] ?? (defaults as Record<string, string | undefined>)[k];
  const aria = (k: LeadField) => (err(k) ? { "aria-invalid": true, "aria-describedby": `lead-${k}-error` } : {});

  if (state.done) {
    return (
      <div role="status" className="space-y-3">
        <p className="text-sm font-semibold text-brand">受け付けました</p>
        <p className="text-xl font-bold text-slate-900">{kind === "TRIAL" ? "2営業日以内に、進め方とお見積もりをお送りします。" : "3営業日以内に、診断結果をお送りします。"}</p>
        <p className="text-slate-700">入力したメールアドレスに届きます。届かない場合は、迷惑メールのフォルダもご確認ください。</p>
        <p className="text-sm text-slate-600">受付内容は保存されました。自動返信メールは送っていません。担当者からのご連絡をお待ちください。</p>
      </div>
    );
  }

  return (
    <form action={formAction} noValidate className="space-y-7">
      {state.error && (
        <p role="alert" className="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">
          {state.error}。赤く表示された項目を直してください。
        </p>
      )}
      <input type="hidden" name="kind" value={kind} />
      {plan && <input type="hidden" name="plan" value={plan} />}
      {/* 機械的な送信よけ（人には見えない） */}
      <input type="text" name="fax" tabIndex={-1} autoComplete="off" className="hidden" aria-hidden />

      <fieldset className="space-y-4">
        <legend className="text-sm font-semibold text-slate-500">1. 診断するサービス</legend>
        <Field id="lead-company" label="会社名・サービス名" error={err("company")}>
          <input id="lead-company" name="company" required defaultValue={val("company")} autoComplete="organization" className={inputCls(!!err("company"))} {...aria("company")} />
        </Field>
        <Field id="lead-websiteUrl" label="サービスのサイト" hint="トップページのURL" error={err("websiteUrl")}>
          <input
            id="lead-websiteUrl"
            type="url"
            name="websiteUrl"
            required
            inputMode="url"
            placeholder="https://"
            defaultValue={val("websiteUrl")}
            className={inputCls(!!err("websiteUrl"))}
            {...aria("websiteUrl")}
          />
        </Field>
      </fieldset>

      <fieldset className="space-y-4">
        <legend className="text-sm font-semibold text-slate-500">2. ご連絡先</legend>
        <Field id="lead-name" label="お名前" error={err("name")}>
          <input id="lead-name" name="name" required autoComplete="name" defaultValue={val("name")} className={inputCls(!!err("name"))} {...aria("name")} />
        </Field>
        <Field id="lead-email" label="メールアドレス" hint="結果はこのアドレスにお送りします" error={err("email")}>
          <input
            id="lead-email"
            type="email"
            name="email"
            required
            autoComplete="email"
            inputMode="email"
            defaultValue={val("email")}
            className={inputCls(!!err("email"))}
            {...aria("email")}
          />
        </Field>
      </fieldset>

      <fieldset className="space-y-4">
        <legend className="text-sm font-semibold text-slate-500">3. 任意</legend>
        <Field
          id="lead-message"
          label={kind === "TRIAL" ? "気になっていること" : "見込み客がAIに聞きそうな質問"}
          hint={kind === "TRIAL" ? "例：ChatGPTに聞くと、競合ばかり紹介される" : "例：地方在住の大学生が利用できる、完全在宅の求人サイトは？"}
          error={err("message")}
        >
          <textarea id="lead-message" name="message" rows={3} defaultValue={val("message")} className={`${inputCls(!!err("message"))} h-auto py-2.5`} />
        </Field>
      </fieldset>

      <div className="space-y-2">
        <button type="submit" disabled={isPending} className={`${btn("primary", "lg")} w-full`}>
          {isPending ? "送信しています…" : submitLabel ?? (kind === "TRIAL" ? "トライアルの見積もりを依頼する" : "無料で診断する")}
        </button>
        <p className="text-sm text-slate-600">送信の時点では料金はかかりません。</p>
      </div>
    </form>
  );
}
