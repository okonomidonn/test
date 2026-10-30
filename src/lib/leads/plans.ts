export const LEAD_PLANS = {
  check: { label: "無料チェック", kind: "CHECK" },
  trial: { label: "トライアル", kind: "TRIAL" },
  basic: { label: "基本プラン", kind: "TRIAL" },
  production: { label: "制作プラン", kind: "TRIAL" },
} as const;

export type LeadPlan = keyof typeof LEAD_PLANS;

export function resolveLeadPlan(value: unknown, legacyKind?: unknown): LeadPlan {
  if (typeof value === "string" && Object.hasOwn(LEAD_PLANS, value)) return value as LeadPlan;
  return legacyKind === "TRIAL" ? "trial" : "check";
}

// 既存の受付種別を維持し、月額の希望プランは任意本文と別にサーバー側で記録する。
// 月額相談でも任意本文は空欄でよく、入力エラー時にこの見出しを本文へ戻さない。
export function leadMessage(plan: LeadPlan, message?: string): string | undefined {
  if (plan !== "basic" && plan !== "production") return message;
  return `【${LEAD_PLANS[plan].label}のご相談】${message ? `\n${message}` : ""}`;
}

export function leadLabel(kind: "CHECK" | "TRIAL", message?: string | null): string {
  if (kind === "CHECK") return LEAD_PLANS.check.label;
  for (const plan of ["basic", "production"] as const) {
    if (message?.startsWith(`【${LEAD_PLANS[plan].label}のご相談】`)) return LEAD_PLANS[plan].label;
  }
  return LEAD_PLANS.trial.label;
}
