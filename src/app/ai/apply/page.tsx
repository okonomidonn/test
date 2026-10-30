import Link from "next/link";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { LeadForm } from "@/components/ai/lead-form";
import { sampleReport } from "@/lib/ai-check/sample";
import { reportKpis } from "@/components/ai/report-dashboard";

export const metadata = { title: "申し込み | AIススメル" };

const COPY = {
  CHECK: {
    title: "無料で診断する",
    lead: "見込み客がAIに聞きそうな質問で、御社のサービスがChatGPTに紹介されているかを測り、公式サイトを診断します。",
    facts: [
      ["料金", "0円"],
      ["届くもの", "紹介率と、最初に直すこと上位3つ"],
      ["届く時期", "3営業日以内にメールで"],
    ],
    after: ["3営業日以内に、診断結果をメールでお送りします", "電話での営業はしません。ご連絡は診断とご相談への対応に必要な範囲に限ります", "続けるかどうかは、結果を見てから決めてください"],
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
  const monthly = sp.plan === "standard" ? { title: "スタンダード", price: "100,000円（税別）／110,000円（税込）", scope: "毎月のAI計測・競合比較・GA4の確認・既存ページ改善・月1回の打ち合わせ" } : sp.plan === "premium" ? { title: "プレミアム", price: "200,000円（税別）／220,000円（税込）", scope: "スタンダードの内容に加え、新規ページの企画・原稿作成・CMSでの制作" } : null;
  const kind = sp.plan === "trial" || monthly ? "TRIAL" : "CHECK";
  const copy = monthly ? {
    title: `継続支援：${monthly.title}のご相談`,
    lead: "1か月単位で、AIでの紹介状況を確認しながらサイトを改善します。",
    facts: [["月額", monthly.price], ["契約", "1か月契約・毎月更新。最低契約期間の縛りなし"], ["内容", monthly.scope], ["作業範囲", "1サービスを対象に、ページ本数・原稿量・修正回数・実装範囲を見積書で合意します"]],
    after: ["2営業日以内に、進め方とお見積もりをメールでお送りします", "見積もりの作業範囲・更新条件をご確認いただいてから正式なご依頼となります", "銀行振込による前払いです。入金確認後、合意した日程で作業を始めます"],
  } : COPY[kind];
  const kpis = reportKpis(sampleReport());

  // ログイン中なら、登録済みのサービスとアカウントの情報を入れておく
  const session = await auth();
  let defaults: { company?: string; websiteUrl?: string; name?: string; email?: string; message?: string } = {};
  if (session?.user?.id) {
    const user = await prisma.user.findUnique({ where: { id: session.user.id }, select: { name: true, email: true } });
    const storeId = typeof sp.store === "string" ? sp.store : undefined;
    const store = storeId ? await prisma.store.findFirst({ where: { id: storeId, ownerId: session.user.id }, select: { name: true, websiteUrl: true } }) : null;
    defaults = { name: user?.name, email: user?.email, company: store?.name, websiteUrl: store?.websiteUrl ?? undefined };
  }
  if (monthly) defaults.message = `継続支援の${monthly.title}（月額${monthly.price}）について相談したいです。`;
  const storeQ = typeof sp.store === "string" ? `&store=${sp.store}` : "";

  return (
    <div className="mx-auto max-w-5xl pt-4">
      {/* 申し込みの種類の切り替え（?plan=trial） */}
      <nav aria-label="申し込みの種類" className="flex flex-wrap gap-1 rounded-lg border border-slate-200 bg-slate-50 p-1 text-sm">
        {[
          ["CHECK", "無料で診断", `/ai/apply${storeQ ? `?${storeQ.slice(1)}` : ""}`],
          ["TRIAL", "トライアル 49,800円", `/ai/apply?plan=trial${storeQ}`],
          ["standard", "月額スタンダード", `/ai/apply?plan=standard${storeQ}`],
          ["premium", "月額プレミアム", `/ai/apply?plan=premium${storeQ}`],
        ].map(([k, label, href]) => (
          <Link
            key={k}
            href={href}
            aria-current={(monthly ? sp.plan === k : kind === k) ? "page" : undefined}
            className={`rounded-md px-3 py-1.5 font-semibold ${(monthly ? sp.plan === k : kind === k) ? "bg-white text-slate-900 shadow-[0_1px_2px_rgba(17,24,39,0.08)] ring-1 ring-slate-200" : "text-slate-600 hover:text-slate-900"}`}
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
          <LeadForm key={typeof sp.plan === "string" ? sp.plan : "check"} kind={kind} defaults={defaults} submitLabel={monthly ? "継続支援を相談する" : undefined} />
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

          <section className="space-y-2 text-sm text-slate-700">
            <h2 className="text-base font-semibold text-slate-900">情報の取り扱い</h2>
            <p>いただいた情報は、診断とご連絡にだけ使います。サイトのURLは、公開されているページの読み取りとAIの回答の判定にだけ使い、結果を公開したり、ほかの会社に渡したりすることはありません。</p>
            <p>
              <Link href="/ai/method#privacy" className="font-semibold text-brand hover:underline">
                情報の取り扱い
              </Link>
              ・
              <Link href="/ai/method#measure" className="font-semibold text-brand hover:underline">
                計測のしかたと限界
              </Link>
            </p>
          </section>
        </div>
      </div>
    </div>
  );
}
