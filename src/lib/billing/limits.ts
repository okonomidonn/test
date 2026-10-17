import { prisma } from "@/lib/prisma";
import { OWNER_LIMITS, ownerCooldownMs } from "@/lib/ai-check/questions";
import { effectivePlan, type PlanDef } from "./plans";

export type RunQuota = { plan: PlanDef; allowed: boolean; reason?: string; usedThisMonth: number };

// 店舗オーナーがこの店舗で今計測できるか。プランの店舗数を超えた店舗（ダウングレード後など）は、古い順に枠内の店舗だけ計測できる。
export async function ownerRunQuota(userId: string, storeId: string): Promise<RunQuota> {
  const user = await prisma.user.findUniqueOrThrow({ where: { id: userId } });
  const plan = effectivePlan(user);
  const monthAgo = new Date(Date.now() - 30 * 24 * 3600_000);
  const [stores, lastRun, usedThisMonth] = await Promise.all([
    prisma.store.findMany({ where: { ownerId: userId }, orderBy: { createdAt: "asc" }, select: { id: true } }),
    prisma.measurementRun.findFirst({ where: { storeId }, orderBy: { createdAt: "desc" }, select: { createdAt: true } }),
    prisma.measurementRun.count({ where: { storeId, createdAt: { gt: monthAgo } } }),
  ]);

  const base = { plan, usedThisMonth };
  if (!stores.slice(0, plan.stores).some((s) => s.id === storeId)) {
    return { ...base, allowed: false, reason: `${plan.name}プランで計測できるのは${plan.stores}店舗までです` };
  }
  if (usedThisMonth >= plan.runsPerMonth) {
    return { ...base, allowed: false, reason: `${plan.name}プランの計測は30日間で${plan.runsPerMonth}回までです` };
  }
  if (ownerCooldownMs(lastRun?.createdAt) > 0) {
    return { ...base, allowed: false, reason: `次の計測は前回から${OWNER_LIMITS.runIntervalHours}時間後にできます` };
  }
  return { ...base, allowed: true };
}

export async function ownerPlan(userId: string): Promise<PlanDef> {
  return effectivePlan(await prisma.user.findUniqueOrThrow({ where: { id: userId } }));
}
