import Link from "next/link";
import { prisma } from "@/lib/prisma";
import { PlatformBadge } from "@/components/badges";
import { cardClass } from "@/components/ui";
import {
  PLATFORMS,
  engagementCount,
  engagementRate,
  formatDateTime,
  formatNumber,
  formatPercent,
  type Metrics,
  type PlatformKey,
} from "@/lib/constants";

const RANGES = [7, 30, 90] as const;
const DAY = 24 * 60 * 60 * 1000;

function sumMetrics(items: Metrics[]): Metrics {
  return items.reduce(
    (s, m) => ({
      impressions: s.impressions + m.impressions,
      likes: s.likes + m.likes,
      comments: s.comments + m.comments,
      shares: s.shares + m.shares,
      saves: s.saves + m.saves,
    }),
    { impressions: 0, likes: 0, comments: 0, shares: 0, saves: 0 },
  );
}

const jstDay = new Intl.DateTimeFormat("sv-SE", { timeZone: "Asia/Tokyo" });

export default async function DashboardPage({ searchParams }: PageProps<"/dashboard">) {
  const sp = await searchParams;
  const days = RANGES.find((r) => String(r) === sp.days) ?? 30;
  const clientId = typeof sp.client === "string" && sp.client ? sp.client : undefined;
  const accountFilter = clientId ? { account: { clientId } } : {};

  const now = new Date();
  const since = new Date(now.getTime() - days * DAY);

  const [clients, published, upcoming, accounts] = await Promise.all([
    prisma.client.findMany({ orderBy: { name: "asc" }, select: { id: true, name: true } }),
    prisma.post.findMany({
      where: { status: "PUBLISHED", publishedAt: { gte: since }, ...accountFilter },
      include: { account: { include: { client: { select: { name: true } } } } },
    }),
    prisma.post.findMany({
      where: { status: "SCHEDULED", ...accountFilter },
      orderBy: { scheduledAt: "asc" },
      take: 5,
      include: { account: { select: { platform: true, handle: true } } },
    }),
    prisma.socialAccount.findMany({
      where: clientId ? { clientId } : undefined,
      select: { platform: true, followers: true },
    }),
  ]);

  const total = sumMetrics(published);
  const totalFollowers = accounts.reduce((s, a) => s + a.followers, 0);

  const byPlatform = PLATFORMS.map((platform) => {
    const posts = published.filter((p) => p.account.platform === platform);
    const m = sumMetrics(posts);
    return {
      platform,
      posts: posts.length,
      accounts: accounts.filter((a) => a.platform === platform).length,
      engagement: engagementCount(m),
      rate: engagementRate(m),
      impressions: m.impressions,
    };
  }).filter((r) => r.posts > 0 || r.accounts > 0);
  const maxPlatformEngagement = Math.max(1, ...byPlatform.map((r) => r.engagement));

  const dailyMap = new Map<string, number>();
  for (let i = days - 1; i >= 0; i--) dailyMap.set(jstDay.format(new Date(now.getTime() - i * DAY)), 0);
  for (const p of published) {
    const key = jstDay.format(p.publishedAt!);
    if (dailyMap.has(key)) dailyMap.set(key, dailyMap.get(key)! + engagementCount(p));
  }
  const daily = [...dailyMap.entries()];
  const maxDaily = Math.max(1, ...daily.map(([, v]) => v));

  const topPosts = [...published]
    .sort((a, b) => engagementCount(b) - engagementCount(a))
    .slice(0, 5);

  const overdueCount = upcoming.filter((p) => p.scheduledAt && p.scheduledAt < now).length;

  const tiles = [
    { label: "公開投稿数", value: formatNumber(published.length) },
    { label: "インプレッション", value: formatNumber(total.impressions) },
    { label: "エンゲージメント", value: formatNumber(engagementCount(total)) },
    { label: "平均エンゲージメント率", value: formatPercent(engagementRate(total)) },
    { label: "総フォロワー", value: formatNumber(totalFollowers) },
  ];

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-end justify-between gap-3">
        <div>
          <h1 className="text-2xl font-bold">エンゲージメント分析</h1>
          <p className="mt-1 text-sm text-slate-500">公開日ベースで直近{days}日間を集計しています。</p>
        </div>
        <form className="flex items-center gap-2 text-sm">
          <select name="client" defaultValue={clientId ?? ""} className="rounded-lg border border-slate-200 bg-white px-3 py-1.5">
            <option value="">全クライアント</option>
            {clients.map((c) => (
              <option key={c.id} value={c.id}>
                {c.name}
              </option>
            ))}
          </select>
          <select name="days" defaultValue={String(days)} className="rounded-lg border border-slate-200 bg-white px-3 py-1.5">
            {RANGES.map((r) => (
              <option key={r} value={r}>
                直近{r}日
              </option>
            ))}
          </select>
          <button type="submit" className="rounded-lg border border-slate-200 bg-white px-3 py-1.5 hover:bg-slate-50">
            表示
          </button>
        </form>
      </div>

      <div className="grid grid-cols-2 gap-3 sm:grid-cols-5">
        {tiles.map((t) => (
          <div key={t.label} className={cardClass}>
            <p className="text-xs text-slate-500">{t.label}</p>
            <p className="mt-1 text-2xl font-bold tabular-nums">{t.value}</p>
          </div>
        ))}
      </div>

      <section className={cardClass}>
        <h2 className="font-bold">日別エンゲージメント数</h2>
        <div className="mt-4 flex h-40 items-end gap-[2px]" role="img" aria-label="日別エンゲージメント数の棒グラフ">
          {daily.map(([day, value]) => (
            <div key={day} className="group relative flex h-full flex-1 items-end">
              <div
                className="w-full rounded-t bg-brand/80 group-hover:bg-brand"
                style={{ height: `${(value / maxDaily) * 100}%`, minHeight: value > 0 ? 2 : 0 }}
              />
              <div className="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden -translate-x-1/2 whitespace-nowrap rounded bg-slate-900 px-2 py-1 text-xs text-white group-hover:block">
                {day}: {formatNumber(value)}
              </div>
            </div>
          ))}
        </div>
        <div className="mt-1 flex justify-between border-t border-slate-200 pt-1 text-xs text-slate-400">
          <span>{daily[0][0]}</span>
          <span>{daily[daily.length - 1][0]}</span>
        </div>
      </section>

      <div className="grid gap-6 lg:grid-cols-2">
        <section className={cardClass}>
          <h2 className="font-bold">SNS別パフォーマンス</h2>
          {byPlatform.length === 0 ? (
            <p className="mt-3 text-sm text-slate-500">データがありません。</p>
          ) : (
            <table className="mt-3 w-full text-sm">
              <thead className="text-left text-xs text-slate-500">
                <tr>
                  <th className="py-1 font-medium">SNS</th>
                  <th className="py-1 text-right font-medium">投稿</th>
                  <th className="py-1 pl-3 font-medium">エンゲージメント</th>
                  <th className="py-1 text-right font-medium">ER</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {byPlatform.map((r) => (
                  <tr key={r.platform}>
                    <td className="py-2">
                      <PlatformBadge platform={r.platform as PlatformKey} />
                    </td>
                    <td className="py-2 text-right tabular-nums">{r.posts}</td>
                    <td className="py-2 pl-3">
                      <div className="flex items-center gap-2">
                        <div className="h-2 flex-1 rounded bg-slate-100">
                          <div
                            className="h-2 rounded bg-brand/80"
                            style={{ width: `${(r.engagement / maxPlatformEngagement) * 100}%` }}
                          />
                        </div>
                        <span className="w-16 text-right text-xs tabular-nums">{formatNumber(r.engagement)}</span>
                      </div>
                    </td>
                    <td className="py-2 text-right tabular-nums">{formatPercent(r.rate)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </section>

        <section className={cardClass}>
          <div className="flex items-center justify-between">
            <h2 className="font-bold">予約中の投稿</h2>
            <Link href="/posts?status=SCHEDULED" className="text-xs text-brand hover:underline">
              すべて見る
            </Link>
          </div>
          {overdueCount > 0 && (
            <p className="mt-2 rounded bg-rose-50 px-3 py-1.5 text-xs text-rose-700">
              ⚠ 予約時刻を過ぎた未公開の投稿が{overdueCount}件あります
            </p>
          )}
          {upcoming.length === 0 ? (
            <p className="mt-3 text-sm text-slate-500">予約中の投稿はありません。</p>
          ) : (
            <ul className="mt-3 divide-y divide-slate-100 text-sm">
              {upcoming.map((p) => (
                <li key={p.id} className="flex items-center gap-2 py-2">
                  <PlatformBadge platform={p.account.platform} />
                  <Link href={`/posts/${p.id}`} className="flex-1 truncate hover:text-brand">
                    {p.content}
                  </Link>
                  <span
                    className={`whitespace-nowrap text-xs ${p.scheduledAt && p.scheduledAt < now ? "text-rose-600" : "text-slate-500"}`}
                  >
                    {formatDateTime(p.scheduledAt)}
                  </span>
                </li>
              ))}
            </ul>
          )}
        </section>
      </div>

      <section className={cardClass}>
        <h2 className="font-bold">エンゲージメント上位の投稿</h2>
        {topPosts.length === 0 ? (
          <p className="mt-3 text-sm text-slate-500">期間内に公開された投稿はありません。</p>
        ) : (
          <table className="mt-3 w-full text-sm">
            <thead className="text-left text-xs text-slate-500">
              <tr>
                <th className="py-1 font-medium">投稿</th>
                <th className="py-1 text-right font-medium">imp</th>
                <th className="py-1 text-right font-medium">エンゲージメント</th>
                <th className="py-1 text-right font-medium">ER</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100">
              {topPosts.map((p) => (
                <tr key={p.id}>
                  <td className="max-w-md py-2 pr-3">
                    <div className="flex items-center gap-2">
                      <PlatformBadge platform={p.account.platform} />
                      <span className="text-xs text-slate-500">{p.account.client.name}</span>
                    </div>
                    <Link href={`/posts/${p.id}`} className="mt-1 line-clamp-1 hover:text-brand">
                      {p.content}
                    </Link>
                  </td>
                  <td className="py-2 text-right tabular-nums">{formatNumber(p.impressions)}</td>
                  <td className="py-2 text-right tabular-nums">{formatNumber(engagementCount(p))}</td>
                  <td className="py-2 text-right tabular-nums">{formatPercent(engagementRate(p))}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </section>
    </div>
  );
}
