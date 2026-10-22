import { prisma } from "@/lib/prisma";
import { ask, enabledEngines, type EngineKey } from "./engines";
import { competitorHits, domainOf, extractShops, rankOf } from "./extract";
import { dailyAnswerLimit } from "./questions";

// 処理中のまま止まった回答（関数のタイムアウト等）を再取得するまでの時間
const STALE_LOCK_MS = 5 * 60 * 1000;

export async function createRun(storeId: string, repeats: number) {
  const engines = enabledEngines();
  if (engines.length === 0) throw new Error("計測に使えるAIが設定されていません（OPENAI_API_KEY / GEMINI_API_KEY）");

  const questions = await prisma.question.findMany({ where: { storeId }, orderBy: { sort: "asc" } });
  if (questions.length === 0) throw new Error("質問が登録されていません");

  const answers = questions.flatMap((q, i) =>
    engines.flatMap((engine) =>
      Array.from({ length: repeats }, (_, r) => ({
        questionText: q.text,
        questionNo: i + 1,
        engine,
        repeat: r + 1,
      })),
    ),
  );
  const since = new Date(Date.now() - 24 * 3600_000);
  const used = await prisma.answer.count({ where: { createdAt: { gt: since } } });
  if (used + answers.length > dailyAnswerLimit()) {
    throw new Error("本日の計測の受付上限に達しました。明日もう一度お試しください");
  }

  return prisma.measurementRun.create({
    data: { storeId, engines, repeats, answers: { create: answers } },
  });
}

async function claimNext(runId?: string) {
  const stale = new Date(Date.now() - STALE_LOCK_MS);
  for (let attempt = 0; attempt < 5; attempt++) {
    const candidate = await prisma.answer.findFirst({
      where: {
        status: "PENDING",
        OR: [{ lockedAt: null }, { lockedAt: { lt: stale } }],
        ...(runId ? { runId } : {}),
      },
      orderBy: [{ createdAt: "asc" }, { questionNo: "asc" }, { repeat: "asc" }],
      select: { id: true, lockedAt: true },
    });
    if (!candidate) return null;
    // 同時に動く別の処理と取り合わないよう、ロック状態が変わっていない時だけ確保する
    const { count } = await prisma.answer.updateMany({
      where: { id: candidate.id, status: "PENDING", lockedAt: candidate.lockedAt },
      data: { lockedAt: new Date() },
    });
    if (count === 1) {
      return prisma.answer.findUniqueOrThrow({
        where: { id: candidate.id },
        include: { run: { include: { store: true } } },
      });
    }
  }
  return null;
}

type Target = { kind: "RESTAURANT" | "WEB"; name: string; aliases: string[]; websiteUrl: string | null };

// Webサービスは、名前ではなくURL（ドメイン）だけで紹介されることがあるため、
// ドメインも名前として扱い、参考ページに公式サイトがあれば「本文で言及」（0）とみなす。
function targetRank(store: Target, shops: string[], text: string, sources: string[]): number | null {
  const domain = store.kind === "WEB" && store.websiteUrl ? domainOf(store.websiteUrl) : null;
  const rank = rankOf([store.name, ...store.aliases, ...(domain ? [domain] : [])], shops, text);
  if (rank !== null || !domain) return rank;
  return sources.some((s) => domainOf(s) === domain) ? 0 : null;
}

async function finishRunIfComplete(runId: string) {
  const pending = await prisma.answer.count({ where: { runId, status: "PENDING" } });
  if (pending > 0) return;
  const done = await prisma.answer.count({ where: { runId, status: "DONE" } });
  await prisma.measurementRun.updateMany({
    where: { id: runId, status: "RUNNING" },
    data: { status: done > 0 ? "DONE" : "FAILED", finishedAt: new Date() },
  });
}

// 未処理の回答を1件ずつ処理する。deadline を過ぎたら次の呼び出しに任せる。
export async function processAnswers({ runId, deadline }: { runId?: string; deadline: number }) {
  let processed = 0;
  while (Date.now() < deadline) {
    const answer = await claimNext(runId);
    if (!answer) break;
    const store = answer.run.store;
    try {
      const res = await ask(answer.engine as EngineKey, answer.questionText);
      const shops = extractShops(res.text);
      const sources = [...new Set(res.sources)];
      await prisma.answer.update({
        where: { id: answer.id },
        data: {
          status: "DONE",
          model: res.model,
          answerText: res.text,
          shops,
          targetRank: targetRank(store, shops, res.text, sources),
          competitorHits: competitorHits(store.competitors, shops, res.text),
          sources,
          completedAt: new Date(),
          lockedAt: null,
        },
      });
    } catch (e) {
      await prisma.answer.update({
        where: { id: answer.id },
        data: { status: "ERROR", error: String(e instanceof Error ? e.message : e).slice(0, 500), completedAt: new Date(), lockedAt: null },
      });
    }
    processed++;
    await finishRunIfComplete(answer.runId);
  }
  return { processed };
}
