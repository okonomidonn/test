import Link from "next/link";
import { notFound } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { requireAccount } from "@/lib/session";
import { findStoreFor } from "@/lib/ai-check/access";
import { ENGINE_LABELS, type EngineKey } from "@/lib/ai-check/engines";
import { RunProgress } from "@/components/ai/run-progress";
import { cardClass } from "@/components/ui";

export default async function RunPage({ params }: PageProps<"/ai/app/stores/[id]/runs/[runId]">) {
  const user = await requireAccount();
  const { id, runId } = await params;
  const store = await findStoreFor(user, id);
  if (!store) notFound();
  const run = await prisma.measurementRun.findFirst({
    where: { id: runId, storeId: id },
    include: { answers: { orderBy: [{ questionNo: "asc" }, { engine: "asc" }, { repeat: "asc" }] } },
  });
  if (!run) notFound();

  const finished = run.answers.filter((a) => a.status !== "PENDING");
  const done = run.answers.filter((a) => a.status === "DONE");
  const hits = done.filter((a) => a.targetRank !== null).length;

  return (
    <div className="space-y-6">
      <div>
        <Link href={`/ai/app/stores/${id}`} className="text-sm text-slate-500 hover:underline">
          ← {store.name}
        </Link>
        <h1 className="mt-1 text-2xl font-bold">計測結果の詳細</h1>
        <p className="text-sm text-slate-500">
          {new Intl.DateTimeFormat("ja-JP", { timeZone: "Asia/Tokyo", dateStyle: "medium", timeStyle: "short" }).format(run.createdAt)}・
          {run.engines.map((e) => ENGINE_LABELS[e as EngineKey] ?? e).join("・")}・各質問{run.repeats}回
        </p>
      </div>

      <section className={cardClass}>
        {run.status === "RUNNING" ? (
          <RunProgress runId={run.id} initialDone={finished.length} total={run.answers.length} />
        ) : (
          <p className="text-sm text-slate-700">
            計測が終わりました。{done.length}回答中 <strong className="text-slate-900">{hits}回</strong> 紹介されました
            {done.length ? `（推薦率 ${Math.round((hits / done.length) * 100)}%）` : ""}。
            {run.answers.length - done.length > 0 && <span className="text-amber-700"> 取得できなかった回答が{run.answers.length - done.length}件あります。</span>}
          </p>
        )}
      </section>

      <section className="space-y-3">
        {finished.map((a) => (
          <details key={a.id} className={`${cardClass} group`}>
            <summary className="flex cursor-pointer list-none flex-wrap items-center justify-between gap-2">
              <span className="text-sm font-semibold text-slate-800">
                Q{a.questionNo}. {a.questionText}
                <span className="ml-2 text-xs font-normal text-slate-500">
                  {ENGINE_LABELS[a.engine as EngineKey] ?? a.engine}
                  {run.repeats > 1 ? `・${a.repeat}回目` : ""}
                </span>
              </span>
              {a.status === "ERROR" ? (
                <span className="rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700">取得失敗</span>
              ) : a.targetRank !== null ? (
                <span className="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                  {a.targetRank > 0 ? `${a.targetRank}番目に紹介` : "本文で言及"}
                </span>
              ) : (
                <span className="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500">紹介なし</span>
              )}
            </summary>
            <div className="mt-4 space-y-3 border-t border-slate-100 pt-4 text-sm">
              {a.status === "ERROR" ? (
                <p className="text-rose-700">{a.error}</p>
              ) : (
                <>
                  {a.shops.length > 0 && <p className="text-xs text-slate-500">回答に出た名前（自動抽出）：{a.shops.join("、")}</p>}
                  <p className="whitespace-pre-wrap leading-relaxed text-slate-700">{a.answerText}</p>
                  {a.sources.length > 0 && <p className="break-all text-xs text-slate-400">参考：{a.sources.join("  ")}</p>}
                </>
              )}
            </div>
          </details>
        ))}
      </section>
    </div>
  );
}
