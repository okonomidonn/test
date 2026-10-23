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
  audience?: string | null;
  features?: string[];
  pitch?: string | null;
};

export function StoreForm({ action, defaults = {}, submitLabel }: { action: Action; defaults?: StoreDefaults; submitLabel: string }) {
  const [state, formAction, isPending] = useActionState(action, {});
  return (
    <form action={formAction} className="space-y-4">
      <Message state={state} />
      <input type="hidden" name="kind" value="RESTAURANT" />
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
        <p className="mt-1 text-sm text-slate-600">AIの回答からお店を見つけるのに使います。読み仮名や略称、英語表記など。</p>
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

export function WebServiceForm({ action, defaults = {}, submitLabel }: { action: Action; defaults?: StoreDefaults; submitLabel: string }) {
  const [state, formAction, isPending] = useActionState(action, {});
  return (
    <form action={formAction} className="space-y-4">
      <Message state={state} />
      <input type="hidden" name="kind" value="WEB" />
      <div className="grid gap-4 sm:grid-cols-2">
        <div>
          <label className={labelClass}>サービス名 *</label>
          <input name="name" required defaultValue={defaults.name} placeholder="例：zaito" className={inputClass} />
        </div>
        <div>
          <label className={labelClass}>カテゴリ *</label>
          <input name="genre" required defaultValue={defaults.genre} placeholder="例：求人サイト" className={inputClass} />
        </div>
      </div>
      <div>
        <label className={labelClass}>誰向けのサービスか</label>
        <input name="audience" defaultValue={defaults.audience ?? ""} placeholder="例：大学生や主婦" className={inputClass} />
      </div>
      <div>
        <label className={labelClass}>強み・特徴の言葉（1行に1つ・5つまで）</label>
        <textarea name="features" rows={3} defaultValue={defaults.features?.join("\n")} placeholder={"例：完全在宅のバイト\n未経験OK\n週1日から"} className={inputClass} />
        <p className="mt-1 text-sm text-slate-600">利用者がAIに聞くときに使いそうな言葉で。ここから「未経験OKの求人サイトを教えて」のような細かい質問を作ります。</p>
      </div>
      <div>
        <label className={labelClass}>一文の立ち位置</label>
        <input name="pitch" defaultValue={defaults.pitch ?? ""} placeholder="例：完全在宅の仕事だけを集めた求人サイト" className={inputClass} />
        <p className="mt-1 text-sm text-slate-600">「誰の・何を・他と何が違うか」。サイト診断で、トップページに書かれているかを確認します。</p>
      </div>
      <div>
        <label className={labelClass}>公式サイトのURL</label>
        <input name="websiteUrl" type="url" defaultValue={defaults.websiteUrl ?? ""} placeholder="https://..." className={inputClass} />
        <p className="mt-1 text-sm text-slate-600">AIが名前を出さずにURLだけで紹介した場合も、紹介されたと数えます。</p>
      </div>
      <div>
        <label className={labelClass}>サービス名の別の書き方（1行に1つ）</label>
        <textarea name="aliases" rows={2} defaultValue={defaults.aliases?.join("\n")} placeholder={"例：ザイト\nZAITO"} className={inputClass} />
        <p className="mt-1 text-sm text-slate-600">AIの回答からサービスを見つけるのに使います。読み方・英語表記・運営会社名など。</p>
      </div>
      <div>
        <label className={labelClass}>比べたい競合サービス（1行に1つ・10件まで）</label>
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
      <textarea name="questions" rows={Math.max(questions.length + 1, 6)} defaultValue={questions.join("\n")} className={`${inputClass} font-mono text-sm`} />
      <p className="text-sm text-slate-600">1行に1つ、{max}個まで。店名・サービス名は入れないでください（利用者が名前を知らずに聞く質問にするため）。</p>
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
      {disabledReason && <span className="text-sm text-slate-600">{disabledReason}</span>}
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
      {!hasUrl && <span className="text-sm text-slate-600">「設定」で公式サイトのURLを登録すると診断できます</span>}
      {state.error && <span className="text-sm text-red-600">{state.error}</span>}
    </form>
  );
}
