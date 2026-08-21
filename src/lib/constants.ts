export const CATEGORIES = [
  "エンジニア・IT",
  "デザイン",
  "ライティング・編集",
  "事務・データ入力",
  "カスタマーサポート",
  "翻訳・通訳",
  "動画・写真編集",
  "マーケティング・広報",
  "営業・企画",
  "その他",
] as const;

export const EMPLOYMENT_TYPE_LABELS: Record<string, string> = {
  FULL_TIME: "正社員",
  PART_TIME: "アルバイト・パート",
  CONTRACT: "業務委託",
  FREELANCE: "フリーランス",
  INTERNSHIP: "インターン",
};

export const WORK_STYLE_LABELS: Record<string, string> = {
  FULL_REMOTE: "フルリモート",
  HYBRID: "ハイブリッド",
  ON_SITE: "出社あり",
};

export const APPLICATION_STATUS_LABELS: Record<string, string> = {
  SUBMITTED: "応募受付",
  REVIEWING: "選考中",
  OFFERED: "採用",
  REJECTED: "不採用",
};

export const APPLICATION_STATUS_STYLES: Record<string, string> = {
  SUBMITTED: "bg-slate-100 text-slate-700",
  REVIEWING: "bg-amber-100 text-amber-800",
  OFFERED: "bg-emerald-100 text-emerald-800",
  REJECTED: "bg-rose-100 text-rose-700",
};

export function formatSalary(min: number | null, max: number | null): string {
  if (min == null && max == null) return "応相談";
  const fmt = (n: number) => `${n.toLocaleString("ja-JP")}円`;
  if (min != null && max != null) return `${fmt(min)}〜${fmt(max)}`;
  if (min != null) return `${fmt(min)}〜`;
  if (max != null) return `〜${fmt(max)}`;
  return "応相談";
}
