import type Stripe from "stripe";
import { prisma } from "@/lib/prisma";
import { planForPrice } from "./stripe";

// Stripeのサブスクリプションの状態を、そのままユーザーの契約情報に写す。
// Webhookは順不同・重複して届くため、発生時刻が保存済みより古いイベントは無視する。
export async function syncSubscription(sub: Stripe.Subscription, eventCreated: number) {
  const customerId = typeof sub.customer === "string" ? sub.customer : sub.customer.id;
  const userId = sub.metadata?.userId;
  const user = await prisma.user.findFirst({
    where: { OR: [{ stripeCustomerId: customerId }, ...(userId ? [{ id: userId }] : [])] },
  });
  if (!user) return { ignored: "unknown customer" };

  const syncedAt = new Date(eventCreated * 1000);
  if (user.stripeSyncedAt && user.stripeSyncedAt > syncedAt) return { ignored: "stale event" };
  // 別のサブスクリプションが既に紐づいていて、こちらが終了済みなら上書きしない（プラン変更で古い方が解約された場合）
  if (user.stripeSubscriptionId && user.stripeSubscriptionId !== sub.id && ["canceled", "incomplete_expired"].includes(sub.status)) {
    return { ignored: "old subscription" };
  }

  const item = sub.items.data[0];
  const plan = planForPrice(item?.price.id);
  const ended = ["canceled", "incomplete_expired"].includes(sub.status);

  await prisma.user.update({
    where: { id: user.id },
    data: {
      stripeCustomerId: customerId,
      stripeSubscriptionId: ended ? null : sub.id,
      subscriptionStatus: sub.status,
      plan: ended || !plan ? "FREE" : plan,
      currentPeriodEnd: item?.current_period_end ? new Date(item.current_period_end * 1000) : null,
      cancelAtPeriodEnd: sub.cancel_at_period_end,
      stripeSyncedAt: syncedAt,
    },
  });
  return { userId: user.id, plan: ended || !plan ? "FREE" : plan, status: sub.status };
}
