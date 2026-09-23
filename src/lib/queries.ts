import { prisma } from "@/lib/prisma";
import type { AccountOption } from "@/components/post-form";

export async function getAccountOptions(): Promise<AccountOption[]> {
  const accounts = await prisma.socialAccount.findMany({
    orderBy: [{ client: { name: "asc" } }, { platform: "asc" }],
    include: { client: { select: { name: true } } },
  });
  return accounts.map((a) => ({
    id: a.id,
    platform: a.platform,
    handle: a.handle,
    clientName: a.client.name,
  }));
}
