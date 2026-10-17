import { prisma } from "@/lib/prisma";
import type { requireAccount } from "@/lib/session";

type Account = Awaited<ReturnType<typeof requireAccount>>;

// オーナーは自分の店舗だけ、チームは全店舗を扱える
export async function findStoreFor(user: Account, storeId: string) {
  const store = await prisma.store.findUnique({ where: { id: storeId } });
  if (!store || (user.role === "OWNER" && store.ownerId !== user.id)) return null;
  return store;
}
