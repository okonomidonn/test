import Link from "next/link";
import { redirect } from "next/navigation";
import { auth } from "@/auth";
import { prisma } from "@/lib/prisma";
import { LeadForm } from "@/components/ai/lead-form";
import { Section } from "@/components/ai/lp";
import { LEAD_PLANS, resolveLeadPlan } from "@/lib/leads/plans";

export const metadata = { title: "申し込み | AIススメル" };

const COPY = {
  CHECK: {
    title: "無料で診断する",
    lead: "求職者や採用担当者がAIに聞きそうな質問で、御社の求人サイトがChatGPTに紹介されているかを測り、サイトを診断します。",
    facts: [
      ["料金", "0円"],
      ["届くもの", "紹介率と、最初に直すこと上位3つ"],
      ["届く時期", "3営業日以内にメールで"],
    ],
    after: ["3営業日以内に、診断結果をメールでお送りします", "電話での営業はしません。ご連絡は診断とご相談への対応に必要な範囲に限ります", "続けるかどうかは、結果を見てから決めてください"],
  },
} as const;

export default async function ApplyPage({ searchParams }: PageProps<"/ai/apply">) {
  const sp = await searchParams;
  // 資料請求は専用ページ（/ai/for/jobs/book）で受け付ける
  const resolved = resolveLeadPlan(sp.plan);
  // トライアルは提供を終了したため、以前のリンク（?plan=trial）は基本プランのご相談として受け付ける
  const plan = resolved === "book" ? "check" : resolved === "trial" ? "basic" : resolved;
  // 無料診断はトップページ下のフォームで受け付ける（ログイン中の店舗からの申し込みだけ、このページを使う）
  if (plan === "check" && typeof sp.store !== "string") redirect("/ai#apply");
  const kind = LEAD_PLANS[plan].kind;
  const monthly = plan === "basic" || plan === "production";
  const copy = monthly ? {
    title: `${LEAD_PLANS[plan].label}のご相談`,
    lead: "ご希望のプランに合わせて、対象ページと作業範囲を確認し、お見積もりをお送りします。",
    facts: [
      ["月額", plan === "basic" ? "100,000円（税別）／110,000円（税込）" : "200,000円（税別）／220,000円（税込）"],
      ["内容", plan === "basic" ? "2つのAIで毎月計測・競合3社と比較・既存ページの改善月2件・GA4の確認・月1回の報告" : "基本プランに加え、選ばれる理由を伝えるページ月2本（各3,000字まで）・既存ページの改善は月4件・求職者の疑問に答える「よくある質問」の作成"],
      ["契約", "1か月単位・最低契約期間なし。当月20日までのメール連絡で当月末終了"],
      ["対象範囲", "CMSで編集できる範囲。制作本数・文字量・修正回数・実装範囲は契約前に見積書で確定"],
    ],
    after: ["2営業日以内に、ご希望のプランの進め方とお見積もりをメールでお送りします", "対象ページ・作業範囲・支払条件に合意してから正式なご依頼となります", "銀行振込による前払いです。入金確認後、合意した日程で始めます"],
  } : COPY.CHECK;

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
    <div className="-mb-8 -mt-8">
      {/* トップ・料金と同じ作り：青の帯で始め、左に内容、右にフォーム */}
      <section className="ml-[calc(50%-50vw)] w-screen bg-brand text-white">
        <div className="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16">
          <nav aria-label="申し込みの種類" className="flex flex-wrap gap-2 text-sm">
            {[
              ["basic", "基本プラン", `/ai/apply?plan=basic${storeQ}`],
              ["production", "制作プラン", `/ai/apply?plan=production${storeQ}`],
            ].map(([k, label, href]) => (
              <Link key={k} href={href} aria-current={plan === k ? "page" : undefined} className={`rounded-md px-4 py-2 font-bold transition ${plan === k ? "bg-white text-brand" : "border border-white/50 text-white hover:bg-white/10"}`}>
                {label}
              </Link>
            ))}
          </nav>
          <h1 className="mt-6 text-[2rem] font-black leading-tight sm:text-[2.75rem]">{copy.title}</h1>
          <p className="palt mt-4 max-w-2xl text-lg leading-relaxed text-blue-50">{copy.lead}</p>
        </div>
      </section>

      <Section>
        <div className="grid gap-x-12 gap-y-10 lg:grid-cols-[minmax(0,1fr)_minmax(0,27rem)]">
          <div className="min-w-0 space-y-10 lg:order-1">
            <dl className="divide-y divide-slate-200 rounded-md bg-[#F1F6FE] px-6 sm:px-8">
              {copy.facts.map(([k, v]) => (
                <div key={k} className="grid gap-1 py-5 sm:grid-cols-[6rem_1fr] sm:gap-4">
                  <dt className="text-sm font-bold text-brand">{k}</dt>
                  <dd className="text-slate-900">{v}</dd>
                </div>
              ))}
            </dl>

            <section>
              <h2 className="text-2xl font-bold text-slate-900">送信したあと</h2>
              <ol className="mt-5 grid gap-3">
                {copy.after.map((t) => (
                  <li key={t} className="rounded-md border border-slate-200 bg-white p-5 text-slate-800">{t}</li>
                ))}
              </ol>
            </section>

            <section className="space-y-2 text-sm text-slate-600">
              <h2 className="text-base font-bold text-slate-900">情報の取り扱い</h2>
              <p>いただいた情報は、診断とご連絡にだけ使います。サイトのURLは、公開されているページの読み取りとAIの回答の判定にだけ使い、結果を公開したり、ほかの会社に渡したりすることはありません。</p>
              <p>
                <Link href="/ai/method#privacy" className="font-bold text-brand hover:underline">情報の取り扱い</Link>
                ・
                <Link href="/ai/method#measure" className="font-bold text-brand hover:underline">計測のしかたと限界</Link>
              </p>
            </section>
          </div>

          {/* フォーム：スマホでは先頭、PCでは右の列 */}
          <div className="order-first min-w-0 rounded-md bg-white p-6 shadow-[0_16px_40px_rgba(30,60,120,0.12)] ring-1 ring-slate-200 sm:p-8 lg:order-2 lg:sticky lg:top-24 lg:self-start">
            <LeadForm key={plan} kind={kind} defaults={defaults} plan={plan} submitLabel={monthly ? `${LEAD_PLANS[plan].label}を相談する` : undefined} />
          </div>
        </div>
      </Section>
    </div>
  );
}
