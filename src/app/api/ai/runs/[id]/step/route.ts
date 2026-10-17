import { NextResponse } from "next/server";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { findStoreFor } from "@/lib/ai-check/access";
import { processAnswers } from "@/lib/ai-check/runner";

// 1問の回答に最大3分ほどかかるため、時間に余裕を持たせる
export const maxDuration = 300;

// 計測画面を開いている間、画面から繰り返し呼ばれて未処理の質問を進める
export async function POST(_req: Request, ctx: RouteContext<"/api/ai/runs/[id]/step">) {
  const session = await auth();
  if (!session?.user?.id) return NextResponse.json({ error: "unauthorized" }, { status: 401 });

  const { id } = await ctx.params;
  const run = await prisma.measurementRun.findUnique({ where: { id }, select: { storeId: true } });
  if (!run || !(await findStoreFor(session.user, run.storeId))) {
    return NextResponse.json({ error: "not found" }, { status: 404 });
  }

  await processAnswers({ runId: id, deadline: Date.now() + 90_000 });

  const [current, total, pending] = await Promise.all([
    prisma.measurementRun.findUniqueOrThrow({ where: { id }, select: { status: true } }),
    prisma.answer.count({ where: { runId: id } }),
    prisma.answer.count({ where: { runId: id, status: "PENDING" } }),
  ]);
  return NextResponse.json({ status: current.status, total, done: total - pending });
}
