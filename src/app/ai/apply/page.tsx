import Link from "next/link";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { LeadForm } from "@/components/ai/lead-form";
import { sampleReport } from "@/lib/ai-check/sample";
import { reportKpis } from "@/components/ai/report-dashboard";

export const metadata = { title: "申し込み | ススメル" };

const COPY = {
  CHECK: {
    title: "無料で診断する",
    lead: "見込み客がAIに聞きそうな質問で、御社のサービスがChatGPTに紹介されているかを測り、公式サイトを診断します。",
    facts: [
      ["料金", "0円"],
      ["届くもの", "紹介率と、最初に直すこと上位3つ"],
      ["届く時期", "3営業日以内にメールで"],
    ],
    after: ["3営業日以内に、診断結果をメールでお送りします", "電話での営業はしません。トライアルのご案内をメールで1回お送りすることがあります", "続けるかどうかは、結果を見てから決めてください"],
  },
  TRIAL: {
    title: "GEO診断・改善トライアル",
    lead: "現状を数字で確かめ、改善を1つ実際に行い、30日後に同じ条件で測り直します。",
    facts: [
      ["料金", "49,800円（税別・1回）"],
      ["期間", "30日間"],
      ["内容", "2つのAIで計測・競合2社と比較・直す点を3つ特定・1ページ改善・再計測"],
    ],
    after: ["2営業日以内に、進め方とお見積もりをメールでお送りします", "内容を確認してから正式にご依頼ください。申し込みの時点では料金はかかりません", "ご依頼後、30分のヒアリングから始めます"],
  },
} as const;

export default async function ApplyPage({ searchParams }: PageProps<"/ai/apply">) {
  const sp = await searchParams;
  const kind = sp.plan === "trial" ? "TRIAL" : "CHECK";
  const copy = COPY[kind];
  const kpis = reportKpis(sampleReport());

  // ログイン中なら、登録済みのサービスとアカウントの情報を入れておく
  const session = await auth();
  let defaults: { company?: string; websiteUrl?: string; name?: string; email?: string } = {};
  if (session?.user?.id) {
    const user = await prisma.user.findUnique({ where: { id: session.user.id }, select: { name: true, email: true } });
    const storeId = typeof sp.store === "string" ? sp.store : undefined;
    const store = storeId ? await prisma.store.findFirst({ where: { id: storeId, ownerId: session.user.id }, select: { name: true, websiteUrl: true } }) : null;
    defaults = { name: user?.name, email: user?.email, company: store?.name, websiteUrl: store?.websiteUrl ?? undefined };
  }
  const storeQ = typeof sp.store === "string" ? `&store=${sp.store}` : "";

  return (
    <div className="mx-auto max-w-5xl pt-4">
      {/* 申し込みの種類の切り替え（?plan=trial） */}
      <nav aria-label="申し込みの種類" className="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-1 text-sm">
        {[
          ["CHECK", "無料で診断", `/ai/apply${storeQ ? `?${storeQ.slice(1)}` : ""}`],
          ["TRIAL", "トライアル 49,800円", `/ai/apply?plan=trial${storeQ}`],
        ].map(([k, label, href]) => (
          <Link
            key={k}
            href={href}
            aria-current={kind === k ? "page" : undefined}
            className={`rounded-md px-3 py-1.5 font-semibold ${kind === k ? "bg-white text-slate-900 shadow-[0_1px_2px_rgba(17,24,39,0.08)] ring-1 ring-slate-200" : "text-slate-600 hover:text-slate-900"}`}
          >
            {label}
          </Link>
        ))}
      </nav>

      <div className="mt-6 grid gap-x-12 gap-y-8 lg:grid-cols-[minmax(0,1fr)_minmax(0,26rem)]">
        <div className="min-w-0">
          <h1 className="text-[1.875rem] font-bold leading-tight text-slate-900 sm:text-[2.25rem]">{copy.title}</h1>
          <p className="mt-3 text-lg text-slate-600">{copy.lead}</p>
          <dl className="mt-6 divide-y divide-slate-200 border-y border-slate-200">
            {copy.facts.map(([k, v]) => (
              <div key={k} className="grid grid-cols-[6rem_1fr] gap-4 py-3">
                <dt className="text-sm font-semibold text-slate-600">{k}</dt>
                <dd className="text-slate-900">{v}</dd>
              </div>
            ))}
          </dl>
        </div>

        {/* フォーム：スマホでは見出しのすぐ後、PCでは右の列 */}
        <div className="min-w-0 rounded-xl border border-slate-200 bg-white p-5 sm:p-6 lg:row-span-2 lg:self-start">
          <LeadForm kind={kind} defaults={defaults} />
        </div>

        <div className="min-w-0 space-y-8">
          <section>
            <h2 className="text-lg font-semibold text-slate-900">送信したあと</h2>
            <ol className="mt-3 space-y-0">
              {copy.after.map((t, i) => (
                <li key={t} className="relative grid grid-cols-[2rem_1fr] gap-2 pb-4 last:pb-0">
                  <span className="relative z-10 grid h-7 w-7 place-items-center rounded-full border border-slate-300 bg-white font-num text-sm font-semibold text-slate-700">{i + 1}</span>
                  {i < copy.after.length - 1 && <span className="absolute left-[0.85rem] top-7 h-[calc(100%-1.75rem)] w-px bg-slate-200" aria-hidden />}
                  <span className="pt-0.5 text-slate-800">{t}</span>
                </li>
              ))}
            </ol>
          </section>

          <section className="rounded-xl border border-slate-200 bg-slate-50 p-5">
            <h2 className="text-sm font-semibold text-slate-900">届く結果の例（デモ）</h2>
            <dl className="mt-3 grid grid-cols-2 gap-x-4 gap-y-3">
              {kpis.slice(0, 4).map((k) => (
                <div key={k.label}>
                  <dt className="text-xs font-medium text-slate-600">{k.label}</dt>
                  <dd className="font-num text-xl font-semibold text-slate-900">
                    {k.value}
                    <span className="text-sm text-slate-500">{k.unit}</span>
                  </dd>
                </div>
              ))}
            </dl>
            <Link href="/ai/sample" className="mt-3 inline-block text-sm font-semibold text-brand hover:underline">
              サンプルレポートを見る →
            </Link>
          </section>

          <p className="text-sm text-slate-600">
            いただいた情報は、診断とご連絡にだけ使います。結果を公開したり、ほかの会社に渡したりすることはありません。AIの回答は毎回変わるため、紹介や問い合わせの増加をお約束するものではありません。
          </p>
        </div>
      </div>
    </div>
  );
}
