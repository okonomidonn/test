import { prisma } from "@/lib/prisma";
import { domainOf, normalize } from "./extract";

const GOURMET_SITES = [
  "tabelog.com", "hotpepper.jp", "retty.me", "gnavi.co.jp", "hitosara.com", "ikyu.com",
  "paypaygourmet.yahoo.co.jp", "jalan.net", "aumo.jp", "rtrp.jp", "ozmall.co.jp", "enjoytokyo.jp",
  "google.com", "maps.google.com", "instagram.com", "tripadvisor.jp", "hamoni.jp", "30min.jp",
];

export type SourceKind = "official" | "gourmet" | "other";

export function sourceKind(domain: string, officialUrl: string | null): SourceKind {
  if (officialUrl && domain === domainOf(officialUrl)) return "official";
  if (GOURMET_SITES.some((g) => domain === g || domain.endsWith(`.${g}`))) return "gourmet";
  return "other";
}

export async function storeStats(storeId: string) {
  const store = await prisma.store.findUniqueOrThrow({ where: { id: storeId } });
  const runs = await prisma.measurementRun.findMany({
    where: { storeId },
    orderBy: { createdAt: "asc" },
    include: {
      answers: {
        select: {
          id: true, engine: true, status: true, questionNo: true, questionText: true, repeat: true,
          targetRank: true, competitorHits: true, sources: true, shops: true,
        },
      },
    },
  });

  // 推薦率＝対象店舗が推薦された回答数 ÷ 取得できた回答数（本文で触れただけ＝順位0も推薦に含める）
  const trend = runs
    .filter((r) => r.status !== "RUNNING")
    .map((r) => {
      const byEngine: Record<string, { total: number; hits: number }> = {};
      for (const a of r.answers) {
        if (a.status !== "DONE") continue;
        const e = (byEngine[a.engine] ??= { total: 0, hits: 0 });
        e.total++;
        if (a.targetRank !== null) e.hits++;
      }
      return { runId: r.id, date: r.createdAt, byEngine };
    })
    .filter((t) => Object.keys(t.byEngine).length > 0);

  const latest = [...runs].reverse().find((r) => r.status !== "RUNNING" && r.answers.some((a) => a.status === "DONE"));
  const latestDone = latest?.answers.filter((a) => a.status === "DONE") ?? [];

  // 質問ごとの結果（最新の計測）
  const questions = new Map<number, { text: string; total: number; hits: number; ranks: number[] }>();
  for (const a of latestDone) {
    const q = questions.get(a.questionNo) ?? { text: a.questionText, total: 0, hits: 0, ranks: [] };
    q.total++;
    if (a.targetRank !== null) {
      q.hits++;
      if (a.targetRank > 0) q.ranks.push(a.targetRank);
    }
    questions.set(a.questionNo, q);
  }

  // 競合との比較（最新の計測）。登録した競合に加え、回答によく出る店も拾う。
  const shopCounts = new Map<string, { label: string; count: number }>();
  for (const a of latestDone) {
    const seen = new Set<string>();
    for (const s of a.shops) {
      const key = normalize(s);
      if (key.length < 2 || seen.has(key)) continue;
      seen.add(key);
      const cur = shopCounts.get(key) ?? { label: s, count: 0 };
      cur.count++;
      shopCounts.set(key, cur);
    }
  }
  const targetHits = latestDone.filter((a) => a.targetRank !== null).length;
  const competitors = store.competitors.map((c) => ({
    name: c,
    hits: latestDone.filter((a) => a.competitorHits.includes(c)).length,
  }));
  const frequentShops = [...shopCounts.values()]
    .filter((s) => s.count >= 2)
    .sort((a, b) => b.count - a.count)
    .slice(0, 10);

  // 参考にされたページ（全計測）
  const domains = new Map<string, { domain: string; count: number; urls: Map<string, number> }>();
  let totalSources = 0;
  for (const r of runs) {
    for (const a of r.answers) {
      for (const src of a.sources) {
        totalSources++;
        const d = domainOf(src);
        const cur = domains.get(d) ?? { domain: d, count: 0, urls: new Map() };
        cur.count++;
        cur.urls.set(src, (cur.urls.get(src) ?? 0) + 1);
        domains.set(d, cur);
      }
    }
  }
  const sources = [...domains.values()]
    .sort((a, b) => b.count - a.count)
    .map((d) => ({
      domain: d.domain,
      count: d.count,
      kind: sourceKind(d.domain, store.websiteUrl),
      urls: [...d.urls.entries()].sort((a, b) => b[1] - a[1]).slice(0, 5),
    }));

  return {
    store,
    runs: runs.map((r) => ({
      id: r.id,
      status: r.status,
      createdAt: r.createdAt,
      engines: r.engines,
      total: r.answers.length,
      done: r.answers.filter((a) => a.status !== "PENDING").length,
      errors: r.answers.filter((a) => a.status === "ERROR").length,
    })),
    trend,
    latest: latest ? { id: latest.id, createdAt: latest.createdAt, answers: latestDone.length, targetHits } : null,
    questions: [...questions.entries()].sort((a, b) => a[0] - b[0]).map(([no, q]) => ({ no, ...q })),
    competitors,
    frequentShops,
    sources,
    totalSources,
  };
}
