import { CATEGORIES, EMPLOYMENT_TYPE_LABELS, WORK_STYLE_LABELS } from "@/lib/constants";

export function SearchForm({
  defaultValues,
  compact = false,
}: {
  defaultValues?: {
    q?: string;
    category?: string;
    workStyle?: string;
    employmentType?: string;
  };
  compact?: boolean;
}) {
  return (
    <form
      action="/jobs"
      className={
        compact
          ? "flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-lg ring-1 ring-black/5 sm:flex-row sm:items-center"
          : "grid grid-cols-1 gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100 sm:grid-cols-4"
      }
    >
      <input
        type="text"
        name="q"
        defaultValue={defaultValues?.q}
        placeholder="キーワード(職種・スキルなど)"
        className="w-full flex-1 rounded-full border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100 sm:col-span-1"
      />

      <select
        name="category"
        defaultValue={defaultValues?.category ?? ""}
        className="rounded-full border border-slate-200 px-4 py-2.5 text-sm text-slate-600 outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
      >
        <option value="">職種すべて</option>
        {CATEGORIES.map((c) => (
          <option key={c} value={c}>
            {c}
          </option>
        ))}
      </select>

      <select
        name="workStyle"
        defaultValue={defaultValues?.workStyle ?? ""}
        className="rounded-full border border-slate-200 px-4 py-2.5 text-sm text-slate-600 outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
      >
        <option value="">働き方すべて</option>
        {Object.entries(WORK_STYLE_LABELS).map(([value, label]) => (
          <option key={value} value={value}>
            {label}
          </option>
        ))}
      </select>

      <select
        name="employmentType"
        defaultValue={defaultValues?.employmentType ?? ""}
        className="rounded-full border border-slate-200 px-4 py-2.5 text-sm text-slate-600 outline-none focus:border-brand focus:ring-2 focus:ring-emerald-100"
      >
        <option value="">雇用形態すべて</option>
        {Object.entries(EMPLOYMENT_TYPE_LABELS).map(([value, label]) => (
          <option key={value} value={value}>
            {label}
          </option>
        ))}
      </select>

      <button
        type="submit"
        className="rounded-full bg-brand px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-dark sm:col-span-1"
      >
        検索する
      </button>
    </form>
  );
}
