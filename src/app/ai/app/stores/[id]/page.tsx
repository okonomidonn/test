import Link from "next/link";
import { notFound } from "next/navigation";
import { prisma } from "@/lib/prisma";
import { requireAccount } from "@/lib/session";
import { findStoreFor } from "@/lib/ai-check/access";
import { storeStats } from "@/lib/ai-check/stats";
import { ENGINE_LABELS, enabledEngines, type EngineKey } from "@/lib/ai-check/engines";
import { ownerPlan, ownerRunQuota } from "@/lib/billing/limits";
import type { AuditCheck } from "@/lib/ai-check/audit";
import { deleteStore, runAudit, startRun, updateQuestions, updateStore } from "@/app/actions/ai-check";
import { AuditForm, QuestionsForm, StartRunForm, StoreForm, WebServiceForm } from "@/components/ai/forms";
import { TERMS, type TargetKind } from "@/lib/ai-check/terms";
import { BarList, TrendChart } from "@/components/ai/charts";
import { ConfirmButton } from "@/components/confirm-button";
import { cardClass, dangerButtonClass } from "@/components/ui";

const TABS = [
  { key: "check", label: "AIでの表示チェック" },
  { key: "sources", label: "参考にされたページ" },
  { key: "improve", label: "改善の提案" },
  { key: "settings", label: "設定" },
] as const;
type Tab = (typeof TABS)[number]["key"];

const fmtDate = (d: Date) =>
  new Intl.DateTimeFormat("ja-JP", { timeZone: "Asia/Tokyo", month: "numeric", day: "numeric", hour: "2-digit", minute: "2-digit" }).format(d);
const pct = (hits: number, total: number) => (total ? Math.round((hits / total) * 100) : 0);

export default async function StorePage({ params, searchParams }: PageProps<"/ai/app/stores/[id]">) {
  const user = await requireAccount();
  const { id } = await params;
  const { tab: rawTab } = await searchParams;
  const store = await findStoreFor(user, id);
  if (!store) notFound();
  const tab: Tab = TABS.some((t) => t.key === rawTab) ? (rawTab as Tab) : "check";

  return (
    <div className="space-y-6">
      <div>
        <Link href="/ai/app" className="text-sm text-slate-600 hover:underline">
          ← 登録一覧
        </Link>
        <h1 className="mt-1 text-3xl font-black text-slate-900">{store.name}</h1>
        <p className="text-sm text-slate-600">
          {store.kind === "WEB" ? (
            <>
              Webサービス・{store.genre}
              {store.audience ? `（${store.audience}向け）` : ""}
            </>
          ) : (
            <>
              {store.area}
              {store.station ? `（${store.station}）` : ""}・{store.genre}
            </>
          )}
        </p>
      </div>

      <NextStep storeId={id} hasUrl={!!store.websiteUrl} />

      <nav className="flex gap-1 overflow-x-auto border-b border-slate-200 text-sm">
        {TABS.map((t) => (
          <Link
            key={t.key}
            href={`/ai/app/stores/${id}?tab=${t.key}`}
            className={`whitespace-nowrap border-b-2 px-4 py-2.5 font-semibold ${
              tab === t.key ? "border-brand text-brand" : "border-transparent text-slate-600 hover:text-slate-800"
            }`}
          >
            {t.label}
          </Link>
        ))}
      </nav>

      {tab === "check" && <CheckTab storeId={id} userId={user.id} isTeam={user.role === "TEAM"} kind={store.kind} />}
      {tab === "sources" && <SourcesTab storeId={id} kind={store.kind} />}
      {tab === "improve" && <ImproveTab storeId={id} hasUrl={!!store.websiteUrl} kind={store.kind} />}
      {tab === "settings" && <SettingsTab storeId={id} userId={user.id} isTeam={user.role === "TEAM"} />}
    </div>
  );
}

