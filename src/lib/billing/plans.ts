import type { Plan, User } from "@/generated/prisma/client";

// AI検索チェックの料金プラン。上限は1回の計測（質問数×AIの数）にAPI費用がかかるため、それを賄える範囲に設定する。
export type PlanDef = {
  key: Plan;
  name: string;
  priceYen: number;
  stores: number;
  runsPerMonth: number;
  maxQuestions: number;
  summary: string;
};

export const PLANS: Record<Plan, PlanDef> = {
  FREE: { key: "FREE", name: "フリー", priceYen: 0, stores: 1, runsPerMonth: 1, maxQuestions: 10, summary: "まずはお試しで1回計測" },
  STARTER: { key: "STARTER", name: "スターター", priceYen: 4980, stores: 1, runsPerMonth: 4, maxQuestions: 15, summary: "1件（店舗・サービス）を毎週チェック" },
  PRO: { key: "PRO", name: "プロ", priceYen: 14800, stores: 3, runsPerMonth: 4, maxQuestions: 15, summary: "3件まで毎週チェック" },
};

export const PAID_PLANS = ["STARTER", "PRO"] as const;
export type PaidPlan = (typeof PAID_PLANS)[number];

// 支払いが遅れている間（past_due）も、Stripeが再請求を続ける間は使えるようにする
const ACTIVE_STATUSES = new Set(["active", "trialing", "past_due"]);

export function effectivePlan(user: Pick<User, "plan" | "subscriptionStatus">): PlanDef {
  if (user.plan !== "FREE" && user.subscriptionStatus && ACTIVE_STATUSES.has(user.subscriptionStatus)) {
    return PLANS[user.plan];
  }
  return PLANS.FREE;
}

export const formatYen = (n: number) => `${n.toLocaleString("ja-JP")}円`;
