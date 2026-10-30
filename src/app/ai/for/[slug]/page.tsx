import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { HeroLeadForm } from "@/components/ai/hero-lead-form";
import { Band, Cta, Head, em } from "@/components/ai/lp";
import { ServicePlans } from "@/components/ai/service-plans";
import { VERTICALS, verticalBySlug } from "@/lib/ai-check/verticals";

export function generateStaticParams() {
  return VERTICALS.map((v) => ({ slug: v.slug }));
}

export async function generateMetadata({ params }: PageProps<"/ai/for/[slug]">): Promise<Metadata> {
  const v = verticalBySlug((await params).slug);
  return v ? { title: v.title, description: v.description } : {};
}

// 業種別の紹介ページ。中身は src/lib/ai-check/verticals.ts
export default async function VerticalPage({ params }: PageProps<"/ai/for/[slug]">) {
  const v = verticalBySlug((await params).slug);
  if (!v) notFound();

  return (
    <div className="-mt-8">
      <section className="ml-[calc(50%-50vw)] w-screen border-b border-slate-200 bg-[linear-gradient(180deg,#f3f7ff_0%,#ffffff_100%)]">
        <div className="mx-auto grid max-w-6xl items-center gap-10 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[1.1fr_1fr]">
          <div>
            <p className="inline-flex rounded-md bg-brand/10 px-3 py-1 text-sm font-bold text-brand">{v.name}向け AI検索対策</p>
            <h1 className="mt-4 text-[2rem] font-bold leading-[1.3] tracking-tight text-slate-900 sm:text-[2.75rem]">
              {v.hero.lead}
              <br />
              <span className="text-brand">{v.hero.em}</span>
              <br />
              {v.hero.tail}
            </h1>
            <p className="mt-5 max-w-lg leading-relaxed text-slate-700 sm:text-lg">{v.hero.sub}</p>
          </div>
          <div id="apply" className="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_12px_32px_-12px_rgba(17,24,39,0.18)] sm:p-7">
            <p className="text-xl font-bold text-slate-900">{v.name}の無料診断</p>
            <p className="mt-1 text-sm text-slate-600">費用0円・3営業日以内にメールでお届け</p>
            <div className="mt-5">
              <HeroLeadForm />
            </div>
          </div>
        </div>
      </section>

      <Band className="bg-white">
        <Head en={`${v.customer}がAIに聞く質問`} sub={`${v.customer}は、サイトを見る前にAIで候補を絞っています。AIススメルは、次の3種類の質問であなたのサイトが紹介されるかを測ります。`}>
          {v.customer}は、<span className={em}>AIに聞いてから</span>{v.name}を選びます
        </Head>
        <div className="mt-10 grid gap-4 md:grid-cols-3">
          {v.questions.map((q) => (
            <div key={q.type} className="rounded-xl bg-[#f5f8fd] p-5">
              <p className="font-bold text-brand">{q.type}</p>
              <p className="mt-3 rounded-lg bg-white px-3 py-2 text-slate-900 ring-1 ring-slate-200">「{q.text}」</p>
              <p className="mt-3 text-sm leading-relaxed text-slate-700">{q.note}</p>
            </div>
          ))}
        </div>
      </Band>

      <Band className="bg-[#f5f8fd]">
        <Head en="診断で見るところ">
          {v.name}ならではの<span className={em}>6つのチェック</span>
        </Head>
        <ol className="mt-10 grid gap-4 md:grid-cols-2">
          {v.checks.map((c, i) => (
            <li key={c.title} className="flex gap-4 rounded-xl bg-white p-5 ring-1 ring-slate-200">
              <span className="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-brand font-num font-bold text-white">{i + 1}</span>
              <div>
                <h3 className="font-bold text-slate-900">{c.title}</h3>
                <p className="mt-1 text-sm leading-relaxed text-slate-700">{c.body}</p>
              </div>
            </li>
          ))}
        </ol>
        <div className="mt-10 rounded-2xl bg-white p-6 ring-1 ring-slate-200 sm:p-8">
          <h3 className="text-lg font-bold text-slate-900">{v.name}でよくある「AIに紹介されない」原因</h3>
          <ul className="mt-4 grid gap-3 sm:grid-cols-2">
            {v.mistakes.map((m) => (
              <li key={m} className="flex gap-2 text-slate-800">
                <span aria-hidden className="font-bold text-rose-600">✕</span>
                {m}
              </li>
            ))}
          </ul>
        </div>
        <Cta />
      </Band>

      {v.proof && (
        <Band className="bg-white">
          <Head en="実例">
            <span className={em}>{v.name}を運営する私たち</span>が、自分のサイトで試しています
          </Head>
          <div className="mx-auto mt-10 max-w-3xl rounded-2xl bg-[#e3ebf5] p-6 sm:p-8">
            <p className="font-bold text-slate-900">{v.proof.title}</p>
            <dl className="mt-4 grid grid-cols-2 gap-4">
              {v.proof.stats.map(([val, k]) => (
                <div key={k} className="rounded-xl bg-white p-4 text-center">
                  <dd className="font-num text-4xl font-bold text-brand">{val}</dd>
                  <dt className="mt-1 text-sm text-slate-700">{k}</dt>
                </div>
              ))}
            </dl>
            <p className="mt-4 text-xs leading-relaxed text-slate-600">{v.proof.note}</p>
          </div>
        </Band>
      )}

      <Band className="bg-[#13284f]">
        <Head en="料金" light sub="無料チェックだけでもご利用いただけます。有料プランへの自動切り替えはありません。">
          まず無料で。改善の依頼はそのあとで。
        </Head>
        <div className="mt-10 rounded-2xl bg-white p-4 sm:p-6">
          <ServicePlans />
        </div>
      </Band>

      <Band className="bg-[#f5f8fd]">
        <Head en="Q&A">{v.name}のよくある質問</Head>
        <div className="mx-auto mt-10 max-w-3xl space-y-3">
          {v.faq.map(([q, a]) => (
            <details key={q} className="group rounded-xl bg-white px-5 ring-1 ring-slate-200">
              <summary className="flex cursor-pointer list-none items-start justify-between gap-4 py-5 font-bold text-slate-900">
                <span>
                  <span className="mr-2 text-brand">Q.</span>
                  {q}
                </span>
                <span aria-hidden className="shrink-0 text-brand group-open:hidden">＋</span>
                <span aria-hidden className="hidden shrink-0 text-brand group-open:inline">−</span>
              </summary>
              <p className="pb-5 leading-relaxed text-slate-700">{a}</p>
            </details>
          ))}
        </div>
        <p className="mt-8 text-center text-sm">
          <Link href="/ai" className="font-semibold text-brand underline underline-offset-4">
            AIススメルのサービス全体を見る →
          </Link>
        </p>
      </Band>
    </div>
  );
}
