"use client";

import { useActionState } from "react";
import type { FormState } from "@/app/actions/ai-check";
import { inputClass, labelClass, primaryButtonClass, secondaryButtonClass } from "@/components/ui";

type Action = (prev: FormState, formData: FormData) => Promise<FormState>;

function Message({ state }: { state: FormState }) {
  if (state.error) return <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700">{state.error}</p>;
  if (state.ok) return <p className="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{state.ok}</p>;
  return null;
}

export type StoreDefaults = {
  name?: string;
  area?: string;
  station?: string | null;
  genre?: string;
  websiteUrl?: string | null;
  aliases?: string[];
  competitors?: string[];
};

export function StoreForm({ action, defaults = {}, submitLabel }: { action: Action; defaults?: StoreDefaults; submitLabel: string }) {
  const [state, formAction, isPending] = useActionState(action, {});
  return (
    <form action={formAction} className="space-y-4">
      <Message state={state} />
      <div className="grid gap-4 sm:grid-cols-2">
        <div>
          <label className={labelClass}>店名 *</label>
          <input name="name" required defaultValue={defaults.name} placeholder="例：串と〆シ エソラ" className={inputClass} />
        </div>
        <div>
          <label className={labelClass}>業態 *</label>
          <input name="genre" required defaultValue={defaults.genre} placeholder="例：焼き鳥・居酒屋" className={inputClass} />
        </div>
        <div>
          <label className={labelClass}>エリア *</label>
          <input name="area" required defaultValue={defaults.area} placeholder="例：国立" className={inputClass} />
        </div>
        <div>
          <label className={labelClass}>最寄り駅</label>
          <input name="station" defaultValue={defaults.station ?? ""} placeholder="例：国立駅" className={inputClass} />
        </div>
      </div>
      <div>
        <label className={labelClass}>公式サイトのURL</label>
        <input name="websiteUrl" type="url" defaultValue={defaults.websiteUrl ?? ""} placeholder="https://..." className={inputClass} />
      </div>
      <div>
        <label className={labelClass}>店名の別の書き方（1行に1つ）</label>
        <textarea name="aliases" rows={2} defaultValue={defaults.aliases?.join("\n")} placeholder={"例：エソラ\nくしとメシ エソラ"} className={inputClass} />
        <p className="mt-1 text-xs text-slate-500">AIの回答からお店を見つけるのに使います。読み仮名や略称、英語表記など。</p>
      </div>
      <div>
        <label className={labelClass}>比べたい近くのお店（1行に1つ・10店まで）</label>
        <textarea name="competitors" rows={3} defaultValue={defaults.competitors?.join("\n")} className={inputClass} />
      </div>
      <button type="submit" disabled={isPending} className={primaryButtonClass}>
        {isPending ? "保存中..." : submitLabel}
      </button>
    </form>
  );
}

export function QuestionsForm({ action, questions, max }: { action: Action; questions: string[]; max: number }) {
  const [state, formAction, isPending] = useActionState(action, {});
  return (
    <form action={formAction} className="space-y-3">
      <Message state={state} />
      <textarea name="questions" rows={Math.max(questions.length + 1, 6)} defaultValue={questions.join("\n")} className={`${inputClass} font-mono text-xs`} />
      <p className="text-xs text-slate-500">1行に1つ、{max}個まで。店名は入れないでください（お客さんが店を知らずに聞く質問にするため）。</p>
      <button type="submit" disabled={isPending} className={secondaryButtonClass}>
        {isPending ? "保存中..." : "質問を保存"}
      </button>
    </form>
  );
}

export function StartRunForm({ action, isTeam, disabledReason }: { action: Action; isTeam: boolean; disabledReason?: string }) {
  const [state, formAction, isPending] = useActionState(action, {});
  return (
    <form action={formAction} className="flex flex-wrap items-center gap-3">
      {isTeam && (
        <select name="repeats" defaultValue="1" className="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
          <option value="1">各質問1回</option>
          <option value="2">各質問2回</option>
          <option value="3">各質問3回</option>
        </select>
      )}
      <button type="submit" disabled={isPending || !!disabledReason} className={primaryButtonClass}>
        {isPending ? "準備中..." : "AIに聞いて計測する"}
      </button>
      {disabledReason && <span className="text-xs text-slate-500">{disabledReason}</span>}
      {state.error && <span className="text-sm text-red-600">{state.error}</span>}
    </form>
  );
}

export function AuditForm({ action, hasUrl }: { action: Action; hasUrl: boolean }) {
  const [state, formAction, isPending] = useActionState(action, {});
  return (
    <form action={formAction} className="flex flex-wrap items-center gap-3">
      <button type="submit" disabled={isPending || !hasUrl} className={primaryButtonClass}>
        {isPending ? "診断中..." : "公式サイトを診断する"}
      </button>
      {!hasUrl && <span className="text-xs text-slate-500">「設定」で公式サイトのURLを登録すると診断できます</span>}
      {state.error && <span className="text-sm text-red-600">{state.error}</span>}
    </form>
  );
}
