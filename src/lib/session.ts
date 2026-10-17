import { redirect } from "next/navigation";
import { auth } from "@/auth";

// 運用代行チーム用の画面・操作。AI検索チェックの店舗オーナーは自分の画面へ戻す。
export async function requireUser() {
  const session = await auth();
  if (!session?.user?.id) redirect("/login");
  if (session.user.role === "OWNER") redirect("/ai/app");
  return session.user;
}

// AI検索チェックの画面。オーナーは自分の店舗、チームは全店舗を扱える。
export async function requireAccount() {
  const session = await auth();
  if (!session?.user?.id) redirect("/ai/login");
  return session.user;
}
