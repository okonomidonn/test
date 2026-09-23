"use client";

import { useActionState, useState } from "react";
import type { PostFormState } from "@/app/actions/posts";
import {
  PLATFORM_CHAR_LIMITS,
  PLATFORM_LABELS,
  POST_STATUSES,
  POST_STATUS_LABELS,
  type PlatformKey,
  type PostStatusKey,
} from "@/lib/constants";
import { inputClass, labelClass, primaryButtonClass } from "@/components/ui";

export type AccountOption = {
  id: string;
  platform: PlatformKey;
  handle: string;
  clientName: string;
};

export type PostValues = {
  accountId: string;
  content: string;
  mediaUrl: string;
  status: PostStatusKey;
  scheduledAt: string;
  publishedAt: string;
  impressions: number;
  likes: number;
  comments: number;
  shares: number;
  saves: number;
};

const METRIC_FIELDS = [
  ["impressions", "インプレッション"],
  ["likes", "いいね"],
  ["comments", "コメント"],
  ["shares", "シェア/リポスト"],
  ["saves", "保存"],
] as const;

export function PostForm({
  action,
  accounts,
  initial,
  submitLabel,
}: {
  action: (prev: PostFormState, formData: FormData) => Promise<PostFormState>;
  accounts: AccountOption[];
  initial: PostValues;
  submitLabel: string;
}) {
  const [state, formAction, isPending] = useActionState<PostFormState, FormData>(action, {});
  const [accountId, setAccountId] = useState(initial.accountId);
  const [content, setContent] = useState(initial.content);
  const [status, setStatus] = useState<PostStatusKey>(initial.status);

  const platform = accounts.find((a) => a.id === accountId)?.platform;
  const limit = platform ? PLATFORM_CHAR_LIMITS[platform] : undefined;
  const length = [...content].length;

  return (
    <form action={formAction} className="space-y-5">
      {state.error && (
        <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700">{state.error}</p>
      )}

      <div>
        <label className={labelClass}>投稿先アカウント *</label>
        <select
          name="accountId"
          required
          value={accountId}
          onChange={(e) => setAccountId(e.target.value)}
          className={inputClass}
        >
          <option value="">選択してください</option>
          {accounts.map((a) => (
            <option key={a.id} value={a.id}>
              {a.clientName} / {PLATFORM_LABELS[a.platform]} @{a.handle}
            </option>
          ))}
        </select>
      </div>

      <div>
        <label className={labelClass}>本文 *</label>
        <textarea
          name="content"
          required
          rows={6}
          maxLength={5000}
          value={content}
          onChange={(e) => setContent(e.target.value)}
          className={inputClass}
        />
        <p className={`mt-1 text-right text-xs ${limit && length > limit ? "text-rose-600" : "text-slate-400"}`}>
          {length}
          {limit ? ` / ${limit}` : ""} 文字
        </p>
      </div>

      <div>
        <label className={labelClass}>画像・動画URL</label>
        <input type="url" name="mediaUrl" defaultValue={initial.mediaUrl} placeholder="https://" className={inputClass} />
      </div>

      <div className="grid gap-4 sm:grid-cols-3">
        <div>
          <label className={labelClass}>ステータス</label>
          <select
            name="status"
            value={status}
            onChange={(e) => setStatus(e.target.value as PostStatusKey)}
            className={inputClass}
          >
            {POST_STATUSES.map((s) => (
              <option key={s} value={s}>
                {POST_STATUS_LABELS[s]}
              </option>
            ))}
          </select>
        </div>
        <div>
          <label className={labelClass}>予約日時（JST）{status === "SCHEDULED" && " *"}</label>
          <input
            type="datetime-local"
            name="scheduledAt"
            required={status === "SCHEDULED"}
            defaultValue={initial.scheduledAt}
            className={inputClass}
          />
        </div>
        {status === "PUBLISHED" && (
          <div>
            <label className={labelClass}>公開日時（JST）</label>
            <input type="datetime-local" name="publishedAt" defaultValue={initial.publishedAt} className={inputClass} />
          </div>
        )}
      </div>

      {status === "PUBLISHED" && (
        <fieldset className="rounded-lg border border-slate-200 p-4">
          <legend className="px-1 text-sm font-semibold text-slate-700">実績（各SNSのインサイトから入力）</legend>
          <div className="grid gap-3 sm:grid-cols-5">
            {METRIC_FIELDS.map(([name, label]) => (
              <div key={name}>
                <label className="block text-xs text-slate-600">{label}</label>
                <input type="number" name={name} min={0} defaultValue={initial[name]} className={inputClass} />
              </div>
            ))}
          </div>
        </fieldset>
      )}

      <button type="submit" disabled={isPending} className={primaryButtonClass}>
        {isPending ? "保存中..." : submitLabel}
      </button>
    </form>
  );
}
