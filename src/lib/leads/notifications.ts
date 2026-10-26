import { prisma } from "@/lib/prisma";
import { leadMailConfig, sendLeadNotice } from "./mail";

const LOCK_MS = 2 * 60_000;
export type NotifyResult = "sent" | "failed" | "skipped" | "busy";

export async function notifyLead(id: string, deliver = sendLeadNotice): Promise<NotifyResult> {
  if (!leadMailConfig()) return "skipped";
  const now = new Date();
  const claim = await prisma.lead.updateMany({
    where: { id, notifiedAt: null, OR: [{ notificationLockedAt: null }, { notificationLockedAt: { lt: new Date(now.getTime() - LOCK_MS) } }] },
    data: { notificationLockedAt: now, notificationAttemptedAt: now, notificationAttempts: { increment: 1 }, notificationError: null },
  });
  if (!claim.count) return "busy";
  try {
    const lead = await prisma.lead.findUniqueOrThrow({ where: { id } });
    await deliver(lead);
    await prisma.lead.update({ where: { id }, data: { notifiedAt: new Date(), notificationLockedAt: null, notificationError: null } });
    return "sent";
  } catch {
    // プロバイダーのレスポンスや秘密鍵・個人情報をログやDBへ残さない。
    await prisma.lead.update({ where: { id }, data: { notificationLockedAt: null, notificationError: "配信サービスへの送信に失敗しました。設定を確認して再送してください。" } });
    return "failed";
  }
}

export async function retryPendingLeadNotifications() {
  if (!leadMailConfig()) return { sent: 0, failed: 0, enabled: false };
  const now = Date.now();
  const leads = await prisma.lead.findMany({
    where: {
      notifiedAt: null, status: { not: "COMPLETED" }, notificationAttempts: { lt: 5 },
      AND: [
        { OR: [{ notificationAttemptedAt: null }, { notificationAttemptedAt: { lt: new Date(now - 15 * 60_000) } }] },
        { OR: [{ notificationLockedAt: null }, { notificationLockedAt: { lt: new Date(now - LOCK_MS) } }] },
      ],
    },
    orderBy: { createdAt: "asc" }, take: 10, select: { id: true },
  });
  let sent = 0;
  let failed = 0;
  for (const lead of leads) {
    const result = await notifyLead(lead.id);
    if (result === "sent") sent++;
    if (result === "failed") failed++;
  }
  return { sent, failed, enabled: true };
}