// 画面を開いたときに「次に何をすればいいか」を1つだけ大きく示す
async function NextStep({ storeId, hasUrl }: { storeId: string; hasUrl: boolean }) {
  const [stats, audit] = await Promise.all([
    storeStats(storeId),
    prisma.siteAudit.findFirst({ where: { storeId }, orderBy: { createdAt: "desc" } }),
  ]);
  const running = stats.runs.find((r) => r.status === "RUNNING");
  const base = `/ai/app/stores/${storeId}`;
  const issues = ((audit?.checks ?? []) as AuditCheck[]).filter((c) => c.status !== "ok").length;
  const missed = stats.questions.filter((q) => q.hits === 0).length;

  let step: { text: string; href: string; label: string };
  if (running) step = { text: `AIに聞いて計測しています（${running.done}/${running.total}）。終わるまで数分かかります。`, href: `${base}/runs/${running.id}`, label: "進み具合を見る" };
  else if (!hasUrl) step = { text: "公式サイトのURLを登録しましょう。サイト診断と、AIが公式サイトを参考にしたかの判定に使います。", href: `${base}?tab=settings`, label: "設定を開く" };
  else if (!audit) step = { text: "まず公式サイトを診断しましょう。無料で、30秒ほどで終わります。", href: `${base}?tab=improve`, label: "サイトを診断する" };
  else if (!stats.latest && enabledEngines().length > 0) step = { text: "AIに質問して、紹介されているかを計測しましょう。", href: `${base}?tab=check`, label: "計測を始める" };
  else if (issues > 0) step = { text: `サイト診断で直すとよい点が${issues}個あります。上から順に直しましょう。`, href: `${base}?tab=improve`, label: "直す点を見る" };
  else if (missed > 0) step = { text: `紹介されなかった質問が${missed}問あります。答えになる内容をサイトに書き足しましょう。`, href: `${base}?tab=improve`, label: "改善の提案を見る" };
  else step = { text: "よい状態です。週に1回計測して、紹介され続けているかを確かめましょう。", href: `${base}?tab=check`, label: "計測結果を見る" };

  return (
    <section className="flex flex-col gap-4 rounded-xl bg-slate-900 p-5 text-white sm:flex-row sm:items-center sm:justify-between sm:px-6">
      <div className="min-w-0 flex-1">
        <p className="text-sm font-bold text-mark">次にやること</p>
        <p className="mt-1 text-base font-semibold">{step.text}</p>
      </div>
      <Link href={step.href} className="shrink-0 rounded-lg bg-mark px-5 py-3 text-center font-bold text-slate-900 hover:brightness-95">
        {step.label}
      </Link>
    </section>
  );
}

