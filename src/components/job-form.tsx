"use client";

import { useActionState } from "react";
import { CATEGORIES, EMPLOYMENT_TYPE_LABELS, WORK_STYLE_LABELS } from "@/lib/constants";
import type { JobFormState } from "@/app/actions/jobs";

export type JobFormDefaults = {
  title?: string;
  description?: string;
  category?: string;
  employmentType?: string;
  workStyle?: string;
  location?: string | null;
  salaryMin?: number | null;
  salaryMax?: number | null;
  tags?: string;
};

export function JobForm({
  action,
  defaultValues,
  submitLabel,
}: {
  action: (state: JobFormState, formData: FormData) => Promise<JobFormState>;
  defaultValues?: JobFormDefaults;
  submitLabel: string;
}) {
  const [state, formAction, isPending] = useActionState<JobFormState, FormData>(action, {});

  return (
    <form action={formAction} className="space-y-5">
      {state.error && (
        <p className="rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-700">{state.error}</p>
      )}

      <div>
        <label className="block text-sm font-medium text-slate-700">求人タイトル</label>
        <input
          type="text"
          name="title"
          required
          maxLength={100}
          defaultValue={defaultValues?.title}
          placeholder="例)【フルリモート】Webエンジニア募集"
          className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
        />
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
          <label className="block text-sm font-medium text-slate-700">職種</label>
          <select
            name="category"
            required
            defaultValue={defaultValues?.category ?? ""}
            className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
          >
            <option value="" disabled>
              選択してください
            </option>
            {CATEGORIES.map((c) => (
              <option key={c} value={c}>
                {c}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium text-slate-700">雇用形態</label>
          <select
            name="employmentType"
            required
            defaultValue={defaultValues?.employmentType ?? ""}
            className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
          >
            <option value="" disabled>
              選択してください
            </option>
            {Object.entries(EMPLOYMENT_TYPE_LABELS).map(([value, label]) => (
              <option key={value} value={value}>
                {label}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="block text-sm font-medium text-slate-700">働き方</label>
          <select
            name="workStyle"
            required
            defaultValue={defaultValues?.workStyle ?? ""}
            className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
          >
            <option value="" disabled>
              選択してください
            </option>
            {Object.entries(WORK_STYLE_LABELS).map(([value, label]) => (
              <option key={value} value={value}>
                {label}
              </option>
            ))}
          </select>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div>
          <label className="block text-sm font-medium text-slate-700">給与下限(円)</label>
          <input
            type="number"
            name="salaryMin"
            min={0}
            defaultValue={defaultValues?.salaryMin ?? undefined}
            className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
          />
        </div>
        <div>
          <label className="block text-sm font-medium text-slate-700">給与上限(円)</label>
          <input
            type="number"
            name="salaryMax"
            min={0}
            defaultValue={defaultValues?.salaryMax ?? undefined}
            className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
          />
        </div>
        <div>
          <label className="block text-sm font-medium text-slate-700">勤務地(任意)</label>
          <input
            type="text"
            name="location"
            maxLength={100}
            defaultValue={defaultValues?.location ?? undefined}
            placeholder="例)全国どこでも可"
            className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
          />
        </div>
      </div>

      <div>
        <label className="block text-sm font-medium text-slate-700">仕事内容</label>
        <textarea
          name="description"
          required
          rows={8}
          maxLength={5000}
          defaultValue={defaultValues?.description}
          placeholder="業務内容、必要なスキル、応募条件などを詳しく記載してください"
          className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
        />
      </div>

      <div>
        <label className="block text-sm font-medium text-slate-700">タグ(カンマ区切り・任意)</label>
        <input
          type="text"
          name="tags"
          maxLength={300}
          defaultValue={defaultValues?.tags}
          placeholder="例)React, TypeScript, 未経験可"
          className="mt-1 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
        />
      </div>

      <button
        type="submit"
        disabled={isPending}
        className="w-full rounded-full bg-brand px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-dark disabled:opacity-60 sm:w-auto"
      >
        {isPending ? "保存中..." : submitLabel}
      </button>
    </form>
  );
}
