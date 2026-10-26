import type { SiteAudit, Store } from "@/generated/prisma/client";
import type { AuditCheck } from "./audit";
import type { storeStats } from "./stats";
import { ENGINE_LABELS, type EngineKey } from "./engines";
import { pagePlansFor } from "./suggest";
import type { ReportData } from "@/components/ai/report-dashboard";

type Stats = Awaited<ReturnType<typeof storeStats>>;

const md = (d: Date) => new Intl.DateTimeFormat("ja-JP", { timeZone: "Asia/Tokyo", month: "numeric", day: "numeric" }).format(d);

// 実際の計測結果とサイト診断から、報告書のデータを作る
export function buildReportData(store: Store, stats: Stats, audit: SiteAudit | null): ReportData {
  const latestRun = stats.latest ? stats.runs.find((r) => r.id === stats.latest!.id) : undefined;
  const engines = (latestRun?.engines ?? []).map((e) => ENGINE_LABELS[e as EngineKey] ?? e).join("・") || "AI";
  const missed = stats.questions.filter((q) => q.hits === 0);

  // 比較ランキング：自社＋登録した競合＋回答によく出た名前（重複は除く）
  const ranking: ReportData["ranking"] = [];
  if (stats.latest) {
    ranking.push({ label: store.name, hits: stats.latest.targetHits, self: true, registered: false });
    for (const c of stats.competitors) ranking.push({ label: c.name, hits: c.hits, self: false, registered: true });
    for (const s of stats.frequentShops) {
      if (!ranking.some((r) => r.label === s.label)) ranking.push({ label: s.label, hits: s.count, self: false, registered: false });
    }
  }

  return {
    name: store.name,
    url: store.websiteUrl,
    createdAt: new Date(),
    description: stats.latest
      ? `${engines}に、見込み客が聞きそうな質問${stats.questions.length}問を聞き、紹介されたか・何番目か・どのサイトを根拠にしたかを記録しました。`
      : "まだ計測していません。計測すると、紹介率・比較ランキング・質問ごとの結果が表示されます。",
    measured: stats.latest ? { hits: stats.latest.targetHits, total: stats.latest.answers, at: stats.latest.createdAt } : null,
    score: audit?.score ?? null,
    questions: stats.questions,
    ranking,
    sources: stats.sources.map((s) => ({ domain: s.domain, count: s.count, kind: s.kind })),
    trend: stats.trend.map((t) => {
      const v = Object.values(t.byEngine).reduce((acc, e) => ({ hits: acc.hits + e.hits, total: acc.total + e.total }), { hits: 0, total: 0 });
      return { label: md(t.date), rate: v.total ? Math.round((v.hits / v.total) * 100) : 0 };
    }),
    checks: (audit?.checks ?? []) as AuditCheck[],
    plans: store.kind === "WEB" ? pagePlansFor(missed.map((q) => q.text), store) : [],
  };
}