async function CheckTab({ storeId, userId, isTeam, kind }: { storeId: string; userId: string; isTeam: boolean; kind: TargetKind }) {
  const t = TERMS[kind];
  const stats = await storeStats(storeId);
  const running = stats.runs.find((r) => r.status === "RUNNING");
  const quota = isTeam ? null : await ownerRunQuota(userId, storeId);
  let disabledReason: string | undefined;
  if (enabledEngines().length === 0) disabledReason = "計測に使うAIのAPIキーが未設定です";
  else if (quota && !quota.allowed) disabledReason = quota.reason;

  const engines = [...new Set(stats.trend.flatMap((t) => Object.keys(t.byEngine)))] as EngineKey[];
  const latestByEngine = stats.trend.at(-1)?.byEngine ?? {};
  const shopMax = Math.max(stats.latest?.targetHits ?? 0, ...stats.competitors.map((c) => c.hits), ...stats.frequentShops.map((s) => s.count), 1);
  const competitorRows = [
    { label: `${stats.store.name}（${t.self}）`, value: stats.latest?.targetHits ?? 0, highlight: true },
    ...stats.competitors.map((c) => ({ label: c.name, value: c.hits })),
  ];

  return (
    <div className="space-y-6">
      <section className={`${cardClass} flex flex-wrap items-center justify-between gap-4`}>
        <div>
          <h2 className="font-bold">AIに聞いて計測</h2>
          <p className="mt-1 text-sm text-slate-600">
            登録した質問を{engines.length ? engines.map((e) => ENGINE_LABELS[e]).join("・") : "AI"}に聞き、{t.target}が紹介されたかを記録します。1問30秒〜2分ほどかかります。
          </p>
        </div>
        {running ? (
          <Link href={`/ai/app/stores/${storeId}/runs/${running.id}`} className="rounded-lg bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-800 ring-1 ring-amber-200">
            計測中（{running.done}/{running.total}）→ 進み具合を見る
          </Link>
        ) : (
          <div className="space-y-1 text-right">
            <StartRunForm action={startRun.bind(null, storeId)} isTeam={isTeam} disabledReason={disabledReason} />
            {quota && (
              <p className="text-sm text-slate-600">
                {quota.plan.name}プラン：30日間で{quota.usedThisMonth} / {quota.plan.runsPerMonth}回使用・
                <Link href="/ai/app/billing" className="font-semibold text-brand hover:underline">
                  プランを見る
                </Link>
              </p>
            )}
          </div>
        )}
      </section>

      {!stats.latest ? (
        <p className={`${cardClass} text-center text-sm text-slate-600`}>まだ計測結果がありません。上のボタンから計測を始めてください。</p>
      ) : (
        <>
          <section className={`${cardClass} grid gap-6 sm:grid-cols-[auto_1fr] sm:items-center sm:gap-10`}>
            <div>
              <p className="text-sm font-semibold text-slate-600">最新の推薦率</p>
              <p className="font-num text-6xl font-semibold leading-none text-slate-900">
                {pct(stats.latest.targetHits, stats.latest.answers)}
                <span className="text-3xl text-slate-500">%</span>
              </p>
              <p className="mt-2 text-sm text-slate-600">
                {stats.latest.answers}回答中 <span className="font-num font-semibold text-slate-900">{stats.latest.targetHits}</span>回 紹介・{fmtDate(stats.latest.createdAt)}
              </p>
            </div>
            <dl className="space-y-3 sm:border-l sm:border-slate-200 sm:pl-10">
              {engines.slice(0, 2).map((e) => {
                const v = latestByEngine[e];
                const value = v ? pct(v.hits, v.total) : 0;
                return (
                  <div key={e} title={v ? `${ENGINE_LABELS[e]}：${v.total}回答中 ${v.hits}回` : undefined}>
                    <div className="flex items-baseline justify-between gap-3 text-sm">
                      <dt className="font-semibold text-slate-800">{ENGINE_LABELS[e]}</dt>
                      <dd className="text-slate-600">
                        {v ? (
                          <>
                            <span className="font-num text-lg font-semibold text-slate-900">{value}%</span>（{v.total}回答中 {v.hits}回）
                          </>
                        ) : (
                          "最新の計測なし"
                        )}
                      </dd>
                    </div>
                    <div className="mt-1 h-2.5 rounded-full bg-slate-100">
                      <div className="h-2.5 rounded-full bg-brand" style={{ width: `${value}%` }} />
                    </div>
                  </div>
                );
              })}
            </dl>
          </section>

          {stats.trend.length > 1 && (
            <section className={cardClass}>
              <h2 className="mb-3 font-bold">推薦率の推移</h2>
              <TrendChart
                points={stats.trend.map((t) => ({
                  label: fmtDate(t.date),
                  values: Object.fromEntries(engines.map((e) => [e, t.byEngine[e] ? pct(t.byEngine[e].hits, t.byEngine[e].total) : null])),
                }))}
                series={engines.slice(0, 2).map((e) => ({ key: e, label: ENGINE_LABELS[e] }))}
              />
            </section>
          )}

          <section className={cardClass}>
            <h2 className="font-bold">質問ごとの結果（最新の計測）</h2>
            <p className="mt-1 text-sm text-slate-600">紹介されなかった質問が、改善のねらい目です。</p>
            <table className="mt-3 w-full text-sm">
              <thead>
                <tr className="border-b border-slate-200 text-left text-sm text-slate-600">
                  <th className="py-2 font-medium">質問</th>
                  <th className="py-2 text-right font-medium">紹介された回数</th>
                  <th className="py-2 text-right font-medium">順位</th>
                </tr>
              </thead>
              <tbody>
                {stats.questions.map((q) => (
                  <tr key={q.no} className="border-b border-slate-100">
                    <td className="py-2 pr-3 text-slate-700">{q.text}</td>
                    <td className="py-2 text-right">
                      <span className="inline-flex items-center gap-2" title={`${q.total}回答中 ${q.hits}回 紹介`}>
                        <span className="flex gap-1" aria-hidden>
                          {Array.from({ length: q.total }, (_, i) => (
                            <span key={i} className={`h-3 w-3 rounded-full ${i < q.hits ? "bg-brand" : "border-2 border-slate-300"}`} />
                          ))}
                        </span>
                        <span className={`font-num text-sm font-semibold ${q.hits ? "text-slate-900" : "text-slate-500"}`}>{q.hits ? `${q.hits}/${q.total}` : "なし"}</span>
                      </span>
                    </td>
                    <td className="py-2 text-right text-sm text-slate-700">
                      {q.ranks.length ? <span className="font-num font-semibold text-slate-900">{q.ranks.map((r) => `${r}位`).join("・")}</span> : q.hits ? "本文で言及" : "—"}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
            <Link href={`/ai/app/stores/${storeId}/runs/${stats.latest.id}`} className="mt-3 inline-block text-sm font-semibold text-brand hover:underline">
              AIの回答全文を見る →
            </Link>
          </section>

          <div className="grid gap-6 lg:grid-cols-2">
            <section className={cardClass}>
              <h2 className="font-bold">競合との比較（最新の計測）</h2>
              <p className="mt-1 text-sm text-slate-600">何回の回答で紹介されたか</p>
              <div className="mt-4">
                {stats.competitors.length ? (
                  <BarList rows={competitorRows} max={shopMax} />
                ) : (
                  <p className="text-sm text-slate-600">「設定」で{t.competitor}を登録すると表示されます。</p>
                )}
              </div>
            </section>
            <section className={cardClass}>
              <h2 className="font-bold">{t.frequent}</h2>
              <p className="mt-1 text-sm text-slate-600">最新の計測で2回以上名前が出たもの（自動抽出のため、名前以外が混ざることがあります）</p>
              <div className="mt-4">
                {stats.frequentShops.length ? (
                  <BarList rows={stats.frequentShops.map((s) => ({ label: s.label, value: s.count }))} max={shopMax} />
                ) : (
                  <p className="text-sm text-slate-600">まだありません。</p>
                )}
              </div>
            </section>
          </div>
        </>
      )}

      <p className="text-sm text-slate-500">
        ※ AIの回答は毎回変わります。推薦率は決まった質問で測った目安で、すべての利用者への表示確率ではありません。APIでの回答はアプリの回答と完全には一致しない場合があります。
      </p>
    </div>
  );
}

const KIND_STYLE = { official: "bg-indigo-50 text-indigo-700", platform: "bg-amber-50 text-amber-800", other: "bg-slate-100 text-slate-700" } as const;

async function SourcesTab({ storeId, kind }: { storeId: string; kind: TargetKind }) {
  const stats = await storeStats(storeId);
  const KIND_LABEL = { official: "公式サイト", platform: TERMS[kind].platform, other: "その他（まとめ記事など）" } as const;
  if (stats.sources.length === 0) {
    return <p className={`${cardClass} text-center text-sm text-slate-600`}>計測すると、AIが参考にしたページがここに表示されます。</p>;
  }
  const kinds = (["official", "platform", "other"] as const).map((k) => ({
    kind: k,
    count: stats.sources.filter((s) => s.kind === k).reduce((n, s) => n + s.count, 0),
  }));
  const max = stats.sources[0]?.count ?? 1;

  return (
    <div className="space-y-6">
      <section className="grid gap-4 sm:grid-cols-4">
        <div className={cardClass}>
          <p className="text-sm text-slate-600">参照の総数</p>
          <p className="mt-1 text-3xl font-bold">{stats.totalSources}</p>
        </div>
        {kinds.map((k) => (
          <div key={k.kind} className={cardClass}>
            <p className="text-sm text-slate-600">{KIND_LABEL[k.kind]}</p>
            <p className="mt-1 text-3xl font-bold">{stats.totalSources ? Math.round((k.count / stats.totalSources) * 100) : 0}%</p>
            <p className="text-sm text-slate-600">{k.count}件</p>
          </div>
        ))}
      </section>

      <section className={cardClass}>
        <h2 className="font-bold">よく参考にされたサイト</h2>
        <p className="mt-1 text-sm text-slate-600">{TERMS[kind].sourcesHint}</p>
        <div className="mt-4">
          <BarList rows={stats.sources.slice(0, 15).map((s) => ({ label: s.domain, value: s.count, highlight: s.kind === "official", note: KIND_LABEL[s.kind] }))} max={max} />
        </div>
      </section>

      <section className={cardClass}>
        <h2 className="font-bold">ページ一覧</h2>
        <table className="mt-3 w-full text-sm">
          <thead>
            <tr className="border-b border-slate-200 text-left text-sm text-slate-600">
              <th className="py-2 font-medium">ページ</th>
              <th className="py-2 font-medium">種類</th>
              <th className="py-2 text-right font-medium">回数</th>
            </tr>
          </thead>
          <tbody>
            {stats.sources.slice(0, 30).flatMap((s) =>
              s.urls.map(([url, n]) => (
                <tr key={url} className="border-b border-slate-100">
                  <td className="max-w-md truncate py-2 pr-3">
                    {url.startsWith("http") ? (
                      <a href={url} target="_blank" rel="noopener noreferrer nofollow" className="text-brand hover:underline">
                        {url.replace(/^https?:\/\//, "").replace(/\?utm_source=openai$/, "")}
                      </a>
                    ) : (
                      url
                    )}
                  </td>
                  <td className="py-2">
                    <span className={`rounded-full px-2 py-0.5 text-sm ${KIND_STYLE[s.kind]}`}>{KIND_LABEL[s.kind]}</span>
                  </td>
                  <td className="py-2 text-right text-slate-700">{n}</td>
                </tr>
              )),
            )}
          </tbody>
        </table>
      </section>
    </div>
  );
}

const STATUS_STYLE = { ok: "bg-emerald-50 text-emerald-800", warn: "bg-amber-50 text-amber-900", ng: "bg-rose-50 text-rose-800" } as const;
const STATUS_LABEL = { ok: "✓ OK", warn: "! 要改善", ng: "✕ 問題あり" } as const;
const STATUS_BAR = { ng: "bg-rose-600", warn: "bg-amber-500", ok: "bg-emerald-600" } as const;

async function ImproveTab({ storeId, hasUrl, kind }: { storeId: string; hasUrl: boolean; kind: TargetKind }) {
  const [audit, stats] = await Promise.all([
    prisma.siteAudit.findFirst({ where: { storeId }, orderBy: { createdAt: "desc" } }),
    storeStats(storeId),
  ]);
  const checks = (audit?.checks ?? []) as AuditCheck[];
  const order = { ng: 0, warn: 1, ok: 2 } as const;
  const missed = stats.questions.filter((q) => q.hits === 0);

  return (
    <div className="space-y-6">
      <section className={`${cardClass} flex flex-wrap items-center justify-between gap-4`}>
        <div>
          <h2 className="font-bold">公式サイトの診断</h2>
          <p className="mt-1 text-sm text-slate-600">
            {kind === "WEB"
              ? "AIが読む公式サイトに、一文の立ち位置・強み・1件1ページの情報・構造化データ・llms.txtがそろっているかを確認します。"
              : "AIが読む公式サイトに、紹介に必要な情報が文字で書かれているかを確認します。"}
          </p>
        </div>
        <AuditForm action={runAudit.bind(null, storeId)} hasUrl={hasUrl} />
      </section>

      {audit && (
        <section className={cardClass}>
          <div className="flex flex-wrap items-end justify-between gap-2">
            <div>
              <p className="text-sm font-semibold text-slate-600">サイト診断スコア</p>
              <p className="font-num text-6xl font-semibold leading-none text-slate-900">
                {audit.score}
                <span className="text-2xl text-slate-500"> / 100</span>
              </p>
            </div>
            <p className="min-w-0 break-all text-sm text-slate-600">
              {fmtDate(audit.createdAt)}に診断・{audit.url}
            </p>
          </div>
          {/* 問題あり・要改善・OKの数を、1本の帯で見せる */}
          <div className="mt-5 flex h-3 gap-0.5 overflow-hidden rounded-full" aria-hidden>
            {(["ng", "warn", "ok"] as const).map((st) => {
              const n = checks.filter((c) => c.status === st).length;
              return n ? <span key={st} className={STATUS_BAR[st]} style={{ flexGrow: n }} /> : null;
            })}
          </div>
          <p className="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-700">
            {(["ng", "warn", "ok"] as const).map((st) => (
              <span key={st}>
                {STATUS_LABEL[st]} <span className="font-num font-semibold text-slate-900">{checks.filter((c) => c.status === st).length}</span>
              </span>
            ))}
          </p>
          <ul className="mt-5 divide-y divide-slate-100">
            {[...checks].sort((a, b) => order[a.status] - order[b.status]).map((c) => (
              <li key={c.key} className="grid gap-1.5 py-4 sm:grid-cols-[7.5rem_1fr]">
                <span className={`h-fit w-fit whitespace-nowrap rounded-full px-2.5 py-0.5 text-sm font-semibold ${STATUS_STYLE[c.status]}`}>{STATUS_LABEL[c.status]}</span>
                <div>
                  <p className="font-semibold text-slate-900">{c.title}</p>
                  <p className="text-sm text-slate-700">{c.detail}</p>
                  {c.fix && <p className="mt-1 text-sm text-slate-800">→ {c.fix}</p>}
                </div>
              </li>
            ))}
          </ul>
        </section>
      )}

      <section className={cardClass}>
        <h2 className="font-bold">紹介されなかった質問</h2>
        {stats.latest ? (
          missed.length ? (
            <>
              <p className="mt-1 text-sm text-slate-600">{TERMS[kind].missedHint}</p>
              <ul className="mt-3 list-disc space-y-1 pl-5 text-sm text-slate-700">
                {missed.map((q) => (
                  <li key={q.no}>{q.text}</li>
                ))}
              </ul>
            </>
          ) : (
            <p className="mt-1 text-sm text-slate-700">最新の計測では、すべての質問で紹介されました。</p>
          )
        ) : (
          <p className="mt-1 text-sm text-slate-600">計測するとここに表示されます。</p>
        )}
      </section>
    </div>
  );
}

async function SettingsTab({ storeId, userId, isTeam }: { storeId: string; userId: string; isTeam: boolean }) {
  const store = await prisma.store.findUniqueOrThrow({
    where: { id: storeId },
    include: { questions: { orderBy: { sort: "asc" } } },
  });
  return (
    <div className="grid gap-6 lg:grid-cols-2">
      <section className={cardClass}>
        <h2 className="mb-4 font-bold">{store.kind === "WEB" ? "サービス情報" : "店舗情報"}</h2>
        {store.kind === "WEB" ? (
          <WebServiceForm action={updateStore.bind(null, storeId)} defaults={store} submitLabel="保存する" />
        ) : (
          <StoreForm action={updateStore.bind(null, storeId)} defaults={store} submitLabel="保存する" />
        )}
      </section>
      <div className="space-y-6">
        <section className={cardClass}>
          <h2 className="mb-1 font-bold">AIに聞く質問</h2>
          <QuestionsForm action={updateQuestions.bind(null, storeId)} questions={store.questions.map((q) => q.text)} max={isTeam ? 50 : (await ownerPlan(userId)).maxQuestions} />
        </section>
        <section className={cardClass}>
          <h2 className="mb-1 font-bold">登録の削除</h2>
          <p className="mb-3 text-sm text-slate-600">計測結果もすべて削除されます。</p>
          <form action={deleteStore.bind(null, storeId)}>
            <ConfirmButton message="この登録と計測結果をすべて削除します。よろしいですか？" className={dangerButtonClass}>
              削除する
            </ConfirmButton>
          </form>
        </section>
      </div>
    </div>
  );
}
