import Link from "next/link";
import { notFound } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { requireAccount } from "@/lib/session";
import { findStoreFor } from "@/lib/ai-check/access";
import { storeStats } from "@/lib/ai-check/stats";
import { pagePlansFor } from "@/lib/ai-check/suggest";
import type { AuditCheck } from "@/lib/ai-check/audit";
import { FixList, PagePlans } from "@/components/ai/fix-list";
import { PrintButton } from "@/components/ai/print-button";

export const metadata = { title: "診断報告書 | ススメル" };

const fmtDay = (d: Date) => new Intl.DateTimeFormat("ja-JP", { timeZone: "Asia/Tokyo", year: "numeric", month: "long", day: "numeric" }).format(d);
const pct = (hits: number, total: number) => (total ? Math.round((hits / total) * 100) : 0);

// お客様にそのまま渡せる報告書（印刷・PDF保存向け）
export default async function ReportPage({ params }: PageProps<"/ai/app/stores/[id]/report">) {
  const user = await requireAccount();
  const { id } = await params;
  const store = await findStoreFor(user, id);
  if (!store) notFound();

  const [stats, audit] = await Promise.all([storeStats(id), prisma.siteAudit.findFirst({ where: { storeId: id }, orderBy: { createdAt: "desc" } })]);
  const checks = (audit?.checks ?? []) as AuditCheck[];
  // 重い順（直す → 直すとよい）に並べる
  const todo = [...checks.filter((c) => c.status === "ng"), ...checks.filter((c) => c.status === "warn")];
  const missed = stats.questions.filter((q) => q.hits === 0);
  const plans = store.kind === "WEB" ? pagePlansFor(missed.map((q) => q.text), store) : [];
  const others = [
    ...stats.competitors.filter((c) => c.hits > 0).map((c) => ({ label: c.name, count: c.hits, registered: true })),
    ...stats.frequentShops.map((s) => ({ label: s.label, count: s.count, registered: false })),
  ].slice(0, 8);
  const next = [...todo.slice(0, 3).map((c) => c.title), ...plans.slice(0, 2).map((p) => `ページを作る：${p.title}`)].slice(0, 5);

  return (
    <article className="mx-auto max-w-3xl space-y-10 bg-white p-6 sm:p-10 print:max-w-none print:p-0">
      <div className="flex flex-wrap items-center justify-between gap-3 print:hidden">
        <Link href={`/ai/app/stores/${id}?tab=improve`} className="text-sm text-slate-600 hover:underline">
          ← 戻る
        </Link>
        <PrintButton />
      </div>

      <header className="border-b-2 border-slate-900 pb-5">
        <p className="text-sm font-semibold text-brand">AI検索の診断報告書</p>
        <h1 className="mt-1 text-3xl font-black text-slate-900">{store.name}</h1>
        <p className="mt-1 break-all text-sm text-slate-600">
          {store.websiteUrl ?? "公式サイト未登録"}・{fmtDay(new Date())}作成・ススメル
        </p>
      </header>

      <section className="grid gap-6 sm:grid-cols-3">
        <div>
          <p className="text-sm font-semibold text-slate-600">AIに紹介された割合</p>
          <p className="font-num text-5xl font-semibold text-slate-900">
            {stats.latest ? pct(stats.latest.targetHits, stats.latest.answers) : "—"}
            <span className="text-2xl text-slate-500">%</span>
          </p>
          <p className="text-sm text-slate-600">{stats.latest ? `${stats.latest.answers}回答中 ${stats.latest.targetHits}回（${fmtDay(stats.latest.createdAt)}）` : "未計測"}</p>
        </div>
        <div>
          <p className="text-sm font-semibold text-slate-600">サイト診断の点数</p>
          <p className="font-num text-5xl font-semibold text-slate-900">
            {audit?.score ?? "—"}
            <span className="text-2xl text-slate-500"> / 100</span>
          </p>
          <p className="text-sm text-slate-600">{audit ? `直すところ ${todo.length}項目` : "未診断"}</p>
        </div>
        <div>
          <p className="text-sm font-semibold text-slate-600">紹介されなかった質問</p>
          <p className="font-num text-5xl font-semibold text-slate-900">{stats.latest ? missed.length : "—"}</p>
          <p className="text-sm text-slate-600">{stats.latest ? `${stats.questions.length}問中` : "未計測"}</p>
        </div>
      </section>

      {next.length > 0 && (
        <section className="rounded-xl bg-slate-900 p-6 text-white print:border print:border-slate-900 print:bg-white print:text-slate-900">
          <h2 className="text-lg font-bold">次の30日でやること</h2>
          <ol className="mt-3 list-decimal space-y-1 pl-5">
            {next.map((n) => (
              <li key={n}>{n}</li>
            ))}
          </ol>
        </section>
      )}

      {stats.latest && (
        <section className="space-y-3">
          <h2 className="text-xl font-bold text-slate-900">質問ごとの紹介</h2>
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-slate-300 text-left text-slate-600">
                <th className="py-2 font-semibold">見込み客がAIに聞きそうな質問</th>
                <th className="py-2 text-right font-semibold">紹介</th>
                <th className="py-2 text-right font-semibold">順位</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {stats.questions.map((q) => (
                <tr key={q.no}>
                  <td className="py-2 pr-3 text-slate-800">{q.text}</td>
                  <td className="py-2 text-right font-num font-semibold">{q.hits ? `${q.hits}/${q.total}` : <span className="text-slate-500">なし</span>}</td>
                  <td className="py-2 text-right font-num">{q.ranks.length ? q.ranks.map((r) => `${r}位`).join("・") : q.hits ? "言及" : "—"}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </section>
      )}

      {(others.length > 0 || stats.sources.length > 0) && (
        <section className="grid gap-8 sm:grid-cols-2">
          {others.length > 0 && (
            <div className="space-y-2">
              <h2 className="text-xl font-bold text-slate-900">代わりに紹介されているもの</h2>
              <ul className="divide-y divide-slate-100 text-sm">
                {others.map((o) => (
                  <li key={o.label} className="flex justify-between gap-3 py-1.5">
                    <span className="text-slate-800">
                      {o.label}
                      {o.registered && <span className="ml-1 text-slate-500">（競合）</span>}
                    </span>
                    <span className="font-num">{o.count}回</span>
                  </li>
                ))}
              </ul>
            </div>
          )}
          {stats.sources.length > 0 && (
            <div className="space-y-2">
              <h2 className="text-xl font-bold text-slate-900">AIが引用したサイト</h2>
              <ul className="divide-y divide-slate-100 text-sm">
                {stats.sources.slice(0, 8).map((s) => (
                  <li key={s.domain} className="flex justify-between gap-3 py-1.5">
                    <span className="min-w-0 break-all text-slate-800">
                      {s.domain}
                      {s.kind === "official" && <span className="ml-1 font-semibold text-brand">（自社）</span>}
                    </span>
                    <span className="font-num">{s.count}回</span>
                  </li>
                ))}
              </ul>
            </div>
          )}
        </section>
      )}

      {audit && (
        <section className="space-y-4">
          <h2 className="text-xl font-bold text-slate-900">サイトの直すところ（上から順に）</h2>
          <FixList checks={checks} />
        </section>
      )}

      {plans.length > 0 && (
        <section className="space-y-4">
          <h2 className="text-xl font-bold text-slate-900">紹介されなかった質問と、作るページの案</h2>
          <PagePlans plans={plans} />
        </section>
      )}

      <footer className="border-t border-slate-200 pt-4 text-sm text-slate-500">
        AIの回答は毎回変わります。この報告書の数字は、決まった質問をAPIで聞いた結果の目安で、すべての利用者への表示を表すものではありません。紹介や問い合わせの増加をお約束するものではありません。
      </footer>
    </article>
  );
}
